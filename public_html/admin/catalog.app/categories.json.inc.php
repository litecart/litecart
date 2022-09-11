<?php

  try {

    if (empty($_GET['parent_id'])) $_GET['parent_id'] = 0;
    if (empty($_GET['language_code'])) $_GET['language_code'] = language::$selected['code'];

    if (!empty($_GET['query'])) {
      $sql_find = [
        "c.id = '". database::input($_GET['query']) ."'",
        "ci.name like '%". database::input($_GET['query']) ."%'",
      ];
    }

    $category = reference::category($_GET['parent_id']);

    $json = [
      'status' => 'ok',
      'id' => !empty($_GET['parent_id']) ? $_GET['parent_id'] : 0,
      'name' => !empty($_GET['parent_id']) ? $category->name : '['. language::translate('title_root', 'Root') .']',
      'parent' => [
        'id' => $category->parent ? $category->parent->id : 0,
        'name' => $category->parent ? $category->parent->name : '['. language::translate('title_root', 'Root') .']',
      ],
      'subcategories' => [],
    ];

    $categories_query = database::query(
      "select c.id, c.parent_id, ci.name, c.date_created from ". DB_TABLE_PREFIX ."categories c
      left join ". DB_TABLE_PREFIX ."categories_info ci on (ci.category_id = c.id and ci.language_code = '". database::input($_GET['language_code']) ."')
      where c.id
      ". (isset($_GET['parent_id']) ? "and c.parent_id = ". (int)$_GET['parent_id'] : "") ."
      ". (!empty($sql_find) ? "and (". implode(" or ", $sql_find) .")" : "") ."
      order by c.priority, ci.name;"
    );

    if (database::num_rows($categories_query)) {
      while ($subcategory = database::fetch($categories_query)) {

        $subcategory['path'] = [];
        if (!empty(reference::category($subcategory['id'])->path)) {
          foreach (reference::category($subcategory['id'])->path as $ancestor) {
            $subcategory['path'][] = $ancestor->name;
          }
        }

        $json['subcategories'][] = $subcategory;
      }
    }

  } catch (Exception $e) {
    $json = ['error' => $e->getMessage()];
  }

  ob_clean();
  header('Content-Type: application/json');
  echo json_encode($json, JSON_UNESCAPED_SLASHES);
  exit;
