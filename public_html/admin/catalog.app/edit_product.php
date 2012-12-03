<?php
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . 'product.inc.php');
  
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
  
  // Save data to database
  if (isset($_POST['save'])) {
    
    if ($_POST['name'][$system->language->selected['code']] == '') $system->notices->add('errors', $system->language->translate('error_must_enter_name', 'You must enter a name'));
    if (empty($_POST['categories'])) $system->notices->add('errors', $system->language->translate('error_must_select_category', 'You must select a category'));
    
    if (!empty($_POST['code']) && $system->database->num_rows($system->database->query("select id from ". DB_TABLE_PRODUCTS ." where id != '". (int)$product->data['id'] ."' and code = '". $system->database->input($_POST['code']) ."' limit 1;"))) $system->notices->add('errors', $system->language->translate('error_code_database_conflict', 'Another entry with the given code already exists in the database'));
    if (!empty($_POST['sku']) && $system->database->num_rows($system->database->query("select id from ". DB_TABLE_PRODUCTS ." where id != '". (int)$product->data['id'] ."' and sku = '". $system->database->input($_POST['sku']) ."' limit 1;"))) $system->notices->add('errors', $system->language->translate('error_ean_database_conflict', 'Another entry with the given SKU already exists in the database'));
    if (!empty($_POST['ean']) && $system->database->num_rows($system->database->query("select id from ". DB_TABLE_PRODUCTS ." where id != '". (int)$product->data['id'] ."' and ean = '". $system->database->input($_POST['ean']) ."' limit 1;"))) $system->notices->add('errors', $system->language->translate('error_ean_database_conflict', 'Another entry with the given EAN already exists in the database'));
    if (!empty($_POST['upc']) && $system->database->num_rows($system->database->query("select id from ". DB_TABLE_PRODUCTS ." where id != '". (int)$product->data['id'] ."' and ean = '". $system->database->input($_POST['upc']) ."' limit 1;"))) $system->notices->add('errors', $system->language->translate('error_ean_database_conflict', 'Another entry with the given UPC already exists in the database'));
    
    if (!$system->notices->get('errors')) {
      
      if (!isset($_POST['status'])) $_POST['status'] = '0';
      if (!isset($_POST['images'])) $_POST['images'] = array();
      if (!isset($_POST['campaigns'])) $_POST['campaigns'] = array();
      if (!isset($_POST['options'])) $_POST['options'] = array();
      if (!isset($_POST['options_stock'])) $_POST['options_stock'] = array();
      
      $fields = array(
        'manufacturer_id',
        'designer_id',
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
      
    // Save new image
      if (!empty($_FILES['new_images']['tmp_name'])) {
        foreach (array_keys($_FILES['new_images']['tmp_name']) as $key) {
          $product->add_image($_FILES['new_images']['tmp_name'][$key]);
        }
      }
      
      $product->save();
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array('app' => $_GET['app'], 'doc' => 'catalog.php', 'category_id' => $_POST['categories'][0])));
      exit;
    }
  }

  // Delete from database
  if (isset($_POST['delete']) && $product) {
    $product->delete();
    $system->notices->add('success', $system->language->translate('success_post_deleted', 'Post deleted'));
    header('Location: '. $system->document->link('', array('app' => $_GET['app'], 'doc' => 'catalog.php', 'category_id' => $_POST['categories'][0])));
    exit();
  }
  
  $system->document->snippets['head_tags']['jquery-tabs'] = '<script src="'. WS_DIR_EXT .'jquery/jquery.tabs.js"></script>';

  $system->document->snippets['head_tags']['ckeditor'] = '<script type="text/javascript" src="'. WS_DIR_EXT .'ckeditor/ckeditor.js"></script>' . PHP_EOL
                                                       . ' <script type="text/javascript" src="'. WS_DIR_EXT .'ckeditor/adapters/jquery.js"></script>' . PHP_EOL
                                                       . ' <script type="text/javascript">' . PHP_EOL
                                                       . '   $(document).ready(function() {' . PHP_EOL
                                                       . '     $("textarea[name^=description]").ckeditor({' . PHP_EOL
                                                       . '       toolbar: [' . PHP_EOL
                                                       . '         ["Source", "-", "DocProps", "Preview", "Print", "-", "Templates", "Maximize", "ShowBlocks"],' . PHP_EOL
                                                       . '         ["NumberedList", "BulletedList", "-", "Outdent", "Indent", "-", "JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"],' . PHP_EOL
                                                       . '         ["Link", "Unlink", "Anchor"],' . PHP_EOL
                                                       . '         ["Image", "Table", "HorizontalRule", "Smiley", "SpecialChar", "PageBreak"],' . PHP_EOL
                                                       . '         ["Format", "Font", "FontSize"],' . PHP_EOL
                                                       . '         ["TextColor", "BGColor"],' . PHP_EOL
                                                       . '         ["Bold", "Italic", "Underline", "Strike", "Subscript", "Superscript", "-", "RemoveFormat"],' . PHP_EOL
                                                       . ' 	    ],' . PHP_EOL
                                                       . '       entities: false,' . PHP_EOL
                                                       . '       enterMode: CKEDITOR.ENTER_P,' . PHP_EOL
                                                       . '       shiftEnterMode: CKEDITOR.ENTER_BR' . PHP_EOL
                                                       . '     });' . PHP_EOL
                                                       . '   });' . PHP_EOL
                                                       . '</script>' . PHP_EOL;
  
?>
  <h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" border="0" align="absmiddle" style="margin-right: 10px;" /><?php echo (!empty($product->data['id'])) ? $system->language->translate('title_edit_product', 'Edit Product') . ': '. $product->data['name'][$system->language->selected['code']] : $system->language->translate('title_add_new_product', 'Add New Product'); ?></h1>
  
<?php
  if (isset($product->data['id'])) {
    if (!empty($product->data['images'])) {
      $image = current($product->data['images']);
      echo '<p><img src="'. $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $image['filename'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 150, 150) .'" /></p>';
      reset($product->data['images']);
    }
  }
