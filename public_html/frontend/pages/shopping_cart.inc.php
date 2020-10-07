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

  $_page->snippets = array(
    'items' => array(),
    'subtotal' => [
      'value' => cart::$total['value'],
      'tax' => cart::$total['tax'],
    ],
    'error' => false,
  );

// Cart

  foreach (cart::$items as $key => $item) {
    $_page->snippets['items'][$key] = array(
      'product_id' => $item['product_id'],
      'link' => document::ilink('product', array('product_id' => $item['product_id'])),
      'thumbnail' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $item['image'], 320, 320, 'FIT_USE_WHITESPACING'),
      'name' => $item['name'],
      'sku' => $item['sku'],
      'options' => array(),
      'display_price' => customer::$data['display_prices_including_tax'] ? $item['price'] + $item['tax'] : $item['price'],
      'price' => $item['price'],
      'tax' => $item['tax'],
      'tax_class_id' => $item['tax_class_id'],
      'quantity' => (float)$item['quantity'],
      'quantity_unit' => $item['quantity_unit'],
      'error' => $item['error'],
    );
  }

  echo $_page->stitch('pages/shopping_cart');
