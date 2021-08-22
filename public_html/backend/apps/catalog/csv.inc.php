<?php

  document::$snippets['title'][] = language::translate('title_import_export_csv', 'Import/Export CSV');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
  breadcrumbs::add(language::translate('title_import_export_csv', 'Import/Export CSV'));

  if (isset($_POST['import'])) {

    try {

      if (empty($_POST['type'])) throw new Exception(language::translate('error_must_select_type', 'You must select type'));

      if (!isset($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
        throw new Exception(language::translate('error_must_select_file_to_upload', 'You must select a file to upload'));
      }

      echo 'CSV Import' . PHP_EOL
         . '----------' . PHP_EOL;

      $csv = file_get_contents($_FILES['file']['tmp_name']);

      if (!$csv = functions::csv_decode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'])) {
        throw new Exception(language::translate('error_failed_decoding_csv', 'Failed decoding CSV'));
      }

      $updated = 0;
      $inserted = 0;
      $line = 1;

      foreach ($csv as $row) {
        $line++;

        switch ($_POST['type']) {

          case 'attributes':

          // Find attribute group
            if (!empty($row['group_id']) && ($attribute_group = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."attribute_groups where id = ". (int)$row['group_id'] ." limit 1;")))) {
              $attribute_group = new ent_attribute_group($attribute_group['id']);

            } elseif (!empty($row['code']) && $attribute_group = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."attribute_groups where code = '". database::input($row['code']) ."' limit 1;"))) {
              $attribute_group = new ent_attribute_group($attribute_group['id']);

            } elseif (!empty($row['group_name']) && $attribute_group = database::fetch(database::query("select group_id as id from ". DB_TABLE_PREFIX ."attribute_groups_info where name = '". database::input($row['group_name']) ."' and language_code = '". database::input($row['language_code']) ."' limit 1;"))) {
              $attribute_group = new ent_attribute_group($attribute_group['id']);
            }

            if (!empty($attribute_group->data['id'])) {

              if (empty($_POST['overwrite'])) {
                echo "Skip updating existing attribute group on line $line" . PHP_EOL;
                continue 2;
              }

              echo "Updating existing attribute group on line $line" . PHP_EOL;
              $updated++;

            } else {

              if (empty($_POST['insert'])) {
                echo "Skip inserting new attribute group on line $line" . PHP_EOL;
                continue 2;
              }

              echo "Inserting new attribute group on line $line" . PHP_EOL;
              $inserted++;

              if (!empty($row['group_id'])) {
                database::query(
                  "insert into ". DB_TABLE_PREFIX ."attribute_groups
                  (id)
                  values (". (int)$row['group_id'] .");"
                );
              }
            }

          // Set attribute data
            if (isset($row['group_code'])) $attribute_group->data['code'] = $row['group_code'];
            if (isset($row['group_name'])) $attribute_group->data['name'][$row['language_code']] = $row['group_name'];
            if (isset($row['sort'])) $attribute_group->data['sort'] = $row['sort'];

            if (!empty($row['value_id'])) {
              $value_key = array_search($row['value_id'], array_column($attribute_group->data['values'], 'id', 'id'));

            } else if (!empty($row['value_name'])) {
              foreach ($attribute_group->data['values'] as $key => $value) {
                if ($value['name'][$row['language_code']] == $row['value_name']) {
                  $value_key = $row['value_id'];
                  break;
                }
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
              $campaign_query = database::query(
                "select id from ". DB_TABLE_PREFIX ."products_campaigns
                where id = ". (int)$row['id'] ."
                limit 1;"
              );
              $campaign = database::fetch($campaign_query);
            }

            if (!empty($campaign['id'])) {

              if (empty($_POST['overwrite'])) {
                echo "Skip updating existing campaign on line $line" . PHP_EOL;
                continue 2;
              }

              echo "Updating existing campaign on line $line" . PHP_EOL;
              $updated++;

            } else {

              if (empty($_POST['insert'])) {
                echo "Skip inserting new campaign on line $line" . PHP_EOL;
                continue 2;
              }

              echo "Inserting new campaign on line $line" . PHP_EOL;
              $inserted++;

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
            if (!empty($row['id']) && $category = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."categories where id = ". (int)$row['id'] ." limit 1;"))) {
              $category = new ent_category($category['id']);

            } elseif (!empty($row['code']) && $category = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."categories where code = '". database::input($row['code']) ."' limit 1;"))) {
              $category = new ent_category($category['id']);

            } elseif (!empty($row['name']) && !empty($row['language_code']) && $category = database::fetch(database::query("select category_id as id from ". DB_TABLE_PREFIX ."categories_info where name = '". database::input($row['name']) ."' and language_code = '". database::input($row['language_code']) ."' limit 1;"))) {
              $category = new ent_category($category['id']);
            }

            if (!empty($category->data['id'])) {

              if (empty($_POST['overwrite'])) {
                echo "Skip updating existing category on line $line" . PHP_EOL;
                continue 2;
              }

              echo 'Updating existing category '. (!empty($row['name']) ? $row['name'] : "on line $line") . PHP_EOL;
              $updated++;

            } else {

              if (empty($_POST['insert'])) {
                echo "Skip inserting new category on line $line" . PHP_EOL;
                continue 2;
              }

              echo 'Inserting new category: '. (!empty($row['name']) ? $row['name'] : "on line $line") . PHP_EOL;
              $inserted++;

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

            if (isset($row['new_image'])) {
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

          case 'manufacturers':

          // Find manufacturer
            if (!empty($row['id']) && $manufacturer = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."manufacturers where id = ". (int)$row['id'] ." limit 1;"))) {
              $manufacturer = new ent_manufacturer($manufacturer['id']);

            } else if (!empty($row['code']) && $manufacturer = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."manufacturers where code = '". database::input($row['code']) ."' limit 1;"))) {
              $manufacturer = new ent_manufacturer($manufacturer['id']);

            } else if (!empty($row['name']) && !empty($row['language_code']) && $manufacturer = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."manufacturers where name = '". database::input($row['name']) ."' limit 1;"))) {
              $manufacturer = new ent_manufacturer($manufacturer['id']);
            }

            if (!empty($manufacturer->data['id'])) {

              if (empty($_POST['overwrite'])) {
                echo "Skip updating existing manufacturer on line $line" . PHP_EOL;
                continue 2;
              }

              echo 'Updating existing manufacturer '. (!empty($row['name']) ? $row['name'] : "on line $line") . PHP_EOL;
              $updated++;

            } else {

              if (empty($_POST['insert'])) {
                echo "Skip inserting new manufacturer on line $line" . PHP_EOL;
                continue 2;
              }

              echo 'Inserting new manufacturer: '. (!empty($row['name']) ? $row['name'] : "on line $line") . PHP_EOL;
              $inserted++;

              if (!empty($row['id'])) {
                database::query(
                  "insert into ". DB_TABLE_PREFIX ."manufacturers (id, date_created)
                  values (". (int)$row['id'] .", '". date('Y-m-d H:i:s') ."');"
                );
                $manufacturer = new ent_manufacturer($row['id']);
              } else {
                $manufacturer = new ent_manufacturer();
              }
            }

          // Set new manufacturer data
            $fields = [
              'status',
              'code',
              'name',
              'keywords',
              'image',
              'priority',
            ];

            foreach ($fields as $field) {
              if (isset($row[$field])) $manufacturer->data[$field] = $row[$field];
            }

          // Set manufacturer info data
            if (!empty($row['language_code'])) {
              $fields = [
                'short_description',
                'description',
                'head_title',
                'h1_title',
                'meta_description',
              ];

              foreach ($fields as $field) {
                if (isset($row[$field])) $manufacturer->data[$field][$row['language_code']] = $row[$field];
              }
            }

            if (isset($row['new_image'])) {
              $manufacturer->save_image($row['new_image']);
            }

            $manufacturer->save();

            if (!empty($row['date_created'])) {
              database::query(
                "update ". DB_TABLE_PREFIX ."manufacturers
                set date_created = '". date('Y-m-d H:i:s', strtotime($row['date_created'])) ."'
                where id = ". (int)$manufacturer->data['id'] ."
                limit 1;"
              );
            }

            break;

          case 'products':

          // Find product
            if (!empty($row['id']) && $product = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."products where id = ". (int)$row['id'] ." limit 1;"))) {
              $product = new ent_product($product['id']);

            } elseif (!empty($row['code']) && $product = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."products where code = '". database::input($row['code']) ."' limit 1;"))) {
              $product = new ent_product($product['id']);

            } elseif (!empty($row['sku']) && $product = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."products where sku = '". database::input($row['sku']) ."' limit 1;"))) {
              $product = new ent_product($product['id']);

            } elseif (!empty($row['mpn']) && $product = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."products where mpn = '". database::input($row['mpn']) ."' limit 1;"))) {
              $product = new ent_product($product['id']);

            } elseif (!empty($row['gtin']) && $product = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."products where gtin = '". database::input($row['gtin']) ."' limit 1;"))) {
              $product = new ent_product($product['id']);

            } elseif (!empty($row['name']) && !empty($row['language_code']) && $product = database::fetch(database::query("select product_id as id from ". DB_TABLE_PREFIX ."products_info where name = '". database::input($row['name']) ."' and language_code = '". database::input($row['language_code']) ."' limit 1;"))) {
              $product = new ent_product($product['id']);
            }

            if (!empty($product->data['id'])) {

              if (empty($_POST['overwrite'])) {
                echo "Skip updating existing product on line $line" . PHP_EOL;
                continue 2;
              }

              echo 'Updating existing product '. (!empty($row['name']) ? $row['name'] : "on line $line") . PHP_EOL;
              $updated++;

            } else {

              if (empty($_POST['insert'])) {
                echo "Skip inserting new product on line $line" . PHP_EOL;
                continue 2;
              }

              echo 'Inserting new product: '. (!empty($row['name']) ? $row['name'] : "on line $line") . PHP_EOL;
              $inserted++;

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

            if (empty($row['manufacturer_id']) && !empty($row['manufacturer_name'])) {
              $manufacturers_query = database::query(
                "select * from ". DB_TABLE_PREFIX ."manufacturers
                where name = '". database::input($row['manufacturer_name']) ."'
                limit 1;"
              );

              if ($manufacturer = database::fetch($manufacturers_query)) {
                $row['manufacturer_id'] = $manufacturer['id'];
              } else {
                $manufacturer = new ent_manufacturer();
                $manufacturer->data['name'] = $row['manufacturer_name'];
                $manufacturer->save();
                $row['manufacturer_id'] = $manufacturer->data['id'];
              }
            }

            if (empty($row['supplier_id']) && !empty($row['supplier_id'])) {
              $suppliers_query = database::query(
                "select * from ". DB_TABLE_PREFIX ."suppliers
                where name = '". database::input($row['supplier_name']) ."'
                limit 1;"
              );
              if ($supplier = database::fetch($suppliers_query)) {
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
              'manufacturer_id',
              'supplier_id',
              'code',
              'sku',
              'mpn',
              'gtin',
              'taric',
              'tax_class_id',
              'quantity',
              'quantity_unit_id',
              'weight',
              'weight_unit',
              'dim_x',
              'dim_y',
              'dim_z',
              'length_unit',
              'purchase_price',
              'purchase_price_currency_code',
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
            if (isset($row['new_images'])) {
              foreach (explode(';', $row['new_images']) as $new_image) {

              // Workaround for remote images and allow_url_fopen = Off
                if (preg_match('#^https?://#', $new_image) && preg_match('#^(|0|false|off)$#i', ini_get('allow_url_fopen'))) {

                  $client = new wrap_http();
                  $response = $client->call('GET', $new_image);

                  if ($client->last_response['status_code'] == 200) {
                    $tmp = tempnam(sys_get_temp_dir(), '');
                    file_put_contents($tmp, $response);
                    $new_image = $tmp;
                  } else {
                    throw new Exception('Remote location '. $new_image .' returned an unexpected http response code ('. $client->last_response['status_code'] .')');
                  }
                }

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
                  'id' => isset($product->previous['attributes'][$attribute['group_id'].'-'.$attribute['value_id']]) ? $product->previous['attributes'][$attribute['group_id'].'-'.$attribute['value_id']]['id'] : null,
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
            if (!empty($row['id']) && $stock_item = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."stock_items where id = ". (int)$row['id'] ." limit 1;"))) {
              $stock_item = new ent_stock_item($stock_item['id']);

            } else if (!empty($row['code']) && $stock_item = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."stock_items where code = '". database::input($row['code']) ."' limit 1;"))) {
              $stock_item = new ent_stock_item($stock_item['id']);

            } elseif (!empty($row['sku']) && $product = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."stock_items where sku = '". database::input($row['sku']) ."' limit 1;"))) {
              $product = new ent_product($product['id']);

            } elseif (!empty($row['mpn']) && $product = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."stock_items where mpn = '". database::input($row['mpn']) ."' limit 1;"))) {
              $product = new ent_product($product['id']);

            } elseif (!empty($row['gtin']) && $product = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."stock_items where gtin = '". database::input($row['gtin']) ."' limit 1;"))) {
              $product = new ent_product($product['id']);
            }

            if (!empty($stock_item->data['id'])) {
              if (empty($_POST['overwrite'])) {
                echo "Skip updating existing stock item on line $line" . PHP_EOL;
                continue 2;
              }
              echo 'Updating existing stock item '. (!empty($row['name'][$row['language_code']]) ? $row['name'][$row['language_code']] : "on line $line") . PHP_EOL;
              $updated++;

            } else {

              if (empty($_POST['insert'])) {
                echo "Skip inserting new stock item on line $line" . PHP_EOL;
                continue 2;
              }

              echo 'Inserting new stock item: '. (!empty($row['name'][$row['language_code']]) ? $row['name'][$row['language_code']] : "on line $line") . PHP_EOL;
              $inserted++;

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

          // Set new stock item data
            $fields = [
              'status',
              'code',
              'sku',
              'mpn',
              'gtin',
              'purchase_price',
              'purchase_price_currency_code',
              'quantity',
              'weight',
              'weight_unit',
              'length',
              'width',
              'height',
              'length_unit',
              'reordered',
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
            if (!empty($row['id']) && $supplier = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."suppliers where id = ". (int)$row['id'] ." limit 1;"))) {
              $supplier = new ent_supplier($supplier['id']);

            } else if (!empty($row['code']) && $supplier = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."suppliers where code = '". database::input($row['code']) ."' limit 1;"))) {
              $supplier = new ent_supplier($supplier['id']);
            }

            if (!empty($supplier->data['id'])) {
              if (empty($_POST['overwrite'])) {
                echo "Skip updating existing supplier on line $line" . PHP_EOL;
                continue 2;
              }
              echo 'Updating existing supplier '. (!empty($row['name']) ? $row['name'] : "on line $line") . PHP_EOL;
              $updated++;

            } else {

              if (empty($_POST['insert'])) {
                echo "Skip inserting new supplier on line $line" . PHP_EOL;
                continue 2;
              }

              echo 'Inserting new supplier: '. (!empty($row['name']) ? $row['name'] : "on line $line") . PHP_EOL;
              $inserted++;

              if (!empty($row['id'])) {
                database::query(
                  "insert into ". DB_TABLE_PREFIX ."suppliers (id, date_created)
                  values (". (int)$row['id'] .", '". date('Y-m-d H:i:s') ."');"
                );
                $supplier = new ent_supplier($row['id']);
              } else {
                $suppliers = new ent_supplier();
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

      header('Content-Type: text/plain; charset='. language::$selected['charset']);
      echo ob_get_clean();
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['export'])) {

    try {

      if (empty($_POST['type'])) throw new Exception(language::translate('error_must_select_type', 'You must select type'));

      $csv = [];

        switch ($_POST['type']) {

          case 'attributes':

            if (empty($_POST['language_code'])) throw new Exception(language::translate('error_must_select_a_language', 'You must select a language'));

            $attributes_query = database::query(
                "select ag.id as group_id, ag.code as group_code, agi.name as group_name, av.id as value_id, avi.name as value_name, avi.language_code, av.priority from ". DB_TABLE_PREFIX ."attribute_values av
                left join ". DB_TABLE_PREFIX ."attribute_groups ag on (ag.id = av.group_id)
                left join ". DB_TABLE_PREFIX ."attribute_groups_info agi on (agi.group_id = av.group_id and agi.language_code = '". database::input($_POST['language_code']) ."')
                left join ". DB_TABLE_PREFIX ."attribute_values_info avi on (avi.value_id = av.id and avi.language_code = '". database::input($_POST['language_code']) ."')
                order by agi.name, av.priority;"
            );

            while ($attribute = database::fetch($attributes_query)) {
              $csv[] = $attribute;
            }

            break;

          case 'campaigns':

            $campaign_query = database::query(
              "select * from ". DB_TABLE_PREFIX ."products_campaigns
              order by product_id;"
            );

            if (!database::num_rows($campaign_query)) {

              $fields_query = database::query(
                "show fields from ". DB_TABLE_PREFIX ."products_campaigns;"
              );

              $csv[] = database::fetch($fields_query);

              break;
            }

            while ($campaign = database::fetch($campaign_query)) {
              $csv[] = $campaign;
            }

            break;

          case 'categories':

            if (empty($_POST['language_code'])) throw new Exception(language::translate('error_must_select_a_language', 'You must select a language'));

            $categories_query = database::query("select id from ". DB_TABLE_PREFIX ."categories order by parent_id;");
            while ($category = database::fetch($categories_query)) {

              $category = new ref_category($category['id'], $_POST['language_code']);

              $csv[] = [
                'id' => $category->id,
                'status' => $category->status,
                'parent_id' => $category->parent_id,
                'code' => $category->code,
                'name' => $category->name,
                'keywords' => implode(',', $category->keywords),
                'short_description' => $category->short_description,
                'description' => $category->description,
                'meta_description' => $category->meta_description,
                'head_title' => $category->head_title,
                'h1_title' => $category->h1_title,
                'image' => $category->image,
                'priority' => $category->priority,
                'language_code' => $_POST['language_code'],
              ];
            }

            break;

          case 'manufacturers':

            if (empty($_POST['language_code'])) throw new Exception(language::translate('error_must_select_a_language', 'You must select a language'));

            $manufacturers_query = database::query("select id from ". DB_TABLE_PREFIX ."manufacturers order by id;");
            while ($manufacturer = database::fetch($manufacturers_query)) {

              $manufacturer = new ref_manufacturer($manufacturer['id'], $_POST['language_code']);

              $csv[] = [
                'id' => $manufacturer->id,
                'status' => $manufacturer->status,
                'code' => $manufacturer->code,
                'name' => $manufacturer->name,
                'keywords' => implode(',', $manufacturer->keywords),
                'short_description' => $manufacturer->short_description,
                'description' => $manufacturer->description,
                'meta_description' => $manufacturer->meta_description,
                'head_title' => $manufacturer->head_title,
                'h1_title' => $manufacturer->h1_title,
                'image' => $manufacturer->image,
                'priority' => $manufacturer->priority,
                'language_code' => $_POST['language_code'],
              ];
            }

            break;

          case 'products':

            if (empty($_POST['language_code'])) throw new Exception(language::translate('error_must_select_a_language', 'You must select a language'));
            if (empty($_POST['currency_code'])) throw new Exception(language::translate('error_must_select_a_currency', 'You must select a currency'));

            $products_query = database::query(
              "select p.id from ". DB_TABLE_PREFIX ."products p
              left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input($_POST['language_code']) ."')
              order by pi.name;"
            );

            while ($product = database::fetch($products_query)) {

              $product = new ref_product($product['id'], $_POST['language_code'], $_POST['currency_code']);

              $attribute_map = function($attribute) {
                if (!empty($attribute['custom_value'])) {
                  return $attribute['group_id'] .':"'. $attribute['custom_value'] .'"';
                } else {
                  return $attribute['group_id'] .':'. $attribute['value_id'];
                }
              };

              $csv[] = [
                'id' => $product->id,
                'status' => $product->status,
                'categories' => implode(',', array_keys($product->categories)),
                'manufacturer_id' => $product->manufacturer_id,
                'supplier_id' => $product->supplier_id,
                'code' => $product->code,
                'sku' => $product->sku,
                'mpn' => $product->mpn,
                'gtin' => $product->gtin,
                'taric' => $product->taric,
                'name' => $product->name,
                'short_description' => $product->short_description,
                'description' => $product->description,
                'keywords' => implode(',', $product->keywords),
                'technical_data' => $product->technical_data,
                'head_title' => $product->head_title,
                'meta_description' => $product->meta_description,
                'images' => implode(';', $product->images),
                'attributes' => implode("\r\n", array_map($attribute_map, $product->attributes)),
                'purchase_price' => $product->purchase_price,
                'purchase_price_currency_code' => $product->purchase_price_currency_code,
                'recommended_price' => $product->recommended_price,
                'price' => $product->price,
                'tax_class_id' => $product->tax_class_id,
                'quantity' => $product->quantity,
                'quantity_unit_id' => $product->quantity_unit['id'],
                'weight' => $product->weight,
                'weight_unit' => $product->weight_unit,
                'length' => $product->length,
                'width' => $product->width,
                'height' => $product->height,
                'length_unit' => $product->length_unit,
                'delivery_status_id' => $product->delivery_status_id,
                'sold_out_status_id' => $product->sold_out_status_id,
                'language_code' => $_POST['language_code'],
                'currency_code' => $_POST['currency_code'],
                'date_valid_from' => $product->date_valid_from,
                'date_valid_to' => $product->date_valid_to,
              ];
            }

            break;

          case 'stock_items':

            $stock_items_query = database::query("select id from ". DB_TABLE_PREFIX ."stock_items order by id;");
            while ($stock_item = database::fetch($stock_items_query)) {

              $stock_item = reference::stock_item($stock_item['id']);

              $csv[] = [
                'id' => $stock_item->id,
                'status' => $stock_item->status,
                'code' => $stock_item->code,
                'sku' => $stock_item->sku,
                'mpn' => $stock_item->mpn,
                'gtin' => $stock_item->gtin,
                'name' => $stock_item->name[language::$selected],
                'purchase_price' => $stock_item->purchase_price,
                'purchase_price_currency_code' => $stock_item->purchase_price_currency_code,
                'weight' => $stock_item->weight,
                'weight_unit' => $stock_item->weight_unit,
                'length' => $stock_item->length,
                'width' => $stock_item->width,
                'height' => $stock_item->height,
                'length_unit' => $stock_item->length_unit,
                'language_code' => $_POST['language_code'],
              ];
            }

            break;

          case 'suppliers':

            $suppliers_query = database::query("select id from ". DB_TABLE_PREFIX ."suppliers order by id;");
            while ($supplier = database::fetch($suppliers_query)) {

              $supplier = reference::supplier($supplier['id']);

              $csv[] = [
                'id' => $supplier->id,
                'status' => $supplier->status,
                'code' => $supplier->code,
                'name' => $supplier->name,
                'keywords' => $supplier->keywords,
                'description' => $supplier->description,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'link' => $supplier->link,
              ];
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
  <div class="card-heading">
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
                <?php echo functions::form_draw_radio_button('type', ['campaigns', language::translate('title_campaigns', 'Campaigns')], true); ?>
                <?php echo functions::form_draw_radio_button('type', ['categories', language::translate('title_categories', 'Categories')], true); ?>
                <?php echo functions::form_draw_radio_button('type', ['manufacturers', language::translate('title_manufacturers', 'Manufacturers')], true); ?>
                <?php echo functions::form_draw_radio_button('type', ['products', language::translate('title_products', 'Products')], true); ?>
                <?php echo functions::form_draw_radio_button('type', ['stock_items', language::translate('title_stock_items', 'Stock Items')], true); ?>
                <?php echo functions::form_draw_radio_button('type', ['suppliers', language::translate('title_suppliers', 'Suppliers')], true); ?>
              </div>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_csv_file', 'CSV File'); ?></label>
              <?php echo functions::form_draw_file_field('file'); ?>
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
                <?php echo functions::form_draw_radio_button('type', ['campaigns', language::translate('title_campaigns', 'Campaigns')], true); ?>
                <?php echo functions::form_draw_radio_button('type', ['categories', language::translate('title_categories', 'Categories')], true, 'data-dependencies="language"'); ?>
                <?php echo functions::form_draw_radio_button('type', ['manufacturers', language::translate('title_manufacturers', 'Manufacturers')], true, 'data-dependencies="language"'); ?>
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
                <?php echo functions::form_draw_select_field('delimiter', [[', ('. language::translate('text_default', 'default') .')', ','], [';'], ['TAB', "\t"], ['|']], true); ?>
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
</script>