<?php
  if (!settings::get('box_campaign_products_num_items')) return;

  functions::draw_lightbox();

  $box_campaign_products_cache_token = cache::token('box_campaign_products', ['language', 'prices'], 'file');
  if (cache::capture($box_campaign_products_cache_token)) {

    $box_campaign_products = new ent_view();

    $products_query = functions::catalog_products_query([
      'campaign' => true,
      'sort' => 'random',
      'limit' => settings::get('box_campaign_products_num_items'),
    ]);

    if (database::num_rows($products_query)) {

      $box_campaign_products->snippets['products'] = [];
      while ($listing_product = database::fetch($products_query)) {
        $box_campaign_products->snippets['products'][] = $listing_product;
      }

      echo $box_campaign_products->stitch('views/box_campaign_products');
    }

    cache::end_capture($box_campaign_products_cache_token);
  }
