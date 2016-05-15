<?php
  if (settings::get('box_campaign_products_num_items') == 0) return;

  functions::draw_fancybox('a.fancybox');

  $box_campaign_products_cache_id = cache::cache_id('box_campaign_products', array('language', 'currency', 'prices'));
  if (cache::capture($box_campaign_products_cache_id, 'file')) {

    $box_campaign_products = new view();

    $products_query = functions::catalog_products_query(array('campaign' => true, 'sort' => 'rand', 'limit' => settings::get('box_campaign_products_num_items')));

    if (database::num_rows($products_query)) {

      $box_campaign_products->snippets['products'] = array();
      while ($listing_product = database::fetch($products_query)) {
        $box_campaign_products->snippets['products'][] = $listing_product;
      }

      echo $box_campaign_products->stitch('views/box_campaign_products');
    }

    cache::end_capture($box_campaign_products_cache_id);
  }
?>