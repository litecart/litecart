<?php
  ob_end_clean();

  if (empty($_REQUEST['page'])) $_REQUEST['page'] = 1;

  if (!empty($_REQUEST['query'])) {
    $sql_find = array(
      "id = '". database::input($_REQUEST['query']) ."'",
      "email like '%". database::input($_REQUEST['query']) ."%'",
      "tax_id like '%". database::input($_REQUEST['query']) ."%'",
      "company like '%". database::input($_REQUEST['query']) ."%'",
      "concat(firstname, ' ', lastname) like '%". database::input($_REQUEST['query']) ."%'",
    );
  }

  $customers_query = database::query(
    "select id, firstname, lastname, company, email, date_created from ". DB_TABLE_CUSTOMERS ."
    ". ((!empty($sql_find)) ? "where (". implode(" or ", $sql_find) .")" : "") ."
    order by firstname, lastname
    limit 15;"
  );

  $customers = array();
  if (database::num_rows($customers_query) > 0) {

    if ($_REQUEST['page'] > 1) database::seek($customers_query, (settings::get('data_table_rows_per_page') * ($_REQUEST['page']-1)));

    $page_items = 0;
    while ($customer = database::fetch($customers_query)) {
      $customers[] = array(
        'id' => $customer['id'],
        'name' => $customer['company'] ? $customer['company'] :  $customer['firstname'] .' '. $customer['lastname'],
        'email' => $customer['email'],
        'date_created' => language::strftime(language::$selected['format_date'], strtotime($customer['date_created'])),
      );

      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }

  header('Content-Type: application/json');
  echo json_encode($customers);
  exit;
