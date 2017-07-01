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
    exit;
  }

  list($product_image_width, $product_image_height) = functions::image_scale_by_width(320, settings::get('product_image_ratio'));

  functions::draw_lightbox();
?>
<style>
#images .thumbnail {
  margin: 0;
}
#images .image {
  overflow: hidden;
}
#images .thumbnail {
  margin-right: 15px;
}
#images img {
  max-width: 50px;
  max-height: 50px;
}
#images .actions {
  text-align: right;
  padding: 0.25em 0;
}
</style>

<h1><?php echo $app_icon; ?> <?php echo !empty($product->data['id']) ? language::translate('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : language::translate('title_add_new_product', 'Add New Product'); ?></h1>

<?php echo functions::form_draw_form_begin('product_form', 'post', false, true); ?>

  <div class="">

    <ul class="nav nav-tabs">
      <li role="presentation" class="active"><a data-toggle="tab" href="#tab-general"><?php echo language::translate('title_general', 'General'); ?></a></li>
      <li role="presentation"><a data-toggle="tab" href="#tab-information"><?php echo language::translate('title_information', 'Information'); ?></a></li>
      <li role="presentation"><a data-toggle="tab" href="#tab-prices"><?php echo language::translate('title_prices', 'Prices'); ?></a></li>
      <li role="presentation"><a data-toggle="tab" href="#tab-options"><?php echo language::translate('title_options', 'Options'); ?></a></li>
      <li role="presentation"><a data-toggle="tab" href="#tab-stock-options"><?php echo language::translate('title_stock_options', 'Stock Options'); ?></a></li>
    </ul>

    <div class="tab-content">
      <div id="tab-general" class="tab-pane active" style="max-width: 960px;">

        <div class="row">
          <div class="col-md-4">

            <div class="form-group">
              <label><?php echo language::translate('title_status', 'Status'); ?></label>
                <?php echo functions::form_draw_toggle('status', isset($_POST['status']) ? $_POST['status'] : '0', 'e/d'); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_categories', 'Categories'); ?></label>
              <div class="form-control" style="height: auto; height: 15em; overflow-y: auto;">
<?php
  function custom_catalog_tree($category_id=0, $depth=1, $count=0) {

    $output = '';

    if ($category_id == 0) {
      $output .= '<div class="checkbox" id="category-id-'. $category_id .'"><label>'. functions::form_draw_checkbox('categories[]', '0', (isset($_POST['categories']) && in_array('0', $_POST['categories'], true)) ? '0' : false, 'data-name="'. htmlspecialchars(language::translate('title_root', 'Root')) .'" data-priority="0"') .' '. functions::draw_fonticon('fa-folder', 'title="'. language::translate('title_root', 'Root') .'" style="color: #cccc66;"') .' ['. language::translate('title_root', 'Root') .']</label></div>' . PHP_EOL;
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

      $output .= '  <div class="checkbox"><label>'. functions::form_draw_checkbox('categories[]', $category['id'], true, 'data-name="'. htmlspecialchars($category['name']) .'" data-priority="'. $count .'"') .' '. functions::draw_fonticon('fa-folder fa-lg', 'style="color: #cccc66; margin-left: '. ($depth*1) .'em;"') .' '. $category['name'] .'</label></div>' . PHP_EOL;

      if (database::num_rows(database::query("select * from ". DB_TABLE_CATEGORIES ." where parent_id = '". $category['id'] ."' limit 1;")) > 0) {
        $output .= custom_catalog_tree($category['id'], $depth+1, $count);
      }
    }

    database::free($categories_query);

    return $output;
  }

  echo custom_catalog_tree();
?>
              </div>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_default_category', 'Default Category'); ?></label>
              <?php echo functions::form_draw_select_field('default_category_id', array(), true); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_product_groups', 'Product Groups'); ?></label>
              <div style="height: auto; height: 11em; overflow-y: auto;" class="form-control">
<?php
  // Output product groups
    $product_groups_query = database::query(
      "select pg.id, pgi.name from ". DB_TABLE_PRODUCT_GROUPS ." pg
      left join ". DB_TABLE_PRODUCT_GROUPS_INFO ." pgi on (pgi.product_group_id = pg.id and pgi.language_code = '". language::$selected['code'] ."')
      order by pgi.name asc;"
    );
    if (database::num_rows($product_groups_query)) {
      while ($product_group = database::fetch($product_groups_query)) {
        echo '<div class="form-group">' . PHP_EOL
           . '  <label>'. $product_group['name'] .'</label>' . PHP_EOL;
        $product_groups_values_query = database::query(
          "select pgv.id, pgvi.name from ". DB_TABLE_PRODUCT_GROUPS_VALUES ." pgv
          left join ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ." pgvi on (pgvi.product_group_value_id = pgv.id and pgvi.language_code = '". language::$selected['code'] ."')
          where pgv.product_group_id = '". (int)$product_group['id'] ."'
          order by pgvi.name asc;"
        );
        while ($product_group_value = database::fetch($product_groups_values_query)) {
          echo '  <div class="checkbox"><label>'. functions::form_draw_checkbox('product_groups[]', $product_group['id'].'-'.$product_group_value['id'], true) .' '. $product_group_value['name'] .'</label></div>' . PHP_EOL;
        }
        echo '</div>' . PHP_EOL;
      }
    } else {
?>
              <div><em><?php echo language::translate('description_no_existing_product_groups', 'There are no existing product groups.'); ?></em></div>
<?php
    }
?>
              </div>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_date_valid_from', 'Date Valid From'); ?></label>
              <?php echo functions::form_draw_date_field('date_valid_from', true); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_date_valid_to', 'Date Valid To'); ?></label>
              <?php echo functions::form_draw_date_field('date_valid_to', true); ?>
            </div>

            <?php if (!empty($product->data['id'])) { ?>
            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_date_updated', 'Date Updated'); ?></label>
                <div><?php echo strftime('%e %b %Y %H:%M', strtotime($product->data['date_updated'])); ?></div>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_date_created', 'Date Created'); ?></label>
                <div><?php echo strftime('%e %b %Y %H:%M', strtotime($product->data['date_created'])); ?></div>
              </div>
            </div>
            <?php } ?>
          </div>

          <div class="col-md-4">

            <div class="form-group">
              <label><?php echo language::translate('title_name', 'Name'); ?></label>
              <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true, ''); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_code', 'Code'); ?></label>
              <?php echo functions::form_draw_text_field('code', true); ?>
            </div>

            <div class="form-group">
              <div class="input-group">
                <label class="input-group-addon" style="width: 100px;"><?php echo language::translate('title_sku', 'SKU'); ?> <a href="https://en.wikipedia.org/wiki/Stock_keeping_unit" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
                <?php echo functions::form_draw_text_field('sku', true); ?>
              </div>

              <div class="input-group">
                <label class="input-group-addon" style="width: 100px;"><?php echo language::translate('title_gtin', 'GTIN'); ?> <a href="https://en.wikipedia.org/wiki/Global_Trade_Item_Number" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
                <?php echo functions::form_draw_text_field('gtin', true); ?>
              </div>

              <div class="input-group">
                <label class="input-group-addon" style="width: 100px;"><?php echo language::translate('title_taric', 'TARIC'); ?> <a href="https://en.wikipedia.org/wiki/TARIC_code" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
                <?php echo functions::form_draw_text_field('taric', true); ?>
              </div>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_quantity', 'Quantity'); ?></label>
              <div class="row">
                <div class="col-md-6">
                  <?php echo functions::form_draw_decimal_field('quantity', true, 4, null, null, 'style="text-align: center;"'); ?>
                </div>
                <div class="col-md-6">
                  <?php echo functions::form_draw_quantity_units_list('quantity_unit_id', true, false); ?>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_weight', 'Weight'); ?></label>
              <div class="input-group">
                <?php echo functions::form_draw_decimal_field('weight', true, 3, 0, null, 'style="text-align: center;"'); ?>
                <?php echo functions::form_draw_weight_classes_list('weight_class', true, false, 'style="width: 25%;"'); ?>
              </div>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_width_height_length', 'Width x Height x Length'); ?></label>
              <div class="input-group">
                <?php echo functions::form_draw_decimal_field('dim_x', true, 2, 0, null, 'style="text-align: center;"'); ?>
                <?php echo functions::form_draw_decimal_field('dim_y', true, 2, 0, null, 'style="text-align: center;"'); ?>
                <?php echo functions::form_draw_decimal_field('dim_z', true, 2, 0, null, 'style="text-align: center;"'); ?>
                <?php echo functions::form_draw_length_classes_list('dim_class', true, false, 'style="width: auto;"'); ?>
              </div>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_delivery_status', 'Delivery Status'); ?></label>
              <?php echo functions::form_draw_delivery_statuses_list('delivery_status_id', true); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_sold_out_status', 'Sold Out Status'); ?></label>
              <?php echo functions::form_draw_sold_out_statuses_list('sold_out_status_id', true); ?>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label><?php echo language::translate('title_images', 'Images'); ?></label>
              <div class="thumbnail">
