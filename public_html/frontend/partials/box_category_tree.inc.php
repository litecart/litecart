<?php

  $box_category_tree_cache_token = cache::token('box_category_tree', ['language', fallback($_GET['category_id'], 0]));
  if (cache::capture($box_category_tree_cache_token)) {

    if (!empty($_GET['category_id'])) {
      $category_path = array_keys(reference::category($_GET['category_id'])->path);
      $parent_id = $category_path[0];
    } else {
      $category_path = [];
      $parent_id = 0;
    }

    $box_category_tree = new ent_view(FS_DIR_TEMPLATE . 'partials/box_category_tree.inc.php');
    $box_category_tree->snippets = [
      'title' => $parent_id ? reference::category($parent_id)->name : language::translate('title_categories', 'Categories'),
      'categories' => [],
      'category_path' => $category_path,
    ];

    $iterator = function($parent_id, $level) use (&$iterator, &$category_path) {

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

        if (settings::get('category_tree_product_count')) {
          $tree[$category['id']]['num_products'] = reference::category($category['id'])->num_products;
        }

        if (in_array($category['id'], $category_path)) {
          $sub_categories_query = functions::catalog_categories_query($category['id']);
          if (database::num_rows($sub_categories_query)) {
            $tree[$category['id']]['subcategories'] = $iterator($category['id'], $level+1);
          }
        }
      }

      return $tree;
    };

    if ($box_category_tree->snippets['categories'] = $iterator(0, 0)) {
      echo $box_category_tree;
    }

    cache::end_capture($box_category_tree_cache_token);
  }
