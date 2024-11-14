<?php

	class ent_product {
		public $data;
		public $previous;

		public function __construct($product_id=null) {

			if (!empty($product_id)) {
				$this->load($product_id);
			} else {
				$this->reset();
			}
		}

		public function reset() {

			$this->data = [];

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."products;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."products_info;"
			)->each(function($field){
				if (in_array($field['Field'], ['id', 'product_id', 'language_code'])) return;
				$this->data[$field['Field']] = array_fill_keys(array_keys(language::$languages), database::create_variable($field));
			});

			$this->data['categories'] = [];
			$this->data['images'] = [];
			$this->data['prices'] = [];
			$this->data['campaigns'] = [];
			$this->data['attributes'] = [];
			$this->data['customizations'] = [];
			$this->data['stock_options'] = [];

			$this->data['status'] = 1;
			$this->data['tax_class_id'] = settings::get('default_tax_class_id');
			$this->data['purchase_price_currency_code'] = settings::get('store_currency_code');
			$this->data['quantity_unit_id'] = settings::get('default_quantity_unit_id');
			$this->data['delivery_status_id'] = settings::get('default_delivery_status_id');
			$this->data['sold_out_status_id'] = settings::get('default_sold_out_status_id');
			$this->data['quantity_min'] = 1;
			$this->data['quantity_available'] = 0;
			$this->data['quantity_reserved'] = 0;

			$this->previous = $this->data;
		}

		public function load($product_id) {

			if (!$product_id) {
				throw new Exception('Invalid product (ID: n/a)');
			}

			$this->reset();

			// Product
			$product = database::query(
				"select * from ". DB_TABLE_PREFIX ."products
				where ". (preg_match('#^[0-9]+$#', $product_id) ? "id = ". (int)$product_id : "code = '". database::input($product_id) ."'") ."
				limit 1;"
			)->fetch();

			if ($product) {
				$this->data = array_replace($this->data, array_intersect_key($product, $this->data));
			} else {
				throw new Exception('Could not find product (ID: '. (int)$product_id .') in database.');
			}

			// Categories
			$this->data['categories'] = database::query(
				"select category_id from ". DB_TABLE_PREFIX ."products_to_categories
				 where product_id = ". (int)$product_id .";"
			)->fetch_all('category_id');

			// Info
			database::query(
				"select * from ". DB_TABLE_PREFIX ."products_info
				where product_id = ". (int)$product_id .";"
			)->each(function($info){
				foreach ($info as $key => $value) {
					if (in_array($key, ['id', 'product_id', 'language_code'])) continue;
					$this->data[$key][$info['language_code']] = $value;
				}
			});

			// Prices
			database::query(
				"select * from ". DB_TABLE_PREFIX ."products_prices
				where product_id = ". (int)$this->data['id'] .";"
			)->each(function($price){
				foreach (array_keys(currency::$currencies) as $currency_code) {
					$this->data['prices'][$currency_code] = $price[$currency_code];
				}
			});

			// Campaigns
			$this->data['campaigns'] = database::query(
				"select cp.*, c.name, c.date_valid_from, c.date_valid_to
				from ". DB_TABLE_PREFIX ."campaigns_products cp
				left join ". DB_TABLE_PREFIX ."campaigns c on (c.id = cp.campaign_id)
				where cp.product_id = ". (int)$this->data['id'] ."
				order by c.date_valid_from;"
			)->fetch_all();

			// Images
			$this->data['images'] = database::query(
				"select * from ". DB_TABLE_PREFIX ."products_images
				where product_id = ". (int)$this->data['id'] ."
				order by priority asc, id asc;"
			)->fetch_all();

			// Attributes
			$this->data['attributes'] = database::query(
				"select pa.*, agi.name as group_name, avi.name as value_name
				from ". DB_TABLE_PREFIX ."products_attributes pa
				left join ". DB_TABLE_PREFIX ."attribute_groups_info agi on (agi.group_id = pa.group_id and agi.language_code = '". database::input(language::$selected['code']) ."')
				left join ". DB_TABLE_PREFIX ."attribute_values_info avi on (avi.value_id = pa.value_id and avi.language_code = '". database::input(language::$selected['code']) ."')
				where product_id = ". (int)$product_id ."
				order by priority, group_name, value_name, custom_value;"
			)->fetch_all();

			// Stock Options
			$this->data['stock_options'] = database::query(
				"select si.*, pso.*, sii.name, ifnull(oi.reserved, 0) as quantity_reserved, si.quantity - ifnull(oi.reserved, 0) as quantity_available
				from ". DB_TABLE_PREFIX ."products_stock_options pso
				left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = pso.stock_item_id)
				left join ". DB_TABLE_PREFIX ."stock_items_info sii on (sii.stock_item_id = pso.stock_item_id and sii.language_code = '". database::input(language::$selected['code']) ."')
				left join (
					select product_id, stock_option_id, sum(quantity) as reserved
					from ". DB_TABLE_PREFIX ."orders_items
					where order_id in (
						select id from ". DB_TABLE_PREFIX ."orders
						where order_status_id in (
							select id from ". DB_TABLE_PREFIX ."order_statuses
							where stock_action = 'reserve'
						)
					)
					group by stock_option_id
				) oi on (oi.product_id = pso.product_id and oi.stock_option_id = pso.id)
				where pso.product_id = ". (int)$this->data['id'] ."
				order by pso.priority;"
			)->fetch_all();

			$this->previous = $this->data;
		}

		public function save() {

			if (!$this->data['id']) {

				database::query(
					"insert into ". DB_TABLE_PREFIX ."products
					(date_created)
					values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			$this->data['categories'] = array_map('trim', $this->data['categories']);
			$this->data['categories'] = array_filter($this->data['categories'], function($var) { return ($var != ''); }); // Don't filter root ('0')
			$this->data['categories'] = array_unique($this->data['categories']);

			$this->data['keywords'] = preg_split('#\s*,\s*#', $this->data['keywords'], -1, PREG_SPLIT_NO_EMPTY);
			$this->data['keywords'] = array_map('trim', $this->data['keywords']);
			$this->data['keywords'] = array_filter($this->data['keywords']);
			$this->data['keywords'] = array_unique($this->data['keywords']);
			$this->data['keywords'] = implode(',', $this->data['keywords']);

			foreach (array_keys($this->data['synonyms']) as $language_code) {
				$this->data['synonyms'][$language_code] = preg_split('#\s*,\s*#', $this->data['synonyms'][$language_code], -1, PREG_SPLIT_NO_EMPTY);
				$this->data['synonyms'][$language_code] = array_map('trim', $this->data['synonyms'][$language_code]);
				$this->data['synonyms'][$language_code] = array_filter($this->data['synonyms'][$language_code]);
				$this->data['synonyms'][$language_code] = array_unique($this->data['synonyms'][$language_code]);
				$this->data['synonyms'][$language_code] = implode(',', $this->data['synonyms'][$language_code]);
			}

			if (empty($this->data['default_category_id']) || !in_array($this->data['default_category_id'], $this->data['categories'])) {
				$this->data['default_category_id'] = reset($this->data['categories']);
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."products
				set status = ". (int)$this->data['status'] .",
					brand_id = ". (int)$this->data['brand_id'] .",
					supplier_id = ". (int)$this->data['supplier_id'] .",
					delivery_status_id = ". (int)$this->data['delivery_status_id'] .",
					sold_out_status_id = ". (int)$this->data['sold_out_status_id'] .",
					default_category_id = ". (int)$this->data['default_category_id'] .",
					keywords = '". database::input($this->data['keywords']) ."',
					quantity_min = ". (float)$this->data['quantity_min'] .",
					quantity_max = ". (float)$this->data['quantity_max'] .",
					quantity_step = ". (float)$this->data['quantity_step'] .",
					quantity_unit_id = ". (int)$this->data['quantity_unit_id'] .",
					purchase_price = ". (float)$this->data['purchase_price'] .",
					purchase_price_currency_code = '". database::input($this->data['purchase_price_currency_code']) ."',
					recommended_price = ". (float)$this->data['recommended_price'] .",
					tax_class_id = ". (int)$this->data['tax_class_id'] .",
					code = '". database::input($this->data['code']) ."',
					sku = '". database::input($this->data['sku']) ."',
					mpn = '". database::input($this->data['mpn']) ."',
					gtin = '". database::input($this->data['gtin']) ."',
					taric = '". database::input($this->data['taric']) ."',
					length = ". (float)$this->data['length'] .",
					width = ". (float)$this->data['width'] .",
					height = ". (float)$this->data['height'] .",
					length_unit = '". database::input($this->data['length_unit']) ."',
					weight = ". (float)$this->data['weight'] .",
					weight_unit = '". database::input($this->data['weight_unit']) ."',
					autofill_technical_data = ". (int)$this->data['autofill_technical_data'] .",
					date_valid_from = ". (empty($this->data['date_valid_from']) ? "null" : "'". date('Y-m-d H:i:s', strtotime($this->data['date_valid_from'])) ."'") .",
					date_valid_to = ". (empty($this->data['date_valid_to']) ? "null" : "'". date('Y-m-d H:i:s', strtotime($this->data['date_valid_to'])) ."'") .",
					date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			// Categories
			database::query(
				"delete from ". DB_TABLE_PREFIX ."products_to_categories
				where product_id = ". (int)$this->data['id'] ."
				and category_id not in ('". implode("', '", database::input($this->data['categories'])) ."');"
			);

			foreach ($this->data['categories'] as $category_id) {
				if (in_array($category_id, $this->previous['categories'])) continue;
				database::query(
					"insert into ". DB_TABLE_PREFIX ."products_to_categories
					(product_id, category_id)
					values (". (int)$this->data['id'] .", ". (int)$category_id .");"
				);
			}

			// Info
			foreach (array_keys(language::$languages) as $language_code) {
				$products_info_query = database::query(
					"select * from ". DB_TABLE_PREFIX ."products_info
					where product_id = ". (int)$this->data['id'] ."
					and language_code = '". database::input($language_code) ."'
					limit 1;"
				);

				if (!$product_info = database::fetch($products_info_query)) {
					database::query(
						"insert into ". DB_TABLE_PREFIX ."products_info
						(product_id, language_code)
						values (". (int)$this->data['id'] .", '". database::input($language_code) ."');"
					);
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."products_info
					set name = '". database::input($this->data['name'][$language_code]) ."',
						short_description = '". database::input($this->data['short_description'][$language_code]) ."',
						description = '". database::input($this->data['description'][$language_code], true) ."',
						technical_data = '". database::input($this->data['technical_data'][$language_code], true) ."',
						synonyms = '". database::input($this->data['synonyms'][$language_code]) ."',
						head_title = '". database::input($this->data['head_title'][$language_code]) ."',
						meta_description = '". database::input($this->data['meta_description'][$language_code]) ."'
					where product_id = ". (int)$this->data['id'] ."
					and language_code = '". database::input($language_code) ."'
					limit 1;"
				);
			}

			// Attributes
			database::query(
				"delete from ". DB_TABLE_PREFIX ."products_attributes
				where product_id = ". (int)$this->data['id'] ."
				and id not in ('". implode("', '", array_column($this->data['attributes'], 'id')) ."');"
			);

			$i = 0;
			foreach ($this->data['attributes'] as $key => $attribute) {
				if (empty($attribute['id'])) {
					database::query(
						"insert into ". DB_TABLE_PREFIX ."products_attributes
						(product_id, group_id, value_id, custom_value)
						values (". (int)$this->data['id'] .", ". (int)$attribute['group_id'] .", ". (int)$attribute['value_id'] .", '". database::input($attribute['custom_value']) ."');"
					);
					$this->data['attributes'][$key]['id'] = $attribute['id'] = database::insert_id();
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."products_attributes
					set group_id = ". (int)$attribute['group_id'] .",
						value_id = ". (int)$attribute['value_id'] .",
						custom_value = '". database::input($attribute['custom_value']) ."',
						priority = ". (int)++$i ."
					where product_id = ". (int)$this->data['id'] ."
					and id = ". (int)$attribute['id'] ."
					limit 1;"
				);
			}

			// Prices
			foreach (array_keys(currency::$currencies) as $currency_code) {

				if (!database::query(
					"select * from ". DB_TABLE_PREFIX ."products_prices
					where product_id = ". (int)$this->data['id'] ."
					limit 1;"
				)->num_rows) {
					database::query(
						"insert into ". DB_TABLE_PREFIX ."products_prices
						(product_id)
						values (". (int)$this->data['id'] .");"
					);
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."products_prices
					set ". implode(',' . PHP_EOL, array_map(function($currency) {
						return "`". database::input($currency['code']) ."` = ". (isset($this->data['prices'][$currency['code']]) ? (float)$this->data['prices'][$currency['code']] : 0);
					}, currency::$currencies)) ."
					where product_id = ". (int)$this->data['id'] ."
					limit 1;"
				);
			}

			// Delete campaigns
			database::query(
				"delete from ". DB_TABLE_PREFIX ."campaigns_products
				where product_id = ". (int)$this->data['id'] ."
				and id not in ('". implode("', '", array_column($this->data['campaigns'], 'campaign_id')) ."');"
			);

			// Update campaigns
			foreach ($this->data['campaigns'] as $key => $campaign) {

				if (empty($campaign['id'])) {

					database::query(
						"insert into ". DB_TABLE_PREFIX ."campaigns_products
						(campaign_id, product_id)
						values (". (int)$this->data['campaign_id'] ."". (int)$this->data['id'] .");"
					);

					$this->data['campaigns'][$key]['id'] = $campaign['id'] = database::insert_id();
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."products_campaigns
					set ". implode("," . PHP_EOL, array_map(function($currency) use ($campaign) {
						return "`". database::input($currency['code']) ."` = ". (isset($campaign[$currency['code']]) ? (float)$campaign[$currency['code']] : 0);
					}, currency::$currencies)) ."
					where product_id = ". (int)$this->data['id'] ."
					and id = ". (int)$campaign['campaign_id'] ."
					limit 1;"
				);
			}

			// Delete images
			database::query(
				"select * from ". DB_TABLE_PREFIX ."products_images
				where product_id = ". (int)$this->data['id'] ."
				and id not in ('". implode("', '", array_column($this->data['images'], 'id')) ."');"
			)->each(function($image){

				if (is_file('storage://images/' . $image['filename'])) {
					unlink('storage://images/' . $image['filename']);
				}

				functions::image_delete_cache('storage://images/' . $image['filename']);

				database::query(
					"delete from ". DB_TABLE_PREFIX ."products_images
					where product_id = ". (int)$this->data['id'] ."
					and id = ". (int)$image['id'] ."
					limit 1;"
				);
			});

			// Update images
			if (!empty($this->data['images'])) {
				$image_priority = 1;

				foreach ($this->data['images'] as $key => $image) {

					if (empty($image['id'])) continue;
					if (empty($image['new_filename'])) continue;

					if ($this->data['images'][$key]['new_filename'] != $image['filename']) {

						if (is_file('storage://images/' . $image['new_filename'])) {
							throw new Exception('Cannot rename '. $this->data['images'][$key]['filename'] .' to '. $this->data['images'][$key]['filename'] .' as the new filename already exists');
						}

						rename('storage://images/' . $image['filename'], FS_DIR_STORAGE . 'images/' . $image['new_filename']);
						$this->data['images'][$key]['filename'] = $image['new_filename'];

						functions::image_delete_cache('storage://images/' . $image['filename']);
						functions::image_delete_cache('storage://images/' . $image['new_filename']);
					}

					database::query(
						"update ". DB_TABLE_PREFIX ."products_images
						set filename = '". database::input($image['filename']) ."',
							priority = ". (int)$image_priority++ ."
						where product_id = ". (int)$this->data['id'] ."
						and id = ". (int)$image['id'] ."
						limit 1;"
					);
				}
			}

			// Set main product image
			$this->data['image'] = !empty($this->data['images']) ? array_values($this->data['images'])[0]['filename'] : '';

			database::query(
				"update ". DB_TABLE_PREFIX ."products
				set image = '". database::input($this->data['image']) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			// Delete customizations
			database::query(
				"delete from ". DB_TABLE_PREFIX ."products_customizations
				where product_id = ". (int)$this->data['id'] ."
				and id not in ('". implode("', '", array_column($this->data['customizations'], 'id')) ."');"
			);

			database::query(
				"delete from ". DB_TABLE_PREFIX ."products_customizations_values
				where product_id = ". (int)$this->data['id'] ."
				and group_id not in ('". implode("', '", array_column($this->data['customizations'], 'group_id')) ."');"
			);

			// Update customizations
			if (!empty($this->data['customizations'])) {

				$i = 0;
				foreach ($this->data['customizations'] as &$option) {

					if (empty($option['id'])) {
						database::query(
							"insert into ". DB_TABLE_PREFIX ."products_customizations
							(product_id, group_id)
							values (". (int)$this->data['id'] .", ". (int)$option['group_id'] .");"
						);
						$option['id'] = database::insert_id();
					}

					database::query(
						"update ". DB_TABLE_PREFIX ."products_customizations set
							group_id = ". (int)$option['group_id'] .",
							`function` = '". database::input($option['function']) ."',
							required = ". (!empty($option['required']) ? 1 : 0) .",
							sort = '". (!empty($option['sort']) ? database::input($option['sort']) : 'alphabetical') ."',
							priority = ". ++$i ."
						where product_id = ". (int)$this->data['id'] ."
						and id = ". (int)$option['id'] ."
						limit 1;"
					);

					// Delete option values
					database::query(
						"delete from ". DB_TABLE_PREFIX ."products_customizations_values
						where product_id = ". (int)$this->data['id'] ."
						and group_id = ". (int)$option['group_id'] ."
						and id not in ('". implode("', '", !empty($option['values']) ? array_column($option['values'], 'id') : []) ."');"
					);

					// Update option values
					if (!empty($option['values'])) {

						$j = 0;
						foreach ($option['values'] as &$value) {

							if (empty($value['id'])) {

								database::query(
									"insert into ". DB_TABLE_PREFIX ."products_customizations_values
									(product_id, group_id, value_id)
									values (". (int)$this->data['id'] .", ". (int)$option['group_id'] .", ". (int)$value['value_id'] .");"
								);

								$value['id'] = database::insert_id();
							}

							$sql_currencies = "";
							foreach (array_keys(currency::$currencies) as $currency_code) {
								$sql_currencies .= $currency_code ." = '". (isset($value[$currency_code]) ? (float)$value[$currency_code] : 0) ."', ";
							}

							database::query(
								"update ". DB_TABLE_PREFIX ."products_customizations_values set
									group_id = ". (int)$option['group_id'] .",
									value_id = ". (int)$value['value_id'] .",
									custom_value = '". database::input($value['custom_value']) ."',
									price_operator = '". database::input($value['price_operator']) ."',
									$sql_currencies
									priority = ". ++$j ."
								where product_id = ". (int)$this->data['id'] ."
								and group_id = ". (int)$option['group_id'] ."
								and id = ". (int)$value['id'] ."
								limit 1;"
							);
						} unset($value);
					}
				} unset($option);
			}

			// Delete stock options
			database::query(
				"delete from ". DB_TABLE_PREFIX ."products_stock_options
				where product_id = ". (int)$this->data['id'] ."
				and id not in ('". implode("', '", array_column($this->data['stock_options'], 'id')) ."');"
			);

			// Update stock options
			if (!empty($this->data['stock_options'])) {

				$i = 0;
				foreach ($this->data['stock_options'] as $key => $stock_option) {

					if (empty($stock_option['id'])) {

						database::query(
							"insert into ". DB_TABLE_PREFIX ."products_stock_options
							(product_id, stock_item_id)
							values (". (int)$this->data['id'] .", ". (int)$stock_option['stock_item_id'] .");"
						);

						$stock_option['id'] = $this->data['stock_options'][$key]['id'] = database::insert_id();
					}

					$sql_currency_prices = implode(",".PHP_EOL, array_map(function($currency) use ($stock_option) {
						return "`". database::input($currency['code']) ."` = ". (isset($stock_option[$currency['code']]) ? (float)$stock_option[$currency['code']] : 0);
					}, currency::$currencies));

					database::query(
						"update ". DB_TABLE_PREFIX ."products_stock_options
						set stock_item_id = ". (int)$stock_option['stock_item_id'] .",
							price_operator = '". database::input($stock_option['price_operator']) ."',
							$sql_currency_prices,
							priority = ". (int)$i++ ."
						where id = ". (int)$stock_option['id'] ."
						and product_id = ". (int)$this->data['id'] ."
						limit 1;"
					);

					// Update stock item
					$ent_stock_item = new ent_stock_item($stock_option['stock_item_id']);
					$ent_stock_item->data['sku'] = $stock_option['sku'];
					$ent_stock_item->data['quantity_adjust'] = $stock_option['quantity_adjustment'];
					$ent_stock_item->data['backordered'] = $stock_option['backordered'];
					$ent_stock_item->save();
				}
			}

			$this->previous = $this->data;

			cache::clear_cache('brands');
			cache::clear_cache('categories');
			cache::clear_cache('products');
		}

		public function add_image($file, $filename='') {

			if (!$file) {
				throw new Exception('Missing image');
			};

			$checksum = md5_file($file);
			if (in_array($checksum, array_column($this->data['images'], 'checksum'))) return false;

			if (!empty($filename)) {
				$filename = 'products/' . $filename;
			}

			if (!$this->data['id']) {
				$this->save();
			}

			if (!is_dir('storage://images/products/')) {
				mkdir('storage://images/products/', 0777);
			}

			if (!$image = new ent_image($file)) {
				throw new Exception('Failed decoding image');
			}

			// 456-Fancy-product-title-N.jpg
			$i=1;
			while (empty($filename) || is_file('storage://images/' . $filename)) {
				$filename = 'products/' . $this->data['id'] .'-'. functions::format_path_friendly($this->data['name'][settings::get('store_language_code')], settings::get('store_language_code')) .'-'. $i++ .'.'. $image->type;
			}

			$priority = count($this->data['images'])+1;

			if (settings::get('image_downsample_size')) {
				list($width, $height) = explode(',', settings::get('image_downsample_size'));
				$image->resample($width, $height, 'FIT_ONLY_BIGGER');
			}

			if (!$image->save('storage://images/' . $filename, 90)) return false;

			functions::image_delete_cache('storage://images/' . $filename);

			database::query(
				"insert into ". DB_TABLE_PREFIX ."products_images
				(product_id, filename, checksum, priority)
				values (". (int)$this->data['id'] .", '". database::input($filename) ."', '". database::input($checksum) ."', ". (int)$priority .");"
			);

			$image_id = database::insert_id();

			$row = [
				'id' => $image_id,
				'filename' => $filename,
				'checksum' => $checksum,
				'priority' => $priority,
			];

			$this->data['images'][] = $row;
			$this->previous['images'][] = $row;
		}

		public function delete() {

			if (!$this->data['id']) return;

			// Delete images
			$this->data['images'] = [];
			$this->save();

			database::query(
				"delete p, cp, ci, pi, pa, pp, pcu, pso, ptc
				from ". DB_TABLE_PREFIX ."products p
				left join ". DB_TABLE_PREFIX ."campaigns_products cp on (cp.product_id = p.id)
				left join ". DB_TABLE_PREFIX ."cart_items ci on (ci.product_id = p.id)
				left join ". DB_TABLE_PREFIX ."products_info pi on (pi.id = p.id)
				left join ". DB_TABLE_PREFIX ."products_attributes pa on (pa.product_id = p.id)
				left join ". DB_TABLE_PREFIX ."products_prices pp on (pp.product_id = p.id)
				left join ". DB_TABLE_PREFIX ."products_customizations pcu on (pcu.product_id = p.id)
				left join ". DB_TABLE_PREFIX ."products_stock_options pso on (pso.product_id = p.id)
				left join ". DB_TABLE_PREFIX ."products_to_categories ptc on (ptc.product_id = p.id)
				where p.id = ". (int)$this->data['id'] .";"
			);

			$this->reset();

			cache::clear_cache('brands');
			cache::clear_cache('category');
			cache::clear_cache('products');
		}
	}
