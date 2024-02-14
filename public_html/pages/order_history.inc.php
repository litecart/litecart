<?php
  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex">';
  document::$snippets['title'][] = language::translate('order_history:head_title', 'Order History');

  customer::require_login();

  if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
    $_GET['page'] = 1;
  }

  breadcrumbs::add(language::translate('title_account', 'Account'));
  breadcrumbs::add(language::translate('title_order_history', 'Order History'));

  $_page = new ent_view();

  $_page->snippets['orders'] = [];

  $orders_query = database::query(
    "select o.*, osi.name as order_status_name from ". DB_TABLE_PREFIX ."orders o
    left join ". DB_TABLE_PREFIX ."order_statuses_info osi on (osi.order_status_id = o.order_status_id and osi.language_code = '". database::input(language::$selected['code']) ."')
    where o.order_status_id
    and o.customer_id = ". (int)customer::$data['id'] ."
    order by o.date_created desc;"
  );

  if (database::num_rows($orders_query) > 0) {
    if ($_GET['page'] > 1) database::seek($orders_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));
    $page_items = 0;

    while ($order = database::fetch($orders_query)) {
      $_page->snippets['orders'][] = [
        'id' => $order['id'],
        'link' => document::ilink('order', ['order_id' => $order['id'], 'public_key' => $order['public_key']]),
        'printable_link' => document::ilink('printable_order_copy', ['order_id' => $order['id'], 'public_key' => $order['public_key']]),
        'order_status' => $order['order_status_name'],
        'date_created' => language::strftime(language::$selected['format_datetime'], strtotime($order['date_created'])),
        'payment_due' => currency::format($order['payment_due'], false, $order['currency_code'], $order['currency_value']),
      ];
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }

  $_page->snippets['pagination'] = functions::draw_pagination(ceil(database::num_rows($orders_query)/settings::get('data_table_rows_per_page')));

  echo $_page->stitch('pages/order_history');
