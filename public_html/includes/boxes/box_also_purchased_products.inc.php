<?php
  if (empty($_GET['product_id'])) return;
  if (!settings::get('box_also_purchased_products_num_items')) return;

  functions::draw_lightbox();

  $box_also_purchased_products_cache_token = cache::token('box_also_purchased_products', [$_GET['product_id'], 'language', 'prices'], 'file');
  if (cache::capture($box_also_purchased_products_cache_token)) {

    $also_purchased_products = reference::product($_GET['product_id'])->also_purchased_products;

    if (!empty($also_purchased_products)) {

      $also_purchased_products = array_slice($also_purchased_products, 0, settings::get('box_also_purchased_products_num_items')*3, true);

      $products_query = functions::catalog_products_query([
        'products' => array_keys($also_purchased_products),
        'sort' => 'random',
        'limit' => settings::get('box_also_purchased_products_num_items'),
      ]);

      if (database::num_rows($products_query)) {

        $box_also_purchased_products = new ent_view();

        $box_also_purchased_products->snippets['products'] = [];
        while ($listing_product = database::fetch($products_query)) {
          $box_also_purchased_products->snippets['products'][] = $listing_product;
        }

        echo $box_also_purchased_products->stitch('views/box_also_purchased_products');
      }
    }

    cache::end_capture($box_also_purchased_products_cache_token);
  }
