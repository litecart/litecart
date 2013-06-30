<?php
  
  if (!empty($_POST['import_categories'])) {
    
    if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
      
      header('Content-type: text/plain; charset='. $system->language->selected['charset']);
      echo "CSV Import\r\n"
         . "----------\r\n";
      
      $csv = file_get_contents($_FILES['file']['tmp_name']);
      
      $csv = $system->functions->csv_decode($csv);
      
      foreach ($csv as $row) {
        
      // Find category
        if (!empty($row['code'])) {
          $category_query = $system->database->query(
            "select id from ". DB_TABLE_CATEGORIES ."
            where code = '". $system->database->input($row['code']) ."'
            limit 1;"
          );
        } else {
          echo "[Skipped] Could not identify category on line $line. Missing code.\r\n";
          continue;
        }
        
        $category = $system->database->fetch($category_query);
        
      // No category, let's create it
        if (empty($category)) {
          if (empty($_POST['insert_categories'])) {
            echo "[Skipped] New category on line $line was not inserted to database.\r\n";
            continue;
          }
          $category = new ctrl_category();
          echo "Inserting new category '{$row['name']}'\r\n";
          
      // Get category
        } else {
          $category = new ctrl_category($category['id']);
          echo "Updating existing category '{$row['name']}'\r\n";
        }
        
      // Set new category data
        foreach (array('status', 'code', 'image', 'parent_id') as $field) {
          if (isset($row[$field])) $category->data[$field] = $row[$field];
        }
        
      // Set category info data
        foreach (array('name', 'short_description', 'description', 'keywords') as $field) {
          if (isset($row[$field])) $category->data[$field][$row['language_code']] = $row[$field];
        }
        
      // Set parent category
        if (!empty($row['parent_code'])) {
          $parent_category_query = $system->database->query(
            "select id from ". DB_TABLE_CATEGORIES ."
            where code = '". $system->database->input($row['parent_code']) ."'
            limit 1;"
          );
          $parent_category = $system->database->fetch($parent_category_query);
          
          if (!empty($parent_category)) {
            $category->data['parent_id'] = $parent_category['id'];
          } else {
            echo " - Could not link category to parent. Parent not found.\r\n";
          }
        }
        
        $category->save();
      }
      
      exit;
    }
  }
  
  if (!empty($_POST['export_categories'])) {
    
    if (empty($_POST['language_code'])) $system->notices->add('errors', $system->language->translate('error_must_select_a_language', 'You must select a language'));
    
    if (empty($system->notices->data['errors'])) {
      
      ob_clean();
      
      $csv = array();
      
      $categories_query = $system->database->query("select id from ". DB_TABLE_CATEGORIES ." order by parent_id;");
      while ($category = $system->database->fetch($categories_query)) {
        $category = new ref_category($category['id']);
        
        $parent_category_query = $system->database->query(
          "select * from ". DB_TABLE_CATEGORIES ."
          where id = '". (int)$category->parent_id ."'
          limit 1;"
        );
        $parent_category = $system->database->fetch($parent_category_query);
        
        $csv[] = array(
          'parent_code' => $parent_category['code'],
          'code' => $category->code,
          'name' => $category->name[$_POST['language_code']],
          'short_description' => $category->short_description[$_POST['language_code']],
          'description' => $category->description[$_POST['language_code']],
          'keywords' => $category->keywords,
          'image' => $category->image,
          'status' => $category->status,
          'priority' => $category->priority,
          'language_code' => $_POST['language_code'],
        );
      }
      
      if ($_POST['output'] == 'screen') {
        header('Content-type: text/plain; charset='. $_POST['charset']);
      } else {
        header('Content-type: application/csv; charset='. $_POST['charset']);
        header('Content-Disposition: attachment; filename=categories-'. $_POST['language_code'] .'.csv');
      }
      
      switch($_POST['eol']) {
        case 'Linux':
          echo $system->functions->csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], "\r", $_POST['charset']);
          break;
        case 'Max':
          echo $system->functions->csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], "\n", $_POST['charset']);
          break;
        case 'Win':
        default:
          echo $system->functions->csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], "\r\n", $_POST['charset']);
          break;
      }
      
      exit;
    }
  }
  
  if (!empty($_POST['import_products'])) {
    
    if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
      
      header('Content-type: text/plain; charset='. $system->language->selected['charset']);
      echo "CSV Import\r\n"
         . "----------\r\n";
      
      if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) {
        
        $map = array(
          'category_codes' => 'category_codes',
          'manufacturer_name' => 'manufacturer_name',
          'id' => 'id',
          'status' => 'status',
          'code' => 'code',
          'sku' => 'sku',
          'ean' => 'ean',
          'upc' => 'upc',
          'taric' => 'taric',
          'name' => 'name',
          'short_description' => 'short_description',
          'description' => 'description',
          'keywords' => 'keywords',
          'attributes' => 'attributes',
          'head_title' => 'head_title',
          'meta_description' => 'meta_description',
          'meta_keywords' => 'meta_keywords',
          'images' => 'images',
          'purchase_price' => 'purchase_price',
          'price' => 'price',
          'tax_class_id' => 'tax_class_id',
          'quantity' => 'quantity',
          'weight' => 'weight',
          'weight_class' => 'weight_class',
          'delivery_status_id' => 'delivery_status_id',
          'sold_out_status_id' => 'sold_out_status_id',
          'language_code' => 'language_code',
          'currency_code' => 'currency_code',
          'date_valid_from' => 'date_valid_from',
          'date_valid_to' => 'date_valid_to',
          
        // osCommerce
          //'products_model' => 'code',
          //'products_name' => 'name',
          //'products_price' => 'price',
          //...
        );
        
        $line = 0;
        while (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
          $line++;
          
        // Register keys
          if (empty($keys)) {
            $keys = array();
            foreach ($data as $key) {
              if (!in_array($key, array_keys($map))) echo '[Warning] Unknown field name \''. $key .'\'.';
              $keys[] = trim($key);
            }
            
        // Fetch data
          } else {
          
            if (count($data) != count($keys)) {
              echo "[Skipped] The number of columns doesn't match on $line.\r\n";
              continue;
            }
            
            $data = array_combine($keys, $data);
            
            foreach ($data as $key => $value) {
              $value = trim($value);
              if (strtolower($system->language->selected['charset']) == 'utf-8') {
                $data[$map[$key]] = utf8_encode($value);
              } else {
                $data[$map[$key]] = $value;
              }
            }
            
          // Find product
            if (!empty($data['id'])) {
              $product_query = $system->database->query(
                "select id from ". DB_TABLE_PRODUCTS ."
                where id = '". $system->database->input($data['id']) ."'
                limit 1;"
              );
            } elseif (!empty($data['code'])) {
              $product_query = $system->database->query(
                "select id from ". DB_TABLE_PRODUCTS ."
                where code = '". $system->database->input($data['code']) ."'
                limit 1;"
              );
            } elseif (!empty($data['sku'])) {
              $product_query = $system->database->query(
                "select id from ". DB_TABLE_PRODUCTS ."
                where sku = '". $system->database->input($data['sku']) ."'
                limit 1;"
              );
            } elseif (!empty($data['ean'])) {
              $product_query = $system->database->query(
                "select id from ". DB_TABLE_PRODUCTS ."
                where ean = '". $system->database->input($data['ean']) ."'
                limit 1;"
              );
            } elseif (!empty($data['upc'])) {
              $product_query = $system->database->query(
                "select id from ". DB_TABLE_PRODUCTS ."
                where sku = '". $system->database->input($data['upc']) ."'
                limit 1;"
              );
            } elseif (!empty($data['name']) && !empty($data['language_code'])) {
              $product_query = $system->database->query(
                "select product_id as id from ". DB_TABLE_PRODUCTS_INFO ."
                where name = '". $system->database->input($data['name']) ."'
                and language_code = '". $data['language_code'] ."'
                limit 1;"
              );
            } else {
              echo "[Skipped] Could not identify product on line $line.\r\n";
              continue;
            }
            $product = $system->database->fetch($product_query);
            
          // No product, let's create it
            if (empty($product)) {
              if (empty($_POST['insert_products'])) {
                echo "[Skipped] New product on line $line was not inserted to database.\r\n";
                continue;
              }
              $product = new ctrl_product();
              echo "Inserting new product on line $line\r\n";
              
          // Get product
            } else {
              $product = new ctrl_product($product['id']);
              echo "Updating existing product ". (!empty($data['name']) ? $data['name'] : "on line $line") ."\r\n";
            }
            
          // Set new product data
            foreach (array('status', 'code', 'sku', 'ean', 'upc', 'taric', 'tax_class_id', 'keywords', 'quantity', 'weight', 'weight_class', 'purchase_price', 'delivery_status_id', 'sold_out_status_id', 'date_valid_from', 'date_valid_to') as $field) {
              if (isset($data[$field])) $product->data[$field] = $data[$field];
            }
            
          // Set price
            if (!empty($data['currency_code'])) {
              if (isset($data['price'])) $product->data['prices'][$data['currency_code']] = $data['price'];
            }
            
          // Set product info data
            if (!empty($data['language_code'])) {
              foreach (array('name', 'short_description', 'description', 'attributes', 'head_title', 'meta_description', 'meta_keywords') as $field) {
                if (isset($data[$field])) {
                  //if (empty($data[$field])) echo "Empty name $field on line $line\r\n" ;
                  $product->data[$field][$data['language_code']] = $data[$field];
                }
              }
            }
            
            if (isset($data['images'])) {
              $data['images'] = explode(';', $data['images']);
              
              $product_images = array();
              $current_images = array();
              foreach ($product->data['images'] as $key => $image) {
                if (in_array($image['filename'], $data['images'])) {
                  $product_images[$key] = $image;
                  $current_images[] = $image['filename'];
                }
              }
              
              $i=0;
              foreach($data['images'] as $image) {
                if (!in_array($image, $current_images)) {
                  $product_images['new'.++$i] = array('filename' => $image);
                }
              }
              
              $product->data['images'] = $product_images;
            }
            
          // Set manufacturer
            if (!empty($data['manufacturer_name'])) {
              $manufacturer_query = $system->database->query(
                "select id from ". DB_TABLE_MANUFACTURERS ."
                where name = '". $system->database->input($data['manufacturer_name']) ."'
                limit 1;"
              );
              $manufacturer = $system->database->fetch($manufacturer_query);
            
              if (empty($manufacturer)) {
              
                echo "Inserting new manufacturer {$data['manufacturer_name']}\r\n";
                $manufacturer = new ctrl_manufacturer();
                $manufacturer->data['status'] = 1;
                
                foreach (array_keys($system->language->languages) as $language_code) {
                  $manufacturer->data['name'] = $data['manufacturer_name'];
                }
                
                $manufacturer->save();
                
              } else {
              
                if ($product->data['manufacturer_id'] != $manufacturer['id']) {
                  $product->data['manufacturer_id'] = $manufacturer['id'];
                }
              }
            }
            
            if (isset($data['category_codes'])) {
              
              $product->data['categories'] = array();
              
              foreach (explode(',', $data['category_codes']) as $category_code) {
                $category_code = trim($category_code);
                $category_query = $system->database->query(
                  "select id from ". DB_TABLE_CATEGORIES ."
                  where code = '". $system->database->input($category_code) ."'
                  limit 1;"
                );
                $category = $system->database->fetch($category_query);
                if (!empty($category)) {
                  $product->data['categories'][] = $category['id'];
                } else {
                  echo " - Category code $category_code not found for product on line $line.\r\n";
                }
              }
            }
            
            $product->save();
          }
        }
        fclose($handle);
      }
      exit;
    }
  }
  
  if (!empty($_POST['export_products'])) {
    
    if (empty($_POST['language_code'])) $system->notices->add('errors', $system->language->translate('error_must_select_a_language', 'You must select a language'));
    
    if (empty($system->notices->data['errors'])) {
    
      $csv = array();
      
      ob_clean();
      
      $products_query = $system->database->query(
        "select p.id from ". DB_TABLE_PRODUCTS ." p
        left join ". DB_TABLE_PRODUCTS_INFO ." pi on (pi.product_id = p.id and pi.language_code = '". $system->database->input($_POST['language_code']) ."')
        order by pi.name;"
      );
      while ($product = $system->database->fetch($products_query)) {
        $product = new ref_product($product['id']);
        
        $category_codes = array();
        foreach ($product->categories as $category_id) {
          $category_query = $system->database->query(
            "select code from ". DB_TABLE_CATEGORIES ."
            where id = '". (int)$category_id ."'
            limit 1;"
          );
          $category = $system->database->fetch($category_query);
          $category_codes[] = $category['code'];
        }
        
        $csv[] = array(
          'id' => $product->id,
          'category_codes' => implode(',', $category_codes),
          'manufacturer_name' => $product->manufacturer['name'],
          'status' => $product->status,
          'code' => $product->code,
          'sku' => $product->sku,
          'upc' => $product->upc,
          //'ean' => $product->ean,
          'taric' => $product->taric,
          'name' => $product->name[$_POST['language_code']],
          'short_description' => $product->short_description[$_POST['language_code']],
          'description' => $product->description[$_POST['language_code']],
          'keywords' => $product->keywords,
          'attributes' => $product->attributes[$_POST['language_code']],
          'head_title' => $product->head_title[$_POST['language_code']],
          'meta_description' => $product->meta_description[$_POST['language_code']],
          'meta_keywords' => $product->meta_keywords[$_POST['language_code']],
          'images' => implode(';', $product->images),
          'purchase_price' => $product->purchase_price,
          'price' => $product->price,
          'tax_class_id' => $product->tax_class_id,
          'quantity' => $product->quantity,
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

      if ($_POST['output'] == 'screen') {
        header('Content-type: text/plain; charset='. $_POST['charset']);
      } else {
        header('Content-type: application/csv; charset='. $_POST['charset']);
        header('Content-Disposition: attachment; filename=products-'. $_POST['language_code'] .'.csv');
      }
      
      switch($_POST['eol']) {
        case 'Linux':
          echo $system->functions->csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], "\r", $_POST['charset']);
          break;
        case 'Max':
          echo $system->functions->csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], "\n", $_POST['charset']);
          break;
        case 'Win':
        default:
          echo $system->functions->csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], "\r\n", $_POST['charset']);
          break;
      }
      
      exit;
    }
  }

