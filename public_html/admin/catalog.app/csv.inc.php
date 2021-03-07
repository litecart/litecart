<?php

  document::$snippets['title'][] = language::translate('title_import_export_csv', 'Import/Export CSV');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
  breadcrumbs::add(language::translate('title_import_export_csv', 'Import/Export CSV'));

  if (isset($_POST['import'])) {

    try {
      if (!isset($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
        throw new Exception(language::translate('error_must_select_file_to_upload', 'You must select a file to upload'));
      }

      ob_clean();

      header('Content-Type: text/plain; charset='. language::$selected['charset']);

      echo "CSV Import\r\n"
         . "----------\r\n";

      $csv = file_get_contents($_FILES['file']['tmp_name']);

      if (!$csv = functions::csv_decode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'])) {
        throw new Exception(language::translate('error_failed_decoding_csv', 'Failed decoding CSV'));
      }

      $updated = 0;
      $inserted = 0;
      $line = 0;

      foreach ($csv as $row) {
        $line++;

        switch ($_POST['type']) {

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
              if (empty($_POST['overwrite'])) continue 2;
              echo "Updating existing group price on line $line" . PHP_EOL;
              $updated++;

            } else {
              if (empty($_POST['insert'])) continue 2;
              echo "Creating new group price on line $line" . PHP_EOL;
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
            if (!empty($row['id']) && $category = database::fetch(database::query("select id from ". DB_TABLE_CATEGORIES ." where id = ". (int)$row['id'] ." limit 1;"))) {
              $category = new ent_category($category['id']);

            } elseif (!empty($row['code']) && $category = database::fetch(database::query("select id from ". DB_TABLE_CATEGORIES ." where code = '". database::input($row['code']) ."' limit 1;"))) {
              $category = new ent_category($category['id']);

            } elseif (!empty($row['name']) && !empty($row['language_code']) && $category = database::fetch(database::query("select category_id as id from ". DB_TABLE_CATEGORIES_INFO ." where name = '". database::input($row['name']) ."' and language_code = '". database::input($row['language_code']) ."' limit 1;"))) {
              $category = new ent_category($category['id']);
            }

            if (!empty($category->data['id'])) {
              if (empty($_POST['overwrite'])) continue 2;
              echo 'Updating existing category '. (!empty($row['name']) ? $row['name'] : "on line $line") . PHP_EOL;
              $updated++;

            } else {
              if (empty($_POST['insert'])) continue 2;
              echo 'Creating new category: '. (!empty($row['name']) ? $row['name'] : "on line $line") . PHP_EOL;
              $inserted++;

              if (!empty($row['id'])) {
                database::query(
                  "insert into ". DB_TABLE_CATEGORIES ." (id, date_created)
                  values (". (int)$row['id'] .", '". date('Y-m-d H:i:s') ."');"
                );
                $category = new ent_category($row['id']);
              } else {
                $category = new ent_category();
              }
            }

          // Set new category data
            $fields = array(
              'parent_id',
              'status',
              'code',
              'keywords',
              'image',
              'priority',
            );

            foreach ($fields as $field) {
              if (isset($row[$field])) $category->data[$field] = $row[$field];
            }

          // Set category info data
            if (!empty($row['language_code'])) {
              $fields = array(
                'name',
                'short_description',
                'description',
                'head_title',
                'h1_title',
                'meta_description',
              );

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
                "update ". DB_TABLE_CATEGORIES ."
                set date_created = '". date('Y-m-d H:i:s', strtotime($row['date_created'])) ."'
                where id = ". (int)$category->data['id'] ."
                limit 1;"
              );
            }

            break;

          case 'manufacturers':

          // Find manufacturer
            if (!empty($row['id']) && $manufacturer = database::fetch(database::query("select id from ". DB_TABLE_MANUFACTURERS ." where id = ". (int)$row['id'] ." limit 1;"))) {
              $manufacturer = new ent_manufacturer($manufacturer['id']);

            } else if (!empty($row['code']) && $manufacturer = database::fetch(database::query("select id from ". DB_TABLE_MANUFACTURERS ." where code = '". database::input($row['code']) ."' limit 1;"))) {
              $manufacturer = new ent_manufacturer($manufacturer['id']);

            } else if (!empty($row['name']) && !empty($row['language_code']) && $manufacturer = database::fetch(database::query("select id from ". DB_TABLE_MANUFACTURERS ." where name = '". database::input($row['name']) ."' limit 1;"))) {
              $manufacturer = new ent_manufacturer($manufacturer['id']);
            }

            if (!empty($manufacturer->data['id'])) {
              if (empty($_POST['overwrite'])) continue 2;
              echo 'Updating existing manufacturer '. (!empty($row['name']) ? $row['name'] : "on line $line") . PHP_EOL;
              $updated++;

            } else {
              if (empty($_POST['insert'])) continue 2;
              echo 'Creating new manufacturer: '. (!empty($row['name']) ? $row['name'] : "on line $line") . PHP_EOL;
              $inserted++;

              if (!empty($row['id'])) {
                database::query(
                  "insert into ". DB_TABLE_MANUFACTURERS ." (id, date_created)
                  values (". (int)$row['id'] .", '". date('Y-m-d H:i:s') ."');"
                );
                $manufacturer = new ent_manufacturer($row['id']);
              } else {
                $manufacturer = new ent_manufacturer();
              }
            }

          // Set new manufacturer data
            $fields = array(
              'status',
              'code',
              'name',
              'keywords',
              'image',
              'priority',
            );

            foreach ($fields as $field) {
              if (isset($row[$field])) $manufacturer->data[$field] = $row[$field];
            }

          // Set manufacturer info data
            if (!empty($row['language_code'])) {
              $fields = array(
                'short_description',
                'description',
                'head_title',
                'h1_title',
                'meta_description',
              );

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
                "update ". DB_TABLE_MANUFACTURERS ."
                set date_created = '". date('Y-m-d H:i:s', strtotime($row['date_created'])) ."'
                where id = ". (int)$manufacturer->data['id'] ."
                limit 1;"
              );
            }

            break;

          case 'products':

          // Find product
            if (!empty($row['id']) && $product = database::fetch(database::query("select id from ". DB_TABLE_PRODUCTS ." where id = ". (int)$row['id'] ." limit 1;"))) {
              $product = new ent_product($product['id']);

            } elseif (!empty($row['code']) && $product = database::fetch(database::query("select id from ". DB_TABLE_PRODUCTS ." where code = '". database::input($row['code']) ."' limit 1;"))) {
              $product = new ent_product($product['id']);

            } elseif (!empty($row['sku']) && $product = database::fetch(database::query("select id from ". DB_TABLE_PRODUCTS ." where sku = '". database::input($row['sku']) ."' limit 1;"))) {
              $product = new ent_product($product['id']);

            } elseif (!empty($row['mpn']) && $product = database::fetch(database::query("select id from ". DB_TABLE_PRODUCTS ." where mpn = '". database::input($row['mpn']) ."' limit 1;"))) {
              $product = new ent_product($product['id']);

            } elseif (!empty($row['gtin']) && $product = database::fetch(database::query("select id from ". DB_TABLE_PRODUCTS ." where gtin = '". database::input($row['gtin']) ."' limit 1;"))) {
              $product = new ent_product($product['id']);

            } elseif (!empty($row['name']) && !empty($row['language_code']) && $product = database::fetch(database::query("select product_id as id from ". DB_TABLE_PRODUCTS_INFO ." where name = '". database::input($row['name']) ."' and language_code = '". database::input($row['language_code']) ."' limit 1;"))) {
              $product = new ent_product($product['id']);
            }

            if (!empty($product->data['id'])) {
              if (empty($_POST['overwrite'])) continue 2;
              echo 'Updating existing product '. (!empty($row['name']) ? $row['name'] : "on line $line") . PHP_EOL;
              $updated++;

            } else {
              if (empty($_POST['insert'])) continue 2;
              echo 'Creating new product: '. (!empty($row['name']) ? $row['name'] : "on line $line") . PHP_EOL;
              $inserted++;

              if (!empty($row['id'])) {
                database::query(
                  "insert into ". DB_TABLE_PRODUCTS ." (id, date_created)
                  values (". (int)$row['id'] .", '". date('Y-m-d H:i:s') ."');"
                );
                $product = new ent_product($row['id']);
              } else {
                $product = new ent_product();
              }
            }

            if (empty($row['manufacturer_id']) && !empty($row['manufacturer_name'])) {
              $manufacturers_query = database::query(
                "select * from ". DB_TABLE_MANUFACTURERS ."
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
                "select * from ". DB_TABLE_SUPPLIERS ."
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

            $fields = array(
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
              'weight_class',
              'dim_x',
              'dim_y',
              'dim_z',
              'dim_class',
              'purchase_price',
              'purchase_price_currency_code',
              'recommended_price',
              'delivery_status_id',
              'sold_out_status_id',
              'date_valid_from',
              'date_valid_to',
            );

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

              $fields = array(
                'name',
                'short_description',
                'description',
                'technical_data',
                'head_title',
                'meta_description',
              );

              foreach ($fields as $field) {
                if (isset($row[$field])) $product->data[$field][$row['language_code']] = $row[$field];
              }
            }

            if (isset($row['images'])) {
              $row['images'] = explode(';', $row['images']);

              $product_images = array();
              $current_images = array();
              foreach ($product->data['images'] as $key => $image) {
                if (in_array($image['filename'], $row['images'])) {
                  $product_images[$key] = $image;
                  $current_images[] = $image['filename'];
                }
              }

              $i=0;
              foreach ($row['images'] as $image) {
                if (!in_array($image, $current_images)) {
                  $product_images['new'.++$i] = array('filename' => $image);
                }
              }

              $product->data['images'] = $product_images;
            }

            if (isset($row['new_images'])) {
              foreach (explode(';', $row['new_images']) as $new_image) {
                $product->add_image($new_image);
              }
            }

            $product->save();

            if (!empty($row['date_created'])) {
              database::query(
                "update ". DB_TABLE_PRODUCTS ."
                set date_created = '". date('Y-m-d H:i:s', strtotime($row['date_created'])) ."'
                where id = ". (int)$product->data['id'] ."
                limit 1;"
              );
            }

            break;

          case 'suppliers':

          // Find supplier
            if (!empty($row['id']) && $supplier = database::fetch(database::query("select id from ". DB_TABLE_SUPPLIERS ." where id = ". (int)$row['id'] ." limit 1;"))) {
              $supplier = new ent_supplier($supplier['id']);

            } else if (!empty($row['code']) && $supplier = database::fetch(database::query("select id from ". DB_TABLE_SUPPLIERS ." where code = '". database::input($row['code']) ."' limit 1;"))) {
              $supplier = new ent_supplier($supplier['id']);
            }

            if (!empty($supplier->data['id'])) {
              if (empty($_POST['overwrite'])) continue 2;
              echo 'Updating existing supplier '. (!empty($row['name']) ? $row['name'] : "on line $line") . PHP_EOL;
              $updated++;

            } else {
              if (empty($_POST['insert'])) continue 2;
              echo 'Creating new supplier: '. (!empty($row['name']) ? $row['name'] : "on line $line") . PHP_EOL;
              $inserted++;

              if (!empty($row['id'])) {
                database::query(
                  "insert into ". DB_TABLE_SUPPLIERS ." (id, date_created)
                  values (". (int)$row['id'] .", '". date('Y-m-d H:i:s') ."');"
                );
                $supplier = new ent_supplier($row['id']);
              } else {
                $suppliers = new ent_supplier();
              }
            }

          // Set new supplier data
            $fields = array(
              'status',
              'code',
              'name',
              'description',
              'email',
              'phone',
              'link',
            );

            foreach ($fields as $field) {
              if (isset($row[$field])) $supplier->data[$field] = $row[$field];
            }

            $supplier->save();

            if (!empty($row['date_created'])) {
              database::query(
                "update ". DB_TABLE_SUPPLIERS ."
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

      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['export'])) {

    try {

      $csv = array();

        switch ($_POST['type']) {

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

            $categories_query = database::query("select id from ". DB_TABLE_CATEGORIES ." order by parent_id;");
            while ($category = database::fetch($categories_query)) {
              $category = new ref_category($category['id'], $_POST['language_code']);

              $csv[] = array(
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
              );
            }

            break;

          case 'manufacturers':

            if (empty($_POST['language_code'])) throw new Exception(language::translate('error_must_select_a_language', 'You must select a language'));

            $manufacturers_query = database::query("select id from ". DB_TABLE_MANUFACTURERS ." order by id;");
            while ($manufacturer = database::fetch($manufacturers_query)) {
              $manufacturer = new ref_manufacturer($manufacturer['id'], $_POST['language_code']);

              $csv[] = array(
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
              );
            }

          case 'products':

            if (empty($_POST['language_code'])) throw new Exception(language::translate('error_must_select_a_language', 'You must select a language'));
            if (empty($_POST['currency_code'])) throw new Exception(language::translate('error_must_select_a_currency', 'You must select a currency'));

            $products_query = database::query(
              "select p.id from ". DB_TABLE_PRODUCTS ." p
              left join ". DB_TABLE_PRODUCTS_INFO ." pi on (pi.product_id = p.id and pi.language_code = '". database::input($_POST['language_code']) ."')
              order by pi.name;"
            );

            while ($product = database::fetch($products_query)) {
              $product = new ref_product($product['id'], $_POST['language_code'], $_POST['currency_code']);

              $csv[] = array(
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
                'purchase_price' => $product->purchase_price,
                'purchase_price_currency_code' => $product->purchase_price_currency_code,
                'recommended_price' => $product->recommended_price,
                'price' => $product->price,
                'tax_class_id' => $product->tax_class_id,
                'quantity' => $product->quantity,
                'quantity_unit_id' => $product->quantity_unit['id'],
                'weight' => $product->weight,
                'weight_class' => $product->weight_class,
                'dim_x' => $product->dim_x,
                'dim_y' => $product->dim_y,
                'dim_z' => $product->dim_z,
                'dim_class' => $product->dim_class,
                'delivery_status_id' => $product->delivery_status_id,
                'sold_out_status_id' => $product->sold_out_status_id,
                'language_code' => $_POST['language_code'],
                'currency_code' => $_POST['currency_code'],
                'date_valid_from' => $product->date_valid_from,
                'date_valid_to' => $product->date_valid_to,
              );
            }

            break;

          case 'suppliers':

            $suppliers_query = database::query("select id from ". DB_TABLE_SUPPLIERS ." order by id;");
            while ($supplier = database::fetch($suppliers_query)) {
              $supplier = reference::supplier($supplier['id']);

              $csv[] = array(
                'id' => $supplier->id,
                'status' => $supplier->status,
                'code' => $supplier->code,
                'name' => $supplier->name,
                'keywords' => $supplier->keywords,
                'description' => $supplier->description,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'link' => $supplier->link,
              );
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
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_csv_import_export', 'CSV Import/Export'); ?>
  </div>

  <div class="panel-body">

    <div class="row">

      <div class="col-sm-6 col-lg-4">
        <?php echo functions::form_draw_form_begin('import_form', 'post', '', true); ?>

          <fieldset>
            <legend><?php echo language::translate('title_import', 'Import'); ?></legend>

            <div class="form-group">
              <label><?php echo language::translate('title_type', 'Type'); ?></label>
              <div>
                <div class="checkbox"><label><?php echo functions::form_draw_radio_button('type', 'campaigns', true); ?> <?php echo language::translate('title_campaigns', 'Campaigns'); ?></label></div>
                <div class="checkbox"><label><?php echo functions::form_draw_radio_button('type', 'categories', true); ?> <?php echo language::translate('title_categories', 'Categories'); ?></label></div>
                <div class="checkbox"><label><?php echo functions::form_draw_radio_button('type', 'manufacturers', true); ?> <?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></label></div>
                <div class="checkbox"><label><?php echo functions::form_draw_radio_button('type', 'products', true); ?> <?php echo language::translate('title_products', 'Products'); ?></label></div>
                <div class="checkbox"><label><?php echo functions::form_draw_radio_button('type', 'suppliers', true); ?> <?php echo language::translate('title_suppliers', 'Suppliers'); ?></label></div>
              </div>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_csv_file', 'CSV File'); ?></label>
              <?php echo functions::form_draw_file_field('file'); ?>
            </div>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
                <?php echo functions::form_draw_select_field('delimiter', array(array(language::translate('title_auto', 'Auto') .' ('. language::translate('text_default', 'default') .')', ''), array(','),  array(';'), array('TAB', "\t"), array('|')), true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
                <?php echo functions::form_draw_select_field('enclosure', array(array('" ('. language::translate('text_default', 'default') .')', '"')), true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
                <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
                <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
              </div>
            </div>

            <div class="form-group">
              <div class="checkbox"><label><?php echo functions::form_draw_checkbox('insert', '1', true); ?> <?php echo language::translate('text_insert_new_entries', 'Insert new entries'); ?></label></div>
              <div class="checkbox"><label><?php echo functions::form_draw_checkbox('overwrite', '1', true); ?> <?php echo language::translate('text_overwrite_existing_entries', 'Overwrite existing entries'); ?></label></div>
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
                <div class="checkbox"><label><?php echo functions::form_draw_radio_button('type', 'campaigns', true); ?> <?php echo language::translate('title_campaigns', 'Campaigns'); ?></label></div>
                <div class="checkbox"><label><?php echo functions::form_draw_radio_button('type', 'categories', true, 'data-dependencies="language"'); ?> <?php echo language::translate('title_categories', 'Categories'); ?></label></div>
                <div class="checkbox"><label><?php echo functions::form_draw_radio_button('type', 'manufacturers', true, 'data-dependencies="language"'); ?> <?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></label></div>
                <div class="checkbox"><label><?php echo functions::form_draw_radio_button('type', 'products', true, 'data-dependencies="currency,language"'); ?> <?php echo language::translate('title_products', 'Products'); ?></label></div>
                <div class="checkbox"><label><?php echo functions::form_draw_radio_button('type', 'suppliers', true); ?> <?php echo language::translate('title_suppliers', 'Suppliers'); ?></label></div>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_language', 'Language'); ?></label>
                <?php echo functions::form_draw_languages_list('language_code', true, false, 'required'); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_currency', 'Currency'); ?></label>
                <?php echo functions::form_draw_currencies_list('currency_code', true, false, 'required'); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
                <?php echo functions::form_draw_select_field('delimiter', array(array(', ('. language::translate('text_default', 'default') .')', ','), array(';'), array('TAB', "\t"), array('|')), true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
                <?php echo functions::form_draw_select_field('enclosure', array(array('" ('. language::translate('text_default', 'default') .')', '"')), true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
                <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
                <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_line_ending', 'Line Ending'); ?></label>
                <?php echo functions::form_draw_select_field('eol', array(array('Win'), array('Mac'), array('Linux')), true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_output', 'Output'); ?></label>
                <?php echo functions::form_draw_select_field('output', array(array(language::translate('title_file', 'File'), 'file'), array(language::translate('title_screen', 'Screen'), 'screen')), true); ?>
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
