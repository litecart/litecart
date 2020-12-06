<?php
// Table Rows
  $orders = array();

  $orders_query = database::query(
    "select o.*, os.color as order_status_color, os.icon as order_status_icon, osi.name as order_status_name from ". DB_TABLE_ORDERS ." o
    left join ". DB_TABLE_ORDER_STATUSES ." os on (os.id = o.order_status_id)
    left join ". DB_TABLE_ORDER_STATUSES_INFO ." osi on (osi.order_status_id = o.order_status_id and osi.language_code = '". language::$selected['code'] ."')
    where o.order_status_id
    and os.is_archived = 0
    order by o.date_created desc, o.id desc
    limit 10;"
  );

  while ($order = database::fetch($orders_query)) {

    if (empty($order['order_status_icon'])) $order['order_status_icon'] = 'fa-circle-thin';
    if (empty($order['order_status_color'])) $order['order_status_color'] = '#cccccc';

    $order_classes = array();
    if (empty($order['order_status_id'])) $order_classes[]= 'semi-transparent';
    if (!empty($order['unread'])) $order_classes[]= 'bold';

    $orders[] = $order;
  }

// Number of Rows
  $num_rows = database::num_rows($orders_query);
?>
<div id="widget-orders" class="widget">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title"><?php echo language::translate('title_orders', 'Orders'); ?></h3>
    </div>

    <div class="panel-body table-responsive">
    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th></th>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th class="main"><?php echo language::translate('title_customer', 'Customer'); ?></th>
          <th><?php echo language::translate('title_country', 'Country'); ?></th>
          <th><?php echo language::translate('title_payment_method', 'Payment Method'); ?></th>
          <th><?php echo language::translate('title_order_status', 'Order Status'); ?></th>
          <th><?php echo language::translate('title_amount', 'Amount'); ?></th>
          <th><?php echo language::translate('title_date', 'Date'); ?></th>
          <th>&nbsp;</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($orders as $order) { ?>
        <tr class="<?php echo implode(' ', $order_classes); ?>">
          <td><?php echo functions::draw_fonticon($order['order_status_icon'], 'style="color: '. $order['order_status_color'] .';"'); ?></td>
          <td><?php echo $order['id']; ?></td>
          <td><a href="<?php echo document::href_link('', array('app' => 'orders', 'doc' => 'edit_order', 'order_id' => $order['id']), true); ?>"><?php echo $order['customer_company'] ? $order['customer_company'] : $order['customer_firstname'] .' '. $order['customer_lastname']; ?></a></td>
          <td><?php echo !empty($order['customer_country_code']) ? reference::country($order['customer_country_code'])->name : ''; ?></td>
          <td><?php echo $order['payment_option_name']; ?></td>
          <td><?php echo ($order['order_status_id'] == 0) ? language::translate('title_uncompleted', 'Uncompleted') : $order['order_status_name']; ?></td>
          <td class="text-right"><?php echo currency::format($order['payment_due'], false, $order['currency_code'], $order['currency_value']); ?></td>
          <td class="text-right"><?php echo language::strftime(language::$selected['format_datetime'], strtotime($order['date_created'])); ?></td>
          <td class="text-right">
              <a href="<?php echo document::href_ilink('printable_packing_slip', array('order_id' => $order['id'], 'public_key' => $order['public_key'], 'media' => 'print')); ?>" target="_blank" title="<?php echo language::translate('title_packing_slip', 'Packing Slip'); ?>"><?php echo functions::draw_fonticon('fa-file-text-o'); ?></a>
              <a href="<?php echo document::href_ilink('printable_order_copy', array('order_id' => $order['id'], 'public_key' => $order['public_key'], 'media' => 'print')); ?>" target="_blank" title="<?php echo language::translate('title_order_copy', 'Order Copy'); ?>"><?php echo functions::draw_fonticon('fa-print'); ?></a>
              <a href="<?php echo document::href_link('', array('app' => 'orders', 'doc' => 'edit_order', 'order_id' => $order['id'])); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a>
          </td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
    </div>
  </div>
</div>