<?php
  if (!settings::get('box_latest_products_num_items')) return;

  functions::draw_lightbox();

  $box_latest_products_cache_token = cache::token('box_latest_products', ['language', 'currency', 'prices']);
  if (cache::capture($box_latest_products_cache_token)) {

      $box_latest_products = new ent_view(FS_DIR_TEMPLATE . 'partials/box_latest_products.inc.php');

      $box_latest_products->snippets['products'] = functions::catalog_products_query([
        'sort' => 'date',
        'limit' => settings::get('box_latest_products_num_items'),
      ])->fetch_all();

      if ($box_latest_products->snippets['products']) {
        echo $box_latest_products;
      }

    cache::end_capture($box_latest_products_cache_token);
  }
