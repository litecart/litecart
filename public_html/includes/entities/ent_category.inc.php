<?php

	class ent_category {
		public $data;
		public $previous;

		public function __construct($id=null) {

			if ($id) {
				$this->load($id);
			} else {
				$this->reset();
			}
		}

		public function reset() {

			$this->data = [];

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."categories;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			foreach ([
				'name',
				'short_description',
				'description',
				'head_title',
				'h1_title',
				'meta_description',
				'synonyms',
			] as $column) {
				$this->data[$column] = array_fill_keys(array_keys(language::$languages), '');
			}

			$this->data['filters'] = [];
			$this->data['products'] = [];

			$this->previous = $this->data;
		}

		public function load($id) {

			if (!preg_match('#^[0-9]+$#', $id)) {
				throw new Exception('Invalid category (ID: '. $id .')');
			}

			$this->reset();

			$category = database::query(
				"select * from ". DB_TABLE_PREFIX ."categories
				where id=". (int)$id ."
				limit 1;"
			)->fetch();

			if ($category) {
				$this->data = array_replace($this->data, array_intersect_key($category, $this->data));
			} else {
				throw new Exception('Could not find category (ID: '. (int)$id .') in database.');
			}

			foreach ([
				'name',
				'short_description',
				'description',
				'head_title',
				'h1_title',
				'meta_description',
				'synonyms',
			] as $column) {
				$this->data[$column] = json_decode($this->data[$column], true) ?: [];
			}

			// Filters
			$this->data['filters'] = database::query(
				"select cf.*, json_value(ag.name, '$.". database::input(language::$selected['code']) ."') as attribute_group_name
				from ". DB_TABLE_PREFIX ."categories_filters cf
				left join ". DB_TABLE_PREFIX ."attribute_groups ag on (ag.id = cf.attribute_group_id)
				where cf.category_id = ". (int)$this->data['id'] ."
				order by cf.priority;"
			)->fetch_all();

			// Products
			$this->data['products'] = database::query(
				"select product_id from ". DB_TABLE_PREFIX ."products_to_categories
				where category_id = ". (int)$this->data['id'] ."
				order by product_id;"
			)->fetch_all('product_id');

			$this->previous = $this->data;
		}

		public function save() {

			if (!empty($this->data['id']) && $this->data['parent_id'] == $this->data['id']) {
				throw new Exception(language::translate('error_cannot_attach_category_to_self', 'Cannot attach category to itself'));
			}

			if (!empty($this->data['id']) && !empty($this->data['parent_id']) && in_array($this->data['parent_id'], array_keys(reference::category($this->data['id'])->descendants))) {
				throw new Exception(language::translate('error_cannot_attach_category_to_descendant', 'You cannot attach a category to a descendant'));
			}

			if (!$this->data['id']) {
				database::query(
					"insert into ". DB_TABLE_PREFIX ."categories
					(parent_id, code, created_at)
					values (". (!empty($this->data['parent_id']) ? (int)$this->data['parent_id'] : "null") .", '". database::input($this->data['code']) ."', '". ($this->data['created_at'] = date('Y-m-d H:i:s')) ."');"
				);
				$this->data['id'] = database::insert_id();
			}

			if ($this->data['parent_id'] == $this->data['id']) {
				$this->data['parent_id'] = 0;
			}

			$this->data['keywords'] = explode(',', $this->data['keywords']);
			$this->data['keywords'] = array_map('trim', $this->data['keywords']);
			$this->data['keywords'] = array_unique($this->data['keywords']);
			$this->data['keywords'] = implode(',', $this->data['keywords']);

			database::query(
				"update ". DB_TABLE_PREFIX ."categories
				set parent_id = ". (!empty($this->data['parent_id']) ? (int)$this->data['parent_id'] : "null") .",
					status = ". (int)$this->data['status'] .",
					code = '". database::input($this->data['code']) ."',
					google_taxonomy_id = ". (int)$this->data['google_taxonomy_id'] .",
					name = '". database::input(json_encode($this->data['name'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ."',
					short_description = '". database::input(json_encode($this->data['short_description'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ."',
					description = '". database::input(json_encode($this->data['description'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ."',
					head_title = '". database::input(json_encode($this->data['head_title'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ."',
					h1_title = '". database::input(json_encode($this->data['h1_title'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ."',
					meta_description = '". database::input(json_encode($this->data['meta_description'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ."',
					synonyms = '". database::input(json_encode($this->data['synonyms'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ."',
					keywords = '". database::input($this->data['keywords']) ."',
					priority = ". (int)$this->data['priority'] .",
					updated_at = '". ($this->data['updated_at'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			// Delete filters
			database::query(
				"delete from ". DB_TABLE_PREFIX ."categories_filters
				where category_id = ". (int)$this->data['id'] ."
				and id not in ('". implode("', '", array_column($this->data['filters'], 'id')) ."');"
			);

			// Update filters
			$priority = 1;
			foreach ($this->data['filters'] as $key => $filter) {
				if (empty($filter['id'])) {
					database::query(
						"insert into ". DB_TABLE_PREFIX ."categories_filters
						(category_id, attribute_group_id)
						values (". (int)$this->data['id'] .", ". (int)$filter['attribute_group_id'] .");"
					);
					$this->data['filters'][$key]['id'] = $filter['id'] = database::insert_id();
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."categories_filters
					set attribute_group_id = '". database::input($filter['attribute_group_id']) ."',
						select_multiple = ". (!empty($filter['select_multiple']) ? 1 : 0) .",
						priority = ". (int)$priority++ ."
					where category_id = ". (int)$this->data['id'] ."
					and id = ". (int)$filter['id'] ."
					limit 1;"
				);
			}

			// Delete product mountpoints
			database::query(
				"delete from ". DB_TABLE_PREFIX ."products_to_categories
				where category_id = ". (int)$this->data['id'] ."
				and product_id not in ('". implode("', '", $this->data['products']) ."');"
			);

			// Insert product mountpoints
			foreach ($this->data['products'] as $product_id) {
				if (empty($filter['id'])) {
					database::query(
						"insert ignore into ". DB_TABLE_PREFIX ."products_to_categories
						(category_id, product_id)
						values (". (int)$this->data['id'] .", ". (int)$product_id .");"
					);
				}
			}

			$this->previous = $this->data;

			cache::clear_cache('category_tree');
			cache::clear_cache('categories');
		}

		public function save_image($file, $filename='') {

			if (!$file) {
				return;
			}

			if (!$this->data['id']) {
				$this->save();
			}

			if (!empty($filename)) {
				$filename = 'categories/'. $filename;
			} else {
				$filename = 'categories/'. $this->data['id'] .'-'. functions::format_path_friendly($this->data['name'][settings::get('store_language_code')], settings::get('store_language_code')) .'.'. $image->type;
			}

			if (!is_dir('storage://images/categories/')) {
				mkdir('storage://images/categories/', 0777);
			}

			if (is_file('storage://images/' . $filename)) {
				unlink('storage://images/' . $filename);
			}

			$image = new ent_image($file);

			if (settings::get('image_downsample_size')) {
				list($width, $height) = explode(',', settings::get('image_downsample_size'));
				$image->resample($width, $height, 'FIT_ONLY_BIGGER');
			}

			if (!$image->save('storage://images/' . $filename, 90)) {
				throw new Exception('Failed saving image');
			}

			functions::image_delete_cache('storage://images/' . $filename);

			database::query(
				"update ". DB_TABLE_PREFIX ."categories
				set image = '". database::input($filename) ."'
				where id = ". (int)$this->data['id'] .";"
			);

			$this->previous['image'] = $this->data['image'] = $filename;
		}

		public function delete_image() {

			if (empty($this->data['image'])) return;

			if (is_file('storage://images/' . $this->data['image'])) {
				unlink('storage://images/' . $this->data['image']);
			}

			functions::image_delete_cache('storage://images/' . $this->data['image']);

			database::query(
				"update ". DB_TABLE_PREFIX ."categories
				set image = ''
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

		 $this->previous['image'] = $this->data['image'] = '';
		}

		public function delete() {

			if (!$this->data['id']) return;

			// Delete subcategories
			database::query(
				"select id from ". DB_TABLE_PREFIX ."categories
				where parent_id = ". (int)$this->data['id'] .";"
			)->each(function($subcategory) {
				$subcategory = new ent_category($subcategory['id']);
				$subcategory->delete();
			});

			// Delete products
			foreach ($this->data['products'] as $product_id) {
				$product = new ent_product($product_id);

				if (($key = array_search($id, $product->data['categories'])) !== false) {
					unset($product->data['categories'][$key]);
				}

				if (empty($product->data['categories'])) {
					$product->delete();
				} else {
					$product->save();
				}
			}

			database::query(
				"delete c, cf, ptc
				from ". DB_TABLE_PREFIX ."categories c
				left join ". DB_TABLE_PREFIX ."categories_filters cf on (cf.category_id = c.id)
				left join ". DB_TABLE_PREFIX ."products_to_categories ptc on (ptc.category_id = c.id)
				where c.id = ". (int)$this->data['id'] .";"
			);

			$this->reset();

			cache::clear_cache('category_tree');
			cache::clear_cache('categories');
		}
	}
