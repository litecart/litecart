<?php

  if (!empty($_GET['product_id'])) {
    $product = new ent_product($_GET['product_id']);
  } else {
    $product = new ent_product();
  }

  if (empty($_POST)) {
    $_POST = $product->data;

    $_POST['keywords'] = implode(',', $_POST['keywords']);

    if (empty($product->data['id']) && isset($_GET['category_id'])) {
      $_POST['categories'][] = $_GET['category_id'];
    }
  }

  document::$snippets['title'][] = !empty($product->data['id']) ? language::translate('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : language::translate('title_add_new_product', 'Add New Product');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'), document::link(WS_DIR_ADMIN, ['doc' => 'catalog'], ['app']));
  breadcrumbs::add(!empty($product->data['id']) ? language::translate('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : language::translate('title_add_new_product', 'Add New Product'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['categories'])) $_POST['categories'] = [0];
      if (empty($_POST['images'])) $_POST['images'] = [];
      if (empty($_POST['attributes'])) $_POST['attributes'] = [];
      if (empty($_POST['campaigns'])) $_POST['campaigns'] = [];
      if (empty($_POST['options'])) $_POST['options'] = [];
      if (empty($_POST['options_stock'])) $_POST['options_stock'] = [];

      if (!empty($_POST['code']) && database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."products where id != '". (int)$product->data['id'] ."' and code = '". database::input($_POST['code']) ."' limit 1;"))) throw new Exception(language::translate('error_code_database_conflict', 'Another entry with the given code already exists in the database'));
      if (!empty($_POST['sku'])  && database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."products where id != '". (int)$product->data['id'] ."' and sku = '". database::input($_POST['sku']) ."' limit 1;")))   throw new Exception(language::translate('error_sku_database_conflict', 'Another entry with the given SKU already exists in the database'));
      if (!empty($_POST['mpn'])  && database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."products where id != '". (int)$product->data['id'] ."' and mpn = '". database::input($_POST['mpn']) ."' limit 1;")))   throw new Exception(language::translate('error_mpn_database_conflict', 'Another entry with the given MPN already exists in the database'));
      if (!empty($_POST['gtin']) && database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."products where id != '". (int)$product->data['id'] ."' and gtin = '". database::input($_POST['gtin']) ."' limit 1;"))) throw new Exception(language::translate('error_gtin_database_conflict', 'Another entry with the given GTIN already exists in the database'));

      $_POST['keywords'] = preg_split('#\s*,\s*#', $_POST['keywords'], -1, PREG_SPLIT_NO_EMPTY);

      $fields = [
        'status',
        'manufacturer_id',
        'supplier_id',
        'delivery_status_id',
        'sold_out_status_id',
        'default_category_id',
        'categories',
        'attributes',
        'keywords',
        'date_valid_from',
        'date_valid_to',
        'quantity',
        'quantity_adjustment',
        'quantity_min',
        'quantity_max',
        'quantity_step',
        'quantity_unit_id',
        'purchase_price',
        'purchase_price_currency_code',
        'recommended_price',
        'prices',
        'campaigns',
        'tax_class_id',
        'code',
        'sku',
        'mpn',
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
        'technical_data',
        'head_title',
        'meta_description',
        'images',
        'options',
        'options_stock',
      ];

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
      header('Location: '. document::link(WS_DIR_ADMIN, ['app' => $_GET['app'], 'doc' => 'catalog', 'category_id' => $_POST['categories'][0]]));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($product->data['id'])) throw new Exception(language::translate('error_must_provide_product', 'You must provide a product'));

      $product->delete();

      if (empty($_POST['categories'])) $_POST['categories'] = [0];
      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['app' => $_GET['app'], 'doc' => 'catalog', 'category_id' => $_POST['categories'][0]]));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  list($product_image_width, $product_image_height) = functions::image_scale_by_width(320, settings::get('product_image_ratio'));

  $option_sort_options = [
    [language::translate('title_list_order', 'List Order'), 'priority'],
    [language::translate('title_alphabetical', 'Alphabetical'), 'alphabetical'],
  ];

  functions::draw_lightbox();
?>
<style>
#categories {
  max-height: 310px;
  overflow-y: auto;
  overflow-x: hidden;
  transition: all 200ms linear;
}
#categories:hover {
  width: 150%;
  z-index: 999;
}
#categories label {
  white-space: nowrap;
}

#images .thumbnail {
  margin: 0;
}
#images .image {
  overflow: hidden;
}
#images .thumbnail {
  margin-inline-end: 15px;
}
#images img {
  max-width: 50px;
  max-height: 50px;
}
#images .actions {
  text-align: end;
  padding: 0.25em 0;
}
</style>

