<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
?>
<div style="float: right;"><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_order_status'), true), language::translate('title_create_new_order_status', 'Create New Order Status'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_order_statuses', 'Order Statuses'); ?></h1>

<?php echo functions::form_draw_form_begin('order_statuses_form', 'post'); ?>
<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle'); ?></th>
    <th><?php echo language::translate('title_id', 'ID'); ?></th>
    <th><?php echo language::translate('title_icon', 'Icon'); ?></th>
    <th width="100%"><?php echo language::translate('title_name', 'Name'); ?></th>
    <th><?php echo language::translate('title_sales', 'Sales'); ?></th>
    <th><?php echo language::translate('title_archived', 'Archived'); ?></th>
    <th><?php echo language::translate('title_notify', 'Notify'); ?></th>
    <th><?php echo language::translate('title_priority', 'Priority'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php

  $orders_status_query = database::query(
    "select os.*, osi.name, os.priority from ". DB_TABLE_ORDER_STATUSES ." os
    left join ". DB_TABLE_ORDER_STATUSES_INFO ." osi on (os.id = osi.order_status_id and language_code = '". language::$selected['code'] ."')
    order by os.priority, osi.name asc;"
  );

  if (database::num_rows($orders_status_query) > 0) {

    if ($_GET['page'] > 1) database::seek($orders_status_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

    $page_items = 0;
    while ($order_status = database::fetch($orders_status_query)) {

      if (empty($order_status['icon'])) $order_status['icon'] = 'fa-circle-thin';
      if (empty($order_status['color'])) $order_status['color'] = '#cccccc';
?>
  <tr class="row">
    <td><?php echo functions::form_draw_checkbox('order_statuses['. $order_status['id'] .']', $order_status['id']); ?></td>
    <td><?php echo $order_status['id']; ?></td>
    <td><?php echo functions::draw_fonticon($order_status['icon'], 'style="color: '. $order_status['color'] .';"'); ?></td>
    <td><a href="<?php echo document::href_link('', array('doc' => 'edit_order_status', 'order_status_id' => $order_status['id']), true); ?>"><?php echo $order_status['name']; ?></a></td>
    <td style="text-align: center;"><?php echo empty($order_status['is_sale']) ? '' : 'x'; ?></td>
    <td style="text-align: center;"><?php echo empty($order_status['is_archived']) ? '' : 'x'; ?></td>
    <td style="text-align: center;"><?php echo empty($order_status['notify']) ? '' : 'x'; ?></td>
    <td style="text-align: center;"><?php echo $order_status['priority']; ?></td>
    <td style="text-align: right;"><a href="<?php echo document::href_link('', array('doc' => 'edit_order_status', 'order_status_id' => $order_status['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
  </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="9"><?php echo language::translate('title_order_statuses', 'Order Statuses'); ?>: <?php echo database::num_rows($orders_status_query); ?></td>
  </tr>
</table>

<script>
  $(".dataTable .checkbox-toggle").click(function() {
    $(this).closest("form").find(":checkbox").each(function() {
      $(this).attr('checked', !$(this).attr('checked'));
    });
    $(".dataTable .checkbox-toggle").attr("checked", true);
  });

  $('.dataTable tr').click(function(event) {
    if ($(event.target).is('input:checkbox')) return;
    if ($(event.target).is('a, a *')) return;
    if ($(event.target).is('th')) return;
    $(this).find('input:checkbox').trigger('click');
  });
</script>

<?php
  echo functions::form_draw_form_end();

  echo functions::draw_pagination(ceil(database::num_rows($orders_status_query)/settings::get('data_table_rows_per_page')));
?>