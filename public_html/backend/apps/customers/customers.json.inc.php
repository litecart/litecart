<?php

  if (empty($_REQUEST['page'])) $_REQUEST['page'] = 1;

  if (!empty($_REQUEST['query'])) {
    $sql_find = [
      "id = '". database::input($_REQUEST['query']) ."'",
      "email like '%". database::input($_REQUEST['query']) ."%'",
      "tax_id like '%". database::input($_REQUEST['query']) ."%'",
      "company like '%". database::input($_REQUEST['query']) ."%'",
      "concat(firstname, ' ', lastname) like '%". database::input($_REQUEST['query']) ."%'",
    ];
  }

  $customers_query = database::query(
    "select id, firstname, lastname, company, email, date_created from ". DB_TABLE_PREFIX ."customers
    ". (!empty($sql_find) ? "where (". implode(" or ", $sql_find) .")" : "") ."
    order by firstname, lastname
    limit 15;"
  );

  $customers = [];
  if (database::num_rows($customers_query)) {

    if ($_REQUEST['page'] > 1) database::seek($customers_query, (settings::get('data_table_rows_per_page') * ($_REQUEST['page']-1)));

    $page_items = 0;
    while ($customer = database::fetch($customers_query)) {
      $customers[] = [
        'id' => $customer['id'],
        'name' => $customer['company'] ? $customer['company'] :  $customer['firstname'] .' '. $customer['lastname'],
        'email' => $customer['email'],
        'date_created' => language::strftime(language::$selected['format_date'], strtotime($customer['date_created'])),
      ];

      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }

  ob_end_clean();
  header('Content-Type: application/json');
  echo json_encode($customers, JSON_UNESCAPED_SLASHES);
  exit;
