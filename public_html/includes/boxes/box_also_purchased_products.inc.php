<?php
  if (!is_object($product)) return;

  functions::draw_fancybox('a.fancybox');

  $box_also_purchased_products_cache_id = cache::cache_id('box_also_purchased_products', array('get', 'language', 'currency', 'prices'));
  if (cache::capture($box_also_purchased_products_cache_id, 'file')) {

    $orders_query = database::query(
      "select distinct order_id as id from ". DB_TABLE_ORDERS_ITEMS ."
      where product_id like '". (int)$product->id ."%';"
    );

    $also_purchased_products = array();
    while ($order = database::fetch($orders_query)) {
      $orders_items_query = database::query(
        "select product_id from ". DB_TABLE_ORDERS_ITEMS ."
        where product_id not like '". (int)$product->id ."%'
        and order_id = '". (int)$order['id'] ."';"
      );
      while ($order_item = database::fetch($orders_items_query)) {
        @list($product_id, $option_id) = explode(':', $order_item['product_id']);
        if (isset($also_purchased_products[$product_id])) {
          $also_purchased_products[$product_id]++;
        } else {
          $also_purchased_products[$product_id] = 1;
        }
      }
    }

    if (!empty($also_purchased_products)) {

      arsort($also_purchased_products);
      $also_purchased_products = array_slice($also_purchased_products, 0, 4, true);

      $products_query = functions::catalog_products_query(array('products' => array_keys($also_purchased_products), 'sort' => 'rand', 'limit' => 5));

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
?>