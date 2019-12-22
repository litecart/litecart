<?php
  breadcrumbs::add(language::translate('title_monthly_sales', 'Monthly Sales'));

  $_GET['date_from'] = !empty($_GET['date_from']) ? date('Y-m-d', strtotime($_GET['date_from'])) : null;
  $_GET['date_to'] = !empty($_GET['date_to']) ? date('Y-m-d', strtotime($_GET['date_to'])) : date('Y-m-d');

  if ($_GET['date_from'] > $_GET['date_to']) list($_GET['date_from'], $_GET['date_to']) = array($_GET['date_to'], $_GET['date_from']);

  $date_first_order = database::fetch(database::query("select min(date_created) from ". DB_TABLE_ORDERS ." limit 1;"));
  $date_first_order = date('Y-m-d', strtotime($date_first_order['min(date_created)']));
  if (empty($date_first_order)) $date_first_order = date('Y-m-d');
  if ($_GET['date_from'] < $date_first_order) $_GET['date_from'] = $date_first_order;

  if ($_GET['date_from'] > date('Y-m-d')) $_GET['date_from'] = date('Y-m-d');
  if ($_GET['date_to'] > date('Y-m-d')) $_GET['date_to'] = date('Y-m-d');

// Table Rows
  $rows = array();

  $orders_query = database::query(
    "select
      group_concat(o.id) as order_ids,
      sum(o.payment_due) - sum(o.tax_total) as total_sales,
      sum(o.tax_total) as total_tax,
      sum(otst.value) as total_subtotal,
      sum(otsf.value) as total_shipping_fees,
      sum(otpf.value) as total_payment_fees,
      date_format(o.date_created, '%Y-%m') as `year_month`
    from ". DB_TABLE_ORDERS ." o
    left join (
      select order_id, sum(value) as value
      from ". DB_TABLE_ORDERS_TOTALS ."
      where module_id = 'ot_subtotal'
      group by order_id
    ) otst on (o.id = otst.order_id)
    left join (
      select order_id, sum(value) as value
      from ". DB_TABLE_ORDERS_TOTALS ."
      where module_id = 'ot_shipping_fee'
      group by order_id
    ) otsf on (o.id = otsf.order_id)
    left join (
      select order_id, sum(value) as value
      from ". DB_TABLE_ORDERS_TOTALS ."
      where module_id = 'ot_payment_fee'
      group by order_id
    ) otpf on (o.id = otpf.order_id)
    where o.order_status_id in (
      select id from ". DB_TABLE_ORDER_STATUSES ."
      where is_sale
    )
    ". (!empty($_GET['date_from']) ? "and o.date_created >= '". date('Y-m-d 00:00:00', strtotime($_GET['date_from'])) ."'" : "") ."
    ". (!empty($_GET['date_to']) ? "and o.date_created <= '". date('Y-m-d 23:59:59', strtotime($_GET['date_to'])) ."'" : "") ."
    group by date_format(o.date_created, '%Y-%m')
    order by `year_month` desc;"
  );

  while ($orders = database::fetch($orders_query)) {
    if (!isset($total)) $total = array();
    foreach (array_keys($orders) as $key) {
      if (!isset($total[$key])) $total[$key] = (float)$orders[$key];
      else $total[$key] += (float)$orders[$key];
    }
    $rows[] = $orders;
  }
?>
<style>
form[name="filter_form"] li {
  vertical-align: middle;
}
</style>

<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_monthly_sales', 'Monthly Sales'); ?>
  </div>

  <div class="panel-action">
    <?php echo functions::form_draw_form_begin('filter_form', 'get'); ?>
      <?php echo functions::form_draw_hidden_field('app'); ?>
      <?php echo functions::form_draw_hidden_field('doc'); ?>
      <ul class="list-inline">
        <li><?php echo language::translate('title_date_period', 'Date Period'); ?>:</li>
        <li>
          <div class="input-group" style="max-width: 380px;">
            <?php echo functions::form_draw_date_field('date_from'); ?>
            <span class="input-group-addon"> - </span>
            <?php echo functions::form_draw_date_field('date_to'); ?>
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
          <th width="100%"><?php echo language::translate('title_month', 'Month'); ?></th>
          <th class="border-left text-center"><?php echo language::translate('title_subtotal', 'Subtotal'); ?></th>
          <th class="border-left text-center"><?php echo language::translate('title_shipping_fees', 'Shipping Fees'); ?></th>
          <th class="border-left text-center"><?php echo language::translate('title_payment_fees', 'Payment Fees'); ?></th>
          <th class="border-left text-center"><?php echo language::translate('title_total', 'Total'); ?></th>
          <th class="text-center"><?php echo language::translate('title_tax', 'Tax'); ?></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($rows as $row) { ?>
        <tr>
          <td><?php echo ucfirst(language::strftime('%B, %Y', strtotime($row['year_month'].'-01'))); ?></td>
          <td class="border-left text-right"><?php echo currency::format($row['total_subtotal'], false, settings::get('store_currency_code')); ?></td>
          <td class="border-left text-right"><?php echo currency::format($row['total_shipping_fees'], false, settings::get('store_currency_code')); ?></td>
          <td class="border-left text-right"><?php echo currency::format($row['total_payment_fees'], false, settings::get('store_currency_code')); ?></td>
          <td class="border-left text-right"><strong><?php echo currency::format($row['total_sales'], false, settings::get('store_currency_code')); ?></strong></td>
          <td class="text-right"><?php echo currency::format($row['total_tax'], false, settings::get('store_currency_code')); ?></td>
        </tr>
        <?php } ?>
      </tbody>

      <?php if (!empty($total)) { ?>
      <tfoot>
        <tr>
          <td class="text-right"><?php echo strtoupper(language::translate('title_total', 'Total')); ?></td>
          <td class="border-left text-right"><?php echo currency::format($total['total_subtotal'], false, settings::get('store_currency_code')); ?></td>
          <td class="border-left text-right"><?php echo currency::format($total['total_shipping_fees'], false, settings::get('store_currency_code')); ?></td>
          <td class="border-left text-right"><?php echo currency::format($total['total_payment_fees'], false, settings::get('store_currency_code')); ?></td>
          <td class="border-left text-right"><strong><?php echo currency::format($total['total_sales'], false, settings::get('store_currency_code')); ?></strong></td>
          <td class="text-right"><?php echo currency::format($total['total_tax'], false, settings::get('store_currency_code')); ?></td>
        </tr>
      </tfoot>
      <?php } ?>
    </table>
  </div>
</div>
