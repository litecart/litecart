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

  document::$snippets['title'][] = !empty($product->data['id']) ? language::translate('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : language::translate('title_create_new_product', 'Create New Product');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'), document::ilink(__APP__.'/category_tree'));
  breadcrumbs::add(!empty($product->data['id']) ? language::translate('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : language::translate('title_create_new_product', 'Create New Product'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['name'][language::$selected['code']])) throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));
      if (empty($_POST['categories'])) throw new Exception(language::translate('error_must_select_category', 'You must select a category'));

      if (!empty($_POST['code']) && database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."products where id != '". (int)$product->data['id'] ."' and code = '". database::input($_POST['code']) ."' limit 1;"))) throw new Exception(language::translate('error_code_database_conflict', 'Another entry with the given code already exists in the database'));

      if (empty($_POST['categories'])) $_POST['categories'] = [];
      if (empty($_POST['images'])) $_POST['images'] = [];
      if (empty($_POST['attributes'])) $_POST['attributes'] = [];
      if (empty($_POST['campaigns'])) $_POST['campaigns'] = [];
      if (empty($_POST['stock_options'])) $_POST['stock_options'] = [];
      if (empty($_POST['autofill_technical_data'])) $_POST['autofill_technical_data'] = '';

      $_POST['keywords'] = preg_split('#\s*,\s*#', $_POST['keywords'], -1, PREG_SPLIT_NO_EMPTY);

      $fields = [
        'code',
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
        'recommended_price',
        'prices',
        'campaigns',
        'tax_class_id',
        'name',
        'short_description',
        'description',
        'technical_data',
        'autofill_technical_data',
        'head_title',
        'meta_description',
        'images',
        'stock_options',
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
      header('Location: '. document::ilink(__APP__.'/category_tree', ['category_id' => $_POST['categories'][0]]));
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
      header('Location: '. document::ilink(__APP__.'/category_tree', ['category_id' => $_POST['categories'][0]]));
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
  text-align: right;
  padding: 0.25em 0;
}
</style>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($product->data['id']) ? language::translate('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : language::translate('title_create_new_product', 'Create New Product'); ?>
    </div>
  </div>

  <ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#tab-general"><?php echo language::translate('title_general', 'General'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-information"><?php echo language::translate('title_information', 'Information'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-prices"><?php echo language::translate('title_prices', 'Prices'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-attributes"><?php echo language::translate('title_attributes', 'Attributes'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-stock"><?php echo language::translate('title_stock', 'Stock'); ?></a></li>
  </ul>

  <div class="card-body">
    <?php echo functions::form_draw_form_begin('product_form', 'post', false, true); ?>

      <div class="tab-content">
        <div id="tab-general" class="tab-pane active">

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
                <?php echo functions::form_draw_datetime_field('date_valid_from', true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_date_valid_to', 'Date Valid To'); ?></label>
                <?php echo functions::form_draw_datetime_field('date_valid_to', true); ?>
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
                <?php echo functions::form_draw_regional_text_field('name['. language::$selected['code'] .']', language::$selected['code'], true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_code', 'Code'); ?></label>
                <?php echo functions::form_draw_text_field('code', true); ?>
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

                    <div class="thumbnail float-start">
                      <img src="<?php echo document::href_link(WS_DIR_STORAGE . functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $product->data['images'][$key]['filename'], $product_image_width, $product_image_height, settings::get('product_image_clipping'))); ?>" alt="" />
                    </div>

                    <div class="input-group">
                      <?php echo functions::form_draw_text_field('images['.$key.'][new_filename]', fallback($_POST['images'][$key]['new_filename'], $_POST['images'][$key]['filename'])); ?>
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
                    <div class="thumbnail float-start">
                      <img src="<?php echo document::href_link(WS_DIR_STORAGE . functions::image_thumbnail(FS_DIR_STORAGE . 'images/no_image.png', $product_image_width, $product_image_height, settings::get('product_image_clipping'))); ?>" alt="" />
                    </div>

                    <div class="input-group">
                      <?php echo functions::form_draw_file_field('new_images[]', 'accept="image/*"'); ?>
                      <div class="input-group-text">
                        <a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('move-up'); ?></a>
                        <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('move-down'); ?></a>
                        <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <a href="#" class="add btn btn-default btn-sm" title="<?php echo language::translate('text_add', 'Add'); ?>"><?php echo functions::draw_fonticon('add'); ?></a>
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

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_name', 'Name'); ?></label>
                  <?php echo functions::form_draw_regional_text_field('name['. $language_code .']', $language_code, true); ?>
                </div>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_short_description', 'Short Description'); ?></label>
                <?php echo functions::form_draw_regional_text_field('short_description['. $language_code .']', $language_code, true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_description', 'Description'); ?></label>
                <?php echo functions::form_draw_regional_wysiwyg_field('description['. $language_code .']', $language_code, true, 'style="height: 250px;"'); ?>
              </div>

              <div class="form-group">
                <span class="float-end"><?php echo functions::form_draw_checkbox('autofill_technical_data', ['1', language::translate('text_autogenerate_from_attributes', 'Generate from attributes')], ''); ?></span>
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

        <div id="tab-prices" class="tab-pane">

          <div style="max-width: 640px;">

            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_tax_class', 'Tax Class'); ?></label>
                <?php echo functions::form_draw_tax_classes_list('tax_class_id', true); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_recommended_price', 'Recommended Price'); ?> / MSRP</label>
                <?php echo functions::form_draw_currency_field('recommended_price', settings::get('site_currency_code'), true); ?>
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
                <?php foreach (currency::$currencies as $currency) { ?>
                <tr>
                  <td><?php echo functions::form_draw_currency_field('prices['. $currency['code'] .']', $currency['code'], true, 'data-currency-price="" placeholder=""'); ?></td>
                <td><?php echo functions::form_draw_currency_field('gross_prices['. $currency['code'] .']', $currency['code']); ?></td>
                </tr>
                <?php } ?>
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
                  <?php foreach (array_keys(currency::$currencies) as $currency_code) { ?>
                  <td><?php echo $currency_code; ?><br />
                    <?php echo functions::form_draw_currency_field('campaigns['.$key.']['. $currency_code. ']', $currency_code, isset($_POST['campaigns'][$key][$currency_code]) ? number_format((float)$_POST['campaigns'][$key][$currency_code], 4, '.', '') : ''); ?>
                  </td>
                   <?php } ?>
                  <td><br /><a class="btn btn-default btn-sm remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>
                </tr>
              <?php } ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="<?php echo 4 + count(currency::$currencies); ?>"><button class="btn btn-default add" type="button"><?php echo functions::draw_fonticon('add'); ?> <?php echo language::translate('text_add_campaign', 'Add Campaign'); ?></button></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <div id="tab-attributes" class="tab-pane" style="max-width: 960px;">

          <table class="table table-striped table-dragable data-table">
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
                <td class="grabable"><?php echo $_POST['attributes'][$key]['group_name']; ?></td>
                <td class="grabable"><?php echo $_POST['attributes'][$key]['value_name']; ?></td>
                <td class="grabable"><?php echo $_POST['attributes'][$key]['custom_value']; ?></td>
                <td class="text-end"><a class="btn btn-default btn-sm remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>
              </tr>
              <?php } ?>
            </tbody>
            <tfoot>
              <tr>
                <td><?php echo functions::form_draw_attribute_groups_list('new_attribute[group_id]', ''); ?></td>
                <td><?php echo functions::form_draw_select_field('new_attribute[value_id]', [], ''); ?></td>
                <td><?php echo functions::form_draw_text_field('new_attribute[custom_value]', ''); ?></td>
                <td><?php echo functions::form_draw_button('add', language::translate('title_add', 'Add'), 'button'); ?></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div id="tab-stock" class="tab-pane">

          <div class="row" style="max-width: 960px;">
            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_minimum_cart_quantity', 'Minimum Cart Quantity'); ?></label>
              <?php echo functions::form_draw_decimal_field('quantity_min', true, null, 'min="0"'); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_maximum_cart_quantity', 'Maximum Cart Quantity'); ?></label>
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

          <h3><?php echo language::translate('title_stock_options', 'Stock Options'); ?></h3>

          <table id="stock-options" class="table table-striped table-dragable table-hover data-table">
            <thead>
              <tr>
                <th><?php echo language::translate('title_item', 'Item'); ?></th>
                <th style="width: 150px;"><?php echo language::translate('title_sku', 'SKU'); ?></th>
                <th style="width: 100px;" class="text-end"><?php echo language::translate('title_weight', 'Weight'); ?></th>
                <th style="width: 150px;" class="text-end"><?php echo language::translate('title_dimensions', 'Dimensions'); ?></th>
                <th style="width: 125px;" class="text-center"><?php echo language::translate('title_pperator', 'Operator'); ?></th>
                <th style="width: 125px;" class="text-center"><?php echo language::translate('title_price', 'Price'); ?></th>
                <th style="width: 125px;" class="text-center"><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
                <th style="width: 175px;" class="text-center"><?php echo language::translate('title_adjust', 'Adjust'); ?></th>
                <th style="width: 175px;" class="text-center"><?php echo language::translate('title_backordered', 'Backordered'); ?></th>
                <th style="width: 85px;">&nbsp;</th>
                <th style="width: 50px;">&nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($_POST['stock_options'])) foreach (array_keys($_POST['stock_options']) as $key) { ?>
              <tr data-stock-item-id="<?php echo $_POST['stock_options'][$key]['stock_item_id']; ?>">
                <td class="grabable">
                  <?php echo functions::form_draw_hidden_field('stock_options['.$key.'][id]', true); ?>
                  <?php echo functions::form_draw_hidden_field('stock_options['.$key.'][stock_item_id]', true); ?>
                  <span class="name"><?php echo $_POST['stock_options'][$key]['name']; ?></span>
                </td>
                <td class="grabable">
                  <span class="sku"><?php echo $_POST['stock_options'][$key]['sku']; ?></span>
                </td>
                <td class="grabable text-end">
                  <span class="weight"><?php echo (float)$_POST['stock_options'][$key]['weight']; ?></span> <span class="weight_unit"><?php echo $_POST['stock_options'][$key]['weight_unit']; ?></span>
                </td>
                <td class="grabable text-end">
                  <span class="length"><?php echo (float)$_POST['stock_options'][$key]['length']; ?></span> x <span class="width"><?php echo (float)$_POST['stock_options'][$key]['width']; ?></span> x <span class="height"><?php echo (float)$_POST['stock_options'][$key]['height']; ?></span> <span class="length_unit"><?php echo $_POST['stock_options'][$key]['length_unit']; ?></span>
                </td>
                <td><?php echo functions::form_draw_select_field('stock_options[new_stock_item_i][price_operator]', ['+', '*', '%', '='], '+'); ?></td>
                <td>
                  <div class="dropdown">
                    <?php echo functions::form_draw_currency_field('stock_options['.$key.'][price]['. settings::get('site_currency_code') .']', settings::get('site_currency_code'), true, 'style="width: 125px;"'); ?>
                    <ul class="dropdown-menu">
                      <?php foreach (currency::$currencies as $currency) { ?>
                      <?php if ($currency['code'] == settings::get('site_currency_code')) continue; ?>
                      <li><?php echo functions::form_draw_currency_field('stock_options['.$key.'][price]['. $currency['code'] .']', $currency['code'], true, 'style="width: 125px;"'); ?></li>
                      <?php } ?>
                    </ul>
                  </div>
                </td>
                <td><?php echo functions::form_draw_decimal_field('stock_options['.$key.'][quantity]', true, 2, 'data-quantity="'. (isset($product->data['stock_options'][$key]) ? (float)$product->data['stock_options'][$key]['quantity'] : '0') .'"'); ?></td>
                <td>
                  <div class="input-group">
                    <span class="input-group-text">&plusmn;</span>
                    <?php echo functions::form_draw_decimal_field('stock_options['. $key .'][quantity_adjustment]', true); ?>
                  </div>
                </td>
                <td>
                  <div class="input-group">
                    <?php echo functions::form_draw_button('transfer', functions::draw_fonticon('fa-arrow-left'), 'button'); ?>
                    <?php echo functions::form_draw_decimal_field('stock_options['. $key .'][backordered]', true, 2, 'min="0"'); ?>
                  </div>
                </td>
                <td class="text-end">
                  <a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #39c;"'); ?></a>
                  <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #39c;"'); ?></a>
                  <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #c33;"'); ?></a>
                </td>
                <td class="text-end">
                  <a class="edit" href="<?php echo document::href_ilink(__APP__.'/edit_stock_item', ['stock_item_id' => $_POST['stock_options'][$key]['stock_item_id'], 'js_callback' => 'upsert_stock_item'], ['app']); ?>" data-toggle="lightbox" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil fa-lg'); ?></a>
                </td>
              </tr>
            <?php } ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="11">
                  <a href="<?php echo document::href_ilink(__APP__.'/stock_item_picker', ['js_callback' => 'upsert_stock_item']); ?>" class="btn btn-default" data-toggle="lightbox"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #6c6;"'); ?> <?php echo language::translate('title_add_stock_item', 'Add Stock Item'); ?></a>
                  <a href="<?php echo document::href_ilink(__APP__.'/edit_stock_item', ['js_callback' => 'upsert_stock_item']); ?>" class="btn btn-default" data-toggle="lightbox" data-seamless="true"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #6c6;"'); ?> <?php echo language::translate('title_create_new_stock_item', 'Create New Stock Item'); ?></a>
                </td>
              </tr>
            </tfoot>
          </table>

        </div>
      </div>

      <div class="card-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
        <?php echo (isset($product->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<script>
// Initiate
  $('#tab-general input[name="name[<?php echo settings::get('site_language_code'); ?>]"]').on('input', function(e){
    $('#tab-info input[name="'+ $(this).attr('name') +'"]').not(this).val($(this).val());
    $('input[name="head_title['+language_code+']"]').attr('placeholder', $(this).val());
    $('input[name="h1_title['+language_code+']"]').attr('placeholder', $(this).val());
  });

  $('#tab-info input[name^="name"]').on('input', function(e){
    var language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
    $('#tab-general input[name="'+ $(this).attr('name') +'"]').not(this).val($(this).val());
    $('input[name="head_title['+ language_code +']"]').attr('placeholder', $(this).val());
    $('input[name="h1_title['+ language_code +']"]').attr('placeholder', $(this).val());
  }).trigger('input');

  $('#tab-general input[name^="short_description"]').on('input', function(e){
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

  $('input[name="sku"]').on('input', function() {
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
               + '    <img src="<?php echo document::href_link(WS_DIR_STORAGE . functions::image_thumbnail(FS_DIR_STORAGE . 'images/no_image.png', $product_image_width, $product_image_height, settings::get('product_image_clipping'))); ?>" alt="" />'
               + '  </div>'
               + '  '
               + '  <div class="input-group">'
               + '    <?php echo functions::form_draw_file_field('new_images[]', 'accept="image/*"'); ?>'
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
      url: '<?php echo document::ilink(__APP__.'/attribute_values.json'); ?>?group_id=' + $(this).val(),
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
               + '  <td class="text-end"><a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>'
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
    switch (currency_code) {
      <?php foreach (currency::$currencies as $currency) echo 'case \''. $currency['code'] .'\': return '. (float)$currency['value'] .';' . PHP_EOL; ?>
    }
  }

  function get_currency_decimals(currency_code) {
    switch (currency_code) {
      <?php foreach (currency::$currencies as $currency) echo 'case \''. $currency['code'] .'\': return '. ($currency['decimals']+2) .';' . PHP_EOL; ?>
    }
  }

// Update prices
  $('select[name="tax_class_id"]').change('input', function(){
    $('input[name^="prices"]').trigger('input');
  });

// Update gross price
  $('input[name^="prices"]').on('input', function() {
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
  }).trigger('input');

// Update net price
  $('input[name^="gross_prices"]').on('input', function() {
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
               + '  <td><br /><a class="btn btn-default btn-sm remove" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('remove')); ?></a></td>'
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

    $('input[name="quantity_min"]').val( parseFloat($('input[name="quantity_min"]').val() || 0).toFixed(decimals) );
    $('input[name="quantity_max"]').val( parseFloat($('input[name="quantity_max"]').val() || 0).toFixed(decimals) );
    $('input[name="quantity_step"]').val( parseFloat($('input[name="quantity_step"]').val() || 0).toFixed(decimals) );
    $('input[name="quantity"]').val( parseFloat($('input[name="quantity"]').val() || 0).toFixed(decimals) );

    $('input[name^="stock_item"][name$="[quantity]"]').each(function(){
      $(this).val( parseFloat($(this).val() || 0).toFixed(decimals) );
    });

    $('input[name^="stock_item"][name$="[quantity_adjustment]"]').each(function(){
      $(this).val( parseFloat($(this).val() || 0).toFixed(decimals) );
    });
  }).trigger('change');

// Stock

  <?php if (currency::$currencies > 1) { ?>
  $('#stock-options').on('focusin', 'input[name^="stock_options"][name*="[price]"]', function(){
    $(this).closest('.dropdown').dropdown();
  });
  <?php } ?>

  $('#stock-options').on('input', 'input[name$="[quantity]"]', function(){
    var adjustment_field = $(this).closest('tr').find('input[name$="[quantity_adjustment]"]');
    $(adjustment_field).val(parseFloat($(this).val() || 0) - parseFloat($(this).data('quantity') || 0));
  });

  $('#stock-options').on('input', 'input[name$="[quantity_adjustment]"]', function(){
    var qty_field = $(this).closest('tr').find('input[name$="[quantity]"]');
    $(qty_field).val(parseFloat($(qty_field).data('quantity') || 0) + parseFloat($(this).val() || 0));
  });

  $('#stock-options button[name="transfer"]').click(function(){
    var quantity_field = $(this).closest('tr').find('input[name$="[quantity_adjustment]"]');
    var backordered_field = $(this).closest('tr').find('input[name$="[backordered]"]');
    console.log($(quantity_field).length);
    $(quantity_field).val(parseFloat($(quantity_field).val() || 0) + parseFloat($(backordered_field).val() || 0)).trigger('input');
    $(backordered_field).val(0);
  });

  $('#stock-options').on('click', '.move-up, .move-down', function(e) {
    e.preventDefault();
    var row = $(this).closest('tr');

    if ($(this).is('.move-up') && $(row).prevAll().length > 1) {
      $(row).insertBefore($(row).prev());
    } else if ($(this).is('.move-down') && $(row).nextAll().length > 0) {
      $(row).insertAfter($(row).next());
    }
  });

  $('#stock-options').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();

    var total = 0;
    $(this).closest('tbody').find('input[name$="[quantity]"]').each(function() {
      total += parseFloat($(this).val() || 0);
    });

    if (!$('input[name^="stock_options"][name$="[id]"]').length) {

      $('input[name="quantity"]').prop('readonly', false);
      $('input[name="quantity_adjustment"]').prop('readonly', false);
      $('input[name="quantity"]').val('');
      $('input[name="quantity_adjustment"]').val('');

    } else {

      $('input[name="quantity"]').val(0);
      $('input[name^="stock_options"][name$="[quantity]"]').each(function() {
        $('input[name="quantity"]').val( parseFloat($('input[name="quantity"]').val() || 0) + parseFloat($(this).val() || 0) );
      });

      $('input[name="quantity_adjustment"]').val(0);
      $('input[name^="stock_options"][name$="[quantity_adjustment]"]').each(function() {
        $('input[name="quantity_adjustment"]').val( parseFloat($('input[name="quantity_adjustment"]').val() || 0) + parseFloat($(this).val() || 0) );
      });
    }
  });

  window.upsert_stock_item = function(stock_item) {

    var output = '<tr data-stock-item-id="'+ stock_item.id +'">'
               + '  <td class="grabable">'
               + '    <?php echo functions::general_escape_js(functions::form_draw_hidden_field('stock_options[new_stock_item_i][id]', '')); ?>'
               + '    <?php echo functions::general_escape_js(functions::form_draw_hidden_field('stock_options[new_stock_item_i][stock_item_id]', '')); ?>'
               + '    <span class="name"></name>'
               + '  </td>'
               + '  <td>'
               + '    <span class="sku"></span>'
               + '  </td>'
               + '  <td class="text-end">'
               + '    <span class="weight"></span> <span class="weight_unit"></span>'
               + '  </td>'
               + '  <td class="text-end">'
               + '    <span class="length"></span> x <span class="width"></span> x <span class="height"></span> <span class="length_unit"></span>'
               + '  </td>'
               + '  <td><?php echo functions::general_escape_js(functions::form_draw_select_field('stock_options[new_stock_item_i][price_operator]', ['+', '*', '%', '='], '+')); ?></td>'
               + '  <td>'
               + '    <div class="dropdown">'
               + '      <?php echo functions::general_escape_js(functions::form_draw_currency_field('stock_options[new_stock_item_i][price]['. settings::get('site_currency_code') .']', settings::get('site_currency_code'), '', 'style="width: 125px;"')); ?>'
               + '      <ul class="dropdown-menu">'
               <?php foreach (currency::$currencies as $currency) { ?>
               <?php if ($currency['code'] == settings::get('site_currency_code')) continue; ?>
               + '        <li><?php echo functions::general_escape_js(functions::form_draw_currency_field('stock_options[new_stock_item_i][price]['. $currency['code'] .']', $currency['code'], '', 'style="width: 125px;"')); ?></li>'
               <?php } ?>
               + '      </ul>'
               + '    </div>'
               + '  </td>'
               + '  <td><?php echo functions::general_escape_js(functions::form_draw_decimal_field('stock_options[new_stock_item_i][quantity]', '0', 2, 'data-quantity="new_stock_item_quantity"')); ?></td>'
               + '  <td>'
               + '    <div class="input-group">'
               + '      <span class="input-group-text">&plusmn;</span>'
               + '    <?php echo functions::general_escape_js(functions::form_draw_decimal_field('stock_options[new_stock_item_i][quantity_adjustment]', '0')); ?>'
               + '    </div>'
               + '  </td>'
               + '  <td>'
               + '    <div class="input-group">'
               + '      <?php echo functions::general_escape_js(functions::form_draw_button('transfer', functions::draw_fonticon('fa-arrow-left'), 'button')); ?>'
               + '      <?php echo functions::general_escape_js(functions::form_draw_decimal_field('stock_options[new_stock_item_i][backordered]', '', 2, 'min="0"')); ?>'
               + '    </div>'
               + '  </td>'
               + '  <td class="text-end">'
               + '    <a class="move-up" href="#" title="<?php echo functions::general_escape_js(language::translate('text_move_up', 'Move up'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #39c;"')); ?></a>'
               + '    <a class="move-down" href="#" title="<?php echo functions::general_escape_js(language::translate('text_move_down', 'Move down'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #39c;"')); ?></a>'
               + '    <a class="remove" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #c33;"')); ?></a>'
               + '  </td>'
               + '  <td class="text-end">'
               + '    <a class="edit" href="<?php echo document::href_ilink(__APP__.'/edit_stock_item', ['stock_item_id' => 'new_stock_item_id', 'js_callback' => 'upsert_stock_item'], ['app']); ?>" data-toggle="lightbox" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil fa-lg'); ?></a>'
               + '  </td>'
               + '</tr>';

    var new_stock_item_i = 1;
    while ($('input[name="stock_options[new_'+new_stock_item_i+']"]').length) new_stock_item_i++;
    output = output.replace(/new_stock_item_id/g, stock_item.id);
    output = output.replace(/new_stock_item_quantity/g, stock_item.quantity);
    output = output.replace(/new_stock_item_i/g, 'new_'+new_stock_item_i);

    if ($('#stock-options tbody tr[data-stock-item-id="'+ stock_item.id +'"]').length) {
      var row = $('#stock-options tbody tr[data-stock-item-id="'+ stock_item.id +'"]');
    } else {
      $('#stock-options tbody').append(output);
      var row = $('#stock-options tbody tr:last');
    }

    $.each(Object.keys(stock_item), function(i, key){ // Iterate Object.keys() because jQuery.each() doesn't support a property named length
      switch (key) {
        case 'id':
          var value = stock_item.id;
          key = 'stock_item_id';
          break;
        case 'name':
          if ($.isPlainObject(stock_item.name)) {
            var value = stock_item.name.<?php echo language::$selected['code']; ?>;
          } else {
            var value = stock_item.name;
          }
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