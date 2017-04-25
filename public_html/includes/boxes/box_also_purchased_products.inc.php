<?php
  if (empty($_GET['product_id'])) return;

  functions::draw_lightbox();

  $box_also_purchased_products_cache_id = cache::cache_id('box_also_purchased_products', array('get', 'language', 'currency', 'prices'));
  if (cache::capture($box_also_purchased_products_cache_id, 'file')) {

    $also_purchased_products = reference::product($_GET['product_id'])->also_purchased_products;

    if (!empty($also_purchased_products)) {

      $also_purchased_products = array_slice($also_purchased_products, 0, settings::get('box_also_purchased_products_num_items')*3, true);

      $products_query = functions::catalog_products_query(array('products' => array_keys($also_purchased_products), 'sort' => 'rand', 'limit' => settings::get('box_also_purchased_products_num_items')));

      if (database::num_rows($products_query)) {

        $box_also_purchased_products = new view();

        $box_also_purchased_products->snippets['products'] = array();
        while ($listing_product = database::fetch($products_query)) {
          $box_also_purchased_products->snippets['products'][] = $listing_product;
        }

        echo $box_also_purchased_products->stitch('views/box_also_purchased_products');
      }
    }

    cache::end_capture($box_also_purchased_products_cache_id);
  }
