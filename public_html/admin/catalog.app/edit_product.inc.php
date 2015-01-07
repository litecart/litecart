<?php
  if (isset($_GET['product_id'])) {
    $product = new ctrl_product($_GET['product_id']);
    
    if (!$_POST) {
      foreach ($product->data as $key => $value) {
        $_POST[$key] = $value;
      }
    }
  } else {
    $product = new ctrl_product();
    if (!$_POST) {
      if (!empty($_GET['category_id'])) $_POST['categories'][] = $_GET['category_id'];
    }
  }
  
  if (isset($_POST['save'])) {
    
    if ($_POST['name'][language::$selected['code']] == '') notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));
    if (empty($_POST['categories'])) notices::add('errors', language::translate('error_must_select_category', 'You must select a category'));
    
    if (!empty($_POST['code']) && database::num_rows(database::query("select id from ". DB_TABLE_PRODUCTS ." where id != '". (int)$product->data['id'] ."' and code = '". database::input($_POST['code']) ."' limit 1;"))) notices::add('warnings', language::translate('error_code_database_conflict', 'Another entry with the given code already exists in the database'));
    if (!empty($_POST['sku']) && database::num_rows(database::query("select id from ". DB_TABLE_PRODUCTS ." where id != '". (int)$product->data['id'] ."' and sku = '". database::input($_POST['sku']) ."' limit 1;"))) notices::add('warnings', language::translate('error_sku_database_conflict', 'Another entry with the given SKU already exists in the database'));
    if (!empty($_POST['ean']) && database::num_rows(database::query("select id from ". DB_TABLE_PRODUCTS ." where id != '". (int)$product->data['id'] ."' and ean = '". database::input($_POST['ean']) ."' limit 1;"))) notices::add('warnings', language::translate('error_ean_database_conflict', 'Another entry with the given EAN already exists in the database'));
    if (!empty($_POST['upc']) && database::num_rows(database::query("select id from ". DB_TABLE_PRODUCTS ." where id != '". (int)$product->data['id'] ."' and upc = '". database::input($_POST['upc']) ."' limit 1;"))) notices::add('warnings', language::translate('error_upc_database_conflict', 'Another entry with the given UPC already exists in the database'));
    
    if (!notices::get('errors')) {
      
      if (!isset($_POST['status'])) $_POST['status'] = '0';
      if (!isset($_POST['images'])) $_POST['images'] = array();
      if (!isset($_POST['campaigns'])) $_POST['campaigns'] = array();
      if (!isset($_POST['options'])) $_POST['options'] = array();
      if (!isset($_POST['options_stock'])) $_POST['options_stock'] = array();
      if (!isset($_POST['product_groups'])) $_POST['product_groups'] = array();
      
      $fields = array(
        'manufacturer_id',
        'supplier_id',
        'delivery_status_id',
        'sold_out_status_id',
        'categories',
        'product_groups',
        'status',
        'date_valid_from',
        'date_valid_to',
        'quantity',
        'purchase_price',
        'prices',
        'campaigns',
        'tax_class_id',
        'code',
        'sku',
        'upc',
        'taric',
        'dim_x',
        'dim_y',
        'dim_z',
        'dim_class',
        'weight',
        'weight_class',
        'name',
        'short_description',
        'description',
        'keywords',
        'head_title',
        'meta_description',
        'meta_keywords',
        'attributes',
        'images',
        'options',
        'options_stock',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $product->data[$field] = $_POST[$field];
      }
      
      if (!empty($_FILES['new_images']['tmp_name'])) {
        foreach (array_keys($_FILES['new_images']['tmp_name']) as $key) {
          $product->add_image($_FILES['new_images']['tmp_name'][$key]);
        }
      }
      
      $product->save();
      
      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('', array('app' => $_GET['app'], 'doc' => 'catalog', 'category_id' => $_POST['categories'][0])));
      exit;
    }
  }

  if (isset($_POST['delete']) && $product) {
    $product->delete();
    notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
    header('Location: '. document::link('', array('app' => $_GET['app'], 'doc' => 'catalog', 'category_id' => $_POST['categories'][0])));
    exit();
  }
  
  document::$snippets['head_tags']['jquery-tabs'] = '<script src="'. WS_DIR_EXT .'jquery/jquery.tabs.js"></script>';
?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo (!empty($product->data['id'])) ? language::translate('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : language::translate('title_add_new_product', 'Add New Product'); ?></h1>
  
<?php
  if (isset($product->data['id'])) {
    if (!empty($product->data['images'])) {
      $image = current($product->data['images']);
      echo '<p><img src="'. functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $image['filename'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 150, 150) .'" /></p>';
      reset($product->data['images']);
    }
  }
?>
  
<?php echo functions::form_draw_form_begin(false, 'post', false, true); ?>
  
  <div class="tabs">
  
    <ul class="index">
      <li><a href="#tab-general"><?php echo language::translate('title_general', 'General'); ?></a></li>
      <li><a href="#tab-information"><?php echo language::translate('title_information', 'Information'); ?></a></li>
      <li><a href="#tab-data"><?php echo language::translate('title_data', 'Data'); ?></a></li>
      <li><a href="#tab-prices"><?php echo language::translate('title_prices', 'Prices'); ?></a></li>
      <li><a href="#tab-options"><?php echo language::translate('title_options', 'Options'); ?></a></li>
      <li><a href="#tab-options-stock"><?php echo language::translate('title_options_stock', 'Options Stock'); ?></a></li>
    </ul>
    
    <div class="content">
      <div id="tab-general">
        <table>
          <tr>
            <td><strong><?php echo language::translate('title_status', 'Status'); ?></strong><br />
              <label><?php echo functions::form_draw_checkbox('status', '1', true); ?> <?php echo language::translate('title_published', 'Published'); ?></label></td>
          </tr>
          <tr>
            <td>
              <strong><?php echo language::translate('title_name', 'Name'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true, '');
  $use_br = true;
}
?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_code', 'Code'); ?></strong><br />
              <?php echo functions::form_draw_text_field('code', true); ?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_categories', 'Categories'); ?></strong><br />
            <div style="width: 360px; max-height: 240px; overflow-y: auto;" class="input-wrapper">
              <table>
