<?php

  try {

    if (empty($_REQUEST['page'])) $_REQUEST['page'] = 1;
    if (empty($_GET['language_code'])) $_GET['language_code'] = language::$selected['code'];

    if (!empty($_REQUEST['query'])) {
      $sql_find = [
        "c.id = '". database::input($_REQUEST['query']) ."'",
        "ci.name like '%". database::input($_REQUEST['query']) ."%'",
      ];
    }

    $category = reference::category($_GET['parent_id']);

    $categories_query = database::query(
      "select c.id, c.parent_id, ci.name, c.date_created from ". DB_PREFIX ."categories c
      left join ". DB_PREFIX ."categories_info ci on (ci.category_id = c.id and ci.language_code = '". database::input($_GET['language_code']) ."')
      where c.id
      ". (isset($_GET['parent_id']) ? "and c.parent_id = '". (int)$_GET['parent_id'] ."'" : "") ."
      ". (!empty($sql_find) ? "and (". implode(" or ", $sql_find) .")" : "") ."
      order by c.priority, ci.name
      limit 20;"
    );

    $json = [
      'status' => 'ok',
      'id' => !empty($_GET['parent_id']) ? $_GET['parent_id'] : 0,
      'name' => !empty($_GET['parent_id']) ? $category->name : '['. language::translate('title_root', 'Root') .']',
      'parent' => [
        'id' => $category->parent ? $category->parent->id : 0,
        'name' => $category->parent ? $category->parent->name : '['. language::translate('title_root', 'Root') .']',
      ],
      'categories' => [],
    ];

    if (database::num_rows($categories_query) > 0) {

      if ($_REQUEST['page'] > 1) database::seek($categories_query, (settings::get('data_table_rows_per_page') * ($_REQUEST['page']-1)));

      $page_items = 0;
      while ($subcategory = database::fetch($categories_query)) {
        $json['subcategories'][] = $subcategory;

        if (++$page_items == settings::get('data_table_rows_per_page')) break;
      }
    }

  } catch (Exception $e) {
    $json = ['error' => $e->getMessage()];
  }

  ob_end_clean();
  header('Content-Type: application/json');
  echo json_encode($json, JSON_UNESCAPED_SLASHES);
  exit;
