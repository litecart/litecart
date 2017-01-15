<?php
  $_GET['date_from'] = !empty($_GET['date_from']) ? date('Y-m-d 00:00:00', strtotime($_GET['date_from'])) : null;
  $_GET['date_to'] = !empty($_GET['date_to']) ? date('Y-m-d 23:59:59', strtotime($_GET['date_to'])) : date('Y-m-d H:i:s');

  if ($_GET['date_from'] > $_GET['date_to']) list($_GET['date_from'], $_GET['date_to']) = array($_GET['date_to'], $_GET['date_from']);

  $date_first_order = database::fetch(database::query("select min(date_created) from ". DB_TABLE_ORDERS ." limit 1;"));
  $date_first_order = $date_first_order['min(date_created)'];
  if (empty($date_first_order)) $date_first_order = date('Y-m-d 00:00:00');
  if ($_GET['date_from'] < $date_first_order) $_GET['date_from'] = $date_first_order;

  if ($_GET['date_from'] > date('Y-m-d H:i:s')) $_GET['date_from'] = date('Y-m-d H:i:s');
  if ($_GET['date_to'] > date('Y-m-d H:i:s')) $_GET['date_to'] = date('Y-m-d H:i:s');

  if (!isset($_GET['page'])) $_GET['page'] = 1;
?>

<style>
.border-left {
  border-left: 1px #999 dashed;
}
form[name="filter_form"] li {
  vertical-align: middle;
}
</style>

<?php echo functions::form_draw_form_begin('filter_form', 'get'); ?>
  <?php echo functions::form_draw_hidden_field('app'); ?>
  <?php echo functions::form_draw_hidden_field('doc'); ?>
  <ul class="list-inline pull-right">
    <li> <?php echo functions::form_draw_search_field('name', true, 'placeholder="'. htmlspecialchars(language::translate('title_item_name', 'Item Name')) .'"'); ?></li>
    <li>
      <div class="input-group" style="max-width: 350px;">
        <?php echo functions::form_draw_date_field('date_from'); ?>
        <span class="input-group-addon"> - </span>
        <?php echo functions::form_draw_date_field('date_to'); ?>
      </div>
    </li>
    <li><?php echo functions::form_draw_button('filter', language::translate('title_filter_now', 'Filter')); ?></li>
  </ul>
<?php echo functions::form_draw_form_end(); ?>

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_most_sold_products', 'Most Sold Products'); ?></h1>

<table class="table table-striped data-table">
  <thead>
    <tr>
      <th width="100%"><?php echo language::translate('title_product', 'Product'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_sales', 'Sales'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_tax', 'Tax'); ?></th>
    </tr>
  </thead>
  <tbody>
<?php
  $order_statuses = array();
  $orders_status_query = database::query(
    "select id from ". DB_TABLE_ORDER_STATUSES ." where is_sale;"
  );
  while ($order_status = database::fetch($orders_status_query)) {
    $order_statuses[] = (int)$order_status['id'];
  }

  $order_items_query = database::query(
    "select
      oi.name,
      sum(oi.quantity) as total_quantity,
      sum(oi.price * oi.quantity) as total_sales,
      sum(oi.tax * oi.quantity) as total_tax
    from ". DB_TABLE_ORDERS_ITEMS ." oi
    left join ". DB_TABLE_ORDERS ." o on (o.id = oi.order_id)
    where o.order_status_id in ('". implode("', '", $order_statuses) ."')
    and o.date_created >= '". date('Y-m-d 00:00:00', strtotime($_GET['date_from'])) ."'
    and o.date_created <= '". date('Y-m-d 23:59:59', strtotime($_GET['date_to'])) ."'
    ". (!empty($_GET['name']) ? "and oi.name like '%". database::input($_GET['name']) ."%'" : "") ."
    group by oi.product_id
    order by total_quantity desc;"
  );

  if (database::num_rows($order_items_query) > 0) {

    if ($_GET['page'] > 1) database::seek($order_items_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

    $page_items = 0;
    while ($order_item = database::fetch($order_items_query)) {
?>
    <tr>
      <td><?php echo $order_item['name']; ?></td>
      <td style="text-align: center;" class="border-left"><?php echo (float)$order_item['total_quantity']; ?></td>
      <td style="text-align: right;" class="border-left"><?php echo currency::format($order_item['total_sales'], false, settings::get('store_currency_code')); ?></td>
      <td style="text-align: right;" class="border-left"><?php echo currency::format($order_item['total_tax'], false, settings::get('store_currency_code')); ?></td>
    </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
  </tbody>
</table>

<?php echo functions::draw_pagination(ceil(database::num_rows($order_items_query)/settings::get('data_table_rows_per_page'))); ?>