<?php
  function custom_catalog_tree($category_id=0, $depth=1) {
    
    $output = '';
    
    if ($category_id == 0) {
      $output .= '<tr>' . PHP_EOL
               . '  <td>'. functions::form_draw_checkbox('categories[]', '0', (isset($_POST['categories']) && in_array('0', $_POST['categories'], true)) ? '0' : false) .'</td>' . PHP_EOL
               . '  <td width="100%"><img src="'. WS_DIR_IMAGES .'icons/16x16/folder_opened.png" width="16" height="16" align="absbottom" /> '. language::translate('title_root', '[Root]') .'</td>' . PHP_EOL
               . '</tr>' . PHP_EOL;
    }
    
  // Output categories
    $categories_query = database::query(
      "select c.id, ci.name
      from ". DB_TABLE_CATEGORIES ." c
      left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". language::$selected['code'] ."')
      where c.parent_id = '". (int)$category_id ."'
      order by c.priority asc, ci.name asc;"
    );
    
    while ($category = database::fetch($categories_query)) {
      $output .= '<tr>' . PHP_EOL
               . '  <td>'. functions::form_draw_checkbox('categories[]', $category['id'], true) .'</td>' . PHP_EOL
               . '  <td style="padding-left: '. ($depth*16) .'px;"><img src="'. WS_DIR_IMAGES .'icons/16x16/folder_closed.png" width="16" height="16" align="absbottom" /> '. $category['name'] .'</td>' . PHP_EOL
               . '</tr>' . PHP_EOL;
               
      if (database::num_rows(database::query("select * from ". DB_TABLE_CATEGORIES ." where parent_id = '". $category['id'] ."' limit 1;")) > 0) {
        $output .= custom_catalog_tree($category['id'], $depth+1);
      }
    }
    
    database::free($categories_query);
    
    return $output;
  }
  
  echo custom_catalog_tree();
?>
                </table>
              </div>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_product_groups', 'Product Groups'); ?></strong><br />
            <div style="width: 360px; max-height: 240px; overflow-y: auto;" class="input-wrapper">
              <table>
<?php
  // Output product groups
    $product_groups_query = database::query(
      "select pg.id, pgi.name from ". DB_TABLE_PRODUCT_GROUPS ." pg
      left join ". DB_TABLE_PRODUCT_GROUPS_INFO ." pgi on (pgi.product_group_id = pg.id and pgi.language_code = '". language::$selected['code'] ."')
      order by pgi.name asc;"
    );
    if (database::num_rows($product_groups_query)) {
      while ($product_group = database::fetch($product_groups_query)) {
        echo '<tr>' . PHP_EOL
           . '  <td colspan="2"><strong>'. $product_group['name'] .'</strong></td>' . PHP_EOL
           . '</tr>' . PHP_EOL;
        $product_groups_values_query = database::query(
          "select pgv.id, pgvi.name from ". DB_TABLE_PRODUCT_GROUPS_VALUES ." pgv
          left join ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ." pgvi on (pgvi.product_group_value_id = pgv.id and pgvi.language_code = '". language::$selected['code'] ."')
          where pgv.product_group_id = '". (int)$product_group['id'] ."'
          order by pgvi.name asc;"
        );
        while ($product_group_value = database::fetch($product_groups_values_query)) {
        echo '<tr>' . PHP_EOL
           . '  <td>'. functions::form_draw_checkbox('product_groups[]', $product_group['id'].'-'.$product_group_value['id'], true) .'</td>' . PHP_EOL
           . '  <td>'. $product_group_value['name'] .'</td>' . PHP_EOL
           . '</tr>' . PHP_EOL;
        }
      }
    } else {
?>
                  <tr>
                    <td><em><?php echo language::translate('description_no_existing_product_groups', 'There are no existing product groups.'); ?></em></td>
                  </tr>
<?php
    }