<?php
  if (isset($product->data['id']) && !empty($product->data['images'])) {
    $image = current($product->data['images']);
    echo '<img class="main-image" src="'. functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $image['filename'], $product_image_width, $product_image_height, settings::get('product_image_clipping')) .'" alt="" />';
    reset($product->data['images']);
  } else {
    echo '<img class="main-image" src="'. functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'no_image.png', $product_image_width, $product_image_height, settings::get('product_image_clipping')) .'" alt="" />';
  }
?>
              </div>
            </div>

            <div id="images">

              <div class="images">
                <?php if (!empty($_POST['images'])) foreach (array_keys($_POST['images']) as $key) { ?>
                <div class="image form-group">
                  <?php echo functions::form_draw_hidden_field('images['.$key.'][id]', true); ?>
                  <?php echo functions::form_draw_hidden_field('images['.$key.'][filename]', $_POST['images'][$key]['filename']); ?>

                  <div class="thumbnail pull-left">
                    <img src="<?php echo functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product->data['images'][$key]['filename'], $product_image_width, $product_image_height, settings::get('product_image_clipping')); ?>" alt="" />
                  </div>

                  <div class="input-group">
                    <?php echo functions::form_draw_text_field('images['.$key.'][new_filename]', isset($_POST['images'][$key]['new_filename']) ? $_POST['images'][$key]['new_filename'] : $_POST['images'][$key]['filename']); ?>
                    <div class="input-group-addon">
                      <a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"'); ?></a>
                      <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"'); ?></a>
                      <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a>
                    </div>
                  </div>
                </div>
                <?php } ?>
              </div>

              <div class="new-images">
                <div class="image form-group">
                  <div class="thumbnail pull-left">
                    <img src="<?php echo functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'no_image.png', $product_image_width, $product_image_height, settings::get('product_image_clipping')); ?>" alt="" />
                  </div>

                  <div class="input-group">
                    <?php echo functions::form_draw_file_field('new_images[]'); ?>
                    <div class="input-group-addon">
                      <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a>
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <a href="#" class="add" title="<?php echo language::translate('text_add', 'Add'); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?></a>
              </div>
            </div>
          </div>
        </div>

      </div>

      <div id="tab-information" class="tab-pane" style="max-width: 640px;">

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_manufacturer', 'Manufacturer'); ?></label>
            <?php echo functions::form_draw_manufacturers_list('manufacturer_id', true); ?>
          </div>

          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_supplier', 'Supplier'); ?></label>
            <?php echo functions::form_draw_suppliers_list('supplier_id', true); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md">
            <label><?php echo language::translate('title_keywords', 'Keywords'); ?></label>
            <?php echo functions::form_draw_text_field('keywords', true); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md">
            <label><?php echo language::translate('title_short_description', 'Short Description'); ?></label>
            <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'short_description['. $language_code .']', true); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md">
            <label><?php echo language::translate('title_description', 'Description'); ?></label>
            <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_wysiwyg_field($language_code, 'description['. $language_code .']', true, 'style="height: 125px;"'); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md">
            <label><?php echo language::translate('title_attributes', 'Attributes'); ?> <a class="attributes-hint" href="#"><?php echo functions::draw_fonticon('fa-question-circle'); ?></a></label>
            <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_textarea($language_code, 'attributes['. $language_code .']', true, 'style="height: 250px;"'); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_head_title', 'Head Title'); ?></label>
            <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'head_title['. $language_code .']', true); ?>
          </div>

          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_meta_description', 'Meta Description'); ?></label>
            <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'meta_description['. $language_code .']', true); ?>
          </div>
        </div>
      </div>

      <div id="tab-prices" class="tab-pane">

        <div id="prices" style="max-width: 640px;">
          <h2><?php echo language::translate('title_prices', 'Prices'); ?></h2>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_purchase_price', 'Purchase Price'); ?></label>
              <div class="input-group">
                <?php echo functions::form_draw_decimal_field('purchase_price', true, 2, 0, null, 'style="width: 40%;"'); ?>
                <?php echo functions::form_draw_currencies_list('purchase_price_currency_code', true, false, 'style="width: 60%;"'); ?>
              </div>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_tax_class', 'Tax Class'); ?></label>
              <?php echo functions::form_draw_tax_classes_list('tax_class_id', true); ?>
            </div>
          </div>

          <table class="table table-striped data-table">
            <thead>
              <tr>
                <td class="col-md-6"><?php echo language::translate('title_price', 'Price'); ?></td>
                <td class="col-md-6"><?php echo language::translate('title_price_incl_tax', 'Price Incl. Tax'); ?> (<a id="price-incl-tax-tooltip" href="#">?</a>)</td>
              </tr>
            </thead>
            <tbody>
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
            </tbody>
          </table>
        </div>

        <h2><?php echo language::translate('title_campaigns', 'Campaigns'); ?></h2>
        <div class="table-responsive">
          <table id="table-campaigns" class="table table-striped data-table">
            <tbody>
              <?php if (!empty($_POST['campaigns'])) foreach (array_keys($_POST['campaigns']) as $key) { ?>
              <tr>
                <td><?php echo language::translate('title_start_date', 'Start Date'); ?><br />
                  <?php echo functions::form_draw_hidden_field('campaigns['.$key.'][id]', true) . functions::form_draw_datetime_field('campaigns['.$key.'][start_date]', true); ?>
                </td>
                <td><?php echo language::translate('title_end_date', 'End Date'); ?><br />
                  <?php echo functions::form_draw_datetime_field('campaigns['.$key.'][end_date]', true); ?>
                </td>
                <td>- %<br />
                  <?php echo functions::form_draw_decimal_field('campaigns['.$key.'][percentage]', '', 2, 0, null); ?>
                </td>
                <td><?php echo settings::get('store_currency_code'); ?><br />
                  <?php echo functions::form_draw_currency_field(settings::get('store_currency_code'), 'campaigns['.$key.']['. settings::get('store_currency_code') .']', true); ?>
                </td>
<?php
  foreach (array_keys(currency::$currencies) as $currency_code) {
    if ($currency_code == settings::get('store_currency_code')) continue;
?>
                <td><?php echo $currency_code; ?><br />
                <?php echo functions::form_draw_currency_field($currency_code, 'campaigns['.$key.']['. $currency_code. ']', isset($_POST['campaigns'][$key][$currency_code]) ? number_format((float)$_POST['campaigns'][$key][$currency_code], 4, '.', '') : ''); ?>
                </td>
<?php
  }
?>
                <td><br /><a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
              </tr>
            </tbody>
            <?php } ?>
            <tfoot>
              <tr>
                <td colspan="<?php echo 5 + count(currency::$currencies) - 1; ?>"><a class="add" href="#" title="<?php echo language::translate('text_add', 'Add'); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?></a></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div id="tab-options" class="tab-pane">
        <h2><?php echo language::translate('title_options', 'Options'); ?></h2>
        <div class="table-responsive">
          <table id="table-options" class="table table-striped data-table">
            <thead>
              <tr>
                <th style="min-width: 400px;"><?php echo language::translate('title_group', 'Group'); ?></th>
                <th><?php echo language::translate('title_value', 'Value'); ?></th>
                <th><?php echo language::translate('title_price_operator', 'Price Operator'); ?></th>
                <th><?php echo language::translate('title_price_adjustment', 'Price Adjustment'); ?></th>
