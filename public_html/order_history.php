<?php
  require_once('includes/app_header.inc.php');
  
  header('X-Robots-Tag: noindex');
  $system->document->snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  
  if (empty($system->customer->data['id'])) die('You must be logged in');
  
  if (!isset($_GET['page'])) $_GET['page'] = 1;
  
  $system->document->snippets['title'][] = $system->language->translate('title_order_history', 'Order History');
  //$system->document->snippets['keywords'] = '';
  //$system->document->snippets['description'] = '';

  $system->breadcrumbs->add($system->language->translate('title_account', 'Account'), '');
  $system->breadcrumbs->add($system->language->translate('title_order_history', 'Order History'), $system->document->link());
  
  $system->functions->draw_fancybox('a.fancybox', array(
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
<h1 style="margin-top: 0px;"><?php echo $system->language->translate('title_order_history', 'Order History'); ?></h1>

<table width="100%" class="dataTable">
  <tr class="header">
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_order', 'Order'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_order_status', 'Order Status'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_date', 'Date'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_amount', 'Amount'); ?></th>
  </tr>
<?php
  $orders_query = $system->database->query(
    "select o.id, o.uid, o.payment_due, o.currency_code, o.currency_value, o.date_created, osi.name as order_status_name from ". DB_TABLE_ORDERS ." o
    left join ". DB_TABLE_ORDER_STATUSES_INFO ." osi on (osi.order_status_id = o.order_status_id and osi.language_code = '". $system->language->selected['code'] ."')
    where o.order_status_id
    and o.customer_id = ". (int)$system->customer->data['id'] ."
    order by o.date_created desc;"
  );
  
  if ($system->database->num_rows($orders_query) > 0) {
  
// Jump to data for current page
  if ($_GET['page'] > 1) $system->database->seek($orders_query, ($system->settings->get('data_table_rows_per_page') * ($_GET['page']-1)));
  
    $page_items = 0;
    
    while ($order = $system->database->fetch($orders_query)) {
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr class="<?php echo $rowclass; ?>">
    <td nowrap="nowrap" align="left" nowrap="nowrap"><a href="<?php echo $system->document->href_link('printable_order_copy.php', array('order_id' => $order['id'], 'checksum' => $system->functions->general_order_public_checksum($order['id']), 'media' => 'print')); ?>" class="fancybox"><?php echo $system->language->translate('title_order', 'Order'); ?> #<?php echo $order['id']; ?></a></td>
    <td nowrap="nowrap" align="center" nowrap="nowrap"><?php echo $order['order_status_name']; ?></td>
    <td nowrap="nowrap" align="right" nowrap="nowrap"><?php echo strftime($system->language->selected['format_datetime'], strtotime($order['date_created'])); ?></td>
    <td nowrap="nowrap" align="right" nowrap="nowrap"><?php echo $system->currency->format($order['payment_due'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
  </tr>
<?php
      if (++$page_items == $system->settings->get('data_table_rows_per_page')) break;
    }
  } else {
?>
  <tr>
    <td colspan="4"><em><?php echo $system->language->translate('title_nothing_found', 'Nothing found'); ?></em></td>
  </tr>
<?php
  }
?>
</table>
<?php
// Display page links
  echo $system->functions->draw_pagination(ceil($system->database->num_rows($orders_query)/$system->settings->get('data_table_rows_per_page')));
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>