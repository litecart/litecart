<?php

  header('X-Robots-Tag: noindex');

  unset(notices::$data['notices']['maintenance_mode']);

  if (empty(cart::$items)) {
    echo '<div id="content">' . PHP_EOL
        . '  <p>'. language::translate('description_no_items_in_cart', 'There are no items in your cart.') .'</p>' . PHP_EOL
        . '  <div><a class="btn btn-default" href="'. document::href_ilink('') .'">'. language::translate('title_back', 'Back') .'</a></div>'
        . '</div>';
    return;
  }

  $box_checkout_cart = new ent_view();

  $box_checkout_cart->snippets = array(
    'items' => array(),
    'subtotal' => cart::$total['value'],
    'subtotal_tax' => cart::$total['tax'],
  );

  foreach (cart::$items as $key => $item) {
    $box_checkout_cart->snippets['items'][$key] = array(
      'product_id' => $item['product_id'],
      'link' => document::ilink('product', array('product_id' => $item['product_id'])),
      'image' => array(
        'original' => 'images/' . (!empty($item['image']) ? $item['image'] : 'no_image.png'), 320, 320, 'FIT_USE_WHITESPACING',
        'thumbnail' => functions::image_thumbnail(FS_DIR_APP . 'images/' . (!empty($item['image']) ? $item['image'] : 'no_image.png'), 320, 320, 'FIT_USE_WHITESPACING'),
      ),
      'name' => $item['name'],
      'sku' => $item['sku'],
      'gtin' => $item['gtin'],
      'taric' => $item['taric'],
      'options' => array(),
      'display_price' => customer::$data['display_prices_including_tax'] ? $item['price'] + $item['tax'] : $item['price'],
      'price' => $item['price'],
      'tax' => $item['tax'],
      'tax_class_id' => $item['tax_class_id'],
      'quantity' => (float)$item['quantity'],
      'quantity_unit' => $item['quantity_unit'],
      'weight' => (float)$item['weight'],
      'weight_class' => $item['weight_class'],
      'dim_x' => (float)$item['dim_x'],
      'dim_y' => (float)$item['dim_y'],
      'dim_z' => (float)$item['dim_z'],
      'dim_class' => $item['dim_class'],
      'error' => $item['error'],
    );

    if (!empty($item['options'])) {
      foreach ($item['options'] as $k => $v) {
        $box_checkout_cart->snippets['items'][$key]['options'][] = $k .': '. $v;
      }
    }
  }

  echo $box_checkout_cart->stitch('views/box_checkout_cart');
