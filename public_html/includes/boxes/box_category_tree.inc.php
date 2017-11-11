<?php

  if (!empty($_GET['category_id'])) {
    $category_path = array_keys(reference::category($_GET['category_id'])->path);
  } else {
    $category_path = array();
  }

  $box_category_tree_cache_id = cache::cache_id('box_category_tree', array('language', !empty($_GET['category_id']) ? $_GET['category_id'] : 0));
  if (cache::capture($box_category_tree_cache_id, 'file')) {

    $box_category_tree = new view();

    $box_category_tree->snippets = array(
      'title' =>  language::translate('title_categories'),
      'categories' => array(),
      'category_path' => $category_path,
    );

    if (!function_exists('custom_build_category_tree')) {
      function custom_build_category_tree($category_id, $level, $category_path, &$output) {

        $categories_query = functions::catalog_categories_query($category_id, ($level == 0) ? 'tree' : null);

        while ($category = database::fetch($categories_query)) {

          $output[$category['id']] = array(
            'id' => $category['id'],
            'parent_id' => $category['parent_id'],
            'name' => $category['name'],
            'link' => document::ilink('category', array('category_id' => $category['id']), false),
            'active' => (!empty($_GET['category_id']) && $category['id'] == $_GET['category_id']) ? true : false,
            //'active' => (!empty($_GET['category_id']) && in_array($category['id'], $category_path)) ? true : false,
            'opened' => (!empty($category_path) && in_array($category['id'], $category_path)) ? true : false,
            'subcategories' => array(),
          );

          if (in_array($category['id'], $category_path)) {
            $sub_categories_query = functions::catalog_categories_query($category['id']);
            if (database::num_rows($sub_categories_query) > 0) {
              custom_build_category_tree($category['id'], $level+1, $category_path, $output[$category['id']]['subcategories']);
            }
          }
        }

        database::free($categories_query);

        return $output;
      }
    }

    if ($box_category_tree->snippets['categories'] = custom_build_category_tree(0, 0, $category_path, $box_category_tree->snippets['categories'])) {
      echo $box_category_tree->stitch('views/box_category_tree');
    }

    cache::end_capture($box_category_tree_cache_id);
  }