?>
                </table>
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <table>
                <tr>
                  <td><strong><?php echo language::translate('title_quantity', 'Quantity'); ?></strong><br />
                    <?php echo functions::form_draw_number_field('quantity', true); ?>
                  </td>
                  <td><strong><?php echo language::translate('title_delivery_status', 'Delivery Status'); ?></strong><br />
                    <?php echo functions::form_draw_delivery_status_list('delivery_status_id', true); ?>
                  </td>
                  <td><strong><?php echo language::translate('title_sold_out_status', 'Sold Out Status'); ?></strong><br />
                    <?php echo functions::form_draw_sold_out_status_list('sold_out_status_id', true); ?>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <?php if (!empty($product->data['images'])) { ?>
          <tr>
            <td><strong><?php echo language::translate('title_product_images', 'Product Images'); ?></strong><br />
              <table id="table-images">
<?php
  if (!empty($_POST['images'])) foreach (array_keys($_POST['images']) as $key) {
?>
    <tr>
      <td><?php echo functions::form_draw_hidden_field('images['.$key.'][id]', true); ?><img src="<?php echo functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product->data['images'][$key]['filename'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 100, 75); ?>" align="left" style="margin: 5px;" /></td>
      <td><?php echo functions::form_draw_hidden_field('images['.$key.'][filename]', $_POST['images'][$key]['filename']); ?><?php echo functions::form_draw_text_field('images['.$key.'][new_filename]', isset($_POST['images'][$key]['new_filename']) ? $_POST['images'][$key]['new_filename'] : $_POST['images'][$key]['filename'], 'data-size="large"'); ?></td>
      <td><a class="move-up" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/up.png" width="16" height="16" border="0" alt="<?php echo language::translate('text_move_up', 'Move up'); ?>" /></a> <a class="move-down" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/down.png" width="16" height="16" border="0" alt="<?php echo language::translate('text_move_down', 'Move down'); ?>" /></a> <a class="remove" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" alt="<?php echo language::translate('text_remove', 'Remove'); ?>" /></a></td>
    </tr>
<?php
  }
?>
              </table>
              <script>
                $("#table-images").on("click", ".move-up, .move-down", function(event) {
                  event.preventDefault();
                  var row = $(this).closest("tr");
                  
                  if ($(this).is(".move-up") && $(row).prevAll().length > 0) {
                    $(row).insertBefore(row.prev());
                  } else if ($(this).is(".move-down") && $(row).nextAll().length > 0) {
                    $(row).insertAfter($(row).next());
                  }
                });
                
                $("#table-images").on("click", ".remove", function(event) {
                  event.preventDefault();
                  $(this).closest('tr').remove();
                });
              </script>
            </td>
          </tr>
          <?php } ?>
          <tr>
            <td><strong><?php echo language::translate('title_upload_images', 'Upload Images'); ?></strong><br />
              <table>
                <tr>
                  <td><?php echo functions::form_draw_file_field('new_images[]', 'data-size="large"'); ?></td>
                </tr>
                <tr>
                  <td><a href="#" id="add-new-image"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" alt="<?php echo language::translate('text_add', 'Add'); ?>" /></a></td>
                </tr>
              </table>
              <script>
                $("body").on("click", "#add-new-image", function(event) {
                  event.preventDefault();
                  $(this).closest("table").find("tr:last-child").before('<tr><td><?php echo str_replace(array("\r", "\n"), '', functions::form_draw_file_field('new_images[]', 'data-size="large"')); ?></td></tr>');
                });
              </script>
              </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_date_valid_from', 'Date Valid From'); ?></strong><br />
              <?php echo functions::form_draw_date_field('date_valid_from', true, 'Date Valid To'); ?></strong><br />
              <?php echo functions::form_draw_date_field('date_valid_to', true); ?></td>
          </tr>
          <?php if (!empty($product->data['id'])) { ?>
          <tr>
            <td><strong><?php echo language::translate('title_date_updated', 'Date Updated'); ?></strong><br />
              <?php echo strftime('%e %b %Y %H:%M', strtotime($product->data['date_updated'])); ?></td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_date_created', 'Date Created'); ?></strong><br />
              <?php echo strftime('%e %b %Y %H:%M', strtotime($product->data['date_created'])); ?></td>
          </tr>
          <?php } ?>
        </table>
      </div>
    
      <div id="tab-information">
        <table>
          <tr>
            <td>
              <strong><?php echo language::translate('title_manufacturer', 'Manufacturer'); ?></strong><br />
                <?php echo functions::form_draw_manufacturers_list('manufacturer_id', true); ?>
            </td>
          </tr>
          <tr>
            <td>
              <strong><?php echo language::translate('title_supplier', 'Supplier'); ?></strong><br />
                <?php echo functions::form_draw_suppliers_list('supplier_id', true); ?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_keywords', 'Keywords'); ?></strong><br />
              <?php echo functions::form_draw_text_field('keywords', true, 'data-size="large"'); ?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_short_description', 'Short Description'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_input_field($language_code, 'short_description['. $language_code .']', true, 'data-size="large"');
  $use_br = true;
}
?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_description', 'Description'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_wysiwyg_field($language_code, 'description['. $language_code .']', true, 'data-size="large" style="height: 240px;"');
  $use_br = true;
}
?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_head_title', 'Head Title'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_input_field($language_code, 'head_title['. $language_code .']', true, 'data-size="large" data-test="test"');
  $use_br = true;
}
?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_meta_description', 'Meta Description'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_input_field($language_code, 'meta_description['. $language_code .']', true, 'data-size="large"');
  $use_br = true;
}
?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_meta_keywords', 'Meta Keywords'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_input_field($language_code, 'meta_keywords['. $language_code .']', true, 'data-size="large"');
  $use_br = true;
}
?>
            </td>
          </tr>
        </table>
      </div>
      
      <div id="tab-data">
        <table>
          <tr>
            <td><strong><?php echo language::translate('title_sku', 'SKU'); ?></strong><br />
              <?php echo functions::form_draw_text_field('sku', true); ?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_upc', 'UPC'); ?></strong><br />
              <?php echo functions::form_draw_text_field('upc', true); ?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_taric', 'TARIC'); ?></strong><br />
              <?php echo functions::form_draw_text_field('taric', true); ?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_weight', 'Weight'); ?></strong><br />
              <?php echo functions::form_draw_decimal_field('weight', true); ?> <?php echo functions::form_draw_weight_classes_list('weight_class', true); ?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_dimensions', 'Dimensions'); ?></strong><br />
              <?php echo functions::form_draw_decimal_field('dim_x', true); ?> x <?php echo functions::form_draw_decimal_field('dim_y', true); ?> x <?php echo functions::form_draw_decimal_field('dim_z', true); ?> <?php echo functions::form_draw_length_classes_list('dim_class', true); ?> (<?php echo language::translate('description_width_height_length', 'width x height x length'); ?>)
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_attributes', 'Attributes'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_textarea($language_code, 'attributes['. $language_code .']', true, 'data-size="large" style="height: 120px;"');  $use_br = true;
}
?>
            </td>
          </tr>
        </table>
      </div>
      
      <div id="tab-prices">
        <h2><?php echo language::translate('title_prices', 'Prices'); ?></h2>
        
        <table>
          <tr>
            <td><strong><?php echo language::translate('title_purchase_price', 'Purchase Price'); ?></strong><br />
              <?php echo functions::form_draw_currency_field(settings::get('store_currency_code'), 'purchase_price', true); ?>
            </td>
          </tr>
        </table>
        
        <table id="table-prices">
          <tr>
            <td><strong><?php echo language::translate('title_tax_class', 'Tax Class'); ?></strong><br />
              <?php echo functions::form_draw_tax_classes_list('tax_class_id', true); ?>
            </td>
          </tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
        </table>
        
        <table>
          <tr>
            <th align="center" style="vertical-align: text-top"><?php echo language::translate('title_currency', 'Currency'); ?></th>
            <th align="center" style="vertical-align: text-top"><?php echo language::translate('title_price', 'Price'); ?></th>
            <td style="text-align: center;"><?php echo language::translate('title_net_price', 'Net Price'); ?> (<a id="net-price-tooltip" href="#">?</a>)</td>
          </tr>
          <tr>
            <td style="width: 75px;"><strong><?php echo settings::get('store_currency_code'); ?></strong></td>
            <td><?php echo functions::form_draw_currency_field(settings::get('store_currency_code'), 'prices['. settings::get('store_currency_code') .']', true, 'data-currency-price="" placeholder=""'); ?></td>
            <td><?php echo functions::form_draw_decimal_field('net_prices['. settings::get('store_currency_code') .']', '', currency::$currencies[settings::get('store_currency_code')]['decimals'], 0, null, 'placeholder=""'); ?></td>
          </tr>
