<?php

  document::$snippets['title'][] = language::translate('title_import_export_csv', 'Import/Export CSV');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
  breadcrumbs::add(language::translate('title_import_export_csv', 'Import/Export CSV'));

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

        echo $progress .'% complete' .' - Estimated time remaining: '. $time_remaining .' s - Memory usage: '. $memory_usage .' MB' . PHP_EOL . PHP_EOL;

      } else {

        if (empty($_POST['type'])) throw new Exception(language::translate('error_must_select_type', 'You must select type'));

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

              database::multi_query(
                "truncate ". DB_TABLE_PREFIX ."attribute_groups;
                truncate ". DB_TABLE_PREFIX ."attribute_groups_info;
                truncate ". DB_TABLE_PREFIX ."attribute_values;
                truncate ". DB_TABLE_PREFIX ."attribute_values_info;
                truncate ". DB_TABLE_PREFIX ."products_options;
                truncate ". DB_TABLE_PREFIX ."products_options_stock;
                truncate ". DB_TABLE_PREFIX ."products_options_values;"
              );

              break;

            case 'campaigns':

              database::multi_query(
                "truncate ". DB_TABLE_PREFIX ."products_campaigns;"
              );

              break;

            case 'categories':

              database::multi_query(
                "truncate ". DB_TABLE_PREFIX ."categories;
                truncate ". DB_TABLE_PREFIX ."categories_filters;
                truncate ". DB_TABLE_PREFIX ."categories_info;
                truncate ". DB_TABLE_PREFIX ."products_to_categories;"
              );

              foreach (functions::file_search('storage://images/categories/*') as $file) {
                if (preg_match('#index\.html$#', $file)) continue;
                functions::file_delete($file);
              }

              break;

            case 'brands':

              database::multi_query(
                "truncate ". DB_TABLE_PREFIX ."brands`;
                truncate ". DB_TABLE_PREFIX ."brands_info;"
              );

              foreach (functions::file_search('storage://images/brands/*') as $file) {
                if (preg_match('#index\.html$#', $file)) continue;
                functions::file_delete($file);
              }

              break;

            case 'products':

              database::multi_query(
                "truncate ". DB_TABLE_PREFIX ."cart_items;
                truncate ". DB_TABLE_PREFIX ."products;
                truncate ". DB_TABLE_PREFIX ."products_attributes;
                truncate ". DB_TABLE_PREFIX ."products_campaigns;
                truncate ". DB_TABLE_PREFIX ."products_downloads;
                truncate ". DB_TABLE_PREFIX ."products_images;
                truncate ". DB_TABLE_PREFIX ."products_info;
                truncate ". DB_TABLE_PREFIX ."products_prices;
                truncate ". DB_TABLE_PREFIX ."products_to_categories;
                truncate ". DB_TABLE_PREFIX ."products_stock_items;
                update ". DB_TABLE_PREFIX ."orders_items set product_id = 0;"
              );

              foreach (functions::file_search('storage://images/products/*') as $file) {
                if (preg_match('#index\.html$#', $file)) continue;
                functions::file_delete($file);
              }

              break;

            case 'stock_items':

              database::multi_query(
                "truncate ". DB_TABLE_PREFIX ."stock_items;
                truncate ". DB_TABLE_PREFIX ."stock_items_info;
                truncate ". DB_TABLE_PREFIX ."stock_transactions;
                truncate ". DB_TABLE_PREFIX ."stock_transactions_contents;
                update ". DB_TABLE_PREFIX ."orders_items set stock_item_id = 0;"
              );

              foreach (functions::file_search('storage://images/stock_items/*') as $file) {
                if (preg_match('#index\.html$#', $file)) continue;
                functions::file_delete($file);
              }

              break;

            case 'suppliers':

              database::multi_query(
                "truncate ". DB_TABLE_PREFIX ."suppliers;"
              );

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
            if (!empty($row['group_id']) && ($attribute_group = database::query("select id from ". DB_TABLE_PREFIX ."attribute_groups where id = ". (int)$row['group_id'] ." limit 1;")->fetch())) {
              $attribute_group = new ent_attribute_group($attribute_group['id']);

            } elseif (!empty($row['code']) && $attribute_group = database::query("select id from ". DB_TABLE_PREFIX ."attribute_groups where code = '". database::input($row['code']) ."' limit 1;")->fetch()) {
              $attribute_group = new ent_attribute_group($attribute_group['id']);

            } elseif (!empty($row['group_name']) && $attribute_group = database::query("select group_id as id from ". DB_TABLE_PREFIX ."attribute_groups_info where name = '". database::input($row['group_name']) ."' and language_code = '". database::input($row['language_code']) ."' limit 1;")->fetch()) {
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
            if (isset($row['group_code'])) $attribute_group->data['code'] = $row['group_code'];
            if (isset($row['group_name'])) $attribute_group->data['name'][$row['language_code']] = $row['group_name'];
            if (isset($row['sort'])) $attribute_group->data['sort'] = $row['sort'];

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
            uasort($attribute_group->data['values'], function($a, $b){
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
                "select id from ". DB_TABLE_PREFIX ."products_campaigns
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
                  "insert into ". DB_TABLE_PREFIX ."products_campaigns
                  (id, product_id)
                  values (". (int)$row['id'] .", '". $row['product_id'] ."');"
                );
              }
            }

            $prices = array_intersect_key($row, currency::$currencies);

            $sql_update_prices = '';
            foreach ($prices as $currency_code => $price) {
              $sql_update_prices .= database::input($currency_code) ." = ". (float)$price . "," . PHP_EOL;
            }

            database::query(
              "update ". DB_TABLE_PREFIX ."products_campaigns
              set product_id = ". (int)$row['product_id'] .",
                  ". $sql_update_prices ."
                  start_date = ". (empty($row['start_date']) ? "null" : "'". date('Y-m-d H:i:s', strtotime($row['start_date'])) ."'") .",
                  end_date = ". (empty($row['end_date']) ? "null" : "'". date('Y-m-d H:i:s', strtotime($row['end_date'])) ."'") ."
              where id = ". (int)$row['id'] ."
              limit 1;"
            );

            break;

          case 'categories':

          // Find category
            if (!empty($row['id']) && $category = database::query("select id from ". DB_TABLE_PREFIX ."categories where id = ". (int)$row['id'] ." limit 1;")->fetch()) {
              $category = new ent_category($category['id']);

            } elseif (!empty($row['code']) && $category = database::query("select id from ". DB_TABLE_PREFIX ."categories where code = '". database::input($row['code']) ."' limit 1;")->fetch()) {
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
            $fields = [
              'parent_id',
              'status',
              'code',
              'keywords',
              'image',
              'priority',
            ];

            foreach ($fields as $field) {
              if (isset($row[$field])) $category->data[$field] = $row[$field];
            }

          // Set category info data
            if (!empty($row['language_code'])) {
              $fields = [
                'name',
                'short_description',
                'description',
                'head_title',
                'h1_title',
                'meta_description',
              ];

              foreach ($fields as $field) {
                if (isset($row[$field])) $category->data[$field][$row['language_code']] = $row[$field];
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
            if (!empty($row['id']) && $brand = database::query("select id from ". DB_TABLE_PREFIX ."brands where id = ". (int)$row['id'] ." limit 1;")->fetch()) {
              $brand = new ent_brand($brand['id']);

            } else if (!empty($row['code']) && $brand = database::query("select id from ". DB_TABLE_PREFIX ."brands where code = '". database::input($row['code']) ."' limit 1;")->fetch()) {
              $brand = new ent_brand($brand['id']);

            } else if (!empty($row['name']) && !empty($row['language_code']) && $brand = database::query("select id from ". DB_TABLE_PREFIX ."brands where name = '". database::input($row['name']) ."' limit 1;")->fetch()) {
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
            $fields = [
              'status',
              'code',
              'name',
              'keywords',
              'image',
              'priority',
            ];

            foreach ($fields as $field) {
              if (isset($row[$field])) $brand->data[$field] = $row[$field];
            }

          // Set brand info data
            if (!empty($row['language_code'])) {
              $fields = [
                'short_description',
                'description',
                'head_title',
                'h1_title',
                'meta_description',
              ];

              foreach ($fields as $field) {
                if (isset($row[$field])) $brand->data[$field][$row['language_code']] = $row[$field];
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
            if (!empty($row['id']) && $product = database::query("select id from ". DB_TABLE_PREFIX ."products where id = ". (int)$row['id'] ." limit 1;")->fetch()) {
              $product = new ent_product($product['id']);

            } elseif (!empty($row['code']) && $product = database::query("select id from ". DB_TABLE_PREFIX ."products where code = '". database::input($row['code']) ."' limit 1;")->fetch()) {
              $product = new ent_product($product['id']);

            } elseif (!empty($row['sku']) && $product = database::query("select id from ". DB_TABLE_PREFIX ."products where sku = '". database::input($row['sku']) ."' limit 1;")->fetch()) {
              $product = new ent_product($product['id']);

            } elseif (!empty($row['mpn']) && $product = database::query("select id from ". DB_TABLE_PREFIX ."products where mpn = '". database::input($row['mpn']) ."' limit 1;")->fetch()) {
              $product = new ent_product($product['id']);

            } elseif (!empty($row['gtin']) && $product = database::query("select id from ". DB_TABLE_PREFIX ."products where gtin = '". database::input($row['gtin']) ."' limit 1;")->fetch()) {
              $product = new ent_product($product['id']);

            }

            if (!empty($product->data['id'])) {

              if (empty($batch['overwrite'])) {
                echo 'Skip updating existing product on line '. $batch['counters']['line'] . PHP_EOL;
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

            $fields = [
              'status',
              'brand_id',
              'supplier_id',
              'code',
              'tax_class_id',
              'quantity',
              'quantity_unit_id',
              'recommended_price',
              'delivery_status_id',
              'sold_out_status_id',
              'date_valid_from',
              'date_valid_to',
            ];

          // Set new product data
            foreach ($fields as $field) {
              if (isset($row[$field])) $product->data[$field] = $row[$field];
            }

            if (isset($row['keywords'])) $product->data['keywords'] = preg_split('#\s*,\s*#', $row['keywords'], -1, PREG_SPLIT_NO_EMPTY);
            if (isset($row['categories'])) $product->data['categories'] = preg_split('#\s*,\s*#', $row['categories'], -1, PREG_SPLIT_NO_EMPTY);

          // Set price
            if (!empty($row['currency_code'])) {
              if (isset($row['price'])) $product->data['prices'][$row['currency_code']] = $row['price'];
            }

          // Set product info data
            if (!empty($row['language_code'])) {

              $fields = [
                'name',
                'short_description',
                'description',
                'technical_data',
                'head_title',
                'meta_description',
              ];

              foreach ($fields as $field) {
                if (isset($row[$field])) $product->data[$field][$row['language_code']] = $row[$field];
              }
            }

          // Set images
            if (isset($row['images'])) {
              $row['images'] = explode(';', $row['images']);

              $product_images = [];
              $current_images = [];
              foreach ($product->data['images'] as $key => $image) {
                if (in_array($image['filename'], $row['images'])) {
                  $product_images[$key] = $image;
                  $current_images[] = $image['filename'];
                }
              }

              $i=0;
              foreach ($row['images'] as $image) {
                if (!in_array($image, $current_images)) {
                  $product_images['new'.++$i] = ['filename' => $image];
                }
              }

              $product->data['images'] = $product_images;
            }

          // Import new images
            if (!empty($row['new_images'])) {
              foreach (explode(';', $row['new_images']) as $new_image) {
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

          case 'stock_items':

          // Find stock_item
            if (!empty($row['id']) && $stock_item = database::query("select id from ". DB_TABLE_PREFIX ."stock_items where id = ". (int)$row['id'] ." limit 1;")->fetch()) {
              $stock_item = new ent_stock_item($stock_item['id']);

            } elseif (!empty($row['sku']) && $product = database::query("select id from ". DB_TABLE_PREFIX ."stock_items where sku = '". database::input($row['sku']) ."' limit 1;")->fetch()) {
              $product = new ent_product($product['id']);

            } elseif (!empty($row['mpn']) && $product = database::query("select id from ". DB_TABLE_PREFIX ."stock_items where mpn = '". database::input($row['mpn']) ."' limit 1;")->fetch()) {
              $product = new ent_product($product['id']);

            } elseif (!empty($row['gtin']) && $product = database::query("select id from ". DB_TABLE_PREFIX ."stock_items where gtin = '". database::input($row['gtin']) ."' limit 1;")->fetch()) {
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
                $stock_items = new ent_stock_item();
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

          // Set new stock item data
            $fields = [
              'brand_id',
              'supplier_id',
              'status',
              'sku',
              'mpn',
              'gtin',
              'quantity',
              'weight',
              'weight_unit',
              'length',
              'width',
              'height',
              'length_unit',
              'purchase_price',
              'purchase_price_currency_code',
              'backordered',
            ];

            foreach ($fields as $field) {
              if (isset($row[$field])) $stock_item->data[$field] = $row[$field];
            }

            $fields = [
              'name',
            ];

            foreach ($fields as $field) {
              if (isset($row[$field])) $stock_item->data[$field][[$row['language_code']]] = $row[$field];
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
            if (!empty($row['id']) && $supplier = database::query("select id from ". DB_TABLE_PREFIX ."suppliers where id = ". (int)$row['id'] ." limit 1;")->fetch()) {
              $supplier = new ent_supplier($supplier['id']);

            } else if (!empty($row['code']) && $supplier = database::query("select id from ". DB_TABLE_PREFIX ."suppliers where code = '". database::input($row['code']) ."' limit 1;")->fetch()) {
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
            $fields = [
              'status',
              'code',
              'name',
              'description',
              'email',
              'phone',
              'link',
            ];

            foreach ($fields as $field) {
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

      header('Refresh: 5; url='. document::link(null, [], ['app', 'doc'], 'resume'));
      exit;

    } catch (Exception $e) {
      unset(session::$data['csv_batch']);
      notices::add('errors', $e->getMessage());
      echo 'Error: ' . $e->getMessage();
      header('Refresh: 5; url='. document::link(null, [], ['app', 'doc'], 'resume'));
      exit;
    }
  }

  if (isset($_POST['export'])) {

    try {

      ini_set('memory_limit', -1);

      if (empty($_POST['type'])) throw new Exception(language::translate('error_must_select_type', 'You must select type'));

      $csv = [];

      switch ($_POST['type']) {

        case 'attributes':

          if (empty($_POST['language_code'])) throw new Exception(language::translate('error_must_select_a_language', 'You must select a language'));

          $csv = database::query(
            "select ag.id as group_id, ag.code as group_code, agi.name as group_name, av.id as value_id, avi.name as value_name, avi.language_code, av.priority, '". database::input($_POST['language_code']) ."' as language_code
            from ". DB_TABLE_PREFIX ."attribute_values av
            left join ". DB_TABLE_PREFIX ."attribute_groups ag on (ag.id = av.group_id)
            left join ". DB_TABLE_PREFIX ."attribute_groups_info agi on (agi.group_id = av.group_id and agi.language_code = '". database::input($_POST['language_code']) ."')
            left join ". DB_TABLE_PREFIX ."attribute_values_info avi on (avi.value_id = av.id and avi.language_code = '". database::input($_POST['language_code']) ."')
            order by agi.name, av.priority;"
          )->export($result)->fetch_all();

          if (!$csv) {
            $csv = [array_fill_keys($result->fields(), '')];
          }

          break;

        case 'brands':

          if (empty($_POST['language_code'])) throw new Exception(language::translate('error_must_select_a_language', 'You must select a language'));

          $csv = database::query(
            "select m.*, '' as new_image, mi.name, mi.short_description, mi.description, mi.meta_description, mi.head_title, mi.h1_title, '". database::input($_POST['language_code']) ."' as language_code
            from ". DB_TABLE_PREFIX ."manufacturers m
            left join ". DB_TABLE_PREFIX ."manufacturers_info mi on (mi.category_id = m.id and mi.language_code = '". database::input($_POST['language_code']) ."')
            order by m.priority;"
          )->export($result)->fetch_all();

          if (!$csv) {
            $csv = [array_fill_keys($result->fields(), '')];
          }

          break;

        case 'campaigns':

          $csv = database::query(
            "select * from ". DB_TABLE_PREFIX ."products_campaigns
            order by product_id;"
          )->export($result)->fetch_all();

          if (!$csv) {
            $csv = [array_fill_keys($result->fields(), '')];
          }

          break;

        case 'categories':

          if (empty($_POST['language_code'])) throw new Exception(language::translate('error_must_select_a_language', 'You must select a language'));

          $csv = database::query(
            "select c.*, c2.code as parent_code, ci.name, '' as new_image, ci.short_description, ci.description, ci.meta_description, ci.head_title, ci.h1_title, '". database::input($_POST['language_code']) ."' as language_code
            from ". DB_TABLE_PREFIX ."categories c
            left join ". DB_TABLE_PREFIX ."categories c2 on (c2.id = c.parent_id)
            left join ". DB_TABLE_PREFIX ."categories_info ci on (ci.category_id = c.id and ci.language_code = '". database::input($_POST['language_code']) ."')
            order by c.priority;"
          )->export($result)->fetch_all();

          if (!$csv) {
            $csv = [array_fill_keys($result->fields(), '')];
          }

          break;

        case 'products':

          if (empty($_POST['language_code'])) throw new Exception(language::translate('error_must_select_a_language', 'You must select a language'));
          if (empty($_POST['currency_code'])) throw new Exception(language::translate('error_must_select_a_currency', 'You must select a currency'));

          $csv = database::query(
            "select p.*, '". database::input($_POST['currency_code']) ."' as currency_code, pi.name, pi.description, pi.short_description, pi.technical_data, pi.meta_description, pi.head_title, '". database::input($_POST['language_code']) ."' as language_code, p2c.categories, pp.price, pim.images, '' as new_image, pa.attributes
            from ". DB_TABLE_PREFIX ."products p
            left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input($_POST['language_code']) ."')
            left join ". DB_TABLE_PREFIX ."manufacturers m on (m.id = p.manufacturer_id)
            left join (
              select product_id, group_concat(category_id separator ',') as categories
              from ". DB_TABLE_PREFIX ."products_to_categories
              group by product_id
              order by category_id
            ) p2c on (p2c.product_id = p.id)
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
            left join (
              select product_id, `". database::input($_POST['currency_code']) ."` as price
              from ". DB_TABLE_PREFIX ."products_prices
            ) pp on (pp.product_id = p.id)
            order by pi.name, pi.id;"
          )->export($result)->fetch_all();

          if (!$csv) {
            $csv = [array_fill_keys($result->fields(), '')];
          }

        case 'stock_items':

          $csv = database::query(
            "select si.*, sii.name, '". database::input($_POST['language_code']) ."' as language_code
            from ". DB_TABLE_PREFIX ."stock_items si
            left join ". DB_TABLE_PREFIX ."stock_items_info sii on (si.product_id = s.id and si.language_code = '". database::input($_POST['language_code']) ."')
            order by s.id;"
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

        default:
          throw new Exception('Unknown type');
      }

      ob_end_clean();

      if ($_POST['output'] == 'screen') {
        header('Content-Type: text/plain; charset='. $_POST['charset']);
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
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
    <?php echo $app_icon; ?> <?php echo language::translate('title_csv_import_export', 'CSV Import/Export'); ?>
  </div>
  </div>

  <div class="card-body">

    <div class="row">

      <div class="col-sm-6 col-lg-4">
        <?php echo functions::form_draw_form_begin('import_form', 'post', '', true); ?>

          <fieldset>
            <legend><?php echo language::translate('title_import', 'Import'); ?></legend>

            <div class="form-group">
              <label><?php echo language::translate('title_type', 'Type'); ?></label>
              <div>
                <?php echo functions::form_draw_radio_button('type', ['attributes', language::translate('title_attributes', 'Attributes')], true, 'data-dependencies="language"'); ?>
                <?php echo functions::form_draw_radio_button('type', ['campaigns', language::translate('title_campaigns', 'Campaigns')], true); ?>
                <?php echo functions::form_draw_radio_button('type', ['categories', language::translate('title_categories', 'Categories')], true); ?>
                <?php echo functions::form_draw_radio_button('type', ['brands', language::translate('title_brands', 'Brands')], true); ?>
                <?php echo functions::form_draw_radio_button('type', ['products', language::translate('title_products', 'Products')], true); ?>
                <?php echo functions::form_draw_radio_button('type', ['stock_items', language::translate('title_stock_items', 'Stock Items')], true); ?>
                <?php echo functions::form_draw_radio_button('type', ['suppliers', language::translate('title_suppliers', 'Suppliers')], true); ?>
              </div>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_csv_file', 'CSV File'); ?></label>
              <?php echo functions::form_draw_file_field('file', 'accept=".csv, .dsv, .tab, .tsv"'); ?>
            </div>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
                <?php echo functions::form_draw_select_field('delimiter', ['' => language::translate('title_auto', 'Auto') .' ('. language::translate('text_default', 'default') .')', ',' => ',',  ';' => ';', "\t" => 'TAB', '|' => '|'], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
                <?php echo functions::form_draw_select_field('enclosure', ['"' => '" ('. language::translate('text_default', 'default') .')'], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
                <?php echo functions::form_draw_select_field('escapechar', ['"' => '" ('. language::translate('text_default', 'default') .')', '\\' => '\\'], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
                <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
              </div>
            </div>

            <div class="form-group">
              <?php echo functions::form_draw_checkbox('reset', ['1', language::translate('text_wipe_storage_clean_before_inserting_data', 'Wipe storage clean before inserting data')], true); ?>
              <?php echo functions::form_draw_checkbox('insert', ['1', language::translate('text_insert_new_entries', 'Insert new entries')], true); ?>
              <?php echo functions::form_draw_checkbox('overwrite', ['1', language::translate('text_overwrite_existing_entries', 'Overwrite existing entries')], true); ?>
            </div>

            <?php echo functions::form_draw_button('import', language::translate('title_import', 'Import'), 'submit'); ?>
          </fieldset>

        <?php echo functions::form_draw_form_end(); ?>
      </div>

      <div class="col-sm-6 col-lg-4">
        <?php echo functions::form_draw_form_begin('export_form', 'post'); ?>

          <fieldset>
            <legend><?php echo language::translate('title_export', 'Export'); ?></legend>

            <div class="form-group">
              <label><?php echo language::translate('title_type', 'Type'); ?></label>
              <div>
                <?php echo functions::form_draw_radio_button('type', ['attributes', language::translate('title_attributes', 'Attributes')], true, 'data-dependencies="language"'); ?>
                <?php echo functions::form_draw_radio_button('type', ['campaigns', language::translate('title_campaigns', 'Campaigns')], true); ?>
                <?php echo functions::form_draw_radio_button('type', ['categories', language::translate('title_categories', 'Categories')], true, 'data-dependencies="language"'); ?>
                <?php echo functions::form_draw_radio_button('type', ['brands', language::translate('title_brands', 'Brands')], true, 'data-dependencies="language"'); ?>
                <?php echo functions::form_draw_radio_button('type', ['products', language::translate('title_products', 'Products')], true, 'data-dependencies="currency,language"'); ?>
                <?php echo functions::form_draw_radio_button('type', ['stock_items', language::translate('title_stock_items', 'Stock Items')], true, 'data-dependencies="language"'); ?>
                <?php echo functions::form_draw_radio_button('type', ['suppliers', language::translate('title_suppliers', 'Suppliers')], true); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_language', 'Language'); ?></label>
                <?php echo functions::form_draw_languages_list('language_code', true, 'required'); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_currency', 'Currency'); ?></label>
                <?php echo functions::form_draw_currencies_list('currency_code', true, 'required'); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
                <?php echo functions::form_draw_select_field('delimiter', [',' => ', ('. language::translate('text_default', 'default') .')', ';' => ';', "\t" => 'TAB', '|' => '|'], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
                <?php echo functions::form_draw_select_field('enclosure', ['"' => '" ('. language::translate('text_default', 'default') .')'], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
                <?php echo functions::form_draw_select_field('escapechar', ['"' => '" ('. language::translate('text_default', 'default') .')', '\\' => '\\'], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
                <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_line_ending', 'Line Ending'); ?></label>
                <?php echo functions::form_draw_select_field('eol', ['Win', 'Mac', 'Linux'], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_output', 'Output'); ?></label>
                <?php echo functions::form_draw_select_field('output', ['file' => language::translate('title_file', 'File'), 'screen' => language::translate('title_screen', 'Screen')], true); ?>
              </div>
            </div>

            <?php echo functions::form_draw_button('export', language::translate('title_export', 'Export'), 'submit'); ?>
          </fieldset>

        <?php echo functions::form_draw_form_end(); ?>
      </div>

    </div>
  </div>
</div>

<script>
  $('form[name="export_form"] input[name="type"]').change(function(){
    var dependencies = $(this).data('dependencies') ? $(this).data('dependencies').split(',') : [];
    $('form[name="export_form"] select[name="currency_code"]').prop('disabled', ($.inArray('currency', dependencies) === -1));
    $('form[name="export_form"] select[name="language_code"]').prop('disabled', ($.inArray('language', dependencies) === -1));
  });

  $('form[name="export_form"] input[name="type"]:checked').trigger('change');

  $('form[name="import_form"] input[name="reset"]').click(function(){
    if ($(this).is(':checked') && !confirm("<?php echo language::translate('text_are_you_sure', 'Are you sure?'); ?>")) return false;
  });

  $('form[name="import_form"] input[name="insert"]').change(function(){
    $('form[name="import_form"] input[name="reset"]').prop('checked', false).prop('disabled', !$(this).is(':checked'));
  }).trigger('change');
</script>