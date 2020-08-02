<?php

  $box_category_tree_cache_token = cache::token('box_category_tree', ['language', !empty($_GET['category_id']) ? $_GET['category_id'] : 0]);
  if (cache::capture($box_category_tree_cache_token)) {

    if (!empty($_GET['category_id'])) {
      $category_path = array_keys(reference::category($_GET['category_id'])->path);
      $parent_id = $category_path[0];
    } else {
      $category_path = [];
      $parent_id = 0;
    }

    $box_category_tree = new ent_view();
    $box_category_tree->snippets = [
      'title' => $parent_id ? reference::category($parent_id)->name : language::translate('title_categories', 'Categories'),
      'categories' => [],
      'category_path' => $category_path,
    ];

    $iterator = function($parent_id, $level, &$category_path, &$_this) {

      $tree = [];

      $categories_query = functions::catalog_categories_query($parent_id);

      while ($category = database::fetch($categories_query)) {

        $tree[$category['id']] = [
          'id' => $category['id'],
          'parent_id' => $category['parent_id'],
          'name' => $category['name'],
          'link' => document::ilink('category', ['category_id' => $category['id']], false),
          'active' => (!empty($_GET['category_id']) && $category['id'] == $_GET['category_id']) ? true : false,
          'opened' => (!empty($category_path) && in_array($category['id'], $category_path)) ? true : false,
          'subcategories' => [],
        ];

        if (in_array($category['id'], $category_path)) {
          $sub_categories_query = functions::catalog_categories_query($category['id']);
          if (database::num_rows($sub_categories_query)) {
            $tree[$category['id']]['subcategories'] = $_this($category['id'], $level+1, $category_path, $_this);
          }
        }
      }

      database::free($categories_query);

      return $tree;
    };

    $box_category_tree->snippets['categories'] = $iterator($parent_id, 0, $category_path, $iterator);

    echo $box_category_tree->stitch('views/box_category_tree');

    cache::end_capture($box_category_tree_cache_token);
  }
