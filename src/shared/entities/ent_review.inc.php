<?php

	class ent_review {
		public $data;
		public $previous;

		const FS_DIR_ATTACHMENTS = FS_DIR_STORAGE . 'data/reviews_attachments/';

		public function __construct(int|null $review_id = null) {

			if ($review_id) {
				$this->load($review_id);
			} else {
				$this->reset();
			}
		}

		public function reset(): void {

			$this->data = [];

			database::query(
				"show fields from ". DB_PREFIX ."reviews;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

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
				database::query(
					"insert into ". DB_PREFIX ."reviews
					(product_id, customer_id, created_at)
					values (". (int)$this->data['product_id'] .", ". (int)$this->data['customer_id'] .", '". database::input(date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

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
					updated_at = '". date('Y-m-d H:i:s') ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

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
			// Delete files for attachments removed
			$existing_attachments = json_decode(database::query("select attachments from ". DB_PREFIX ."reviews where id = ". (int)$this->data['id'] ." limit 1;")->fetch('attachments'), true) ?: [];
			$remaining_ids = array_column($this->data['attachments'], 'id');
			foreach ($existing_attachments as $attachment) {
				if (!in_array($attachment['id'], $remaining_ids)) {
					if (is_file(self::FS_DIR_ATTACHMENTS . $attachment['attachment'])) {
						@unlink(self::FS_DIR_ATTACHMENTS . $attachment['attachment']);
					}
					f::image_delete_cache(self::FS_DIR_ATTACHMENTS . $attachment['attachment']);
				}
			}

			// attachments are stored in the review row as JSON; ensure priorities are correct
			$priority = 1;
			foreach ($this->data['attachments'] as $key => $attachment) {
				if (empty($attachment['id'])) {
					$attachment['id'] = time() . '-' . random_int(1000, 9999);
					$this->data['attachments'][$key]['id'] = $attachment['id'];
				}
				if (!empty($attachment['new_filename']) && !is_file(FS_DIR_APP . 'images/' . $attachment['new_filename'])) {
					f::image_delete_cache(self::FS_DIR_ATTACHMENTS . $attachment['filename']);
					f::image_delete_cache(self::FS_DIR_ATTACHMENTS . $attachment['new_filename']);
					if ($attachment['new_filename'] != $attachment['filename']) {
						rename(self::FS_DIR_ATTACHMENTS . $attachment['filename'], self::FS_DIR_ATTACHMENTS . $attachment['new_filename']);
						$attachment['filename'] = $attachment['new_filename'];
						$this->data['attachments'][$key]['filename'] = $attachment['filename'];
					}
				}
				$this->data['attachments'][$key]['priority'] = $priority++;
			}

			// persist attachments JSON
			database::query(
				"update ". DB_PREFIX ."reviews set attachments = '". database::input(f::format_json($this->data['attachments'])) ."' where id = ". (int)$this->data['id'] ." limit 1;"
			);

			$this->previous = $this->data;

			cache::clear_cache('reviews');
		}

		public function add_attachment(string $src, string $filename, string $mime = ''): ?bool {

			if (empty($src)) return null;

			if (empty($this->data['id'])) $this->save();

			if (!is_dir(self::FS_DIR_ATTACHMENTS)) mkdir(self::FS_DIR_ATTACHMENTS, 0777, true);

			$file = null;
			$i = 1;
			while (empty($file) || is_file(self::FS_DIR_ATTACHMENTS . $file)) {
				$file = $this->data['id'] .'-'. $i++ .'-'. pathinfo($filename, PATHINFO_FILENAME) .'.'. strtolower(pathinfo($filename, PATHINFO_EXTENSION));
			}

			$priority = count($this->data['attachments']) + 1;

			if (!@rename($src, self::FS_DIR_ATTACHMENTS . $file)) return false;

			// add attachment to attachments array and persist
			$attachment_id = time() . '-' . random_int(1000, 9999);
			$this->data['attachments'][$attachment_id] = [
				'id' => $attachment_id,
				'attachment' => $file,
				'filename' => $filename,
				'mime' => $mime,
				'priority' => $priority,
			];

			$this->previous['attachments'][$attachment_id] = $this->data['attachments'][$attachment_id];

			// persist attachments JSON
			database::query(
				"update ". DB_PREFIX ."reviews set attachments = '". database::input(f::format_json($this->data['attachments'])) ."' where id = ". (int)$this->data['id'] ." limit 1;"
			);
		}

		public function delete(): void {

			// remove attachment files listed in attachments JSON
			$attachments = json_decode($this->data['attachments'], true) ?: [];
			foreach ($attachments as $attachment) {
				if (is_file(self::FS_DIR_ATTACHMENTS . $attachment['attachment'])) {
					@unlink(self::FS_DIR_ATTACHMENTS . $attachment['attachment']);
				}
				f::image_delete_cache(self::FS_DIR_ATTACHMENTS . $attachment['attachment']);
			}

			// delete review row
			database::query(
				"delete from ". DB_PREFIX ."reviews
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->reset();

			cache::clear_cache('reviews');
		}
	}