?>
  
  <?php echo $system->functions->form_draw_form_begin(false, 'post', false, true); ?>
  
  <div class="tabs">
  
    <ul class="index">
      <li><a href="#tab-general"><?php echo $system->language->translate('title_general', 'General'); ?></a></li>
      <li><a href="#tab-information"><?php echo $system->language->translate('title_information', 'Information'); ?></a></li>
      <li><a href="#tab-data"><?php echo $system->language->translate('title_data', 'Data'); ?></a></li>
      <li><a href="#tab-prices"><?php echo $system->language->translate('title_prices', 'Prices'); ?></a></li>
      <li><a href="#tab-options"><?php echo $system->language->translate('title_options', 'Options'); ?></a></li>
      <li><a href="#tab-options-stock"><?php echo $system->language->translate('title_options_stock', 'Options Stock'); ?></a></li>
    </ul>
    
    <div class="content">
      <div id="tab-general">
        <table border="0" cellpadding="5" cellspacing="0">
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_status', 'Status'); ?></strong><br />
              <?php echo $system->functions->form_draw_checkbox('status', '1', (isset($_POST['status'])) ? $_POST['status'] : '0'); ?> <?php echo $system->language->translate('title_published', 'Published'); ?></td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_code', 'Code'); ?></strong><br />
              <?php echo $system->functions->form_draw_input_field('code', (isset($_POST['code']) ? $_POST['code'] : ''), 'text'); ?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap">
              <strong><?php echo $system->language->translate('title_name', 'Name'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_input_field($language_code, 'name['. $language_code .']', (isset($_POST['name'][$language_code]) ? $_POST['name'][$language_code] : ''), 'text', 'style="width: 360px"');
  $use_br = true;
}
?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_categories', 'Categories'); ?></strong><br />
            <div style="max-height: 200px; overflow-y: auto;" class="input-field">
              <table cellpadding="0" cellspacing="0" border="0">
<?php
  function custom_catalog_tree($category_id=0, $depth=1) {
    global $system;
    
    $output = '';
    
    if ($category_id == 0) {
      $output .= '<tr>' . PHP_EOL
               . '  <td>'. $system->functions->form_draw_checkbox('categories[]', '0', (isset($_POST['categories']) && in_array('0', $_POST['categories'], true)) ? '0' : false) .'</td>' . PHP_EOL
               . '  <td width="100%"><img src="'. WS_DIR_IMAGES .'icons/16x16/folder_opened.png" width="16" height="16" align="absbottom" /> '. $system->language->translate('title_root', '[Root]') .'</td>' . PHP_EOL
               . '</tr>' . PHP_EOL;
    }
    
  // Output categories
    $categories_query = $system->database->query(
      "select c.id, ci.name
      from ". DB_TABLE_CATEGORIES ." c
      left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". $system->language->selected['code'] ."')
      where c.parent_id = '". (int)$category_id ."'
      order by c.priority asc, ci.name asc;"
    );
    
    while ($category = $system->database->fetch($categories_query)) {
      $output .= '<tr>' . PHP_EOL
               . '  <td>'. $system->functions->form_draw_checkbox('categories[]', $category['id'], (isset($_POST['categories']) && in_array($category['id'], $_POST['categories'])) ? $category['id'] : false) .'</td>' . PHP_EOL
               . '  <td style="padding-left: '. ($depth*16) .'px;"><img src="'. WS_DIR_IMAGES .'icons/16x16/folder_closed.png" width="16" height="16" align="absbottom" /> '. $category['name'] .'</td>' . PHP_EOL
               . '</tr>' . PHP_EOL;
               
      if ($system->database->num_rows($system->database->query("select * from ". DB_TABLE_CATEGORIES ." where parent_id = '". $category['id'] ."' limit 1;")) > 0) {
        $output .= custom_catalog_tree($category['id'], $depth+1);
      }
    }
    
    $system->database->free($categories_query);
    
    return $output;
  }
  
  echo custom_catalog_tree();
?>
                </table>
              </div>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_product_groups', 'Product Groups'); ?></strong><br />
            <div style="max-height: 200px; overflow-y: auto;" class="input-field">
              <table cellpadding="0" cellspacing="0" border="0">
<?php
  // Output product groups
    $product_groups_query = $system->database->query(
      "select pg.id, pgi.name from ". DB_TABLE_PRODUCT_GROUPS ." pg
      left join ". DB_TABLE_PRODUCT_GROUPS_INFO ." pgi on (pgi.product_group_id = pg.id and pgi.language_code = '". $system->language->selected['code'] ."')
      order by pgi.name asc;"
    );
    if ($system->database->num_rows($product_groups_query)) {
    while ($product_group = $system->database->fetch($product_groups_query)) {
      echo '<tr>' . PHP_EOL
         . '  <th colspan="2">'. $product_group['name'] .'</th>' . PHP_EOL
         . '</tr>' . PHP_EOL;
      $product_groups_values_query = $system->database->query(
        "select pgv.id, pgvi.name from ". DB_TABLE_PRODUCT_GROUPS_VALUES ." pgv
        left join ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ." pgvi on (pgvi.product_group_value_id = pgv.id and pgvi.language_code = '". $system->language->selected['code'] ."')
        where pgv.product_group_id = '". (int)$product_group['id'] ."'
        order by pgvi.name asc;"
      );
      while ($product_group_value = $system->database->fetch($product_groups_values_query)) {
      echo '<tr>' . PHP_EOL
         . '  <td>'. $system->functions->form_draw_checkbox('product_groups[]', $product_group['id'].'-'.$product_group_value['id'], (isset($_POST['product_groups']) && in_array($product_group['id'].'-'.$product_group_value['id'], $_POST['product_groups'])) ? $product_group['id'].'-'.$product_group_value['id'] : false) .'</td>' . PHP_EOL
         . '  <td>'. $product_group_value['name'] .'</td>' . PHP_EOL
         . '</tr>' . PHP_EOL;
      }
    }
    } else {
?>
                  <tr>
                    <td><em><?php echo $system->language->translate('description_no_existing_product_groups', 'There are no existing product groups.'); ?></em></td>
                  </tr>
<?php
    }
