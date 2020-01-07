<?php

  breadcrumbs::add(language::translate('title_most_sold_products', 'Most Sold Products'));

  $_GET['date_from'] = !empty($_GET['date_from']) ? date('Y-m-d', strtotime($_GET['date_from'])) : null;
  $_GET['date_to'] = !empty($_GET['date_to']) ? date('Y-m-d', strtotime($_GET['date_to'])) : date('Y-m-d');

  if ($_GET['date_from'] > $_GET['date_to']) list($_GET['date_from'], $_GET['date_to']) = array($_GET['date_to'], $_GET['date_from']);

  $date_first_order = database::fetch(database::query("select min(date_created) from ". DB_TABLE_ORDERS ." limit 1;"));
  $date_first_order = date('Y-m-d', strtotime($date_first_order['min(date_created)']));
  if (empty($date_first_order)) $date_first_order = date('Y-m-d');
  if ($_GET['date_from'] < $date_first_order) $_GET['date_from'] = $date_first_order;

  if ($_GET['date_from'] > date('Y-m-d')) $_GET['date_from'] = date('Y-m-d');
  if ($_GET['date_to'] > date('Y-m-d')) $_GET['date_to'] = date('Y-m-d');

  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

// Table Rows
  $rows = array();

  $order_items_query = database::query(
    "select
      oi.product_id,
      oi.sku,
      oi.name,
      sum(oi.quantity) as total_quantity,
      sum(oi.price * oi.quantity) as total_sales,
      sum(oi.tax * oi.quantity) as total_tax
    from ". DB_TABLE_ORDERS_ITEMS ." oi
    left join ". DB_TABLE_ORDERS ." o on (o.id = oi.order_id)
    where o.order_status_id in (
      select id from ". DB_TABLE_ORDER_STATUSES ."
      where is_sale
    )
    and o.date_created >= '". date('Y-m-d 00:00:00', strtotime($_GET['date_from'])) ."'
    and o.date_created <= '". date('Y-m-d 23:59:59', strtotime($_GET['date_to'])) ."'
    ". (!empty($_GET['name']) ? "and oi.name like '%". database::input($_GET['name']) ."%'" : "") ."
    group by oi.product_id
    order by total_quantity desc;"
  );

  if ($_GET['page'] > 1) database::seek($order_items_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

  $page_items = 0;
  while ($order_item = database::fetch($order_items_query)) {
    $rows[] = $order_item;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($order_items_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));
?>

<style>
form[name="filter_form"] li {
  vertical-align: middle;
}
</style>

<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_most_sold_products', 'Most Sold Products'); ?>
  </div>

  <div class="panel-action">
    <?php echo functions::form_draw_form_begin('filter_form', 'get'); ?>
      <?php echo functions::form_draw_hidden_field('app'); ?>
      <?php echo functions::form_draw_hidden_field('doc'); ?>
      <ul class="list-inline">
        <li> <?php echo functions::form_draw_search_field('name', true, 'placeholder="'. htmlspecialchars(language::translate('title_item_name', 'Item Name')) .'"'); ?></li>
        <li>
          <div class="input-group" style="max-width: 380px;">
            <?php echo functions::form_draw_date_field('date_from', true); ?>
            <span class="input-group-addon"> - </span>
            <?php echo functions::form_draw_date_field('date_to', true); ?>
          </div>
        </li>
        <li><?php echo functions::form_draw_button('filter', language::translate('title_filter_now', 'Filter')); ?></li>
      </ul>
    <?php echo functions::form_draw_form_end(); ?>
  </div>

  <div class="panel-body">
    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th width="100%"><?php echo language::translate('title_product', 'Product'); ?></th>
          <th style="text-align: center;"><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
          <th style="text-align: center;"><?php echo language::translate('title_sales', 'Sales'); ?></th>
          <th style="text-align: center;"><?php echo language::translate('title_tax', 'Tax'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row) { ?>
        <tr>
          <td><?php echo $row['name']; ?></td>
          <td style="text-align: center;" class="border-left"><?php echo (float)$row['total_quantity']; ?></td>
          <td style="text-align: right;" class="border-left"><?php echo currency::format($row['total_sales'], false, settings::get('store_currency_code')); ?></td>
          <td style="text-align: right;" class="border-left"><?php echo currency::format($row['total_tax'], false, settings::get('store_currency_code')); ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <div class="panel-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
</div>
