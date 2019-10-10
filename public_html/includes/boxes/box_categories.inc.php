<?php
  $box_categories_cache_token = cache::token('box_categories', array('language'), 'file');
  if (cache::capture($box_categories_cache_token)) {

    $categories_query = functions::catalog_categories_query();
    if (database::num_rows($categories_query)) {

      $box_categories = new ent_view();

      $box_categories->snippets = array(
        'categories' => array(),
      );

      while ($category = database::fetch($categories_query)) {
        $box_categories->snippets['categories'][] = $category;
      }

      echo $box_categories->stitch('views/box_categories');
    }
    cache::end_capture($box_categories_cache_token);
  }
