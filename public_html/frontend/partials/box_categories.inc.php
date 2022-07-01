<?php
  $box_categories_cache_token = cache::token('box_categories', ['language']);
  if (cache::capture($box_categories_cache_token)) {

    $box_categories = new ent_view(FS_DIR_TEMPLATE . 'partials/box_categories.inc.php');

    $box_categories->snippets['categories'] = functions::catalog_categories_query()->fetch_all();

    echo $box_categories;

    cache::end_capture($box_categories_cache_token);
  }
