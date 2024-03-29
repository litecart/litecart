<?php

  /*!
   * This file contains PHP logic that is separated from the HTML view.
   * Visual changes can be made to the file found in the template folder:
   *
   *   ~/frontend/templates/default/pages/product.inc.php
   */

  if (empty($_GET['product_id'])) {
    include 'app://frontend/pages/error_document.inc.php';
    return;
  }

  $product = reference::product($_GET['product_id']);

  if (empty($product->id)) {
    http_response_code(410);
    include 'app://frontend/pages/error_document.inc.php';
    return;
  }

  if (empty($product->status)) {
    http_response_code(404);
    include 'app://frontend/pages/error_document.inc.php';
    return;
  }

  if (!empty($product->date_valid_from) && $product->date_valid_from > date('Y-m-d H:i:s')) {
    notices::add('errors', sprintf(language::translate('text_product_cannot_be_purchased_until_s', 'The product cannot be purchased until %s'), language::strftime(language::$selected['format_date'], strtotime($product->date_valid_from))));
  }

  if (!empty($product->date_valid_to) && $product->date_valid_to < date('Y-m-d H:i:s')) {
    notices::add('errors', language::translate('text_product_can_no_longer_be_purchased', 'The product can no longer be purchased'));
  }

  if (empty($_GET['category_id']) && empty($_GET['manufacturer_id'])) {
    $_GET['category_id'] = $product->default_category_id;
  }

  $category = reference::category($_GET['category_id']);

  database::query(
    "update ". DB_TABLE_PREFIX ."products
    set views = views + 1
    where id = ". (int)$_GET['product_id'] ."
    limit 1;"
  );

  document::$title[] = $product->head_title ? $product->head_title : $product->name;
  document::$description = $product->meta_description ? $product->meta_description : strip_tags($product->short_description);

  document::$head_tags['canonical'] = '<link rel="canonical" href="'. document::href_ilink('product', ['product_id' => (int)$product->id], ['category_id']) .'">';

  if ($product->image) {
    document::$head_tags[] = '<meta property="og:image" content="'. document::href_rlink('storage://images/' . $product->image) .'">';
  }

  if (!empty($_GET['category_id'])) {
    breadcrumbs::add(language::translate('title_categories', 'Categories'), document::ilink('categories'));
    foreach (reference::category($_GET['category_id'])->path as $category_crumb) {
      document::$title[] = $category_crumb->name;
      breadcrumbs::add($category_crumb->name, document::ilink('category', ['category_id' => $category_crumb->id]));
    }
  } else if ($product->brand) {
    document::$title[] = $product->brand->name;
    breadcrumbs::add(language::translate('title_brands', 'Brands'), document::ilink('brands'));
    breadcrumbs::add($product->brand->name, document::ilink('brand', ['brand_id' => $product->brand->id]));
  }
  breadcrumbs::add($product->name);

  functions::draw_lightbox();

// Recently viewed products
  if (isset(session::$data['recently_viewed_products'][$product->id])) {
    unset(session::$data['recently_viewed_products'][$product->id]);
  }

  if (empty(session::$data['recently_viewed_products']) || !is_array(session::$data['recently_viewed_products'])) {
    session::$data['recently_viewed_products'] = [];
  }

  session::$data['recently_viewed_products'][$product->id] = [
    'id' => $product->id,
    'name' => $product->name,
    'image' => $product->image ? 'storage://images/'.$product->image : '',
  ];

// Page
  if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/product.ajax.inc.php');
  } else {
    $_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/product.inc.php');
  }

  $schema_json = [
    '@context' => 'http://schema.org/',
    '@type' => 'Product',
    'productID' => $product->id,
    'sku' => $product->sku,
    'gtin14' => $product->gtin,
    'mpn' => $product->mpn,
    'name' => $product->name,
    'image' => $product->image ? 'storage://images/' . $product->image : '',
    'description' => (!empty($product->description) && (trim(strip_tags($product->description)) != '')) ? $product->description : '',
    'brand' => [],
    'offers' => [
      '@type' => 'Offer',
      'priceCurrency' => currency::$selected['code'],
      'price' => currency::format_raw(tax::get_price($product->final_price, $product->tax_class_id)),
      'priceValidUntil' => (!empty($product->campaign['end_date']) && strtotime($product->campaign['end_date']) > time()) ? $product->campaign['end_date'] : null,
      'itemCondition' => 'https://schema.org/NewCondition', // Or RefurbishedCondition, DamagedCondition, UsedCondition
      'availability' => ($product->quantity_available > 0) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
      'url' => document::link(),
    ],
  ];

  $_page->snippets = [
    'product_id' => $product->id,
    'link' => document::ilink('product', [], true),
    'code' => $product->code,
    'sku' => $product->sku,
    'mpn' => $product->mpn,
    'gtin' => $product->gtin,
    'name' => $product->name,
    'short_description' => !empty($product->short_description) ? $product->short_description : '',
    'description' => !empty($product->description) ? $product->description : '',
    'technical_data' => preg_split('#\r\n?|\n#', $product->technical_data, -1, PREG_SPLIT_NO_EMPTY),
    'head_title' => !empty($product->head_title) ? $product->head_title : $product->name,
    'meta_description' => !empty($product->meta_description) ? $product->meta_description : $product->short_description,
    'attributes' => $product->attributes,
    'stock_options' => [],
    'keywords' => $product->keywords,
    'image' => $product->images ? 'storage://images/' . $product->image : '',
    'sticker' => '',
    'extra_images' => [],
    'main_category' => [],
    'category' => [],
    'brand' => [],
    'recommended_price' => tax::get_price($product->recommended_price, $product->tax_class_id),
    'regular_price' => tax::get_price($product->final_price, $product->tax_class_id),
    'campaign_price' => (isset($product->campaign['price']) && $product->campaign['price'] > 0) ? tax::get_price($product->campaign['price'], $product->tax_class_id) : null,
    'final_price' => tax::get_price($product->final_price, $product->tax_class_id),
    'tax_class_id' => $product->tax_class_id,
    'including_tax' => !empty(customer::$data['display_prices_including_tax']),
    'total_tax' => $product->tax,
    'tax_rates' => [],
    'quantity_min' => ($product->quantity_min > 0) ? $product->quantity_min : 1,
    'quantity_max' => ($product->quantity_max > 0) ? $product->quantity_max : null,
    'quantity_step' => ($product->quantity_step > 0) ? $product->quantity_step : null,
    'quantity_unit' => $product->quantity_unit,
    'quantity_available' => $product->quantity_available,
    'quantity_reserved' => $product->quantity_reserved,
    'stock_status' => null,
    'delivery_status' => !empty($product->delivery_status) ? $product->delivery_status : [],
    'sold_out_status' => !empty($product->sold_out_status) ? $product->sold_out_status : [],
    'orderable' => !empty($product->sold_out_status['orderable']),
    'cheapest_shipping_fee' => null,
  ];