?>
                </table>
              </div>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap">
              <table border="0" cellpadding="5" cellspacing="0" style="margin: -5px;">
                <tr>
                  <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_quantity', 'Quantity'); ?></strong><br />
                    <?php echo $system->functions->form_draw_input_field('quantity', (isset($_POST['quantity']) ? $_POST['quantity'] : ''), 'text', 'style="width: 50px"'); ?>
                  </td>
                  <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_delivery_status', 'Delivery Status'); ?></strong><br />
                    <?php echo $system->functions->form_draw_delivery_status_list('delivery_status_id', isset($_POST['delivery_status_id']) ? $_POST['delivery_status_id'] : 0); ?>
                  </td>
                  <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_sold_out_status', 'Sold Out Status'); ?></strong><br />
                    <?php echo $system->functions->form_draw_sold_out_status_list('sold_out_status_id', isset($_POST['sold_out_status_id']) ? $_POST['sold_out_status_id'] : 0); ?>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <?php if (isset($product->data['id'])) { ?>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_product_images', 'Product Images'); ?></strong><br />
              <table cellpadding="0" cellspacing="0" border="0">
<?php
  if (!empty($_POST['images'])) foreach (array_keys($_POST['images']) as $key) {
?>
    <tr>
      <td><?php echo $system->functions->form_draw_hidden_field('images['.$key.'][id]', $_POST['images'][$key]['id']); ?><img src="<?php echo $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product->data['images'][$key]['filename'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 100, 75); ?>" align="left" style="margin: 5px;" /></td>
      <td><?php echo $system->functions->form_draw_hidden_field('images['.$key.'][filename]', $_POST['images'][$key]['filename']); ?><?php echo $system->functions->form_draw_input_field('images['.$key.'][new_filename]', isset($_POST['images'][$key]['new_filename']) ? $_POST['images'][$key]['new_filename'] : $_POST['images'][$key]['filename'], 'text'); ?></td>
      <td><a id="move-image-up" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/arrow_up.png" width="16" height="16" border="0" alt="<?php echo $system->language->translate('text_move_up', 'Move up'); ?>" /></a> <a id="move-image-down" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/arrow_down.png" width="16" height="16" border="0" alt="<?php echo $system->language->translate('text_move_down', 'Move down'); ?>" /></a> <a id="remove-image" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" border="0" alt="<?php echo $system->language->translate('text_remove', 'Remove'); ?>" /></a></td>
    </tr>
<?php
  }
?>
              </table>
              <script>
                $("#move-image-up, #move-image-down").live("click", function(event) {
                  event.preventDefault();
                  var row = $(this).parents("tr:first");
                  var firstrow = $('table tr:first');

                  if ($(this).is("#move-image-up") && row.prevAll().length > 0) {
                      row.insertBefore(row.prev());
                  } else if ($(this).is("#move-image-down") && row.nextAll().length > 0) {
                      row.insertAfter(row.next());
                  } else {
                    return false;
                  }
                });
                
                $("#remove-image").live("click", function(event) {
                  event.preventDefault();
                  $(this).closest('tr').remove();
                });
              </script>
            </td>
          </tr>
          <?php } ?>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_upload_images', 'Upload Images'); ?></strong><br />
              <div><?php echo $system->functions->form_draw_file_field('new_images[]', 'style="width: 360px"'); ?></div>
               <a href="#" id="add-new-image"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" border="0" alt="<?php echo $system->language->translate('text_add', 'Add'); ?>" /></a>
              <script>
                $("#add-new-image").live("click", function(event) {
                  event.preventDefault();
                  $(this).before('<div><?php echo str_replace(array("\r", "\n"), '', $system->functions->form_draw_file_field('new_images[]', 'style="width: 360px"')); ?></div>');
                });
              </script>
              </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_date_valid_from', 'Date Valid From'); ?></strong><br />
              <?php echo $system->functions->form_draw_date_field('date_valid_from', (isset($_POST['date_valid_from'])) ? $_POST['date_valid_from'] : ''); ?></td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_date_valid_to', 'Date Valid To'); ?></strong><br />
              <?php echo $system->functions->form_draw_date_field('date_valid_to', (isset($_POST['date_valid_to'])) ? $_POST['date_valid_to'] : ''); ?></td>
          </tr>
          <?php if (isset($product->data['id'])) { ?>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_date_updated', 'Date Updated'); ?></strong><br />
              <?php echo strftime('%e %b %Y %H:%M', strtotime($product->data['date_updated'])); ?></td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_date_created', 'Date Created'); ?></strong><br />
              <?php echo strftime('%e %b %Y %H:%M', strtotime($product->data['date_created'])); ?></td>
          </tr>
          <?php } ?>
        </table>
      </div>
    
      <div id="tab-information">
        <table border="0" cellpadding="5" cellspacing="0">
          <tr>
            <td align="left" valign="top" nowrap="nowrap">
              <strong><?php echo $system->language->translate('title_manufacturer', 'Manufacturer'); ?></strong><br />
                <?php echo $system->functions->form_draw_manufacturers_list('manufacturer_id', !empty($_POST['manufacturer_id']) ? $_POST['manufacturer_id'] : ''); ?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap">
              <strong><?php echo $system->language->translate('title_designer', 'Designer'); ?></strong><br />
                <?php echo $system->functions->form_draw_designers_list('designer_id', !empty($_POST['designer_id']) ? $_POST['designer_id'] : ''); ?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap">
              <strong><?php echo $system->language->translate('title_supplier', 'Supplier'); ?></strong><br />
                <?php echo $system->functions->form_draw_suppliers_list('supplier_id', !empty($_POST['supplier_id']) ? $_POST['supplier_id'] : ''); ?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_keywords', 'Keywords'); ?></strong><br />
              <?php echo $system->functions->form_draw_input_field('keywords', (isset($_POST['keywords']) ? $_POST['keywords'] : ''), 'text', 'style="width: 360px;"'); ?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_short_description', 'Short Description'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_input_field($language_code, 'short_description['. $language_code .']', (isset($_POST['short_description'][$language_code]) ? $_POST['short_description'][$language_code] : ''), 'text', 'style="width: 360px;"');
  $use_br = true;
}
?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_description', 'Description'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo '<img src="'. WS_DIR_IMAGES .'icons/languages/'. $language_code .'.png" align="top" /> ' . $system->functions->form_draw_textarea('description['. $language_code .']', (isset($_POST['description'][$language_code]) ? $_POST['description'][$language_code] : ''), 'style="width: 360px; height: 160px;"');
  $use_br = true;
}
?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_head_title', 'Head Title'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_input_field($language_code, 'head_title['. $language_code .']', (isset($_POST['head_title'][$language_code]) ? $_POST['head_title'][$language_code] : ''), 'text', 'style="width: 360px;"');
  $use_br = true;
}
?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_meta_description', 'Meta Description'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_input_field($language_code, 'meta_description['. $language_code .']', (isset($_POST['meta_description'][$language_code]) ? $_POST['meta_description'][$language_code] : ''), 'text', 'style="width: 360px;"');
  $use_br = true;
}
?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_meta_keywords', 'Meta Keywords'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_input_field($language_code, 'meta_keywords['. $language_code .']', (isset($_POST['meta_keywords'][$language_code]) ? $_POST['meta_keywords'][$language_code] : ''), 'text', 'style="width: 360px;"');
  $use_br = true;
}
?>
            </td>
          </tr>
        </table>
      </div>
      
      <div id="tab-data">
        <table border="0" cellpadding="5" cellspacing="0">
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_sku', 'SKU'); ?></strong><br />
              <?php echo $system->functions->form_draw_input_field('sku', (isset($_POST['sku']) ? $_POST['sku'] : ''), 'text'); ?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_upc', 'UPC'); ?></strong><br />
              <?php echo $system->functions->form_draw_input_field('upc', (isset($_POST['upc']) ? $_POST['upc'] : ''), 'text'); ?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_taric', 'TARIC'); ?></strong><br />
              <?php echo $system->functions->form_draw_input_field('taric', (isset($_POST['taric']) ? $_POST['taric'] : ''), 'text'); ?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_weight', 'Weight'); ?></strong><br />
              <?php echo $system->functions->form_draw_input_field('weight', (isset($_POST['weight']) ? $_POST['weight'] : ''), 'text', 'style="width: 50px"'); ?> <?php echo $system->weight->draw_select_field('weight_class', (isset($_POST['weight_class']) ? $_POST['weight_class'] : ''), 'style="width: 50px"'); ?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_dimensions', 'Dimensions'); ?></strong><br />
              <?php echo $system->functions->form_draw_input_field('dim_x', (isset($_POST['dim_x']) ? $_POST['dim_x'] : ''), 'text', 'style="width: 50px"'); ?> x <?php echo $system->functions->form_draw_input_field('dim_y', (isset($_POST['dim_y']) ? $_POST['dim_y'] : ''), 'text', 'style="width: 50px"'); ?> x <?php echo $system->functions->form_draw_input_field('dim_z', (isset($_POST['dim_z']) ? $_POST['dim_z'] : ''), 'text', 'style="width: 50px"'); ?> <?php echo $system->length->draw_select_field('dim_class', (isset($_POST['dim_class']) ? $_POST['dim_class'] : ''), 'style="width: 50px"'); ?> (<?php echo $system->language->translate('description_width_height_length', 'width x height x length'); ?>)
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_attributes', 'Attributes'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys($system->language->languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo $system->functions->form_draw_regional_textarea($language_code, 'attributes['. $language_code .']', (isset($_POST['attributes'][$language_code]) ? $_POST['attributes'][$language_code] : ''), 'style="width: 360px; height: 100px;"');  $use_br = true;
}
?>
            </td>
          </tr>
        </table>
      </div>
      
      <div id="tab-prices">
        <h2><?php echo $system->language->translate('title_prices', 'Prices'); ?></h2>
        
        <table border="0" cellpadding="5" cellspacing="0">
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_purchase_price', 'Purchase Price'); ?></strong><br />
              <?php echo $system->currency->currencies[$system->settings->get('store_currency_code')]['prefix']; ?><?php echo $system->functions->form_draw_input_field('purchase_price', (isset($_POST['purchase_price']) ? $_POST['purchase_price'] : ''), 'text', 'style="width: 50px"'); ?><?php echo $system->currency->currencies[$system->settings->get('store_currency_code')]['suffix']; ?>
            </td>
          </tr>
        </table>
        
        <table id="table-prices" border="0" cellpadding="5" cellspacing="0">
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><strong><?php echo $system->language->translate('title_tax_class', 'Tax Class'); ?></strong><br />
              <?php echo $system->functions->form_draw_tax_classes_list('tax_class_id', isset($_POST['tax_class_id']) ? $_POST['tax_class_id'] : 0); ?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap">&nbsp;</td>
          </tr>
        </table>
        
        <table border="0" cellpadding="5" cellspacing="0" style="margin: 0 -5px;">
          <tr>
            <th align="left" valign="top" nowrap="nowrap"><?php echo $system->language->translate('title_currency', 'Currency'); ?></th>
            <th align="left" valign="top" nowrap="nowrap"><?php echo $system->language->translate('title_price', 'Price'); ?></th>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $system->language->translate('title_conversion_value', 'Conversion Value'); ?></td>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $system->language->translate('title_net_price', 'Net Price'); ?> (<a id="net-price-tooltip" href="#">?</a>)</td>
          </tr>
          <tr>
            <td><strong><?php echo $system->settings->get('store_currency_code'); ?></strong></td>
            <td><?php echo $system->functions->form_draw_currency_field($system->settings->get('store_currency_code'), 'prices['. $system->settings->get('store_currency_code') .']', isset($_POST['prices'][$system->settings->get('store_currency_code')]) ? $_POST['prices'][$system->settings->get('store_currency_code')] : 0); ?></td>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $system->functions->form_draw_currency_field($system->settings->get('store_currency_code'), 'currency_prices['. $system->settings->get('store_currency_code') .']', '', 'disabled="disabled"'); ?></td>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $system->functions->form_draw_currency_field($system->settings->get('store_currency_code'), 'net_prices['. $system->settings->get('store_currency_code') .']', ''); ?></td>
            <td></td>
          </tr>