<?php
foreach (currency::$currencies as $currency) {
  if ($currency['code'] == settings::get('store_currency_code')) continue;
?>
          <tr>
            <td><?php echo $currency['code']; ?></td>
            <td><?php echo functions::form_draw_currency_field($currency['code'], 'prices['. $currency['code'] .']', true, 'data-currency-price="" placeholder=""'); ?></td>
            <td><?php echo functions::form_draw_decimal_field('net_prices['. $currency['code'] .']', '', $currency['decimals'], 0, null, 'placeholder=""'); ?></td>
          </tr>
<?php
}
?>
        </table>
        <script>
          function get_tax_rate() {
            switch ($("select[name=tax_class_id]").val()) {
<?php
  $tax_classes_query = database::query(
    "select * from ". DB_TABLE_TAX_CLASSES ."
    order by name asc;"
  );
  while ($tax_class = database::fetch($tax_classes_query)) {
    echo '              case "'. $tax_class['id'] . '":'. PHP_EOL
       . '                return '. tax::get_tax(100, $tax_class['id'], settings::get('store_country_code'), settings::get('store_zone_code')) .';' . PHP_EOL;
  }
?>
              default:
                return 0;
            }
          }
          
          function get_currency_value(currency_code) {
            switch (currency_code) {
<?php
  foreach (currency::$currencies as $currency) {
    echo '              case "'. $currency['code'] .'":' . PHP_EOL
       . '                return '. $currency['value'] .';' . PHP_EOL;
  }
?>
            }
          }
          
          function get_currency_decimals(currency_code) {
            switch (currency_code) {
<?php
  foreach (currency::$currencies as $currency) {
    echo '              case "'. $currency['code'] .'":' . PHP_EOL
       . '                return '. $currency['decimals'] .';' . PHP_EOL;
  }
?>
            }
          }
          
          $("select[name='tax_class_id'], input[name^='prices']").bind("change keyup", function() {
            
            var currency_code = $(this).attr('name').replace(/^prices\[(.*)\]$/, "$1");
            var price = Number($(this).val());
            var net_price = Number($(this).val()) * (1+(get_tax_rate()/100));
            
          // Update net price
            if (net_price == 0) {
              $("input[name='net_prices["+ currency_code +"]']").val("");
            } else {
              $("input[name='net_prices["+ currency_code +"]']").val(net_price.toFixed(get_currency_decimals(currency_code)));
            }
            
            if (currency_code != '<?php echo settings::get('store_currency_code'); ?>') return;
            
          // Update system currency price
            var currency_price = price * get_currency_value(currency_code);
            var currency_net_price = net_price * get_currency_value(currency_code);
            
            if (currency_price == 0) {
              $("input[name='prices["+ currency_code +"]']").attr("placeholder", "")
            } else {
              $("input[name='prices["+ currency_code +"]']").attr("placeholder", price.toFixed(get_currency_decimals(currency_code)));
            };
            
          // Update currency prices
            $("input[name^='prices']").each(function(){
              var currency_code = $(this).attr('name').replace(/^prices\[(.*)\]$/, "$1");
              
              if (currency_code != '<?php echo settings::get('store_currency_code'); ?>') {
                
                var currency_price = price * get_currency_value(currency_code);
                var currency_net_price = net_price * get_currency_value(currency_code);
                
                if (currency_price == 0) {
                  $("input[name='prices["+ currency_code +"]']").attr("placeholder", Number(0).toFixed(get_currency_decimals(currency_code)))
                  $("input[name='net_prices["+ currency_code +"]']").attr("placeholder", Number(0).toFixed(get_currency_decimals(currency_code)))
                } else {
                  $("input[name='prices["+ currency_code +"]']").attr("placeholder", currency_price.toFixed(get_currency_decimals(currency_code)));
                  $("input[name='net_prices["+ currency_code +"]']").attr("placeholder", currency_net_price.toFixed(get_currency_decimals(currency_code)));
                };
              
              }
            });
          });
          
          $("input[name^='net_prices']").bind("change keyup", function() {
            
            var currency_code = $(this).attr('name').replace(/^net_prices\[(.*)\]$/, "$1");
            var price = Number($(this).val()) / (1+(get_tax_rate()/100));
            var net_price = Number($(this).val());
            
          // Update price
            if (price == 0) {
              $("input[name='prices["+ currency_code +"]']").val("");
            } else {
              $("input[name='prices["+ currency_code +"]']").val(price.toFixed(get_currency_decimals(currency_code)));
            }
            
            if (currency_code != '<?php echo settings::get('store_currency_code'); ?>') return;
            
          // Update system currency price
            var currency_price = price * get_currency_value(currency_code);
            var currency_net_price = net_price * get_currency_value(currency_code);
            
            if (currency_price == 0) {
              $("input[name='prices["+ currency_code +"]']").attr("placeholder", Number(0).toFixed(get_currency_decimals(currency_code)))
              $("input[name='net_prices["+ currency_code +"]']").attr("placeholder", Number(0).toFixed(get_currency_decimals(currency_code)))
            } else {
              $("input[name='prices["+ currency_code +"]']").attr("placeholder", currency_price.toFixed(get_currency_decimals(currency_code)));
              $("input[name='net_prices["+ currency_code +"]']").attr("placeholder", currency_net_price.toFixed(get_currency_decimals(currency_code)));
            };
            
          // Update currency prices
            $("input[name^='prices']").each(function() {
              var currency_code = $(this).attr('name').replace(/^prices\[(.*)\]$/, "$1");
              
              if (currency_code != '<?php echo settings::get('store_currency_code'); ?>') {
                
                var currency_price = price * get_currency_value(currency_code);
                var currency_net_price = net_price * get_currency_value(currency_code);
                
                if (currency_price == 0) {
                  $("input[name='prices["+ currency_code +"]']").attr("placeholder", Number(0).toFixed(get_currency_decimals(currency_code)))
                  $("input[name='net_prices["+ currency_code +"]']").attr("placeholder", Number(0).toFixed(get_currency_decimals(currency_code)))
                } else {
                  $("input[name='prices["+ currency_code +"]']").attr("placeholder", currency_price.toFixed(get_currency_decimals(currency_code)));
                  $("input[name='net_prices["+ currency_code +"]']").attr("placeholder", currency_price.toFixed(get_currency_decimals(currency_code)));
                };
              
              }
            });
          });
          
        // Initiate
          $("input[name^='prices']").trigger("change");
          $("input[name^='net_prices']").trigger("change");
          
          $("body").on('click', "#net-price-tooltip", function(e) {
            e.preventDefault;
            alert("<?php echo str_replace(array("\r", "\n", "\""), array('', '', "\\\""), language::translate('text_net_price_tooltip', 'The net price field helps you calculate gross price based on the store country tax. But all prices input to database are always excluding tax.')); ?>");
          });
        </script>
        
        <h2><?php echo language::translate('title_campaigns', 'Campaigns'); ?></h2>
        <table id="table-campaigns">
          <?php if (!empty($_POST['campaigns'])) foreach (array_keys($_POST['campaigns']) as $key) { ?>
          <tr>
            <td><strong><?php echo language::translate('title_start_date', 'Start Date'); ?></strong><br />
              <?php echo functions::form_draw_hidden_field('campaigns['.$key.'][id]', true) . functions::form_draw_datetime_field('campaigns['.$key.'][start_date]', true); ?>
            </td>
            <td><strong><?php echo language::translate('title_end_date', 'End Date'); ?></strong><br />
              <?php echo functions::form_draw_datetime_field('campaigns['.$key.'][end_date]', true); ?>
            </td>
            <td>- %<br />
              <?php echo functions::form_draw_decimal_field('campaigns['.$key.'][percentage]', '', 2, 0, null, 'data-size="tiny"'); ?>
            </td>
            <td><strong><?php echo settings::get('store_currency_code'); ?></strong><br />
              <?php echo functions::form_draw_currency_field(settings::get('store_currency_code'), 'campaigns['.$key.']['. settings::get('store_currency_code') .']', true, 'data-size="small"'); ?>
            </td>
<?php
  foreach (array_keys(currency::$currencies) as $currency_code) {
    if ($currency_code == settings::get('store_currency_code')) continue;
?>
            <td><?php echo $currency_code; ?><br />
              <?php echo functions::form_draw_currency_field($currency_code, 'campaigns['.$key.']['. $currency_code. ']', isset($_POST['campaigns'][$key][$currency_code]) ? number_format($_POST['campaigns'][$key][$currency_code], 4, '.', '') : '', 'data-size="small"'); ?>
            </td>
<?php
  }
?>
            <td><a id="remove-campaign" href="#"><br /><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" /></a></td>
          </tr>
          <?php } ?>
          <tr>
            <td colspan="5"><a id="add-campaign" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" /></a></td>
          </tr>
        </table>
        
        <script>
          $("body").on("keyup change", "input[name^='campaigns'][name$='[percentage]']", function() {
            var parent = $(this).closest('tr');
            
            <?php foreach (currency::$currencies as $currency) { ?>
            if ($("input[name^='prices'][name$='[<?php echo $currency['code']; ?>]']").val() > 0) {
              var value = $("input[name='prices[<?php echo $currency['code']; ?>]']").val() * ((100 - $(this).val()) / 100);
              value = Number(value).toFixed(<?php echo $currency['decimals']; ?>);
              $(parent).find("input[name$='[<?php echo $currency['code']; ?>]']").val(value);
            } else {
              $(parent).find("input[name$='[<?php echo $currency['code']; ?>]']").val('');
            }
            <?php } ?>
            
            <?php foreach (currency::$currencies as $currency) { ?>
            var value = $(parent).find("input[name^='campaigns'][name$='[<?php echo settings::get('store_currency_code'); ?>]']").val() * <?php echo $currency['value']; ?>;
            value = Number(value).toFixed(<?php echo $currency['decimals']; ?>);
            $(parent).find("input[name^='campaigns'][name$='[<?php echo $currency['code']; ?>]']").attr('placeholder', value);
            <?php } ?>
          });
          
          $("body").on("keyup change", "input[name^='campaigns'][name$='[<?php echo settings::get('store_currency_code'); ?>]']", function() {
            var parent = $(this).closest('tr');
            var percentage = ($("input[name='prices[<?php echo settings::get('store_currency_code'); ?>]']").val() - $(this).val()) / $("input[name='prices[<?php echo settings::get('store_currency_code'); ?>]']").val() * 100;
            percentage = Number(percentage).toFixed(2);
            $(parent).find("input[name$='[percentage]']").val(percentage);
            
            <?php foreach (currency::$currencies as $currency) { ?>
            var value = 0;
            value = $(parent).find("input[name^='campaigns'][name$='[<?php echo settings::get('store_currency_code'); ?>]']").val() * <?php echo $currency['value']; ?>;
            value = Number(value).toFixed(<?php echo $currency['decimals']; ?>);
            $(parent).find("input[name^='campaigns'][name$='[<?php echo $currency['code']; ?>]']").attr('placeholder', value);
            if ($(parent).find("input[name^='campaigns'][name$='[<?php echo $currency['code']; ?>]']").val() == 0) {
              $(parent).find("input[name^='campaigns'][name$='[<?php echo $currency['code']; ?>]']").val('');
            }
            <?php } ?>
          });
          $("input[name^='campaigns'][name$='[<?php echo settings::get('store_currency_code'); ?>]']").trigger("keyup");
          
          $("body").on("click", "#remove-campaign", function(event) {
            event.preventDefault();
            $(this).closest('tr').remove();
          });
          
          var new_campaign_i = 1;
          $("body").on("click", "#add-campaign", function(event) {
            event.preventDefault();
            var output = '<tr>'
                       + '  <td><strong><?php echo language::translate('title_start_date', 'Start Date'); ?></strong><br />'
                       + '    <?php echo str_replace(PHP_EOL, '', functions::form_draw_hidden_field('campaigns[new_campaign_i][id]', '') . functions::form_draw_datetime_field('campaigns[new_campaign_i][start_date]', '')); ?>'
                       + '  </td>'
                       + '  <td><strong><?php echo language::translate('title_end_date', 'End Date'); ?></strong><br />'
                       + '    <?php echo str_replace(PHP_EOL, '', functions::form_draw_datetime_field('campaigns[new_campaign_i][end_date]', '')); ?>'
                       + '  </td>'
                       + '  <td>- %<br />'
                       + '    <?php echo str_replace(PHP_EOL, '', functions::form_draw_decimal_field('campaigns[new_campaign_i][percentage]', '', 2, 0, null, 'data-size="tiny"')); ?>'
                       + '  </td>'
                       + '  <td><strong><?php echo settings::get('store_currency_code'); ?></strong><br />'
                       + '    <?php echo str_replace(PHP_EOL, '', functions::form_draw_currency_field(settings::get('store_currency_code'), 'campaigns[new_campaign_i]['. settings::get('store_currency_code') .']', '')); ?>'
                       + '  </td>'
<?php
  foreach (array_keys(currency::$currencies) as $currency_code) {
    if ($currency_code == settings::get('store_currency_code')) continue;
?>
                       + '  <td><?php echo $currency_code; ?><br />'
                       + '    <?php echo str_replace(PHP_EOL, '', functions::form_draw_currency_field($currency_code, 'campaigns[new_campaign_i]['. $currency_code .']', '', 'data-size="small"')); ?>'
                       + '  </td>'
<?php
  }
?>
                       + '  <td><a id="remove-campaign" href="#"><br /><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" /></a></td>'
                       + '</tr>';
           while ($("input[name='campaigns[new_"+new_campaign_i+"]']").length) new_campaign_i++;
            output = output.replace(/new_campaign_i/g, 'new_' + new_campaign_i);
            $("#table-campaigns tr:last").before(output);
            new_campaign_i++;
          });
        </script>
      </div>
      
      <div id="tab-options">
        <h2><?php echo language::translate('title_options', 'Options'); ?></h2>
        <table id="table-options" width="100%">
          <tr>
            <th style="vertical-align: text-top">&nbsp;</th>
            <th style="vertical-align: text-top"><?php echo language::translate('title_group', 'Group'); ?></th>
            <th style="vertical-align: text-top"><?php echo language::translate('title_value', 'Value'); ?></th>
            <th style="vertical-align: text-top"><?php echo language::translate('title_price_operator', 'Price Operator'); ?></th>
            <th style="vertical-align: text-top"><?php echo settings::get('store_currency_code'); ?></th>
