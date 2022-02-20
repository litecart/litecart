<?php
  if (settings::get('catalog_only_mode')) return;

  $box_shopping_cart = new ent_view(FS_DIR_TEMPLATE . 'partials/box_shopping_cart.inc.php');
  $box_shopping_cart->snippets = [
    'items' => [],
    'link' => document::ilink('shopping_cart'),
    'num_items' => count(cart::$items),
    'subtotal' => cart::$items,
  ];

  foreach (cart::$items as $key => $item) {
    $item['thumbnail'] = functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $item['image'], 64, 64, 'FIT_USE_WHITESPACING');
    $box_shopping_cart->snippets['items'][$key] = $item;
  }

  if (!empty(customer::$data['display_prices_including_tax'])) {
    $box_shopping_cart->snippets['subtotal'] = currency::format(cart::$cart->data['subtotal'] + cart::$cart->data['subtotal_tax']);
  } else {
    $box_shopping_cart->snippets['subtotal'] = currency::format(cart::$cart->data['subtotal']);
  }

  echo $box_shopping_cart;
