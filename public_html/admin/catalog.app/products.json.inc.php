<?php
  ob_end_clean();

  if (empty($_REQUEST['page'])) $_REQUEST['page'] = 1;
  if (empty($_GET['language_code'])) $_GET['language_code'] = language::$selected['code'];
  if (empty($_GET['currency_code'])) $_GET['currency_code'] = currency::$selected['code'];
  if (empty($_GET['currency_value'])) $_GET['currency_value'] = currency::$currencies[$_GET['currency_code']]['value'];

  if (!empty($_REQUEST['query'])) {
    $sql_find = array(
      "p.id = '". database::input($_REQUEST['query']) ."'",
      "p.code like '". database::input($_REQUEST['query']) ."%'",
      "find_in_set(p.keywords, '". database::input($_REQUEST['query']) ."')",
      "p.sku like '". database::input($_REQUEST['query']) ."%'",
      "p.gtin like '". database::input($_REQUEST['query']) ."%'",
      "pi.name like '%". database::input($_REQUEST['query']) ."%'",
    );
  }

  $products_query = database::query(
    "select p.id, p.code, p.sku, p.quantity, p.date_created, pi.name, pp.price from ". DB_TABLE_PRODUCTS ." p
    left join ". DB_TABLE_PRODUCTS_INFO ." pi on (pi.product_id = p.id and pi.language_code = '". database::input($_GET['language_code']) ."')
    left join (
      select product_id, if(`". database::input($_GET['currency_code']) ."`, `". database::input($_GET['currency_code']) ."` / ". (float)$_GET['currency_value'] .", `". database::input(settings::get('store_currency_code')) ."`) as price
      from ". DB_TABLE_PRODUCTS_PRICES ."
    ) pp on (pp.product_id = p.id)
    ". ((!empty($sql_find)) ? "where (". implode(" or ", $sql_find) .")" : "") ."
    order by pi.name
    limit 15;"
  );

  $products = array();
  if (database::num_rows($products_query) > 0) {

    if ($_REQUEST['page'] > 1) database::seek($products_query, (settings::get('data_table_rows_per_page') * ($_REQUEST['page']-1)));

    $page_items = 0;
    while ($product = database::fetch($products_query)) {
      $products[] = array(
        'id' => $product['id'],
        'name' => $product['name'],
        'code' => $product['code'],
        'sku' => $product['sku'],
        'price' => array(
          'formatted' => currency::format($product['price'], true, $_GET['currency_code'], $_GET['currency_value']),
          'value' => (float)$product['price'],
        ),
        'quantity' => (float)$product['quantity'],
        'date_created' => language::strftime(language::$selected['format_date'], strtotime($product['date_created'])),
      );

      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }

  header('Content-Type: application/json');
  echo json_encode($products);
  exit;