<?php
  foreach (array_keys(currency::$currencies) as $currency_code) {
    if ($currency_code == settings::get('store_currency_code')) continue;
?>
            <td><?php echo $currency_code; ?></td>
<?php
  }
?>
            <th style="vertical-align: text-top">&nbsp;</th>
          </tr>
  <?php
  if (!empty($_POST['options'])) {
    foreach (array_keys($_POST['options']) as $key) {
  ?>
          <tr>
            <td><a class="add" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" title="<?php echo language::translate('text_insert_before', 'Insert before'); ?>" /></a><?php echo functions::form_draw_hidden_field('options['.$key.'][id]', true); ?></td>
            <td><?php echo functions::form_draw_option_groups_list('options['.$key.'][group_id]', true); ?></td>
            <td><?php echo functions::form_draw_option_values_list($_POST['options'][$key]['group_id'], 'options['.$key.'][value_id]', true); ?></td>
            <td><?php echo functions::form_draw_select_field('options['.$key.'][price_operator]', array('+','%','*'), $_POST['options'][$key]['price_operator'], false, 'data-size="auto"'); ?></td>
            <td><?php echo functions::form_draw_currency_field(settings::get('store_currency_code'), 'options['.$key.']['.settings::get('store_currency_code').']', true); ?></td>
<?php
      foreach (array_keys(currency::$currencies) as $currency_code) {
        if ($currency_code == settings::get('store_currency_code')) continue;
?>
            <td><?php echo str_replace(PHP_EOL, '', functions::form_draw_currency_field($currency_code, 'options['.$key.']['. $currency_code. ']', number_format($_POST['options'][$key][$currency_code], 4, '.', ''))); ?></td>
<?php
      }
?>
            <td style="text-align: right;"><a class="move-up" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/up.png" width="16" height="16" border="0" title="<?php echo language::translate('text_move_up', 'Move up'); ?>" /></a> <a class="move-down" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/down.png" width="16" height="16" border="0" title="<?php echo language::translate('text_move_down', 'Move down'); ?>" /></a> <a class="remove" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" /></a></td>
          </tr>
<?php
    }
  }