<?php
  foreach (array_keys(currency::$currencies) as $currency_code) {
    if ($currency_code == settings::get('store_currency_code')) continue;
?>
              <th class="text-center"></th>
<?php
  }
?>
                <th>&nbsp;</th>
              </tr>
            </thead>
            <tbody>
<?php
  if (!empty($_POST['options'])) {
    foreach (array_keys($_POST['options']) as $key) {
?>
            <tr>
              <td><?php echo functions::form_draw_option_groups_list('options['.$key.'][group_id]', true); ?></td>
              <td><?php echo functions::form_draw_option_values_list($_POST['options'][$key]['group_id'], 'options['.$key.'][value_id]', true); ?></td>
              <td style="text-align: center;"><?php echo functions::form_draw_select_field('options['.$key.'][price_operator]', array('+','%','*'), $_POST['options'][$key]['price_operator'], false); ?></td>
              <td><?php echo functions::form_draw_currency_field(settings::get('store_currency_code'), 'options['.$key.']['.settings::get('store_currency_code').']', true); ?></td>
<?php
      foreach (array_keys(currency::$currencies) as $currency_code) {
        if ($currency_code == settings::get('store_currency_code')) continue;
?>
              <td><?php echo str_replace(PHP_EOL, '', functions::form_draw_currency_field($currency_code, 'options['.$key.']['. $currency_code. ']', number_format((float)$_POST['options'][$key][$currency_code], 4, '.', ''))); ?></td>
<?php
      }
?>
              <td class="text-right"><a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"'); ?></a> <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"'); ?></a> <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
            </tr>
<?php
    }
  }
