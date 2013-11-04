<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
?>
<div style="float: right;"><?php echo $system->functions->form_draw_link_button($system->document->link('', array('doc' => 'edit_order_status'), true), $system->language->translate('title_create_new_order_status', 'Create New Order Status'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo $system->language->translate('title_order_statuses', 'Order Statuses'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('order_statuses_form', 'post'); ?>
<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th><?php echo $system->functions->form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_name', 'Name'); ?></th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_sales', 'Sales'); ?></th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_notify', 'Notify'); ?></th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_priority', 'Priority'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php

  $orders_status_query = $system->database->query(
    "select os.id, os.is_sale, os.notify, osi.name, os.priority from ". DB_TABLE_ORDER_STATUSES ." os
    left join ". DB_TABLE_ORDER_STATUSES_INFO ." osi on (os.id = osi.order_status_id and language_code = '". $system->language->selected['code'] ."')
    order by os.priority, osi.name asc;"
  );

  if ($system->database->num_rows($orders_status_query) > 0) {
    
  // Jump to data for current page
    if ($_GET['page'] > 1) $system->database->seek($orders_status_query, ($system->settings->get('data_table_rows_per_page') * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($order_status = $system->database->fetch($orders_status_query)) {
    
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr class="<?php echo $rowclass; ?>">
    <td><?php echo $system->functions->form_draw_checkbox('order_statuses['. $order_status['id'] .']', $order_status['id']); ?></td>
    <td align="left"><?php echo $order_status['id']; ?></td>
    <td align="left" nowrap="nowrap"><a href="<?php echo $system->document->href_link('', array('doc' => 'edit_order_status', 'order_status_id' => $order_status['id']), true); ?>"><?php echo $order_status['name']; ?></a></td>
    <td align="center" nowrap="nowrap"><?php echo empty($order_status['is_sale']) ? '' : 'x'; ?></td>
    <td align="center" nowrap="nowrap"><?php echo empty($order_status['notify']) ? '' : 'x'; ?></td>
    <td align="center"><?php echo $order_status['priority']; ?></td>
    <td align="right"><a href="<?php echo $system->document->href_link('', array('doc' => 'edit_order_status', 'order_status_id' => $order_status['id']), true); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/edit.png'; ?>" width="16" height="16" alt="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" title="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" /></a></td>
  </tr>
<?php
      if (++$page_items == $system->settings->get('data_table_rows_per_page')) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="7" align="left"><?php echo $system->language->translate('title_order_statuses', 'Order Statuses'); ?>: <?php echo $system->database->num_rows($orders_status_query); ?></td>
  </tr>
</table>

<script>
  $(".dataTable input[name='checkbox_toggle']").click(function() {
    $(this).closest("form").find(":checkbox").each(function() {
      $(this).attr('checked', !$(this).attr('checked'));
    });
    $(".dataTable input[name='checkbox_toggle']").attr("checked", true);
  });

  $('.dataTable tr').click(function(event) {
    if ($(event.target).is('input:checkbox')) return;
    if ($(event.target).is('a, a *')) return;
    if ($(event.target).is('th')) return;
    $(this).find('input:checkbox').trigger('click');
  });
</script>

<?php
  echo $system->functions->form_draw_form_end();
  
  echo $system->functions->draw_pagination(ceil($system->database->num_rows($orders_status_query)/$system->settings->get('data_table_rows_per_page')));
?>