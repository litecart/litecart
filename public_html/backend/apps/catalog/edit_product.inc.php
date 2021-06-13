<?php

  if (!empty($_GET['product_id'])) {
    $product = new ent_product($_GET['product_id']);
  } else {
    $product = new ent_product();
    $product->data['quantity_min'] = 1;
    $product->data['quantity_max'] = 0;
    $product->data['quantity_step'] = 0;
  }

  if (!$_POST) {
    $_POST = $product->data;

    $_POST['keywords'] = implode(',', $_POST['keywords']);

    if (empty($product->data['id']) && isset($_GET['category_id'])) {
      $_POST['categories'][] = $_GET['category_id'];
    }
  }

  document::$snippets['title'][] = !empty($product->data['id']) ? language::translate('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : language::translate('title_add_new_product', 'Add New Product');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'), document::ilink('catalog/catalog'));
  breadcrumbs::add(!empty($product->data['id']) ? language::translate('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : language::translate('title_add_new_product', 'Add New Product'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['name'][language::$selected['code']])) throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));
      if (empty($_POST['categories'])) throw new Exception(language::translate('error_must_select_category', 'You must select a category'));

      if (!empty($_POST['code']) && database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."products where id != '". (int)$product->data['id'] ."' and code = '". database::input($_POST['code']) ."' limit 1;"))) throw new Exception(language::translate('error_code_database_conflict', 'Another entry with the given code already exists in the database'));
      if (!empty($_POST['sku'])  && database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."products where id != '". (int)$product->data['id'] ."' and sku = '". database::input($_POST['sku']) ."' limit 1;")))   throw new Exception(language::translate('error_sku_database_conflict', 'Another entry with the given SKU already exists in the database'));
      if (!empty($_POST['mpn'])  && database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."products where id != '". (int)$product->data['id'] ."' and mpn = '". database::input($_POST['mpn']) ."' limit 1;")))   throw new Exception(language::translate('error_mpn_database_conflict', 'Another entry with the given MPN already exists in the database'));
      if (!empty($_POST['gtin']) && database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."products where id != '". (int)$product->data['id'] ."' and gtin = '". database::input($_POST['gtin']) ."' limit 1;"))) throw new Exception(language::translate('error_gtin_database_conflict', 'Another entry with the given GTIN already exists in the database'));

      if (empty($_POST['categories'])) $_POST['categories'] = [];
      if (empty($_POST['images'])) $_POST['images'] = [];
      if (empty($_POST['attributes'])) $_POST['attributes'] = [];
      if (empty($_POST['campaigns'])) $_POST['campaigns'] = [];
      if (empty($_POST['stock_items'])) $_POST['stock_items'] = [];
      if (empty($_POST['autofill_technical_data'])) $_POST['autofill_technical_data'] = '';

      $_POST['keywords'] = preg_split('#\s*,\s*#', $_POST['keywords'], -1, PREG_SPLIT_NO_EMPTY);

      $fields = [
        'type',
        'status',
        'brand_id',
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
        'length',
        'width',
        'height',
        'length_unit',
        'weight',
        'weight_unit',
        'name',
        'short_description',
        'description',
        'technical_data',
        'autofill_technical_data',
        'head_title',
        'meta_description',
        'images',
        'stock_items',
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
      header('Location: '. document::ilink('catalog/catalog', ['category_id' => $_POST['categories'][0]]));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($product->data['id'])) throw new Exception(language::translate('error_must_provide_product', 'You must provide a product'));

      $product->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink('catalog/catalog', ['category_id' => $_POST['categories'][0]]));
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

