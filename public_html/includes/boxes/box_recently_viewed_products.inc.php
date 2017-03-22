<?php
  if (empty(session::$data['recently_viewed_products'])) return;

  if (settings::get('box_recently_viewed_products_num_items') == 0) return;

  functions::draw_lightbox();

// Get from catalog
  $product_ids = array_reverse(array_column(session::$data['recently_viewed_products'], 'id'));
  $recently_viewed_products_query = functions::catalog_products_query(array('products' => $product_ids));

  $recently_viewed_products = array();
  while($product = database::fetch($recently_viewed_products_query)) {
    $recently_viewed_products[$product['id']] = $product;
  }

// Sort
  usort($recently_viewed_products, function ($a, $b) use ($product_ids) {
    $pos_a = array_search($a['id'], $product_ids);
    $pos_b = array_search($b['id'], $product_ids);
    return $pos_a - $pos_b;
  });

// Create list
  $box_recently_viewed_products = new view();
  $box_recently_viewed_products->snippets['products'] = array();

  list($width, $height) = functions::image_scale_by_width(160, settings::get('product_image_ratio'));

  $count = 0;
  foreach($recently_viewed_products as $product) {
    $box_recently_viewed_products->snippets['products'][] = array(
      'id' => $product['id'],
      'name' => $product['name'],
      'thumbnail' => functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product['image'], $width, $height, settings::get('product_image_clipping'), settings::get('product_image_trim')),
      'link' => document::ilink('product', array('product_id' => $product['id'])),
    );
    if (++$count >= settings::get('box_recently_viewed_products_num_items')) break;
  }

// Unset rest
  $product_ids = array_column($box_recently_viewed_products->snippets['products'], 'id');
  foreach(array_keys(session::$data['recently_viewed_products']) as $key) {
    if (!in_array(session::$data['recently_viewed_products'][$key]['id'], $product_ids)) {
      unset(session::$data['recently_viewed_products'][$key]);
    }
  }

// Output
  echo $box_recently_viewed_products->stitch('views/box_recently_viewed_products');
