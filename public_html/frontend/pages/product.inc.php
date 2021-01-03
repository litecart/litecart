<?php

  if (empty($_GET['product_id'])) {
    include vmod::check(FS_DIR_APP . 'pages/error_document.inc.php');
    return;
  }

  $product = reference::product($_GET['product_id']);

  if (empty($product->id)) {
    http_response_code(410);
    include vmod::check(FS_DIR_APP . 'pages/error_document.inc.php');
    return;
  }

  if (empty($product->status)) {
    http_response_code(404);
    include vmod::check(FS_DIR_APP . 'pages/error_document.inc.php');
    return;
  }

  if ($product->date_valid_from > date('Y-m-d H:i:s')) {
    notices::add('errors', sprintf(language::translate('text_product_cannot_be_purchased_until_s', 'The product cannot be purchased until %s'), language::strftime(language::$selected['format_date'], strtotime($product->date_valid_from))));
  }

  if (strtotime($product->date_valid_to) > '1971' && $product->date_valid_to < date('Y-m-d H:i:s')) {
    notices::add('errors', language::translate('text_product_can_no_longer_be_purchased', 'The product can no longer be purchased'));
  }

  if (empty($_GET['category_id']) && empty($product->brand)) {
    if ($product->category_ids) {
      $category_ids = array_values($product->category_ids);
      $_GET['category_id'] = array_shift($category_ids);
    }
  }

  database::query(
    "update ". DB_TABLE_PREFIX ."products
    set views = views + 1
    where id = ". (int)$_GET['product_id'] ."
    limit 1;"
  );

  document::$snippets['title'][] = $product->head_title ? $product->head_title : $product->name;
  document::$snippets['description'] = $product->meta_description ? $product->meta_description : strip_tags($product->short_description);
  document::$snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. document::href_ilink('product', ['product_id' => (int)$product->id], ['category_id']) .'" />';

  if (!empty($product->image)) {
    document::$snippets['head_tags'][] = '<meta property="og:image" content="'. document::link(WS_DIR_STORAGE . 'images/' . $product->image) .'"/>';
  }

  if (!empty($_GET['category_id'])) {
    breadcrumbs::add(language::translate('title_categories', 'Categories'), document::ilink('categories'));
    foreach (reference::category($_GET['category_id'])->path as $category_crumb) {
      document::$snippets['title'][] = $category_crumb->name;
      breadcrumbs::add($category_crumb->name, document::ilink('category', ['category_id' => $category_crumb->id]));
    }
  } else if (!empty($product->brand)) {
    document::$snippets['title'][] = $product->brand->name;
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
    'image' => $product->image,
  ];

// Page
  $_page = new ent_view();

  $schema_json = [
    '@context' => 'http://schema.org/',
    '@type' => 'Product',
    'productID' => $product->id,
    'sku' => $product->sku,
    'gtin14' => $product->gtin,
    'mpn' => $product->mpn,
    'name' => $product->name,
    'image' => document::link(!empty($product->image) ? 'images/' . $product->image : 'images/no_image.png'),
    'description' => !empty($product->description) ? strip_tags($product->description) : '',
    'brand' => [],
    'offers' => [
      '@type' => 'Offer',
      'priceCurrency' => currency::$selected['code'],
      'price' => (isset($product->campaign['price']) && $product->campaign['price'] > 0) ? (float)currency::format_raw(tax::get_price($product->campaign['price'], $product->tax_class_id)) : (float)currency::format_raw(tax::get_price($product->price, $product->tax_class_id)),
      'priceValidUntil' => (!empty($product->campaign) && strtotime($product->campaign['end_date']) > time()) ? $product->campaign['end_date'] : null,
      'itemCondition' => 'https://schema.org/NewCondition', // Or RefurbishedCondition, DamagedCondition, UsedCondition
      'availability' => ($product->quantity > 0) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
      'url' => document::link(),
    ],
  ];

  list($width, $height) = functions::image_scale_by_width(480, settings::get('product_image_ratio'));

  $_page->snippets = [
    'product_id' => $product->id,
    'link' => document::ilink('product', [], true),
    'code' => $product->code,
    'sku' => $product->sku,
    'mpn' => $product->mpn,
    'gtin' => $product->gtin,
    'name' => $product->name,
    'short_description' => !empty($product->short_description) ? $product->short_description : '',
    'description' => !empty($product->description) ? $product->description : '<em style="opacity: 0.65;">'. language::translate('text_no_product_description', 'There is no description for this product yet.') . '</em>',
    'technical_data' => preg_split('#\r\n?|\n#', $product->technical_data, -1, PREG_SPLIT_NO_EMPTY),
    'head_title' => !empty($product->head_title) ? $product->head_title : $product->name,
    'meta_description' => !empty($product->meta_description) ? $product->meta_description : $product->short_description,
    'attributes' => $product->attributes,
    'keywords' => $product->keywords,
    'image' => [
      'original' => ltrim(!empty($product->images) ? 'images/' . $product->image : 'images/no_image.png', '/'),
      'thumbnail' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $product->image, $width, $height, settings::get('product_image_clipping'), settings::get('product_image_trim')),
      'thumbnail_2x' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $product->image, $width*2, $height*2, settings::get('product_image_clipping'), settings::get('product_image_trim')),
      'viewport' => [
        'width' => $width,
        'height' => $height,
      ],
    ],
    'sticker' => '',
    'extra_images' => [],
    'brand' => [],
    'recommended_price' => tax::get_price((float)$product->recommended_price, $product->tax_class_id),
    'regular_price' => tax::get_price($product->price, $product->tax_class_id),
    'campaign_price' => (isset($product->campaign['price']) && $product->campaign['price'] > 0) ? tax::get_price($product->campaign['price'], $product->tax_class_id) : null,
    'tax_class_id' => $product->tax_class_id,
    'including_tax' => !empty(customer::$data['display_prices_including_tax']) ? true : false,
    'total_tax' => tax::get_tax(!empty($product->campaign['price']) ? $product->campaign['price'] : $product->price, $product->tax_class_id),
    'tax_rates' => [],
    'quantity' => round((float)$product->quantity, $product->quantity_unit ? (int)$product->quantity_unit['decimals'] : 0),
    'quantity_unit' => $product->quantity_unit,
    'stock_status' => null,
    'delivery_status' => !empty($product->delivery_status) ? $product->delivery_status : [],
    'sold_out_status' => !empty($product->sold_out_status) ? $product->sold_out_status : [],
    'orderable' => !empty($product->sold_out_status['orderable']),
    'cheapest_shipping_fee' => null,
  ];

