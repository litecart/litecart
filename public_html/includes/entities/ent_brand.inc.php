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

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."brands_info;"
			)->each(function($field) {
				if (in_array($field['Field'], ['id', 'brand_id', 'language_code'])) return;
				$this->data[$field['Field']] = array_fill_keys(array_keys(language::$languages), database::create_variable($field));
			});

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

			if ($brand) {
				$this->data = array_replace($this->data, array_intersect_key($brand, $this->data));
			} else {
				throw new Exception('Could not find brand (ID: '. (int)$id .') in database.');
			}

			database::query(
				"select * from ". DB_TABLE_PREFIX ."brands_info
				where brand_id = ". (int)$id .";"
			)->each(function($info){
				foreach ($info as $key => $value) {
					if (in_array($key, ['id', 'brand_id', 'language_code'])) continue;
					$this->data[$key][$info['language_code']] = $value;
				}
			});

			$this->previous = $this->data;
		}

		public function save() {

			if (!$this->data['id']) {

				database::query(
					"insert into ". DB_TABLE_PREFIX ."brands
					(date_created)
					values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			$this->data['keywords'] = preg_split('#\s*,\s*#', $this->data['keywords'], -1, PREG_SPLIT_NO_EMPTY);
			$this->data['keywords'] = array_unique($this->data['keywords']);
			$this->data['keywords'] = implode(',', $this->data['keywords']);

			database::query(
				"update ". DB_TABLE_PREFIX ."brands
				set status = '". database::input($this->data['status']) ."',
					featured = '". database::input($this->data['featured']) ."',
					code = '". database::input($this->data['code']) ."',
					name = '". database::input($this->data['name']) ."',
					image = '". database::input($this->data['image']) ."',
					keywords = '". database::input($this->data['keywords']) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			foreach (array_keys(language::$languages) as $language_code) {

				$info = database::query(
					"select * from ". DB_TABLE_PREFIX ."brands_info
					where brand_id = ". (int)$this->data['id'] ."
					and language_code = '". database::input($language_code) ."'
					limit 1;"
				, $this->data)->fetch_all();

				if (!$info) {
					database::query(
						"insert into ". DB_TABLE_PREFIX ."brands_info
						(brand_id, language_code)
						values (". (int)$this->data['id'] .", '". database::input($language_code) ."');"
					);

					$info['id'] = database::insert_id();
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."brands_info
					set short_description = '". database::input(fallback($this->data['short_description'][$language_code])) ."',
						description = '". database::input(fallback($this->data['description'][$language_code])) ."',
						head_title = '". database::input(fallback($this->data['head_title'][$language_code])) ."',
						h1_title = '". database::input(fallback($this->data['h1_title'][$language_code])) ."',
						meta_description = '". database::input(fallback($this->data['meta_description'][$language_code])) ."',
						link = '". database::input(fallback($this->data['link'][$language_code])) ."'
					where brand_id = ". (int)$this->data['id'] ."
					and language_code = '". database::input($language_code) ."'
					limit 1;"
				);
			}

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
				list($width, $height) = preg_split('#\s*,\s*#', settings::get('image_downsample_size'), -1, PREG_SPLIT_NO_EMPTY);
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
				notices::add('errors', language::translate('error_delete_brand_not_empty_products', 'The brand could not be deleted because there are products linked to it.'));
				header('Location: '. $_SERVER['REQUEST_URI']);
				exit;
			}

			if ($this->data['image'] && is_file('storage://images/brands/' . $this->data['image'])) {
				unlink('storage://images/brands/' . $this->data['image']);
			}

			database::query(
				"delete b, bi
				from ". DB_TABLE_PREFIX ."brands b
				left join ". DB_TABLE_PREFIX ."brands_info bi on (bi.brand_id = b.id)
				where b.id = ". (int)$this->data['id'] .";"
			);

			$this->reset();

			cache::clear_cache('brands');
		}
	}