?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo $system->language->translate('title_csv_import_export', 'CSV Import/Export'); ?></h1>

<h2><?php echo $system->language->translate('title_categories', 'Categories'); ?></h2>
<table  width="100%">
  <tr>
    <td width="50%">
      <?php echo $system->functions->form_draw_form_begin('import_categories_form', 'post', '', true); ?>
      <h3><?php echo $system->language->translate('title_import_from_csv', 'Import From CSV'); ?></h3>
      <table>
        <tr>
          <td colspan="3"><?php echo $system->language->translate('title_csv_file', 'CSV File'); ?></br>
            <?php echo $system->functions->form_draw_file_field('file'); ?></td>
        </tr>
        <tr>
          <td colspan="3"><label><?php echo $system->functions->form_draw_checkbox('insert_categories', 'true', true); ?> <?php echo $system->language->translate('text_insert_new_categories', 'Insert new categories'); ?></label></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_delimiter', 'Delimiter'); ?><br />
            <?php echo $system->functions->form_draw_select_field('delimiter', array(array(', ('. $system->language->translate('text_default', 'default') .')', ','), array(';'), array('TAB', "\t"), array('|'))); ?></td>
          <td><?php echo $system->language->translate('title_enclosure', 'Enclosure'); ?><br />
            <?php echo $system->functions->form_draw_select_field('enclosure', array(array('" ('. $system->language->translate('text_default', 'default') .')', '"'))); ?></td>
          <td><?php echo $system->language->translate('title_escape_character', 'Escape Character'); ?><br />
            <?php echo $system->functions->form_draw_select_field('escapechar', array(array('" ('. $system->language->translate('text_default', 'default') .')', '"'), array('\\', '\\'))); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_charset', 'Charset'); ?><br />
            <?php echo $system->functions->form_draw_select_field('charset', array(array('UTF-8'), array('ISO-8859-1'))); ?></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td colspan="3"><?php echo $system->functions->form_draw_button('import_categories', $system->language->translate('title_import', 'Import'), 'submit'); ?></td>
        </tr>
      </table>
      <?php echo $system->functions->form_draw_form_end(); ?>
    </td>
    <td width="50%">
      <?php echo $system->functions->form_draw_form_begin('export_categories_form', 'post'); ?>
      <h3><?php echo $system->language->translate('title_export_to_csv', 'Export To CSV'); ?></h3>
        <table style="margin: -5px;">
          <tr>
            <td colspan="3"><?php echo $system->language->translate('title_language', 'Language'); ?><br />
              <?php echo $system->functions->form_draw_languages_list('language_code').' '; ?></td>
          </tr>
          <tr>
            <td><?php echo $system->language->translate('title_delimiter', 'Delimiter'); ?><br />
              <?php echo $system->functions->form_draw_select_field('delimiter', array(array(', ('. $system->language->translate('text_default', 'default') .')', ','), array(';'), array('TAB', "\t"), array('|'))); ?></td>
            <td><?php echo $system->language->translate('title_enclosure', 'Enclosure'); ?><br />
              <?php echo $system->functions->form_draw_select_field('enclosure', array(array('" ('. $system->language->translate('text_default', 'default') .')', '"'))); ?></td>
            <td><?php echo $system->language->translate('title_escape_character', 'Escape Character'); ?><br />
              <?php echo $system->functions->form_draw_select_field('escapechar', array(array('" ('. $system->language->translate('text_default', 'default') .')', '"'), array('\\', '\\'))); ?></td>
          </tr>
          <tr>
            <td><?php echo $system->language->translate('title_charset', 'Charset'); ?><br />
              <?php echo $system->functions->form_draw_select_field('charset', array(array('UTF-8'), array('ISO-8859-1'))); ?></td>
            <td><?php echo $system->language->translate('title_line_ending', 'Line Ending'); ?><br />
              <?php echo $system->functions->form_draw_select_field('eol', array(array('Win'), array('Mac'), array('Linux'))); ?></td>
            <td><?php echo $system->language->translate('title_output', 'Output'); ?><br />
              <?php echo $system->functions->form_draw_select_field('output', array(array($system->language->translate('title_file', 'File'), 'file'), array($system->language->translate('title_screen', 'Screen'), 'screen'))); ?></td>
          </tr>
          <tr>
            <td colspan="3"><?php echo $system->functions->form_draw_button('export_categories', $system->language->translate('title_export', 'Export'), 'submit'); ?></td>
          </tr>
        </table>
      <?php echo $system->functions->form_draw_form_end(); ?>
    </td>
  </tr>