<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo !empty($product->data['id']) ? language::translate('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : language::translate('title_add_new_product', 'Add New Product'); ?>
  </div>

  <ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#tab-general"><?php echo language::translate('title_general', 'General'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-information"><?php echo language::translate('title_information', 'Information'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-attributes"><?php echo language::translate('title_attributes', 'Attributes'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-prices"><?php echo language::translate('title_prices', 'Prices'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-options"><?php echo language::translate('title_options', 'Options'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-stock"><?php echo language::translate('title_stock', 'Stock'); ?></a></li>
  </ul>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('product_form', 'post', false, true); ?>

      <div class="tab-content">
        <div id="tab-general" class="tab-pane active" style="max-width: 1200px;">

          <div class="row">
            <div class="col-md-4">

              <div class="form-group">
                <label><?php echo language::translate('title_status', 'Status'); ?></label>
                <?php echo functions::form_draw_toggle('status', isset($_POST['status']) ? $_POST['status'] : '0', 'e/d'); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_categories', 'Categories'); ?></label>
                <?php echo functions::form_draw_categories_list('categories[]', true, 'style="max-height: 480px;"'); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_default_category', 'Default Category'); ?></label>
                <?php echo functions::form_draw_select_field('default_category_id', [], true); ?>
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
                  <div><?php echo language::strftime('%e %b %Y %H:%M', strtotime($product->data['date_updated'])); ?></div>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_date_created', 'Date Created'); ?></label>
                  <div><?php echo language::strftime('%e %b %Y %H:%M', strtotime($product->data['date_created'])); ?></div>
                </div>
              </div>
              <?php } ?>
            </div>

            <div class="col-md-4">

              <div class="form-group">
                <label><?php echo language::translate('title_name', 'Name'); ?></label>
                 <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_code', 'Code'); ?></label>
                <?php echo functions::form_draw_text_field('code', true); ?>
              </div>

              <div class="form-group">
                <div class="input-group">
                  <label class="input-group-text" style="width: 125px;"><?php echo language::translate('title_sku', 'SKU'); ?> <a href="https://en.wikipedia.org/wiki/Stock_keeping_unit" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
                  <?php echo functions::form_draw_text_field('sku', true); ?>
                </div>

                <div class="input-group">
                  <label class="input-group-text" style="width: 125px;"><?php echo language::translate('title_mpn', 'MPN'); ?> <a href="https://en.wikipedia.org/wiki/Manufacturer_part_number" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
                  <?php echo functions::form_draw_text_field('mpn', true); ?>
                </div>

                <div class="input-group">
                  <label class="input-group-text" style="width: 125px;"><?php echo language::translate('title_gtin', 'GTIN'); ?> <a href="https://en.wikipedia.org/wiki/Global_Trade_Item_Number" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
                  <?php echo functions::form_draw_text_field('gtin', true); ?>
                </div>

                <div class="input-group">
                  <label class="input-group-text" style="width: 125px;"><?php echo language::translate('title_taric', 'TARIC'); ?> <a href="https://en.wikipedia.org/wiki/TARIC_code" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
                  <?php echo functions::form_draw_text_field('taric', true); ?>
                </div>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_manufacturer', 'Manufacturer'); ?></label>
                <?php echo functions::form_draw_manufacturers_list('manufacturer_id', true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_supplier', 'Supplier'); ?></label>
                <?php echo functions::form_draw_suppliers_list('supplier_id', true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_keywords', 'Keywords'); ?></label>
                <?php echo functions::form_draw_text_field('keywords', true); ?>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label><?php echo language::translate('title_images', 'Images'); ?></label>
                <div class="thumbnail">
<?php
  if (isset($product->data['id']) && !empty($product->data['images'])) {
    $image = current($product->data['images']);
    echo '<img class="main-image" src="'. document::href_link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_APP . 'images/' . $image['filename'], $product_image_width, $product_image_height, settings::get('product_image_clipping'))) .'" alt="" />';
    reset($product->data['images']);
  } else {
    echo '<img class="main-image" src="'. document::href_link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_APP . 'images/no_image.png', $product_image_width, $product_image_height, settings::get('product_image_clipping'))) .'" alt="" />';
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

                    <div class="thumbnail float-start">
                      <img src="<?php echo document::href_link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_APP . 'images/' . $product->data['images'][$key]['filename'], $product_image_width, $product_image_height, settings::get('product_image_clipping'))); ?>" alt="" />
                    </div>

                    <div class="input-group">
                      <?php echo functions::form_draw_text_field('images['.$key.'][new_filename]', isset($_POST['images'][$key]['new_filename']) ? $_POST['images'][$key]['new_filename'] : $_POST['images'][$key]['filename']); ?>
                      <div class="input-group-text">
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
                    <div class="thumbnail float-start">
                      <img src="<?php echo document::href_link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_APP . 'images/no_image.png', $product_image_width, $product_image_height, settings::get('product_image_clipping'))); ?>" alt="" />
                    </div>

                    <div class="input-group">
                      <?php echo functions::form_draw_file_field('new_images[]'); ?>
                      <div class="input-group-text">
                        <a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"'); ?></a>
                        <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"'); ?></a>
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

        <div id="tab-information" class="tab-pane">

          <ul class="nav nav-tabs">
            <?php foreach (language::$languages as $language) { ?>
              <li<?php echo ($language['code'] == language::$selected['code']) ? ' class="active"' : ''; ?>><a data-toggle="tab" href="#<?php echo $language['code']; ?>"><?php echo $language['name']; ?></a></li>
            <?php } ?>
          </ul>

          <div class="tab-content">

            <?php foreach (array_keys(language::$languages) as $language_code) { ?>
            <div id="<?php echo $language_code; ?>" class="tab-pane fade in<?php echo ($language_code == language::$selected['code']) ? ' active' : ''; ?>">

              <div class="row">
                <div class="col-md-6">

                  <div class="form-group">
                    <label><?php echo language::translate('title_name', 'Name'); ?></label>
                    <?php echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true); ?>
                  </div>

                  <div class="form-group">
                    <label><?php echo language::translate('title_short_description', 'Short Description'); ?></label>
                    <?php echo functions::form_draw_regional_input_field($language_code, 'short_description['. $language_code .']', true); ?>
                  </div>

                  <div class="form-group">
                    <label><?php echo language::translate('title_description', 'Description'); ?></label>
                    <?php echo functions::form_draw_regional_wysiwyg_field($language_code, 'description['. $language_code .']', true, 'style="height: 250px;"'); ?>
                  </div>

                  <div class="row">
                    <div class="form-group col-md-6">
                      <label><?php echo language::translate('title_head_title', 'Head Title'); ?></label>
                      <?php echo functions::form_draw_regional_input_field($language_code, 'head_title['. $language_code .']', true); ?>
                    </div>

                    <div class="form-group col-md-6">
                      <label><?php echo language::translate('title_meta_description', 'Meta Description'); ?></label>
                      <?php echo functions::form_draw_regional_input_field($language_code, 'meta_description['. $language_code .']', true); ?>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label><?php echo language::translate('title_technical_data', 'Technical Data'); ?> <a class="technical-data-hint" href="#"><?php echo functions::draw_fonticon('fa-question-circle'); ?></a></label>
                    <?php echo functions::form_draw_regional_textarea($language_code, 'technical_data['. $language_code .']', true, 'style="height: 480px;"'); ?>
                  </div>
                </div>

              </div>
            </div>
            <?php } ?>

          </div>
        </div>

        <div id="tab-attributes" class="tab-pane" style="max-width: 960px;">

          <table class="table table-striped data-table">
            <thead>
              <tr>
                <th style="width: 320px;"><?php echo language::translate('title_group', 'Group'); ?></th>
                <th style="width: 320px;"><?php echo language::translate('title_value', 'Value'); ?></th>
                <th><?php echo language::translate('title_custom_value', 'Custom Value'); ?></th>
                <th style="width: 60px;"></th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($_POST['attributes'])) foreach (array_keys($_POST['attributes']) as $key) { ?>
              <tr>
                <?php echo functions::form_draw_hidden_field('attributes['.$key.'][id]', true); ?>
                <?php echo functions::form_draw_hidden_field('attributes['.$key.'][group_id]', true); ?>
                <?php echo functions::form_draw_hidden_field('attributes['.$key.'][group_name]', true); ?>
                <?php echo functions::form_draw_hidden_field('attributes['.$key.'][value_id]', true); ?>
                <?php echo functions::form_draw_hidden_field('attributes['.$key.'][value_name]', true); ?>
                <?php echo functions::form_draw_hidden_field('attributes['.$key.'][custom_value]', true); ?>
                <td><?php echo $_POST['attributes'][$key]['group_name']; ?></td>
                <td><?php echo $_POST['attributes'][$key]['value_name']; ?></td>
                <td><?php echo $_POST['attributes'][$key]['custom_value']; ?></td>
                <td class="text-end"><a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
              </tr>
              <?php } ?>
            </tbody>
            <tfoot>
              <tr>
                <td><?php echo functions::form_draw_attribute_groups_list('new_attribute[group_id]', [], ''); ?></td>
                <td><?php echo functions::form_draw_select_field('new_attribute[value_id]', [], ''); ?></td>
                <td><?php echo functions::form_draw_text_field('new_attribute[custom_value]', ''); ?></td>
                <td><?php echo functions::form_draw_button('add', language::translate('title_add', 'Add'), 'button'); ?></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div id="tab-prices" class="tab-pane">

          <div id="prices" style="max-width: 640px;">
            <h2><?php echo language::translate('title_prices', 'Prices'); ?></h2>

            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_purchase_price', 'Purchase Price'); ?></label>
                <div class="input-group">
                  <?php echo functions::form_draw_decimal_field('purchase_price', true, 2, 0, null); ?>
                  <?php echo functions::form_draw_currencies_list('purchase_price_currency_code', true, false); ?>
                </div>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_recommended_price', 'Recommended Price'); ?> / MSRP</label>
                <?php echo functions::form_draw_currency_field(settings::get('store_currency_code'), 'recommended_price', true); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_tax_class', 'Tax Class'); ?></label>
                <?php echo functions::form_draw_tax_classes_list('tax_class_id', true); ?>
              </div>
            </div>

            <table class="table table-striped data-table">
              <thead>
                <tr>
                  <td style="width: 50%;"><?php echo language::translate('title_price', 'Price'); ?></td>
                  <td style="width: 50%;"><?php echo language::translate('title_price_incl_tax', 'Price Incl. Tax'); ?> (<a id="price-incl-tax-tooltip" href="#">?</a>)</td>
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
              <?php } ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="<?php echo 5 + count(currency::$currencies) - 1; ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?> <a class="add" href="#"><?php echo language::translate('text_add_campaign', 'Add Campaign'); ?></a></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