?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="<?php echo 5 + count(currency::$currencies); ?>"><a class="add" href="#" title="<?php echo language::translate('text_insert_before', 'Insert before'); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?></a></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div id="tab-stock-options" class="tab-pane">
        <h2><?php echo language::translate('title_stock_options', 'Stock Options'); ?></h2>
        <div class="table-responsive">
          <table id="table-stock-options" class="table table-striped data-table">
            <thead>
              <tr>
                <th style="min-width: 400px;"><?php echo language::translate('title_option', 'Option'); ?></th>
                <th class=" text-center"><?php echo language::translate('title_sku', 'SKU'); ?></th>
                <th class="text-center"><?php echo language::translate('title_qty', 'Qty'); ?></th>
                <th class="text-center"><?php echo language::translate('title_weight', 'Weight'); ?></th>
                <th><?php echo language::translate('title_dimensions', 'Dimensions'); ?></th>
                <th>&nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($_POST['options_stock'])) foreach (array_keys($_POST['options_stock']) as $key) { ?>
              <tr>
                <td><?php echo functions::form_draw_hidden_field('options_stock['.$key.'][id]', true); ?><?php echo functions::form_draw_hidden_field('options_stock['.$key.'][combination]', true); ?>
                  <?php echo functions::form_draw_hidden_field('options_stock['.$key.'][name]['. language::$selected['name'] .']', true); ?>
                  <?php echo $_POST['options_stock'][$key]['name'][language::$selected['code']]; ?></td>
                <td><?php echo functions::form_draw_text_field('options_stock['.$key.'][sku]', true); ?></td>
                <td><?php echo functions::form_draw_number_field('options_stock['.$key.'][quantity]', true); ?></td>
                <td>
                  <div class="input-group">
                    <?php echo functions::form_draw_decimal_field('options_stock['.$key.'][weight]', true, 1, 0); ?>
                    <?php echo functions::form_draw_weight_classes_list('options_stock['.$key.'][weight_class]', true, false, 'style="width: auto;"'); ?>
                  </div>
                </td>
                <td>
                  <div class="input-group">
                    <?php echo functions::form_draw_decimal_field('options_stock['.$key.'][dim_x]', true, 1, 0); ?>
                    <?php echo functions::form_draw_decimal_field('options_stock['.$key.'][dim_y]', true, 1, 0); ?>
                    <?php echo functions::form_draw_decimal_field('options_stock['.$key.'][dim_z]', true, 1, 0); ?>
                    <?php echo functions::form_draw_length_classes_list('options_stock['.$key.'][dim_class]', true, false, 'style="width: auto;"'); ?>
                  </div>
                </td>
                <td class="text-right">
                  <a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"'); ?></a>
                  <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"'); ?></a>
                  <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a>
                </td>
              </tr>
            <?php } ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="6"><a href="#" data-toggle="lightbox" data-target="#new-stock-option"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?></a></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div id="new-stock-option" class="lightbox" style="display: none; max-width: 640px;">
          <h3 class="title"><?php echo language::translate('title_new_stock_option', 'New Stock Option'); ?></h3>

          <table class="table table-striped" style="width: 100%;">
            <thead>
              <tr>
                <th style="width: 50%;"><?php echo language::translate('title_group', 'Group'); ?></th>
                <th style="width: 50%;"><?php echo language::translate('title_value', 'Value'); ?></th>
                <th>&nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?php echo functions::form_draw_option_groups_list('new_option[new_1][group_id]', ''); ?></td>
                <td><?php echo functions::form_draw_select_field('new_option[new_1][value_id]', array(array('','')), '', false, false, 'disabled="disabled"'); ?></td>
                <td><a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td><a class="add" href="#" title="<?php echo language::translate('text_add', 'Add'); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?></a></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
            </tfoot>
          </table>

          <button type="button" class="btn btn-default" name="add_stock_option"><?php echo language::translate('title_add_stock_option', 'Add Stock Option'); ?></button>
        </div>
      </div>
    </div>
  </div>

  <p class="btn-group">
    <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
    <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
    <?php echo (isset($product->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>

<script>

// Initiate

  $('input[name^="name"]').bind('input propertyChange', function(e){
    var language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
    console.log($(this).val());
    $('input[name="head_title['+language_code+']"]').attr('placeholder', $(this).val());
    $('input[name="h1_title['+language_code+']"]').attr('placeholder', $(this).val());
  }).trigger('input');

  $('input[name^="short_description"]').bind('input propertyChange', function(e){
    var language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
    $('input[name="meta_description['+language_code+']"]').attr('placeholder', $(this).val());
  }).trigger('input');

// Default Category

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

// Quantity Unit

  $('select[name="quantity_unit_id"]').change(function(){
    var value = parseFloat($('input[name="quantity"]').val());
    $('input[name="quantity"]').val(value.toFixed($(this).data('decimals')));
  }).trigger('change');

// Images

  $('#images').on('click', '.move-up, .move-down', function(e) {
    e.preventDefault();
    var row = $(this).closest('.form-group');

    if ($(this).is('.move-up') && $(row).prevAll().length > 0) {
      $(row).insertBefore(row.prev());
    } else if ($(this).is('.move-down') && $(row).nextAll().length > 0) {
      $(row).insertAfter($(row).next());
    }
    refreshMainImage();
  });

  $('#images').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('.form-group').remove();
    refreshMainImage();
  });

  $('#images .add').click(function(e) {
    e.preventDefault();
    var output = '<div class="image form-group">'
               + '  <div class="thumbnail pull-left">'
               + '    <img src="<?php echo functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'no_image.png', $product_image_width, $product_image_height, settings::get('product_image_clipping')); ?>" alt="" />'
               + '  </div>'
               + '  '
               + '  <div class="input-group">'
               + '    <?php echo functions::form_draw_file_field('new_images[]'); ?>'
               + '    <div class="input-group-addon">'
               + '      <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a>'
               + '    </div>'
               + '  </div>'
               + '</div>';
    $('#images .new-images').append(output);
    refreshMainImage();
  });

  $('#images').on('change', 'input[type="file"]', function(e) {
    var img = $(this).closest('.form-group').find('img');

    var oFReader = new FileReader();
    oFReader.readAsDataURL(this.files[0]);
    oFReader.onload = function(e){
      $(img).attr('src', e.target.result);
    };
    oFReader.onloadend = function(e) {
      refreshMainImage();
    };
  });

  function refreshMainImage() {
    console.log($('#images img:first').attr('src'));
    if ($('#images img:first').length) {
      $('#tab-general .main-image').attr('src', $('#images img:first').attr('src'));
      return;
    }

    $('#tab-general .main-image').attr('src', '<?php echo functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'no_image.png', $product_image_width, $product_image_height, settings::get('product_image_clipping')); ?>');
  }

