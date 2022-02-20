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

  $_page = new ent_view(FS_DIR_TEMPLATE . 'pages/shopping_cart.inc.php');

  $_page->snippets = [
    'items' => [],
    'subtotal' => cart::$cart->data['subtotal'],
    'subtotal_tax' => cart::$cart->data['subtotal_tax'],
    'display_prices_including_tax' => cart::$cart->data['display_prices_including_tax'],
    'error' => false,
  ];

  foreach (currency::$currencies as $currency) {
    if (!empty(user::$data['id']) || $currency['status'] == 1) $_page->snippets['currencies'][] = $currency;
  }

  foreach (language::$languages as $language) {
    if (!empty(user::$data['id']) || $language['status'] == 1) $_page->snippets['languages'][] = $language;
  }

// Cart

  foreach (cart::$items as $key => $item) {
    $_page->snippets['items'][$key] = [
      'product_id' => $item['product_id'],
      'stock_item_id' => $item['stock_item_id'],
      'name' => $item['name'],
      'sku' => $item['sku'],
      'image' => [
        'original' => 'images/' . fallback($item['image'], 'no_image.png'), 320, 320, 'FIT_USE_WHITESPACING',
        'thumbnail' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . fallback($item['image'], 'no_image.png'), 320, 320, 'FIT_USE_WHITESPACING'),
      ],
      'link' => document::ilink('product', ['product_id' => $item['product_id']]),
      'display_price' => customer::$data['display_prices_including_tax'] ? $item['price'] + $item['tax'] : $item['price'],
      'price' => $item['price'],
      'final_price' => $item['final_price'],
      'tax' => $item['tax'],
      'tax_class_id' => $item['tax_class_id'],
      'quantity' => (float)$item['quantity'],
      'quantity_unit_name' => $item['quantity_unit_name'],
      'quantity_min' => $item['quantity_min'],
      'quantity_max' => $item['quantity_max'],
      'quantity_step' => $item['quantity_step'],
      'error' => fallback($item['error']),
    ];
  }

  echo $_page;