<?php
foreach (array_keys($system->currency->currencies) as $currency_code) {
  if ($currency_code == $system->settings->get('store_currency_code')) continue;
?>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $currency_code; ?></td>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $system->functions->form_draw_currency_field($currency_code, 'prices['. $currency_code .']', isset($_POST['prices'][$currency_code]) ? number_format($_POST['prices'][$currency_code], 4, '.', '') : ''); ?></td>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $system->functions->form_draw_currency_field($currency_code, 'currency_prices['. $currency_code .']', '', 'disabled="disabled"'); ?></td>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $system->functions->form_draw_currency_field($currency_code, 'net_prices['. $currency_code .']', ''); ?></td>
            <td align="left" valign="top" nowrap="nowrap"></td>
          </tr>
<?php
}
?>
          </tr>
        </table>
        <script>
          function get_tax_rate() {
            switch ($("select[name=tax_class_id]").val()) {
<?php
  $tax_classes_query = $system->database->query(
    "select * from ". DB_TABLE_TAX_CLASSES ."
    order by name asc;"
  );
  while ($tax_class = $system->database->fetch($tax_classes_query)) {
    echo '      case "'. $tax_class['id'] . '":'. PHP_EOL
       . '        return '. $system->tax->get_tax(100, $tax_class['id']) .';' . PHP_EOL;
  }
?>
              default:
                return 0;
            }
          }
          
          function calculate_price() {
            <?php foreach ($system->currency->currencies as $currency) echo 'var value = $("input[name=\'net_prices['. $currency['code'] .']\']").val()/(1+(get_tax_rate()/100)); $("input[name=\'prices['. $currency['code'] .']\']").val(value.toFixed(4)); ' . PHP_EOL; ?>
          }
          
          function calculate_net_price() {
            <?php foreach ($system->currency->currencies as $currency) echo 'var value = $("input[name=\'prices['. $currency['code'] .']\']").val()*(1+(get_tax_rate()/100)); $("input[name=\'net_prices['. $currency['code'] .']\']").val(value.toFixed(4)); ' . PHP_EOL; ?>
          }
          calculate_net_price();
          
          function currency_convert_prices() {
            <?php foreach ($system->currency->currencies as $currency) echo 'var value = $("input[name=\'prices['. $system->settings->get('store_currency_code') .']\']").val() * '. $currency['value'] .'; $("input[name=\'currency_prices['. $currency['code'] .']\']").val(value.toFixed(4)); ' . PHP_EOL; ?>
          }
          currency_convert_prices();
          
          $("select[name='tax_class_id']").change(function() {
            calculate_net_price();
          });
          
          $("input[name^='prices']").keyup(function() {
            calculate_net_price();
            currency_convert_prices();
          });

          $("input[name^='net_prices']").keyup(function() {
            calculate_price();
            currency_convert_prices();
          });
          
          $("#net-price-tooltip").live('click', function(e) {
            e.preventDefault;
            alert("<?php echo str_replace(array("\r", "\n", "\""), array('', '', "\\\""), $system->language->translate('text_net_price_tooltip', 'The net price field helps you calculate gross price based on the store country tax. But all prices input to database are always excluding tax.')); ?>");
          });
        </script>
        
        <h2><?php echo $system->language->translate('title_campaigns', 'Campaigns'); ?></h2>
        <table id="table-campaigns" border="0" cellpadding="5" cellspacing="0">
          <?php if (!empty($_POST['campaigns'])) foreach (array_keys($_POST['campaigns']) as $key) { ?>
          <tr>
            <td nowrap="nowrap"><strong><?php echo $system->language->translate('title_start_date', 'Start Date'); ?></strong><br />
              <?php echo $system->functions->form_draw_hidden_field('campaigns['.$key.'][id]', isset($_POST['campaigns'][$key]['id']) ? $_POST['campaigns'][$key]['id'] : '') . $system->functions->form_draw_datetime_field('campaigns['.$key.'][start_date]', isset($_POST['campaigns'][$key]['start_date']) ? $_POST['campaigns'][$key]['start_date'] : '', 'style="width: 110px;"'); ?>
            </td>
            <td nowrap="nowrap"><strong><?php echo $system->language->translate('title_end_date', 'End Date'); ?></strong><br />
              <?php echo $system->functions->form_draw_datetime_field('campaigns['.$key.'][end_date]', isset($_POST['campaigns'][$key]['end_date']) ? $_POST['campaigns'][$key]['end_date'] : '', 'style="width: 110px;"'); ?>
            </td>
            <td nowrap="nowrap"><?php echo $currency_code; ?><br />
              <?php echo $system->functions->form_draw_input_field('campaigns['.$key.'][percent]', '', 'text', 'style="width: 50px;"'); ?>
            </td>
            <td nowrap="nowrap"><strong><?php echo $system->settings->get('store_currency_code'); ?></strong><br />
              <?php echo $system->functions->form_draw_currency_field($system->currency->selected['code'], 'campaigns['.$key.']['. $system->settings->get('store_currency_code') .']', isset($_POST['campaigns'][$key][$system->settings->get('store_currency_code')]) ? $_POST['campaigns'][$key][$system->settings->get('store_currency_code')] : 0, 'text', 'style="width: 50px;"'); ?>
            </td>
<?php
  foreach (array_keys($system->currency->currencies) as $currency_code) {
    if ($currency_code == $system->settings->get('store_currency_code')) continue;
?>
            <td nowrap="nowrap"><?php echo $currency_code; ?><br />
              <?php echo $system->functions->form_draw_currency_field($currency_code, 'campaigns['.$key.']['. $currency_code. ']', isset($_POST['campaigns'][$key][$currency_code]) ? number_format($_POST['campaigns'][$key][$currency_code], 4, '.', '') : '', 'text', 'style="width: 50px;"'); ?>
            </td>
<?php
  }
?>
            <td><a id="remove-campaign" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" border="0" /></a></td>
          </tr>
          <?php } ?>
          <tr>
            <td colspan="5"><a id="add-campaign" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" border="0" /></a></td>
          </tr>
        </table>
        
        <script>
          $("input[name^='campaigns'][name$='[percentage]']").live("keyup", function() {
            var index = $(this).attr('name').match(/^campaigns\[([^\]]+)\]\[percentage\]$/);
            var index = index[1];
            <?php foreach ($system->currency->currencies as $currency) { ?>
            $("input[name^='campaigns["+ index +"][<?php echo $currency['code']; ?>]']").val($("input[name='prices[<?php echo $currency['code']; ?>]']").val() * ((100 - $(this).val()) / 100));
            <?php } ?>
          });
          
          $("input[name^='campaigns'][name$='[<?php echo $system->currency->selected['code']; ?>]']").live("keyup", function() {
            var index = $(this).attr('name').match(/^campaigns\[([^\]]+)\]\[<?php echo $system->currency->selected['code']; ?>\]$/);
            var index = index[1];
            $("input[name^='campaigns["+ index +"][percentage]']").val(($("input[name='prices[<?php echo $system->currency->selected['code']; ?>]']").val() - $(this).val()) / $("input[name='prices[<?php echo $system->currency->selected['code']; ?>]']").val() * 100);
          });
          
          $("#remove-campaign").live("click", function(event) {
            event.preventDefault();
            $(this).closest('tr').remove();
          });
          
          var new_campaign_i = 1;
          $("#add-campaign").live("click", function(event) {
            event.preventDefault();
            var output = '<tr>'
                       + '  <td nowrap="nowrap"><strong><?php echo $system->language->translate('title_start_date', 'Start Date'); ?></strong><br />'
                       + '    <?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_hidden_field('campaigns[new_campaign_i][id]', '') . $system->functions->form_draw_datetime_field('campaigns[new_campaign_i][start_date]', '', 'style="width: 110px;"')); ?>'
                       + '  </td>'
                       + '  <td nowrap="nowrap"><strong><?php echo $system->language->translate('title_end_date', 'End Date'); ?></strong><br />'
                       + '    <?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_datetime_field('campaigns[new_campaign_i][end_date]', '', 'style="width: 110px;"')); ?>'
                       + '  </td>'
                       + '  <td nowrap="nowrap">%<br />'
                       + '    <?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_input_field('campaigns[new_campaign_i][percentage]', '', 'text', 'style="width: 50px;"')); ?>'
                       + '  </td>'
                       + '  <td nowrap="nowrap"><strong><?php echo $system->settings->get('store_currency_code'); ?></strong><br />'
                       + '    <?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_currency_field($system->settings->get('store_currency_code'), 'campaigns[new_campaign_i]['. $system->settings->get('store_currency_code') .']', '')); ?>'
                       + '  </td>'
<?php
  foreach (array_keys($system->currency->currencies) as $currency_code) {
    if ($currency_code == $system->settings->get('store_currency_code')) continue;
?>
                       + '  <td nowrap="nowrap"><?php echo $currency_code; ?><br />'
                       + '    <?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_currency_field($currency_code, 'campaigns[new_campaign_i]['. $currency_code. ']', '')); ?>'
                       + '  </td>'
<?php
  }
?>
                       + '  <td><a id="remove-campaign" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" border="0" /></a></td>'
                       + '</tr>';
           while ($("input[name='campaigns[new_"+new_campaign_i+"]']").length) new_campaign_i++;
            output = output.replace(/new_campaign_i/g, 'new_' + new_campaign_i);
            $("#table-campaigns tr:last").before(output);
            new_campaign_i++;
          });
        </script>
      </div>
      
      <div id="tab-options">
        <h2><?php echo $system->language->translate('title_options', 'Options'); ?></h2>
        <table id="table-options" border="0" cellpadding="5" cellspacing="0" width="100%">
          <tr>
            <th align="left" valign="top" nowrap="nowrap">&nbsp;</th>
            <th align="left" valign="top" nowrap="nowrap"><?php echo $system->language->translate('title_group', 'Group'); ?></th>
            <th align="left" valign="top" nowrap="nowrap"><?php echo $system->language->translate('title_value', 'Value'); ?></th>
            <th align="left" valign="top" nowrap="nowrap"><?php echo $system->language->translate('title_price_operator', 'Price Operator'); ?></th>
            <th align="left" valign="top" nowrap="nowrap"><?php echo $system->settings->get('store_currency_code'); ?></th>
