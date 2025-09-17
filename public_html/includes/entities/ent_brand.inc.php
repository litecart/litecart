<?php

	class ent_brand {
		public $data;
		public $previous;

		public function __construct($id='') {

			if ($id) {
				$this->load($id);
			} else {
				$this->reset();
			}
		}

		public function reset() {

			$this->data = [];

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."brands;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			foreach ([
				'short_description',
				'description',
				'head_title',
				'h1_title',
				'meta_description',
				'link',
			] as $column) {
				$this->data[$column] = array_fill_keys(array_keys(language::$languages), '');
			}

			$this->previous = $this->data;
		}

		public function load($id) {

			if (!preg_match('#^[0-9]+$#', $id)) {
				throw new Exception('Invalid brand (ID: '. $id .')');
			}

			$this->reset();

			$brand = database::query(
				"select * from ". DB_TABLE_PREFIX ."brands
				where id = ". (int)$id ."
				limit 1;"
			)->fetch();

			if (!$brand) {
				throw new Exception('Could not find brand (ID: '. (int)$id .') in database.');
			}

			$this->data = array_replace($this->data, array_intersect_key($brand, $this->data));

			foreach ([
				'short_description',
				'description',
				'head_title',
				'h1_title',
				'meta_description',
				'link',
			] as $column) {
				$this->data[$column] = json_decode($this->data[$column], true) ?: [];
				$this->data[$column] += array_fill_keys(array_keys(language::$languages), '');
			}

			$this->previous = $this->data;
		}

		public function save() {

			if (!$this->data['id']) {

				database::query(
					"insert into ". DB_TABLE_PREFIX ."brands
					(created_at)
					values ('". ($this->data['created_at'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			$this->data['keywords'] = functions::string_split($this->data['keywords']);
			$this->data['keywords'] = array_unique($this->data['keywords']);
			$this->data['keywords'] = implode(',', $this->data['keywords']);

			database::query(
				"update ". DB_TABLE_PREFIX ."brands
				set status = '". database::input($this->data['status']) ."',
					featured = '". database::input($this->data['featured']) ."',
					code = '". database::input($this->data['code']) ."',
					name = '". database::input($this->data['name']) ."',
					short_description = '". database::input(functions::json_format($this->data['short_description'])) ."',
					description = '". database::input(functions::json_format($this->data['description'])) ."',
					head_title = '". database::input(functions::json_format($this->data['head_title'])) ."',
					h1_title = '". database::input(functions::json_format($this->data['h1_title'])) ."',
					meta_description = '". database::input(functions::json_format($this->data['meta_description'])) ."',
					link = '". database::input(functions::json_format($this->data['link'])) ."',
					image = '". database::input($this->data['image']) ."',
					keywords = '". database::input($this->data['keywords']) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->previous = $this->data;

			cache::clear_cache('brands');
		}

		public function save_image($file) {

			if (!$file) {
				return;
			}

			if (!$this->data['id']) {
				$this->save();
			}

			if (!is_dir('storage://images/brands/')) {
				mkdir('storage://images/brands/', 0777);
			}

			$image = new ent_image($file);

			// 456-12345_Fancy-title.jpg
			$filename = 'brands/' . $this->data['id'] .'-'. functions::format_path_friendly($this->data['name'], settings::get('store_language_code')) .'.'. $image->type;

			if (is_file('storage://images/' . $this->data['image'])) {
				unlink('storage://images/' . $this->data['image']);
			}

			functions::image_delete_cache('storage://images/' . $filename);

			if (settings::get('image_downsample_size')) {
				list($width, $height) = functions::string_split(settings::get('image_downsample_size'));
				$image->resample($width, $height, 'FIT_ONLY_BIGGER');
			}

			$image->save('storage://images/' . $filename, 90);

			database::query(
				"update ". DB_TABLE_PREFIX ."brands
				set image = '". database::input($filename) ."'
				where id = ". (int)$this->data['id'] .";"
			);

			$this->previous['image'] = $this->data['image'] = $filename;
		}

		public function delete_image() {

			if (!$this->data['id']) return;

			if ($this->data['image'] && is_file('storage://images/' . $this->data['image'])) {
				unlink('storage://images/' . $this->data['image']);
			}

			functions::image_delete_cache('storage://images/' . $this->data['image']);

			database::query(
				"update ". DB_TABLE_PREFIX ."brands
				set image = ''
				where id = ". (int)$this->data['id'] .";"
			);

			$this->previous['image'] = $this->data['image'] = '';
		}

		public function delete() {

			if (!$this->data['id']) return;

			if (database::query(
				"select id from ". DB_TABLE_PREFIX ."products
				where brand_id = ". (int)$this->data['id'] ."
				limit 1;"
			)->num_rows) {
				notices::add('errors', t('error_delete_brand_not_empty_products', 'The brand could not be deleted because there are products linked to it.'));
				reload();
				exit;
			}

			if ($this->data['image'] && is_file('storage://images/brands/' . $this->data['image'])) {
				unlink('storage://images/brands/' . $this->data['image']);
			}

			database::query(
				"delete b
				from ". DB_TABLE_PREFIX ."brands b
				where b.id = ". (int)$this->data['id'] .";"
			);

			$this->reset();

			cache::clear_cache('brands');
		}
	}
