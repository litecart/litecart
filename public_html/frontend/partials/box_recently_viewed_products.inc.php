<?php
  if (empty(session::$data['recently_viewed_products'])) return;

  if (!settings::get('box_recently_viewed_products_num_items')) return;

  functions::draw_lightbox();

// Get from catalog
  $recently_viewed_products = functions::catalog_products_query([
    'products' => array_reverse(array_column(session::$data['recently_viewed_products'], 'id'))
  ])->fetch_all();

  $product_ids = array_column(session::$data['recently_viewed_products'], 'id');

// Sort
  usort($recently_viewed_products, function ($a, $b) use ($product_ids) {
    $pos_a = array_search($a['id'], $product_ids);
    $pos_b = array_search($b['id'], $product_ids);
    return $pos_b - $pos_a;
  });

// Create list
  $box_recently_viewed_products = new ent_view(FS_DIR_TEMPLATE . 'partials/box_recently_viewed_products.inc.php');
  $box_recently_viewed_products->snippets['products'] = [];

  list($width, $height) = functions::image_scale_by_width(160, settings::get('product_image_ratio'));

  $count = 0;
  foreach ($recently_viewed_products as $product) {
    $box_recently_viewed_products->snippets['products'][] = [
      'id' => $product['id'],
      'name' => $product['name'],
      'image' => [
        'original' => 'storage://images/' . $product['image'],
        'thumbnail' => functions::image_thumbnail('storage://images/' . $product['image'], $width, $height, settings::get('product_image_trim')),
        'thumbnail_2x' => functions::image_thumbnail('storage://images/' . $product['image'], $width*2, $height*2, settings::get('product_image_trim')),
        'viewport' => [
          'width' => $width,
          'height' => $height,
          'ratio' => str_replace(':', '/', settings::get('product_image_ratio')),
          'clipping' => strtolower(settings::get('product_image_clipping')),
        ],
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
