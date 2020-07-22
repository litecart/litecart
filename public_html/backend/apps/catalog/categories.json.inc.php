<?php
  ob_end_clean();

  if (empty($_REQUEST['page'])) $_REQUEST['page'] = 1;
  if (empty($_GET['language_code'])) $_GET['language_code'] = language::$selected['code'];

  if (!empty($_REQUEST['query'])) {
    $sql_find = [
      "c.id = '". database::input($_REQUEST['query']) ."'",
      "ci.name like '%". database::input($_REQUEST['query']) ."%'",
    ];
  }

  $categories_query = database::query(
    "select c.id, c.parent_id, ci.name, c.date_created from ". DB_PREFIX ."categories c
    left join ". DB_PREFIX ."categories_info ci on (ci.category_id = c.id and ci.language_code = '". database::input($_GET['language_code']) ."')
    where c.id
    ". (isset($_GET['parent_id']) ? "and c.parent_id = '". (int)$_GET['parent_id'] ."'" : "") ."
    ". (!empty($sql_find) ? "and (". implode(" or ", $sql_find) .")" : "") ."
    order by c.priority, ci.name
    limit 20;"
  );

  $categories = [];
  if (database::num_rows($categories_query) > 0) {

    if ($_REQUEST['page'] > 1) database::seek($categories_query, (settings::get('data_table_rows_per_page') * ($_REQUEST['page']-1)));

    $page_items = 0;
    while ($category = database::fetch($categories_query)) {
      $categories[] = $category;

      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }

  header('Content-Type: application/json');
  echo json_encode($categories, JSON_UNESCAPED_SLASHES);
  exit;
