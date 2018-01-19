<?php
  if (settings::get('catalog_only_mode')) return;

  $box_cart = new view();
  $box_cart->snippets = array(
    'items' => cart::$items,
    'link' => document::ilink('checkout'),
    'num_items' => cart::$total['items'],
  );

  if (!empty(customer::$data['display_prices_including_tax'])) {
    $box_cart->snippets['cart_total'] = currency::format(cart::$total['value'] + cart::$total['tax']);
  } else {
    $box_cart->snippets['cart_total'] = currency::format(cart::$total['value']);
  }

  echo $box_cart->stitch('views/box_cart');
