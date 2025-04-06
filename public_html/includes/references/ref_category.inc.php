<?php

	class ref_category extends abs_reference_entity {

		protected $_language_codes;

		function __construct($category_id, $language_code=null) {

			if (empty($language_code)) {
				$language_code = language::$selected['code'];
			}

			$this->_data['id'] = (int)$category_id;
			$this->_language_codes = array_unique([
				$language_code,
				settings::get('default_language_code'),
				settings::get('store_language_code'),
			]);
		}

		protected function _load($field) {

			switch($field) {

				case 'parent':

					$this->_data['parent'] = false;

					if (empty($this->parent_id)) return;

					$this->_data['parent'] = reference::category($this->parent_id, $this->_language_codes[0]);

					break;

				case 'main_category':
				case 'ancestor':

					//$this->_data['ancestor'] = $this;

					//while ($this->_data['ancestor']->parent_id) {
					//	$this->_data['ancestor'] = $this->_data['ancestor']->parent;
					//}

					//$this->_data['main_category'] = &$this->_data['ancestor'];

					$this->_data['ancestor'] = database::query(
						"select t2.id from (
							select @r as _id,
								(select @r := parent_id from ". DB_TABLE_PREFIX ."categories where id = _id) as parent_id,
								@l := @l + 1
							from (
								select @r := ". (int)$this->id .", @l := 0) vars,
								". DB_TABLE_PREFIX ."categories h
								where @r <> 0
							) t1
							join  ". DB_TABLE_PREFIX ."categories t2 on (t1._id = t2.id)
						limit 1;"
					)->fetch(function($category){
						return reference::category($category['id'], $this->_language_codes[0]);
					});

					$this->_data['main_category'] = &$this->_data['ancestor'];

					break;

				case 'path':

					$this->_data['path'] = [$this->_data['id'] => $this];

					$current = $this;
					while ($current->parent_id) {
						$this->_data['path'] = [$current->parent_id => $current->parent] + $this->_data['path'];
						$current = $current->parent;
					}

					break;

				case 'products':

					$this->_data['products'] = database::query(
						"select id from ". DB_TABLE_PREFIX ."products
						where status
						and id in (
							select product_id from ". DB_TABLE_PREFIX ."products_to_categories
							where category_id = ". (int)$this->_data['id'] ."
						)
						and (quantity > 0 or sold_out_status_id in (
							select id from ". DB_TABLE_PREFIX ."sold_out_statuses
							where (hidden is null or hidden = 0)
						))
						and (date_valid_from is null or date_valid_from <= '". date('Y-m-d H:i:s') ."')
						and (date_valid_to is null or date_valid_to >= '". date('Y-m-d H:i:s') ."');"
					)->fetch_all(function($product){
						return reference::product($product['id'], $this->_language_codes[0]);
					});

					break;

				case 'num_subcategories':

					if (!empty($this->_data['subcategories'])) {
						$this->_data['num_subcategories'] = count($this->_data['subcategories']);
						break;
					}

					$this->_data['num_subcategories'] = database::query(
						"select count(id) as num_subcategories from ". DB_TABLE_PREFIX ."categories
						where status
						and parent_id ". (int)$this->_data['id'] .";"
					)->fetch('num_subcategories');

					break;

				case 'num_products':

					if (!empty($this->_data['products'])) {
						$this->_data['num_products'] = count($this->_data['products']);
						break;
					}

					$this->_data['num_products'] = database::query(
						"select count(id) as num_products from ". DB_TABLE_PREFIX ."products
						where status
						and id in (
							select product_id from ". DB_TABLE_PREFIX ."products_to_categories
							where category_id = ". (int)$this->_data['id'] ."
							". ($this->descendants ? "or category_id in (". implode(", ", array_keys($this->descendants)) .")" : "") ."
						)
						and (quantity > 0 or sold_out_status_id in (
							select id from ". DB_TABLE_PREFIX ."sold_out_statuses
							where (hidden is null or hidden = 0)
						))
						and (date_valid_from is null or date_valid_from <= '". date('Y-m-d H:i:s') ."')
						and (date_valid_to is null or date_valid_to >= '". date('Y-m-d H:i:s') ."');"
					)->fetch('num_products');

					break;

				case 'siblings':

					$this->_data['siblings'] = [];

					if (empty($this->parent_id)) return;

					database::query(
						"select id from ". DB_TABLE_PREFIX ."categories
						where status
						and parent_id = ". (int)$this->parent_id ."
						and id != ". (int)$this->_data['id'] ."
						order by priority;"
					)->each(function($category) {
						$this->_data['siblings'][$category['id']] = reference::category($category['id'], $this->_language_codes[0]);
					});

					break;

				case 'descendants':

					$this->_data['descendants'] = [];

					database::query(
						"select id, parent_id from ". DB_TABLE_PREFIX ."categories
						join (select @parent_id := ". (int)$this->_data['id'] .") tmp
						where find_in_set(parent_id, @parent_id)
						and length(@parent_id := concat(@parent_id, ',', id));"
					)->each(function($category) {
						$this->_data['descendants'][$category['id']] = reference::category($category['id'], $this->_language_codes[0]);
					});

					break;

				case 'subcategories':
				case 'children':

					$this->_data['children'] = [];

					database::query(
						"select id from ". DB_TABLE_PREFIX ."categories
						where status
						and parent_id = ". (int)$this->_data['id'] ."
						order by priority;"
					)->each(function($category) {
						$this->_data['children'][$category['id']] = reference::category($category['id'], $this->_language_codes[0]);
					});

					$this->_data['subcategories'] = &$this->_data['children'];

					break;

				default:

					$category = database::query(
						"select * from ". DB_TABLE_PREFIX ."categories
						where id = ". (int)$this->_data['id'] ."
						limit 1;"
					)->fetch(function($category) {

						foreach ([
							'name',
							'description',
							'short_description',
							'head_title',
							'meta_description',
							'h1_title',
							'synonyms',
						] as $field) {

							$category[$field] = json_decode($category[$field], true) ?: [];

							foreach ($this->_language_codes as $language_code) {
								if (!empty($category[$field][$language_code])) {
									$category[$field] = $category[$field][$language_code];
								}
							}
						}

						$category['keywords'] = preg_split('#\s*,\s*#', $category['keywords'], -1, PREG_SPLIT_NO_EMPTY);

						return $category;
					});

					if (!$category) {
						$category = database::query(
							"show fields from ". DB_TABLE_PREFIX ."categories;"
						)->fetch(function($field) {
							return database::create_variable($field);
						});
					}

					foreach ($category as $key => $value) {
						$this->_data[$key] = $value;
					}

					break;
			}
		}
	}
