<?php
  if (empty($_GET['product_id'])) return;

  $product = reference::product($_GET['product_id']);

  if (!settings::get('box_similar_products_num_items')) return;

  functions::draw_lightbox();

  $box_similar_products_cache_token = cache::token('box_similar_products', array('get', 'language', 'currency', 'prices'), 'file');
  if (cache::capture($box_similar_products_cache_token)) {

    $products_query = functions::catalog_products_search_query(array(
      'product_name' => $product->name,
      'categories' => isset($_GET['category_id']) ? array($_GET['category_id']) : array_keys($product->categories),
      'manufacturers' => array($product->manufacturer_id),
      'exclude_products' => array($product->id),
      'keywords' => $product->keywords,
      'limit' => settings::get('box_similar_products_num_items'),
    ));

    if (database::num_rows($products_query) > 0) {

      $box_similar_products = new ent_view();

      $box_similar_products->snippets['products'] = array();
      while ($listing_product = database::fetch($products_query)) {
        if (empty($listing_product['occurrences'])) break;
        $box_similar_products->snippets['products'][] = $listing_product;
      }

      if ($box_similar_products->snippets['products']) {
        echo $box_similar_products->stitch('views/box_similar_products');
      }
    }

    cache::end_capture($box_similar_products_cache_token);
  }
