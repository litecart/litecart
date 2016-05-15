<?php
  if (empty(session::$data['recently_viewed_products'])) return;

  if (settings::get('box_recently_viewed_products_num_items') == 0) return;

  $box_recently_viewed_products = new view();

  $box_recently_viewed_products->snippets['products'] = array();

  list($width, $height) = functions::image_scale_by_width(160, settings::get('product_image_ratio'));

  $count = 0;
  foreach(array_reverse(session::$data['recently_viewed_products'], true) as $key => $current_product) {
    if (++$count <= settings::get('box_recently_viewed_products_num_items')) {
      $box_recently_viewed_products->snippets['products'][$key] = array(
        'id' => $current_product['id'],
        'name' => $current_product['name'],
        'thumbnail' => functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $current_product['image'], $width, $height, settings::get('product_image_clipping')),
        'link' => document::ilink('product', array('product_id' => $current_product['id'])),
      );
    } else {
      unset(session::$data['recently_viewed_products'][$key]);
    }
  }

  echo $box_recently_viewed_products->stitch('views/box_recently_viewed_products');
?>