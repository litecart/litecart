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
form[name="filter_form"] li {
  vertical-align: middle;
}
</style>

<?php echo functions::form_draw_form_begin('filter_form', 'get'); ?>
  <?php echo functions::form_draw_hidden_field('app'); ?>
  <?php echo functions::form_draw_hidden_field('doc'); ?>
  <ul class="list-inline pull-right">
    <li><?php echo language::translate('title_date_period', 'Date Period'); ?>:</li>
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

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_most_shopping_customers', 'Most Shopping Customers'); ?></h1>

<table class="table table-striped data-table">
  <thead>
    <tr>
      <th><?php echo language::translate('title_customer', 'Customer'); ?></th>
      <th width="100%"><?php echo language::translate('title_email_address', 'Email Address'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_total_amount', 'Total Amount'); ?></th>
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

  $customers_query = database::query(
    "select sum(o.payment_due - tax_total) as total_amount, o.customer_id as id, if(o.customer_company, o.customer_company, concat(o.customer_firstname, ' ', o.customer_lastname)) as name, customer_email as email from ". DB_TABLE_ORDERS ." o
    where o.order_status_id in ('". implode("', '", $order_statuses) ."')
    ". (!empty($_GET['date_from']) ? "and o.date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', strtotime($_GET['date_from'])), date('d', strtotime($_GET['date_from'])), date('Y', strtotime($_GET['date_from'])))) ."'" : "") ."
    ". (!empty($_GET['date_to']) ? "and o.date_created <= '". date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', strtotime($_GET['date_to'])), date('d', strtotime($_GET['date_to'])), date('Y', strtotime($_GET['date_to'])))) ."'" : "") ."
    group by if(o.customer_id, o.customer_id, o.customer_email)
    order by total_amount desc;"
  );

  if (database::num_rows($customers_query) > 0) {

    if ($_GET['page'] > 1) database::seek($customers_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

    $page_items = 0;
    while ($customer = database::fetch($customers_query)) {
?>
    <tr>
      <td><?php echo !empty($customer['id']) ? '<a href="'. document::link('', array('app' => 'customers', 'doc' => 'edit_customer', 'customer_id' => $customer['id'])) .'">'. $customer['name'] .'</a>' : $customer['name'] .' <em>('. language::translate('title_guest', 'Guest') .')</em>'; ?></td>
      <td><?php echo $customer['email']; ?></td>
      <td style="text-align: right;"><?php echo currency::format($customer['total_amount'], false, settings::get('store_currency_code')); ?></td>
    </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
  </tbody>
</table>

<?php echo functions::draw_pagination(ceil(database::num_rows($customers_query)/settings::get('data_table_rows_per_page'))); ?>