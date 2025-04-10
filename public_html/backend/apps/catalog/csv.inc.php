<?php

	document::$title[] = language::translate('title_import_export_csv', 'Import/Export CSV');

	breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
	breadcrumbs::add(language::translate('title_import_export_csv', 'Import/Export CSV'), document::ilink());

	if (isset($_POST['import']) || isset($_GET['resume'])) {

		try {

			ini_set('memory_limit', -1);

			ob_clean();

			header('Content-Type: text/plain; charset='. mb_http_output());

			if (isset($_GET['resume'])) {

				if (empty(session::$data['csv_batch'])) {
					throw new Exception('Missing batch to resume');
				}

				$batch = &session::$data['csv_batch'];

				$progress = round(($batch['total_lines'] - count($batch['rows'])) / $batch['total_lines'] * 100, 2, PHP_ROUND_HALF_DOWN);
				$time_elapsed = round(microtime(true) - $batch['time_start'], 2);
				$time_remaining = round($time_elapsed / $progress * 100, 2) - $time_elapsed;
				$memory_usage = round(memory_get_usage() / 1024 / 1024, 3);

				echo implode(PHP_EOL, [
					functions::draw_progress_bar($progress, 15),
					'Estimated time remaining: '. $time_remaining .' s',
					'Memory usage: '. $memory_usage .' MB',
					'',
					'',
				]);

			} else {

				if (empty($_POST['type'])) {
					throw new Exception(language::translate('error_must_select_type', 'You must select type'));
				}

				if (!isset($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
					throw new Exception(language::translate('error_must_select_file_to_upload', 'You must select a file to upload'));
				}

				if (!empty($_FILES['file']['error'])) {
					throw new Exception(language::translate('error_uploaded_file_rejected', 'An uploaded file was rejected for unknown reason'));
				}

				$csv = file_get_contents($_FILES['file']['tmp_name']);

				if (!$csv = functions::csv_decode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'])) {
					throw new Exception(language::translate('error_failed_decoding_csv', 'Failed decoding CSV'));
				}

				if (!empty($_POST['reset'])) {

					echo PHP_EOL
						 . 'Wiping data...' . PHP_EOL . PHP_EOL;

					switch ($_POST['type']) {

						case 'attributes':

							database::multi_query(implode(PHP_EOL, [
								"truncate ". DB_TABLE_PREFIX ."attribute_groups;",
								"truncate ". DB_TABLE_PREFIX ."attribute_values;",
							]));

							break;

						case 'campaigns':

							database::multi_query(implode(PHP_EOL, [
								"truncate ". DB_TABLE_PREFIX ."campaigns;",
							]));

							break;

						case 'categories':

							database::multi_query(implode(PHP_EOL, [
								"truncate ". DB_TABLE_PREFIX ."categories;",
								"truncate ". DB_TABLE_PREFIX ."categories_filters;",
								"truncate ". DB_TABLE_PREFIX ."products_to_categories;",
							]));

							foreach (functions::file_search('storage://images/categories/*') as $file) {
								if (preg_match('#index\.html$#', $file)) continue;
								functions::file_delete($file);
							}

							break;

						case 'brands':

							database::multi_query(implode(PHP_EOL, [
								"truncate ". DB_TABLE_PREFIX ."brands`;",
							]));

							foreach (functions::file_search('storage://images/brands/*') as $file) {
								if (preg_match('#index\.html$#', $file)) continue;
								functions::file_delete($file);
							}

							break;

						case 'products':

							database::multi_query(implode(PHP_EOL, [
								"truncate ". DB_TABLE_PREFIX ."campaigns_products;",
								"truncate ". DB_TABLE_PREFIX ."cart_items;",
								"truncate ". DB_TABLE_PREFIX ."products;",
								"truncate ". DB_TABLE_PREFIX ."products_attributes;",
								"truncate ". DB_TABLE_PREFIX ."products_images;",
								"truncate ". DB_TABLE_PREFIX ."products_prices;",
								"truncate ". DB_TABLE_PREFIX ."products_to_categories;",
								"truncate ". DB_TABLE_PREFIX ."products_references;",
								"truncate ". DB_TABLE_PREFIX ."products_stock_options;",
								"update ". DB_TABLE_PREFIX ."orders_items set product_id = 0;",
							]));

							foreach (functions::file_search('storage://images/products/*') as $file) {
								if (preg_match('#index\.html$#', $file)) continue;
								functions::file_delete($file);
							}

							break;

						case 'product_prices':

							database::multi_query(implode(PHP_EOL, [
								"truncate ". DB_TABLE_PREFIX ."products_prices;",
							]));

							break;

						case 'product_stock_options':

							database::multi_query(implode(PHP_EOL, [
								"truncate ". DB_TABLE_PREFIX ."product_stock_options;",
							]));

							break;

						case 'suppliers':

							database::multi_query(implode(PHP_EOL, [
								"truncate ". DB_TABLE_PREFIX ."suppliers;",
							]));

							break;

						case 'stock_items':

							database::multi_query(implode(PHP_EOL, [
								"truncate ". DB_TABLE_PREFIX ."products_stock_options;",
								"truncate ". DB_TABLE_PREFIX ."stock_items;",
							]));

							break;
					}
				}

				echo 'Creating a batch of '. count($csv) .' lines for processing' . PHP_EOL . PHP_EOL;

				session::$data['csv_batch'] = [
					'type' => $_POST['type'],
					'time_start' => microtime(true),
					'rows' => $csv,
					'total_lines' => count($csv),
					'insert' => !empty($_POST['insert']),
					'overwrite' => !empty($_POST['overwrite']),
					'counters' => [
						'updated' => 0,
						'inserted' => 0,
						'line' => 0,
					],
				];

				$batch = &session::$data['csv_batch'];
			}

			$time_start = microtime(true);

			ignore_user_abort(true);

			echo 'Processing batch...' . PHP_EOL . PHP_EOL;

			while ($row = array_shift($batch['rows'])) {

				if (round(microtime(true) - $time_start) > 5) {
					array_unshift($batch['rows'], $row);
					echo PHP_EOL . 'Resuming '. number_format(count($batch['rows']), 0, '', ' ') .' remaining lines for processing...' . PHP_EOL . PHP_EOL;
					header('Refresh: 0; url='. document::link(null, ['resume' => 'true']));
					exit;
				}

				if (connection_aborted()) {
					throw new Exception('Connection aborted');
				}

				$batch['counters']['line']++;

				switch ($batch['type']) {

					case 'attributes':

						// Find attribute group
						if (!empty($row['group_id']) && $attribute_group = database::query(
							"select id from ". DB_TABLE_PREFIX ."attribute_groups
							where id = ". (int)$row['group_id'] ."
							limit 1;"
						)->fetch()) {
							$attribute_group = new ent_attribute_group($attribute_group['id']);

						} else if (!empty($row['code']) && $attribute_group = database::query(
							"select id from ". DB_TABLE_PREFIX ."attribute_groups
							where code = '". database::input($row['code']) ."'
							limit 1;"
						)->fetch()) {
							$attribute_group = new ent_attribute_group($attribute_group['id']);

						} else if (!empty($row['group_name']) && $attribute_group = database::query(
							"select group_id as id
							from ". DB_TABLE_PREFIX ."attribute_groups
							where json_value(name, '$.". database::input($row['language_code']) ."') = '". database::input($row['group_name']) ."'
							limit 1;"
						)->fetch()) {
							$attribute_group = new ent_attribute_group($attribute_group['id']);
						}

						if (!empty($attribute_group->data['id'])) {

							if (empty($batch['overwrite'])) {
								echo 'Skip updating existing attribute group on line '. $batch['counters']['line'] . PHP_EOL;
								continue 2;
							}

							echo 'Updating existing attribute group on line '. $batch['counters']['line'] . PHP_EOL;
							$batch['counters']['updated']++;

						} else {

							if (empty($batch['insert'])) {
								echo 'Skip inserting new attribute group on line '. $batch['counters']['line'] . PHP_EOL;
								continue 2;
							}

							echo 'Inserting new attribute group on line '. $batch['counters']['line'] . PHP_EOL;
							$batch['counters']['inserted']++;

							if (!empty($row['group_id'])) {
								database::query(
									"insert into ". DB_TABLE_PREFIX ."attribute_groups
									(id)
									values (". (int)$row['group_id'] .");"
								);
								$attribute_group = new ent_attribute_group($row['group_id']);
							} else {
								$attribute_group = new ent_attribute_group();
							}
						}

						// Set attribute data
						if (isset($row['group_code'])) {
							$attribute_group->data['code'] = $row['group_code'];
						}

						if (isset($row['group_name'])) {
							$attribute_group->data['name'][$row['language_code']] = $row['group_name'];
						}

						if (isset($row['sort'])) {
							$attribute_group->data['sort'] = $row['sort'];
						}

						foreach ($attribute_group->data['values'] as $key => $value) {
							if (!empty($row['value_id']) && $value['id'] == $row['value_id']) {
								$value_key = $key;
								break;
							}

							if (!empty($row['value_name']) && isset($value['name'][$row['language_code']]) && $value['name'][$row['language_code']] == $row['value_name']) {
								$value_key = $key;
								break;
							}
						}

						if (!empty($value_key)) {
							$attribute_group->data['values'][$value_key]['name'][$row['language_code']] = $row['value_name'];
						} else {
							$attribute_group->data['values'][] = [
								'name' => [
									$row['language_code'] => $row['value_name'],
								],
							];
						}

						// Sort values
						uasort($attribute_group->data['values'], function($a, $b) {
							if (!isset($a['priority'])) $a['priority'] = '';
							if (!isset($b['priority'])) $b['priority'] = '';

							if ($a['priority'] == $b['priority']) {
								return ($a['name'] < $b['name']) ? -1 : 1;
							}

							return ($a['priority'] < $b['priority']) ? -1 : 1;
						});

						$attribute_group->save();

						break;

					case 'campaigns':

						// Find campaign
						if (!empty($row['id'])) {
							$campaign = database::query(
								"select id from ". DB_TABLE_PREFIX ."campaigns
								where id = ". (int)$row['id'] ."
								limit 1;"
							)->fetch();
						}

						if (!empty($campaign['id'])) {

							if (empty($batch['overwrite'])) {
								echo 'Skip updating existing campaign on line '. $batch['counters']['line'] . PHP_EOL;
								continue 2;
							}

							echo 'Updating existing campaign on line '. $batch['counters']['line'] . PHP_EOL;
							$batch['counters']['updated']++;

						} else {

							if (empty($batch['insert'])) {
								echo 'Skip inserting new campaign on line '. $batch['counters']['line'] . PHP_EOL;
								continue 2;
							}

							echo 'Inserting new campaign on line '. $batch['counters']['line'] . PHP_EOL;
							$batch['counters']['inserted']++;

							if (!empty($row['id'])) {
								database::query(
									"insert into ". DB_TABLE_PREFIX ."campaigns
									(id, product_id)
									values (". (int)$row['id'] .", '". $row['product_id'] ."');"
								);
							}
						}

						database::query(
							"insert into ". DB_TABLE_PREFIX ."campaigns_products
							(campaign_id, product_id, price)
							on duplicate key update price = '". database::input($row['price']) ."';"
						);

						break;

					case 'categories':

						// Find category
						if (!empty($row['id']) && $category = database::query(
							"select id from ". DB_TABLE_PREFIX ."categories
							where id = ". (int)$row['id'] ."
							limit 1;"
						)->fetch()) {
							$category = new ent_category($category['id']);

						} elseif (!empty($row['code']) && $category = database::query(
							"select id from ". DB_TABLE_PREFIX ."categories
							where code = '". database::input($row['code']) ."'
							limit 1;"
						)->fetch()) {
							$category = new ent_category($category['id']);

						}

						if (!empty($category->data['id'])) {

							if (empty($batch['overwrite'])) {
								echo 'Skip updating existing category on line '. $batch['counters']['line'] . PHP_EOL;
								continue 2;
							}

							echo 'Updating existing category '. fallback($row['name'], 'on line '. $batch['counters']['line']) . PHP_EOL;
							$batch['counters']['updated']++;

						} else {

							if (empty($batch['insert'])) {
								echo 'Skip inserting new category on line '. $batch['counters']['line'] . PHP_EOL;
								continue 2;
							}

							echo 'Inserting new category: '. fallback($row['name'], 'on line '. $batch['counters']['line']) . PHP_EOL;
							$batch['counters']['inserted']++;

							if (!empty($row['id'])) {
								database::query(
									"insert into ". DB_TABLE_PREFIX ."categories (id, date_created)
									values (". (int)$row['id'] .", '". date('Y-m-d H:i:s') ."');"
								);
								$category = new ent_category($row['id']);
							} else {
								$category = new ent_category();
							}
						}

						if (empty($row['parent_id']) && !empty($row['parent_code'])) {
							$row['parent_id'] = database::query(
								"select id from ". DB_TABLE_PREFIX ."categories
								where code = '". database::input($row['parent_code']) ."'
								limit 1;"
							)->fetch('id');
						}

						// Set new category data
						foreach ([
							'parent_id',
							'status',
							'code',
							'keywords',
							'image',
							'priority',
						] as $field) {
							if (isset($row[$field])) {
								$category->data[$field] = $row[$field];
							}
						}

						// Set category info data
						if (!empty($row['language_code'])) {

							foreach ([
								'name',
								'short_description',
								'description',
								'synonyms',
								'head_title',
								'h1_title',
								'meta_description',
							] as $field) {
								if (isset($row[$field])) {
									$category->data[$field][$row['language_code']] = $row[$field];
								}
							}
						}

						if (!empty($row['new_image'])) {
							$category->save_image($row['new_image']);
						}

						$category->save();

						if (!empty($row['date_created'])) {
							database::query(
								"update ". DB_TABLE_PREFIX ."categories
								set date_created = '". date('Y-m-d H:i:s', strtotime($row['date_created'])) ."'
								where id = ". (int)$category->data['id'] ."
								limit 1;"
							);
						}

						break;

					case 'brands':

						// Find brand
						if (!empty($row['id']) && $brand = database::query(
							"select id from ". DB_TABLE_PREFIX ."brands
							where id = ". (int)$row['id'] ."
							limit 1;"
						)->fetch()) {
							$brand = new ent_brand($brand['id']);

						} else if (!empty($row['code']) && $brand = database::query(
							"select id from ". DB_TABLE_PREFIX ."brands
							where code = '". database::input($row['code']) ."'
							limit 1;"
						)->fetch()) {
							$brand = new ent_brand($brand['id']);

						} else if (!empty($row['name']) && !empty($row['language_code']) && $brand = database::query(
							"select id from ". DB_TABLE_PREFIX ."brands
							where name = '". database::input($row['name']) ."'
							limit 1;"
						)->fetch()) {
							$brand = new ent_brand($brand['id']);
						}

						if (!empty($brand->data['id'])) {

							if (empty($batch['overwrite'])) {
								echo 'Skip updating existing brand on line '. $batch['counters']['line'] . PHP_EOL;
								continue 2;
							}

							echo 'Updating existing brand '. fallback($row['name'], 'on line '. $batch['counters']['line']) . PHP_EOL;
							$batch['counters']['updated']++;

						} else {

							if (empty($_POST['insert'])) {
								echo "Skip inserting new brand on line $line" . PHP_EOL;
								continue 2;
							}

							echo 'Inserting new brand: '. fallback($row['name'], "on line $line") . PHP_EOL;
							$batch['counters']['inserted']++;

							if (!empty($row['id'])) {
								database::query(
									"insert into ". DB_TABLE_PREFIX ."brands (id, date_created)
									values (". (int)$row['id'] .", '". date('Y-m-d H:i:s') ."');"
								);
								$brand = new ent_brand($row['id']);
							} else {
								$brand = new ent_brand();
							}
						}

						// Set new brand data
						foreach ([
							'status',
							'code',
							'name',
							'keywords',
							'image',
							'priority',
						] as $field) {
							if (isset($row[$field])) {
								$brand->data[$field] = $row[$field];
							}
						}

						// Set brand info data
						if (!empty($row['language_code'])) {

							foreach ([
								'short_description',
								'description',
								'head_title',
								'h1_title',
								'meta_description',
							] as $field) {
								if (isset($row[$field])) {
									$brand->data[$field][$row['language_code']] = $row[$field];
								}
							}
						}

						if (!empty($row['new_image'])) {
							$brand->save_image($row['new_image']);
						}

						$brand->save();

						if (!empty($row['date_created'])) {
							database::query(
								"update ". DB_TABLE_PREFIX ."brands
								set date_created = '". date('Y-m-d H:i:s', strtotime($row['date_created'])) ."'
								where id = ". (int)$brand->data['id'] ."
								limit 1;"
							);
						}

						break;

					case 'products':

						// Find product
						if (!empty($row['id']) && $product = database::query(
							"select id from ". DB_TABLE_PREFIX ."products
							where id = ". (int)$row['id'] ."
							limit 1;"
						)->fetch()) {
							$product = new ent_product($product['id']);

						} elseif (!empty($row['code']) && $product = database::query(
							"select id from ". DB_TABLE_PREFIX ."products
							where code = '". database::input($row['code']) ."'
							limit 1;"
						)->fetch()) {
							$product = new ent_product($product['id']);

						} elseif (!empty($row['sku']) && $product = database::query(
							"select id from ". DB_TABLE_PREFIX ."products
							where sku = '". database::input($row['sku']) ."'
							limit 1;"
						)->fetch()) {
							$product = new ent_product($product['id']);

						} elseif (!empty($row['mpn']) && $product = database::query(
							"select id from ". DB_TABLE_PREFIX ."products
							where mpn = '". database::input($row['mpn']) ."'
							limit 1;"
						)->fetch()) {
							$product = new ent_product($product['id']);

						} elseif (!empty($row['gtin']) && $product = database::query(
							"select id from ". DB_TABLE_PREFIX ."products
							where gtin = '". database::input($row['gtin']) ."'
							limit 1;"
						)->fetch()) {
							$product = new ent_product($product['id']);
						}

						if (!empty($product->data['id'])) {

							if (empty($batch['overwrite'])) {
								echo 'Skip updating existing product (ID: '. $product->data['id'] .') on line '. $batch['counters']['line'] . PHP_EOL;
								continue 2;
							}

							echo 'Updating existing product '. fallback($row['name'], 'on line '. $batch['counters']['line']) . PHP_EOL;
							$batch['counters']['updated']++;

						} else {

							if (empty($batch['insert'])) {
								echo 'Skip inserting new product on line '. $batch['counters']['line'] . PHP_EOL;
								continue 2;
							}

							echo 'Inserting new product: '. fallback($row['name'], 'on line '. $batch['counters']['line']) . PHP_EOL;
							$batch['counters']['inserted']++;

							if (!empty($row['id'])) {
								database::query(
									"insert into ". DB_TABLE_PREFIX ."products (id, date_created)
									values (". (int)$row['id'] .", '". date('Y-m-d H:i:s') ."');"
								);
								$product = new ent_product($row['id']);
							} else {
								$product = new ent_product();
							}
						}

						if (empty($row['brand_id']) && !empty($row['brand_name'])) {

							$brand = database::query(
								"select * from ". DB_TABLE_PREFIX ."brands
								where name = '". database::input($row['brand_name']) ."'
								limit 1;"
							)->fetch();

							if ($brand) {
								$row['brand_id'] = $brand['id'];
							} else {
								$brand = new ent_brand();
								$brand->data['name'] = $row['brand_name'];
								$brand->save();
								$row['brand_id'] = $brand->data['id'];
							}
						}

						if (empty($row['supplier_id']) && !empty($row['supplier_id'])) {

							$supplier = database::query(
								"select * from ". DB_TABLE_PREFIX ."suppliers
								where name = '". database::input($row['supplier_name']) ."'
								limit 1;"
							)->fetch();

							if ($supplier) {
								$row['supplier_id'] = $supplier['id'];
							} else {
								$supplier = new ent_supplier();
								$supplier->data['name'] = $row['supplier_name'];
								$supplier->save();
								$row['supplier_id'] = $supplier->data['id'];
							}
						}

						// Set new product data
						foreach ([
							'status',
							'brand_id',
							'default_catgeory_id',
							'supplier_id',
							'code',
							'keywords',
							'tax_class_id',
							'quantity',
							'quantity_min',
							'quantity_max',
							'quantity_step',
							'quantity_unit_id',
							'recommended_price',
							'delivery_status_id',
							'sold_out_status_id',
							'date_valid_from',
							'date_valid_to',
						] as $field) {
							if (isset($row[$field])) {
								$product->data[$field] = $row[$field];
							}
						}

						if (isset($row['categories'])) {
							$product->data['categories'] = preg_split('#\s*,\s*#', $row['categories'], -1, PREG_SPLIT_NO_EMPTY);
						}

						// Set product info data
						if (!empty($row['language_code'])) {

							foreach ([
								'name',
								'short_description',
								'description',
								'technical_data',
								'synonyms',
								'head_title',
								'meta_description',
							] as $field) {
								if (isset($row[$field])) {
									$product->data[$field][$row['language_code']] = $row[$field];
								}
							}
						}

						if (empty($product->data['id'])) {
							$product->save(); // Create product ID as we need it for images
						}

						// Delete images (by reinserting the ones that should stay)
						if (isset($row['images'])) {
							$row['images'] = preg_split('#;#', $row['images'], -1, PREG_SPLIT_NO_EMPTY);

							$product->data['images'] = [];
							foreach ($product->previous['images'] as $key => $image) {
								if (in_array($image['filename'], $row['images'])) {
									$product->data['images'][$key] = $image;
								}
							}

							foreach ($row['images'] as $filename) {
								if (!in_array($filename, array_column($product->data['images'], 'filename'))) {

									if (!is_file(FS_DIR_STORAGE . 'images/' . $filename)) continue;

									$checksum = md5(FS_DIR_STORAGE . 'images/' . $filename);

									database::query(
										"insert into ". DB_TABLE_PREFIX ."products_images
										(product_id, filename, checksum)
										values (". (int)$product->data['id'] .", '". database::input($filename) ."', '". database::input($checksum) ."');"
									);

									$image_id = database::insert_id();

									$product->data['images'][$image_id] = [
										'id' => $image_id,
										'filename' => $filename,
										'checksum' => $checksum,
										'priority' => 0,
									];

								}
							}
						}

						// Import new images
						if (!empty($row['new_images'])) {
							foreach (preg_split('#;#', $row['new_images'], -1, PREG_SPLIT_NO_EMPTY) as $new_image) {
								$product->add_image($new_image);
							}
						}

						// Set attributes
						if (isset($row['attributes'])) {
							$product->data['attributes'] = [];

							foreach (preg_split('#\R+#', $row['attributes'], -1, PREG_SPLIT_NO_EMPTY) as $attribute_row) {

								if (preg_match('#^([0-9]+):([0-9]+)$#', $attribute_row, $matches)) {
									$attribute = [
										'group_id' => $matches[1],
										'value_id' => $matches[2],
										'custom_value' => '',
									];

								} else if (preg_match('#^([0-9]+):"([^"]*)"#', $attribute_row, $matches)) {
									$attribute = [
										'group_id' => $matches[1],
										'value_id' => 0,
										'custom_value' => $matches[2],
									];

								} else {
									echo " - Skipping unknown attribute $attribute_row" . PHP_EOL;
									continue;
								}

								$product->data['attributes'][] = [
									'id' => isset($product->previous['attributes'][$attribute['group_id'].'-'.$attribute['value_id']]) ? $product->previous['attributes'][$attribute['group_id'].'-'.$attribute['value_id']]['id'] : '',
									'group_id' => $attribute['group_id'],
									'value_id' => $attribute['value_id'],
									'custom_value' => $attribute['custom_value'],
								];
							}
						}

						$product->save();

						if (!empty($row['date_created'])) {
							database::query(
								"update ". DB_TABLE_PREFIX ."products
								set date_created = '". date('Y-m-d H:i:s', strtotime($row['date_created'])) ."'
								where id = ". (int)$product->data['id'] ."
								limit 1;"
							);
						}

						break;

					case 'product_prices':

						foreach (['product_id'] as $column) {
							if (empty($row[$column])) {
								throw new Exception("Missing value for mandatory column $column on line $i");
							}
						}

						$product = new ent_product($row['product_id']);

						if (($key = array_search($row['id'], array_combine(array_keys($product->data['prices']), array_column($product->data['prices'], 'id')))) !== false) {

							if (empty($batch['overwrite'])) {
								echo "Skip updating existing price on line $line" . PHP_EOL;
								continue 2;
							}

							echo "Updating existing price on line $line" . PHP_EOL;
							$batch['counters']['updated']++;

						} else {

							if (empty($batch['insert'])) {
								echo "Skip inserting new price on line $line" . PHP_EOL;
								continue 2;
							}

							echo "Inserting new price on line $line" . PHP_EOL;
							$batch['counters']['inserted']++;
						}

						foreach ([
							'product_id',
							'customer_group_id',
							'min_quantity',
						] as $field) {
							if (isset($row[$field])) {
								$price[$field] = $row[$field];
							} else if (!isset($price[$field])) {
								$price[$field] = null;
							}
						}

						$price['price'] = json_decode($price['price'], true);

						$product->data['prices'][$key] = $price;
						$product->save();

						break;

					case 'product_stock_options':

						foreach (['product_id', 'stock_item_id'] as $column) {
							if (empty($row[$column])) {
								throw new Exception("Missing value for mandatory column $column on line $i");
							}
						}

						$product = new ent_product($row['product_id']);

/*
						// Find stock option
						if (!empty($row['id']) && $stock_option = database::query(
							"select id from ". DB_TABLE_PREFIX ."products_stock_options
							where id = ". (int)$row['sku'] ."
							limit 1;"
						)->fetch()) {

						} else if (!empty($row['sku']) && $stock_item = database::query(
							"select pso.id from ". DB_TABLE_PREFIX ."products_stock_options pso
							left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = pso.stock_item_id)
							where pso.product_id = ". (int)$row['product_id'] ."
							and si.sku = '". database::input($row['sku']) ."'
							limit 1;"
						)->fetch()) {
							$stock_option = $stock_item;

						} elseif (!empty($row['mpn']) && $product = database::query(
							"select pso.id from ". DB_TABLE_PREFIX ."products_stock_options pso
							left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = pso.stock_item_id)
							where pso.product_id = ". (int)$row['product_id'] ."
							and si.mpn = '". database::input($row['mpn']) ."'
							limit 1;"
						)->fetch()) {
							$stock_option = $stock_item;

						} elseif (!empty($row['gtin']) && $product = database::query(
							"select pso.id from ". DB_TABLE_PREFIX ."products_stock_options pso
							left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = pso.stock_item_id)
							where pso.product_id = ". (int)$row['product_id'] ."
							and si.gtin = '". database::input($row['gtin']) ."'
							limit 1;"
						)->fetch()) {
							$stock_option = $stock_item;
						}
*/
						if (($key = array_search($row['id'], array_combine(array_keys($product->data['stock_options']), array_column($product->data['stock_options'], 'id')))) !== false) {

							if (empty($batch['overwrite'])) {
								echo "Skip updating existing stock option on line $line" . PHP_EOL;
								continue 2;
							}

							echo 'Updating existing stock item '. fallback($row['name'][$row['language_code']], "on line $line") . PHP_EOL;
							$batch['counters']['updated']++;

						} else {

							if (empty($batch['insert'])) {
								echo "Skip inserting new stock item on line $line" . PHP_EOL;
								continue 2;
							}

							echo 'Inserting new stock item '. fallback($row['name'][$row['language_code']], "on line $line") . PHP_EOL;
							$batch['counters']['inserted']++;
						}

						foreach ([
							'product_id',
							'stock_item_id',
							'price_modifier',
							'priority',
						] as $field) {
							if (isset($row[$field])) {
								$stock_option[$field] = $row[$field];
							} else if (!isset($stock_option[$field])) {
								$stock_option[$field] = null;
							}
						}

						$stock_option['price_adjustment'] = json_decode($stock_option['price_adjustment'], true);

						$product->data['stock_options'][$key] = $stock_option;
						$product->save();

						break;

					case 'stock_items':

						// Find stock_item
						if (!empty($row['id']) && $stock_item = database::query(
							"select id from ". DB_TABLE_PREFIX ."stock_items
							where id = ". (int)$row['id'] ."
							limit 1;"
						)->fetch()) {
							$stock_item = new ent_stock_item($stock_item['id']);

						} elseif (!empty($row['sku']) && $product = database::query(
							"select id from ". DB_TABLE_PREFIX ."stock_items
							where sku = '". database::input($row['sku']) ."'
							limit 1;"
						)->fetch()) {
							$product = new ent_product($product['id']);

						} elseif (!empty($row['mpn']) && $product = database::query(
							"select id from ". DB_TABLE_PREFIX ."stock_items
							where mpn = '". database::input($row['mpn']) ."'
							limit 1;"
						)->fetch()) {
							$product = new ent_product($product['id']);

						} elseif (!empty($row['gtin']) && $product = database::query(
							"select id from ". DB_TABLE_PREFIX ."stock_items
							where gtin = '". database::input($row['gtin']) ."'
							limit 1;"
						)->fetch()) {
							$product = new ent_product($product['id']);
						}

						if (!empty($stock_item->data['id'])) {

							if (empty($batch['overwrite'])) {
								echo "Skip updating existing stock item on line $line" . PHP_EOL;
								continue 2;
							}

							echo 'Updating existing stock item '. fallback($row['name'][$row['language_code']], "on line $line") . PHP_EOL;
							$batch['counters']['updated']++;

						} else {

							if (empty($batch['insert'])) {
								echo "Skip inserting new stock item on line $line" . PHP_EOL;
								continue 2;
							}

							echo 'Inserting new stock item: '. fallback($row['name'][$row['language_code']], "on line $line") . PHP_EOL;
							$batch['counters']['inserted']++;

							if (!empty($row['id'])) {
								database::query(
									"insert into ". DB_TABLE_PREFIX ."stock_items (id, date_created)
									values (". (int)$row['id'] .", '". date('Y-m-d H:i:s') ."');"
								);
								$stock_item = new ent_stock_item($row['id']);
							} else {
								$stock_item = new ent_stock_item();
							}
						}

						if (empty($row['brand_id']) && !empty($row['brand_name'])) {

							$brand = database::query(
								"select * from ". DB_TABLE_PREFIX ."brands
								where name = '". database::input($row['brand_name']) ."'
								limit 1;"
							)->fetch();

							if ($brand) {
								$row['brand_id'] = $brand['id'];
							} else {
								$brand = new ent_brand();
								$brand->data['name'] = $row['brand_name'];
								$brand->save();
								$row['brand_id'] = $brand->data['id'];
							}
						}

						// Set new stock_item data
						foreach ([
							'brand_id',
							'status',
							'code',
							'sku',
							'mpn',
							'gtin',
							'taric',
							'weight',
							'weight_unit',
							'length',
							'width',
							'height',
							'length_unit',
							'backordered',
							'purchase_price',
							'purchase_price_currency_code',
							'priority',
						] as $field) {
							if (isset($row[$field])) {
								$stock_item->data[$field] = $row[$field];
							}
						}

						// Set info
						if (!empty($row['language_code'])) {

							foreach ([
								'name',
							] as $field) {
								if (isset($row[$field])) {
									$product->data[$field][$row['language_code']] = $row[$field];
								}
							}
						}

						$stock_item->save();

						if (!empty($row['date_created'])) {
							database::query(
								"update ". DB_TABLE_PREFIX ."stock_items
								set date_created = '". date('Y-m-d H:i:s', strtotime($row['date_created'])) ."'
								where id = ". (int)$stock_item->data['id'] ."
								limit 1;"
							);
						}

						break;

					case 'suppliers':

						// Find supplier
						if (!empty($row['id']) && $supplier = database::query(
							"select id from ". DB_TABLE_PREFIX ."suppliers
							where id = ". (int)$row['id'] ."
							limit 1;"
						)->fetch()) {
							$supplier = new ent_supplier($supplier['id']);

						} else if (!empty($row['code']) && $supplier = database::query(
							"select id from ". DB_TABLE_PREFIX ."suppliers
							where code = '". database::input($row['code']) ."'
							limit 1;"
						)->fetch()) {
							$supplier = new ent_supplier($supplier['id']);
						}

						if (!empty($supplier->data['id'])) {

							if (empty($batch['overwrite'])) {
								echo 'Skip updating existing supplier on line '. $batch['counters']['line'] . PHP_EOL;
								continue 2;
							}

							echo 'Updating existing supplier '. fallback($row['name'], 'on line '. $batch['counters']['line']) . PHP_EOL;
							$batch['counters']['updated']++;

						} else {

							if (empty($batch['insert'])) {
								echo 'Skip inserting new supplier on line '. $batch['counters']['line'] . PHP_EOL;
								continue 2;
							}

							echo 'Inserting new supplier: '. fallback($row['name'], 'on line '. $batch['counters']['line']) . PHP_EOL;
							$batch['counters']['inserted']++;

							if (!empty($row['id'])) {
								database::query(
									"insert into ". DB_TABLE_PREFIX ."suppliers (id, date_created)
									values (". (int)$row['id'] .", '". date('Y-m-d H:i:s') ."');"
								);
								$supplier = new ent_supplier($row['id']);
							} else {
								$supplier = new ent_supplier();
							}
						}

						// Set new supplier data
						foreach ([
							'status',
							'code',
							'name',
							'description',
							'email',
							'phone',
							'link',
						] as $field) {
							if (isset($row[$field])) $supplier->data[$field] = $row[$field];
						}

						$supplier->save();

						if (!empty($row['date_created'])) {
							database::query(
								"update ". DB_TABLE_PREFIX ."suppliers
								set date_created = '". date('Y-m-d H:i:s', strtotime($row['date_created'])) ."'
								where id = ". (int)$supplier->data['id'] ."
								limit 1;"
							);
						}

						break;

					default:
						throw new Exception('Unknown type');
				}
			}

			unset(session::$data['csv_batch']);

			echo PHP_EOL . 'Completed!';

			notices::add('success', language::translate('success_import_completed', 'Import completed'));

			header('Refresh: 5; url='. document::ilink(__APP__.'/csv'));
			exit;

		} catch (Exception $e) {
			unset(session::$data['csv_batch']);
			notices::add('errors', $e->getMessage());
			echo 'Error: ' . $e->getMessage();
			header('Refresh: 5; url='. document::ilink(__APP__.'/csv'));
			exit;
		}
	}

	if (isset($_POST['export'])) {

		try {

			ini_set('memory_limit', -1);

			if (empty($_POST['type'])) {
				throw new Exception(language::translate('error_must_select_type', 'You must select type'));
			}

			$csv = [];

			switch ($_POST['type']) {

				case 'attributes':

					if (empty($_POST['language_code'])) {
						throw new Exception(language::translate('error_must_select_a_language', 'You must select a language'));
					}

					$csv = database::query(
						"select ag.id as group_id, ag.code as group_code, av.id as value_id, av.priority,
							json_value(ag.name, '$.". database::input($_POST['language_code']) ."') as group_name,
							json_value(av.name, '$.". database::input($_POST['language_code']) ."') as value_name,
							'". database::input($_POST['language_code']) ."' as language_code
						from ". DB_TABLE_PREFIX ."attribute_values av
						left join ". DB_TABLE_PREFIX ."attribute_groups ag on (ag.id = av.group_id)
						order by name, av.priority;"
					)->export($result)->fetch_all();

					if (!$csv) {
						$csv = [array_fill_keys($result->fields(), '')];
					}

					break;

				case 'brands':

					if (empty($_POST['language_code'])) {
						throw new Exception(language::translate('error_must_select_a_language', 'You must select a language'));
					}

					$csv = database::query(
						"select b.*, '' as new_image,
							json_value(b.name, '". database::input($_POST['language_code']) ."') as name,
							json_value(bi.short_description, '". database::input($_POST['language_code']) ."') as short_description,
							json_value(bi.description, '". database::input($_POST['language_code']) ."') as description,
							json_value(bi.meta_description, '". database::input($_POST['language_code']) ."') as meta_description,
							json_value(bi.head_title, '". database::input($_POST['language_code']) ."') as head_title,
							json_value(bi.h1_title, '". database::input($_POST['language_code']) ."') as h1_title,
							'". database::input($_POST['language_code']) ."' as language_code
						from ". DB_TABLE_PREFIX ."brands b
						order by b.priority;"
					)->export($result)->fetch_all();

					if (!$csv) {
						$csv = [array_fill_keys($result->fields(), '')];
					}

					break;

				case 'campaigns':

					$csv = database::query(
						"select * from ". DB_TABLE_PREFIX ."campaigns_products cp
						left join ". DB_TABLE_PREFIX ."campaigns c on (c.id = cp.campaign_id)
						order by c.date_valid_from, c.date_valid_to, cp.product_id;"
					)->export($result)->fetch_all();

					if (!$csv) {
						$csv = [array_fill_keys($result->fields(), '')];
					}

					break;

				case 'categories':

					if (empty($_POST['language_code'])) {
						throw new Exception(language::translate('error_must_select_a_language', 'You must select a language'));
					}

					$csv = database::query(
						"select c.*, c2.code as parent_code,
							json_value(c.name, '". database::input($_POST['language_code']) ."') as name,
							json_value(c.short_description, '". database::input($_POST['language_code']) ."') as short_description,
							json_value(c.description, '". database::input($_POST['language_code']) ."') as description,
							json_value(c.meta_description, '". database::input($_POST['language_code']) ."') as meta_description,
							json_value(c.head_title, '". database::input($_POST['language_code']) ."') as head_title,
							json_value(c.h1_title, '". database::input($_POST['language_code']) ."') as h1_title,
							'' as new_image, '". database::input($_POST['language_code']) ."' as language_code
						from ". DB_TABLE_PREFIX ."categories c
						left join ". DB_TABLE_PREFIX ."categories c2 on (c2.id = c.parent_id)
						order by c.priority;"
					)->export($result)->fetch_all();

					if (!$csv) {
						$csv = [array_fill_keys($result->fields(), '')];
					}

					break;

				case 'products':

					if (empty($_POST['language_code'])) {
						throw new Exception(language::translate('error_must_select_a_language', 'You must select a language'));
					}

					if (empty($_POST['currency_code'])) {
						throw new Exception(language::translate('error_must_select_a_currency', 'You must select a currency'));
					}

					$csv = database::query(
						"select p.*,
							json_value(p.name, '". database::input($_POST['language_code']) ."') as name,
							json_value(p.description, '". database::input($_POST['language_code']) ."') as description,
							json_value(p.short_description, '". database::input($_POST['language_code']) ."') as short_description,
							json_value(p.technical_data, '". database::input($_POST['language_code']) ."') as technical_data,
							json_value(p.meta_description, '". database::input($_POST['language_code']) ."') as meta_description,
							json_value(p.head_title, '". database::input($_POST['language_code']) ."') as head_title,
							'". database::input($_POST['language_code']) ."' as language_code,
							ptc.categories, pim.images, '' as new_image, pa.attributes

						from ". DB_TABLE_PREFIX ."products p

						left join (
							select product_id, group_concat(category_id separator ',') as categories
							from ". DB_TABLE_PREFIX ."products_to_categories
							group by product_id
							order by category_id
						) ptc on (ptc.product_id = p.id)
						left join (
							select product_id, group_concat(concat(group_id, ':', if(custom_value != '', concat('\"', custom_value, '\"'), value_id)) separator '\r\n') as attributes
							from ". DB_TABLE_PREFIX ."products_attributes
							group by product_id
						) pa on (p.id = pa.product_id)
						left join (
							select product_id, group_concat(filename separator ';') as images
							from ". DB_TABLE_PREFIX ."products_images
							group by product_id
							order by priority
						) pim on (pim.product_id = p.id)
						order by name, pi.id;"
					)->export($result)->fetch_all();

					if (!$csv) {
						$csv = [array_fill_keys($result->fields(), '')];
					}

					break;

				case 'product_stock_options':

					$csv = database::query(
						"select * from ". DB_TABLE_PREFIX ."products_stock_options
						order by id;"
					)->export($result)->fetch_all();

					if (!$csv) {
						$csv = [array_fill_keys($result->fields(), '')];
					}

					break;

				case 'suppliers':

					$csv = database::query(
						"select * from ". DB_TABLE_PREFIX ."suppliers
						order by id;"
					)->export($result)->fetch_all();

					if (!$csv) {
						$csv = [array_fill_keys($result->fields(), '')];
					}

					break;

				case 'stock_items':

					$csv = database::query(
						"select si.*,
							json_value(si.name, '". database::input($_POST['language_code']) ."') as name,
							'". database::input($_POST['language_code']) ."' as language_code
						from ". DB_TABLE_PREFIX ."stock_items si
						order by si.id;"
					)->export($result)->fetch_all();

					if (!$csv) {
						$csv = [array_fill_keys($result->fields(), '')];
					}

					break;

				default:
					throw new Exception('Unknown type');
			}

			ob_clean();

			if ($_POST['output'] == 'screen') {
				header('Content-Type: text/plain; charset='. $_POST['charset']);
				header('Content-Disposition: inline; filename='. $_POST['type'] . (!empty($_POST['language_code']) ? '-'. $_POST['language_code'] : '') . (!empty($_POST['currency_code']) ? '-'. $_POST['currency_code'] : '') .'.csv');
			} else {
				header('Content-Type: application/csv; charset='. $_POST['charset']);
				header('Content-Disposition: attachment; filename='. $_POST['type'] . (!empty($_POST['language_code']) ? '-'. $_POST['language_code'] : '') . (!empty($_POST['currency_code']) ? '-'. $_POST['currency_code'] : '') .'.csv');
			}

			switch($_POST['eol']) {
				case 'Linux':
					echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\r");
					break;
				case 'Mac':
					echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\n");
					break;
				case 'Win':
				default:
					echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\r\n");
					break;
			}

			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
		<?php echo $app_icon; ?> <?php echo language::translate('title_csv_import_export', 'CSV Import/Export'); ?>
	</div>
	</div>

	<div class="card-body">

		<div class="grid" style="max-width: 1200px;">

			<div class="col-lg-6">
				<?php echo functions::form_begin('import_form', 'post', '', true); ?>

					<fieldset>
						<legend><?php echo language::translate('title_import', 'Import'); ?></legend>

						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_type', 'Type'); ?></div>
							<div class="form-input">
								<?php echo functions::form_radio_button('type', ['attributes', language::translate('title_attributes', 'Attributes')], true); ?>
								<?php echo functions::form_radio_button('type', ['brands', language::translate('title_brands', 'Brands')], true); ?>
								<?php echo functions::form_radio_button('type', ['campaigns', language::translate('title_campaigns', 'Campaigns')], true); ?>
								<?php echo functions::form_radio_button('type', ['categories', language::translate('title_categories', 'Categories')], true); ?>
								<?php echo functions::form_radio_button('type', ['products', language::translate('title_products', 'Products')], true); ?>
								<?php echo functions::form_radio_button('type', ['product_prices', language::translate('title_product_prices', 'Product Prices')], true); ?>
								<?php echo functions::form_radio_button('type', ['product_stock_options', language::translate('title_product_stock_options', 'Product Stock Options')], true); ?>
								<?php echo functions::form_radio_button('type', ['stock_items', language::translate('title_stock_items', 'Stock Items')], true); ?>
								<?php echo functions::form_radio_button('type', ['suppliers', language::translate('title_suppliers', 'Suppliers')], true); ?>
							</div>
						</label>

						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_csv_file', 'CSV File'); ?></div>
							<?php echo functions::form_input_file('file', 'accept=".csv, .dsv, .tab, .tsv"'); ?>
						</label>

						<div class="grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_delimiter', 'Delimiter'); ?></div>
									<?php echo functions::form_select('delimiter', ['' => language::translate('title_auto', 'Auto') .' ('. language::translate('text_default', 'default') .')', ',' => ',',  ';' => ';', "\t" => 'TAB', '|' => '|'], true); ?>
								</label>
							</div>

							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_enclosure', 'Enclosure'); ?></div>
									<?php echo functions::form_select('enclosure', ['"' => '" ('. language::translate('text_default', 'default') .')'], true); ?>
								</label>
							</div>
						</div>

						<div class="grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_escape_character', 'Escape Character'); ?></div>
									<?php echo functions::form_select('escapechar', ['"' => '" ('. language::translate('text_default', 'default') .')', '\\' => '\\'], true); ?>
								</label>
							</div>

							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_charset', 'Charset'); ?></div>
									<?php echo functions::form_select_encoding('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
								</label>
							</div>
						</div>

						<div class="form-group">
							<?php echo functions::form_checkbox('insert', ['1', language::translate('text_insert_new_entries', 'Insert new entries')], true); ?>
							<?php echo functions::form_checkbox('reset', ['1', language::translate('text_wipe_storage_clean_before_inserting_data', 'Wipe storage clean before inserting data')], true); ?>
							<?php echo functions::form_checkbox('overwrite', ['1', language::translate('text_overwrite_existing_entries', 'Overwrite existing entries')], true); ?>
						</div>

						<?php echo functions::form_button('import', language::translate('title_import', 'Import'), 'submit'); ?>
					</fieldset>

				<?php echo functions::form_end(); ?>
			</div>

			<div class="col-lg-6">
				<?php echo functions::form_begin('export_form', 'post'); ?>

					<fieldset>
						<legend><?php echo language::translate('title_export', 'Export'); ?></legend>

						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_type', 'Type'); ?></div>
							<div class="form-input">
								<?php echo functions::form_radio_button('type', ['attributes', language::translate('title_attributes', 'Attributes')], true, 'data-dependencies="language"'); ?>
								<?php echo functions::form_radio_button('type', ['brands', language::translate('title_brands', 'Brands')], true, 'data-dependencies="language"'); ?>
								<?php echo functions::form_radio_button('type', ['campaigns', language::translate('title_campaigns', 'Campaigns')], true); ?>
								<?php echo functions::form_radio_button('type', ['categories', language::translate('title_categories', 'Categories')], true, 'data-dependencies="language"'); ?>
								<?php echo functions::form_radio_button('type', ['products', language::translate('title_products', 'Products')], true, 'data-dependencies="language"'); ?>
								<?php echo functions::form_radio_button('type', ['product_prices', language::translate('title_product_prices', 'Product Prices')], true); ?>
								<?php echo functions::form_radio_button('type', ['product_stock_options', language::translate('title_product_stock_options', 'Product Stock Options')], true); ?>
								<?php echo functions::form_radio_button('type', ['stock_items', language::translate('title_stock_items', 'Stock Items')], true, 'data-dependencies="language"'); ?>
								<?php echo functions::form_radio_button('type', ['suppliers', language::translate('title_suppliers', 'Suppliers')], true); ?>
							</div>
						</label>

						<div class="grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_language', 'Language'); ?></div>
									<?php echo functions::form_select_language('language_code', true, 'required'); ?>
								</label>
							</div>

							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_currency', 'Currency'); ?></div>
									<?php echo functions::form_select_currency('currency_code', true, 'required'); ?>
								</label>
							</div>
						</div>

						<div class="grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_delimiter', 'Delimiter'); ?></div>
									<?php echo functions::form_select('delimiter', [',' => ', ('. language::translate('text_default', 'default') .')', ';' => ';', "\t" => 'TAB', '|' => '|'], true); ?>
								</label>
							</div>

							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_enclosure', 'Enclosure'); ?></div>
									<?php echo functions::form_select('enclosure', ['"' => '" ('. language::translate('text_default', 'default') .')'], true); ?>
								</label>
							</div>
						</div>

						<div class="grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_escape_character', 'Escape Character'); ?></div>
									<?php echo functions::form_select('escapechar', ['"' => '" ('. language::translate('text_default', 'default') .')', '\\' => '\\'], true); ?>
								</label>
							</div>

							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_charset', 'Charset'); ?></div>
									<?php echo functions::form_select_encoding('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
								</label>
							</div>
						</div>

						<div class="grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_line_ending', 'Line Ending'); ?></div>
									<?php echo functions::form_select('eol', ['Win', 'Mac', 'Linux'], true); ?>
								</label>
							</div>

							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_output', 'Output'); ?></div>
									<?php echo functions::form_select('output', ['screen' => language::translate('title_screen', 'Screen'), 'file' => language::translate('title_file', 'File')], true); ?>
								</label>
							</div>
						</div>

						<?php echo functions::form_button('export', language::translate('title_export', 'Export'), 'submit'); ?>
					</fieldset>

				<?php echo functions::form_end(); ?>
			</div>

		</div>
	</div>
</div>

<script>
	$('form[name="export_form"] input[name="type"]').on('change', function() {
		let dependencies = $(this).data('dependencies') ? $(this).data('dependencies').split(',') : [];
		$('form[name="export_form"] select[name="currency_code"]').prop('disabled', ($.inArray('currency', dependencies) === -1));
		$('form[name="export_form"] select[name="language_code"]').prop('disabled', ($.inArray('language', dependencies) === -1));
	});

	$('form[name="export_form"] input[name="type"]:checked').trigger('change');

	$('form[name="import_form"] input[name="reset"]').on('click', function() {
		if ($(this).is(':checked') && !confirm("<?php echo language::translate('text_are_you_sure', 'Are you sure?'); ?>")) return false;
	});

	$('form[name="import_form"] input[name="insert"]').on('change', function() {
		$('form[name="import_form"] input[name="reset"]').prop('checked', false).prop('disabled', !$(this).is(':checked'));
	}).trigger('change');
</script>