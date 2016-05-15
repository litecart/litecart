<?php
  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  document::$snippets['title'][] = language::translate('order_history:head_title', 'Order History');

  if (empty(customer::$data['id'])) die('You must be logged in');

  if (!isset($_GET['page'])) $_GET['page'] = 1;

  breadcrumbs::add(language::translate('title_account', 'Account'));
  breadcrumbs::add(language::translate('title_order_history', 'Order History'));

  functions::draw_fancybox('a.fancybox', array(
    'type'          => 'iframe',
    'padding'       => '40',
    'width'         => 640,
    'height'        => 800,
    'titlePosition' => 'inside',
    'transitionIn'  => 'elastic',
    'transitionOut' => 'elastic',
    'speedIn'       => 600,
    'speedOut'      => 200,
    'overlayShow'   => true
  ));

  $page = new view();

  $page->snippets['orders'] = array();

  $orders_query = database::query(
    "select o.id, o.uid, o.payment_due, o.currency_code, o.currency_value, o.date_created, osi.name as order_status_name from ". DB_TABLE_ORDERS ." o
    left join ". DB_TABLE_ORDER_STATUSES_INFO ." osi on (osi.order_status_id = o.order_status_id and osi.language_code = '". language::$selected['code'] ."')
    where o.order_status_id
    and o.customer_id = ". (int)customer::$data['id'] ."
    order by o.date_created desc;"
  );

  if (database::num_rows($orders_query) > 0) {
    if ($_GET['page'] > 1) database::seek($orders_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));
    $page_items = 0;

    while ($order = database::fetch($orders_query)) {
      $page->snippets['orders'][] = array(
        'id' => $order['id'],
        'link' => document::ilink('printable_order_copy', array('order_id' => $order['id'], 'checksum' => functions::general_order_public_checksum($order['id']), 'media' => 'print')),
        'order_status' => $order['order_status_name'],
        'date_created' => language::strftime(language::$selected['format_datetime'], strtotime($order['date_created'])),
        'payment_due' => currency::format($order['payment_due'], false, false, $order['currency_code'], $order['currency_value']),
      );
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }

  $page->snippets['pagination'] = functions::draw_pagination(ceil(database::num_rows($orders_query)/settings::get('data_table_rows_per_page')));

  echo $page->stitch('views/box_order_history');
?>