// Extra Images
  foreach (array_slice(array_values($product->images), 1) as $image) {
    $_page->snippets['extra_images'][] = 'storage://images/' . $image;
  }

// Watermark Images
  if (settings::get('product_image_watermark') && $product->image) {
    $_page->snippets['image'] = functions::image_process($product->image, ['watermark' => true]);
    foreach ($_page->snippets['extra_images'] as $image) {
      $_page->snippets['extra_images'][$key] = functions::image_process($image, ['watermark' => true]);
    }
  }

// Stickers
  if (!empty($product->campaign['price']) && $product->campaign['price'] > 0) {
    $percentage = round(($product->price - $product->campaign['price']) / $product->price * 100);
    $_page->snippets['sticker'] = '<div class="sticker sale">'. language::translate('sticker_sale', 'Sale') .' '. $percentage .'%</div>';
  } else if ($product->date_created > date('Y-m-d', strtotime('-'.settings::get('new_products_max_age')))) {
    $_page->snippets['sticker'] = '<div class="sticker new">'. language::translate('sticker_new', 'New') .'</div>';
  }

// Main Category
  if (!empty($category->id)) {
    $_page->snippets['main_category'] = [
      'id' => $category->main_category->id,
      'name' => $category->main_category->name,
      'image' => $category->main_category->image ? 'storage://images/' . $category->main_category->image : '',
      'link' => document::ilink('category', ['category_id' => $category->main_category->id]),
    ];
  }

// Category
  if (!empty($category->id)) {
    $schema_json['category'] = $category->name;
    $_page->snippets['category'] = [
      'id' => $category->id,
      'name' => $category->name,
      'image' => $category->image ? 'storage://images/' . $category->image : '',
      'link' => document::ilink('category', ['category_id' => $category->id]),
    ];
  }

// Brand
  if (!empty($product->brand)) {
    $schema_json['brand']['name'] = $product->brand->name;
    $_page->snippets['brand'] = [
      'id' => $product->brand->id,
      'name' => $product->brand->name,
      'image' => $product->brand->image ? 'storage://images/' . $product->brand->image : '',
      'link' => document::ilink('brand', ['brand_id' => $product->brand->id]),
    ];
  }

// Stock Options
  foreach ($product->stock_options as $stock_option) {
    $stock_option['image'] = $stock_option['image'] ? 'storage://images/' . $stock_option['image'] : '';
    $_page->snippets['stock_options'][] = $stock_option;
  }

// Stock Status
  if (!empty($product->quantity_unit['name'])) {
    $_page->snippets['stock_status'] = settings::get('display_stock_count') ? language::number_format($product->quantity_available, $product->quantity_unit['decimals']) .' '. $product->quantity_unit['name'] : language::translate('title_in_stock', 'In Stock');
  } else {
    $_page->snippets['stock_status'] = settings::get('display_stock_count') ? language::number_format($product->quantity_available) : language::translate('title_in_stock', 'In Stock');
  }

// Cheapest shipping
  if (settings::get('display_cheapest_shipping')) {

    $tmp_order = (object)[
      'data' => [
        'items' => [
          [
            'quantity' => 1,
            'product_id' => $product->id,
            'price' => $product->final_price,
            'tax' => tax::get_tax($product->final_price, $product->tax_class_id),
            'tax_class_id' => $product->tax_class_id,
            'weight' => $product->weight,
            'weight_unit' => $product->weight_unit,
            'length' => $product->length,
            'width' => $product->width,
            'height' => $product->height,
            'length_unit' => $product->length_unit,
          ],
        ],
        'subtotal' => $product->final_price,
        'subtotal_tax' => $product->tax,
        'customer' => customer::$data,
        'currency_code' => currency::$selected['code'],
      ],
    ];

    $shipping = new mod_shipping();
    $cheapest_shipping = $shipping->cheapest($tmp_order);

    if (!empty($cheapest_shipping)) {
      $_page->snippets['cheapest_shipping_fee'] = tax::get_price($cheapest_shipping['fee'], $cheapest_shipping['tax_class_id']);
    }
  }

  document::$head_tags['schema_json'] = '<script type="application/ld+json">'. json_encode($schema_json, JSON_UNESCAPED_SLASHES) .'</script>';

  echo $_page;
