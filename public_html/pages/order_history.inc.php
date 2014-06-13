<?php
  
  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  
  if (empty(customer::$data['id'])) die('You must be logged in');
  
  if (!isset($_GET['page'])) $_GET['page'] = 1;
  
  document::$snippets['title'][] = language::translate('title_order_history', 'Order History');
  //document::$snippets['keywords'] = '';
  //document::$snippets['description'] = '';

  breadcrumbs::add(language::translate('title_account', 'Account'), '');
  breadcrumbs::add(language::translate('title_order_history', 'Order History'), document::link());
  
  functions::draw_fancybox('a.fancybox', array(
    'type'          => 'iframe',
    'padding'       => '40',
    'width'         => 600,
    'height'        => 800,
    'titlePosition' => 'inside',
    'transitionIn'  => 'elastic',
    'transitionOut' => 'elastic',
    'speedIn'       => 600,
    'speedOut'      => 200,
    'overlayShow'   => true
  ));
  
?>
<h1 style="margin-top: 0px;"><?php echo language::translate('title_order_history', 'Order History'); ?></h1>

<table width="100%" class="dataTable">
  <tr class="header">
    <th nowrap="nowrap" align="left" width="100%"><?php echo language::translate('title_order', 'Order'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo language::translate('title_order_status', 'Order Status'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo language::translate('title_date', 'Date'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo language::translate('title_amount', 'Amount'); ?></th>
  </tr>
<?php
  $orders_query = database::query(
    "select o.id, o.uid, o.payment_due, o.currency_code, o.currency_value, o.date_created, osi.name as order_status_name from ". DB_TABLE_ORDERS ." o
    left join ". DB_TABLE_ORDER_STATUSES_INFO ." osi on (osi.order_status_id = o.order_status_id and osi.language_code = '". language::$selected['code'] ."')
    where o.order_status_id
    and o.customer_id = ". (int)customer::$data['id'] ."
    order by o.date_created desc;"
  );
  
  if (database::num_rows($orders_query) > 0) {
  
// Jump to data for current page
  if ($_GET['page'] > 1) database::seek($orders_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));
  
    $page_items = 0;
    
    while ($order = database::fetch($orders_query)) {
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr class="<?php echo $rowclass; ?>">
    <td nowrap="nowrap" align="left" nowrap="nowrap"><a href="<?php echo document::href_link('printable_order_copy.php', array('order_id' => $order['id'], 'checksum' => functions::general_order_public_checksum($order['id']), 'media' => 'print')); ?>" class="fancybox"><?php echo language::translate('title_order', 'Order'); ?> #<?php echo $order['id']; ?></a></td>
    <td nowrap="nowrap" align="center" nowrap="nowrap"><?php echo $order['order_status_name']; ?></td>
    <td nowrap="nowrap" align="right" nowrap="nowrap"><?php echo strftime(language::$selected['format_datetime'], strtotime($order['date_created'])); ?></td>
    <td nowrap="nowrap" align="right" nowrap="nowrap"><?php echo currency::format($order['payment_due'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
  </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  } else {
?>
  <tr>
    <td colspan="4"><em><?php echo language::translate('title_nothing_found', 'Nothing found'); ?></em></td>
  </tr>
<?php
  }
?>
</table>
<?php
// Display page links
  echo functions::draw_pagination(ceil(database::num_rows($orders_query)/settings::get('data_table_rows_per_page')));
?>