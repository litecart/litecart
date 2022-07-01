<?php
  if (!settings::get('box_campaign_products_num_items')) return;

  functions::draw_lightbox();

  $box_campaign_products_cache_token = cache::token('box_campaign_products', ['language', 'currency']);
  if (cache::capture($box_campaign_products_cache_token)) {

    $box_campaign_products = new ent_view(FS_DIR_TEMPLATE . 'partials/box_campaign_products.inc.php');

    $box_campaign_products->snippets['products'] = functions::catalog_products_query([
      'campaign' => true,
      'sort' => 'random',
      'limit' => settings::get('box_campaign_products_num_items'),
    ])->fetch_all();

    if ($box_campaign_products->snippets['products']) {
      echo $box_campaign_products;
    }

    cache::end_capture($box_campaign_products_cache_token);
  }
