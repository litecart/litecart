<?php
  if (settings::get('catalog_only_mode')) return;
  
  $box_cart = new view();
  $box_cart->snippets = array(
    'link' => document::ilink('checkout'),
    'num_items' => cart::$data['total']['items'],
    'cart_total' => !empty(customer::$data['display_prices_including_tax']) ? currency::format(cart::$data['total']['value'] + cart::$data['total']['tax']) : currency::format(cart::$data['total']['value']),
  );
  
  echo $box_cart->stitch('views/box_cart');
?>