// Attributes

  $('a.attributes-hint').click(function(e){
    e.preventDefault();
    alert('Syntax:\n\nTitle1\nProperty1: Value1\nProperty2: Value2\n\nTitle2\nProperty3: Value3...');
  });

// Prices

  function get_tax_rate() {
    switch ($('select[name=tax_class_id]').val()) {
<?php
  $tax_classes_query = database::query(
    "select * from ". DB_TABLE_TAX_CLASSES ."
    order by name asc;"
  );
  while ($tax_class = database::fetch($tax_classes_query)) {
    echo '      case "'. $tax_class['id'] . '":'. PHP_EOL
       . '        return '. tax::get_tax(100, $tax_class['id'], 'store') .';' . PHP_EOL;
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
    echo '      case \''. $currency['code'] .'\':' . PHP_EOL
       . '        return '. $currency['value'] .';' . PHP_EOL;
  }
?>
    }
  }

  function get_currency_decimals(currency_code) {
    switch (currency_code) {
<?php
  foreach (currency::$currencies as $currency) {
    echo '      case \''. $currency['code'] .'\':' . PHP_EOL
       . '        return '. ($currency['decimals']+2) .';' . PHP_EOL;
  }
?>
    }
  }

  $('select[name="tax_class_id"], input[name^="prices"]').bind('input propertyChange', function() {

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

    if (currency_code != '<?php echo settings::get('store_currency_code'); ?>') return;

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
      var currency_code = $(this).attr('name').replace(/^prices\[(.*)\]$/, "$1");

      if (currency_code != '<?php echo settings::get('store_currency_code'); ?>') {

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

// Initiate Prices
  $('input[name^="prices"]').trigger('propertyChange');
  $('input[name^="gross_prices"]').trigger('propertyChange');

  $('#price-incl-tax-tooltip').click(function(e) {
    e.preventDefault;
    alert('<?php echo str_replace(array("\r", "\n", "'"), array("", "", "\\'"), language::translate('tooltip_field_price_incl_tax', 'This field helps you calculate net price based on the store region tax. All prices input to database are always excluding tax.')); ?>');
  });

// Campaigns

  $('#table-campaigns').on('keyup change input', 'input[name^="campaigns"][name$="[percentage]"]', function() {
    var parent = $(this).closest('tr');

    <?php foreach (currency::$currencies as $currency) { ?>
    if ($('input[name^="prices"][name$="[<?php echo $currency['code']; ?>]"]').val() > 0) {
      var value = $('input[name="prices[<?php echo $currency['code']; ?>]"]').val() * ((100 - $(this).val()) / 100);
      value = Number(value).toFixed(<?php echo $currency['decimals']; ?>);
      $(parent).find('input[name$="[<?php echo $currency['code']; ?>]"]').val(value);
    } else {
      $(parent).find('input[name$="[<?php echo $currency['code']; ?>]"]').val("");
    }
    <?php } ?>

    <?php foreach (currency::$currencies as $currency) { ?>
    var value = $(parent).find('input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val() * <?php echo $currency['value']; ?>;
    value = Number(value).toFixed(<?php echo $currency['decimals']; ?>);
    $(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').attr('placeholder', value);
    <?php } ?>
  });

  $('#table-campaigns').on('keyup change input', 'input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]', function() {
    var parent = $(this).closest('tr');
    var percentage = ($('input[name="prices[<?php echo settings::get('store_currency_code'); ?>]"]').val() - $(this).val()) / $('input[name="prices[<?php echo settings::get('store_currency_code'); ?>]"]').val() * 100;
    percentage = Number(percentage).toFixed(2);
    $(parent).find('input[name$="[percentage]"]').val(percentage);

    <?php foreach (currency::$currencies as $currency) { ?>
    var value = 0;
    value = $(parent).find('input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val() * <?php echo $currency['value']; ?>;
    value = Number(value).toFixed(<?php echo $currency['decimals']; ?>);
    $(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').attr("placeholder", value);
    if ($(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').val() == 0) {
      $(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').val('');
    }
    <?php } ?>
  });
  $('input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').trigger('keyup');

  $('#table-campaigns').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
  });

  var new_campaign_i = 1;
  $('#table-campaigns').on('click', '.add', function(e) {
    e.preventDefault();
    var output = '<tr>'
               + '  <td><?php echo functions::general_escape_js(language::translate('title_start_date', 'Start Date')); ?><br />'
               + '    <?php echo functions::general_escape_js(functions::form_draw_hidden_field('campaigns[new_campaign_i][id]', '') . functions::form_draw_datetime_field('campaigns[new_campaign_i][start_date]', '')); ?>'
               + '  </td>'
               + '  <td><?php echo functions::general_escape_js(language::translate('title_end_date', 'End Date')); ?><br />'
               + '    <?php echo functions::general_escape_js(functions::form_draw_datetime_field('campaigns[new_campaign_i][end_date]', '')); ?>'
               + '  </td>'
               + '  <td>- %<br />'
               + '    <?php echo functions::general_escape_js(functions::form_draw_decimal_field('campaigns[new_campaign_i][percentage]', '', 2, 0, null)); ?>'
               + '  </td>'
               + '  <td><?php echo functions::general_escape_js(settings::get('store_currency_code')); ?><br />'
               + '    <?php echo functions::general_escape_js(functions::form_draw_currency_field(settings::get('store_currency_code'), 'campaigns[new_campaign_i]['. settings::get('store_currency_code') .']', '')); ?>'
               + '  </td>'
<?php
  foreach (array_keys(currency::$currencies) as $currency_code) {
    if ($currency_code == settings::get('store_currency_code')) continue;
?>
               + '  <td><?php echo functions::general_escape_js($currency_code); ?><br />'
               + '    <?php echo functions::general_escape_js(functions::form_draw_currency_field($currency_code, 'campaigns[new_campaign_i]['. $currency_code .']', '')); ?>'
               + '  </td>'
<?php
  }
?>
               + '  <td><br /><a class="remove" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
               + '</tr>';
   while ($('input[name="campaigns[new_'+new_campaign_i+']"]').length) new_campaign_i++;
    output = output.replace(/new_campaign_i/g, 'new_' + new_campaign_i);
    $('#table-campaigns tbody').append(output);
    new_campaign_i++;
  });

// Options

  $('#table-options').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
  });

  $('#table-options').on('click', '.move-up, .move-down', function(e) {
    e.preventDefault();
    var row = $(this).closest('tr');
    if ($(this).is('.move-up') && $(row).prevAll().length > 1) {
      $(row).insertBefore($(row).prev());
    } else if ($(this).is('.move-down') && $(row).nextAll().length > 0) {
      $(row).insertAfter($(row).next());
    }
  });

  $('#table-options').on('change', 'select[name^="options"][name$="[group_id]"]', function(){
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
  $('#table-options').on('click', '.add', function(e) {
    e.preventDefault();
    var output = '<tr>'
               + '  <td><?php echo functions::general_escape_js(functions::form_draw_option_groups_list('options[new_option_i][group_id]', '')); ?></td>'
               + '  <td><?php echo functions::general_escape_js(functions::form_draw_select_field('options[new_option_i][value_id]', array(array('','')), '')); ?></td>'
               + '  <td class="text-center"><?php echo functions::general_escape_js(functions::form_draw_select_field('options[new_option_i][price_operator]', array('+','*'), '+', false)); ?></td>'
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
    $('#table-options tbody').append(output);
    new_option_i++;
  });

// Stock options

  $('#table-stock-options').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
  });

  $('#table-stock-options').on('click', '.move-up, .move-down', function(e) {
    e.preventDefault();
    var row = $(this).closest('tr');

    if ($(this).is('.move-up') && $(row).prevAll().length > 1) {
      $(row).insertBefore($(row).prev());
    } else if ($(this).is('.move-down') && $(row).nextAll().length > 0) {
      $(row).insertAfter($(row).next());
    }
  });

  var option_index = 2;
  $('body').on('click', '#new-stock-option .add', function(e) {
    e.preventDefault();
    var output = '<tr>'
               + '  <td><?php echo functions::general_escape_js(functions::form_draw_option_groups_list('new_option[option_index][group_id]', '')); ?></td>'
               + '  <td><?php echo functions::general_escape_js(functions::form_draw_select_field('new_option[option_index][value_id]', array(array('','')), '', false, false, 'disabled="disabled"')); ?></td>'
               + '  <td><a class="remove" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
               + '</tr>';
    output = output.replace(/option_index/g, 'new_' + option_index);
    $(this).closest('table').find('tbody').append(output);
    option_index++;
  });

  $('body').on('click', '#new-stock-option .remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
  });

  $('#new-stock-option').on('change', 'select[name^="new_option"][name$="[group_id]"]', function(){
    var valueField = this.name.replace(/group/, 'value');
    var box = $(this).closest('#new-stock-option');
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
        $(box).find('select[name="'+ valueField +'"]').html('');
        if ($(box).find('select[name="'+ valueField +'"]').attr('disabled')) $(box).find('select[name="'+ valueField +'"]').removeAttr('disabled');
        if (data) {
          $.each(data, function(i, zone) {
            $(box).find('select[name="'+ valueField +'"]').append('<option value="'+ zone.id +'">'+ zone.name +'</option>');
          });
        } else {
          $(box).find('select[name="'+ valueField +'"]').attr('disabled', 'disabled');
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
  });

  var new_option_stock_i = 1;
  $('body').on('click', '#new-stock-option button[name="add_stock_option"]', function(e) {
    e.preventDefault();
    var box = $(this).closest('#new-stock-option');
    var new_option_code = '';
    var new_option_name = '';
    var use_coma = false;
    var success = $(box).find('select[name^="new_option"][name$="[group_id]"]').each(function(i, groupElement) {
      var groupElement = $(box).find(groupElement);
      var valueElement = $(box).find('select[name="'+ $(groupElement).attr('name').replace(/group_id/g, 'value_id') +'"]');
      if (valueElement.val() == '') {
        alert("<?php echo language::translate('error_empty_option_group', 'Error: Empty option group'); ?>");
        return false;
      }
      if (groupElement.val() == '') {
        alert("<?php echo language::translate('error_empty_option_value', 'Error: Empty option value'); ?>");
        return false;
      }
      if (use_coma) {
        new_option_code += ',';
        new_option_name += ', ';
      }
      new_option_code += $(groupElement).val() + '-' + $(valueElement).val();
      new_option_name += $(valueElement).find('option:selected').text();
      use_coma = true;
    });
    if (new_option_code == '') return;
    var output = '<tr>'
               + '  <td><?php echo functions::general_escape_js(functions::form_draw_hidden_field('options_stock[new_option_stock_i][id]', '') . functions::form_draw_hidden_field('options_stock[new_option_stock_i][combination]', 'new_option_code') . functions::form_draw_hidden_field('options_stock[new_option_stock_i][name]['. language::$selected['code'] .']', 'new_option_name')); ?>new_option_name</td>'
               + '  <td><?php echo functions::general_escape_js(functions::form_draw_text_field('options_stock[new_option_stock_i][sku]', '')); ?></td>'
               + '  <td><?php echo functions::general_escape_js(functions::form_draw_number_field('options_stock[new_option_stock_i][quantity]', '0')); ?></td>'
               + '  <td>'
               + '    <div class="input-group">'
               + '    <?php echo functions::general_escape_js(functions::form_draw_decimal_field('options_stock[new_option_stock_i][weight]', '0.00', 1, 0)); ?>'
               + '    <?php echo functions::general_escape_js(functions::form_draw_weight_classes_list('options_stock[new_option_stock_i][weight_class]', '', false, 'style="width: auto;"')); ?>'
               + '    </div>'
               + '  </td>'
               + '  <td>'
               + '    <div class="input-group">'
               + '      <?php echo functions::general_escape_js(functions::form_draw_decimal_field('options_stock[new_option_stock_i][dim_x]', '0.00', 1, 0)); ?>'
               + '      <?php echo functions::general_escape_js(functions::form_draw_decimal_field('options_stock[new_option_stock_i][dim_y]', '0.00', 1, 0)); ?>'
               + '      <?php echo functions::general_escape_js(functions::form_draw_decimal_field('options_stock[new_option_stock_i][dim_z]', '0.00', 1, 0)); ?>'
               + '      <?php echo functions::general_escape_js(functions::form_draw_length_classes_list('options_stock[new_option_stock_i][dim_class]', '', false, 'style="width: auto;"')); ?>'
               + '  </td>'
               + '  <td class="text-right">'
               + '    <a class="move-up" href="#" title="<?php echo functions::general_escape_js(language::translate('text_move_up', 'Move up'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"')); ?></a>'
               + '    <a class="move-down" href="#" title="<?php echo functions::general_escape_js(language::translate('text_move_down', 'Move down'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"')); ?></a>'
               + '    <a class="remove" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a>'
               + '  </td>'
               + '</tr>';
    while ($('input[name="options_stock[new_'+new_option_stock_i+']"]').length) new_option_stock_i++;
    output = output.replace(/new_option_stock_i/g, 'new_' + new_option_stock_i);
    output = output.replace(/new_option_code/g, new_option_code);
    output = output.replace(/new_option_name/g, new_option_name);
    $('#table-stock-options').find('tbody').append(output);
    new_option_stock_i++;
    $.featherlight.close();
  });
</script>