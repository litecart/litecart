<?php
  $box_categories_cache_token = cache::token('box_categories', ['language']);
  if (cache::capture($box_categories_cache_token)) {

    $categories_query = functions::catalog_categories_query();

      $box_categories = new ent_view('views/box_categories.inc.php');

      $box_categories->snippets = [
        'categories' => [],
      ];

      while ($category = database::fetch($categories_query)) {
        $box_categories->snippets['categories'][] = $category;
      }

      echo $box_categories;
    }

    cache::end_capture($box_categories_cache_token);
  }
