<?php

	class ent_review {
		public $data;
		public $previous;

		public function __construct(int|null $review_id = null) {

			if ($review_id) {
				$this->load($review_id);
			} else {
				$this->reset();
			}
		}

		public function reset(): void {

			$this->data = [];

			foreach (database::schema('reviews') as $field) {
				$this->data[$field['Field']] = database::create_variable($field);
			}

			// initialize multi-language fields previously stored in a separate info table
			$this->data['title'] = array_fill_keys(array_keys(language::$languages), '');
			$this->data['description'] = array_fill_keys(array_keys(language::$languages), '');
			$this->data['attachments'] = [];

			$this->previous = $this->data;
		}

		public function load(int $review_id): void {

			if (!preg_match('#^\d+$#', $review_id)) {
				throw new Exception('Invalid review (ID: '. $review_id .')');
			}

			$this->reset();

			$review = database::query(
				"select * from ". DB_PREFIX ."reviews
				where id = ". (int)$review_id ."
				limit 1;"
			)->fetch(function(&$review) {
				$review['title'] = json_decode($review['title'], true) ?: [];
				$review['description'] = json_decode($review['description'], true) ?: [];
				$review['attachments'] = json_decode($review['attachments'], true) ?: [];
			});

			if (!$review) {
				throw new Exception('Could not find review (ID: '. (int)$review_id .') in database.');
			}

			$this->data = array_replace($this->data, array_intersect_key($review, $this->data));



			$this->previous = $this->data;
		}

		public function save(): void {

			if (empty($this->data['id'])) {
				database::insert('reviews', [
					'product_id' => (int)$this->data['product_id'],
					'customer_id' => (int)$this->data['customer_id'],
					'created_at' => date('Y-m-d H:i:s'),
				]);

				$this->data['id'] = database::insert_id();
			}

			// Read the stored attachments before the update, or the removed ones can not be detected
			$existing_attachments = json_decode(database::query(
				"select attachments from ". DB_PREFIX ."reviews
				where id = ". (int)$this->data['id'] ." limit 1;"
			)->fetch('attachments'), true) ?: [];

			database::query(
				"update ". DB_PREFIX ."reviews
				set status = ". (int)$this->data['status'] .",
					product_id = ". (int)$this->data['product_id'] .",
					customer_id = ". (int)$this->data['customer_id'] .",
					customer_email = '". database::input($this->data['customer_email']) ."',
					customer_name = '". database::input($this->data['customer_name']) ."',
					rating = ". (int)$this->data['rating'] .",
					upvotes = ". (int)$this->data['upvotes'] .",
					downvotes = ". (int)$this->data['downvotes'] .",
					title = '". database::input(f::format_json($this->data['title'])) ."',
					description = '". database::input(f::format_json($this->data['description'])) ."',
					attachments = '". database::input(f::format_json($this->data['attachments'])) ."',
					updated_at = '". date('Y-m-d H:i:s') ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			// Delete attachments that were removed

			$remaining_ids = array_column($this->data['attachments'], 'id');

			foreach ($existing_attachments as $attachment) {
				if (!in_array($attachment['id'], $remaining_ids)) {

					$attachment_file = 'storage://reviews/attachments/' . $attachment['attachment'];

					if (is_file($attachment_file)) {
						@unlink($attachment_file);
					}

					f::image_delete_cache($attachment_file);
				}
			}

			// attachments are stored in the review row as JSON; ensure priorities are correct
			$priority = 1;
			foreach ($this->data['attachments'] as $key => $attachment) {

				if (empty($attachment['id'])) {
					$attachment['id'] = time() . '-' . random_int(1000, 9999);
					$this->data['attachments'][$key]['id'] = $attachment['id'];
				}

				if (!empty($attachment['new_filename']) && !is_file('storage://reviews/attachments/' . $attachment['new_filename'])) {

					f::image_delete_cache('storage://reviews/attachments/' . $attachment['filename']);
					f::image_delete_cache('storage://reviews/attachments/' . $attachment['new_filename']);

					if ($attachment['new_filename'] != $attachment['filename']) {
						rename('storage://reviews/attachments/' . $attachment['filename'], 'storage://reviews/attachments/' . $attachment['new_filename']);
						$attachment['filename'] = $attachment['new_filename'];
						$this->data['attachments'][$key]['filename'] = $attachment['filename'];
					}
				}

				$this->data['attachments'][$key]['priority'] = $priority++;
			}

			database::query(
				"update ". DB_PREFIX ."reviews
				set attachments = '". database::input(f::format_json($this->data['attachments'])) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->previous = $this->data;

			cache::clear_cache('reviews');
		}

		public function add_attachment(string $src, string $filename, string $mime = ''): ?bool {

			if (empty($src)) {
				return null;
			}

			if (empty($this->data['id'])) {
				$this->save();
			}

			if (!is_dir('storage://reviews/attachments/')) {
				mkdir('storage://reviews/attachments/', 2775, true);
			}

			$file = null;

			$i = 1;
			while (empty($file) || is_file('storage://reviews/attachments/' . $file)) {
				$file = $this->data['id'] .'-'. $i++ .'-'. pathinfo($filename, PATHINFO_FILENAME) .'.'. strtolower(pathinfo($filename, PATHINFO_EXTENSION));
			}

			$priority = count($this->data['attachments']) + 1;

			if (!@rename($src, 'storage://reviews/attachments/' . $file)) return false;

			$attachment_id = time() . '-' . random_int(1000, 9999);

			$this->data['attachments'][$attachment_id] = [
				'id' => $attachment_id,
				'attachment' => $file,
				'filename' => $filename,
				'mime' => $mime,
				'priority' => $priority,
			];

			$this->previous['attachments'][$attachment_id] = $this->data['attachments'][$attachment_id];

			database::query(
				"update ". DB_PREFIX ."reviews
				set attachments = '". database::input(f::format_json($this->data['attachments'])) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);
		}

		// Get the approved reviews for a product
		// Only public columns are selected: customer_email and customer_id must never be exposed
		public static function get_for_product(int $product_id, int $page=1, int $items_per_page=10, int|null &$num_rows=null, int|null &$num_pages=null): array {

			return database::prepare(
				"select id, product_id, customer_name, rating, upvotes, downvotes,
				title, description, attachments, created_at, updated_at
				from ". DB_PREFIX ."reviews
				where status
				and product_id = ". $product_id ."
				order by created_at desc, id desc;"
			)->fetch_page(function(&$review) {

				$review['title'] = new type_translation(json_decode($review['title'] ?? '', true) ?: []);
				$review['description'] = new type_translation(json_decode($review['description'] ?? '', true) ?: []);
				$review['attachments'] = json_decode($review['attachments'] ?? '', true) ?: [];

				foreach ($review['attachments'] as $key => $attachment) {
					$review['attachments'][$key] = [
						'filename' => $attachment['filename'] ?? '',
						'attachment' => 'storage://reviews/attachments/'. ($attachment['attachment'] ?? ''),
						'link' => document::rlink('storage://reviews/attachments/'. ($attachment['attachment'] ?? '')),
					];
				}
			}, null, $page, $items_per_page, $num_rows, $num_pages);
		}

		// Get the approved review counts and average rating for a product
		public static function get_ratings(int $product_id): array {

			return database::query(
				"select count(*) as num_ratings, round(avg(rating), 1) as average_rating
				from ". DB_PREFIX ."reviews
				where status
				and product_id = ". $product_id
			)->fetch();
		}

		// Get the review a customer has written for a product
		public static function get_for_customer(int $product_id, int $customer_id): array|null {

			// A customer id of zero means nobody is signed in
			if ($customer_id < 1) return null;

			return database::query(
				"select *
				from ". DB_PREFIX ."reviews
				where product_id = ". $product_id ."
				and customer_id = ". $customer_id ."
				limit 1;"
			)->fetch(function(&$review) {
				$review['title'] = new type_translation(json_decode($review['title'] ?? '', true) ?: []);
				$review['description'] = new type_translation(json_decode($review['description'] ?? '', true) ?: []);
				$review['attachments'] = json_decode($review['attachments'] ?? '', true) ?: [];
			});
		}

		public function delete(): void {

			$attachments = json_decode($this->data['attachments'], true) ?: [];

			foreach ($attachments as $attachment) {

				$attachment_file = 'storage://reviews/attachments/' . $attachment['attachment'];

				if (is_file($attachment_file)) {
					@unlink($attachment_file);
				}

				f::image_delete_cache($attachment_file);
			}

			database::query(
				"delete from ". DB_PREFIX ."reviews
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->reset();

			cache::clear_cache('reviews');
		}
	}
