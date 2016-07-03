<?php
  if (!empty($_GET['product_id'])) {
    $product = catalog::product($_GET['product_id']);
  }

  if (empty($_GET['category_id']) && empty($product->manufacturer)) {
    if (count($product->category_ids)) {
      $category_ids = array_values($product->category_ids);
      $_GET['category_id'] = array_shift($category_ids);
    }
  }

  if (empty($product->id)) {
    notices::add('errors', language::translate('error_410_gone', 'The requested file is no longer available'));
    http_response_code(410);
    header('Refresh: 0; url='. document::ilink(''));
    exit;
  }

  if ( empty($product->status)) {
    notices::add('errors', language::translate('error_404_not_found', 'The requested file could not be found'));
    http_response_code(404);
    header('Refresh: 0; url='. document::ilink(''));
    exit;
  }

  if ($product->date_valid_from > date('Y-m-d H:i:s')) {
    notices::add('errors', sprintf(language::translate('text_product_cannot_be_purchased_until_s', 'The product cannot be purchased until %s'), language::strftime(language::$selected['format_date'], strtotime($product->date_valid_from))));
  }

  if (substr($product->date_valid_to, 0, 10) != '0000-00-00' && substr($product->date_valid_to, 0, 4) > '1971' && $product->date_valid_to < date('Y-m-d H:i:s')) {
    notices::add('errors', language::translate('text_product_can_no_longer_be_purchased', 'The product can no longer be purchased'));
  }

  database::query(
    "update ". DB_TABLE_PRODUCTS ."
    set views = views + 1
    where id = '". (int)$_GET['product_id'] ."'
    limit 1;"
  );

  if (!empty($_GET['category_id'])) {
    foreach (functions::catalog_category_trail($_GET['category_id']) as $category_id => $category_name) {
      document::$snippets['title'][] = $category_name;
    }
  } else if (!empty($product->manufacturer)) {
    document::$snippets['title'][] = $product->manufacturer['name'];
  }

  document::$snippets['title'][] = $product->head_title[language::$selected['code']] ? $product->head_title[language::$selected['code']] : $product->name[language::$selected['code']];
  document::$snippets['description'] = $product->meta_description[language::$selected['code']] ? $product->meta_description[language::$selected['code']] : strip_tags($product->short_description[language::$selected['code']]);
  document::$snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. document::href_ilink('product', array('product_id' => (int)$product->id), false) .'" />';
  document::$snippets['head_tags']['jquery-tabs'] = '<script src="'. WS_DIR_EXT .'jquery/jquery.tabs.js"></script>';
  document::$snippets['head_tags']['animate_from_to'] = '<script src="'. WS_DIR_EXT .'jquery/jquery.animate_from_to-1.0.min.js"></script>';

  if (!empty($product->image)) {
    document::$snippets['head_tags'][] = '<meta property="og:image" content="'. document::link(WS_DIR_IMAGES . $product->image) .'"/>';
  }

  if (!empty($_GET['category_id'])) {
    breadcrumbs::add(language::translate('title_categories', 'Categories'), document::ilink('categories'));
    foreach (functions::catalog_category_trail($_GET['category_id']) as $category_id => $category_name) {
      breadcrumbs::add($category_name, document::ilink('category', array('category_id' => (int)$category_id)));
    }
  } else if (!empty($product->manufacturer)) {
    breadcrumbs::add(language::translate('title_manufacturers', 'Manufacturers'), document::ilink('manufacturers'));
    breadcrumbs::add(functions::reference_get_manufacturer_name($product->manufacturer['id']), document::ilink('manufacturer', array('manufacturer_id' => $product->manufacturer['id'])));
  }
  breadcrumbs::add($product->name[language::$selected['code']]);

  functions::draw_fancybox("a.fancybox[data-fancybox-group='product']");

// Recently viewed products
  if (isset(session::$data['recently_viewed_products'][$product->id])) {
    unset(session::$data['recently_viewed_products'][$product->id]);
  }

  session::$data['recently_viewed_products'][$product->id] = array(
    'id' => $product->id,
    'name' => $product->name[language::$selected['code']],
    'image' => $product->image,
  );

  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'column_left.inc.php');

