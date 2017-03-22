<?php
  if (settings::get('box_popular_products_num_items') == 0) return;

  functions::draw_lightbox('a.lightbox');

  $box_popular_products_cache_id = cache::cache_id('box_popular_products', array('language', 'currency', 'prices'));
  if (cache::capture($box_popular_products_cache_id, 'file')) {

    $products_query = functions::catalog_products_query(array('sort' => 'popularity', 'limit' => settings::get('box_popular_products_num_items')*2));

    if (database::num_rows($products_query)) {

      $listing_products = array();
      while ($listing_product = database::fetch($products_query)) {
        $listing_products[] = $listing_product;
      }

      shuffle($listing_products);

      $listing_products = array_slice($listing_products, 0, settings::get('box_popular_products_num_items'));

      $box_popular_products = new view();

      $box_popular_products->snippets['products'] = array();
      foreach ($listing_products as $listing_product) {
        $box_popular_products->snippets['products'][] = $listing_product;
      }

      echo $box_popular_products->stitch('views/box_popular_products');
    }

    cache::end_capture($box_popular_products_cache_id);
  }