?>
          <tr>
            <td><a class="add" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" title="<?php echo language::translate('text_insert_before', 'Insert before'); ?>" /></a></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
        </table>
        <script>
          $("#table-options").on("click", ".remove", function(event) {
            event.preventDefault();
            $(this).closest('tr').remove();
          });
          
          $("#table-options").on("click", ".move-up, .move-down", function(event) {
            event.preventDefault();
            var row = $(this).closest("tr");
            if ($(this).is(".move-up") && $(row).prevAll().length > 1) {
              $(row).insertBefore($(row).prev());
            } else if ($(this).is(".move-down") && $(row).nextAll().length > 0) {
              $(row).insertAfter($(row).next());
            }
          });
          
          $("#table-options").on("change", "select[name^='options'][name$='[group_id]']", function(){
            var valueField = this.name.replace(/group/, 'value');
            $('body').css('cursor', 'wait');
            $.ajax({
              url: '<?php echo document::ilink('ajax/option_values.json'); ?>?option_group_id=' + $(this).val(),
              type: 'get',
              cache: true,
              async: true,
              dataType: 'json',
              error: function(jqXHR, textStatus, errorThrown) {
                alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
              },
              success: function(data) {
                $('select[name=\''+ valueField +'\']').html('');
                if ($('select[name=\''+ valueField +'\']').attr('disabled')) $('select[name=\''+ valueField +'\']').removeAttr('disabled');
                if (data) {
                  $.each(data, function(i, zone) {
                    $('select[name=\''+ valueField +'\']').append('<option value="'+ zone.id +'">'+ zone.name +'</option>');
                  });
                } else {
                  $('select[name=\''+ valueField +'\']').attr('disabled', 'disabled');
                }
              },
              complete: function() {
                $('body').css('cursor', 'auto');
              }
            });
          });
          
          var new_option_i = 1;
          $("#table-options").on("click", ".add", function(event) {
            event.preventDefault();
            var output = '<tr>'
                       + '  <td><a class="add" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" title="<?php echo language::translate('text_insert_before', 'Insert before'); ?>" /></a><?php echo str_replace(PHP_EOL, '', functions::form_draw_hidden_field('options[new_option_i][id]', '')); ?></td>'
                       + '  <td><?php echo str_replace(PHP_EOL, '', functions::form_draw_option_groups_list('options[new_option_i][group_id]', '')); ?></td>'
                       + '  <td><?php echo str_replace(PHP_EOL, '', functions::form_draw_select_field('options[new_option_i][value_id]', array(array('','')), '')); ?></td>'
                       + '  <td><?php echo str_replace(PHP_EOL, '', functions::form_draw_select_field('options[new_option_i][price_operator]', array('+','*'), '+', false, 'data-size="auto"')); ?></td>'
                       + '  <td><?php echo str_replace(PHP_EOL, '', functions::form_draw_currency_field(settings::get('store_currency_code'), 'options[new_option_i]['. settings::get('store_currency_code') .']', 0)); ?></td>'
<?php
  foreach (array_keys(currency::$currencies) as $currency_code) {
    if ($currency_code == settings::get('store_currency_code')) continue;
?>
                       + '  <td><?php echo str_replace(PHP_EOL, '', functions::form_draw_currency_field($currency_code, 'options[new_option_i]['. $currency_code. ']', '')); ?></td>'
<?php
  }
?>
                       + '  <td><a class="move-up" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/up.png" width="16" height="16" border="0" title="<?php echo language::translate('text_move_up', 'Move up'); ?>" /></a> <a class="move-down" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/down.png" width="16" height="16" border="0" title="<?php echo language::translate('text_move_down', 'Move down'); ?>" /></a> <a class="remove" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" title="<?php echo language::translate('title_remove', 'Remove'); ?>" /></a></td>'
                       + '</tr>';
            output = output.replace(/new_option_i/g, 'new_' + new_option_i);
            $(this).closest('tr').before(output);
            new_option_i++;
          });
        </script>
      </div>
      
      <div id="tab-options-stock">
        <h2><?php echo language::translate('title_options_stock', 'Options Stock'); ?></h2>
        <table id="table-options-stock" style="width: 100%;">
          <tr>
            <th style="vertical-align: text-top"><?php echo language::translate('title_option', 'Option'); ?></th>
            <th style="vertical-align: text-top"><?php echo language::translate('title_sku', 'SKU'); ?></th>
            <th style="vertical-align: text-top"><?php echo language::translate('title_qty', 'Qty'); ?></th>
            <th style="vertical-align: text-top"><?php echo language::translate('title_weight', 'Weight'); ?></th>
            <th style="vertical-align: text-top"><?php echo language::translate('title_dimensions', 'Dimensions'); ?></th>
            <th style="vertical-align: text-top">&nbsp;</th>
          </tr>
