<?php
  if (!settings::get('box_popular_products_num_items')) return;

  functions::draw_lightbox();

  $box_popular_products_cache_token = cache::token('box_popular_products', ['language', 'prices'], 'file');
  if (cache::capture($box_popular_products_cache_token)) {

    $products_query = functions::catalog_products_query([
      'sort' => 'popularity',
      'limit' => settings::get('box_popular_products_num_items')*2,
    ]);

    if (database::num_rows($products_query)) {

      $listing_products = [];
      while ($listing_product = database::fetch($products_query)) {
        $listing_products[] = $listing_product;
      }

      shuffle($listing_products);

      $listing_products = array_slice($listing_products, 0, settings::get('box_popular_products_num_items'));

      $box_popular_products = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_popular_products.inc.php');

      $box_popular_products->snippets['products'] = [];
      foreach ($listing_products as $listing_product) {
        $box_popular_products->snippets['products'][] = $listing_product;
      }

      echo $box_popular_products->render();
    }

    cache::end_capture($box_popular_products_cache_token);
  }