<style>
#tab-options li {
  background: #f9f9f9;
  padding: 1em;
  border-radius: 4px;
  margin-bottom: 2em;
  border: 1px solid #ececec;
}
</style>
        <div id="tab-options" class="tab-pane">

          <ul id="options" class="list-unstyled">
            <?php foreach ($_POST['options'] as $group_id => $option) { ?>
            <li data-group-id="<?php echo functions::escape_html($group_id); ?>" data-group-name="<?php echo functions::escape_html($option['name']); ?>">

              <div class="float-end">
                <a class="move-group-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-2x', 'style="color: #3399cc;"'); ?></a>
                <a class="move-group-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-2x', 'style="color: #3399cc;"'); ?></a>
                <a class="remove-group" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-2x', 'style="color: #cc3333;"'); ?></a>
              </div>

              <h2><?php echo $option['name']; ?></h2>
              <?php echo functions::form_draw_hidden_field('options['.$group_id.'][id]', true) . functions::form_draw_hidden_field('options['.$group_id.'][group_id]', true) . functions::form_draw_hidden_field('options['.$group_id.'][name]', true); ?>

              <div class="row">
                <div class="form-group col-sm-4 col-md-2">
                  <label><?php echo language::translate('title_function', 'Function'); ?></label>
                  <?php echo functions::form_draw_select_field('options['.$group_id.'][function]', in_array($option['function'], ['select', 'radio', 'checkbox']) ? ['select', 'radio', 'checkbox'] : ['text', 'textarea'], true); ?>
                </div>

                <?php if (in_array($option['function'], ['select', 'radio', 'checkbox'])) { ?>
                <div class="form-group col-sm-4 col-md-2">
                  <label><?php echo language::translate('title_sort_values', 'Sort Values'); ?></label>
                  <?php echo functions::form_draw_select_field('options['.$group_id.'][sort]', $option_sort_options, true); ?>
                </div>
                <?php } ?>

                <div class="form-group col-sm-4 col-md-2">
                  <label><?php echo language::translate('title_required', 'Required'); ?></label>
                  <div class="checkbox">
                    <label><?php echo functions::form_draw_checkbox('options['.$group_id.'][required]', '1', true); ?> <?php echo language::translate('title_required', 'Required'); ?></label>
                  </div>
                </div>
              </div>

              <?php if (in_array($option['function'], ['select', 'radio', 'checkbox'])) { ?>
              <div class="table-responsive">
                <table class="table table-striped table-hover table-dragable data-table">
                  <thead>
                    <tr>
                      <th class="main"><?php echo language::translate('title_option', 'Option'); ?></th>
                      <th style="width: 150px;"><?php echo language::translate('title_price_operator', 'Price Operator'); ?></th>
                      <th colspan="<?php echo count(currency::$currencies); ?>"><?php echo language::translate('title_price_adjustment', 'Price Adjustment'); ?></th>
                      <th style="width: 85px;">&nbsp;</th>
                    </tr>
                  </thead>

                  <tbody>
                  <?php foreach ($option['values'] as $value_id => $value) { ?>
                    <tr data-value-id="<?php echo functions::escape_html($value['value_id']); ?>" data-value-name="<?php echo functions::escape_html($_POST['options'][$group_id]['values'][$value_id]['name']); ?>">
                      <td class="grabable"><?php echo functions::form_draw_hidden_field('options['.$group_id.'][values]['. $value_id .'][id]', true) . functions::form_draw_hidden_field('options['.$group_id.'][values]['. $value_id .'][value_id]', true) . functions::form_draw_hidden_field('options['.$group_id.'][values]['. $value_id .'][custom_value]', true) . functions::form_draw_hidden_field('options['.$group_id.'][values]['. $value_id .'][name]', true); ?><?php echo $value['name']; ?></td>
                      <td style="text-align: center;"><?php echo functions::form_draw_select_field('options['.$group_id.'][values]['. $value_id .'][price_operator]', ['+','%','*','='], true); ?></td>
                      <?php foreach (array_keys(currency::$currencies) as $currency_code) echo '<td>'. functions::form_draw_currency_field($currency_code, 'options['.$group_id.'][values]['. $value_id .']['. $currency_code. ']', (!empty($_POST['options'][$group_id]['values'][$value_id][$currency_code]) || $_POST['options'][$group_id]['values'][$value_id][$currency_code] != 0) ? true : '', 'style="width: 100px;"') .'</td>'; ?>
                      <td class="text-end"><a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"'); ?></a> <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"'); ?></a> <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
                    </tr>
                  <?php } ?>
                  </tbody>
                </table>
              </div>
              <?php } ?>

            </li>
            <?php } ?>
          </ul>

          <div>
            <a class="btn btn-default" href="#modal-predefined-option" data-toggle="lightbox"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?> <?php echo language::translate('title_add_predefined_option', 'Add Predefined Option'); ?></a>
            <a class="btn btn-default" href="#modal-user-input-option" data-toggle="lightbox"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?> <?php echo language::translate('title_add_user_input_option', 'Add User Input Option'); ?></a>
          </div>

          <div id="modal-predefined-option" style="display: none;">
            <fieldset style="max-width: 960px;">
              <legend><?php echo language::translate('title_add_predefined_option', 'Add Predefined Option'); ?></legend>
              <div class="row" style="margin-bottom: 0;">
                <div class="form-group col-md-3">
                  <label><?php echo language::translate('title_attribute_group', 'Attribute Group'); ?></label>
                  <?php echo functions::form_draw_attribute_groups_list('new_predefined_option[group_id]', ''); ?>
                </div>

                <div class="form-group col-md-3">
                  <label><?php echo language::translate('title_value', 'Value'); ?></label>
                  <?php echo functions::form_draw_select_field('new_predefined_option[value_id]', [['','']], '', 'disabled'); ?>
                </div>

                <div class="form-group col-md-3">
                  <label><?php echo language::translate('title_custom_value', 'Custom Value'); ?></label>
                  <?php echo functions::form_draw_text_field('new_predefined_option[custom_value]', ''); ?>
                </div>

                <div class="col-md-3" style="align-self: end;">
                  <?php echo functions::form_draw_button('add_predefined_option', language::translate('title_add', 'Add'), 'button', 'class="btn btn-default btn-block"'); ?>
                </div>
              </div>
            </fieldset>
          </div>

          <div id="modal-user-input-option" style="display: none;">
            <fieldset>
              <legend><?php echo language::translate('title_add_user_input_option', 'Add User Input Option'); ?></legend>
              <div class="row" style="margin-bottom: 0;">
                <div class="form-group col-md-8">
                  <label><?php echo language::translate('title_attribute_group', 'Attribute Group'); ?></label>
                  <?php echo functions::form_draw_attribute_groups_list('new_user_input_option[group_id]', ''); ?>
                </div>
                <div class="col-md-4" style="align-self: end;">
                  <?php echo functions::form_draw_button('add_user_input_option', language::translate('title_add', 'Add'), 'button', 'class="btn btn-default btn-block"'); ?>
                </div>
              </div>
            </fieldset>
          </div>
        </div>

        <div id="tab-stock" class="tab-pane">
          <h2><?php echo language::translate('title_stock', 'Stock'); ?></h2>

          <div class="row" style="max-width: 640px;">
            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_min_order_qty', 'Min. Order Qty'); ?></label>
              <?php echo functions::form_draw_decimal_field('quantity_min', true, 2, 0); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_max_order_quantity', 'Max. Order Qty'); ?></label>
              <?php echo functions::form_draw_decimal_field('quantity_max', true, 2, 0); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_quantity_step', 'Quantity Step'); ?></label>
              <?php echo functions::form_draw_decimal_field('quantity_step', true, 2, 0); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_quantity_unit', 'Quantity Unit'); ?></label>
              <?php echo functions::form_draw_quantity_units_list('quantity_unit_id', true, false); ?>
            </div>
          </div>

          <div class="row" style="max-width: 640px;">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_delivery_status', 'Delivery Status'); ?></label>
              <?php echo functions::form_draw_delivery_statuses_list('delivery_status_id', true); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_sold_out_status', 'Sold Out Status'); ?></label>
              <?php echo functions::form_draw_sold_out_statuses_list('sold_out_status_id', true); ?>
            </div>

          </div>

          <div class="table-responsive">
            <table id="table-stock" class="table table-striped table-hover data-table">
              <thead>
                <tr>
                  <th><?php echo language::translate('title_option', 'Option'); ?></th>
                  <th style="width: 250px;"><?php echo language::translate('title_sku', 'SKU'); ?></th>
                  <th style="width: 185px;"><?php echo language::translate('title_weight', 'Weight'); ?></th>
                  <th style="width: 400px;"><?php echo language::translate('title_dimensions', 'Dimensions'); ?></th>
                  <th class="text-center" style="width: 125px;"><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
                  <th class="text-center" style="width: 150px;"><?php echo language::translate('title_adjust', 'Adjust'); ?></th>
                  <th style="width: 85px;">&nbsp;</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong><?php echo language::translate('title_default_item', 'Default Item'); ?></strong></td>
                  <td><?php echo functions::form_draw_text_field('sku', true); ?></td>
                  <td>
                    <div class="input-group">
                      <?php echo functions::form_draw_decimal_field('weight', true, 4, 0); ?>
                      <?php echo functions::form_draw_weight_classes_list('weight_class', true); ?>
                    </div>
                  </td>
                  <td>
                    <div class="input-group">
                      <?php echo functions::form_draw_decimal_field('dim_x', true, 4, 0); ?>
                      <?php echo functions::form_draw_decimal_field('dim_y', true, 4, 0); ?>
                      <?php echo functions::form_draw_decimal_field('dim_z', true, 4, 0); ?>
                      <?php echo functions::form_draw_length_classes_list('dim_class', true); ?>
                    </div>
                  </td>
                  <td><?php echo functions::form_draw_decimal_field('quantity', true, 2, null, null, 'data-quantity="'. (float)$product->data['quantity'] .'"' . (!empty($_POST['options_stock']) ? ' readonly' : '')); ?></td>
                  <td>
                    <div class="input-group">
                      <span class="input-group-text">&plusmn;</span>
                      <?php echo functions::form_draw_decimal_field('quantity_adjustment', true, 2, null, null, !empty($_POST['options_stock']) ? 'readonly' : ''); ?>
                    </div>
                  </td>
                  <td></td>
                </tr>
                <?php if (!empty($_POST['options_stock'])) foreach (array_keys($_POST['options_stock']) as $key) { ?>
                <tr>
                  <td><?php echo functions::form_draw_hidden_field('options_stock['.$key.'][id]', true); ?><?php echo functions::form_draw_hidden_field('options_stock['.$key.'][combination]', true); ?>
                    <?php echo functions::form_draw_hidden_field('options_stock['.$key.'][name]['. language::$selected['name'] .']', true); ?>
                    <?php echo $_POST['options_stock'][$key]['name'][language::$selected['code']]; ?></td>
                  <td><?php echo functions::form_draw_text_field('options_stock['.$key.'][sku]', true); ?></td>
                  <td>
                    <div class="input-group">
                      <?php echo functions::form_draw_decimal_field('options_stock['.$key.'][weight]', true, 4, 0); ?>
                      <?php echo functions::form_draw_weight_classes_list('options_stock['.$key.'][weight_class]', true); ?>
                    </div>
                  </td>
                  <td>
                    <div class="input-group">
                      <?php echo functions::form_draw_decimal_field('options_stock['.$key.'][dim_x]', true, 4, 0); ?>
                      <?php echo functions::form_draw_decimal_field('options_stock['.$key.'][dim_y]', true, 4, 0); ?>
                      <?php echo functions::form_draw_decimal_field('options_stock['.$key.'][dim_z]', true, 4, 0); ?>
                      <?php echo functions::form_draw_length_classes_list('options_stock['.$key.'][dim_class]', true); ?>
                    </div>
                  </td>
                  <td><?php echo functions::form_draw_decimal_field('options_stock['.$key.'][quantity]', true, 2, null, null, 'data-quantity="'. (isset($product->data['options_stock'][$key]['quantity']) ? (float)$product->data['options_stock'][$key]['quantity'] : '0') .'"'); ?></td>
                  <td>
                    <div class="input-group">
                      <span class="input-group-text">&plusmn;</span>
                      <?php echo functions::form_draw_decimal_field('options_stock['.$key.'][quantity_adjustment]', true); ?>
                    </div>
                  </td>
                  <td class="text-end">
                    <a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"'); ?></a>
                    <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"'); ?></a>
                    <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a>
                  </td>
                </tr>
              <?php } ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="7"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?> <a class="add" href="#"><?php echo language::translate('title_add_stock_option', 'Add Stock Option'); ?></a></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div id="new-stock-option" class="lightbox" style="display: none; max-width: 640px;">
            <h3 class="title"><?php echo language::translate('title_create_stock_option_from_combination_of_options', 'Create a new stock option from a combination of options'); ?></h3>

            <table class="table table-striped" style="width: 100%;">
              <thead>
                <tr>
                  <th></th>
                  <th style="width: 50%;"><?php echo language::translate('title_group', 'Group'); ?></th>
                  <th style="width: 50%;"><?php echo language::translate('title_value', 'Value'); ?></th>
                </tr>
              </thead>
              <tbody />
            </table>

            <button type="button" class="btn btn-default" name="add_stock_option"><?php echo language::translate('title_add_stock_option', 'Add Stock Option'); ?></button>
          </div>
        </div>
      </div>

      <div class="panel-action btn-group">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        <?php echo (isset($product->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<script>

// Initiate

  $('input[name^="name"]').on('input', function(e){
    var language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
    $('.nav-tabs a[href="#'+language_code+'"]').css('opacity', $(this).val() ? 1 : .5);
    $('input[name="name['+language_code+']"]').not(this).val($(this).val());
    $('input[name="head_title['+language_code+']"]').attr('placeholder', $(this).val());
    $('input[name="h1_title['+language_code+']"]').attr('placeholder', $(this).val());
  }).trigger('input');

  $('input[name^="short_description"]').on('input', function(e){
    var language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
    $('input[name="meta_description['+language_code+']"]').attr('placeholder', $(this).val());
  }).trigger('input');

// Default Category

  $('[data-toggle="category-picker"]').on('change', function() {
    var default_category_id = $('select[name="default_category_id"]').val();

    $('select[name="default_category_id"]').html('');

    $('input[name="categories[]"]').each(function() {
      $('select[name="default_category_id"]').append('<option value="'+ $(this).val() +'">'+ $(this).data('name') +'</option>');
    });

    $('select[name="default_category_id"] option[value="'+ default_category_id +'"]').prop('selected', true);
  }).trigger('change');

  $('select[name="default_category_id"]').val('<?php echo $product->data['default_category_id']; ?>');

// SKU

  $('input[name="sku"]').change(function() {
    $('input[name="sku"]').not(this).val($(this).val());
  });

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
               + '  <div class="thumbnail float-start">'
               + '    <img src="<?php echo document::href_link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_APP . 'images/no_image.png', $product_image_width, $product_image_height, settings::get('product_image_clipping'))); ?>" alt="" />'
               + '  </div>'
               + '  '
               + '  <div class="input-group">'
               + '    <?php echo functions::form_draw_file_field('new_images[]'); ?>'
               + '    <div class="input-group-text">'
               + '      <a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"'); ?></a>'
               + '      <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"'); ?></a>'
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
    if ($('#images img:first').length) {
      $('#tab-general .main-image').attr('src', $('#images img:first').attr('src'));
      return;
    }

    $('#tab-general .main-image').attr('src', '<?php echo document::href_link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_APP . 'images/no_image.png', $product_image_width, $product_image_height, settings::get('product_image_clipping'))); ?>');
  }