<?php
  if (!empty($_POST['options_stock'])) {
    foreach (array_keys($_POST['options_stock']) as $key) {
?>
          <tr>
            <td><?php echo functions::form_draw_hidden_field('options_stock['.$key.'][id]', true); ?><?php echo functions::form_draw_hidden_field('options_stock['.$key.'][combination]', true); ?>
            <?php echo functions::form_draw_hidden_field('options_stock['.$key.'][name]['. language::$selected['name'] .']', true); ?>
            <?php echo $_POST['options_stock'][$key]['name'][language::$selected['code']]; ?></td>
            <td><?php echo functions::form_draw_text_field('options_stock['.$key.'][sku]', true, 'data-size="small"'); ?></td>
            <td><?php echo functions::form_draw_number_field('options_stock['.$key.'][quantity]', true); ?></td>
            <td><?php echo functions::form_draw_decimal_field('options_stock['.$key.'][weight]', true); ?> <?php echo functions::form_draw_weight_classes_list('options_stock['.$key.'][weight_class]', true); ?></td>
            <td><?php echo functions::form_draw_decimal_field('options_stock['.$key.'][dim_x]', true); ?> x <?php echo functions::form_draw_decimal_field('options_stock['.$key.'][dim_y]', true); ?> x <?php echo functions::form_draw_decimal_field('options_stock['.$key.'][dim_z]', true); ?> <?php echo functions::form_draw_length_classes_list('options_stock['.$key.'][dim_class]', true); ?></td>
            <td style="text-align: right;"><a class="move-up" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/up.png" width="16" height="16" border="0" title="<?php echo language::translate('text_move_up', 'Move up'); ?>" /></a> <a class="move-down" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/down.png" width="16" height="16" border="0" title="<?php echo language::translate('text_move_down', 'Move down'); ?>" /></a> <a class="remove" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" /></a></td>
          </tr>
<?php
    }
  }
