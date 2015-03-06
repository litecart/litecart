<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['date_from'])) $_GET['date_from'] = date('Y-m-d', strtotime('-1 months'));
  if (empty($_GET['date_to'])) $_GET['date_to'] = date('Y-m-d');
?>

<div style="float: right; display: inline;">
  <?php echo functions::form_draw_form_begin('filter_form', 'get'); ?>
    <?php echo functions::form_draw_hidden_field('app'); ?>
    <?php echo functions::form_draw_hidden_field('doc'); ?>
    <table>
      <tr>
        <td><?php echo language::translate('title_date_period', 'Date Period'); ?>:</td>
        <td><?php echo functions::form_draw_date_field('date_from'); ?> - <?php echo functions::form_draw_date_field('date_to'); ?></td>
        <td><?php echo functions::form_draw_button('filter', language::translate('title_filter_now', 'Filter')); ?></td>
      </tr>
    </table>
  <?php echo functions::form_draw_form_end(); ?>
</div>


<h1 style="margin-top: 0px;"><?php echo $app_icon; ?><?php echo language::translate('title_most_sold_products', 'Most Sold Products'); ?></h1>

<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th width="100%"><?php echo language::translate('title_product', 'Product'); ?></th>
    <th style="text-align: center;"><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
  </tr>
<?php
  $order_statuses = array();
  $orders_status_query = database::query(
    "select id from ". DB_TABLE_ORDER_STATUSES ." where is_sale;"
  );
  while ($order_status = database::fetch($orders_status_query)) {
    $order_statuses[] = (int)$order_status['id'];
  }
  
  $order_items_query = database::query(
    "select sum(oi.quantity) as quantity, oi.name from ". DB_TABLE_ORDERS_ITEMS ." oi
    left join ". DB_TABLE_ORDERS ." o on (o.id = oi.order_id)
    where o.order_status_id in ('". implode("', '", $order_statuses) ."')
    and o.date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', strtotime($_GET['date_from'])), date('d', strtotime($_GET['date_from'])), date('Y', strtotime($_GET['date_from'])))) ."'
    and o.date_created <= '". date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', strtotime($_GET['date_to'])), date('d', strtotime($_GET['date_to'])), date('Y', strtotime($_GET['date_to'])))) ."'
    group by oi.product_id
    order by quantity desc
    limit 50;"
  );
  
  if (database::num_rows($order_items_query) > 0) {
    
    if ($_GET['page'] > 1) database::seek($order_items_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($order_item = database::fetch($order_items_query)) {
      
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr class="<?php echo $rowclass; ?>">
    <td><?php echo $order_item['name']; ?></td>
    <td style="text-align: center;"><?php echo $order_item['quantity']; ?></td>
  </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
</table>

<?php echo functions::draw_pagination(ceil(database::num_rows($order_items_query)/settings::get('data_table_rows_per_page'))); ?>