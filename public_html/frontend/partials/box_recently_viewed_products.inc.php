<?php
  if (empty(session::$data['recently_viewed_products'])) return;

  if (!settings::get('box_recently_viewed_products_num_items')) return;

  functions::draw_lightbox();

// Get from catalog
  $product_ids = array_reverse(array_column(session::$data['recently_viewed_products'], 'id'));
  $recently_viewed_products_query = functions::catalog_products_query(['products' => $product_ids]);

  $recently_viewed_products = [];
  while ($product = database::fetch($recently_viewed_products_query)) {
    $recently_viewed_products[$product['id']] = $product;
  }

// Sort
  usort($recently_viewed_products, function ($a, $b) use ($product_ids) {
    $pos_a = array_search($a['id'], $product_ids);
    $pos_b = array_search($b['id'], $product_ids);
    return $pos_a - $pos_b;
  });

// Create list
  $box_recently_viewed_products = new ent_view('partials/box_recently_viewed_products.inc.php');
  $box_recently_viewed_products->snippets['products'] = [];

  list($width, $height) = functions::image_scale_by_width(160, settings::get('product_image_ratio'));

  $count = 0;
  foreach ($recently_viewed_products as $product) {
    $box_recently_viewed_products->snippets['products'][] = [
      'id' => $product['id'],
      'name' => $product['name'],
      'image' => [
        'original' => 'images/' . $product['image'],
        'thumbnail_1x' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $product['image'], $width, $height, settings::get('product_image_clipping'), settings::get('product_image_trim')),
        'thumbnail_2x' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $product['image'], $width*2, $height*2, settings::get('product_image_clipping'), settings::get('product_image_trim')),
      ],

      'link' => document::ilink('product', ['product_id' => $product['id']]),
    ];
    if (++$count >= settings::get('box_recently_viewed_products_num_items')) break;
  }

// Unset rest
  $product_ids = array_column($box_recently_viewed_products->snippets['products'], 'id');
  foreach (array_keys(session::$data['recently_viewed_products']) as $key) {
    if (!in_array(session::$data['recently_viewed_products'][$key]['id'], $product_ids)) {
      unset(session::$data['recently_viewed_products'][$key]);
    }
  }

// Output
  echo $box_recently_viewed_products;