?>
        </table>
        <p>&nbsp;</p>
        <h3><?php echo language::translate('title_new_combination', 'New Combination'); ?></h3>
        <table id="table-option-combo">
          <tr>
            <th style="vertical-align: text-top"><?php echo language::translate('title_group', 'Group'); ?></th>
            <th style="vertical-align: text-top"><?php echo language::translate('title_value', 'Value'); ?></th>
            <th style="vertical-align: text-top">&nbsp;</th>
          </tr>
          <tr>
            <td><?php echo str_replace(PHP_EOL, '', functions::form_draw_option_groups_list('new_option[new_1][group_id]', '')); ?></td>
            <td><?php echo str_replace(PHP_EOL, '', functions::form_draw_select_field('new_option[new_1][value_id]', array(array('','')), '', false, false, 'disabled="disabled"')); ?></td>
          </tr>
          <tr>
            <td><a class="add" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" /></a></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td><?php echo functions::form_draw_button('add_option', language::translate('title_add_option', 'Add Option'), 'button'); ?></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
        </table>
        <script>
          $("#table-options-stock").on("click", ".remove", function(event) {
            event.preventDefault();
            $(this).closest('tr').remove();
          });
          
          $("#table-options-stock").on("click", ".move-up, .move-down", function(event) {
            event.preventDefault();
            var row = $(this).closest("tr");
            
            if ($(this).is(".move-up") && $(row).prevAll().length > 1) {
              $(row).insertBefore($(row).prev());
            } else if ($(this).is(".move-down") && $(row).nextAll().length > 0) {
              $(row).insertAfter($(row).next());
            }
          });
          
          var option_index = 2;
          $("#table-option-combo").on("click", ".add", function(event) {
            event.preventDefault();
            var output = '<tr>'
                       + '  <td><?php echo str_replace(PHP_EOL, '', functions::form_draw_option_groups_list('new_option[option_index][group_id]', '')); ?></td>'
                       + '  <td><?php echo str_replace(PHP_EOL, '', functions::form_draw_select_field('new_option[option_index][value_id]', array(array('','')), '', false, false, 'disabled="disabled"')); ?></td>'
                       + '  <td><a class="remove" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" title="<?php echo language::translate('title_remove', 'Remove'); ?>" /></a></td>'
                       + '</tr>';
            output = output.replace(/option_index/g, 'new_' + option_index);
            $(this).closest('tr').before(output);
            option_index++;
          });
          
          $("#table-option-combo").on("click", ".remove", function(event) {
            event.preventDefault();
            $(this).closest('tr').remove();
          });
          
          $("#table-option-combo").on("change", "select[name^='new_option'][name$='[group_id]']", function(){
            var valueField = this.name.replace(/group/, 'value');
            $('body').css('cursor', 'wait');
            $.ajax({
              url: '<?php echo document::ilink('ajax/option_values.json'); ?>?option_group_id=' + $(this).val(),
              type: 'get',
              cache: true,
              async: true,
              dataType: 'json',
              error: function(jqXHR, textStatus, errorThrown) {
                alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
              },
              success: function(data) {
                $('select[name=\''+ valueField +'\']').html('');
                if ($('select[name=\''+ valueField +'\']').attr('disabled')) $('select[name=\''+ valueField +'\']').removeAttr('disabled');
                if (data) {
                  $.each(data, function(i, zone) {
                    $('select[name=\''+ valueField +'\']').append('<option value="'+ zone.id +'">'+ zone.name +'</option>');
                  });
                } else {
                  $('select[name=\''+ valueField +'\']').attr('disabled', 'disabled');
                }
              },
              complete: function() {
                $('body').css('cursor', 'auto');
              }
            });
          });
          
          var new_option_stock_i = 1;
          $("#table-option-combo").on("click", "button[name='add_option']", function(event) {
            event.preventDefault();
            var new_option_code = '';
            var new_option_name = '';
            var use_coma = false;
            var success = $("select[name^='new_option'][name$='[group_id]']").each(function(i, groupElement) {
              var groupElement = $(groupElement);
              var valueElement = $("select[name='"+ $(groupElement).attr("name").replace(/group_id/g, 'value_id') +"']");
              if (valueElement.val() == "") {
                alert("<?php echo language::translate('error_empty_option_group', 'Error: Empty option group'); ?>");
                return false;
              }
              if (groupElement.val() == "") {
                alert("<?php echo language::translate('error_empty_option_value', 'Error: Empty option value'); ?>");
                return false;
              }
              if (use_coma) {
                new_option_code += ",";
                new_option_name += ", ";
              }
              new_option_code += groupElement.val() + "-" + valueElement.val();
              new_option_name += valueElement.find("option:selected").text();
              use_coma = true;
            });
            if (new_option_code == "") return;
            var output = '<tr>'
                       + '  <td><?php echo str_replace(PHP_EOL, '', functions::form_draw_hidden_field('options_stock[new_option_stock_i][id]', '') . functions::form_draw_hidden_field('options_stock[new_option_stock_i][combination]', 'new_option_code') . functions::form_draw_hidden_field('options_stock[new_option_stock_i][name]['. language::$selected['code'] .']', 'new_option_name')); ?>new_option_name</td>'
                       + '  <td><?php echo str_replace(PHP_EOL, '', functions::form_draw_text_field('options_stock[new_option_stock_i][sku]', '', 'data-size="small"')); ?></td>'
                       + '  <td><?php echo str_replace(PHP_EOL, '', functions::form_draw_number_field('options_stock[new_option_stock_i][quantity]', '0')); ?></td>'
                       + '  <td><?php echo str_replace(PHP_EOL, '', functions::form_draw_decimal_field('options_stock[new_option_stock_i][weight]', '0.00') .' '. functions::form_draw_weight_classes_list('options_stock[new_option_stock_i][weight_class]', '')); ?></td>'
                       + '  <td><?php echo str_replace(PHP_EOL, '', functions::form_draw_decimal_field('options_stock[new_option_stock_i][dim_x]', '0.00') .' x '. functions::form_draw_decimal_field('options_stock[new_option_stock_i][dim_y]', '0.00') .' x '. functions::form_draw_decimal_field('options_stock[new_option_stock_i][dim_z]', '0.00') .' '. functions::form_draw_length_classes_list('options_stock[new_option_stock_i][dim_class]', '')); ?></td>'
                       + '  <td><a class="move-up" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/up.png" width="16" height="16" border="0" title="<?php echo language::translate('text_move_up', 'Move up'); ?>" /></a> <a class="move-down" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/down.png" width="16" height="16" border="0" title="<?php echo language::translate('text_move_down', 'Move down'); ?>" /></a> <a class="remove" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" title="<?php echo language::translate('title_remove', 'Remove'); ?>" /></a></td>'
                       + '</tr>';
            while ($("input[name='options_stock[new_"+new_option_stock_i+"]']").length) new_option_stock_i++;
            output = output.replace(/new_option_stock_i/g, 'new_' + new_option_stock_i);
            output = output.replace(/new_option_code/g, new_option_code);
            output = output.replace(/new_option_name/g, new_option_name);
            $("#table-options-stock").append(output);
            new_option_stock_i++;
          });
        </script>
      </div>
    </div>
  </div>
  
  <p><span class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($product->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></span></p>
  
<?php echo functions::form_draw_form_end(); ?>