// Extra Images
  list($width, $height) = functions::image_scale_by_width(160, settings::get('product_image_ratio'));
  foreach (array_slice(array_values($product->images), 1) as $image) {
    $_page->snippets['extra_images'][] = [
      'original' => 'images/' . $image,
      'thumbnail' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $image, $width, $height, settings::get('product_image_clipping'), settings::get('product_image_trim')),
      'thumbnail_2x' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $image, $width*2, $height*2, settings::get('product_image_clipping'), settings::get('product_image_trim')),
      'viewport' => [
        'width' => $width,
        'height' => $height,
      ],
    ];
  }

// Watermark Images
  if (settings::get('product_image_watermark')) {
    $_page->snippets['image']['original'] = functions::image_process(FS_DIR_APP . $_page->snippets['image']['original'], ['watermark' => true]);
    foreach (array_keys($_page->snippets['extra_images']) as $key) {
      $_page->snippets['extra_images'][$key]['original'] = functions::image_process(FS_DIR_APP . $_page->snippets['extra_images'][$key]['original'], ['watermark' => true]);
    }
  }

// Stickers
  if (!empty($product->campaign['price'])) {
    $percentage = round(($product->price - $product->campaign['price']) / $product->price * 100);
    $_page->snippets['sticker'] = '<div class="sticker sale" title="'. language::translate('title_on_sale', 'On Sale') .'">'. language::translate('sticker_sale', 'Sale') .'<br />-'. $percentage .'%</div>';
  } else if ($product->date_created > date('Y-m-d', strtotime('-'.settings::get('new_products_max_age')))) {
    $_page->snippets['sticker'] = '<div class="sticker new" title="'. language::translate('title_new', 'New') .'">'. language::translate('sticker_new', 'New') .'</div>';
  }

// Brand
  if (!empty($product->brand)) {
    $schema_json['brand']['name'] = $product->brand->name;
    $_page->snippets['brand'] = [
      'id' => $product->brand->id,
      'name' => $product->brand->name,
      'image' => [],
      'link' => document::ilink('brand', ['brand_id' => $product->brand->id]),
    ];

    if (!empty($product->brand->image)) {
      $_page->snippets['brand']['image'] = [
        'original' => 'images/' . $product->brand->image,
        'thumbnail' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $product->brand->image, 200, 60),
        'thumbnail_2x' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $product->brand->image, 400, 120),
        'viewport' => [
          'width' => 200,
          'height' => 60,
        ],
      ];
    }
  }

// Stock Status
  if ($product->quantity_unit) {
    $_page->snippets['stock_status'] = settings::get('display_stock_count') ? language::number_format($product->quantity, $product->quantity_unit['decimals']) .' '. $product->quantity_unit['name'] : language::translate('title_in_stock', 'In Stock');
  } else {
    $_page->snippets['stock_status'] = settings::get('display_stock_count') ? language::number_format($product->quantity, 0) : language::translate('title_in_stock', 'In Stock');
  }

// Tax
  $tax_rates = tax::get_tax_by_rate(!empty($product->campaign['price']) ? $product->campaign['price'] : $product->price, $product->tax_class_id);
  if (!empty($tax_rates)) {
    foreach ($tax_rates as $tax_rate) {
      $_page->snippets['tax_rates'][] = currency::format($tax_rate['tax']) .' ('. $tax_rate['name'] .')';
    }
  }

// Cheapest shipping
  if (settings::get('display_cheapest_shipping')) {

    $shipping = new mod_shipping();

    $shipping_items = [
      [
        'quantity' => 1,
        'product_id' => $product->id,
        'price' => !empty($product->campaign['price']) ? $product->campaign['price'] : $product->price,
        'tax' => tax::get_tax(!empty($product->campaign['price']) ? $product->campaign['price'] : $product->price, $product->tax_class_id),
        'tax_class_id' => $product->tax_class_id,
        'weight' => $product->weight,
        'weight_class' => $product->weight_class,
        'dim_x' => $product->dim_x,
        'dim_y' => $product->dim_y,
        'dim_z' => $product->dim_z,
        'dim_class' => $product->dim_class,
      ],
    ];

    $cheapest_shipping = $shipping->cheapest($shipping_items, currency::$selected['code'], customer::$data);

    if (!empty($cheapest_shipping)) {
      $_page->snippets['cheapest_shipping_fee'] = tax::get_price($cheapest_shipping['cost'], $cheapest_shipping['tax_class_id']);
    }
  }

  if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    echo $_page->stitch('pages/product.ajax');
  } else {
    echo $_page->stitch('pages/product');
  }

  document::$snippets['head_tags']['schema_json'] = '<script type="application/ld+json">'. json_encode($schema_json, JSON_UNESCAPED_SLASHES) .'</script>';