// Technical Data

  $('a.technical-data-hint').click(function(e){
    e.preventDefault();
    alert('Syntax:\n\nTitle1\nProperty1: Value1\nProperty2: Value2\n\nTitle2\nProperty3: Value3...');
  });

// Attributes

  $('select[name="new_attribute[group_id]"]').change(function(){
    $('body').css('cursor', 'wait');
    $.ajax({
      url: '<?php echo document::link(WS_DIR_ADMIN, ['doc' => 'attribute_values.json'], ['app']); ?>&group_id=' + $(this).val(),
      type: 'get',
      cache: true,
      async: true,
      dataType: 'json',
      error: function(jqXHR, textStatus, errorThrown) {
        alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
      },
      success: function(data) {
        $('select[name="new_attribute[value_id]"').html('');
        if ($('select[name="new_attribute[value_id]"').attr('disabled')) $('select[name="attribute[value_id]"]').prop('disabled', false);
        if (data) {
          $('select[name="new_attribute[value_id]"').append('<option value="0">-- <?php echo language::translate('title_select', 'Select'); ?> --</option>');
          $.each(data, function(i, zone) {
            $('select[name="new_attribute[value_id]"').append('<option value="'+ zone.id +'">'+ zone.name +'</option>');
          });
        } else {
          $('select[name="new_attribute[value_id]"').prop('disabled', true);
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
  });

  var new_attribute_i = 0;
  $('#tab-attributes button[name="add"]').click(function(){

    if ($('select[name="new_attribute[group_id]"]').val() == '') {
      alert("<?php echo language::translate('error_must_select_attribute_group', 'You must select an attribute group'); ?>");
      return;
    }

    if ($('select[name="new_attribute[value_id]"]').val() == '' || $('select[name="new_attribute[value_id]"]').val() == '0') {
      if ($('input[name="new_attribute[custom_value]"]').val() == '') {
        alert("<?php echo language::translate('error_must_select_attribute_value', 'You must select an attribute value'); ?>");
        return;
      }
    } else {
      if ($('input[name="new_attribute[custom_value]"]').val() != '') {
        alert("<?php echo language::translate('error_cannot_define_both_value_and_custom_value', 'You can not define both a value and a custom value'); ?>");
        return;
      }
    }

    var output = '<tr>'
               + '  <?php echo functions::escape_js(functions::form_draw_hidden_field('attributes[new_attribute_i][id]', '')); ?>'
               + '  <?php echo functions::escape_js(functions::form_draw_hidden_field('attributes[new_attribute_i][group_id]', 'new_group_id')); ?>'
               + '  <?php echo functions::escape_js(functions::form_draw_hidden_field('attributes[new_attribute_i][group_name]', 'new_group_name')); ?>'
               + '  <?php echo functions::escape_js(functions::form_draw_hidden_field('attributes[new_attribute_i][value_id]', 'new_value_id')); ?>'
               + '  <?php echo functions::escape_js(functions::form_draw_hidden_field('attributes[new_attribute_i][value_name]', 'new_value_name')); ?>'
               + '  <?php echo functions::escape_js(functions::form_draw_hidden_field('attributes[new_attribute_i][custom_value]', 'new_custom_value')); ?>'
               + '  <td>new_group_name</td>'
               + '  <td>new_value_name</td>'
               + '  <td>new_custom_value</td>'
               + '  <td class="text-end"><a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>'
               + '</tr>';

    while ($('input[name="attributes[new_'+new_attribute_i+']"]').length) new_attribute_i++;
    output = output.replace(/new_attribute_i/g, 'new_' + new_attribute_i);
    output = output.replace(/new_group_id/g, $('select[name="new_attribute[group_id]"] option:selected').val());
    output = output.replace(/new_group_name/g, $('select[name="new_attribute[group_id]"] option:selected').text());
    output = output.replace(/new_value_id/g, $('select[name="new_attribute[value_id]"] option:selected').val());
    if ($('select[name="new_attribute[value_id]"] option:selected').val() != '0') {
      output = output.replace(/new_value_name/g, $('select[name="new_attribute[value_id]"] option:selected').text());
    } else {
      output = output.replace(/new_value_name/g, '');
    }
    output = output.replace(/new_custom_value/g, $('input[name="new_attribute[custom_value]"]').val());
    new_attribute_i++;

    $('#tab-attributes tbody').append(output);
  });

  $('#tab-attributes tbody').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
  });

// Prices

  function get_tax_rate() {
    switch ($('select[name=tax_class_id]').val()) {
<?php
  $tax_classes_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."tax_classes
    order by name asc;"
  );
  while ($tax_class = database::fetch($tax_classes_query)) {
    echo '      case "'. $tax_class['id'] . '": return '. tax::get_tax(100, $tax_class['id'], 'store') .';';
  }
?>
      default: return 0;
    }
  }

  function get_currency_value(currency_code) {
    switch (currency_code) { <?php foreach (currency::$currencies as $currency) echo 'case \''. $currency['code'] .'\': return '. (float)$currency['value'] .'; '; ?> }
  }

  function get_currency_decimals(currency_code) {
    switch (currency_code) { <?php foreach (currency::$currencies as $currency) echo 'case \''. $currency['code'] .'\': return '. ($currency['decimals']+2) .'; '; ?> }
  }

// Update prices
  $('select[name="tax_class_id"]').on('change', function(){
    $('input[name^="prices"]').trigger('change');
  });

// Update gross price
  $('input[name^="prices"]').on('input', function() {
    var currency_code = $(this).attr('name').match(/^prices\[([A-Z]{3})\]$/)[1],
        decimals = get_currency_decimals(currency_code),
        gross_field = $('input[name="gross_prices['+ currency_code +']"]');

    var gross_price = Number( parseFloat($(this).val() || 0) * (1 + (get_tax_rate()/100)) ).toFixed(decimals);

    if ($(this).val() == 0) {
      $(gross_field).val('');
    } else {
      $(gross_field).val(gross_price);
    }

    update_currency_prices();
  }).trigger('change');

// Update net price
  $('input[name^="gross_prices"]').on('input', function() {
    var currency_code = $(this).attr('name').match(/^gross_prices\[([A-Z]{3})\]$/)[1],
        decimals = get_currency_decimals(currency_code),
        net_field = $('input[name="prices['+ currency_code +']"]');

    var net_price = Number( parseFloat($(this).val() || 0) / (1 + (get_tax_rate()/100)) ).toFixed(decimals);

    if ($(this).val() == 0) {
      $(net_field).val('');
    } else {
      $(net_field).val(net_price);
    }

    update_currency_prices();
  });

// Update price placeholders
  function update_currency_prices() {
    var store_currency_code = '<?php echo settings::get('store_currency_code'); ?>',
        currencies = ['<?php echo implode("','", array_keys(currency::$currencies)); ?>'],
        net_price = $('input[name^="prices"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val(),
        gross_price = $('input[name^="gross_prices"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val();

    if (!net_price) net_price = $('input[name^="prices"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').attr('placeholder');
    if (!gross_price) gross_price = $('input[name^="gross_prices"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').attr('placeholder');

    $.each(currencies, function(i,currency_code){
      if (currency_code == '<?php echo settings::get('store_currency_code'); ?>') return;

      var currency_decimals = get_currency_decimals(currency_code),
          currency_net_price = net_price / get_currency_value(currency_code);
          currency_gross_price = gross_price / get_currency_value(currency_code);

      currency_net_price = currency_net_price ? parseFloat(currency_net_price.toFixed(currency_decimals) || 0) : '';
      currency_gross_price = currency_gross_price ? parseFloat(currency_gross_price.toFixed(currency_decimals) || 0) : '';

      $('input[name="prices['+ currency_code +']"]').attr('placeholder', currency_net_price);
      $('input[name="gross_prices['+ currency_code +']"]').attr('placeholder', currency_gross_price);
    });
  }

  $('#price-incl-tax-tooltip').click(function(e) {
    e.preventDefault;
    alert('<?php echo str_replace(["\r", "\n", "'"], ["", "", "\\'"], language::translate('tooltip_field_price_incl_tax', 'This field helps you calculate gross price based on the store region tax. All prices input to database are always excluding tax.')); ?>');
  });

// Campaigns

  $('#table-campaigns').on('input', 'input[name^="campaigns"][name$="[percentage]"]', function() {
    var parent = $(this).closest('tr');

    <?php foreach (currency::$currencies as $currency) { ?>
    if ($('input[name^="prices"][name$="[<?php echo $currency['code']; ?>]"]').val() > 0) {
      var value = Number($('input[name="prices[<?php echo $currency['code']; ?>]"]').val() * (100 - $(this).val()) / 100).toFixed(<?php echo $currency['decimals']; ?>);
      $(parent).find('input[name$="[<?php echo $currency['code']; ?>]"]').val(value);
    } else {
      $(parent).find('input[name$="[<?php echo $currency['code']; ?>]"]').val("");
    }
    <?php } ?>

    <?php foreach (currency::$currencies as $currency) { ?>
    var value = Number($(parent).find('input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val() / <?php echo $currency['value']; ?>).toFixed(<?php echo $currency['decimals']; ?>);
    $(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').attr('placeholder', value);
    <?php } ?>
  });

  $('#table-campaigns').on('input', 'input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]', function() {
    var parent = $(this).closest('tr');
    var percentage = ($('input[name="prices[<?php echo settings::get('store_currency_code'); ?>]"]').val() - $(this).val()) / $('input[name="prices[<?php echo settings::get('store_currency_code'); ?>]"]').val() * 100;
    percentage = percentage.toFixed(2);
    $(parent).find('input[name$="[percentage]"]').val(percentage);

    <?php foreach (currency::$currencies as $currency) { ?>
    var value = 0;
    value = $(parent).find('input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val() / <?php echo $currency['value']; ?>;
    value = value.toFixed(<?php echo $currency['decimals']; ?>);
    $(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').attr("placeholder", value);
    if ($(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').val() == 0) {
      $(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').val('');
    }
    <?php } ?>
  });
  $('input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').trigger('input');

  $('#table-campaigns').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
  });

  var new_campaign_i = 1;
  $('#table-campaigns').on('click', '.add', function(e) {
    e.preventDefault();
    var output = '<tr>'
               + '  <td><?php echo functions::escape_js(language::translate('title_start_date', 'Start Date')); ?><br />'
               + '    <?php echo functions::escape_js(functions::form_draw_hidden_field('campaigns[new_campaign_i][id]', '') . functions::form_draw_datetime_field('campaigns[new_campaign_i][start_date]', '')); ?>'
               + '  </td>'
               + '  <td><?php echo functions::escape_js(language::translate('title_end_date', 'End Date')); ?><br />'
               + '    <?php echo functions::escape_js(functions::form_draw_datetime_field('campaigns[new_campaign_i][end_date]', '')); ?>'
               + '  </td>'
               + '  <td>- %<br />'
               + '    <?php echo functions::escape_js(functions::form_draw_decimal_field('campaigns[new_campaign_i][percentage]', '', 2, 0, null)); ?>'
               + '  </td>'
               + '  <td><?php echo functions::escape_js(settings::get('store_currency_code')); ?><br />'
               + '    <?php echo functions::escape_js(functions::form_draw_currency_field(settings::get('store_currency_code'), 'campaigns[new_campaign_i]['. settings::get('store_currency_code') .']', '')); ?>'
               + '  </td>'
<?php
  foreach (array_keys(currency::$currencies) as $currency_code) {
    if ($currency_code == settings::get('store_currency_code')) continue;
?>
               + '  <td><?php echo functions::escape_js($currency_code); ?><br />'
               + '    <?php echo functions::escape_js(functions::form_draw_currency_field($currency_code, 'campaigns[new_campaign_i]['. $currency_code .']', '')); ?>'
               + '  </td>'
<?php
  }
?>
               + '  <td><br /><a class="remove" href="#" title="<?php echo functions::escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
               + '</tr>';
    while ($('input[name="campaigns[new_'+new_campaign_i+']"]').length) new_campaign_i++;
    output = output.replace(/new_campaign_i/g, 'new_' + new_campaign_i);
    $('#table-campaigns tbody').append(output);
    new_campaign_i++;
  });

// Options

  $('#tab-options').on('click', '.remove-group', function(e) {
    e.preventDefault();
    $(this).closest('li').remove();
  });

  $('#tab-options').on('click', '.move-group-up, .move-group-down', function(e) {
    e.preventDefault();
    var row = $(this).closest('li');
    if ($(this).is('.move-group-up') && $(row).prevAll().length > 0) {
      $(row).insertBefore($(row).prev());
    } else if ($(this).is('.move-group-down') && $(row).nextAll().length > 0) {
      $(row).insertAfter($(row).next());
    }
  });

  $('#tab-options').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
  });

  $('#tab-options').on('click', '.move-up, .move-down', function(e) {
    e.preventDefault();
    var row = $(this).closest('tr');
    if ($(this).is('.move-up') && $(row).prevAll().length > 0) {
      $(row).insertBefore($(row).prev());
    } else if ($(this).is('.move-down') && $(row).nextAll().length > 0) {
      $(row).insertAfter($(row).next());
    }
  });

  $('body').on('change', '.featherlight select[name="new_predefined_option[group_id]"]', function(){
    $('body').css('cursor', 'wait');
    $.ajax({
      url: '<?php echo document::link(null, ['doc' => 'attribute_values.json'], ['app']); ?>&group_id=' + $(this).val(),
      type: 'get',
      cache: true,
      async: true,
      dataType: 'json',
      error: function(jqXHR, textStatus, errorThrown) {
        alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
      },
      success: function(data) {
        $('select[name="new_predefined_option[value_id]"').html('');
        if ($('select[name="new_predefined_option[value_id]"').attr('disabled')) $('select[name="new_predefined_option[value_id]"]').prop('disabled', false);
        if (data) {
          $('select[name="new_predefined_option[value_id]"').append('<option value="0">-- <?php echo language::translate('title_select', 'Select'); ?> --</option>');
          $.each(data, function(i, zone) {
            $('select[name="new_predefined_option[value_id]"').append('<option value="'+ zone.id +'">'+ zone.name +'</option>');
          });
        } else {
          $('select[name="new_predefined_option[value_id]"').prop('disabled', true);
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
  });

  $('body').on('change', '.featherlight select[name="new_user_input_option[group_id]"]', function(){
    $('body').css('cursor', 'wait');
    $.ajax({
      url: '<?php echo document::link(null, ['doc' => 'attribute_values.json'], ['app']); ?>&group_id=' + $(this).val(),
      type: 'get',
      cache: true,
      async: true,
      dataType: 'json',
      error: function(jqXHR, textStatus, errorThrown) {
        alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
      },
      success: function(data) {
        $('select[name="new_user_input_option[value_id]"').html('');
        if ($('select[name="new_user_input_option[value_id]"').attr('disabled')) $('select[name="new_user_input_option[value_id]"]').prop('disabled', false);
        if (data) {
          $('select[name="new_user_input_option[value_id]"').append('<option value="0">-- <?php echo language::translate('title_select', 'Select'); ?> --</option>');
          $.each(data, function(i, zone) {
            $('select[name="new_user_input_option[value_id]"').append('<option value="'+ zone.id +'">'+ zone.name +'</option>');
          });
        } else {
          $('select[name="new_user_input_option[value_id]"').prop('disabled', true);
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
  });

  $('body').on('change', '.featherlight select[name="new_predefined_option[value_id]"]', function(){
    $('input[name="new_predefined_option[custom_value]"').val('');
  });

  $('body').on('keydown', '.featherlight input[name="new_predefined_option[custom_value]"]', function(){
    $('select[name="new_predefined_option[value_id]"').val('0');
  });

  var new_option_group_i = 1;
  var new_option_value_i = 1;
  $('body').on('click', '.featherlight button[name="add_predefined_option"]', function(e) {
    e.preventDefault();

    var groupElement = $(this).closest('fieldset').find('select[name="new_predefined_option[group_id]"]');
    var valueElement = $(this).closest('fieldset').find('select[name="new_predefined_option[value_id]"]');
    var customValueElement = $(this).closest('fieldset').find('input[name="new_predefined_option[custom_value]"]');

    if ($(groupElement).val() == '') {
      alert("<?php echo language::translate('error_must_select_attribute_group', 'You must select an attribute group'); ?>");
      return;
    }
    if ($(valueElement).val() == '' || $(valueElement).val() == '0') {
      if ($(customValueElement).val() == '') {
        alert("<?php echo language::translate('error_must_select_attribute_value', 'You must select an attribute value'); ?>");
        return;
      }
    } else {
      if ($(customValueElement).val() != '') {
        console.log($(valueElement).val(), $(customValueElement).val());
        alert("<?php echo language::translate('error_cannot_define_both_value_and_custom_value', 'You can not define both a value and a custom value'); ?>");
        return;
      }
    }

    if ($('#tab-options :input[name^="options"][name$="[group_id]"][value="'+ $(groupElement).val() +'"]').closest('li').find('input[name$="[value_id]"][value="'+ $(valueElement).val() +'"]').length) {
      if ($(customValueElement).val() != '') {
        if ($('#tab-options :input[name^="options"][name$="[group_id]"][value="'+ $(groupElement).val() +'"]').closest('li').find('input[name$="[custom_value]"][value="'+ escape($(customValueElement).val()) +'"]').length) {
          alert("<?php echo language::translate('error_option_already_defined', 'This option is already defined'); ?>");
          return;
        }

      } else {
        if ($('#tab-options :input[name^="options"][name$="[group_id]"][value="'+ $(groupElement).val() +'"]').closest('li').find('input[name$="[value_id]"][value="'+ $(valueElement).val() +'"]').closest('tr').find('input[name$="[custom_value]"]').val() == $(customValueElement).val()) {
          alert("<?php echo language::translate('error_option_already_defined', 'This option is already defined'); ?>");
          return;
        }
      }
    }

    if (!$('#tab-options input[name^="options"][name$="[group_id]"][value="'+ $(groupElement).val() +'"]').length) {
      var output = '<li data-group-id="'+ escapeHTML($(groupElement).val()) +'" data-group-name="'+ escapeHTML($(groupElement).find('option:selected').text()) +'">'
                 + '  <div class="float-end">'
                 + '    <a class="move-group-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-2x', 'style="color: #3399cc;"'); ?></a>'
                 + '    <a class="move-group-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-2x', 'style="color: #3399cc;"'); ?></a>'
                 + '    <a class="remove-group" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-2x', 'style="color: #cc3333;"'); ?></a>'
                 + '  </div>'
                 + '  <h2>'+ $(this).closest('fieldset').find('select[name="new_predefined_option[group_id]"] option:selected').text() +'</h2>'
                 + '  <?php echo functions::escape_js(functions::form_draw_hidden_field('options[new_group_id][group_id]', 'new_group_id')); ?>'
                 + '  <div class="row">'
                 + '    <div class="form-group col-sm-4 col-md-2">'
                 + '      <label><?php echo functions::escape_js(language::translate('title_function', 'Function')); ?></label>'
                 + '      <?php echo functions::escape_js(functions::form_draw_select_field('options[new_group_id][function]', ['select', 'radio', 'checkbox'], 'select')); ?>'
                 + '    </div>'
                 + '    <div class="form-group col-sm-4 col-md-2">'
                 + '      <label><?php echo functions::escape_js(language::translate('title_sort_values', 'Sort Values')); ?></label>'
                 + '      <?php echo functions::escape_js(functions::form_draw_select_field('options[new_group_id][sort]', $option_sort_options, 'custom')); ?>'
                 + '    </div>'
                 + '    <div class="form-group col-sm-4 col-md-2">'
                 + '      <label><?php echo functions::escape_js(language::translate('title_required', 'Required')); ?></label>'
                 + '      <div class="checkbox">'
                 + '        <label><?php echo functions::form_draw_checkbox('options[new_group_id][required]', '1', true); ?> <?php echo language::translate('title_required', 'Required'); ?></label>'
                 + '      </div>'
                 + '    </div>'
                 + '  </div>'
                 + '  <div class="table-responsive">'
                 + '    <table id="table-options" class="table table-striped table-hover table-dragable data-table">'
                 + '      <thead>'
                 + '        <tr>'
                 + '          <th><?php echo functions::escape_js(language::translate('title_option', 'Option')); ?></th>'
                 + '          <th style="width: 150px;"><?php echo language::translate('title_price_operator', 'Price Operator'); ?></th>'
                 + '          <th colspan="<?php echo count(currency::$currencies); ?>"><?php echo language::translate('title_price_adjustment', 'Price Adjustment'); ?></th>'
                 + '          <th style="width: 85px;">&nbsp;</th>'
                 + '        </tr>'
                 + '      </thead>'
                 + '      <tbody>'
                 + '      </tbody>'
                 + '    </table>'
                 + '  </div>'
                 + '</li>';

      output = output.replace(/new_option_group_i/g, 'new_' + new_option_group_i);
      output = output.replace(/new_group_id/g, $(groupElement).val());
      output = output.replace(/new_group_name/g, $(groupElement).find('option:selected').text());
      $('#tab-options ul').append(output);
      new_option_group_i++;
    }

    var output = '<tr data-value-id="'+ escapeHTML($(valueElement).val()) +'" data-value-name="'+ escapeHTML(($(valueElement).val() != 0) ? $(valueElement).find('option:selected').text() : $(customValueElement).val()) +'">'
               + '  <td class="grabable"><?php echo functions::escape_js(functions::form_draw_hidden_field('options[new_group_id][values][new_option_value_i][value_id]', 'new_value_id')) . functions::form_draw_hidden_field('options[new_group_id][values][new_option_value_i][custom_value]', 'new_custom_value'); ?>'+ (($.inArray($(valueElement).val(), ['', '0']) !== -1) ? $(customValueElement).val() : $(valueElement).find('option:selected').text()) +'</td>'
               + '  <td style="text-align: center;"><?php echo functions::escape_js(functions::form_draw_select_field('options[new_group_id][values][new_option_value_i][price_operator]', ['+','%','*','='], true)); ?></td>'
               + '  <?php foreach (array_keys(currency::$currencies) as $currency_code) echo '<td style="width: 200px;">'. functions::escape_js(functions::form_draw_currency_field($currency_code, 'options[new_group_id][values][new_option_value_i]['. $currency_code. ']', '')) .'</td>'; ?>'
               + '  <td class="text-end"><a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"'); ?></a> <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"'); ?></a> <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>'
               + '</tr>';

    output = output.replace(/new_option_value_i/g, 'new_' + new_option_value_i);
    output = output.replace(/new_group_id/g, $(groupElement).val());
    output = output.replace(/new_value_id/g, $(valueElement).val());
    output = output.replace(/new_custom_value/g, $(customValueElement).val().replace(/"/, '&quot;'));
    $(':input[name^="options"][name$="[group_id]"][value="'+ $(groupElement).val() +'"]').closest('li').find('tbody').append(output);
    new_option_value_i++;

    $.featherlight.close();
  });

  $('body').on('click', '.featherlight button[name="add_user_input_option"]', function(e) {
    e.preventDefault();

    var groupElement = $(this).closest('fieldset').find('select[name="new_user_input_option[group_id]"]');

    if ($(groupElement).val() == '') {
      alert("<?php echo language::translate('error_must_select_attribute_group', 'You must select an attribute group'); ?>");
      return;
    }

    if ($('#tab-options :input[name^="options"][name$="[group_id]"][value="'+ $(groupElement).val() +'"]').length) {
      alert("<?php echo language::translate('error_group_already_defined', 'This group is already defined'); ?>");
      return;
    }

    var output = '<li>'
               + '  <div class="float-end">'
               + '    <a class="move-group-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-2x', 'style="color: #3399cc;"'); ?></a>'
               + '    <a class="move-group-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-2x', 'style="color: #3399cc;"'); ?></a>'
               + '    <a class="remove-group" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-2x', 'style="color: #cc3333;"'); ?></a>'
               + '  </div>'
               + '  <h2>'+ $(this).closest('fieldset').find('select[name="new_user_input_option[group_id]"] option:selected').text() +'</h2>'
               + '  <?php echo functions::escape_js(functions::form_draw_hidden_field('options[new_group_id][group_id]', 'new_group_id')); ?>'
               + '  <div class="row">'
               + '    <div class="form-group col-sm-4 col-md-2">'
               + '      <label><?php echo language::translate('title_function', 'Function'); ?></label>'
               + '      <?php echo functions::escape_js(functions::form_draw_select_field('options[new_group_id][function]', ['text', 'textarea'], 'text')); ?>'
               + '    </div>'
               + '    <div class="form-group col-sm-4 col-md-2">'
               + '      <label><?php echo language::translate('title_required', 'Required'); ?></label>'
               + '      <div class="checkbox">'
               + '        <label><?php echo functions::form_draw_checkbox('options[new_group_id][required]', '1', true); ?> <?php echo language::translate('title_required', 'Required'); ?></label>'
               + '      </div>'
               + '    </div>'
               + '  </div>'
               + '</li>';

    output = output.replace(/new_group_id/g, $(groupElement).val());
    output = output.replace(/new_group_name/g, $(groupElement).find('option:selected').text());
    $('#tab-options ul').append(output);

    $.featherlight.close();
  });

// Quantity Unit

  $('select[name="quantity_unit_id"]').change(function(){
    if ($('option:selected', this).data('decimals') === undefined) return;

    var decimals = $('option:selected', this).data('decimals');

    var value = parseFloat($('input[name="quantity"]').val() || 0).toFixed(decimals);
    $('input[name="quantity"]').val(value);

    $('input[name^="option_stock"][name$="[quantity]"]').each(function(){
      var value = parseFloat($(this).val() || 0).toFixed(decimals);
      $(this).val(value);
    });

    $('input[name^="option_stock"][name$="[quantity_adjustment]"]').each(function(){
      var value = parseFloat($(this).val() || 0).toFixed(decimals);
      $(this).val(value);
    });
  }).trigger('change');

// Stock

  $('#table-stock').on('input', 'input[name="quantity"]', function(){
    $('input[name="quantity_adjustment"]').val( parseFloat($(this).val() || 0) - parseFloat($(this).data('quantity') || 0) );
  });

  $('#table-stock').on('input', 'input[name="quantity_adjustment"]', function(){
    $('input[name="quantity"]').val( parseFloat($('input[name="quantity"]').data('quantity') || 0) + parseFloat($(this).val() || 0) );
  });

  $('#table-stock').on('input', 'input[name$="[quantity]"]', function(){
    var adjustment_field = $(this).closest('tr').find('input[name$="[quantity_adjustment]"]');
    $(adjustment_field).val( parseFloat($(this).val() || 0) - parseFloat($(this).data('quantity') || 0) );

    $('input[name="quantity"]').val(0);
    $(this).closest('tbody').find('input[name$="[quantity]"]').each(function() {
      $('input[name="quantity"]').val( parseFloat($('input[name="quantity"]').val() || 0) + parseFloat($(this).val() || 0) );
    });

    $('input[name="quantity_adjustment"]').val(0);
    $(this).closest('tbody').find('input[name$="[quantity_adjustment]"]').each(function() {
      $('input[name="quantity_adjustment"]').val( parseFloat($('input[name="quantity_adjustment"]').val() || 0) + parseFloat($(this).val() || 0) );
    });
  });

  $('#table-stock').on('input', 'input[name$="[quantity_adjustment]"]', function(){
    var qty_field = $(this).closest('tr').find('input[name$="[quantity]"]');
    $(qty_field).val( parseFloat($(qty_field).data('quantity') || 0) + parseFloat($(this).val() || 0) );

    $('input[name="quantity"]').val(0);
    $(this).closest('tbody').find('input[name$="[quantity]"]').each(function() {
      $('input[name="quantity"]').val( parseFloat($('input[name="quantity"]').val() || 0) + parseFloat($(this).val() || 0) );
    });

    $('input[name="quantity_adjustment"]').val(0);
    $(this).closest('tbody').find('input[name$="[quantity_adjustment]"]').each(function() {
      $('input[name="quantity_adjustment"]').val( parseFloat($('input[name="quantity_adjustment"]').val() || 0) + parseFloat($(this).val() || 0) );
    });
  });

  $('#table-stock').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();

    var total = 0;
    $(this).closest('tbody').find('input[name$="[quantity]"]').each(function() {
      total += parseFloat($(this).val() || 0);
    });

    if (!$('input[name^="options_stock"][name$="[id]"]').length) {

      $('input[name="quantity"]').prop('readonly', false);
      $('input[name="quantity_adjustment"]').prop('readonly', false);
      $('input[name="quantity"]').val('');
      $('input[name="quantity_adjustment"]').val('');

    } else {

      $('input[name="quantity"]').val(0);
      $('input[name^="options_stock"][name$="[quantity]"]').each(function() {
        $('input[name="quantity"]').val( parseFloat($('input[name="quantity"]').val() || 0) + parseFloat($(this).val() || 0) );
      });

      $('input[name="quantity_adjustment"]').val(0);
      $('input[name^="options_stock"][name$="[quantity_adjustment]"]').each(function() {
        $('input[name="quantity_adjustment"]').val( parseFloat($('input[name="quantity_adjustment"]').val() || 0) + parseFloat($(this).val() || 0) );
      });
    }
  });

  $('#table-stock').on('click', '.move-up, .move-down', function(e) {
    e.preventDefault();
    var row = $(this).closest('tr');

    if ($(this).is('.move-up') && $(row).prevAll().length > 1) {
      $(row).insertBefore($(row).prev());
    } else if ($(this).is('.move-down') && $(row).nextAll().length > 0) {
      $(row).insertAfter($(row).next());
    }
  });

// New Stock Option (Modal)

  $('#table-stock').on('click', '.add', function(e) {
    e.preventDefault();

    var output = '';

    $('#options li').each(function(i, group) {
      var group_id = $(this).data('group-id'),
        group_name = $(this).data('group-name');

      $(group).find('.data-table tbody tr').each(function(j, row) {
        var value_id = $(row).data('value-id'),
          value_name = $(row).data('value-name'),
          combination = group_id + '-' + ((value_id != 0) ? value_id : '0:"' + value_name +'"');

        output += '<tr data-group-id="'+ escapeHTML(group_id) +'" data-group-name="'+ escapeHTML(group_name) +'" data-value-id="'+ escapeHTML(value_id) +'" data-value-name="'+ escapeHTML(value_name) +'">'
                + '  <td><span class="form-check"><input type="checkbox" name="combination[]" value="'+ escapeHTML(combination) +'" /></span></td>'
                + '  <td>'+ group_name +'</td>'
                + '  <td>'+ value_name +'</td>'
                + '</tr>';
      });
    });

    $('#new-stock-option table tbody').html(output);
    $.featherlight('#new-stock-option');
  });

  var new_option_stock_i = 1;
  $('body').on('click', '#new-stock-option button[name="add_stock_option"]', function(e) {
    e.preventDefault();

    var modal = $(this).closest('#new-stock-option'),
      new_option_combination = '',
      new_option_name = '',
      use_comma = false;

    $(modal).find('table tbody tr :input[name="combination[]"]:checked').each(function() {
      var row = $(this).closest('tr');

      if (use_comma) {
        new_option_combination += ',';
        new_option_name += ', ';
      }

      new_option_combination += $(row).data('group-id') + '-' + $(row).data('value-id')
                              + (($(row).data('value-id') == 0) ? ':"' + $(row).data('value-name')+'"' : '');

      new_option_name += $(row).data('value-name');

      use_comma = true;
    });

    if (new_option_combination == '') return;

    var output = '<tr>'
               + '  <td><?php echo functions::escape_js(functions::form_draw_hidden_field('options_stock[new_option_stock_i][id]', '') . functions::form_draw_hidden_field('options_stock[new_option_stock_i][combination]', 'new_option_combination') . functions::form_draw_hidden_field('options_stock[new_option_stock_i][name]['. language::$selected['code'] .']', 'new_option_name')); ?>new_option_name</td>'
               + '  <td><?php echo functions::escape_js(functions::form_draw_text_field('options_stock[new_option_stock_i][sku]', '')); ?></td>'
               + '  <td>'
               + '    <div class="input-group">'
               + '      <?php echo functions::escape_js(functions::form_draw_decimal_field('options_stock[new_option_stock_i][weight]', '0.00', 4, 0)); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_weight_classes_list('options_stock[new_option_stock_i][weight_class]', '')); ?>'
               + '    </div>'
               + '  </td>'
               + '  <td>'
               + '    <div class="input-group">'
               + '      <?php echo functions::escape_js(functions::form_draw_decimal_field('options_stock[new_option_stock_i][dim_x]', '0.00', 4, 0)); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_decimal_field('options_stock[new_option_stock_i][dim_y]', '0.00', 4, 0)); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_decimal_field('options_stock[new_option_stock_i][dim_z]', '0.00', 4, 0)); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_length_classes_list('options_stock[new_option_stock_i][dim_class]', '')); ?>'
               + '    </div>'
               + '  </td>'
               + '  <td><?php echo functions::escape_js(functions::form_draw_decimal_field('options_stock[new_option_stock_i][quantity]', '0', 2, null, null, 'data-quantity="0"')); ?></td>'
               + '  <td>'
               + '    <div class="input-group">'
               + '      <span class="input-group-text">&plusmn;</span>'
               + '    <?php echo functions::escape_js(functions::form_draw_decimal_field('options_stock[new_option_stock_i][quantity_adjustment]', '0')); ?>'
               + '    </div>'
               + '  </td>'
               + '  <td class="text-end">'
               + '    <a class="move-up" href="#" title="<?php echo functions::escape_js(language::translate('text_move_up', 'Move up'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"')); ?></a>'
               + '    <a class="move-down" href="#" title="<?php echo functions::escape_js(language::translate('text_move_down', 'Move down'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"')); ?></a>'
               + '    <a class="remove" href="#" title="<?php echo functions::escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a>'
               + '  </td>'
               + '</tr>';

    while ($('input[name="options_stock[new_'+new_option_stock_i+']"]').length) new_option_stock_i++;
    output = output.replace(/new_option_stock_i/g, 'new_' + new_option_stock_i);
    output = output.replace(/new_option_combination/g, escapeHTML(new_option_combination));
    output = output.replace(/new_option_name/g, new_option_name);

    $('#table-stock').find('tbody').append(output);
    new_option_stock_i++;

    $('input[name="quantity"]').prop('readonly', true);
    $('input[name="quantity_adjustment"]').prop('readonly', true);

    if ($('input[name^="options_stock"][name$="[id]"]').length == 1) {
      $('input[name="quantity"]').val('');
      $('input[name="quantity_adjustment"]').val('');
    }

    $.featherlight.close();
  });
</script>