<?php
  if (!settings::get('box_popular_products_num_items')) return;

  functions::draw_lightbox();

  $box_popular_products_cache_token = cache::token('box_popular_products', ['language', 'prices'], 'file');
  if (cache::capture($box_popular_products_cache_token)) {

    $products = functions::catalog_products_query([
      'sort' => 'popularity',
      'limit' => settings::get('box_popular_products_num_items') * 2,
    ])->fetch_all();

    shuffle($products);

    $products = array_slice($products, 0, settings::get('box_popular_products_num_items'));

    $box_popular_products = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_popular_products.inc.php');

    $box_popular_products->snippets['products'] = [];
    foreach ($products as $listing_product) {
      $box_popular_products->snippets['products'][] = $listing_product;
    }

    echo $box_popular_products->render();

    cache::end_capture($box_popular_products_cache_token);
  }
