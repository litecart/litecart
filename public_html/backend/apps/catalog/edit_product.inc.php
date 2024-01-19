<?php

  if (!empty($_GET['product_id'])) {
    $product = new ent_product($_GET['product_id']);
  } else {
    $product = new ent_product();
    $product->data['status'] = 1;
    $product->data['quantity_min'] = 1;
    $product->data['quantity_max'] = 0;
    $product->data['quantity_step'] = 0;
  }

  if (!$_POST) {
    $_POST = $product->data;

    if (empty($product->data['id']) && isset($_GET['category_id'])) {
      $_POST['categories'][] = $_GET['category_id'];
    }
  }

  document::$title[] = !empty($product->data['id']) ? language::translate('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : language::translate('title_create_new_product', 'Create New Product');

  breadcrumbs::add(language::translate('title_category_tree', 'Category Tree'), document::ilink(__APP__.'/category_tree'));
  breadcrumbs::add(!empty($product->data['id']) ? language::translate('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : language::translate('title_create_new_product', 'Create New Product'));

  if (isset($_POST['save'])) {

    try {

      if (empty($_POST['name'][language::$selected['code']])) {
        throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));
      }

      if (empty($_POST['categories'])) {
        throw new Exception(language::translate('error_must_select_category', 'You must select a category'));
      }

      if (!empty($_POST['code']) && database::query("select id from ". DB_TABLE_PREFIX ."products where id != '". (int)$product->data['id'] ."' and code = '". database::input($_POST['code']) ."' limit 1;")->num_rows) {
        throw new Exception(language::translate('error_code_database_conflict', 'Another entry with the given code already exists in the database'));
      }

      if (!empty($_FILES['new_images']['tmp_name'])) {
        foreach (array_keys($_FILES['new_images']['tmp_name']) as $key) {
          if (!empty($_FILES['new_images']['tmp_name'][$key]) && !empty($_FILES['new_images']['error'][$key])) {
            throw new Exception(language::translate('error_uploaded_image_rejected', 'An uploaded image was rejected for unknown reason'));
          }
        }
      }

      if (empty($_POST['categories'])) $_POST['categories'] = [];
      if (empty($_POST['images'])) $_POST['images'] = [];
      if (empty($_POST['campaigns'])) $_POST['campaigns'] = [];
      if (empty($_POST['attributes'])) $_POST['attributes'] = [];
      if (empty($_POST['stock_options'])) $_POST['stock_options'] = [];
      if (empty($_POST['autofill_technical_data'])) $_POST['autofill_technical_data'] = '';

      $fields = [
        'status',
        'brand_id',
        'delivery_status_id',
        'sold_out_status_id',
        'default_category_id',
        'categories',
        'attributes',
        'keywords',
        'synonyms',
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
        'code',
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
        if (isset($_POST[$field])) {
          $product->data[$field] = $_POST[$field];
        }
      }

      if (!empty($_FILES['new_images']['tmp_name'])) {
        foreach (array_keys($_FILES['new_images']['tmp_name']) as $key) {
          if (is_uploaded_file($_FILES['new_images']['tmp_name'][$key])) {
            $product->add_image($_FILES['new_images']['tmp_name'][$key]);
          }
        }
      }

      $product->save();

      if (!empty($_GET['redirect_url'])) {
        $_GET['redirect_url'] = new ent_link($_GET['redirect_url']);
        $_GET['redirect_url']->host = '';
      } else {
        $_GET['redirect_url'] = document::ilink(__APP__.'/category_tree', ['category_id' => isset($_POST['categories'][0]) ? $_POST['categories'][0] : '']);
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $_GET['redirect_url']);
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {

      if (empty($product->data['id'])) {
        throw new Exception(language::translate('error_must_provide_product', 'You must provide a product'));
      }

      $product->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/category_tree', ['category_id' => $_POST['categories'][0]]));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

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

  <nav class="nav nav-tabs">
    <a class="nav-link active" data-toggle="tab" href="#tab-general"><?php echo language::translate('title_general', 'General'); ?></a>
    <a class="nav-link" data-toggle="tab" href="#tab-information"><?php echo language::translate('title_information', 'Information'); ?></a>
    <a class="nav-link" data-toggle="tab" href="#tab-prices"><?php echo language::translate('title_prices', 'Prices'); ?></a>
    <a class="nav-link" data-toggle="tab" href="#tab-attributes"><?php echo language::translate('title_attributes', 'Attributes'); ?></a>
    <a class="nav-link" data-toggle="tab" href="#tab-stock"><?php echo language::translate('title_stock_and_delivery', 'Stock and Delivery'); ?></a>
  </nav>

  <div class="card-body">
    <?php echo functions::form_begin('product_form', 'post', false, true); ?>

      <div class="tab-content">
        <div id="tab-general" class="tab-pane active" style="max-width: 1200px;">

          <div class="row">
            <div class="col-md-4">

              <div class="form-group">
                <label><?php echo language::translate('title_status', 'Status'); ?></label>
                <?php echo functions::form_toggle('status', 'e/d', true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_brand', 'Brand'); ?></label>
                <?php echo functions::form_select_brand('brand_id', true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_categories', 'Categories'); ?></label>
                <?php echo functions::form_select_category('categories[]', true, 'style="max-height: 480px;"'); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_default_category', 'Default Category'); ?></label>
                <?php echo functions::form_select('default_category_id', [], true); ?>
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
                <?php echo functions::form_regional_text('name['. language::$selected['code'] .']', language::$selected['code'], true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_code', 'Code'); ?></label>
                <?php echo functions::form_input_text('code', true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_price', 'Price'); ?></label>
                <?php echo functions::form_input_money('prices['.settings::get('store_currency_code').']', settings::get('store_currency_code'), true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_keywords', 'Keywords'); ?></label>
                <?php echo functions::form_input_tags('keywords', true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_synonyms', 'Synonyms'); ?></label>
                <?php echo functions::form_input_tags('synonyms', true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_date_valid_from', 'Date Valid From'); ?></label>
                <?php echo functions::form_input_datetime('date_valid_from', true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_date_valid_to', 'Date Valid To'); ?></label>
                <?php echo functions::form_input_datetime('date_valid_to', true); ?>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label><?php echo language::translate('title_images', 'Images'); ?></label>
<?php
  if ($product->data['images']) {
    $image = current($product->data['images']);
    echo functions::draw_thumbnail('storage://images/' . ($image['filename'] ?  $image['filename'] : 'no_image.png'), 480, 0, 'product', 'id="main-image"');
    reset($product->data['images']);
  }
?>
              </div>

              <div id="images">

                <div class="images">
                  <?php if (!empty($_POST['images'])) foreach (array_keys($_POST['images']) as $key) { ?>
                  <div class="image form-group">
                    <?php echo functions::form_input_hidden('images['.$key.'][id]', true); ?>
                    <?php echo functions::form_input_hidden('images['.$key.'][filename]', $_POST['images'][$key]['filename']); ?>

                    <div class="float-start thumbnail <?php echo strtolower(settings::get('product_image_clipping')); ?>">
                      <?php echo functions::draw_thumbnail('storage://images/' . $product->data['images'][$key]['filename'], 64, 0, 'product'); ?>
                    </div>

                    <div class="input-group">
                      <?php echo functions::form_input_text('images['.$key.'][new_filename]', fallback($_POST['images'][$key]['new_filename'], $_POST['images'][$key]['filename'])); ?>
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
                </div>

                <div class="form-group">
                  <a href="#" class="add btn btn-default btn-sm"><?php echo functions::draw_fonticon('add'); ?> <?php echo language::translate('text_add_image', 'Add Image'); ?></a>
                </div>
              </div>
            </div>
          </div>

        </div>

        <div id="tab-information" class="tab-pane" style="max-width: 1200px;">

          <nav class="nav nav-tabs" style="margin-top: -1em;">
            <?php foreach (language::$languages as $language) { ?>
            <a class="nav-link<?php if ($language['code'] == language::$selected['code']) echo ' active'; ?>" data-toggle="tab" href="#<?php echo $language['code']; ?>"><?php echo $language['name']; ?></a>
            <?php } ?>
          </nav>

          <div class="tab-content">
            <?php foreach (array_keys(language::$languages) as $language_code) { ?>
            <div id="<?php echo $language_code; ?>" class="tab-pane fade in<?php if ($language_code == language::$selected['code']) echo ' active'; ?>">

              <div class="row">
                <div class="col-md-6">

                  <div class="form-group">
                    <label><?php echo language::translate('title_name', 'Name'); ?></label>
                    <?php echo functions::form_regional_text('name['. $language_code .']', $language_code, true); ?>
                  </div>

                  <div class="form-group">
                    <label><?php echo language::translate('title_short_description', 'Short Description'); ?></label>
                    <?php echo functions::form_regional_text('short_description['. $language_code .']', $language_code, true); ?>
                  </div>

                  <div class="form-group">
                    <label><?php echo language::translate('title_description', 'Description'); ?></label>
                    <?php echo functions::form_regional_wysiwyg('description['. $language_code .']', $language_code, true, 'style="height: 250px;"'); ?>
                  </div>

                  <div class="row">
                    <div class="form-group col-md-6">
                      <label><?php echo language::translate('title_head_title', 'Head Title'); ?></label>
                      <?php echo functions::form_regional_text('head_title['. $language_code .']', $language_code, true); ?>
                    </div>

                    <div class="form-group col-md-6">
                      <label><?php echo language::translate('title_meta_description', 'Meta Description'); ?></label>
                      <?php echo functions::form_regional_text('meta_description['. $language_code .']', $language_code, true); ?>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <?php echo language::translate('title_technical_data', 'Technical Data'); ?> <a class="technical-data-hint" href="#"><?php echo functions::draw_fonticon('fa-question-circle'); ?></a>
                    <?php echo functions::form_regional_textarea('technical_data['. $language_code .']', $language_code, true, 'style="height: 250px;"'); ?>
                    <div><?php echo functions::form_input_checkbox('autofill_technical_data', ['1', language::translate('text_autogenerate_from_attributes', 'Generate from attributes')], ''); ?></div>
                  </div>
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
                <?php echo functions::form_select_tax_class('tax_class_id', true); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_recommended_price', 'Recommended Price'); ?> / MSRP</label>
                <?php echo functions::form_input_money('recommended_price', settings::get('store_currency_code'), true); ?>
              </div>
            </div>

            <div id="prices" class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_price', 'Price'); ?></label>
                <?php echo functions::form_input_money('prices['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), true, 'data-currency-price="" placeholder=""'); ?>
                <?php foreach (array_diff(array_keys(currency::$currencies), [settings::get('store_currency_code')]) as $currency_code) { ?>
                <?php echo functions::form_input_money('prices['. $currency_code .']', $currency_code, true, 'data-currency-price="" placeholder=""'); ?>
                <?php } ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_price_incl_tax', 'Price Incl. Tax'); ?> <a href="#" id="price-incl-tax-tooltip"><?php echo functions::draw_fonticon('fa-question-circle'); ?></a></label>
                <?php echo functions::form_input_money('gross_prices['. settings::get('store_currency_code') .']', settings::get('store_currency_code')); ?>
                <?php foreach (array_diff(array_keys(currency::$currencies), [settings::get('store_currency_code')]) as $currency_code) { ?>
                <?php echo functions::form_input_money('gross_prices['. $currency_code .']', $currency_code); ?>
                <?php } ?>
              </div>
            </div>
          </div>

          <h2 style="margin-top: 2em;"><?php echo language::translate('title_campaigns', 'Campaigns'); ?></h2>

          <div style="margin: 0 -2em -3em -2em">
            <table id="table-campaigns" class="table table-striped data-table">
              <thead>
                <tr>
                  <th><?php echo language::translate('title_start_date', 'Start Date'); ?></th>
                  <th><?php echo language::translate('title_end_date', 'End Date'); ?></th>
                  <th><?php echo language::translate('title_percentage', 'Percentage'); ?></th>
                  <th><?php echo settings::get('store_currency_code'); ?></th>
                  <?php foreach (array_diff(array_keys(currency::$currencies), [settings::get('store_currency_code')]) as $currency_code) { ?><th><?php echo $currency_code; ?></th><?php } ?>
                  <th></th>
                </tr>
              </thead>

              <tbody>
                <?php if (!empty($_POST['campaigns'])) foreach (array_keys($_POST['campaigns']) as $key) { ?>
                <tr>
                  <td><?php echo functions::form_input_hidden('campaigns['.$key.'][id]', true) . functions::form_input_datetime('campaigns['.$key.'][start_date]', true); ?></td>
                  <td><?php echo functions::form_input_datetime('campaigns['.$key.'][end_date]', true); ?></td>
                  <td>
                    <div class="input-group">
                      <?php echo functions::form_input_decimal('campaigns['.$key.'][percentage]', '', 2, 'min="0"'); ?>
                      <span class="input-group-text">%</span>
                    </div>
                  </td>
                  <td><?php echo language::translate('title_end_date', 'End Date'); ?><br />
                    <?php echo functions::form_input_datetime('campaigns['.$key.'][end_date]', true); ?>
                  </td>
                  <td>- %<br />
                    <?php echo functions::form_input_decimal('campaigns['.$key.'][percentage]', '', 2, 'min="0"'); ?>
                  </td>
                  <?php foreach (array_keys(currency::$currencies) as $currency_code) { ?>
                  <td><?php echo functions::form_input_money('campaigns['.$key.']['. $currency_code. ']', $currency_code, isset($_POST['campaigns'][$key][$currency_code]) ? number_format((float)$_POST['campaigns'][$key][$currency_code], 4, '.', '') : ''); ?></td>
                  <?php } ?>
                  <td><a class="btn btn-default btn-sm remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>
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
                <th style="width: 320px;"><?php echo language::translate('title_attribute_group', 'Attribute Group'); ?></th>
                <th style="width: 320px;"><?php echo language::translate('title_value', 'Value'); ?></th>
                <th><?php echo language::translate('title_custom_value', 'Custom Value'); ?></th>
                <th style="width: 60px;"></th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($_POST['attributes'])) foreach (array_keys($_POST['attributes']) as $key) { ?>
              <tr>
                <?php echo functions::form_input_hidden('attributes['.$key.'][id]', true); ?>
                <?php echo functions::form_input_hidden('attributes['.$key.'][group_id]', true); ?>
                <?php echo functions::form_input_hidden('attributes['.$key.'][group_name]', true); ?>
                <?php echo functions::form_input_hidden('attributes['.$key.'][value_id]', true); ?>
                <?php echo functions::form_input_hidden('attributes['.$key.'][value_name]', true); ?>
                <?php echo functions::form_input_hidden('attributes['.$key.'][custom_value]', true); ?>
                <td class="grabable"><?php echo $_POST['attributes'][$key]['group_name']; ?></td>
                <td class="grabable"><?php echo $_POST['attributes'][$key]['value_name']; ?></td>
                <td class="grabable"><?php echo $_POST['attributes'][$key]['custom_value']; ?></td>
                <td class="text-end"><a class="btn btn-default btn-sm remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>
              </tr>
              <?php } ?>
            </tbody>
            <tfoot>
              <tr>
                <td><?php echo functions::form_select_attribute_group('new_attribute[group_id]', ''); ?></td>
                <td><?php echo functions::form_select('new_attribute[value_id]', [], ''); ?></td>
                <td><?php echo functions::form_input_text('new_attribute[custom_value]', ''); ?></td>
                <td><?php echo functions::form_button('add', language::translate('title_add', 'Add'), 'button'); ?></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div id="tab-stock" class="tab-pane">

          <div class="row">
            <div class="col-md-6">
              <div class="row">
                <div class="form-group col-md-3">
                  <label><?php echo language::translate('title_minimum_cart_quantity', 'Minimum Cart Quantity'); ?></label>
                  <?php echo functions::form_input_decimal('quantity_min', true, null, 'min="0"'); ?>
                </div>

                <div class="form-group col-md-3">
                  <label><?php echo language::translate('title_maximum_cart_quantity', 'Maximum Cart Quantity'); ?></label>
                  <?php echo functions::form_input_decimal('quantity_max', true, null, 'min="0"'); ?>
                </div>

                <div class="form-group col-md-3">
                  <label><?php echo language::translate('title_quantity_step', 'Quantity Step'); ?></label>
                  <?php echo functions::form_input_decimal('quantity_step', true, null, 'min="0"'); ?>
                </div>

                <div class="form-group col-md-3">
                  <label><?php echo language::translate('title_quantity_unit', 'Quantity Unit'); ?></label>
                  <?php echo functions::form_select_quantity_unit('quantity_unit_id', true); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_delivery_status', 'Delivery Status'); ?></label>
                  <?php echo functions::form_select_delivery_status('delivery_status_id', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_sold_out_status', 'Sold Out Status'); ?></label>
                  <?php echo functions::form_select_sold_out_status('sold_out_status_id', true); ?>
                </div>
              </div>

            </div>

            <div class="col-md-6">

              <h2><?php echo language::translate('title_also_included_products', 'Also Included Products'); ?></h2>

              <table class="table table-striped table-dragable data-table">
                <thead>
                  <tr>
                    <th><?php echo language::translate('title_id', 'ID'); ?></th>
                    <th><?php echo language::translate('title_name', 'Name'); ?></th>
                    <th class="main"><?php echo language::translate('title_stock_option', 'Stock Option'); ?></th>
                    <th><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
                    <th></th>
                  </tr>
                </thead>

                <tbody>
                  <?php foreach ($_POST['chained_products'] as $key => $chained_product) { ?>
                  <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-end">
                      <a class="remove btn btn-default btn-sm" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times', 'style="color: #c33;"'); ?></a>
                    </td>
                  </tr>
                  <?php } ?>
                </tbody>

                <tfoot>
                  <td colspan="5">
                    <a href="<?php echo document::href_ilink(__APP__.'/product_picker', ['js_callback' => 'add_chained_product']); ?>" class="btn btn-default" data-toggle="lightbox"><?php echo functions::draw_fonticon('add'); ?> <?php echo language::translate('title_add_product', 'Add Product'); ?></a>
                  </td>
                </tfoot>
              </table>

            </div>
          </div>

          <h3><?php echo language::translate('title_stock_options', 'Stock Options'); ?></h3>

          <table id="stock-options" class="table table-striped table-dragable table-hover data-table">
            <thead>
              <tr>
                <th colspan="2"><?php echo language::translate('title_stock_item', 'Stock Item'); ?></th>
                <th style="width: 100px;"><?php echo language::translate('title_sku', 'SKU'); ?></th>
                <th style="width: 100px;" class="text-end"><?php echo language::translate('title_weight', 'Weight'); ?></th>
                <th style="width: 150px;" class="text-end"><?php echo language::translate('title_dimensions', 'Dimensions'); ?></th>
                <th style="width: 125px;" class="text-center"><?php echo language::translate('title_price', 'Price'); ?></th>
                <th style="width: 150px;" class="text-center"><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
                <th style="width: 175px;" class="text-center"><?php echo language::translate('title_adjust_qty', 'Adjust Qty'); ?></th>
                <th style="width: 175px;" class="text-center"><?php echo language::translate('title_backordered', 'Backordered'); ?></th>
                <th style="width: 85px;"></th>
                <th style="width: 50px;"></th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($_POST['stock_options'])) foreach ($_POST['stock_options'] as $key => $stock_option) { ?>
              <tr data-stock-item-id="<?php echo $stock_option['stock_item_id']; ?>">
                <td class="grabable">
                  <?php echo functions::form_input_hidden('stock_options['.$key.'][id]', true); ?>
                  <?php echo functions::form_input_hidden('stock_options['.$key.'][stock_item_id]', true); ?>
                  <?php echo functions::form_input_hidden('stock_options['.$key.'][sku]', true); ?>
                  <?php echo functions::form_input_hidden('stock_options['.$key.'][weight]', true); ?>
                  <?php echo functions::form_input_hidden('stock_options['.$key.'][weight_unit]', true); ?>
                  <?php echo functions::form_input_hidden('stock_options['.$key.'][length]', true); ?>
                  <?php echo functions::form_input_hidden('stock_options['.$key.'][width]', true); ?>
                  <?php echo functions::form_input_hidden('stock_options['.$key.'][height]', true); ?>
                  <?php echo functions::form_input_hidden('stock_options['.$key.'][length_unit]', true); ?>
                  <span class="id"><?php echo $stock_option['id']; ?></span>
                </td>
                <td class="grabable">
                  <span class="name"><?php echo $stock_option['name'][language::$selected['code']]; ?></span>
                </td>
                <td class="grabable">
                  <span class="sku"><?php echo $stock_option['sku']; ?></span>
                </td>
                <td class="grabable text-end">
                  <span class="weight"><?php echo (float)$stock_option['weight']; ?></span> <span class="weight_unit"><?php echo $stock_option['weight_unit']; ?></span>
                </td>
                <td class="grabable text-end">
                  <span class="length"><?php echo (float)$stock_option['length']; ?></span> x <span class="width"><?php echo (float)$stock_option['width']; ?></span> x <span class="height"><?php echo (float)$stock_option['height']; ?></span> <span class="length_unit"><?php echo $stock_option['length_unit']; ?></span>
                </td>
                <td>
                  <div class="input-group">
                    <?php echo functions::form_select('stock_options['.$key.'][price_operator]', ['+', '*', '%', '='], true, 'style="width: 100px;"'); ?>
                    <div class="dropdown">
                      <?php echo functions::form_input_money('stock_options['.$key.'][price]['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), true, 'style="width: 125px;"'); ?>
                      <ul class="dropdown-menu">
                        <?php foreach (currency::$currencies as $currency) { ?>
                        <?php if ($currency['code'] == settings::get('store_currency_code')) continue; ?>
                        <li><?php echo functions::form_input_money('stock_options['.$key.'][price]['. $currency['code'] .']', $currency['code'], true, 'style="width: 125px;"'); ?></li>
                        <?php } ?>
                      </ul>
                    </div>
                  </div>
                </td>
                <td><?php echo functions::form_input_decimal('stock_options['.$key.'][quantity]', true, 2, 'data-quantity="'. (isset($product->data['stock_options'][$key]) ? (float)$product->data['stock_options'][$key]['quantity'] : '0') .'"'); ?></td>
                <td>
                  <div class="input-group">
                    <span class="input-group-text">&plusmn;</span>
                    <?php echo functions::form_input_decimal('stock_options['. $key .'][quantity_adjustment]', true); ?>
                  </div>
                </td>
                <td>
                  <div class="input-group">
                    <?php echo functions::form_button('transfer', functions::draw_fonticon('fa-arrow-left'), 'button'); ?>
                    <?php echo functions::form_input_decimal('stock_options['. $key .'][backordered]', true, 2, 'min="0"'); ?>
                  </div>
                </td>
                <td class="text-end">
                  <a class="remove btn btn-default btn-sm" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #c33;"'); ?></a>
                </td>
                <td class="text-end">
                  <a class="edit btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_stock_item', ['stock_item_id' => $stock_option['stock_item_id'], 'js_callback' => 'upsert_stock_item'], ['app']); ?>" data-toggle="lightbox" data-seamless="true" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil fa-lg'); ?></a>
                </td>
              </tr>
            <?php } ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="10">
                  <a href="<?php echo document::href_ilink(__APP__.'/stock_item_picker', ['js_callback' => 'upsert_stock_item']); ?>" class="btn btn-default" data-toggle="lightbox"><?php echo functions::draw_fonticon('fa-plus', 'style="color: #6c6;"'); ?> <?php echo language::translate('title_add_existing_stock_item', 'Add Existing Stock Item'); ?></a>
                  <a href="<?php echo document::href_ilink(__APP__.'/edit_stock_item', ['js_callback' => 'upsert_stock_item']); ?>" class="btn btn-default" data-toggle="lightbox" data-seamless="true" data-width="800"><?php echo functions::draw_fonticon('fa-plus', 'style="color: #6c6;"'); ?> <?php echo language::translate('title_create_new_stock_item', 'Create New Stock Item'); ?></a>
                </td>
              </tr>
            </tfoot>
          </table>


        </div>
<!--
        <div id="tab-stock" class="tab-pane">

          <div class="row">
            <div class="col-md-6">

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_delivery_status', 'Delivery Status'); ?></label>
                  <?php echo functions::form_select_delivery_status('delivery_status_id', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_sold_out_status', 'Sold Out Status'); ?></label>
                  <?php echo functions::form_select_sold_out_status('sold_out_status_id', true); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-4">
                  <label><?php echo language::translate('title_min_order_qty', 'Min. Order Qty'); ?></label>
                  <?php echo functions::form_input_decimal('quantity_min', true, 2, 0); ?>
                </div>

                <div class="form-group col-md-4">
                  <label><?php echo language::translate('title_max_order_quantity', 'Max. Order Qty'); ?></label>
                  <?php echo functions::form_input_decimal('quantity_max', true, 2, 0); ?>
                </div>

                <div class="form-group col-md-4">
                  <label><?php echo language::translate('title_quantity_step', 'Quantity Step'); ?></label>
                  <?php echo functions::form_input_decimal('quantity_step', true, 2, 0); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-7">
                  <label><?php echo language::translate('title_purchase_price', 'Purchase Price'); ?></label>
                  <div class="input-group">
                    <?php echo functions::form_input_decimal('purchase_price', true, 2, 'min="0"'); ?>
                    <?php echo functions::form_select_currency('purchase_price_currency_code', true); ?>
                  </div>
                </div>

                <div class="form-group col-md-5">
                  <label><?php echo language::translate('title_reserved_quantity', 'Reserved Quantity'); ?></label>
                  <div class="form-input text-end" readonly>
                    <?php echo !empty($product->data['id']) ? (float)$product->data['quantity_reserved'] : 'n/a'; ?>
                  </div>
                </div>
              </div>

              <h2 style="margin-top: 1em;"><?php echo language::translate('title_digital_item', 'Digital Item'); ?></h2>

              <div class="row">
                <div class="form-group col-md-9">
                  <label><?php echo language::translate('title_downloadable_file', 'Downloadable File'); ?></label>
                  <?php echo functions::form_input_file('file'); ?>
                  <?php if (!empty($product->data['file'])) { ?>
                  <div><?php echo functions::form_input_checkbox('delete_file', ['1', language::translate('text_delete', 'Delete') .' '. $product->data['filename']], true); ?></div>
                  <?php } ?>
                </div>

                <div class="form-group col-md-3">
                  <label><?php echo language::translate('title_downloads', 'Downloads'); ?></label>
                  <?php echo functions::form_input_number('downloads', true, 'readonly'); ?>
                </div>
              </div>

            </div>

            <div class="col-md-6">

              <h2 style="margin-top: 2em;"><?php echo language::translate('title_also_included_products', 'Also Included Products'); ?></h2>

              <table class="table table-striped table-dragable data-table">
                <thead>
                  <tr>
                    <th><?php echo language::translate('title_id', 'ID'); ?></th>
                    <th><?php echo language::translate('title_name', 'Name'); ?></th>
                    <th class="main"><?php echo language::translate('title_stock_option', 'Stock Option'); ?></th>
                    <th><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
                    <th></th>
                  </tr>
                </thead>

                <tbody>
                  <?php foreach ($_POST['chained_products'] as $key => $chained_product) { ?>
                  <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-end">
                      <a class="remove btn btn-default btn-sm" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times', 'style="color: #c33;"'); ?></a>
                    </td>
                  </tr>
                  <?php } ?>
                </tbody>

                <tfoot>
                  <td colspan="5">
                    <a href="<?php echo document::href_ilink(__APP__.'/product_picker', ['js_callback' => 'add_chained_product']); ?>" class="btn btn-default" data-toggle="lightbox"><?php echo functions::draw_fonticon('add'); ?> <?php echo language::translate('title_add_product', 'Add Product'); ?></a>
                  </td>
                </tfoot>
              </table>

            </div>
          </div>

          <h2 style="margin-top: 2em;"><?php echo language::translate('title_stock_options', 'Stock Options'); ?></h2>

          <div style="margin: 0 -2em -4em -2em;">
            <table id="stock-options" class="table table-striped table-dragable table-hover data-table">
              <thead>
                <tr>
                  <th colspan="2"><?php echo language::translate('title_item', 'Item'); ?></th>
                  <th style="width: 150px;"><?php echo language::translate('title_sku', 'SKU'); ?></th>
                  <th style="width: 100px;"><?php echo language::translate('title_weight', 'Weight'); ?></th>
                  <th style="width: 150px;"><?php echo language::translate('title_dimensions', 'Dimensions'); ?></th>
                  <th style="width: 125px;" class="text-center"><?php echo language::translate('title_price', 'Price'); ?></th>
                  <th style="width: 125px;" class="text-center"><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
                  <th style="width: 175px;" class="text-center"><?php echo language::translate('title_adjust', 'Adjust'); ?></th>
                  <th style="width: 175px;" class="text-center"><?php echo language::translate('title_backordered', 'Backordered'); ?></th>
                  <th style="width: 50px;"></th>
                  <th style="width: 50px;"></th>
                  <th style="width: 50px;"></th>
                </tr>
              </thead>

              <tbody>
                <?php foreach ($_POST['stock_options'] as $key => $stock_option) { ?>
                <tr>
                  <td>
                    <?php echo functions::draw_thumbnail('storage://images/'.$stock_option['image'], 64, 0, 'product'); ?>
                  </td>
                  <td>
                    <?php echo functions::form_input_hidden('stock_options['.$key.'][id]', true); ?>
                    <?php echo functions::form_input_hidden('stock_options['.$key.'][weight]', true); ?>
                    <?php echo functions::form_input_hidden('stock_options['.$key.'][weight_unit]', true); ?>
                    <?php echo functions::form_input_hidden('stock_options['.$key.'][length]', true); ?>
                    <?php echo functions::form_input_hidden('stock_options['.$key.'][width]', true); ?>
                    <?php echo functions::form_input_hidden('stock_options['.$key.'][height]', true); ?>
                    <?php echo functions::form_input_hidden('stock_options['.$key.'][length_unit]', true); ?>
                    <?php foreach (language::$languages as $language) echo functions::form_input_hidden('stock_options['.$key.'][name]['.$language['code'].']', true); ?>
                    <div class="dropdown">
                      <?php echo functions::form_regional_text('stock_options['.$key.'][name]['. language::$selected['code'] .']', language::$selected['code'], true, 'style="width: 125px;"'); ?>
                      <ul class="dropdown-menu" style="right:0;">
                        <?php foreach (array_diff(array_keys(language::$languages), [language::$selected['code']]) as $language_code) { ?>
                        <li><?php echo functions::form_regional_text('stock_options['.$key.'][name]['. $language_code .']', $language_code, true, 'style="width: 125px;"'); ?></li>
                        <?php } ?>
                      </ul>
                    </div>
                  </td>
                  <td data-field="sku">
                    <?php echo functions::form_input_text('sku', $_POST['stock_options'][$key]['sku']); ?>
                  </td>
                  <td>
                    <span data-field="weight"><?php echo (float)$_POST['stock_options'][$key]['weight']; ?></span> <span data-field="weight_unit"><?php echo (float)$_POST['stock_options'][$key]['weight_unit']; ?></span>
                  </td>
                  <td>
                    <span data-field="length"><?php echo (float)$_POST['stock_options'][$key]['length']; ?></span>
                    x <span data-field="width"><?php echo (float)$_POST['stock_options'][$key]['width']; ?></span>
                    x <span data-field="height"><?php echo (float)$_POST['stock_options'][$key]['height']; ?></span>
                    <span data-field="length_unit"><?php echo (float)$_POST['stock_options'][$key]['length_unit']; ?></span>
                  </td>
                  <td>
                    <div class="dropdown">
                      <?php echo functions::form_input_money('stock_options['.$key.'][price]['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), true, 'style="width: 125px;"'); ?>
                      <ul class="dropdown-menu" style="right:0;">
                        <?php foreach (array_diff(array_keys(currency::$currencies), [settings::get('store_currency_code')]) as $currency_code) { ?>
                        <li><?php echo functions::form_input_money('stock_options['.$key.'][price]['. $currency_code .']', $currency_code, true, 'style="width: 125px;"'); ?></li>
                        <?php } ?>
                      </ul>
                    </div>
                  </td>
                  <td>
                    <?php echo functions::form_input_decimal('stock_options['.$key.'][quantity]', true, 2, 'data-quantity="'. (isset($product->data['stock_options'][$key]) ? (float)$product->data['stock_options'][$key]['quantity'] : '0') .'"'); ?>
                  </td>
                  <td>
                    <div class="input-group">
                      <span class="input-group-text">&plusmn;</span>
                      <?php echo functions::form_input_decimal('stock_options['.$key.'][quantity_adjustment]', true); ?>
                    </div>
                  </td>
                  <td>
                    <div class="input-group">
                      <?php echo functions::form_button('stock_options['.$key.'][transfer_backordered]', functions::draw_fonticon('fa-arrow-left'), 'button'); ?>
                      <?php echo functions::form_input_decimal('stock_options['.$key.'][backordered]', true, 2, 'min="0"'); ?>
                    </div>
                  </td>
                  <td>
                    <?php echo functions::escape_js(functions::draw_fonticon('fa-arrows-v')); ?>
                  </td>
                  <td class="text-end">
                    <span class="remove btn btn-default btn-sm" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times', 'style="color: #c33;"'); ?></span>
                  </td>
                  <td class="text-end">
                    <span class="edit btn btn-default btn-sm" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></span>
                  </td>
                </tr>
                <?php } ?>
              </tbody>

              <tfoot>
                <tr>
                  <td colspan="12">
                    <span class="add btn btn-default"><?php echo functions::draw_fonticon('add'); ?> <?php echo language::translate('title_create_new_stock_option', 'Create New Stock Option'); ?></span>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
-->
      </div>

      <div class="card-action">
        <?php echo functions::form_button_predefined('save'); ?>
        <?php if (!empty($product->data['id'])) echo functions::form_button_predefined('delete'); ?>
        <?php echo functions::form_button_predefined('cancel'); ?>
      </div>

    <?php echo functions::form_end(); ?>
  </div>
</div>

<div id="modal-edit-stock-option" class="modal fade" style="max-width: 720px; display: none;">
  <div class="modal-header">
    <h2><?php echo language::translate('title_edit_stock_option', 'Edit Stock Option'); ?></h2>
  </div>

  <div class="modal-body">

    <div class="row">
      <div class="form-group col-md-8">
        <label><?php echo language::translate('title_name', 'Name'); ?></label>
        <div class="dropdown">
          <div class="input-group">
            <span class="input-group-text" style="font-family: monospace;"><?php echo settings::get('store_language_code'); ?></span>
            <?php echo functions::form_input_text('stock_option[name]['. settings::get('store_language_code') .']', ''); ?>
          </div>

          <ul class="dropdown-menu" style="right:0;">
            <?php foreach (array_diff(array_keys(language::$languages), [settings::get('store_language_code')]) as $language_code) { ?>
            <li>
              <div class="input-group">
                <span class="input-group-text"><?php echo $language_code; ?></span>
                <?php echo functions::form_input_text('stock_option[name]['. $language_code .']', $language_code, true, 'style="width: 125px;"'); ?>
              </div>
            </li>
            <?php } ?>
          </ul>
        </div>
      </div>

      <div class="form-group col-md-4">
        <label><?php echo language::translate('title_sku', 'SKU'); ?></label>
        <?php echo functions::form_input_text('stock_option[sku]', ''); ?>
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-3">
        <label><?php echo language::translate('title_price', 'Price'); ?></label>
        <div class="dropdown">
          <?php echo functions::form_input_money('stock_option[price]['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), true, 'style="width: 125px;"'); ?>
          <ul class="dropdown-menu" style="right:0;">
            <?php foreach (array_diff(array_keys(currency::$currencies), [settings::get('store_currency_code')]) as $currency_code) { ?>
            <li><?php echo functions::form_input_money('price['. $currency_code .']', $currency_code, true, 'style="width: 125px;"'); ?></li>
            <?php } ?>
          </ul>
        </div>
      </div>

      <div class="form-group col-md-3">
        <label><?php echo language::translate('title_quantity', 'Quantity'); ?></label>
        <?php echo functions::form_input_decimal('stock_option[quantity]', '', 'data-quantity="0"'); ?>
      </div>

      <div class="form-group col-md-3">
        <label><?php echo language::translate('title_quantity_adjustment', 'Quantity Adjustment'); ?></label>
        <?php echo functions::form_input_decimal('stock_option[quantity_adjustment]', '', 2); ?>
      </div>

      <div class="form-group col-md-3">
        <label><?php echo language::translate('title_backordered', 'Backordered'); ?></label>
        <div class="input-group">
          <?php echo functions::form_button('stock_option[transfer_backordered]', functions::draw_fonticon('fa-arrow-left'), 'button'); ?>
          <?php echo functions::form_input_decimal('stock_option[backordered]', true, 2, 'min="0"'); ?>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-4">
        <label><?php echo language::translate('title_weight', 'Weight'); ?></label>
        <div class="input-group">
          <?php echo functions::form_input_decimal('stock_option[weight]', true, 3, 'min="0"'); ?>
          <?php echo functions::form_select_weight_unit('stock_option[weight_unit]', true); ?>
        </div>
      </div>

      <div class="form-group col-md-8">
        <label><?php echo language::translate('title_dimensions', 'Dimensions'); ?></label>
        <div class="input-group">
          <?php echo functions::form_input_decimal('stock_option[length]', true, 3, 'min="0"'); ?>
          <span class="input-group-text">x</span>
          <?php echo functions::form_input_decimal('stock_option[width]', true, 3, 'min="0"'); ?>
          <span class="input-group-text">x</span>
          <?php echo functions::form_input_decimal('stock_option[height]', true, 3, 'min="0"'); ?>
          <?php echo functions::form_select_length_unit('stock_option[length_unit]', true); ?>
        </div>
      </div>
    </div>

    <div class="modal-action">
      <?php echo functions::form_button('ok', language::translate('title_ok', 'OK'), 'button', '', 'ok'); ?>
      <?php echo functions::form_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="$.featherlight.close();"', 'cancel'); ?>
    </div>
  </div>
</div>

<script>

// Initiate

  $('input[name="name[<?php echo settings::get('store_language_code'); ?>]"]').on('input', function(e){
    $('input[name="'+ $(this).attr('name') +'"]').not(this).val($(this).val());
  }).first().trigger('input');

  $('input[name^="name"]').on('input', function(e){
    let language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
    $('input[name="head_title['+language_code+']"]').attr('placeholder', $(this).val());
    $('input[name="h1_title['+language_code+']"]').attr('placeholder', $(this).val());
  });

  $('input[name^="short_description"]').on('input', function(e){
    let language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
    $('input[name="meta_description['+language_code+']"]').attr('placeholder', $(this).val());
  });

// Default Category

  $('[data-toggle="category-picker"]').change(function(){
    let default_category_id = $('select[name="default_category_id"] option:selected').val();

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
    let row = $(this).closest('.form-group');

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

    let output = [
      '<div class="image form-group">',
      '  <div class="thumbnail float-start">',
      '    <?php echo functions::draw_thumbnail('storage://images/no_image.png', 64, 0, 'product'); ?>',
      '  </div>',
      '  ',
      '  <div class="input-group">',
      '    <?php echo functions::form_input_file('new_images[]', 'accept="image/*"'); ?>',
      '    <div class="input-group-text">',
      '      <a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('move-up'); ?></a>',
      '      <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('move-down'); ?></a>',
      '      <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a>',
      '    </div>',
      '  </div>',
      '</div>'
    ].join('');

    $('#images .new-images').append(output);
    refreshMainImage();
  });

  $('#images').on('change', 'input[type="file"]', function(e) {
    let img = $(this).closest('.form-group').find('img');

    let oFReader = new FileReader();
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

    $('#tab-general .main-image').attr('src', '<?php echo document::href_rlink(functions::draw_thumbnail('storage://images/no_image.png', 360, 0, 'product')); ?>');
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

// Prices

  $('input[name="prices[<?php echo settings::get('store_currency_code'); ?>]"]').on('input', function() {
    $('input[name="prices[<?php echo settings::get('store_currency_code'); ?>]"]').not(this).val($(this).val());
  });

  function get_tax_rate() {
    switch ($('select[name=tax_class_id]').val()) {
<?php
  $tax_classes_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."tax_classes
    order by name asc;"
  )->each(function($tax_class){
    echo '      case "'. $tax_class['id'] . '": return '. tax::get_tax(100, $tax_class['id'], 'store') .';' . PHP_EOL;
  });
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
    let currency_code = $(this).attr('name').match(/^prices\[([A-Z]{3})\]$/)[1],
        decimals = get_currency_decimals(currency_code),
        gross_field = $('input[name="gross_prices['+ currency_code +']"]');

    let gross_price = parseFloat(Number($(this).val() * (1+(get_tax_rate()/100))).toFixed(decimals));

    if ($(this).val() == 0) {
      $(gross_field).val('');
    } else {
      $(gross_field).val(gross_price);
    }

    update_currency_prices();
  }).trigger('input');

// Update net price
  $('input[name^="gross_prices"]').on('input', function() {
    let currency_code = $(this).attr('name').match(/^gross_prices\[([A-Z]{3})\]$/)[1],
        decimals = get_currency_decimals(currency_code),
        net_field = $('input[name="prices['+ currency_code +']"]');

    let net_price = parseFloat(Number($(this).val() / (1+(get_tax_rate()/100))).toFixed(decimals));

    if ($(this).val() == 0) {
      $(net_field).val('');
    } else {
      $(net_field).val(net_price);
    }

    update_currency_prices();
  });

// Update price placeholders
  function update_currency_prices() {
    let store_currency_code = '<?php echo settings::get('store_currency_code'); ?>',
        currencies = ['<?php echo implode("','", array_keys(currency::$currencies)); ?>'],
        net_price = $('input[name^="prices"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val(),
        gross_price = $('input[name^="gross_prices"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val();

    if (!net_price) {
      net_price = $('input[name^="prices"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').attr('placeholder');
    }

    if (!gross_price) {
      gross_price = $('input[name^="gross_prices"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').attr('placeholder');
    }

    $.each(currencies, function(i,currency_code){
      if (currency_code == '<?php echo settings::get('store_currency_code'); ?>') return;

      let currency_decimals = get_currency_decimals(currency_code),
          currency_net_price = net_price / get_currency_value(currency_code);
          currency_gross_price = gross_price / get_currency_value(currency_code);

      currency_net_price = currency_net_price ? parseFloat(currency_net_price.toFixed(currency_decimals)) : '';
      currency_gross_price = currency_gross_price ? parseFloat(currency_gross_price.toFixed(currency_decimals)) : '';

      $('input[name="prices['+ currency_code +']"]').attr('placeholder', currency_net_price);
      $('input[name="gross_prices['+ currency_code +']"]').attr('placeholder', currency_gross_price);
    });
  }

  $('#price-incl-tax-tooltip').click(function(e) {
    e.preventDefault();
    alert('<?php echo str_replace(["\r", "\n", "'"], ["", "", "\\'"], language::translate('tooltip_field_price_incl_tax', 'This field helps you calculate net price based on the tax rates set for the store region. The prices stored in the database are always excluding tax.')); ?>');
  });

// Campaigns

  $('#table-campaigns').on('input', 'input[name^="campaigns"][name$="[percentage]"]', function() {
    let parent = $(this).closest('tr'),
      value = 0;

    <?php foreach (currency::$currencies as $currency) { ?>
    if ($('input[name^="prices"][name$="[<?php echo $currency['code']; ?>]"]').val() > 0) {
      value = parseFloat($('input[name="prices[<?php echo $currency['code']; ?>]"]').val() * (100 - $(this).val()) / 100).toFixed(<?php echo $currency['decimals']; ?>);
      $(parent).find('input[name$="[<?php echo $currency['code']; ?>]"]').val(value);
    } else {
      $(parent).find('input[name$="[<?php echo $currency['code']; ?>]"]').val("");
    }
    <?php } ?>

    <?php foreach (currency::$currencies as $currency) { ?>
    value = parseFloat($(parent).find('input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val() / <?php echo $currency['value']; ?>).toFixed(<?php echo $currency['decimals']; ?>);
    $(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').attr('placeholder', value);
    <?php } ?>
  });

  $('#table-campaigns').on('input', 'input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]', function() {
    let parent = $(this).closest('tr');
    let percentage = ($('input[name="prices[<?php echo settings::get('store_currency_code'); ?>]"]').val() - $(this).val()) / $('input[name="prices[<?php echo settings::get('store_currency_code'); ?>]"]').val() * 100;
    percentage = percentage.toFixed(2);
    $(parent).find('input[name$="[percentage]"]').val(percentage);

    <?php foreach (currency::$currencies as $currency) { ?>
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

  let new_campaign_index = 0;
  while ($(':input[name^="campaigns['+new_campaign_index+']"]').length) new_campaign_index++;

  $('#table-campaigns').on('click', '.add', function(e) {
    e.preventDefault();

    let output = [
      '<tr>',
      '  <td><?php echo functions::escape_js(functions::form_input_hidden('campaigns[new_campaign_index][id]', '') . functions::form_input_datetime('campaigns[new_campaign_index][start_date]', '')); ?></td>',
      '  <td><?php echo functions::escape_js(functions::form_input_datetime('campaigns[new_campaign_index][end_date]', '')); ?></td>',
      '  <td>',
      '    <div class="input-group">',
      '      <?php echo functions::form_input_decimal('campaigns[new_campaign_index][percentage]', '', 2, 'min="0"'); ?>',
      '      <span class="input-group-text">%</span>',
      '    </div>',
      '  </td>',
      '  <td><?php echo functions::escape_js(functions::form_input_money('campaigns[new_campaign_index]['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), '')); ?></td>',
        <?php foreach (array_diff(array_keys(currency::$currencies), [settings::get('store_currency_code')]) as $currency_code) { ?>
      '  <td><?php echo functions::escape_js(functions::form_input_money('campaigns[new_campaign_index]['. $currency_code .']', $currency_code, '')); ?></td>',
        <?php } ?>
      '  <td><a class="btn btn-default btn-sm remove" href="#" title="<?php echo functions::escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('remove')); ?></a></td>',
      '</tr>'
    ].join('')
    .replace(/new_campaign_index/g, 'new_' + new_campaign_index++);

    $('#table-campaigns tbody').append(output);
  });

// Attributes

  $('select[name="new_attribute[group_id]"]').change(function(){

    if ($(this).val() == '') {
      $('select[name="new_attribute[value_id]"]').html('').prop('disabled', true);
      $(':input[name="new_attribute[custom_value]"]').prop('disabled', true);
      return;
    }

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
        $('select[name="new_attribute[value_id]"]').html('');
        if (data) {
          $('select[name="new_attribute[value_id]"]').prop('disabled', false);
          $(':input[name="new_attribute[custom_value]"]').prop('disabled', false);
          $('select[name="new_attribute[value_id]"]').append('<option value="0">-- <?php echo language::translate('title_select', 'Select'); ?> --</option>');
          $.each(data, function(i, zone) {
            $('select[name="new_attribute[value_id]"]').append('<option value="'+ zone.id +'">'+ zone.name +'</option>');
          });
        } else {
          $('select[name="new_attribute[value_id]"]').prop('disabled', true);
          $(':input[name="new_attribute[custom_value]"]').prop('disabled', false);
        }
      },
    });
  }).trigger('change');

  let new_attribute_index = 0;
  while ($(':input[name^="attributes['+new_attribute_index+']"]').length) new_attribute_index++;

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

    let output = [
      '<tr>',
      '  <td>',
      '    <?php echo functions::escape_js(functions::form_input_hidden('attributes[new_attribute_index][id]', '')); ?>',
      '    <?php echo functions::escape_js(functions::form_input_hidden('attributes[new_attribute_index][group_id]', 'new_group_id')); ?>',
      '    <?php echo functions::escape_js(functions::form_input_hidden('attributes[new_attribute_index][group_name]', 'new_group_name')); ?>',
      '    <?php echo functions::escape_js(functions::form_input_hidden('attributes[new_attribute_index][value_id]', 'new_value_id')); ?>',
      '    <?php echo functions::escape_js(functions::form_input_hidden('attributes[new_attribute_index][value_name]', 'new_value_name')); ?>',
      '    <?php echo functions::escape_js(functions::form_input_hidden('attributes[new_attribute_index][custom_value]', 'new_custom_value')); ?>',
      '    new_group_name',
      '  </td>',
      '  <td>new_value_name</td>',
      '  <td>new_custom_value</td>',
      '  <td class="text-end"><a class="btn btn-default btn-sm remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>',
      '</tr>'
    ].join('')
    .replace(/new_attribute_index/g, 'new_' + new_attribute_index++)
    .replace(/new_group_id/g, $('select[name="new_attribute[group_id]"] option:selected').val())
    .replace(/new_group_name/g, $('select[name="new_attribute[group_id]"] option:selected').text())
    .replace(/new_value_id/g, $('select[name="new_attribute[value_id]"] option:selected').val())
    .replace(/new_custom_value/g, $('input[name="new_attribute[custom_value]"]').val());

    if ($('select[name="new_attribute[value_id]"] option:selected').val() != '0') {
      output = output.replace(/new_value_name/g, $('select[name="new_attribute[value_id]"] option:selected').text());
    } else {
      output = output.replace(/new_value_name/g, '');
    }

    $('#tab-attributes tbody').append(output);

    $('select[name="new_attribute[group_id]"]').val('').trigger('change');
  });

  $('#tab-attributes tbody').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
  });

// Quantity Unit

  $('select[name="quantity_unit_id"]').change(function(){
    if ($('option:selected', this).data('decimals') === undefined) return;

    let decimals = $('option:selected', this).data('decimals');

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

// Quantity and Adjustments

  $('body').on('input', ':input[name="quantity"], :input[name$="[quantity]"]', function(){
    let $quantity_adjustment_field = $(':input[name="' + $(this).attr('name').replace('quantity', 'quantity_adjustment') + '"]');
      quantity = parseFloat($(this).val()),
      quantity_adjustment = parseFloat($(this).val()) - parseFloat($(this).data('quantity')),
      decimals = parseInt($('select[name="quantity_unit_id"] option:selected').data('decimals'));

    $(':input[name="'+ $(this).attr('name')+'"]').not(this).val( quantity.toFixed(decimals) );
    $quantity_adjustment_field.val( quantity_adjustment.toFixed(decimals) );
  });

  $('body').on('input', ':input[name="quantity_adjustment"], :input[name$="[quantity_adjustment]"]', function(){
    let $quantity_field = $(':input[name="' + $(this).attr('name').replace('quantity_adjustment', 'quantity') + '"]');
      quantity = parseFloat($quantity_field.data('quantity') || 0),
      quantity_adjustment = parseFloat($(this).val() || 0),
      decimals = parseInt($('select[name="quantity_unit_id"] option:selected').data('decimals') || 0);

    $(':input[name="'+ $(this).attr('name') +'"]').not(this).val( quantity_adjustment.toFixed(decimals) );
    $quantity_field.val( (quantity + quantity_adjustment).toFixed(decimals) );
  });

// Transfer Backordered Quantity

  $('body').on('click', 'button[name*="transfer_backordered"]', function(){
    let $quantity_adjustment_field = $(':input[name="' + $(this).attr('name').replace('transfer_backordered', 'quantity_adjustment') +'"]'),
      $backordered_field = $(':input[name="' + $(this).attr('name').replace('transfer_backordered', 'backordered') +'"]'),
      quantity_adjustment = parseFloat($quantity_adjustment_field.val() || 0),
      backordered = parseFloat($backordered_field.val() || 0);

    $quantity_adjustment_field.val( quantity_adjustment + backordered ).trigger('input');
    $backordered_field.val('');
  });

// Quantity Unit

  $('select[name="quantity_unit_id"]').on('change', function(){
    let decimals = parseInt($('select[name="quantity_unit_id"] option:selected').data('decimals'));
    $('input[name$="[quantity]"], input[name$="[quantity_adjustment]"], input[name$="[backordered]"]').each(function(){
      if ($(this).val() != '') {
        $(this).val( parseFloat($(this).val()).toFixed(decimals) );
      }
    });
  }).trigger('change');

// Chained Products

  $('input[name="type"][value="variable"]').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();

    let total = 0;
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

  $('#tab-type tbody').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
  });

  window.upsert_variation_product = function(variation_product) {

    let output = [
      '<tr>',
      '  <?php echo functions::escape_js(functions::form_input_hidden('attributes[new_attribute_index][id]', '')); ?>',
      '  <?php echo functions::escape_js(functions::form_input_hidden('attributes[new_attribute_index][group_id]', 'new_group_id')); ?>',
      '  <?php echo functions::escape_js(functions::form_input_hidden('attributes[new_attribute_index][group_name]', 'new_group_name')); ?>',
      '  <?php echo functions::escape_js(functions::form_input_hidden('attributes[new_attribute_index][value_id]', 'new_value_id')); ?>',
      '  <?php echo functions::escape_js(functions::form_input_hidden('attributes[new_attribute_index][value_name]', 'new_value_name')); ?>',
      '  <?php echo functions::escape_js(functions::form_input_hidden('attributes[new_attribute_index][custom_value]', 'new_custom_value')); ?>',
      '  <td>new_group_name</td>',
      '  <td>new_value_name</td>',
      '  <td>new_custom_value</td>',
      '  <td class="text-end"><a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>',
      '</tr>'
    ].join('')
    .replace(/new_attribute_index/g, 'new_' + new_attribute_index++);

    let $output = $(output);

    $.each(Object.keys(variation_product), function(i, key){ // Iterate Object.keys() because jQuery.each() doesn't support a property named length
      let value = null;
      switch (key) {
        case 'id':
          key = 'chained_product_id';
          value = variation_product.id;
          break;
        case 'name':
          if ($.isPlainObject(variation_product.name)) {
            value = variation_product.name.<?php echo language::$selected['code']; ?>;
           } else {
            value = variation_product.name;
           }
          break;
        case 'quantity_adjustment':
          break;
        default:
          value = variation_product[key];
          break;
      }

      $output.find(':input[name$="['+ key +']"]').val(value);
      $output.find('.'+ key, output).text(value);
  });

    if (!$('input[name="type"][value="variable"] tbody input[name="chained_products[]"][value="'+ variation_product.id +'"]').length) {
      $('input[name="type"][value="variable"] tbody').append($output);
    }
  }
/*
// Stock Options

  $('#stock-options').on('click', '.remove', function(){
    $(this).closest('tr').remove();
  });

  $('#stock-options').on('click', '.edit', function(){

    $.featherlight('#modal-edit-stock-option');
    let modal = $('.featherlight.active'),
        row = $(this).closest('tr');

    $(modal).find('h2').text("<?php echo functions::escape_js(language::translate('title_edit_stock_option', 'Edit Stock Option')); ?>");
    $(modal).data('stock-option-id', row);

    $.each($(modal).find(':input'), function(i,field){
      let name = $(field).attr('name').replace(/\[(.*)\]$/, '$1');
      let value = $(row).find(':input[name$="'+name+'"]').val();
      $(modal).find(':input[name$="'+name+'"]').val(value);
    });
  });

  $('#stock-options').on('click', '.add', function(){
    $.featherlight('#modal-edit-stock-option');

    let modal = $('.featherlight.active'),
        row = $(this).closest('tr');

    $(modal).data('row', null);

    $(modal).find('h2').text("<?php echo functions::escape_js(language::translate('title_create_new_stock_option', 'Create New Stock Option')); ?>");

    $.each($(modal).find(':input'), function(i,field){
      $(this).val('');
    });
  });

  let new_stock_option_index = 0;
  while ($(':input[name^="stock_options['+new_stock_option_index+']"]').length) new_stock_option_index++;

  $('body').on('click', '#modal-edit-stock-option button[name="ok"]', function(){

    let modal = $(this).closest('.featherlight');

    let row = $(modal).data('row');

    if (!row) {
      let $output = $([
        '<tr>',
        '  <td>',
        '    <img class="thumbnail <?php echo settings::get('product_image_clipping'); ?>" />',
        '  </td>',
        '  <td>',
        '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_option_index][id]', '')); ?>',
        '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_option_index][stock_item_id]', '')); ?>',
        '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_option_index][weight]', '')); ?>',
        '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_option_index][weight_unit]', '')); ?>',
        '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_option_index][length]', '')); ?>',
        '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_option_index][width]', '')); ?>',
        '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_option_index][height]', '')); ?>',
        '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_option_index][length_unit]', '')); ?>',
        <?php foreach (language::$languages as $language) { ?>
        '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_option_index][name]['.$language['code'].']', '')); ?>',
        <?php } ?>
        '    <span data-field="name[<?php echo language::$selected['code']; ?>]"><?php echo functions::escape_js(fallback($_POST['stock_options']['new_stock_option_index']['name'][language::$selected['code']], '')); ?></span>',
        '  </td>',
        '  <td>',
        '    <?php echo functions::escape_js(functions::form_input_text('stock_options[new_stock_option_index][sku]', '')); ?>',
        '  </td>',
        '  <td class="text-end">',
        '    <span data-field="weight">0</span> <span data-field="weight_unit"></span>',
        '  </td>',
        '  <td class="text-end">',
        '    <span data-field="length">0</span> x <span data-field="width">0</span> x <span data-field="height">0</span> <span data-field="length_unit"></span>',
        '  </td>',
        '  <td>',
        '    <?php echo functions::escape_js(functions::form_input_money('stock_options[new_stock_option_index][price]['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), '', 'style="width: 125px;"')); ?>',
        '  </td>',
        '  <td><?php echo functions::escape_js(functions::form_input_decimal('stock_options[new_stock_option_index][quantity]', '', 2, 'data-quantity="0"')); ?></td>',
        '  <td>',
        '    <div class="input-group">',
        '      <span class="input-group-text">&plusmn;</span>',
        '      <?php echo functions::escape_js(functions::form_input_decimal('stock_options[new_stock_option_index][quantity_adjustment]', '')); ?>',
        '    </div>',
        '  </td>',
        '  <td>',
        '    <div class="input-group">',
        '      <?php echo functions::escape_js(functions::form_button('transfer', functions::draw_fonticon('fa-arrow-left'), 'button')); ?>',
        '      <?php echo functions::escape_js(functions::form_input_decimal('stock_options[new_stock_option_index][backordered]', '', 2, 'min="0"')); ?>',
        '    </div>',
        '  </td>',
        '  <td class="grabable">',
        '    <?php echo functions::escape_js(functions::draw_fonticon('fa-arrows-v')); ?>',
        '  </td>',
        '  <td class="text-end">',
        '    <span class="remove btn btn-default btn-sm" title="<?php echo functions::escape_js(language::translate('title_remove', 'Remove')); ?>"><?php echo functions::escape_js(functions::draw_fonticon('fa-times', 'style="color: #c33;"')); ?></span>',
        '  </td>',
        '  <td class="text-end">',
        '    <span class="edit btn btn-default btn-sm" title="<?php echo functions::escape_js(language::translate('title_edit', 'Edit')); ?>"><?php echo functions::escape_js(functions::draw_fonticon('fa-pencil')); ?></span>',
        '  </td>',
        '</tr>'
      ].join('\n')
      .replace(/new_stock_option_index/, 'new_' + new_stock_option_index++))
      .appendTo('#stock-options tbody:last');

      row = $('#stock-options tbody:last tr:last');
    }

    $.each($(modal).find(':input'), function(i,element){
      let field = $(element).attr('name').replace(/^stock_option\[(.*?)\]/, '$1');
      let value = $(element).val();

      $(row).find(':input[name$="['+field+']"]').val(value);
      $(row).find('[data-field="'+ field +'"]').text(value);
    });

    $.featherlight.close();
  });

  $('body').on('focus', 'input[name^="stock_option[name]"]', function(e) {
    $(this).closest('.dropdown').addClass('open');
  });

  $('body').on('focus', 'input[name^="stock_option[price]"]', function(e) {
    $(this).closest('.dropdown').addClass('open');
  });
  */

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

    let output = [
      '<tr data-stock-item-id="'+ stock_item.id +'">',
      '  <td class="grabable">',
      '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][id]', '')); ?>',
      '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][stock_option_id]', '')); ?>',
    '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][sku]', '')); ?>',
    '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][name]', '')); ?>',
      '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][weight]', '')); ?>',
      '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][weight_unit]', '')); ?>',
      '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][length]', '')); ?>',
      '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][width]', '')); ?>',
      '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][height]', '')); ?>',
      '    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][length_unit]', '')); ?>',
      '    <span class="name"></name>',
      '  </td>',
      '  <td>',
      '    <span class="sku"></span>',
      '  </td>',
      '  <td class="text-end">',
      '    <span class="weight"></span> <span class="weight_unit"></span>',
      '  </td>',
      '  <td class="text-end">',
      '    <span class="length"></span> x <span class="width"></span> x <span class="height"></span> <span class="length_unit"></span>',
      '  </td>',
      '  <td><?php echo functions::escape_js(functions::form_select('stock_options[new_stock_item_i][price_operator]', ['+', '*', '%', '='], '+')); ?></td>',
      '  <td>',
      '    <div class="dropdown">',
      '      <?php echo functions::escape_js(functions::form_input_money('stock_options[new_stock_item_i][price]['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), '', 'style="width: 125px;"')); ?>',
      '      <ul class="dropdown-menu">',
      <?php foreach (currency::$currencies as $currency) { ?>
      <?php if ($currency['code'] == settings::get('store_currency_code')) continue; ?>
      '        <li><?php echo functions::escape_js(functions::form_input_money('stock_options[new_stock_item_i][price]['. $currency['code'] .']', $currency['code'], '', 'style="width: 125px;"')); ?></li>',
      <?php } ?>
      '      </ul>',
      '    </div>',
      '  </td>',
      '  <td><?php echo functions::escape_js(functions::form_input_decimal('stock_options[new_stock_item_i][quantity]', '0', 2, 'data-quantity="new_stock_item_quantity"')); ?></td>',
      '  <td>',
      '    <div class="input-group">',
      '      <span class="input-group-text">&plusmn;</span>',
      '    <?php echo functions::escape_js(functions::form_input_decimal('stock_options[new_stock_item_i][quantity_adjustment]', '0')); ?>',
      '    </div>',
      '  </td>',
      '  <td>',
      '    <div class="input-group">',
      '      <?php echo functions::escape_js(functions::form_button('transfer', functions::draw_fonticon('fa-arrow-left'), 'button')); ?>',
      '      <?php echo functions::escape_js(functions::form_input_decimal('stock_options[new_stock_item_i][backordered]', '', 2, 'min="0"')); ?>',
      '    </div>',
      '  </td>',
      '  <td class="text-end">',
      '    <a class="remove btn btn-default btn-sm" href="#" title="<?php echo functions::escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #c33;"')); ?></a>',
      '  </td>',
      '  <td class="text-end">',
      '    <a class="edit btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_stock_item', ['stock_option_id' => 'new_stock_option_id', 'js_callback' => 'upsert_stock_item'], ['app']); ?>" data-toggle="lightbox" data-seamless="true" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil fa-lg'); ?></a>',
      '  </td>',
      '</tr>',
    ].join('\n');

    var new_stock_item_i = 1;
    while ($('input[name="stock_options[new_'+new_stock_item_i+']"]').length) new_stock_item_i++;
    output = output.replace(/new_stock_option_id/g, stock_item.id)
                   .replace(/new_stock_item_i/g, 'new_'+new_stock_item_i);

    var $output = $(output);

    $.each(Object.keys(stock_item), function(i, key){ // Iterate Object.keys() because jQuery.each() doesn't support a property named length
      switch (key) {

        case 'id':
          key = 'stock_option_id';
          var value = stock_item.id;
          break;

        case 'name':
          if ($.isPlainObject(stock_item.name)) {
            var value = stock_item.name.<?php echo language::$selected['code']; ?>;
          } else {
            var value = stock_item.name;
          }
          break;

        case 'quantity_adjustment':
          break;

        default:
          var value = stock_item[key];
          break;
      }

      $output.find(':input[name$="['+ key +']"]').val(value);
      $output.find('.'+ key, output).text(value);
    });

    if ($('#stock-options tbody tr[data-stock-item-id="'+ stock_item.id +'"]').length) {
      $('#stock-options tbody tr[data-stock-item-id="'+ stock_item.id +'"]').replaceWith($output);
    } else {
      $('#stock-options tbody').append($output);
    }
  }
</script>