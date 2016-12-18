<?php

  if (!empty($_GET['product_id'])) {
    $product = new ctrl_product($_GET['product_id']);
  } else {
    $product = new ctrl_product();
  }

  if (empty($_POST)) {
    foreach ($product->data as $key => $value) {
      $_POST[$key] = $value;
    }

    if (isset($_GET['category_id']) && empty($_POST['categories'])) $_POST['categories'][] = $_GET['category_id'];
  }

  breadcrumbs::add(!empty($product->data['id']) ? language::translate('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : language::translate('title_add_new_product', 'Add New Product'));

  if (isset($_POST['save'])) {

    if (empty($_POST['name'][language::$selected['code']])) notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));
    if (empty($_POST['categories'])) notices::add('errors', language::translate('error_must_select_category', 'You must select a category'));

    if (!empty($_POST['code']) && database::num_rows(database::query("select id from ". DB_TABLE_PRODUCTS ." where id != '". (int)$product->data['id'] ."' and code = '". database::input($_POST['code']) ."' limit 1;"))) notices::add('warnings', language::translate('error_code_database_conflict', 'Another entry with the given code already exists in the database'));
    if (!empty($_POST['sku']) && database::num_rows(database::query("select id from ". DB_TABLE_PRODUCTS ." where id != '". (int)$product->data['id'] ."' and sku = '". database::input($_POST['sku']) ."' limit 1;"))) notices::add('warnings', language::translate('error_sku_database_conflict', 'Another entry with the given SKU already exists in the database'));
    if (!empty($_POST['gtin']) && database::num_rows(database::query("select id from ". DB_TABLE_PRODUCTS ." where id != '". (int)$product->data['id'] ."' and gtin = '". database::input($_POST['gtin']) ."' limit 1;"))) notices::add('warnings', language::translate('error_gtin_database_conflict', 'Another entry with the given GTIN already exists in the database'));

    if (empty(notices::$data['errors'])) {

      if (!isset($_POST['images'])) $_POST['images'] = array();
      if (!isset($_POST['campaigns'])) $_POST['campaigns'] = array();
      if (!isset($_POST['options'])) $_POST['options'] = array();
      if (!isset($_POST['options_stock'])) $_POST['options_stock'] = array();
      if (!isset($_POST['product_groups'])) $_POST['product_groups'] = array();

      $fields = array(
        'status',
        'manufacturer_id',
        'supplier_id',
        'delivery_status_id',
        'sold_out_status_id',
        'default_category_id',
        'categories',
        'product_groups',
        'date_valid_from',
        'date_valid_to',
        'quantity',
        'quantity_unit_id',
        'purchase_price',
        'purchase_price_currency_code',
        'prices',
        'campaigns',
        'tax_class_id',
        'code',
        'sku',
        'gtin',
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

  document::$snippets['foot_tags']['jquery-tabs'] = '<script src="'. WS_DIR_EXT .'jquery/jquery.tabs.js"></script>';
?>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo !empty($product->data['id']) ? language::translate('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : language::translate('title_add_new_product', 'Add New Product'); ?></h1>

<?php
  if (isset($product->data['id'])) {
    if (!empty($product->data['images'])) {
      $image = current($product->data['images']);
      echo '<p><img src="'. functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $image['filename'], 150, 150) .'" alt="" /></p>';
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
              <?php echo functions::form_draw_toggle('status', isset($_POST['status']) ? $_POST['status'] : '0', 'e/d'); ?>
            </td>
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
  function custom_catalog_tree($category_id=0, $depth=1, $count=0) {

    $output = '';

    if ($category_id == 0) {
      $output .= '<tr>' . PHP_EOL
               . '  <td>'. functions::form_draw_checkbox('categories[]', '0', (empty($_POST['categories']) || in_array('0', $_POST['categories'])) ? '0' : false, 'data-name="'. htmlspecialchars(language::translate('title_root', 'Root')) .'" data-priority="0"') .'</td>' . PHP_EOL
               . '  <td width="100%" id = "category-id-'. $category_id .'">'. functions::draw_fonticon('fa-folder fa-lg', 'title="'. language::translate('title_root', 'Root') .'" style="color: #cccc66;"') .'</td>' . PHP_EOL
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
      $count++;
      $output .= '<tr>' . PHP_EOL
               . '  <td>'. functions::form_draw_checkbox('categories[]', $category['id'], true, 'data-name="'. htmlspecialchars($category['name']) .'" data-priority="'. $count .'"') .'</td>' . PHP_EOL
               . '  <td>'. functions::draw_fonticon('fa-folder fa-lg', 'style="color: #cccc66; margin-left: '. ($depth*16) .'px;"') .' '. $category['name'] .'</td>' . PHP_EOL
               . '</tr>' . PHP_EOL;

      if (database::num_rows(database::query("select * from ". DB_TABLE_CATEGORIES ." where parent_id = '". $category['id'] ."' limit 1;")) > 0) {
        $output .= custom_catalog_tree($category['id'], $depth+1, $count);
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
            <td><strong><?php echo language::translate('title_default_category', 'Default Category'); ?></strong><br />
<?php
	$options = array();

	$category_name_query = database::query(
	  "select category_id as id, name from ". DB_TABLE_CATEGORIES ." c
    left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". database::input(language::$selected['code']) ."')
	  where c.id in ('". implode("', '", database::input($product->data['categories'])) ."');"
	);

	while ($category = database::fetch($category_name_query)) {
	  $options[] = array($category['name'], $category['id']);
	}

  echo functions::form_draw_select_field('default_category_id', $options, true);
?>
<script>
  $('input[name="categories[]"]').change(function() {
    if ($(this).is(":checked")) {
      $('select[name="default_category_id"]').append('<option value="'+ $(this).val() +'">'+ $(this).data('name') +'</option>');
    } else {
      $('select[name="default_category_id"] option[value="'+ $(this).val() +'"]').remove();
    }
    var default_category = $('select[name="default_category_id"] option:selected').val();
    $('select[name="default_category_id"]').html($('select[name="default_category_id"] option').sort(function(a,b){
        a = $('input[name="categories[]"][value="'+ a.value +'"]').data('priority');
        b = $('input[name="categories[]"][value="'+ b.value +'"]').data('priority');
        return a-b;
    }));
    $('select[name="default_category_id"] option').prop('selected', '');
    $('select[name="default_category_id"] option[value="'+ default_category +'"]').prop('selected', 'selected');
  });
  $('input[name="categories[]"]:checked').trigger('change');
</script>
             </td>
           </tr>
          <tr>
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
                    <?php echo functions::form_draw_decimal_field('quantity', true); ?>
                  </td>
                  <td><strong><?php echo language::translate('title_quantity_unit', 'Quantity Unit'); ?></strong><br />
                    <?php echo functions::form_draw_quantity_units_list('quantity_unit_id', true); ?>
                  </td>
                  <td><strong><?php echo language::translate('title_delivery_status', 'Delivery Status'); ?></strong><br />
                    <?php echo functions::form_draw_delivery_statuses_list('delivery_status_id', true); ?>
                  </td>
                  <td><strong><?php echo language::translate('title_sold_out_status', 'Sold Out Status'); ?></strong><br />
                    <?php echo functions::form_draw_sold_out_statuses_list('sold_out_status_id', true); ?>
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
      <td><?php echo functions::form_draw_hidden_field('images['.$key.'][id]', true); ?><img src="<?php echo functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product->data['images'][$key]['filename'], 100, 75); ?>" alt="" style="float: left; margin: 5px;" /></td>
      <td><?php echo functions::form_draw_hidden_field('images['.$key.'][filename]', $_POST['images'][$key]['filename']); ?><?php echo functions::form_draw_text_field('images['.$key.'][new_filename]', isset($_POST['images'][$key]['new_filename']) ? $_POST['images'][$key]['new_filename'] : $_POST['images'][$key]['filename'], 'data-size="large"'); ?></td>
      <td><a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"'); ?></a> <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"'); ?></a> <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
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
                  <td><a href="#" id="add-new-image" title="<?php echo language::translate('text_add', 'Add'); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?></a></td>
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
              <?php echo functions::form_draw_date_field('date_valid_from', true); ?></td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_date_valid_to', 'Date Valid To'); ?></strong><br />
              <?php echo functions::form_draw_date_field('date_valid_to', true); ?></td>
          </tr>
          <?php if (!empty($product->data['id'])) { ?>
          <tr>
            <td><strong><?php echo language::translate('title_date_updated', 'Date Updated'); ?></strong><br />
              <?php echo language::strftime('%e %b %Y %H:%M', strtotime($product->data['date_updated'])); ?></td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_date_created', 'Date Created'); ?></strong><br />
              <?php echo language::strftime('%e %b %Y %H:%M', strtotime($product->data['date_created'])); ?></td>
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
  echo functions::form_draw_regional_wysiwyg_field($language_code, 'description['. $language_code .']', true, 'data-size="large" style="height: 125px;"');
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
  echo functions::form_draw_regional_input_field($language_code, 'head_title['. $language_code .']', true, 'data-size="large"');
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
        </table>
      </div>

      <div id="tab-data">
        <table>
          <tr>
            <td><strong><?php echo language::translate('title_sku', 'SKU'); ?></strong> <a href="https://en.wikipedia.org/wiki/Stock_keeping_unit" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a><br />
              <?php echo functions::form_draw_text_field('sku', true); ?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_gtin', 'GTIN'); ?></strong> <a href="https://en.wikipedia.org/wiki/Global_Trade_Item_Number" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a><br />
              <?php echo functions::form_draw_text_field('gtin', true); ?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_taric', 'TARIC'); ?></strong> <a href="https://en.wikipedia.org/wiki/TARIC_code" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a><br />
              <?php echo functions::form_draw_text_field('taric', true); ?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_weight', 'Weight'); ?></strong><br />
              <?php echo functions::form_draw_decimal_field('weight', true); ?> <?php echo functions::form_draw_weight_classes_list('weight_class', true); ?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_dimensions', 'Dimensions'); ?></strong> (<?php echo language::translate('title_width_height_length', 'Width x Height x Length'); ?>)<br />
              <span class="input-wrapper"><?php echo functions::form_draw_decimal_field('dim_x', true, 2, 0, null, 'style="width: 75px; text-align: center;"'); ?> <sub>x</sub> <?php echo functions::form_draw_decimal_field('dim_y', true, 2, 0, null, 'style="width: 75px; text-align: center;"'); ?> <sub>x</sub> <?php echo functions::form_draw_decimal_field('dim_z', true, 2, 0, null, 'style="width: 75px; text-align: center;"'); ?></span>  <?php echo functions::form_draw_length_classes_list('dim_class', true); ?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_attributes', 'Attributes'); ?></strong> (<a class="attributes-hint" href="#">?</a>)<br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_textarea($language_code, 'attributes['. $language_code .']', true, 'data-size="large" style="height: 120px;"');  $use_br = true;
}
?>
              <script>
                $('a.attributes-hint').click(function(){
                  alert('Syntax:\n\nTitle1\nProperty1: Value1\nProperty2: Value2\nTitle2\nProperty3: Value3...');
                });
              </script>
            </td>
          </tr>
        </table>
      </div>

      <div id="tab-prices">
        <h2><?php echo language::translate('title_prices', 'Prices'); ?></h2>

        <table>
          <tr>
            <td><strong><?php echo language::translate('title_purchase_price', 'Purchase Price'); ?></strong><br />
              <?php echo functions::form_draw_decimal_field('purchase_price', true, 2); ?> <?php echo functions::form_draw_currencies_list('purchase_price_currency_code', true); ?>
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
            <th style="text-align: left;"><?php echo language::translate('title_price', 'Price'); ?></th>
            <th style="text-align: left;"><?php echo language::translate('title_price_incl_tax', 'Price Incl. Tax'); ?> (<a id="price-incl-tax-tooltip" href="#">?</a>)</th>
          </tr>
          <tr>
            <td><?php echo functions::form_draw_currency_field(settings::get('store_currency_code'), 'prices['. settings::get('store_currency_code') .']', true, 'data-currency-price="" placeholder=""'); ?></td>
            <td><?php echo functions::form_draw_decimal_field('gross_prices['. settings::get('store_currency_code') .']', '', currency::$currencies[settings::get('store_currency_code')]['decimals'], 0, null, 'placeholder=""'); ?></td>
          </tr>
<?php
foreach (currency::$currencies as $currency) {
  if ($currency['code'] == settings::get('store_currency_code')) continue;
?>
          <tr>
            <td><?php echo functions::form_draw_currency_field($currency['code'], 'prices['. $currency['code'] .']', true, 'data-currency-price="" placeholder=""'); ?></td>
            <td><?php echo functions::form_draw_decimal_field('gross_prices['. $currency['code'] .']', '', $currency['decimals'], 0, null, 'placeholder=""'); ?></td>
          </tr>
<?php
}
?>
        </table>
        <script>
          function get_tax_rate() {
            switch ($('select[name=tax_class_id]').val()) {
<?php
  $tax_classes_query = database::query(
    "select * from ". DB_TABLE_TAX_CLASSES ."
    order by name asc;"
  );
  while ($tax_class = database::fetch($tax_classes_query)) {
    echo '              case "'. $tax_class['id'] . '":'. PHP_EOL
       . '                return '. tax::get_tax(100, $tax_class['id'], 'store') .';' . PHP_EOL;
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

          $('select[name="tax_class_id"], input[name^="prices["]').bind('input propertyChange', function() {

            var currency_code = $(this).attr('name').match(/^prices\[([A-Z]{3})\]$/)[1];
            var price = Number($(this).val());
            var net_price = Number($(this).val()) * (1+(get_tax_rate()/100));

          // Update net price
            if (net_price == 0) {
              $('input[name="gross_prices['+ currency_code +']"]').val('');
            } else {
              $('input[name="gross_prices['+ currency_code +']"]').val(net_price.toFixed(get_currency_decimals(currency_code)));
            }

            if (currency_code != '<?php echo settings::get('store_currency_code'); ?>') return;

          // Update system currency price
            var currency_price = price * get_currency_value(currency_code);
            var currency_gross_price = net_price * get_currency_value(currency_code);

            if (currency_price == 0) {
              $('input[name="prices['+ currency_code +']"]').attr('placeholder', '')
            } else {
              $('input[name="prices['+ currency_code +']"]').attr('placeholder', price.toFixed(get_currency_decimals(currency_code)));
            };

          // Update currency prices
            $('input[name^="prices"]').each(function(){
              var currency_code = $(this).attr('name').replace(/^prices\[(.*)\]$/, "$1");

              if (currency_code != '<?php echo settings::get('store_currency_code'); ?>') {

                var currency_price = price * get_currency_value(currency_code);
                var currency_gross_price = net_price * get_currency_value(currency_code);

                if (currency_price == 0) {
                  $('input[name="prices['+ currency_code +']"]').attr('placeholder', Number(0).toFixed(get_currency_decimals(currency_code)))
                  $('input[name="gross_prices['+ currency_code +']"]').attr('placeholder', Number(0).toFixed(get_currency_decimals(currency_code)))
                } else {
                  $('input[name="prices['+ currency_code +']"]').attr('placeholder', currency_price.toFixed(get_currency_decimals(currency_code)));
                  $('input[name="gross_prices['+ currency_code +']"]').attr('placeholder', currency_gross_price.toFixed(get_currency_decimals(currency_code)));
                };

              }
            });
          });

          $('input[name^="gross_prices"]').bind('input propertyChange', function() {
            
            var currency_code = $(this).attr('name').match(/^gross_prices\[([A-Z]{3})\]$/)[1];
            var price = Number($(this).val()) / (1+(get_tax_rate()/100));
            var net_price = Number($(this).val());

          // Update price
            if (price == 0) {
              $('input[name="prices['+ currency_code +']"]').val('');
            } else {
              $('input[name="prices['+ currency_code +']"]').val(price.toFixed(get_currency_decimals(currency_code)));
            }

            if (currency_code != "<?php echo settings::get("store_currency_code"); ?>") return;

          // Update system currency price
            var currency_price = price * get_currency_value(currency_code);
            var currency_gross_price = net_price * get_currency_value(currency_code);

            if (currency_price == 0) {
              $('input[name="prices['+ currency_code +']"]').attr('placeholder', Number(0).toFixed(get_currency_decimals(currency_code)))
              $('input[name="gross_prices['+ currency_code +']"]').attr('placeholder', Number(0).toFixed(get_currency_decimals(currency_code)))
            } else {
              $('input[name="prices['+ currency_code +']"]').attr('placeholder', currency_price.toFixed(get_currency_decimals(currency_code)));
              $('input[name="gross_prices['+ currency_code +']"]').attr('placeholder', currency_gross_price.toFixed(get_currency_decimals(currency_code)));
            };

          // Update currency prices
            $('input[name^="prices"]').each(function() {
              var currency_code = $(this).attr("name").replace(/^prices\[(.*)\]$/, '$1');

              if (currency_code != "<?php echo settings::get("store_currency_code"); ?>") {

                var currency_price = price * get_currency_value(currency_code);
                var currency_gross_price = net_price * get_currency_value(currency_code);

                if (currency_price == 0) {
                  $('input[name="prices['+ currency_code +']"]').attr('placeholder', Number(0).toFixed(get_currency_decimals(currency_code)))
                  $('input[name="gross_prices['+ currency_code +']"]').attr('placeholder', Number(0).toFixed(get_currency_decimals(currency_code)))
                } else {
                  $('input[name="prices['+ currency_code +']"]').attr('placeholder', currency_price.toFixed(get_currency_decimals(currency_code)));
                  $('input[name="gross_prices['+ currency_code +']"]').attr('placeholder', currency_price.toFixed(get_currency_decimals(currency_code)));
                };

              }
            });
          });

        // Initiate
          $('input[name^="prices"]').trigger('change');
          $('input[name^="gross_prices"]').trigger('change');

          $('body').on('click', '#price-incl-tax-tooltip', function(e) {
            e.preventDefault;
            alert("<?php echo str_replace(array("\r", "\n", "\""), array('', '', "\\\""), language::translate('tooltip_price_incl_tax', 'This field helps you calculate net price based on the store region tax. All prices input to database are always excluding tax.')); ?>");
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
              <?php echo functions::form_draw_currency_field($currency_code, 'campaigns['.$key.']['. $currency_code. ']', isset($_POST['campaigns'][$key][$currency_code]) ? number_format((float)$_POST['campaigns'][$key][$currency_code], 4, '.', '') : '', 'data-size="small"'); ?>
            </td>
<?php
  }
?>
            <td><br /><a id="remove-campaign" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
          </tr>
          <?php } ?>
          <tr>
            <td colspan="5"><a id="add-campaign" href="#" title="<?php echo language::translate('text_add', 'Add'); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?></a></td>
          </tr>
        </table>

        <script>
          $('body').on('input propertyChange', 'input[name^="campaigns"][name$="[percentage]"]', function() {
            var parent = $(this).closest('tr');

            <?php foreach (currency::$currencies as $currency) { ?>
            if ($('input[name^="prices"][name$="[<?php echo $currency['code']; ?>]"]').val() > 0) {
              var value = $('input[name="prices[<?php echo $currency['code']; ?>]"]').val() * ((100 - $(this).val()) / 100);
              value = Number(value).toFixed(<?php echo $currency['decimals']; ?>);
              $(parent).find('input[name$="[<?php echo $currency['code']; ?>]"]').val(value);
            } else {
              $(parent).find('input[name$="[<?php echo $currency['code']; ?>]"]').val('');
            }
            <?php } ?>

            <?php foreach (currency::$currencies as $currency) { ?>
            var value = $(parent).find('input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val() * <?php echo $currency['value']; ?>;
            value = Number(value).toFixed(<?php echo $currency['decimals']; ?>);
            $(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').attr('placeholder', value);
            <?php } ?>
          });

          $('body').on('input propertyChange', 'input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]', function() {
            var parent = $(this).closest("tr");
            var percentage = ($('input[name="prices[<?php echo settings::get('store_currency_code'); ?>]"]').val() - $(this).val()) / $('input[name="prices[<?php echo settings::get('store_currency_code'); ?>]"]').val() * 100;
            percentage = Number(percentage).toFixed(2);
            $(parent).find('input[name$="[percentage]"]').val(percentage);

            <?php foreach (currency::$currencies as $currency) { ?>
            var value = 0;
            value = $(parent).find('input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val() * <?php echo $currency['value']; ?>;
            value = Number(value).toFixed(<?php echo $currency['decimals']; ?>);
            $(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').attr('placeholder', value);
            if ($(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').val() == 0) {
              $(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').val('');
            }
            <?php } ?>
          });
          $('input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').trigger('keyup');

          $('body').on('click', '#remove-campaign', function(event) {
            event.preventDefault();
            $(this).closest('tr').remove();
          });

          var new_campaign_i = 1;
          $('body').on('click', '#add-campaign', function(event) {
            event.preventDefault();
            var output = '<tr>'
                       + '  <td><strong><?php functions::general_escape_js(language::translate('title_start_date', 'Start Date')); ?></strong><br />'
                       + '    <?php echo functions::general_escape_js(functions::form_draw_hidden_field('campaigns[new_campaign_i][id]', '') . functions::form_draw_datetime_field('campaigns[new_campaign_i][start_date]', '')); ?>'
                       + '  </td>'
                       + '  <td><strong><?php echo functions::general_escape_js(language::translate('title_end_date', 'End Date')); ?></strong><br />'
                       + '    <?php echo functions::general_escape_js(functions::form_draw_datetime_field('campaigns[new_campaign_i][end_date]', '')); ?>'
                       + '  </td>'
                       + '  <td>- %<br />'
                       + '    <?php echo functions::general_escape_js(functions::form_draw_decimal_field('campaigns[new_campaign_i][percentage]', '', 2, 0, null, 'data-size="tiny"')); ?>'
                       + '  </td>'
                       + '  <td><strong><?php echo functions::general_escape_js(settings::get('store_currency_code')); ?></strong><br />'
                       + '    <?php echo functions::general_escape_js(functions::form_draw_currency_field(settings::get('store_currency_code'), 'campaigns[new_campaign_i]['. settings::get('store_currency_code') .']', '')); ?>'
                       + '  </td>'
<?php
  foreach (array_keys(currency::$currencies) as $currency_code) {
    if ($currency_code == settings::get('store_currency_code')) continue;
?>
                       + '  <td><?php echo functions::general_escape_js($currency_code); ?><br />'
                       + '    <?php echo functions::general_escape_js(functions::form_draw_currency_field($currency_code, 'campaigns[new_campaign_i]['. $currency_code .']', '', 'data-size="small"')); ?>'
                       + '  </td>'
<?php
  }
?>
                       + '  <td><br /><a id="remove-campaign" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
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
        <table id="table-options">
          <tr>
            <th>&nbsp;</th>
            <th style="text-align: left; vertical-align: text-top;"><?php echo language::translate('title_group', 'Group'); ?></th>
            <th style="text-align: left; vertical-align: text-top;"><?php echo language::translate('title_value', 'Value'); ?></th>
            <th style="vertical-align: text-top;"><?php echo language::translate('title_price_operator', 'Price Operator'); ?></th>
            <th style="vertical-align: text-top; text-align: left;"><?php echo language::translate('title_price_adjust', 'Price Adjust'); ?></th>
<?php
  foreach (array_keys(currency::$currencies) as $currency_code) {
    if ($currency_code == settings::get('store_currency_code')) continue;
?>
            <th style="vertical-align: text-top; text-align: center;"></th>
<?php
  }
?>
            <th style="vertical-align: text-top;">&nbsp;</th>
          </tr>
  <?php
  if (!empty($_POST['options'])) {
    foreach (array_keys($_POST['options']) as $key) {
  ?>
          <tr>
            <td><a class="add" href="#" title="<?php echo language::translate('text_insert_before', 'Insert before'); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?></a><?php echo functions::form_draw_hidden_field('options['.$key.'][id]', true); ?></td>
            <td><?php echo functions::form_draw_option_groups_list('options['.$key.'][group_id]', true); ?></td>
            <td><?php echo functions::form_draw_option_values_list($_POST['options'][$key]['group_id'], 'options['.$key.'][value_id]', true); ?></td>
            <td style="text-align: center;"><?php echo functions::form_draw_select_field('options['.$key.'][price_operator]', array('+','%','*'), $_POST['options'][$key]['price_operator'], false, 'data-size="auto"'); ?></td>
            <td><?php echo functions::form_draw_currency_field(settings::get('store_currency_code'), 'options['.$key.']['.settings::get('store_currency_code').']', true); ?></td>
<?php
      foreach (array_keys(currency::$currencies) as $currency_code) {
        if ($currency_code == settings::get('store_currency_code')) continue;
?>
            <td><?php echo functions::form_draw_currency_field($currency_code, 'options['.$key.']['. $currency_code. ']', number_format((float)$_POST['options'][$key][$currency_code], 4, '.', '')); ?></td>
<?php
      }
?>
            <td style="white-space: nowrap; text-align: right;"><a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"'); ?></a> <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"'); ?></a> <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
          </tr>
<?php
    }
  }
?>
          <tr>
            <td><a class="add" href="#" title="<?php echo language::translate('text_insert_before', 'Insert before'); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?></a></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
        </table>
        <script>
          $('#table-options').on('click', '.remove', function(event) {
            event.preventDefault();
            $(this).closest('tr').remove();
          });

          $('#table-options').on('click', '.move-up, .move-down', function(event) {
            event.preventDefault();
            var row = $(this).closest('tr');
            if ($(this).is('.move-up') && $(row).prevAll().length > 1) {
              $(row).insertBefore($(row).prev());
            } else if ($(this).is('.move-down') && $(row).nextAll().length > 0) {
              $(row).insertAfter($(row).next());
            }
          });

          $('#table-options').on('input propertyChange', 'select[name^="options"][name$="[group_id]"]', function(){
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
                $('select[name="'+ valueField +'"]').html('');
                if ($('select[name="'+ valueField +'"]').attr('disabled')) $('select[name="'+ valueField +'"]').removeAttr('disabled');
                if (data) {
                  $.each(data, function(i, zone) {
                    $('select[name="'+ valueField +'"]').append('<option value="'+ zone.id +'">'+ zone.name +'</option>');
                  });
                } else {
                  $('select[name="'+ valueField +'"]').attr('disabled', 'disabled');
                }
              },
              complete: function() {
                $('body').css('cursor', 'auto');
              }
            });
          });

          var new_option_i = 1;
          $('#table-options').on('click', '.add', function(event) {
            event.preventDefault();
            var output = '<tr>'
                       + '  <td><a class="add" href="#" title="<?php echo functions::general_escape_js(language::translate('text_insert_before', 'Insert before'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"')); ?></a><?php echo functions::general_escape_js(functions::form_draw_hidden_field('options[new_option_i][id]', '')); ?></td>'
                       + '  <td><?php echo functions::general_escape_js(functions::form_draw_option_groups_list('options[new_option_i][group_id]', '')); ?></td>'
                       + '  <td><?php echo functions::general_escape_js(functions::form_draw_select_field('options[new_option_i][value_id]', array(array('','')), '')); ?></td>'
                       + '  <td style="text-align: center;"><?php echo functions::general_escape_js(functions::form_draw_select_field('options[new_option_i][price_operator]', array('+','*'), '+', false, 'data-size="auto"')); ?></td>'
                       + '  <td><?php echo functions::general_escape_js(functions::form_draw_currency_field(settings::get('store_currency_code'), 'options[new_option_i]['. settings::get('store_currency_code') .']', 0)); ?></td>'
<?php
  foreach (array_keys(currency::$currencies) as $currency_code) {
    if ($currency_code == settings::get('store_currency_code')) continue;
?>
                       + '  <td><?php echo functions::general_escape_js(functions::form_draw_currency_field($currency_code, 'options[new_option_i]['. $currency_code. ']', '')); ?></td>'
<?php
  }
?>
                       + '  <td style="white-space: nowrap; text-align: right;"><a class="move-up" href="#" title="<?php echo functions::general_escape_js(language::translate('text_move_up', 'Move up'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"')); ?></a> <a class="move-down" href="#" title="<?php echo functions::general_escape_js(language::translate('text_move_down', 'Move down'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"')); ?></a> <a class="remove" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
                       + '</tr>';
            output = output.replace(/new_option_i/g, 'new_' + new_option_i);
            $(this).closest('tr').before(output);
            new_option_i++;
          });
        </script>
      </div>

      <div id="tab-options-stock">
        <h2><?php echo language::translate('title_options_stock', 'Options Stock'); ?></h2>
        <table id="table-options-stock">
          <tr>
            <th style="vertical-align: text-top; text-align: left;"><?php echo language::translate('title_option', 'Option'); ?></th>
            <th style="vertical-align: text-top; text-align: center;"><?php echo language::translate('title_sku', 'SKU'); ?></th>
            <th style="vertical-align: text-top; text-align: center;"><?php echo language::translate('title_qty', 'Qty'); ?></th>
            <th style="vertical-align: text-top; text-align: center;"><?php echo language::translate('title_weight', 'Weight'); ?></th>
            <th style="vertical-align: text-top; text-align: left;"><?php echo language::translate('title_dimensions', 'Dimensions'); ?></th>
            <th style="vertical-align: text-top; text-align: left;">&nbsp;</th>
          </tr>
<?php
  if (!empty($_POST['options_stock'])) {
    foreach (array_keys($_POST['options_stock']) as $key) {
?>
          <tr>
            <td style="white-space: nowrap;"><?php echo functions::form_draw_hidden_field('options_stock['.$key.'][id]', true); ?><?php echo functions::form_draw_hidden_field('options_stock['.$key.'][combination]', true); ?>
            <?php echo functions::form_draw_hidden_field('options_stock['.$key.'][name]['. language::$selected['name'] .']', true); ?>
            <?php echo $_POST['options_stock'][$key]['name'][language::$selected['code']]; ?></td>
            <td><?php echo functions::form_draw_text_field('options_stock['.$key.'][sku]', true, 'data-size="small"'); ?></td>
            <td><?php echo functions::form_draw_number_field('options_stock['.$key.'][quantity]', true); ?></td>
            <td style="white-space: nowrap;"><?php echo functions::form_draw_decimal_field('options_stock['.$key.'][weight]', true); ?> <?php echo functions::form_draw_weight_classes_list('options_stock['.$key.'][weight_class]', true); ?></td>
            <td style="white-space: nowrap;"><?php echo functions::form_draw_decimal_field('options_stock['.$key.'][dim_x]', true); ?> <sub>x</sub> <?php echo functions::form_draw_decimal_field('options_stock['.$key.'][dim_y]', true); ?> <sub>x</sub> <?php echo functions::form_draw_decimal_field('options_stock['.$key.'][dim_z]', true); ?> <?php echo functions::form_draw_length_classes_list('options_stock['.$key.'][dim_class]', true); ?></td>
            <td style="white-space: nowrap; text-align: right;"><a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"'); ?></a> <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"'); ?></a> <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
          </tr>
<?php
    }
  }
?>
        </table>

        <p>&nbsp;</p>

        <fieldset style="display: inline-block;">
          <legend><h3><?php echo language::translate('title_new_combination', 'New Combination'); ?></h3></legend>
          <table id="table-option-combo">
            <tr>
              <th style="vertical-align: text-top;"><?php echo language::translate('title_group', 'Group'); ?></th>
              <th style="vertical-align: text-top;"><?php echo language::translate('title_value', 'Value'); ?></th>
              <th style="vertical-align: text-top;">&nbsp;</th>
            </tr>
            <tr>
              <td><?php echo functions::form_draw_option_groups_list('new_option[new_1][group_id]', ''); ?></td>
              <td><?php echo functions::form_draw_select_field('new_option[new_1][value_id]', array(array('','')), '', false, false, 'disabled="disabled"'); ?></td>
            </tr>
            <tr>
              <td><a class="add" href="#" title="<?php echo language::translate('text_add', 'Add'); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?> <?php echo language::translate('title_another_option', 'Another Option'); ?></a></td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td><?php echo functions::form_draw_button('add_combination', language::translate('title_add_combination', 'Add Combination'), 'button'); ?></td>
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
                         + '  <td><?php echo functions::general_escape_js(functions::form_draw_option_groups_list('new_option[option_index][group_id]', '')); ?></td>'
                         + '  <td><?php echo functions::general_escape_js(functions::form_draw_select_field('new_option[option_index][value_id]', array(array('','')), '', false, false, 'disabled="disabled"')); ?></td>'
                         + '  <td><a class="remove" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
                         + '</tr>';
              output = output.replace(/option_index/g, 'new_' + option_index);
              $(this).closest('tr').before(output);
              option_index++;
            });

            $("#table-option-combo").on("click", ".remove", function(event) {
              event.preventDefault();
              $(this).closest('tr').remove();
            });

            $("#table-option-combo").on("input propertyChange", "select[name^='new_option'][name$='[group_id]']", function(){
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
            $("#table-option-combo").on("click", "button[name='add_combination']", function(event) {
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
                         + '  <td><?php echo functions::general_escape_js(functions::form_draw_hidden_field('options_stock[new_option_stock_i][id]', '') . functions::form_draw_hidden_field('options_stock[new_option_stock_i][combination]', 'new_option_code') . functions::form_draw_hidden_field('options_stock[new_option_stock_i][name]['. language::$selected['code'] .']', 'new_option_name')); ?>new_option_name</td>'
                         + '  <td><?php echo functions::general_escape_js(functions::form_draw_text_field('options_stock[new_option_stock_i][sku]', '', 'data-size="small"')); ?></td>'
                         + '  <td><?php echo functions::general_escape_js(functions::form_draw_number_field('options_stock[new_option_stock_i][quantity]', '0')); ?></td>'
                         + '  <td style="white-space: nowrap;"><?php echo functions::general_escape_js(functions::form_draw_decimal_field('options_stock[new_option_stock_i][weight]', '0.00') .' '. functions::form_draw_weight_classes_list('options_stock[new_option_stock_i][weight_class]', '')); ?></td>'
                         + '  <td style="white-space: nowrap;"><?php echo functions::general_escape_js(functions::form_draw_decimal_field('options_stock[new_option_stock_i][dim_x]', '0.00') .' <sub>x</sub> '. functions::form_draw_decimal_field('options_stock[new_option_stock_i][dim_y]', '0.00') .' <sub>x</sub> '. functions::form_draw_decimal_field('options_stock[new_option_stock_i][dim_z]', '0.00') .' '. functions::form_draw_length_classes_list('options_stock[new_option_stock_i][dim_class]', '')); ?></td>'
                         + '  <td style="white-space: nowrap; text-align: right;"><a class="move-up" href="#" title="<?php echo functions::general_escape_js(language::translate('text_move_up', 'Move up'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"')); ?></a> <a class="move-down" href="#" title="<?php echo functions::general_escape_js(language::translate('text_move_down', 'Move down'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"')); ?></a> <a class="remove" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
                         + '</tr>';
              while ($("input[name='options_stock[new_"+new_option_stock_i+"]']").length) new_option_stock_i++;
              output = output.replace(/new_option_stock_i/g, 'new_' + new_option_stock_i);
              output = output.replace(/new_option_code/g, new_option_code);
              output = output.replace(/new_option_name/g, new_option_name);
              $("#table-options-stock").append(output);
              new_option_stock_i++;
            });
          </script>
        </fieldset>
      </div>
    </div>
  </div>

  <p><span class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($product->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></span></p>

<?php echo functions::form_draw_form_end(); ?>