</table>

<hr />

<h2><?php echo $system->language->translate('title_products', 'Products'); ?></h2>
<table  width="100%">
  <tr>
    <td width="50%"><?php echo $system->functions->form_draw_form_begin('import_products_form', 'post', '', true); ?>
      <h3><?php echo $system->language->translate('title_import_from_csv', 'Import From CSV'); ?></h3>
      <table>
        <tr>
          <td colspan="3"><?php echo $system->language->translate('title_csv_file', 'CSV File'); ?></br>
            <?php echo $system->functions->form_draw_file_field('file'); ?></td>
        </tr>
        <tr>
          <td colspan="3"><label><?php echo $system->functions->form_draw_checkbox('insert_products', 'true', true); ?> <?php echo $system->language->translate('text_insert_new_products', 'Insert new products'); ?></label></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_delimiter', 'Delimiter'); ?><br />
            <?php echo $system->functions->form_draw_select_field('delimiter', array(array(', ('. $system->language->translate('text_default', 'default') .')', ','), array(';'), array('TAB', "\t"), array('|'))); ?></td>
          <td><?php echo $system->language->translate('title_enclosure', 'Enclosure'); ?><br />
            <?php echo $system->functions->form_draw_select_field('enclosure', array(array('" ('. $system->language->translate('text_default', 'default') .')', '"'))); ?></td>
          <td><?php echo $system->language->translate('title_escape_character', 'Escape Character'); ?><br />
            <?php echo $system->functions->form_draw_select_field('escapechar', array(array('" ('. $system->language->translate('text_default', 'default') .')', '"'), array('\\', '\\'))); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_charset', 'Charset'); ?><br />
            <?php echo $system->functions->form_draw_select_field('charset', array(array('UTF-8'), array('ISO-8859-1'))); ?></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td colspan="3"><?php echo $system->functions->form_draw_button('import_products', $system->language->translate('title_import', 'Import'), 'submit'); ?></td>
        </tr>
      </table>
      <?php echo $system->functions->form_draw_form_end(); ?></td>
    <td width="50%"><?php echo $system->functions->form_draw_form_begin('export_products_form', 'post'); ?>
      <h3><?php echo $system->language->translate('title_export_to_csv', 'Export To CSV'); ?></h3>
      <table>
        <tr>
          <td><?php echo $system->language->translate('title_language', 'Language'); ?><br />
            <?php echo $system->functions->form_draw_languages_list('language_code'); ?>
          </td>
          <td><?php echo $system->language->translate('title_currency', 'Currency'); ?><br />
            <?php echo $system->functions->form_draw_currencies_list('currency_code'); ?>
          </td>
          <td>
          </td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_delimiter', 'Delimiter'); ?><br />
            <?php echo $system->functions->form_draw_select_field('delimiter', array(array(', ('. $system->language->translate('text_default', 'default') .')', ','), array(';'), array('TAB', "\t"), array('|'))); ?></td>
          <td><?php echo $system->language->translate('title_enclosure', 'Enclosure'); ?><br />
            <?php echo $system->functions->form_draw_select_field('enclosure', array(array('" ('. $system->language->translate('text_default', 'default') .')', '"'))); ?></td>
          <td><?php echo $system->language->translate('title_escape_character', 'Escape Character'); ?><br />
            <?php echo $system->functions->form_draw_select_field('escapechar', array(array('" ('. $system->language->translate('text_default', 'default') .')', '"'), array('\\', '\\'))); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_charset', 'Charset'); ?><br />
            <?php echo $system->functions->form_draw_select_field('charset', array(array('UTF-8'), array('ISO-8859-1'))); ?></td>
          <td><?php echo $system->language->translate('title_line_ending', 'Line Ending'); ?><br />
            <?php echo $system->functions->form_draw_select_field('eol', array(array('Win'), array('Mac'), array('Linux'))); ?></td>
          <td><?php echo $system->language->translate('title_output', 'Output'); ?><br />
            <?php echo $system->functions->form_draw_select_field('output', array(array($system->language->translate('title_file', 'File'), 'file'), array($system->language->translate('title_screen', 'Screen'), 'screen'))); ?></td>
        </tr>
        <tr>
          <td colspan="3"><?php echo $system->functions->form_draw_button('export_products', $system->language->translate('title_export', 'Export'), 'submit'); ?></td>
        </tr>
      </table>
      <?php echo $system->functions->form_draw_form_end(); ?></td>
  </tr>
</table>
<p>&nbsp;</p>