// Page
  $box_product = new view();

  list($width, $height) = functions::image_scale_by_width(320, settings::get('product_image_ratio'));

  $box_product->snippets = array(
    'product_id' => $product->id,
    'code' => $product->code,
    'sku' => $product->sku,
    'gtin' => $product->gtin,
    'name' => $product->name[language::$selected['code']],
    'short_description' => !empty($product->short_description[language::$selected['code']]) ? $product->short_description[language::$selected['code']] : '',
    'description' => !empty($product->description[language::$selected['code']]) ? $product->description[language::$selected['code']] : '<p><em style="opacity: 0.65;">'. language::translate('text_no_product_description', 'There is no description for this product yet.') . '</em></p>',
    'head_title' => !empty($product->head_title[language::$selected['code']]) ? $product->head_title[language::$selected['code']] : $product->name[language::$selected['code']],
    'meta_description' => !empty($product->meta_description[language::$selected['code']]) ? $product->meta_description[language::$selected['code']] : $product->short_description[language::$selected['code']],
    'keywords' => $product->keywords,
    'attributes' => !empty($product->attributes[language::$selected['code']]) ? preg_split('/\R+/', $product->attributes[language::$selected['code']]) : array(),
    'image' => array(
      'original' => !empty($product->images) ? WS_DIR_IMAGES . @array_shift(array_values($product->images)) : WS_DIR_IMAGES . 'no_image.png',
      'thumbnail' => functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . @array_shift(array_values($product->images)), $width, $height, settings::get('product_image_clipping')),
      'thumbnail_2x' => functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . @array_shift(array_values($product->images)), $width*2, $height*2, settings::get('product_image_clipping')),
      'viewport' => array(
        'width' => $width,
        'height' => $height,
      ),
    ),
    'sticker' => '',
    'extra_images' => array(),
    'manufacturer' => $product->manufacturer,
    'regular_price' => currency::format(tax::get_price($product->price, $product->tax_class_id)),
    'campaign_price' => !empty($product->campaign['price']) ? currency::format(tax::get_price($product->campaign['price'], $product->tax_class_id)) : null,
    'regular_price_value' => tax::get_price($product->price, $product->tax_class_id),
    'campaign_price_value' => !empty($product->campaign['price']) ? tax::get_price($product->campaign['price'], $product->tax_class_id) : 0,
    'tax_class_id' => $product->tax_class_id,
    'including_tax' => !empty(customer::$data['display_prices_including_tax']) ? true : false,
    'tax_rates' => array(),
    'quantity' => @round($product->quantity, $product->quantity_unit['decimals']),
    'quantity_unit_name' => $product->quantity_unit['name'][language::$selected['code']],
    'quantity_unit_decimals' => $product->quantity_unit['decimals'],
    'stock_status_value' => (settings::get('display_stock_count')) ? round($product->quantity, $product->quantity_unit['decimals']) .' '. $product->quantity_unit['name'][language::$selected['code']] : language::translate('title_in_stock', 'In Stock'),
    'delivery_status_value' => !empty($product->delivery_status['name'][language::$selected['code']]) ? $product->delivery_status['name'][language::$selected['code']] : '',
    'sold_out_status_value' => !empty($product->sold_out_status['name'][language::$selected['code']]) ? $product->sold_out_status['name'][language::$selected['code']] : '',
    'orderable' => $product->sold_out_status['orderable'],
    'cheapest_shipping' => null,
    'catalog_only_mode' => settings::get('catalog_only_mode'),
    'options' => array(),
  );

// Extra Images
  list($width, $height) = functions::image_scale_by_width(160, settings::get('product_image_ratio'));
  foreach (array_slice(array_values($product->images), 1) as $image) {
    $box_product->snippets['extra_images'][] = array(
      'original' => WS_DIR_IMAGES . $image,
      'thumbnail' => functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $image, $width, $height, settings::get('product_image_clipping')),
      'thumbnail_2x' => functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $image, $width*2, $height*2, settings::get('product_image_clipping')),
      'viewport' => array(
        'width' => $width,
        'height' => $height,
      ),
    );
  }

