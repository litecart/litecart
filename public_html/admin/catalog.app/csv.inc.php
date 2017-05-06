<?php

  if (!empty($_POST['export_categories'])) {

    if (empty($_POST['language_code'])) notices::add('errors', language::translate('error_must_select_a_language', 'You must select a language'));

    if (empty(notices::$data['errors'])) {

      $csv = array();

      $categories_query = database::query("select id from ". DB_TABLE_CATEGORIES ." order by parent_id;");
      while ($category = database::fetch($categories_query)) {
        $category = new ref_category($category['id'], $_POST['language_code']);

        $csv[] = array(
          'id' => $category->id,
          'status' => $category->status,
          'parent_id' => $category->parent_id,
          'code' => $category->code,
          'name' => $category->name,
          'keywords' => implode(',', $product->keywords),
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

      ob_clean();

      if ($_POST['output'] == 'screen') {
        header('Content-Type: text/plain; charset='. $_POST['charset']);
      } else {
        header('Content-Type: application/csv; charset='. $_POST['charset']);
        header('Content-Disposition: attachment; filename=categories-'. $_POST['language_code'] .'.csv');
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
    }
  }

  if (!empty($_POST['import_categories'])) {

    if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {

      ob_clean();

      header('Content-Type: text/plain; charset='. language::$selected['charset']);

      echo "CSV Import\r\n"
         . "----------\r\n";

      $csv = file_get_contents($_FILES['file']['tmp_name']);

      $csv = functions::csv_decode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset']);

      $line = 0;
      foreach ($csv as $row) {
        $line++;

      // Find category
        if (!empty($row['id'])) {
          if ($category = database::fetch(database::query("select id from ". DB_TABLE_CATEGORIES ." where id = ". (int)$row['id'] ." limit 1;"))) {
            $category = new ctrl_category($category['id']);
            echo "Updating existing category ". (!empty($row['name']) ? $row['name'] : "on line $line") ."\r\n";
          } else {
            if (empty($_POST['insert_categories'])) {
              echo "[Skipped] New category on line $line was not inserted to database.\r\n";
              continue;
            }
            database::query("insert into ". DB_TABLE_CATEGORIES ." (id, date_created) values (". (int)$row['id'] .", '". date('Y-m-d H:i:s') ."');");
            $category = new ctrl_category($row['id']);
            echo 'Creating new category: '. $row['name'] . PHP_EOL;
          }

        } elseif (!empty($row['code'])) {
          if ($category = database::fetch(database::query("select id from ". DB_TABLE_CATEGORIES ." where code = '". database::input($row['code']) ."' limit 1;"))) {
            $category = new ctrl_category($category['id']);
            echo "Updating existing category ". (!empty($row['name']) ? $row['name'] : "on line $line") ."\r\n";
          } else {
            if (empty($_POST['insert_categories'])) {
              echo "[Skipped] New category on line $line was not inserted to database.\r\n";
              continue;
            }
            $category = new ctrl_category();
            echo 'Creating new category: '. $row['name'] . PHP_EOL;
          }

        } elseif (!empty($row['name']) && !empty($row['language_code'])) {
          if ($category = database::fetch(database::query("select category_id as id from ". DB_TABLE_CATEGORIES_INFO ." where name = '". database::input($row['name']) ."' and language_code = '". $row['language_code'] ."' limit 1;"))) {
            $category = new ctrl_category($category['id']);
            echo "Updating existing category ". (!empty($row['name']) ? $row['name'] : "on line $line") ."\r\n";
          } else {
            if (empty($_POST['insert_categories'])) {
              echo "[Skipped] New category on line $line was not inserted to database.\r\n";
              continue;
            }
            $category = new ctrl_category();
          }

        } else {
          echo "[Skipped] Could not identify category on line $line.\r\n";
          continue;
        }

        if (isset($row['dock'])) $row['dock'] = explode(',', $row['dock']);

      // Set default category data
        if (empty($category->data['id']) & empty($row['dock'])) {
          $category->data['dock'][] = 'tree';
        }

      // Set new category data
        foreach (array('parent_id', 'status', 'code', 'dock', 'keywords', 'image') as $field) {
          if (isset($row[$field])) $category->data[$field] = $row[$field];
        }

      // Set category info data
        foreach (array('name', 'short_description', 'description', 'head_title', 'h1_title', 'meta_description') as $field) {
          if (isset($row[$field])) $category->data[$field][$row['language_code']] = $row[$field];
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
      }

      exit;
    }
  }

  if (!empty($_POST['export_products'])) {

    if (empty($_POST['language_code'])) notices::add('errors', language::translate('error_must_select_a_language', 'You must select a language'));

    if (empty(notices::$data['errors'])) {

      $csv = array();

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
          'gtin' => $product->gtin,
          'taric' => $product->taric,
          'name' => $product->name,
          'short_description' => $product->short_description,
          'description' => $product->description,
          'keywords' => implode(',', $product->keywords),
          'attributes' => $product->attributes,
          'head_title' => $product->head_title,
          'meta_description' => $product->meta_description,
          'images' => implode(';', $product->images),
          'purchase_price' => $product->purchase_price,
          'purchase_price_currency_code' => $product->purchase_price_currency_code,
          'price' => $product->price,
          'tax_class_id' => $product->tax_class_id,
          'quantity' => $product->quantity,
          'quantity_unit_id' => $product->quantity_unit['id'],
          'weight' => $product->weight,
          'weight_class' => $product->weight_class,
          'delivery_status_id' => $product->delivery_status_id,
          'sold_out_status_id' => $product->sold_out_status_id,
          'language_code' => $_POST['language_code'],
          'currency_code' => $_POST['currency_code'],
          'date_valid_from' => $product->date_valid_from,
          'date_valid_to' => $product->date_valid_to,
        );
      }

      ob_clean();

      if ($_POST['output'] == 'screen') {
        header('Content-Type: text/plain; charset='. $_POST['charset']);
      } else {
        header('Content-Type: application/csv; charset='. $_POST['charset']);
        header('Content-Disposition: attachment; filename=products-'. $_POST['language_code'] .'.csv');
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
    }
  }

  if (!empty($_POST['import_products'])) {

    if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {

      ob_clean();

      header('Content-Type: text/plain; charset='. language::$selected['charset']);

      echo "CSV Import\r\n"
         . "----------\r\n";

      $csv = file_get_contents($_FILES['file']['tmp_name']);

      $csv = functions::csv_decode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset']);

      $line = 0;
      foreach ($csv as $row) {
        $line++;

      // Find product
        if (!empty($row['id'])) {
          if ($product = database::fetch(database::query("select id from ". DB_TABLE_PRODUCTS ." where id = ". (int)$row['id'] ." limit 1;"))) {
            $product = new ctrl_product($product['id']);
            echo "Updating existing product ". (!empty($row['name']) ? $row['name'] : "on line $line") ."\r\n";
          } else {
            if (empty($_POST['insert_products'])) {
              echo "[Skipped] New product on line $line was not inserted to database.\r\n";
              continue;
            }
            database::query("insert into ". DB_TABLE_PRODUCTS ." (id, date_created) values (". (int)$row['id'] .", '". date('Y-m-d H:i:s') ."');");
            $product = new ctrl_product($row['id']);
            echo 'Creating new product: '. $row['name'] . PHP_EOL;
          }

        } elseif (!empty($row['code'])) {
          if ($product = database::fetch(database::query("select id from ". DB_TABLE_PRODUCTS ." where code = '". database::input($row['code']) ."' limit 1;"))) {
            $product = new ctrl_product($product['id']);
            echo "Updating existing product ". (!empty($row['name']) ? $row['name'] : "on line $line") ."\r\n";
          } else {
            if (empty($_POST['insert_products'])) {
              echo "[Skipped] New product on line $line was not inserted to database.\r\n";
              continue;
            }
            $product = new ctrl_product();
            echo 'Creating new product: '. $row['name'] . PHP_EOL;
          }

        } elseif (!empty($row['sku'])) {
          if ($product = database::fetch(database::query("select id from ". DB_TABLE_PRODUCTS ." where sku = '". database::input($row['sku']) ."' limit 1;"))) {
            $product = new ctrl_product($product['id']);
            echo "Updating existing product ". (!empty($row['name']) ? $row['name'] : "on line $line") ."\r\n";
          } else {
            if (empty($_POST['insert_products'])) {
              echo "[Skipped] New product on line $line was not inserted to database.\r\n";
              continue;
            }
            $product = new ctrl_product();
            echo 'Creating new product: '. $row['name'] . PHP_EOL;
          }

        } elseif (!empty($row['gtin'])) {
          if ($product = database::fetch(database::query("select id from ". DB_TABLE_PRODUCTS ." where gtin = '". database::input($row['gtin']) ."' limit 1;"))) {
            $product = new ctrl_product($product['id']);
            echo "Updating existing product ". (!empty($row['name']) ? $row['name'] : "on line $line") ."\r\n";
          } else {
            if (empty($_POST['insert_products'])) {
              echo "[Skipped] New product on line $line was not inserted to database.\r\n";
              continue;
            }
            $product = new ctrl_product();
            echo 'Creating new product: '. $row['name'] . PHP_EOL;
          }

        } elseif (!empty($row['name']) && !empty($row['language_code'])) {
          if ($product = database::fetch(database::query("select product_id as id from ". DB_TABLE_PRODUCTS_INFO ." where name = '". database::input($row['name']) ."' and language_code = '". $row['language_code'] ."' limit 1;"))) {
            $product = new ctrl_product($product['id']);
            echo "Updating existing product ". (!empty($row['name']) ? $row['name'] : "on line $line") ."\r\n";
          } else {
            if (empty($_POST['insert_products'])) {
              echo "[Skipped] New product on line $line was not inserted to database.\r\n";
              continue;
            }
            $product = new ctrl_product();
          }

        } else {
          echo "[Skipped] Could not identify product on line $line.\r\n";
          continue;
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
            $manufacturer = new ctrl_manufacturer();
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
            $supplier = new ctrl_supplier();
            $supplier->data['name'] = $row['supplier_name'];
            $supplier->save();
            $row['supplier_id'] = $supplier->data['id'];
          }
        }

        $fields = array(
          'status',
          'categories',
          'manufacturer_id',
          'supplier_id',
          'code',
          'sku',
          'gtin',
          'taric',
          'tax_class_id',
          'keywords',
          'quantity',
          'quantity_unit_id',
          'weight',
          'weight_class',
          'purchase_price',
          'purchase_price_currency_code',
          'delivery_status_id',
          'sold_out_status_id',
          'date_valid_from',
          'date_valid_to'
        );

      // Set new product data
        foreach ($fields as $field) {
          if (isset($row[$field])) $product->data[$field] = $row[$field];
        }

        if (isset($row['categories'])) $product->data['categories'] = explode(',', str_replace(' ', '', $product->data['categories']));

      // Set price
        if (!empty($row['currency_code'])) {
          if (isset($row['price'])) $product->data['prices'][$row['currency_code']] = $row['price'];
        }

      // Set product info data
        if (!empty($row['language_code'])) {
          foreach (array('name', 'short_description', 'description', 'attributes', 'head_title', 'meta_description') as $field) {
            if (isset($row[$field])) {
              $product->data[$field][$row['language_code']] = $row[$field];
            }
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
          foreach($row['images'] as $image) {
            if (!in_array($image, $current_images)) {
              $product_images['new'.++$i] = array('filename' => $image);
            }
          }

          $product->data['images'] = $product_images;
        }

        if (isset($row['new_images'])) {
          foreach(explode(';', $row['new_images']) as $new_image) {
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
      }

      exit;
    }
  }

?>
<h1><?php echo $app_icon; ?> <?php echo language::translate('title_csv_import_export', 'CSV Import/Export'); ?></h1>

<div class="row">
  <div class="col-md-6">

    <h2><?php echo language::translate('title_categories', 'Categories'); ?></h2>

    <div class="row">
      <div class="col-md-6">
        <fieldset class="well">
          <legend><?php echo language::translate('title_import_from_csv', 'Import From CSV'); ?></legend>

          <?php echo functions::form_draw_form_begin('import_categories_form', 'post', '', true); ?>

            <div class="form-group">
              <label><?php echo language::translate('title_csv_file', 'CSV File'); ?></label>
              <?php echo functions::form_draw_file_field('file'); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
              <?php echo functions::form_draw_select_field('delimiter', array(array(language::translate('title_auto', 'Auto') .' ('. language::translate('text_default', 'default') .')', ''), array(','),  array(';'), array('TAB', "\t"), array('|')), true, false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
              <?php echo functions::form_draw_select_field('enclosure', array(array('" ('. language::translate('text_default', 'default') .')', '"')), true, false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
              <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true, false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
              <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8', false); ?>
            </div>

            <div class="form-group">
              <label><?php echo functions::form_draw_checkbox('insert_categories', 'true', true); ?> <?php echo language::translate('text_insert_new_categories', 'Insert new categories'); ?></label>
            </div>

            <?php echo functions::form_draw_button('import_categories', language::translate('title_import', 'Import'), 'submit'); ?>

          <?php echo functions::form_draw_form_end(); ?>
        </fieldset>
      </div>

      <div class="col-md-6">
        <fieldset class="well">
          <legend><?php echo language::translate('title_export_to_csv', 'Export To CSV'); ?></legend>

          <?php echo functions::form_draw_form_begin('export_categories_form', 'post'); ?>

            <div class="form-group">
              <label><?php echo language::translate('title_language', 'Language'); ?></label>
              <?php echo functions::form_draw_languages_list('language_code', true, false).' '; ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
              <?php echo functions::form_draw_select_field('delimiter', array(array(', ('. language::translate('text_default', 'default') .')', ','), array(';'), array('TAB', "\t"), array('|')), true, false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
              <?php echo functions::form_draw_select_field('enclosure', array(array('" ('. language::translate('text_default', 'default') .')', '"')), true, false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
              <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true, false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
              <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8', false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_line_ending', 'Line Ending'); ?></label>
              <?php echo functions::form_draw_select_field('eol', array(array('Win'), array('Mac'), array('Linux')), true, false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_output', 'Output'); ?></label>
              <?php echo functions::form_draw_select_field('output', array(array(language::translate('title_file', 'File'), 'file'), array(language::translate('title_screen', 'Screen'), 'screen')), true, false); ?>
            </div>

            <?php echo functions::form_draw_button('export_categories', language::translate('title_export', 'Export'), 'submit'); ?>

          <?php echo functions::form_draw_form_end(); ?>
        </fieldset>
      </div>
    </div>
  </div>

  <div class="col-md-6">

    <h2><?php echo language::translate('title_products', 'Products'); ?></h2>

    <div class="row">
      <div class="col-md-6">
        <fieldset class="well">
          <legend><?php echo language::translate('title_import_from_csv', 'Import From CSV'); ?></legend>

          <?php echo functions::form_draw_form_begin('import_products_form', 'post', '', true); ?>

            <div class="form-group">
              <label><?php echo language::translate('title_csv_file', 'CSV File'); ?></label>
              <?php echo functions::form_draw_file_field('file'); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
              <?php echo functions::form_draw_select_field('delimiter', array(array(language::translate('title_auto', 'Auto') .' ('. language::translate('text_default', 'default') .')', ''), array(','),  array(';'), array('TAB', "\t"), array('|')), true, false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
              <?php echo functions::form_draw_select_field('enclosure', array(array('" ('. language::translate('text_default', 'default') .')', '"')), true, false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
              <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true, false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
              <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8', false); ?>
            </div>

            <div class="form-group">
              <label><?php echo functions::form_draw_checkbox('insert_products', 'true', true); ?> <?php echo language::translate('text_insert_new_products', 'Insert new products'); ?></label>
            </div>

            <?php echo functions::form_draw_button('import_products', language::translate('title_import', 'Import'), 'submit'); ?>

          <?php echo functions::form_draw_form_end(); ?>
        </fieldset>
      </div>

      <div class="col-md-6">
        <fieldset class="well">
          <legend><?php echo language::translate('title_export_to_csv', 'Export To CSV'); ?></legend>

          <?php echo functions::form_draw_form_begin('export_products_form', 'post'); ?>

            <div class="form-group">
              <label><?php echo language::translate('title_language', 'Language'); ?></label>
              <?php echo functions::form_draw_languages_list('language_code', true, false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_currency', 'Currency'); ?></label>
              <?php echo functions::form_draw_currencies_list('currency_code', true, false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
              <?php echo functions::form_draw_select_field('delimiter', array(array(', ('. language::translate('text_default', 'default') .')', ','), array(';'), array('TAB', "\t"), array('|')), true, false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
              <?php echo functions::form_draw_select_field('enclosure', array(array('" ('. language::translate('text_default', 'default') .')', '"')), true, false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
              <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true, false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
              <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8', false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_line_ending', 'Line Ending'); ?></label>
              <?php echo functions::form_draw_select_field('eol', array(array('Win'), array('Mac'), array('Linux')), true, false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_output', 'Output'); ?></label>
              <?php echo functions::form_draw_select_field('output', array(array(language::translate('title_file', 'File'), 'file'), array(language::translate('title_screen', 'Screen'), 'screen')), true, false); ?>
            </div>

            <?php echo functions::form_draw_button('export_products', language::translate('title_export', 'Export'), 'submit'); ?>

          <?php echo functions::form_draw_form_end(); ?>
        </fieldset>
      </div>
    </div>
  </div>
</div>
