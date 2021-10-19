<?php
  if (settings::get('catalog_only_mode')) return;

  $box_shopping_cart = new ent_view('partials/box_shopping_cart.inc.php');
  $box_shopping_cart->snippets = [
    'items' => [],
    'link' => document::ilink('shopping_cart'),
    'num_items' => cart::$total['items'],
    'cart_total' => null,
  ];

  foreach (cart::$items as $key => $item) {
    $item['thumbnail'] = functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $item['image'], 64, 64, 'FIT_USE_WHITESPACING');
    $box_shopping_cart->snippets['items'][$key] = $item;
  }

  if (!empty(customer::$data['display_prices_including_tax'])) {
    $box_shopping_cart->snippets['cart_total'] = currency::format(cart::$total['value'] + cart::$total['tax']);
  } else {
    $box_shopping_cart->snippets['cart_total'] = currency::format(cart::$total['value']);
  }

  echo $box_shopping_cart;
