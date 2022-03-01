<?php
  header('X-Robots-Tag: noindex');

  document::$snippets['title'][] = language::translate('order_history:head_title', 'Order History');

  customer::require_login();

  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  breadcrumbs::add(language::translate('title_account', 'Account'));
  breadcrumbs::add(language::translate('title_order_history', 'Order History'));

  $_page = new ent_view(FS_DIR_TEMPLATE . 'pages/order_history.inc.php');

  $_page->snippets['orders'] = [];

  $orders_query = database::query(
    "select o.*, osi.name as order_status_name from ". DB_TABLE_PREFIX ."orders o
    left join ". DB_TABLE_PREFIX ."order_statuses os on (os.id = o.order_status_id)
    left join ". DB_TABLE_PREFIX ."order_statuses_info osi on (osi.order_status_id = o.order_status_id and osi.language_code = '". language::$selected['code'] ."')
    where os.hidden != 0
    and o.customer_id = ". (int)customer::$data['id'] ."
    order by o.date_created desc;"
  );

  if (database::num_rows($orders_query) > 0) {
    if ($_GET['page'] > 1) database::seek($orders_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));
    $page_items = 0;

    while ($order = database::fetch($orders_query)) {

      $downloadable_order_items_query = database::query(
        "select oi.id from ". DB_TABLE_PREFIX ."orders_items oi
        left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = oi.stock_item_id)
        where oi.order_id = ". (int)$order['id'] ."
        and oi.stock_item_id
        and si.file
        limit 1;"
      );

      $_page->snippets['orders'][] = [
        'id' => $order['id'],
        'link' => document::ilink('order', ['order_id' => $order['id'], 'public_key' => $order['public_key']]),
        'printable_link' => document::ilink('printable_order_copy', ['order_id' => $order['id'], 'public_key' => $order['public_key']]),
        'order_status' => $order['order_status_name'],
        'num_downloads' => database::num_rows($downloadable_order_items_query),
        'date_created' => language::strftime(language::$selected['format_datetime'], strtotime($order['date_created'])),
        'total' => currency::format($order['total'], false, $order['currency_code'], $order['currency_value']),
      ];

      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }

  $_page->snippets['pagination'] = functions::draw_pagination(ceil(database::num_rows($orders_query)/settings::get('data_table_rows_per_page')));

  echo $_page;
