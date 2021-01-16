<?php

  if (!empty($_GET['product_id'])) {
    $product = new ent_product($_GET['product_id']);
  } else {
    $product = new ent_product();
  }

  if (empty($_POST)) {
    foreach ($product->data as $key => $value) {
      $_POST[$key] = $value;
    }

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
      if (empty($_POST['stock_options'])) $_POST['stock_options'] = [];

      $_POST['keywords'] = preg_split('#\s*,\s*#', $_POST['keywords'], -1, PREG_SPLIT_NO_EMPTY);

      $fields = [
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

<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo !empty($product->data['id']) ? language::translate('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : language::translate('title_add_new_product', 'Add New Product'); ?>
  </div>

  <ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#tab-general"><?php echo language::translate('title_general', 'General'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-information"><?php echo language::translate('title_information', 'Information'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-attributes"><?php echo language::translate('title_attributes', 'Attributes'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-prices"><?php echo language::translate('title_prices', 'Prices'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-stock"><?php echo language::translate('title_stock', 'Stock'); ?></a></li>
  </ul>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('product_form', 'post', false, true); ?>

      <div class="tab-content">
        <div id="tab-general" class="tab-pane active" style="max-width: 960px;">

          <div class="row">
            <div class="col-md-4">

              <div class="form-group">
                <label><?php echo language::translate('title_status', 'Status'); ?></label>
                <?php echo functions::form_draw_toggle('status', 'e/d', isset($_POST['status']) ? $_POST['status'] : '0'); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_categories', 'Categories'); ?></label>
                <div id="categories" class="form-control">
<?php
  $catalog_tree_iterator = function($category_id=0, $depth=1, $count=0) use (&$catalog_tree_iterator) {

    $output = '';

    if (empty($category_id)) {
      $output .= '<div class="checkbox" id="category-id-0"><label>'. functions::form_draw_checkbox('categories[]', '0', (isset($_POST['categories']) && in_array('0', $_POST['categories'], true)) ? '0' : false, 'data-name="'. htmlspecialchars(language::translate('title_root', 'Root')) .'" data-priority="0"') .' '. functions::draw_fonticon('fa-folder fa-lg', 'title="'. language::translate('title_root', 'Root') .'" style="color: #cccc66;"') .' ['. language::translate('title_root', 'Root') .']</label></div>' . PHP_EOL;
    }

  // Output categories
    $categories_query = database::query(
      "select c.id, ci.name
      from ". DB_TABLE_PREFIX ."categories c
      left join ". DB_TABLE_PREFIX ."categories_info ci on (ci.category_id = c.id and ci.language_code = '". database::input(language::$selected['code']) ."')
      where c.parent_id = ". (int)$category_id ."
      order by c.priority asc, ci.name asc;"
    );

    while ($category = database::fetch($categories_query)) {
      $count++;

      $output .= '  <div class="checkbox"><label>'. functions::form_draw_checkbox('categories[]', $category['id'], true, 'data-name="'. htmlspecialchars($category['name']) .'" data-priority="'. $count .'"') .' '. functions::draw_fonticon('fa-folder fa-lg', 'style="color: #cccc66; margin-left: '. ($depth*1) .'em;"') .' '. $category['name'] .'</label></div>' . PHP_EOL;

      if (database::num_rows(database::query("select * from ". DB_TABLE_PREFIX ."categories where parent_id = ". (int)$category['id'] ." limit 1;")) > 0) {
        $output .= $catalog_tree_iterator($category['id'], $depth+1, $count);
      }
    }

    return $output;
  };

  echo $catalog_tree_iterator(0, 1, 0);
?>
                </div>
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
                 <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field('name['. $language_code .']', $language_code, true); ?>
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
                  <label class="input-group-addon" style="width: 100px;"><?php echo language::translate('title_mpn', 'MPN'); ?> <a href="https://en.wikipedia.org/wiki/Manufacturer_part_number" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
                  <?php echo functions::form_draw_text_field('mpn', true); ?>
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
                      <div class="input-group-addon">
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
                      <div class="input-group-addon">
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
                <?php echo functions::form_draw_regional_input_field('short_description['. $language_code .']', $language_code, true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_description', 'Description'); ?></label>
                <?php echo functions::form_draw_regional_wysiwyg_field('description['. $language_code .']', $language_code, true, 'style="height: 250px;"'); ?>
              </div>

              <div class="form-group">
                <label class="pull-right"><?php echo functions::form_draw_checkbox('autogenerate_technical_data', '1', true); ?> <?php echo language::translate('text_autogenerate_from_attributes', 'Generate from attributes'); ?></label>
                <label><?php echo language::translate('title_technical_data', 'Technical Data'); ?> <a class="technical-data-hint" href="#"><?php echo functions::draw_fonticon('fa-question-circle'); ?></a></label>
                <?php echo functions::form_draw_regional_textarea('technical_data['. $language_code .']', $language_code, true, 'style="height: 250px;"'); ?>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_head_title', 'Head Title'); ?></label>
                  <?php echo functions::form_draw_regional_input_field('head_title['. $language_code .']', $language_code, true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_meta_description', 'Meta Description'); ?></label>
                  <?php echo functions::form_draw_regional_input_field('meta_description['. $language_code .']', $language_code, true); ?>
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
                  <?php echo functions::form_draw_decimal_field('purchase_price', true, 2, 'min="0"'); ?>
                  <span class="input-group-addon">
                    <?php echo functions::form_draw_currencies_list('purchase_price_currency_code', true); ?>
                  </span>
                </div>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_recommended_price', 'Recommended Price'); ?> / MSRP</label>
                <?php echo functions::form_draw_currency_field('recommended_price', settings::get('store_currency_code'), true); ?>
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
                  <td><?php echo functions::form_draw_currency_field('prices['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), true, 'data-currency-price="" placeholder=""'); ?></td>
                <td><?php echo functions::form_draw_decimal_field('gross_prices['. settings::get('store_currency_code') .']', '', currency::$currencies[settings::get('store_currency_code')]['decimals'], 'min="0"'); ?></td>
                </tr>
<?php
  foreach (currency::$currencies as $currency) {
    if ($currency['code'] == settings::get('store_currency_code')) continue;
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
                  <td><?php echo settings::get('store_currency_code'); ?><br />
                    <?php echo functions::form_draw_currency_field('campaigns['.$key.']['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), true); ?>
                  </td>
<?php
  foreach (array_keys(currency::$currencies) as $currency_code) {
    if ($currency_code == settings::get('store_currency_code')) continue;
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
                  <td colspan="<?php echo 5 + count(currency::$currencies) - 1; ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?> <a class="add" href="#"><?php echo language::translate('text_add_campaign', 'Add Campaign'); ?></a></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
        <div id="tab-stock" class="tab-pane">
          <h2><?php echo language::translate('title_stock', 'Stock'); ?></h2>

          <div class="row" style="max-width: 960px;">
            <div class="form-group col-md-4">
              <label><?php echo language::translate('title_quantity_unit', 'Quantity Unit'); ?></label>
              <?php echo functions::form_draw_quantity_units_list('quantity_unit_id', true); ?>
            </div>

            <div class="form-group col-md-4">
              <label><?php echo language::translate('title_delivery_status', 'Delivery Status'); ?></label>
              <?php echo functions::form_draw_delivery_statuses_list('delivery_status_id', true); ?>
            </div>

            <div class="form-group col-md-4">
              <label><?php echo language::translate('title_sold_out_status', 'Sold Out Status'); ?></label>
              <?php echo functions::form_draw_sold_out_statuses_list('sold_out_status_id', true); ?>
            </div>
          </div>

<style>
.stock-option {
  padding: 1em;
  background: #f3f3f3;
  border-radius: 4px;
  margin: 1em 0;
  border: 1px solid #ccc;
}

.stock-option h3 {
  margin-top: 0;
}
</style>

          <div class="stock-options">

            <?php //if (!empty($_POST['stock_items'])) foreach ($_POST['stock_items'] as $i => $stock_item) { ?>
            <div class="stock-option box grey">

              <h3><?php echo language::translate('title_stock_option', 'Stock Option'); ?></h3>


              <div class="row">
                <div class="col-sm-4 col-md-2">

                  <div class="form-group">
                    <label><?php echo language::translate('title_image', 'Image'); ?></label>

                    <div class="thumbnail">
                    <a class="set-image" href="#">
<?php
  if (!empty($stock_option['image'])) {
    echo '<img class="main-image" src="'. document::href_link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_APP . 'images/' . $image['filename'], $product_image_width, $product_image_height, settings::get('product_image_clipping'))) .'" alt="" />';
  } else {
    echo '<img class="main-image" src="'. document::href_link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_APP . 'images/no_image.png', $product_image_width, $product_image_height, settings::get('product_image_clipping'))) .'" alt="" />';
  }
?>
                      </a>
                    </div>
                  </div>
                </div>
                <div class="col-sm-4 col-md-2">
                  <div class="form-group">
                    <label><?php echo language::translate('title_name', 'Name'); ?></label>
                    <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'stock_items['.$i.'][name]['. $language_code .']'); ?>
                  </div>
                </div>
              </div>

              <div class="table-responsive">
                <table class="table table-striped table-hover table-dragable data-table">
                  <thead>
                    <tr>
                      <th><?php echo language::translate('title_item', 'Item'); ?></th>
                      <th style="width: 200px;"><?php echo language::translate('title_sku', 'SKU'); ?></th>
                      <th style="width: 185px;"><?php echo language::translate('title_weight', 'Weight'); ?></th>
                      <th style="width: 400px;"><?php echo language::translate('title_dimensions', 'Dimensions'); ?></th>
                      <th class="text-center" style="width: 125px;"><?php echo language::translate('title_qty', 'Qty'); ?></th>
                  <th class="text-center" style="width: 125px;"><?php echo language::translate('title_adjust', 'Adjust'); ?></th>
                      <th style="width: 85px;">&nbsp;</th>
                    </tr>
                  </thead>

                  <tbody>
                    <?php if (!empty($stock_option['items'])) foreach ($stock_option['items'] as $j => $stock_item) { ?>
                    <tr>
                      <td><?php echo functions::form_draw_hidden_field('stock_options['.$key.'][items]['.$j.'][id]', true); ?><?php echo functions::form_draw_hidden_field('stock_options['.$key.'][items]['.$j.'][combination]', true); ?>
                        <?php echo functions::form_draw_hidden_field('stock_options['.$key.'][items]['.$j.'][name][items]['.$j.']['. language::$selected['name'] .']', true); ?>
                        <?php echo $_POST['stock_options']['items'][$j][$key]['items'][$j]['name']['items'][$j][language::$selected['code']]; ?></td>
                      <td><?php echo functions::form_draw_text_field('stock_options['.$key.'][items]['.$j.'][sku]', true); ?></td>
                      <td>
                        <div class="input-group">
                          <?php echo functions::form_draw_decimal_field('stock_options['.$key.'][items]['.$j.'][weight]', true, 3, 'min="0"'); ?>
                          <span class="input-group-addon">
                            <?php echo functions::form_draw_weight_classes_list('stock_options['.$key.'][items]['.$j.'][weight_class]', true, false, 'style="width: auto;"'); ?>
                          </span>
                        </div>
                      </td>
                      <td>
                        <div class="input-group">
                          <?php echo functions::form_draw_decimal_field('stock_options['.$key.'][items]['.$j.'][dim_x]', true, 3, 'min="0"'); ?>
                          <?php echo functions::form_draw_decimal_field('stock_options['.$key.'][items]['.$j.'][dim_y]', true, 3, 'min="0"'); ?>
                          <?php echo functions::form_draw_decimal_field('stock_options['.$key.'][items]['.$j.'][dim_z]', true, 3, 'min="0"'); ?>
                          <span class="input-group-addon">
                            <?php echo functions::form_draw_length_classes_list('stock_options['.$key.'][items]['.$j.'][dim_class]', true, false, 'style="width: auto;"'); ?>
                          </span>
                        </div>
                      </td>
                      <td><?php echo functions::form_draw_decimal_field('stock_options['.$key.'][items]['.$j.'][quantity]', true); ?></td>
                      <td><?php echo functions::form_draw_decimal_field('options_stock['.$key.'][quantity_adjustment]', true); ?></td>
                      <td class="text-right">
                        <a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"'); ?></a>
                        <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"'); ?></a>
                        <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a>
                      </td>
                    </tr>
                    <?php } ?>
                  </tbody>

                  <tfoot>
                    <td colspan="6">
                      <a class="btn btn-default" href="#modal-stock-item" data-toggle="lightbox"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?> <?php echo language::translate('title_add_stock_item', 'Add Stock Item'); ?></a>
                    </td>
                  </tfoot>
                </table>
              </div>

            </div>
            <?php //} ?>
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
                      <span class="input-group-addon">
                        <?php echo functions::form_draw_weight_classes_list('weight_class', true, false, 'style="width: auto;"'); ?>
                      </span>
                    </div>
                  </td>
                  <td>
                    <div class="input-group">
                      <?php echo functions::form_draw_decimal_field('dim_x', true, 4, 0); ?>
                      <?php echo functions::form_draw_decimal_field('dim_y', true, 4, 0); ?>
                      <?php echo functions::form_draw_decimal_field('dim_z', true, 4, 0); ?>
                      <span class="input-group-addon">
                        <?php echo functions::form_draw_length_classes_list('dim_class', true, false, 'style="width: auto;"'); ?>
                      </span>
                    </div>
                  </td>
                  <td><?php echo functions::form_draw_decimal_field('quantity', true, 2, null, null, 'data-quantity="'. (float)$product->data['quantity'] .'"' . (!empty($_POST['options_stock']) ? ' readonly' : '')); ?></td>
                  <td>
                    <div class="input-group">
                      <span class="input-group-addon">&plusmn;</span>
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
                      <span class="input-group-addon">
                        <?php echo functions::form_draw_weight_classes_list('options_stock['.$key.'][weight_class]', true, false, 'style="width: auto;"'); ?>
                      </span>
                    </div>
                  </td>
                  <td>
                    <div class="input-group">
                      <?php echo functions::form_draw_decimal_field('options_stock['.$key.'][dim_x]', true, 4, 0); ?>
                      <?php echo functions::form_draw_decimal_field('options_stock['.$key.'][dim_y]', true, 4, 0); ?>
                      <?php echo functions::form_draw_decimal_field('options_stock['.$key.'][dim_z]', true, 4, 0); ?>
                      <span class="input-group-addon">
                        <?php echo functions::form_draw_length_classes_list('options_stock['.$key.'][dim_class]', true, false, 'style="width: auto;"'); ?>
                      </span>
                    </div>
                  </td>
                  <td><?php echo functions::form_draw_decimal_field('options_stock['.$key.'][quantity]', true, 2, null, null, 'data-quantity="'. (isset($product->data['options_stock'][$key]['quantity']) ? (float)$product->data['options_stock'][$key]['quantity'] : '0') .'"'); ?></td>
                  <td><?php echo functions::form_draw_decimal_field('options_stock['.$key.'][quantity_adjustment]', true); ?></td>
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
                  <td colspan="7"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?> <a href="#" data-toggle="lightbox" data-target="#new-stock-option"><?php echo language::translate('title_add_stock_option', 'Add Stock Option'); ?></a></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <div class="panel-action btn-group">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        <?php echo (isset($product->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<script>

// Initiate

  $('input[name^="name"]').bind('input propertyChange', function(e){
    var language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
    $('input[name="head_title['+language_code+']"]').attr('placeholder', $(this).val());
    $('input[name="h1_title['+language_code+']"]').attr('placeholder', $(this).val());
  }).trigger('input');

  $('input[name^="short_description"]').bind('input propertyChange', function(e){
    var language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
    $('input[name="meta_description['+language_code+']"]').attr('placeholder', $(this).val());
  }).trigger('input');

// Default Category

  $('input[name="categories[]"]').change(function() {
    if ($(this).is(':checked')) {
      if ($(this).val() == '<?php echo $product->data['default_category_id']; ?>') {
        $('select[name="default_category_id"]').append('<option value="'+ $(this).val() +'" selected="selected">'+ $(this).data('name') +'</option>');
      } else {
        $('select[name="default_category_id"]').append('<option value="'+ $(this).val() +'">'+ $(this).data('name') +'</option>');
      }
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
               + '    <div class="input-group-addon">'
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
               + '  <td><?php echo functions::general_escape_js(language::translate('title_start_date', 'Start Date')); ?><br />'
               + '    <?php echo functions::general_escape_js(functions::form_draw_hidden_field('campaigns[new_campaign_i][id]', '') . functions::form_draw_datetime_field('campaigns[new_campaign_i][start_date]', '')); ?>'
               + '  </td>'
               + '  <td><?php echo functions::general_escape_js(language::translate('title_end_date', 'End Date')); ?><br />'
               + '    <?php echo functions::general_escape_js(functions::form_draw_datetime_field('campaigns[new_campaign_i][end_date]', '')); ?>'
               + '  </td>'
               + '  <td>- %<br />'
               + '    <?php echo functions::general_escape_js(functions::form_draw_decimal_field('campaigns[new_campaign_i][percentage]', '', 2, 'min="0"')); ?>'
               + '  </td>'
               + '  <td><?php echo functions::general_escape_js(settings::get('store_currency_code')); ?><br />'
               + '    <?php echo functions::general_escape_js(functions::form_draw_currency_field('campaigns[new_campaign_i]['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), '')); ?>'
               + '  </td>'
<?php
  foreach (array_keys(currency::$currencies) as $currency_code) {
    if ($currency_code == settings::get('store_currency_code')) continue;
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

    var value = parseFloat($('input[name="quantity"]').val()).toFixed(decimals);
    $('input[name="quantity"]').val(value);

    $('input[name^="stock_option"][name$="[quantity]"]').each(function(){
      var value = parseFloat($(this).val()).toFixed(decimals);
      $(this).val(value);
    });

    $('input[name^="option_stock"][name$="[quantity_adjustment]"]').each(function(){
      var value = parseFloat($(this).val()).toFixed(decimals);
      $(this).val(value);
    });
  }).trigger('change');

// Stock

  $('#table-stock').on('input', 'input[name="quantity"]', function(){
    $('input[name="quantity_adjustment"]').val(parseFloat($(this).val()) - parseFloat($(this).data('quantity')));
  });

  $('#table-stock').on('input', 'input[name="quantity_adjustment"]', function(){
    $('input[name="quantity"]').val(parseFloat($('input[name="quantity"]').data('quantity')) + parseFloat($(this).val()));
  });

  $('#table-stock').on('input', 'input[name$="[quantity]"]', function(){
    var adjustment_field = $(this).closest('tr').find('input[name$="[quantity_adjustment]"]');
    $(adjustment_field).val(parseFloat($(this).val()) - parseFloat($(this).data('quantity')));

    $('input[name="quantity"]').val(0);
    $(this).closest('tbody').find('input[name$="[quantity]"]').each(function() {
      $('input[name="quantity"]').val( parseFloat($('input[name="quantity"]').val()) + parseFloat($(this).val() ));
    });

    $('input[name="quantity_adjustment"]').val(0);
    $(this).closest('tbody').find('input[name$="[quantity_adjustment]"]').each(function() {
      $('input[name="quantity_adjustment"]').val( parseFloat($('input[name="quantity_adjustment"]').val()) + parseFloat($(this).val()) );
    });
  });

  $('#table-stock').on('input', 'input[name$="[quantity_adjustment]"]', function(){
    var qty_field = $(this).closest('tr').find('input[name$="[quantity]"]');
    $(qty_field).val(parseFloat($(qty_field).data('quantity')) + parseFloat($(this).val()));

    $('input[name="quantity"]').val(0);
    $(this).closest('tbody').find('input[name$="[quantity]"]').each(function() {
      $('input[name="quantity"]').val( parseFloat($('input[name="quantity"]').val()) + parseFloat($(this).val()) );
    });

    $('input[name="quantity_adjustment"]').val(0);
    $(this).closest('tbody').find('input[name$="[quantity_adjustment]"]').each(function() {
      $('input[name="quantity_adjustment"]').val( parseFloat($('input[name="quantity_adjustment"]').val()) + parseFloat($(this).val()));
    });
  });

  $('#table-stock').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();

    var total = 0;
    $(this).closest('tbody').find('input[name$="[quantity]"]').each(function() {
      total += parseFloat($(this).val());
    });

    if (!$('input[name^="options_stock"][name$="[id]"]').length) {

      $('input[name="quantity"]').prop('readonly', false);
      $('input[name="quantity_adjustment"]').prop('readonly', false);
      $('input[name="quantity"]').val('');
      $('input[name="quantity_adjustment"]').val('');

    } else {

      $('input[name="quantity"]').val(0);
      $('input[name^="options_stock"][name$="[quantity]"]').each(function() {
        $('input[name="quantity"]').val(parseFloat($('input[name="quantity"]').val()) + parseFloat($(this).val()));
      });

      $('input[name="quantity_adjustment"]').val(0);
      $('input[name^="options_stock"][name$="[quantity_adjustment]"]').each(function() {
        console.log(parseFloat($('input[name="quantity_adjustment"]').val()), parseFloat($(this).val()));
        $('input[name="quantity_adjustment"]').val(parseFloat($('input[name="quantity_adjustment"]').val()) + parseFloat($(this).val()));
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

  var option_index = 2;
  $('body').on('click', '#new-stock-option .add', function(e) {
    e.preventDefault();
    var output = '<tr>'
               + '  <td><?php echo functions::general_escape_js(functions::form_draw_attribute_groups_list('new_option[option_index][group_id]', '')); ?></td>'
               + '  <td><?php echo functions::general_escape_js(functions::form_draw_select_field('new_option[option_index][value_id]', [['','']], '', 'disabled')); ?></td>'
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
    var modal = $(this).closest('#new-stock-option');
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
        $(modal).find('select[name="'+ valueField +'"]').html('');
        if ($(modal).find('select[name="'+ valueField +'"]').is(':disabled')) $(modal).find('select[name="'+ valueField +'"]').prop('disabled', false);
        if (data) {
          $.each(data, function(i, zone) {
            $(modal).find('select[name="'+ valueField +'"]').append('<option value="'+ zone.id +'">'+ zone.name +'</option>');
          });
        } else {
          $(modal).find('select[name="'+ valueField +'"]').prop('disabled', true);
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
  });

  var new_stock_option_i = 1;
  $('body').on('click', '#new-stock-option button[name="add_stock_option"]', function(e) {
    e.preventDefault();

    var modal = $(this).closest('#new-stock-option');
    var new_option_code = '';
    var new_option_name = '';
    var use_coma = false;

    $(modal).find('select[name^="new_option"][name$="[group_id]"]').each(function(i, groupElement) {
      var groupElement = $(modal).find(groupElement);
      var valueElement = $(modal).find('select[name="'+ $(groupElement).attr('name').replace(/group_id/g, 'value_id') +'"]');

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
               + '  <td><?php echo functions::general_escape_js(functions::form_draw_hidden_field('stock_options[new_stock_option_i][id]', '') . functions::form_draw_hidden_field('stock_options[new_stock_option_i][combination]', 'new_option_code') . functions::form_draw_hidden_field('stock_options[new_stock_option_i][name]['. language::$selected['code'] .']', 'new_option_name')); ?>new_option_name</td>'
               + '  <td><?php echo functions::general_escape_js(functions::form_draw_text_field('stock_options[new_stock_option_i][sku]', '')); ?></td>'
               + '  <td>'
               + '    <div class="input-group">'
               + '      <?php echo functions::general_escape_js(functions::form_draw_decimal_field('stock_options[new_stock_option_i][weight]', '0.00', 3, 'min="0"')); ?>'
               + '      <span class="input-group-addon">'
               + '        <?php echo functions::general_escape_js(functions::form_draw_weight_classes_list('stock_options[new_stock_option_i][weight_class]', '', false, 'style="width: auto;"')); ?>'
               + '      </span>'
               + '    </div>'
               + '  </td>'
               + '  <td>'
               + '    <div class="input-group">'
               + '      <?php echo functions::general_escape_js(functions::form_draw_decimal_field('stock_options[new_stock_option_i][dim_x]', '0.00', 3, 'min="0"')); ?>'
               + '      <?php echo functions::general_escape_js(functions::form_draw_decimal_field('stock_options[new_stock_option_i][dim_y]', '0.00', 3, 'min="0"')); ?>'
               + '      <?php echo functions::general_escape_js(functions::form_draw_decimal_field('stock_options[new_stock_option_i][dim_z]', '0.00', 3, 'min="0"')); ?>'
               + '      <span class="input-group-addon">'
               + '        <?php echo functions::general_escape_js(functions::form_draw_length_classes_list('stock_options[new_stock_option_i][dim_class]', '', false, 'style="width: auto;"')); ?>'
               + '      </span>'
               + '    </div>'
               + '  </td>'
               + '  <td><?php echo functions::general_escape_js(functions::form_draw_decimal_field('options_stock[new_option_stock_i][quantity]', '0', 2, null, null, 'data-quantity="0"')); ?></td>'
               + '  <td>'
               + '    <div class="input-group">'
               + '      <span class="input-group-addon">&plusmn;</span>'
               + '    <?php echo functions::general_escape_js(functions::form_draw_decimal_field('options_stock[new_option_stock_i][quantity_adjustment]', '0')); ?>'
               + '    </div>'
               + '  </td>'
               + '  <td class="text-right">'
               + '    <a class="move-up" href="#" title="<?php echo functions::general_escape_js(language::translate('text_move_up', 'Move up'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('move-up')); ?></a>'
               + '    <a class="move-down" href="#" title="<?php echo functions::general_escape_js(language::translate('text_move_down', 'Move down'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('move-down')); ?></a>'
               + '    <a class="remove" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('remove')); ?></a>'
               + '  </td>'
               + '</tr>';
    while ($('input[name="stock_options[new_'+new_stock_option_i+']"]').length) new_stock_option_i++;
    output = output.replace(/new_stock_option_i/g, 'new_' + new_stock_option_i);
    output = output.replace(/new_option_code/g, new_option_code);
    output = output.replace(/new_option_name/g, new_option_name);

    $('#table-stock').find('tbody').append(output);
    new_stock_option_i++;

    $('input[name="quantity"]').prop('readonly', true);
    $('input[name="quantity_adjustment"]').prop('readonly', true);

    if ($('input[name^="options_stock"][name$="[id]"]').length == 1) {
      $('input[name="quantity"]').val('');
      $('input[name="quantity_adjustment"]').val('');
    }

    $.featherlight.close();
  });
</script>
