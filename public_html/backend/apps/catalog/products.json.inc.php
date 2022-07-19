<?php

  if (empty($_REQUEST['page'])) $_REQUEST['page'] = 1;
  if (empty($_GET['language_code'])) $_GET['language_code'] = language::$selected['code'];
  if (empty($_GET['currency_code'])) $_GET['currency_code'] = currency::$selected['code'];
  if (empty($_GET['currency_value'])) $_GET['currency_value'] = currency::$currencies[$_GET['currency_code']]['value'];

  $products = [];

  if (!empty($_REQUEST['query'])) {
    $sql_find = [
      "p.id = '". database::input($_REQUEST['query']) ."'",
      "p.code like '". addcslashes(database::input($_REQUEST['query']), '%_') ."%'",
      "find_in_set(p.keywords, '". database::input($_REQUEST['query']) ."')",
      "p.sku like '". addcslashes(database::input($_REQUEST['query']), '%_') ."%'",
      "p.mpn like '". addcslashes(database::input($_REQUEST['query']), '%_') ."%'",
      "p.gtin like '". addcslashes(database::input($_REQUEST['query']), '%_') ."%'",
      "pi.name like '%". addcslashes(database::input($_REQUEST['query']), '%_') ."%'",
    ];
  }

  $products_query = database::query(
    "select p.id, p.code, p.sku, p.quantity, p.date_created, pi.name, pp.price from ". DB_TABLE_PREFIX ."products p
    left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input($_GET['language_code']) ."')
    left join (
      select product_id, if(`". database::input($_GET['currency_code']) ."`, `". database::input($_GET['currency_code']) ."` * ". (float)$_GET['currency_value'] .", `". database::input(settings::get('site_currency_code')) ."`) as price
      from ". DB_TABLE_PREFIX ."products_prices
    ) pp on (pp.product_id = p.id)
    ". (!empty($sql_find) ? "where (". implode(" or ", $sql_find) .")" : "") ."
    order by pi.name
    limit 15;"
  );

  if (database::num_rows($products_query)) {

    if ($_REQUEST['page'] > 1) database::seek($products_query, (settings::get('data_table_rows_per_page') * ($_REQUEST['page']-1)));

    $page_items = 0;
    while ($product = database::fetch($products_query)) {
      $products[] = [
        'id' => $product['id'],
        'name' => $product['name'],
        'code' => $product['code'],
        'sku' => $product['sku'],
        'price' => [
          'formatted' => currency::format($product['price'], true, $_GET['currency_code'], $_GET['currency_value']),
          'value' => (float)$product['price'],
        ],
        'quantity' => (float)$product['quantity'],
        'date_created' => language::strftime(language::$selected['format_date'], strtotime($product['date_created'])),
      ];

      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }

  ob_clean();
  header('Content-Type: application/json');
  echo json_encode($products, JSON_UNESCAPED_SLASHES);
  exit;