// Watermark Images
  if (settings::get('product_image_watermark')) {
    $box_product->snippets['image']['original'] = functions::image_process(FS_DIR_HTTP_ROOT . $box_product->snippets['image']['original'], array('watermark' => true));
    foreach (array_keys($box_product->snippets['extra_images']) as $key) {
      $box_product->snippets['extra_images'][$key]['original'] = functions::image_process(FS_DIR_HTTP_ROOT . $box_product->snippets['extra_images'][$key]['original'], array('watermark' => true));
    }
  }

// Stickers
  if (!empty($product->campaign['price'])) {
    $box_product->snippets['sticker'] = '<div class="sticker sale" title="'. language::translate('title_on_sale', 'On Sale') .'">'. language::translate('sticker_sale', 'Sale') .'</div>';
  } else if ($product->date_created > date('Y-m-d', strtotime('-'.settings::get('new_products_max_age')))) {
    $box_product->snippets['sticker'] = '<div class="sticker new" title="'. language::translate('title_new', 'New') .'">'. language::translate('sticker_new', 'New') .'</div>';
  }

// Tax
  $tax_rates = tax::get_tax_by_rate($product->campaign['price'] ? $product->campaign['price'] : $product->price, $product->tax_class_id);
  if (!empty($tax_rates)) {
    foreach ($tax_rates as $tax_rate) {
      $box_product->snippets['tax_rates'][] = currency::format($tax_rate['tax']) .' ('. $tax_rate['name'] .')';
    }
  }

// Cheapest shipping
  if (settings::get('display_cheapest_shipping')) {
    $shipping = new mod_shipping('local');
    $cheapest_shipping = $shipping->cheapest(
      array(
        $product->id => array(
          'quantity' => 1,
          'product_id' => $product->id,
          'price' => $product->campaign['price'] ? $product->campaign['price'] : $product->price,
          'tax_class_id' => $product->tax_class_id,
          'weight' => $product->weight,
          'weight_class' => $product->weight_class,
          'dim_x' => $product->dim_x,
          'dim_x' => $product->dim_x,
          'dim_y' => $product->dim_y,
          'dim_z' => $product->dim_z,
          'dim_class' => $product->dim_class,
        ),
      ),
      $product->campaign['price'] ? $product->campaign['price'] : $product->price,
      tax::get_tax($product->campaign['price'] ? $product->campaign['price'] : $product->price, $product->tax_class_id),
      currency::$selected['code'],
      customer::$data
    );
    if (!empty($cheapest_shipping)) {
      $box_product->snippets['cheapest_shipping'] = null;
      list($module_id, $option_id) = explode(':', $cheapest_shipping);
      if (empty($shipping->data['options'][$module_id]['options'][$option_id]['error']) && !empty($shipping->data['options'][$module_id]['options'][$option_id]['cost'])) {
        $shipping_cost = $shipping->data['options'][$module_id]['options'][$option_id]['cost'];
        $shipping_tax_class_id = $shipping->data['options'][$module_id]['options'][$option_id]['tax_class_id'];
        $box_product->snippets['cheapest_shipping'] = str_replace('%price', currency::format(tax::get_price($shipping_cost, $shipping_tax_class_id)), language::translate('text_cheapest_shipping_from_price', 'Cheapest shipping from %price'));
      }
    }
  }

