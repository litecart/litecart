<?php
  if (empty(session::$data['recently_viewed_products'])) return;
  
  $box_recently_viewed_products = new view();
  
  $box_recently_viewed_products->snippets['products'] = array();
  
  $count = 0;
  foreach(array_reverse(session::$data['recently_viewed_products'], true) as $key => $array) {
    if (++$count <= 4) {
      $box_recently_viewed_products->snippets['products'][$key] = $array;
    } else {
      unset(session::$data['recently_viewed_products'][$key]);
    }
  }
  
  echo $box_recently_viewed_products->stitch('views/box_recently_viewed_products');
?>