<div class="card card-app">
  <div class="card-heading">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($product->data['id']) ? language::translate('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : language::translate('title_add_new_product', 'Add New Product'); ?>
    </div>
  </div>

  <ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#tab-general"><?php echo language::translate('title_general', 'General'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-information"><?php echo language::translate('title_information', 'Information'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-attributes"><?php echo language::translate('title_attributes', 'Attributes'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-prices"><?php echo language::translate('title_prices', 'Prices'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-stock"><?php echo language::translate('title_stock', 'Stock'); ?></a></li>
  </ul>

  <div class="card-body">
    <?php echo functions::form_draw_form_begin('product_form', 'post', false, true); ?>

      <div class="tab-content">
        <div id="tab-general" class="tab-pane active" style="max-width: 960px;">

          <div class="row">
            <div class="col-md-4">

              <div class="form-group">
                <label><?php echo language::translate('title_status', 'Status'); ?></label>
                <?php echo functions::form_draw_toggle('status', 'e/d', true); ?>
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
                 <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_text_field('name['. $language_code .']', $language_code, true); ?>
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
                <label><?php echo language::translate('title_brand', 'Brand'); ?></label>
                <?php echo functions::form_draw_brands_list('brand_id', true); ?>
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
    echo '<img class="main-image" src="'. document::href_link(WS_DIR_STORAGE . functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $image['filename'], $product_image_width, $product_image_height, settings::get('product_image_clipping'))) .'" alt="" />';
    reset($product->data['images']);
  } else {
    echo '<img class="main-image" src="'. document::href_link(WS_DIR_STORAGE . functions::image_thumbnail(FS_DIR_STORAGE . 'images/no_image.png', $product_image_width, $product_image_height, settings::get('product_image_clipping'))) .'" alt="" />';
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
                      <img src="<?php echo document::href_link(WS_DIR_STORAGE . functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $product->data['images'][$key]['filename'], $product_image_width, $product_image_height, settings::get('product_image_clipping'))); ?>" alt="" />
                    </div>

                    <div class="input-group">
                      <?php echo functions::form_draw_text_field('images['.$key.'][new_filename]', isset($_POST['images'][$key]['new_filename']) ? $_POST['images'][$key]['new_filename'] : $_POST['images'][$key]['filename']); ?>
                      <div class="input-group-text">
                        <a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('move-up'); ?></a>
                        <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('move-down'); ?></a>
                        <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a>
                      </div>
                    </div>
                  </div>
                  <?php } ?>
                </div>

                <div class="new-images">
                  <div class="image form-group">
                    <div class="thumbnail pull-left">
                      <img src="<?php echo document::href_link(WS_DIR_STORAGE . functions::image_thumbnail(FS_DIR_STORAGE . 'images/no_image.png', $product_image_width, $product_image_height, settings::get('product_image_clipping'))); ?>" alt="" />
                    </div>

                    <div class="input-group">
                      <?php echo functions::form_draw_file_field('new_images[]'); ?>
                      <div class="input-group-text">
                        <a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('move-up'); ?></a>
                        <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('move-down'); ?></a>
                        <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <a href="#" class="add" title="<?php echo language::translate('text_add', 'Add'); ?>"><?php echo functions::draw_fonticon('add'); ?></a>
                </div>
              </div>
            </div>
          </div>

        </div>

        <div id="tab-information" class="tab-pane" style="max-width: 640px;">

          <ul class="nav nav-tabs">
            <?php foreach (language::$languages as $language) { ?>
              <li<?php echo ($language['code'] == language::$selected['code']) ? ' class="active"' : ''; ?>><a data-toggle="tab" href="#<?php echo $language['code']; ?>"><?php echo $language['name']; ?></a></li>
            <?php } ?>
          </ul>

          <div class="tab-content">
            <?php foreach (array_keys(language::$languages) as $language_code) { ?>
            <div id="<?php echo $language_code; ?>" class="tab-pane fade in<?php echo ($language_code == language::$selected['code']) ? ' active' : ''; ?>">

              <div class="form-group">
                <label><?php echo language::translate('title_short_description', 'Short Description'); ?></label>
                <?php echo functions::form_draw_regional_text_field('short_description['. $language_code .']', $language_code, true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_description', 'Description'); ?></label>
                <?php echo functions::form_draw_regional_wysiwyg_field('description['. $language_code .']', $language_code, true, 'style="height: 250px;"'); ?>
              </div>

              <div class="form-group">
                <label class="pull-right"><?php echo functions::form_draw_checkbox('autofill_technical_data', '1', true); ?> <?php echo language::translate('text_autogenerate_from_attributes', 'Generate from attributes'); ?></label>
                <label><?php echo language::translate('title_technical_data', 'Technical Data'); ?> <a class="technical-data-hint" href="#"><?php echo functions::draw_fonticon('fa-question-circle'); ?></a></label>
                <?php echo functions::form_draw_regional_textarea('technical_data['. $language_code .']', $language_code, true, 'style="height: 250px;"'); ?>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_head_title', 'Head Title'); ?></label>
                  <?php echo functions::form_draw_regional_text_field('head_title['. $language_code .']', $language_code, true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_meta_description', 'Meta Description'); ?></label>
                  <?php echo functions::form_draw_regional_text_field('meta_description['. $language_code .']', $language_code, true); ?>
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
                <td class="text-right"><a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>
              </tr>
              <?php } ?>
            </tbody>
            <tfoot>
              <tr>
                <td><?php echo functions::form_draw_attribute_groups_list('new_attribute[group_id]', []); ?></td>
                <td><?php echo functions::form_draw_select_field('new_attribute[value_id]', [], ''); ?></td>
                <td><?php echo functions::form_draw_text_field('new_attribute[custom_value]', ''); ?></td>
                <td><?php echo functions::form_draw_button('add', language::translate('title_add', 'Add'), 'button'); ?></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div id="tab-prices" class="tab-pane">

          <div style="max-width: 640px;">

            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_purchase_price', 'Purchase Price'); ?></label>
                <div class="input-group">
                  <?php echo functions::form_draw_decimal_field('purchase_price', true, 2, 'min="0"'); ?>
                  <?php echo functions::form_draw_currencies_list('purchase_price_currency_code', true); ?>
                </div>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_recommended_price', 'Recommended Price'); ?> / MSRP</label>
                <?php echo functions::form_draw_currency_field('recommended_price', settings::get('site_currency_code'), true); ?>
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
                  <td><?php echo functions::form_draw_currency_field('prices['. settings::get('site_currency_code') .']', settings::get('site_currency_code'), true, 'data-currency-price="" placeholder=""'); ?></td>
                <td><?php echo functions::form_draw_decimal_field('gross_prices['. settings::get('site_currency_code') .']', '', currency::$currencies[settings::get('site_currency_code')]['decimals'], 'min="0"'); ?></td>
                </tr>
<?php
  foreach (currency::$currencies as $currency) {
    if ($currency['code'] == settings::get('site_currency_code')) continue;
?>
                <tr>
                  <td><?php echo functions::form_draw_currency_field('prices['. $currency['code'] .']', $currency['code'], true, 'data-currency-price="" placeholder=""'); ?></td>
                <td><?php echo functions::form_draw_decimal_field('gross_prices['. $currency['code'] .']', '', $currency['decimals'], 'min="0"'); ?></td>
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
                    <?php echo functions::form_draw_decimal_field('campaigns['.$key.'][percentage]', '', 2, 'min="0"'); ?>
                  </td>
                  <td><?php echo settings::get('site_currency_code'); ?><br />
                    <?php echo functions::form_draw_currency_field('campaigns['.$key.']['. settings::get('site_currency_code') .']', settings::get('site_currency_code'), true); ?>
                  </td>
<?php
  foreach (array_keys(currency::$currencies) as $currency_code) {
    if ($currency_code == settings::get('site_currency_code')) continue;
?>
                  <td><?php echo $currency_code; ?><br />
                    <?php echo functions::form_draw_currency_field('campaigns['.$key.']['. $currency_code. ']', $currency_code, isset($_POST['campaigns'][$key][$currency_code]) ? number_format((float)$_POST['campaigns'][$key][$currency_code], 4, '.', '') : ''); ?>
                  </td>
<?php
  }
?>
                  <td><br /><a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>
                </tr>
              <?php } ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="<?php echo 5 + count(currency::$currencies) - 1; ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #6c6;"'); ?> <a class="add" href="#"><?php echo language::translate('text_add_campaign', 'Add Campaign'); ?></a></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <div id="tab-stock" class="tab-pane">

          <div class="row" style="max-width: 960px;">
            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_quantity_min', 'Quantity Minimum'); ?></label>
              <?php echo functions::form_draw_decimal_field('quantity_min', true, null, 'min="0"'); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_quantity_maximum', 'Quantity Maximum'); ?></label>
              <?php echo functions::form_draw_decimal_field('quantity_max', true, null, 'min="0"'); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_quantity_step', 'Quantity Step'); ?></label>
              <?php echo functions::form_draw_decimal_field('quantity_step', true, null, 'min="0"'); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_quantity_unit', 'Quantity Unit'); ?></label>
              <?php echo functions::form_draw_quantity_units_list('quantity_unit_id', true); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_delivery_status', 'Delivery Status'); ?></label>
              <?php echo functions::form_draw_delivery_statuses_list('delivery_status_id', true); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_sold_out_status', 'Sold Out Status'); ?></label>
              <?php echo functions::form_draw_sold_out_statuses_list('sold_out_status_id', true); ?>
            </div>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_type', 'Type'); ?></label>
            <div>
              <label><?php echo functions::form_draw_radio_button('type', 'single', true); ?> <strong><?php echo language::translate('title_single', 'Single'); ?></strong> &ndash; <?php echo language::translate('text_let_the_customer_choose_one_of_the_stock_items', 'Let the customer choose one of the stock items'); ?></label>
              <label><?php echo functions::form_draw_radio_button('type', 'bundle', true); ?> <strong><?php echo language::translate('title_bundle', 'Bundle'); ?></strong> &ndash; <?php echo language::translate('text_all_stock_items_are_included', 'All stock items are included'); ?></label>
            </div>
          </div>

          <div class="table-responsive">
            <table id="stock-items" class="table table-striped table-dragable table-hover data-table">
              <thead>
                <tr>
                  <th style="width: 150px;"><?php echo language::translate('title_sku', 'SKU'); ?></th>
                  <th><?php echo language::translate('title_item', 'Item'); ?></th>
                  <th style="width: 100px;" class="text-right"><?php echo language::translate('title_weight', 'Weight'); ?></th>
                  <th style="width: 150px;" class="text-right"><?php echo language::translate('title_dimensions', 'Dimensions'); ?></th>
                  <th style="width: 125px;" class="text-center"><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
                  <th style="width: 175px;" class="text-center"><?php echo language::translate('title_adjust', 'Adjust'); ?></th>
                  <th style="width: 175px;" class="text-center"><?php echo language::translate('title_ordered', 'Ordered'); ?></th>
                  <th style="width: 85px;">&nbsp;</th>
                  <th style="width: 50px;">&nbsp;</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($_POST['stock_items'])) foreach (array_keys($_POST['stock_items']) as $key) { ?>
                <tr>
                  <td class="grabable">
                    <?php echo functions::form_draw_hidden_field('stock_items['.$key.'][id]', true); ?>
                    <?php echo functions::form_draw_hidden_field('stock_items['.$key.'][stock_item_id]', true); ?>
                    <?php echo functions::form_draw_hidden_field('stock_items['.$key.'][name]', true); ?>
                    <?php echo functions::form_draw_hidden_field('stock_items['. $key .'][sku]', true); ?>
                    <?php echo functions::form_draw_hidden_field('stock_items['. $key .'][gtin]', true); ?>
                    <?php echo functions::form_draw_hidden_field('stock_items['. $key .'][taric]', true); ?>
                    <?php echo functions::form_draw_hidden_field('stock_items['. $key .'][weight]', true); ?>
                    <?php echo functions::form_draw_hidden_field('stock_items['. $key .'][weight_unit]', true); ?>
                    <?php echo functions::form_draw_hidden_field('stock_items['. $key .'][length]', true); ?>
                    <?php echo functions::form_draw_hidden_field('stock_items['. $key .'][width]', true); ?>
                    <?php echo functions::form_draw_hidden_field('stock_items['. $key .'][height]', true); ?>
                    <?php echo functions::form_draw_hidden_field('stock_items['. $key .'][length_unit]', true); ?>
                    <span class="sku"><?php echo $_POST['stock_items'][$key]['sku']; ?></span>
                  </td>
                  <td class="grabable">
                    <span class="name"><?php echo $_POST['stock_items'][$key]['name']; ?></span>
                  </td>
                  <td class="grabable text-right">
                    <span class="weight"><?php echo (float)$_POST['stock_items'][$key]['weight']; ?></span> <span class="weight_unit"><?php echo $_POST['stock_items'][$key]['weight_unit']; ?></span>
                  </td>
                  <td class="grabable text-right">
                    <span class="length"><?php echo (float)$_POST['stock_items'][$key]['length']; ?></span> x <span class="width"><?php echo (float)$_POST['stock_items'][$key]['width']; ?></span> x <span class="height"><?php echo (float)$_POST['stock_items'][$key]['height']; ?></span> <span class="length_unit"><?php echo $_POST['stock_items'][$key]['length_unit']; ?></span>
                  </td>
                  <td><?php echo functions::form_draw_decimal_field('stock_items['.$key.'][quantity]', true, 2, 'data-quantity="'. (isset($product->data['stock_items'][$key]) ? (float)$product->data['stock_items'][$key]['quantity'] : '0') .'"'); ?></td>
                  <td>
                    <div class="input-group">
                      <span class="input-group-text">&plusmn;</span>
                      <?php echo functions::form_draw_decimal_field('stock_items['. $key .'][quantity_adjustment]', true); ?>
                    </div>
                  </td>
                  <td>
                    <div class="input-group">
                      <?php echo functions::form_draw_button('transfer', functions::draw_fonticon('fa-arrow-left'), 'button'); ?>
                      <?php echo functions::form_draw_decimal_field('stock_items['. $key .'][ordered]', true, 2, 'min="0"'); ?>
                    </div>
                  </td>
                  <td class="text-right">
                    <a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #39c;"'); ?></a>
                    <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #39c;"'); ?></a>
                    <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #c33;"'); ?></a>
                  </td>
                  <td class="text-right">
                    <a class="edit" href="<?php echo document::href_ilink('', ['doc' => 'edit_stock_item', 'stock_item_id' => $_POST['stock_items'][$key]['stock_item_id'], 'js_callback' => 'upsert_stock_item'], ['app']); ?>" data-toggle="lightbox" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil fa-lg'); ?></a>
                  </td>
                </tr>
              <?php } ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="9">
                    <a href="<?php echo document::href_ilink('catalog/stock_item_picker', ['js_callback' => 'upsert_stock_item']); ?>" class="btn btn-default" data-toggle="lightbox"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #6c6;"'); ?> <?php echo language::translate('title_add_stock_item', 'Add Stock Item'); ?></a>
                    <a href="<?php echo document::href_ilink('catalog/edit_stock_item', ['js_callback' => 'upsert_stock_item']); ?>" class="btn btn-default" data-toggle="lightbox"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #6c6;"'); ?> <?php echo language::translate('title_create_new_stock_item', 'Create New Stock Item'); ?></a>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>

        </div>
      </div>

      <div class="card-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        <?php echo (isset($product->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'class="btn btn-danger" onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<script>
// Initiate

  $('#tab-general input[name^="name"]').bind('input propertyChange', function(e){
    var language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
    $('input[name="head_title['+language_code+']"]').attr('placeholder', $(this).val());
    $('input[name="h1_title['+language_code+']"]').attr('placeholder', $(this).val());
  }).trigger('input');

  $('#tab-general input[name^="short_description"]').bind('input propertyChange', function(e){
    var language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
    $('input[name="meta_description['+language_code+']"]').attr('placeholder', $(this).val());
  }).trigger('input');

// Default Category

  $('[data-toggle="category-picker"]').change(function(){
    var default_category_id = $('select[name="default_category_id"] option:selected').val();

    $('select[name="default_category_id"]').html('');
    $.each($(this).find(':input[name="categories[]"]'), function(category){
      $('select[name="default_category_id"]').append('<option value="'+ $(this).val() +'">'+ unescape($(this).data('name')) +'</option>');
    });

    if (default_category_id) {
      $('select[name="default_category_id"]').val(default_category_id);
    }

    if (!$('select[name="default_category_id"]').val()) {
      $('select[name="default_category_id"]').val($('select[name="default_category_id"] option:first').val());
    }
  }).trigger('change');

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
               + '  <div class="thumbnail pull-left">'
               + '    <img src="<?php echo document::href_link(WS_DIR_STORAGE . functions::image_thumbnail(FS_DIR_STORAGE . 'images/no_image.png', $product_image_width, $product_image_height, settings::get('product_image_clipping'))); ?>" alt="" />'
               + '  </div>'
               + '  '
               + '  <div class="input-group">'
               + '    <?php echo functions::form_draw_file_field('new_images[]'); ?>'
               + '    <div class="input-group-text">'
               + '      <a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('move-up'); ?></a>'
               + '      <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('move-down'); ?></a>'
               + '      <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a>'
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

    $('#tab-general .main-image').attr('src', '<?php echo document::href_link(WS_DIR_STORAGE . functions::image_thumbnail(FS_DIR_STORAGE . 'images/no_image.png', $product_image_width, $product_image_height, settings::get('product_image_clipping'))); ?>');
  }

