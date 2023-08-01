<?php

  if (!empty($_GET['category_id'])) {
    $main_category = array_values(reference::category($_GET['category_id'])->path)[0];
    $trail = array_keys(reference::category($_GET['category_id'])->path);
  } else {
    $main_category = false;
    $trail = [];
  }

  $box_category_tree = new ent_view();
  $box_category_tree->snippets = [
    'title' => $main_category ? $main_category->name : language::translate('title_categories', 'Categories'),
    'main_category' => $main_category ? $main_category->id : 0,
    'categories' => [],
    'trail' => $trail,
    'backlink' => document::ilink('category', ['category_id' => $main_category ? $main_category->id : 0]),
  ];

  if (!nil($_GET['category_id'], $main_category, route::$selected['controller'])) {
    if (route::$selected['controller'] == 'category' && $_GET['category_id'] == $main_category->id) {
      $box_category_tree->snippets['backlink'] = document::ilink('categories');
    }
  }

  $iterator = function($parent_id) use (&$iterator, &$trail) {

    $tree = [];

    $categories_query = functions::catalog_categories_query($parent_id);

    while ($category = database::fetch($categories_query)) {

      $tree[$category['id']] = [
        'id' => $category['id'],
        'parent_id' => $category['parent_id'],
        'name' => $category['name'],
        'link' => document::ilink('category', ['category_id' => $category['id']], false),
        'active' => (!empty($_GET['category_id']) && $category['id'] == $_GET['category_id']) ? true : false,
        'opened' => (!empty($trail) && in_array($category['id'], $trail)) ? true : false,
        'subcategories' => [],
      ];

      if (settings::get('category_tree_product_count')) {
        $tree[$category['id']]['num_products'] = reference::category($category['id'])->num_products;
      }

      if (in_array($category['id'], $trail)) {
        $sub_categories_query = functions::catalog_categories_query($category['id']);
        if (database::num_rows($sub_categories_query)) {
          $tree[$category['id']]['subcategories'] = $iterator($category['id']);
        }
      }
    }

    return $tree;
  };

  $box_category_tree->snippets['categories'] = $iterator($main_category ? $main_category->id : 0);

  echo $box_category_tree->render(FS_DIR_TEMPLATE . 'partials/box_category_tree.inc.php');
