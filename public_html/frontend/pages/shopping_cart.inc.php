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

  $_page = new ent_view('pages/shopping_cart.inc.php');

  $_page->snippets = [
    'items' => [],
    'subtotal' => [
      'value' => cart::$total['value'],
      'tax' => cart::$total['tax'],
    ],
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
      'stock_option_id' => $item['stock_option_id'],
      'name' => $item['name'],
      'sku' => $item['sku'],
      'image' => [
        'original' => 'images/' . (!empty($item['image']) ? $item['image'] : 'no_image.png'), 320, 320, 'FIT_USE_WHITESPACING',
        'thumbnail' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . (!empty($item['image']) ? $item['image'] : 'no_image.png'), 320, 320, 'FIT_USE_WHITESPACING'),
      ],
      'link' => document::ilink('product', ['product_id' => $item['product_id']]),
      'display_price' => customer::$data['display_prices_including_tax'] ? $item['price'] + $item['tax'] : $item['price'],
      'price' => $item['price'],
      'tax' => $item['tax'],
      'tax_class_id' => $item['tax_class_id'],
      'quantity' => (float)$item['quantity'],
      'quantity_unit' => $item['quantity_unit'],
      'error' => !empty($item['error']) ? $item['error'] : null,
    ];
  }

  echo $_page;