// Technical Data

  $('a.technical-data-hint').click(function(e){
    e.preventDefault();
    alert('Syntax:\n\nTitle1\nProperty1: Value1\nProperty2: Value2\n\nTitle2\nProperty3: Value3...');
  });

  $('input[name="autogenerate_techdata"]').change(function(){
    if ($(this).is(':checked')) {
      $('textarea[name^="technical_data"]').prop('disabled', true);
    } else {
      $('textarea[name^="technical_data"]').prop('disabled', false);
    }
  }).trigger('change');

// Attributes

  $('select[name="new_attribute[group_id]"]').change(function(){
    $('body').css('cursor', 'wait');
    $.ajax({
      url: '<?php echo document::ilink('catalog/attribute_values.json'); ?>&group_id=' + $(this).val(),
      type: 'get',
      cache: true,
      async: true,
      dataType: 'json',
      error: function(jqXHR, textStatus, errorThrown) {
        alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
      },
      success: function(data) {
        $('select[name="new_attribute[value_id]"').html('');
        if ($('select[name="new_attribute[value_id]"').is(':disabled')) $('select[name="attribute[value_id]"]').prop('disabled', false);
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
               + '  <?php echo functions::general_escape_js(functions::form_draw_hidden_field('attributes[new_attribute_i][id]', '')); ?>'
               + '  <?php echo functions::general_escape_js(functions::form_draw_hidden_field('attributes[new_attribute_i][group_id]', 'new_group_id')); ?>'
               + '  <?php echo functions::general_escape_js(functions::form_draw_hidden_field('attributes[new_attribute_i][group_name]', 'new_group_name')); ?>'
               + '  <?php echo functions::general_escape_js(functions::form_draw_hidden_field('attributes[new_attribute_i][value_id]', 'new_value_id')); ?>'
               + '  <?php echo functions::general_escape_js(functions::form_draw_hidden_field('attributes[new_attribute_i][value_name]', 'new_value_name')); ?>'
               + '  <?php echo functions::general_escape_js(functions::form_draw_hidden_field('attributes[new_attribute_i][custom_value]', 'new_custom_value')); ?>'
               + '  <td>new_group_name</td>'
               + '  <td>new_value_name</td>'
               + '  <td>new_custom_value</td>'
               + '  <td class="text-right"><a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>'
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
  $('select[name="tax_class_id"]').change('change', function(){
    $('input[name^="prices"]').trigger('change');
  });

// Update gross price
  $('input[name^="prices"]').bind('input change', function() {
    var currency_code = $(this).attr('name').match(/^prices\[([A-Z]{3})\]$/)[1],
        decimals = get_currency_decimals(currency_code),
        gross_field = $('input[name="gross_prices['+ currency_code +']"]');

    var gross_price = parseFloat(Number($(this).val() * (1+(get_tax_rate()/100))).toFixed(decimals));

    if ($(this).val() == 0) {
      $(gross_field).val('');
    } else {
      $(gross_field).val(gross_price);
    }

    update_currency_prices();
  }).trigger('change');

// Update net price
  $('input[name^="gross_prices"]').bind('input change', function() {
    var currency_code = $(this).attr('name').match(/^gross_prices\[([A-Z]{3})\]$/)[1],
        decimals = get_currency_decimals(currency_code),
        net_field = $('input[name="prices['+ currency_code +']"]');

    var net_price = parseFloat(Number($(this).val() / (1+(get_tax_rate()/100))).toFixed(decimals));

    if ($(this).val() == 0) {
      $(net_field).val('');
    } else {
      $(net_field).val(net_price);
    }

    update_currency_prices();
  });

// Update price placeholders
  function update_currency_prices() {
    var store_currency_code = '<?php echo settings::get('site_currency_code'); ?>',
        currencies = ['<?php echo implode("','", array_keys(currency::$currencies)); ?>'],
        net_price = $('input[name^="prices"][name$="[<?php echo settings::get('site_currency_code'); ?>]"]').val(),
        gross_price = $('input[name^="gross_prices"][name$="[<?php echo settings::get('site_currency_code'); ?>]"]').val();

    if (!net_price) net_price = $('input[name^="prices"][name$="[<?php echo settings::get('site_currency_code'); ?>]"]').attr('placeholder');
    if (!gross_price) gross_price = $('input[name^="gross_prices"][name$="[<?php echo settings::get('site_currency_code'); ?>]"]').attr('placeholder');

    $.each(currencies, function(i,currency_code){
      if (currency_code == '<?php echo settings::get('site_currency_code'); ?>') return;

      var currency_decimals = get_currency_decimals(currency_code),
          currency_net_price = net_price / get_currency_value(currency_code);
          currency_gross_price = gross_price / get_currency_value(currency_code);

      currency_net_price = currency_net_price ? parseFloat(currency_net_price.toFixed(currency_decimals)) : '';
      currency_gross_price = currency_gross_price ? parseFloat(currency_gross_price.toFixed(currency_decimals)) : '';

      $('input[name="prices['+ currency_code +']"]').attr('placeholder', currency_net_price);
      $('input[name="gross_prices['+ currency_code +']"]').attr('placeholder', currency_gross_price);
    });
  }

  $('#price-incl-tax-tooltip').click(function(e) {
    e.preventDefault;
    alert('<?php echo str_replace(["\r", "\n", "'"], ["", "", "\\'"], language::translate('tooltip_field_price_incl_tax', 'This field helps you calculate net price based on the store region tax. All prices input to database are always excluding tax.')); ?>');
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
    var value = Number($(parent).find('input[name^="campaigns"][name$="[<?php echo settings::get('site_currency_code'); ?>]"]').val() / <?php echo $currency['value']; ?>).toFixed(<?php echo $currency['decimals']; ?>);
    $(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').attr('placeholder', value);
    <?php } ?>
  });

  $('#table-campaigns').on('input', 'input[name^="campaigns"][name$="[<?php echo settings::get('site_currency_code'); ?>]"]', function() {
    var parent = $(this).closest('tr');
    var percentage = ($('input[name="prices[<?php echo settings::get('site_currency_code'); ?>]"]').val() - $(this).val()) / $('input[name="prices[<?php echo settings::get('site_currency_code'); ?>]"]').val() * 100;
    percentage = percentage.toFixed(2);
    $(parent).find('input[name$="[percentage]"]').val(percentage);

    <?php foreach (currency::$currencies as $currency) { ?>
    var value = 0;
    value = $(parent).find('input[name^="campaigns"][name$="[<?php echo settings::get('site_currency_code'); ?>]"]').val() / <?php echo $currency['value']; ?>;
    value = value.toFixed(<?php echo $currency['decimals']; ?>);
    $(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').attr("placeholder", value);
    if ($(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').val() == 0) {
      $(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').val('');
    }
    <?php } ?>
  });
  $('input[name^="campaigns"][name$="[<?php echo settings::get('site_currency_code'); ?>]"]').trigger('input');

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
               + '    <?php echo functions::general_escape_js(functions::form_draw_decimal_field('campaigns[new_campaign_i][percentage]', '', 2, 'min="0"')); ?>'
               + '  </td>'
               + '  <td><?php echo functions::general_escape_js(settings::get('site_currency_code')); ?><br />'
               + '    <?php echo functions::general_escape_js(functions::form_draw_currency_field('campaigns[new_campaign_i]['. settings::get('site_currency_code') .']', settings::get('site_currency_code'), '')); ?>'
               + '  </td>'
<?php
  foreach (array_keys(currency::$currencies) as $currency_code) {
    if ($currency_code == settings::get('site_currency_code')) continue;
?>
               + '  <td><?php echo functions::general_escape_js($currency_code); ?><br />'
               + '    <?php echo functions::general_escape_js(functions::form_draw_currency_field('campaigns[new_campaign_i]['. $currency_code .']', $currency_code, '')); ?>'
               + '  </td>'
<?php
  }
?>
               + '  <td><br /><a class="remove" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('remove')); ?></a></td>'
               + '</tr>';
    while ($('input[name="campaigns[new_'+new_campaign_i+']"]').length) new_campaign_i++;
    output = output.replace(/new_campaign_i/g, 'new_' + new_campaign_i);
    $('#table-campaigns tbody').append(output);
    new_campaign_i++;
  });

// Quantity Unit

  $('select[name="quantity_unit_id"]').change(function(){
    if ($('option:selected', this).data('decimals') === undefined) return;

    var decimals = $('option:selected', this).data('decimals');

    $('input[name="quantity_min"]').val( parseFloat($('input[name="quantity_min"]').val()).toFixed(decimals) );
    $('input[name="quantity_max"]').val( parseFloat($('input[name="quantity_max"]').val()).toFixed(decimals) );
    $('input[name="quantity_step"]').val( parseFloat($('input[name="quantity_step"]').val()).toFixed(decimals) );
    $('input[name="quantity"]').val( parseFloat($('input[name="quantity"]').val()).toFixed(decimals) );

    $('input[name^="stock_item"][name$="[quantity]"]').each(function(){
      $(this).val( parseFloat($(this).val()).toFixed(decimals) );
    });

    $('input[name^="option_stock"][name$="[quantity_adjustment]"]').each(function(){
      $(this).val( parseFloat($(this).val()).toFixed(decimals) );
    });
  }).trigger('change');

// Stock

  $('#stock-items').on('input', 'input[name$="[quantity]"]', function(){
    var adjustment_field = $(this).closest('tr').find('input[name$="[quantity_adjustment]"]');
    $(adjustment_field).val(parseFloat($(this).val()) - parseFloat($(this).data('quantity')));
  });

  $('#stock-items').on('input', 'input[name$="[quantity_adjustment]"]', function(){
    var qty_field = $(this).closest('tr').find('input[name$="[quantity]"]');
    $(qty_field).val(parseFloat($(qty_field).data('quantity')) + parseFloat($(this).val()));
  });

  $('#stock-items button[name="transfer"]').click(function(){
    var quantity_field = $(this).closest('tr').find('input[name$="[quantity_adjustment]"]');
    var ordered_field = $(this).closest('tr').find('input[name$="[ordered]"]');
    console.log($(quantity_field).length);
    $(quantity_field).val(Number($(quantity_field).val()) + Number($(ordered_field).val())).trigger('input');
    $(ordered_field).val(0);
  });

  $('#stock-items').on('click', '.move-up, .move-down', function(e) {
    e.preventDefault();
    var row = $(this).closest('tr');

    if ($(this).is('.move-up') && $(row).prevAll().length > 1) {
      $(row).insertBefore($(row).prev());
    } else if ($(this).is('.move-down') && $(row).nextAll().length > 0) {
      $(row).insertAfter($(row).next());
    }
  });

  $('#stock-items').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();

    var total = 0;
    $(this).closest('tbody').find('input[name$="[quantity]"]').each(function() {
      total += parseFloat($(this).val());
    });

    if (!$('input[name^="stock_items"][name$="[id]"]').length) {

      $('input[name="quantity"]').prop('readonly', false);
      $('input[name="quantity_adjustment"]').prop('readonly', false);
      $('input[name="quantity"]').val('');
      $('input[name="quantity_adjustment"]').val('');

    } else {

      $('input[name="quantity"]').val(0);
      $('input[name^="stock_items"][name$="[quantity]"]').each(function() {
        $('input[name="quantity"]').val(parseFloat($('input[name="quantity"]').val()) + parseFloat($(this).val()));
      });

      $('input[name="quantity_adjustment"]').val(0);
      $('input[name^="stock_items"][name$="[quantity_adjustment]"]').each(function() {
        $('input[name="quantity_adjustment"]').val(parseFloat($('input[name="quantity_adjustment"]').val()) + parseFloat($(this).val()));
      });
    }
  });

  window.upsert_stock_item = function(stock_item) {

    if (!$('input[name^="stock_items"][name$="[sku]"][value="'+ stock_item.sku +'"]').length) {
      var output = '<tr>'
                 + '  <td class="grabable">'
                 + '    <?php echo functions::general_escape_js(functions::form_draw_hidden_field('stock_items[new_stock_item_i][id]', '')); ?>'
                 + '    <?php echo functions::general_escape_js(functions::form_draw_hidden_field('stock_items[new_stock_item_i][stock_item_id]', '')); ?>'
                 + '    <?php echo functions::general_escape_js(functions::form_draw_hidden_field('stock_items[new_stock_item_i][name]', '')); ?>'
                 + '    <?php echo functions::general_escape_js(functions::form_draw_hidden_field('stock_items[new_stock_item_i][sku]', '')); ?>'
                 + '    <?php echo functions::general_escape_js(functions::form_draw_hidden_field('stock_items[new_stock_item_i][gtin]', '')); ?>'
                 + '    <?php echo functions::general_escape_js(functions::form_draw_hidden_field('stock_items[new_stock_item_i][taric]', '')); ?>'
                 + '    <?php echo functions::general_escape_js(functions::form_draw_hidden_field('stock_items[new_stock_item_i][weight]', '')); ?>'
                 + '    <?php echo functions::general_escape_js(functions::form_draw_hidden_field('stock_items[new_stock_item_i][weight_unit]', '')); ?>'
                 + '    <?php echo functions::general_escape_js(functions::form_draw_hidden_field('stock_items[new_stock_item_i][length]', '')); ?>'
                 + '    <?php echo functions::general_escape_js(functions::form_draw_hidden_field('stock_items[new_stock_item_i][width]', '')); ?>'
                 + '    <?php echo functions::general_escape_js(functions::form_draw_hidden_field('stock_items[new_stock_item_i][height]', '')); ?>'
                 + '    <?php echo functions::general_escape_js(functions::form_draw_hidden_field('stock_items[new_stock_item_i][length_unit]', '')); ?>'
                 + '    <span class="sku"></span>'
                 + '  </td>'
                 + '  <td>'
                 + '    <span class="name"></name>'
                 + '  </td>'
                 + '  <td class="text-right">'
                 + '    <span class="weight"></span> <span class="weight_unit"></span>'
                 + '  </td>'
                 + '  <td class="text-right">'
                 + '    <span class="length"></span> x <span class="width"></span> x <span class="height"></span> <span class="length_unit"></span>'
                 + '  </td>'
                 + '  <td><?php echo functions::general_escape_js(functions::form_draw_decimal_field('stock_items[new_stock_item_i][quantity]', '0', 2, 'data-quantity="0"')); ?></td>'
                 + '  <td>'
                 + '    <div class="input-group">'
                 + '      <span class="input-group-text">&plusmn;</span>'
                 + '    <?php echo functions::general_escape_js(functions::form_draw_decimal_field('stock_items[new_stock_item_i][quantity_adjustment]', '0')); ?>'
                 + '    </div>'
                 + '  </td>'
                 + '  <td>'
                 + '    <div class="input-group">'
                 + '      <?php echo functions::general_escape_js(functions::form_draw_button('transfer', functions::draw_fonticon('fa-arrow-left'), 'button')); ?>'
                 + '      <?php echo functions::general_escape_js(functions::form_draw_decimal_field('stock_items[new_stock_item_i][ordered]', '', 2, 'min="0"')); ?>'
                 + '    </div>'
                 + '  </td>'
                 + '  <td class="text-right">'
                 + '    <a class="move-up" href="#" title="<?php echo functions::general_escape_js(language::translate('text_move_up', 'Move up'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #39c;"')); ?></a>'
                 + '    <a class="move-down" href="#" title="<?php echo functions::general_escape_js(language::translate('text_move_down', 'Move down'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #39c;"')); ?></a>'
                 + '    <a class="remove" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #c33;"')); ?></a>'
                 + '  </td>'
                 + '  <td class="text-right">'
                 + '    <a class="edit" href="<?php echo document::href_ilink('catalog/edit_stock_item', ['stock_item_id' => 'new_stock_item_id', 'js_callback' => 'upsert_stock_item'], ['app']); ?>" data-toggle="lightbox" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil fa-lg'); ?></a>'
                 + '  </td>'
                 + '</tr>';

      var new_stock_item_i = 1;
      while ($('input[name="stock_items[new_'+new_stock_item_i+']"]').length) new_stock_item_i++;
      output = output.replace(/new_stock_item_id/g, stock_item.id);
      output = output.replace(/new_stock_item_i/g, 'new_'+new_stock_item_i);

      $('#stock-items').find('tbody').append(output);
      var row = $('#stock-items tbody tr:last');
    } else {
      var row = $('input[name^="stock_items"][name$="[sku]"][value="'+ stock_item.sku +'"]').closest('tr');
    }

    if (!$(row).length) console.error('Could not find row');

    $.each(Object.keys(stock_item), function(i, key){ // Iterate Object.keys() because jQuery.each() doesn't support a property named length
      switch (key) {
        case 'id':
          var value = stock_item[key];
          key = 'stock_item_id';
          break;
        case 'name':
          var value = stock_item[key][window._env.session.language_code];
          break;
        default:
          var value = stock_item[key];
          break;
      }
      $(row).find(':input[name$="['+key+']"]').val(value);
      $(row).find('.'+key).text(value);
    });
  }

</script>