// Options
  if (count($product->options) > 0) {
    foreach ($product->options as $group) {
      $values = '';
      switch ($group['function']) {

        case 'checkbox':
          $use_br = false;

          foreach (array_keys($group['values']) as $value_id) {
            if ($use_br) $values .= '<br />';

            $price_adjust_text = '';
            if ($group['values'][$value_id]['price_adjust']) {
              $price_adjust_text = currency::format(tax::get_price($group['values'][$value_id]['price_adjust'], $product->tax_class_id));
              if ($group['values'][$value_id]['price_adjust'] > 0) {
                $price_adjust_text = ' +'.$price_adjust_text;
              }
            }

            $values .= '<label>' . functions::form_draw_checkbox('options['.$group['name'][language::$selected['code']].'][]', $group['values'][$value_id]['name'][language::$selected['code']], true, !empty($group['required']) ? 'required="required"' : '') .' '. $group['values'][$value_id]['name'][language::$selected['code']] . $price_adjust_text . '</label>' . PHP_EOL;
            $use_br = true;
          }
          break;

        case 'input':

          $value_ids = array_keys($group['values']);
          $value_id = array_shift($value_ids);

          $price_adjust_text = '';
          if ($group['values'][$value_id]['price_adjust']) {
            $price_adjust_text = currency::format(tax::get_price($group['values'][$value_id]['price_adjust'], $product->tax_class_id));
            if ($group['values'][$value_id]['price_adjust'] > 0) {
              $price_adjust_text = ' +'.$price_adjust_text;
            }
          }

          $values .= functions::form_draw_text_field('options['.$group['name'][language::$selected['code']].']', isset($_POST['options'][$group['name'][language::$selected['code']]]) ? true : $group['values'][$value_id]['value'], !empty($group['required']) ? 'required="required"' : '') . $price_adjust_text . PHP_EOL;
          break;

        case 'radio':

          $use_br = false;
          foreach (array_keys($group['values']) as $value_id) {
            if ($use_br) $values .= '<br />';

            $price_adjust_text = '';
            if ($group['values'][$value_id]['price_adjust']) {
              $price_adjust_text = currency::format(tax::get_price($group['values'][$value_id]['price_adjust'], $product->tax_class_id));
              if ($group['values'][$value_id]['price_adjust'] > 0) {
                $price_adjust_text = ' +'.$price_adjust_text;
              }
            }

            $values .= '<label>' . functions::form_draw_radio_button('options['.$group['name'][language::$selected['code']].']', $group['values'][$value_id]['name'][language::$selected['code']], true, !empty($group['required']) ? 'required="required"' : '') .' '. $group['values'][$value_id]['name'][language::$selected['code']] . $price_adjust_text . '</label>' . PHP_EOL;
            $use_br = true;
          }
          break;

        case 'select':

          $options = array(array('-- '. language::translate('title_select', 'Select') .' --', ''));
          foreach (array_keys($group['values']) as $value_id) {

            $price_adjust_text = '';
            if ($group['values'][$value_id]['price_adjust']) {
              $price_adjust_text = currency::format(tax::get_price($group['values'][$value_id]['price_adjust'], $product->tax_class_id));
              if ($group['values'][$value_id]['price_adjust'] > 0) {
                $price_adjust_text = ' +'.$price_adjust_text;
              }
            }

            $options[] = array($group['values'][$value_id]['name'][language::$selected['code']] . $price_adjust_text, $group['values'][$value_id]['name'][language::$selected['code']]);
          }

          $values .= functions::form_draw_select_field('options['.$group['name'][language::$selected['code']].']', $options, true, false, !empty($group['required']) ? 'required="required"' : '');
          break;

        case 'textarea':

          $value_ids = array_keys($group['values']);
          $value_id = array_shift($value_ids);

          $price_adjust_text = '';
          if (!empty($group['values'][$value_id]['price_adjust'])) {
            $price_adjust_text = '';
            if ($group['values'][$value_id]['price_adjust'] > 0) {
              $price_adjust_text = ' <br />+'. currency::format(tax::get_price($group['values'][$value_id]['price_adjust'], $product->tax_class_id));
            }
          }

          $values .= functions::form_draw_textarea('options['.$group['name'][language::$selected['code']].']', isset($_POST['options'][$group['name'][language::$selected['code']]]) ? true : $group['values'][$value_id]['value'], !empty($group['required']) ? 'required="required"' : '') . $price_adjust_text. PHP_EOL;
          break;
      }

      $box_product->snippets['options'][] = array(
        'name' => $group['name'][language::$selected['code']],
        'description' => $group['description'][language::$selected['code']],
        'required' => !empty($group['required']) ? 1 : 0,
        'values' => $values,
      );
    }
  }

  echo $box_product->stitch('views/box_product');

  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_also_purchased_products.inc.php');

  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_similar_products.inc.php');
?>