<?php
  foreach (array_keys($system->currency->currencies) as $currency_code) {
    if ($currency_code == $system->settings->get('store_currency_code')) continue;
?>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $currency_code; ?></td>
<?php
  }
?>
            <th align="left" valign="top" nowrap="nowrap">&nbsp;</th>
          </tr>
  <?php
  if (!empty($_POST['options'])) {
    foreach (array_keys($_POST['options']) as $key) {
  ?>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><a id="add-option" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" border="0" title="<?php echo $system->language->translate('text_insert_before', 'Insert before'); ?>" /></a><?php echo $system->functions->form_draw_hidden_field('options['.$key.'][id]', $_POST['options'][$key]['id']); ?></td>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $system->functions->form_draw_option_groups_list('options['.$key.'][group_id]', $_POST['options'][$key]['group_id']); ?></td>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $system->functions->form_draw_option_values_list($_POST['options'][$key]['group_id'], 'options['.$key.'][value_id]', $_POST['options'][$key]['value_id']); ?></td>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $system->functions->form_draw_select_field('options['.$key.'][price_operator]', array('+','*'), $_POST['options'][$key]['price_operator']); ?></td>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $system->functions->form_draw_currency_field($system->settings->get('store_currency_code'), 'options['.$key.']['.$system->settings->get('store_currency_code').']', $_POST['options'][$key][$system->settings->get('store_currency_code')]); ?></td>
<?php
      foreach (array_keys($system->currency->currencies) as $currency_code) {
        if ($currency_code == $system->settings->get('store_currency_code')) continue;
?>
            <td nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_currency_field($currency_code, 'options['.$key.']['. $currency_code. ']', number_format($_POST['options'][$key][$currency_code], 4, '.', ''))); ?></td>
<?php
      }
?>
            <td align="left" valign="top" nowrap="nowrap"><a id="move-option-up" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/arrow_up.png" width="16" height="16" border="0" title="<?php echo $system->language->translate('text_move_up', 'Move up'); ?>" /></a> <a id="move-option-down" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/arrow_down.png" width="16" height="16" border="0" title="<?php echo $system->language->translate('text_move_down', 'Move down'); ?>" /></a> <a id="remove-option" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" border="0" /></a></td>
          </tr>
<?php
    }
  }
