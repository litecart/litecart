<?php
  ob_end_clean();

  if (empty($_REQUEST['page'])) $_REQUEST['page'] = 1;
  if (empty($_GET['language_code'])) $_GET['language_code'] = language::$selected['code'];

  if (!empty($_REQUEST['query'])) {
    $sql_find = [
      "si.id = '". database::input($_REQUEST['query']) ."'",
      "si.sku like '". database::input($_REQUEST['query']) ."%'",
      "si.mpn like '". database::input($_REQUEST['query']) ."%'",
      "si.gtin like '". database::input($_REQUEST['query']) ."%'",
      "sii.name like '%". database::input($_REQUEST['query']) ."%'",
      "b.name like '%". database::input($_REQUEST['query']) ."%'",
    ];
  }

  $stock_items_query = database::query(
    "select si.*, sii.name, b.name as brand_name from ". DB_TABLE_PREFIX ."stock_items si
    left join ". DB_TABLE_PREFIX ."stock_items_info sii on (sii.stock_item_id = si.id and sii.language_code = '". database::input($_GET['language_code']) ."')
		left join ". DB_TABLE_PREFIX ."brands b on (b.id = si.id)
    ". (!empty($sql_find) ? "where (". implode(" or ", $sql_find) .")" : "") ."
    order by si.sku, b.name, sii.name
    limit 15;"
  );

  $stock_items = [];
  if (database::num_rows($stock_items_query)) {

    if ($_REQUEST['page'] > 1) database::seek($stock_items_query, (settings::get('data_table_rows_per_page') * ($_REQUEST['page']-1)));

    $page_items = 0;
    while ($stock_item = database::fetch($stock_items_query)) {
      foreach ($stock_item as $key => $value) {
        if (is_numeric($value)) $stock_item[$key] = floatval($value);
      }
      $stock_item['date_updated'] = language::strftime(language::$selected['format_date'], strtotime($stock_item['date_updated']));
      $stock_item['date_created'] = language::strftime(language::$selected['format_date'], strtotime($stock_item['date_created']));
      $stock_items[] = $stock_item;
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }

  header('Content-Type: application/json');
  echo json_encode($stock_items, JSON_UNESCAPED_SLASHES);
  exit;
