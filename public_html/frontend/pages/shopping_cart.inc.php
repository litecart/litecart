<?php
  header('X-Robots-Tag: noindex');

  if (settings::get('catalog_only_mode')) return;

  document::$snippets['title'][] = language::translate('title_shopping_cart', 'Shopping Cart');

  breadcrumbs::add(language::translate('title_shopping_cart', 'Shopping Cart'));

  functions::draw_lightbox();

  if (empty(cart::$items)) {
    echo '<div id="content">' . PHP_EOL
        . '  <p>'. language::translate('description_no_items_in_cart', 'There are no items in your cart.') .'</p>' . PHP_EOL
        . '  <div><a class="btn btn-default" href="'. document::href_ilink('') .'">'. language::translate('title_back', 'Back') .'</a></div>'
        . '</div>';
    return;
  }

  $_page = new ent_view();

  $_page->snippets = [
    'items' => [],
    'subtotal' => [
      'value' => cart::$total['value'],
      'tax' => cart::$total['tax'],
    ],
    'error' => false,
  ];

// Cart

  foreach (cart::$items as $key => $item) {
    $_page->snippets['items'][$key] = [
      'product_id' => $item['product_id'],
      'link' => document::ilink('product', ['product_id' => $item['product_id']]),
      'image' => [
        'original' => 'images/' . (!empty($item['image']) ? $item['image'] : 'no_image.png'), 320, 320, 'FIT_USE_WHITESPACING',
        'thumbnail' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . (!empty($item['image']) ? $item['image'] : 'no_image.png'), 320, 320, 'FIT_USE_WHITESPACING'),
      ],
      'name' => $item['name'],
      'sku' => $item['sku'],
      'options' => [],
      'display_price' => customer::$data['display_prices_including_tax'] ? $item['price'] + $item['tax'] : $item['price'],
      'price' => $item['price'],
      'tax' => $item['tax'],
      'tax_class_id' => $item['tax_class_id'],
      'quantity' => (float)$item['quantity'],
      'quantity_unit' => $item['quantity_unit'],
      'error' => $item['error'],
    ];
  }

  echo $_page->stitch('pages/shopping_cart.inc.php');