?>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><a id="add-option" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" border="0" title="<?php echo $system->language->translate('text_insert_before', 'Insert before'); ?>" /></a></td>
            <td align="left" valign="top" nowrap="nowrap">&nbsp;</td>
            <td align="left" valign="top" nowrap="nowrap">&nbsp;</td>
            <td align="left" valign="top" nowrap="nowrap">&nbsp;</td>
            <td align="left" valign="top" nowrap="nowrap">&nbsp;</td>
          </tr>
        </table>
        <script>
          $("#remove-option").live("click", function(event) {
            event.preventDefault();
            $(this).closest('tr').remove();
          });
          
          $("#move-option-up, #move-option-down").live("click", function(event) {
            event.preventDefault();
            var row = $(this).parents("tr:first");
            var firstrow = $('table tr:first');

            if ($(this).is("#move-option-up") && row.prevAll().length > 1) {
                row.insertBefore(row.prev());
            } else if ($(this).is("#move-option-down") && row.nextAll().length > 0) {
                row.insertAfter(row.next());
            } else {
              return false;
            }
          });
          
          $("select[name^='options'][name$='[group_id]']").live("change", function(){
            var valueField = this.name.replace(/group/, 'value');
            $('body').css('cursor', 'wait');
            $.ajax({
              url: '<?php echo $system->document->link(WS_DIR_AJAX .'option_values.json.php'); ?>?option_group_id=' + $(this).val(),
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
          $("#add-option").live("click", function(event) {
            event.preventDefault();
            var output = '<tr>'
                       + '  <td nowrap="nowrap"><a id="add-option" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" border="0" title="<?php echo $system->language->translate('text_insert_before', 'Insert before'); ?>" /></a><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_hidden_field('options[new_option_i][id]', '')); ?></td>'
                       + '  <td nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_option_groups_list('options[new_option_i][group_id]', '')); ?></td>'
                       + '  <td nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_select_field('options[new_option_i][value_id]', array(array('','')), '')); ?></td>'
                       + '  <td nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_select_field('options[new_option_i][price_operator]', array('+','*'), '+')); ?></td>'
                       + '  <td nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_currency_field($system->settings->get('store_currency_code'), 'options[new_option_i]['. $system->settings->get('store_currency_code') .']', 0)); ?></td>'
<?php
  foreach (array_keys($system->currency->currencies) as $currency_code) {
    if ($currency_code == $system->settings->get('store_currency_code')) continue;
?>
                       + '  <td nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_currency_field($currency_code, 'options[new_option_i]['. $currency_code. ']', '')); ?></td>'
<?php
  }
?>
                       + '  <td nowrap="nowrap" align="left" valign="top"><a id="move-option-up" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/arrow_up.png" width="16" height="16" border="0" title="<?php echo $system->language->translate('text_move_up', 'Move up'); ?>" /></a> <a id="move-option-down" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/arrow_down.png" width="16" height="16" border="0" title="<?php echo $system->language->translate('text_move_down', 'Move down'); ?>" /></a> <a id="remove-option" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" border="0" title="<?php echo $system->language->translate('title_remove', 'Remove'); ?>" /></a></td>'
                       + '</tr>';
            output = output.replace(/new_option_i/g, 'new_' + new_option_i);
            $(this).closest('tr').before(output);
            new_option_i++;
          });
        </script>
      </div>
      
      <div id="tab-options-stock">
        <h2><?php echo $system->language->translate('title_options_stock', 'Options Stock'); ?></h2>
        <table id="table-options" border="0" cellpadding="5" cellspacing="0">
          <tr>
            <th align="left" valign="top" nowrap="nowrap"><?php echo $system->language->translate('title_option', 'Option'); ?></th>
            <th align="left" valign="top" nowrap="nowrap"><?php echo $system->language->translate('title_SKU', 'SKU'); ?></th>
            <th align="left" valign="top" nowrap="nowrap"><?php echo $system->language->translate('title_qty', 'Qty'); ?></th>
            <th align="left" valign="top" nowrap="nowrap"><?php echo $system->language->translate('title_weight', 'Weight'); ?></th>
            <th align="left" valign="top" nowrap="nowrap"><?php echo $system->language->translate('title_dimensions', 'Dimensions'); ?></th>
            <th align="left" valign="top" nowrap="nowrap">&nbsp;</th>
          </tr>
<?php
  if (!empty($_POST['options_stock'])) {
    foreach (array_keys($_POST['options_stock']) as $key) {
?>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $system->functions->form_draw_hidden_field('options_stock['.$key.'][id]', $_POST['options_stock'][$key]['id']); ?><?php echo $system->functions->form_draw_hidden_field('options_stock['.$key.'][combination]', $_POST['options_stock'][$key]['combination']); ?>
            <?php echo $system->functions->form_draw_hidden_field('options_stock['.$key.'][name]['. $system->language->selected['name'] .']', $_POST['options_stock'][$key]['name'][$system->language->selected['code']]); ?>
            <?php echo $_POST['options_stock'][$key]['name'][$system->language->selected['code']]; ?></td>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $system->functions->form_draw_input_field('options_stock['.$key.'][sku]', $_POST['options_stock'][$key]['sku'], 'text', 'style="width: 75px;"'); ?></td>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $system->functions->form_draw_input_field('options_stock['.$key.'][quantity]', $_POST['options_stock'][$key]['quantity'], 'text', 'style="width: 25px;"'); ?></td>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $system->functions->form_draw_input_field('options_stock['.$key.'][weight]', $_POST['options_stock'][$key]['weight'], 'text', 'style="width: 40px;"'); ?> <?php echo $system->functions->form_draw_weight_classes_list('options_stock['.$key.'][weight_class]', $_POST['options_stock'][$key]['weight_class']); ?></td>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $system->functions->form_draw_input_field('options_stock['.$key.'][dim_x]', $_POST['options_stock'][$key]['dim_x'], 'text', 'style="width: 40px;"'); ?> x <?php echo $system->functions->form_draw_input_field('options_stock['.$key.'][dim_y]', $_POST['options_stock'][$key]['dim_y'], 'text', 'style="width: 40px;"'); ?> x <?php echo $system->functions->form_draw_input_field('options_stock['.$key.'][dim_z]', $_POST['options_stock'][$key]['dim_z'], 'text', 'style="width: 40px;"'); ?> <?php echo $system->functions->form_draw_length_classes_list('options_stock['.$key.'][dim_class]', (isset($_POST['options_stock'][$key]['dim_class']) ? $_POST['options_stock'][$key]['dim_class'] : '')); ?></td>
            <td align="left" valign="top" nowrap="nowrap"><a id="move-option-up" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/arrow_up.png" width="16" height="16" border="0" title="<?php echo $system->language->translate('text_move_up', 'Move up'); ?>" /></a> <a id="move-option-down" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/arrow_down.png" width="16" height="16" border="0" title="<?php echo $system->language->translate('text_move_down', 'Move down'); ?>" /></a> <a id="remove-option" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" border="0" /></a></td>
          </tr>
<?php
    }
  }
?>
        </table>
        <h3><?php echo $system->language->translate('title_new_option', 'New Option'); ?></h3>
        <table id="table-option-combo" border="0" cellpadding="5" cellspacing="0">
          <tr>
            <th align="left" valign="top" nowrap="nowrap"><?php echo $system->language->translate('title_group', 'Group'); ?></th>
            <th align="left" valign="top" nowrap="nowrap"><?php echo $system->language->translate('title_value', 'Value'); ?></th>
            <th align="left" valign="top" nowrap="nowrap">&nbsp;</th>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_option_groups_list('new_option[new_1][group_id]', '')); ?></td>
            <td align="left" valign="top" nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_select_field('new_option[new_1][value_id]', array(array('','')), '', false, false, 'disabled="disabled"')); ?></td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><a id="add-group-value" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" border="0" /> <?php echo $system->language->translate('title_add_combination', 'Add Combination'); ?></a></td>
            <td align="left" valign="top" nowrap="nowrap">&nbsp;</td>
            <td align="left" valign="top" nowrap="nowrap">&nbsp;</td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $system->functions->form_draw_button('add_option', $system->language->translate('title_add_option', 'Add Option'), 'button'); ?></td>
            <td align="left" valign="top" nowrap="nowrap">&nbsp;</td>
            <td align="left" valign="top" nowrap="nowrap">&nbsp;</td>
          </tr>
        </table>
        <script>
          $("#remove-option").live("click", function(event) {
            event.preventDefault();
            $(this).closest('tr').remove();
          });
          
          $("#move-option-up, #move-option-down").live("click", function(event) {
            event.preventDefault();
            var row = $(this).parents("tr:first");
            var firstrow = $('table tr:first');

            if ($(this).is("#move-option-up") && row.prevAll().length > 1) {
                row.insertBefore(row.prev());
            } else if ($(this).is("#move-option-down") && row.nextAll().length > 0) {
                row.insertAfter(row.next());
            } else {
              return false;
            }
          });
          
          var option_index = 2;
          $("#add-group-value").live("click", function(event) {
            event.preventDefault();
            var output = '<tr>'
                       + '  <td align="left" valign="top" nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_option_groups_list('new_option[option_index][group_id]', '')); ?></td>'
                       + '  <td align="left" valign="top" nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_select_field('new_option[option_index][value_id]', array(array('','')), '', false, false, 'disabled="disabled"')); ?></td>'
                       + '  <td align="left" valign="top" nowrap="nowrap"><a id="remove-group-value" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" border="0" title="<?php echo $system->language->translate('title_remove', 'Remove'); ?>" /></a></td>'
                       + '</tr>';
            output = output.replace(/option_index/g, 'new_' + option_index);
            $(this).closest('tr').before(output);
            option_index++;
          });
          
          $("#remove-group-value").live("click", function(event) {
            event.preventDefault();
            $(this).closest('tr').remove();
          });
          
          $("select[name^='new_option'][name$='[group_id]']").live("change", function(){
            var valueField = this.name.replace(/group/, 'value');
            $('body').css('cursor', 'wait');
            $.ajax({
              url: '<?php echo $system->document->link(WS_DIR_AJAX .'option_values.json.php'); ?>?option_group_id=' + $(this).val(),
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
          $("button[name=add_option]").live("click", function(event) {
            event.preventDefault();
            var new_option_code = '';
            var new_option_name = '';
            var use_coma = false;
            var success = $("select[name^='new_option'][name$='[group_id]']").each(function(i, groupElement) {
              var groupElement = $(groupElement);
              var valueElement = $("select[name='"+ $(groupElement).attr("name").replace(/group_id/g, 'value_id') +"']");
              if (valueElement.val() == "") {
                alert("<?php echo $system->language->translate('error_empty_option_group', 'Error: Empty option group'); ?>");
                return false;
              }
              if (groupElement.val() == "") {
                alert("<?php echo $system->language->translate('error_empty_option_value', 'Error: Empty option value'); ?>");
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
                       + '  <td nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_hidden_field('options_stock[new_option_stock_i][id]', '') . $system->functions->form_draw_hidden_field('options_stock[new_option_stock_i][combination]', 'new_option_code') . $system->functions->form_draw_hidden_field('options_stock[new_option_stock_i][name]['. $system->language->selected['code'] .']', 'new_option_name')); ?>new_option_name</td>'
                       + '  <td nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_input_field('options_stock[new_option_stock_i][sku]', '', 'text', 'style="width: 75px;"')); ?></td>'
                       + '  <td nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_input_field('options_stock[new_option_stock_i][quantity]', '0', 'text', 'style="width: 25px;"')); ?></td>'
                       + '  <td nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_input_field('options_stock[new_option_stock_i][weight]', '0.00', 'text', 'style="width: 40px;"') .' '. $system->functions->form_draw_weight_classes_list('options_stock[new_option_stock_i][weight_class]', '')); ?></td>'
                       + '  <td nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_input_field('options_stock[new_option_stock_i][dim_x]', '0.00', 'text', 'style="width: 40px;"') .' x '. $system->functions->form_draw_input_field('options_stock[new_option_stock_i][dim_y]', '0.00', 'text', 'style="width: 40px;"') .' x '. $system->functions->form_draw_input_field('options_stock[new_option_stock_i][dim_z]', '0.00', 'text', 'style="width: 40px;"') .' '. $system->functions->form_draw_length_classes_list('options_stock[new_option_stock_i][dim_class]', '')); ?></td>'
                       + '  <td nowrap="nowrap" align="left" valign="top"><a id="move-option-up" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/arrow_up.png" width="16" height="16" border="0" title="<?php echo $system->language->translate('text_move_up', 'Move up'); ?>" /></a> <a id="move-option-down" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/arrow_down.png" width="16" height="16" border="0" title="<?php echo $system->language->translate('text_move_down', 'Move down'); ?>" /></a> <a id="remove-option" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" border="0" title="<?php echo $system->language->translate('title_remove', 'Remove'); ?>" /></a></td>'
                       + '</tr>';
            while ($("input[name='options_stock[new_"+new_option_stock_i+"]']").length) new_option_stock_i++;
            output = output.replace(/new_option_stock_i/g, 'new_' + new_option_stock_i);
            output = output.replace(/new_option_code/g, new_option_code);
            output = output.replace(/new_option_name/g, new_option_name);
            $("#table-options tr:last").after(output);
            new_option_stock_i++;
          });
        </script>
      </div>
    </div>
  </div>
  <p><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"'); ?> <?php echo (isset($product->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"') : false; ?></p>
  <?php echo $system->functions->form_draw_form_end(); ?>