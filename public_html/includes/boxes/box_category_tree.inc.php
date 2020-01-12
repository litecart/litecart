<?php

  $box_category_tree_cache_token = cache::token('box_category_tree', array('language', !empty($_GET['category_id']) ? $_GET['category_id'] : 0), 'file');
  if (cache::capture($box_category_tree_cache_token)) {

    if (!empty($_GET['category_id'])) {
      $category_path = array_keys(reference::category($_GET['category_id'])->path);
    } else {
      $category_path = array();
    }

    $box_category_tree = new ent_view();

    $box_category_tree->snippets = array(
      'title' => language::translate('title_categories', 'Categories'),
      'categories' => array(),
      'category_path' => $category_path,
    );

    $iterator = function($parent_id, $level, &$category_path, &$iterator) {

      $tree = array();

      $categories_query = functions::catalog_categories_query($parent_id);

      while ($category = database::fetch($categories_query)) {

        $tree[$category['id']] = array(
          'id' => $category['id'],
          'parent_id' => $category['parent_id'],
          'name' => $category['name'],
          'link' => document::ilink('category', array('category_id' => $category['id']), false),
          'active' => (!empty($_GET['category_id']) && $category['id'] == $_GET['category_id']) ? true : false,
          'opened' => (!empty($category_path) && in_array($category['id'], $category_path)) ? true : false,
          'subcategories' => array(),
        );

        if (in_array($category['id'], $category_path)) {
          $sub_categories_query = functions::catalog_categories_query($category['id']);
          if (database::num_rows($sub_categories_query)) {
            $tree[$category['id']]['subcategories'] = $iterator($category['id'], $level+1, $category_path, $iterator);
          }
        }
      }

      database::free($categories_query);

      return $tree;
    };

    if ($box_category_tree->snippets['categories'] = $iterator(0, 0, $category_path, $iterator)) {
      echo $box_category_tree->stitch('views/box_category_tree');
    }

    cache::end_capture($box_category_tree_cache_token);
  }
