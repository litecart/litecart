<?php

  header('X-Robots-Tag: noindex');

  unset(notices::$data['notices']['maintenance_mode']);

  if (empty(cart::$items)) {
    include vmod::check(FS_DIR_TEMPLATE . 'views/box_checkout_no_items.inc.php');
    return;
  }

  $box_checkout_cart = new ent_view();

  $box_checkout_cart->snippets = [
    'items' => [],
    'subtotal' => cart::$total['value'],
    'subtotal_tax' => cart::$total['tax'],
  ];

  foreach (cart::$items as $key => $item) {
    $box_checkout_cart->snippets['items'][$key] = [
      'product_id' => $item['product_id'],
      'link' => document::ilink('product', ['product_id' => $item['product_id']]),
      'image' => [
        'original' => 'images/' . (!empty($item['image']) ? $item['image'] : 'no_image.png'), 320, 320, 'FIT_USE_WHITESPACING',
        'thumbnail' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . (!empty($item['image']) ? $item['image'] : 'no_image.png'), 320, 320, 'FIT_USE_WHITESPACING'),
      ],
      'name' => $item['name'],
      'sku' => $item['sku'],
      'gtin' => $item['gtin'],
      'taric' => $item['taric'],
      'options' => [],
      'display_price' => customer::$data['display_prices_including_tax'] ? $item['price'] + $item['tax'] : $item['price'],
      'price' => $item['price'],
      'tax' => $item['tax'],
      'tax_class_id' => $item['tax_class_id'],
      'quantity' => (float)$item['quantity'],
      'quantity_min' => $item['quantity_min'],
      'quantity_max' => $item['quantity_max'],
      'quantity_step' => $item['quantity_step'],
      'quantity_unit' => $item['quantity_unit'],
      'weight' => (float)$item['weight'],
      'weight_class' => $item['weight_class'],
      'dim_x' => (float)$item['dim_x'],
      'dim_y' => (float)$item['dim_y'],
      'dim_z' => (float)$item['dim_z'],
      'dim_class' => $item['dim_class'],
      'error' => $item['error'],
    ];

    if (!empty($item['options'])) {
      foreach ($item['options'] as $k => $v) {
        $box_checkout_cart->snippets['items'][$key]['options'][] = $k .': '. $v;
      }
    }
  }

  echo $box_checkout_cart->stitch('views/box_checkout_cart');
