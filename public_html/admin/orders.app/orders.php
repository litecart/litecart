<?php
  if (!isset($_GET['order_status_id'])) $_GET['order_status_id'] = '';
  if (!isset($_GET['page'])) $_GET['page'] = 1;
  
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
<div style="float: right;"><a class="button" href="<?php echo $system->document->href_link('', array('doc' => 'edit_order.php'), true); ?>"><?php echo $system->language->translate('title_create_new_order', 'Create New Order'); ?></a></div>
<div style="float: right; padding-right: 10px;"><?php echo $system->functions->form_draw_order_status_list('order_status_id', isset($_GET['order_status_id']) ? $_GET['order_status_id'] : false, 'onchange="location=(\''. $system->document->link('', array(), true, array('page', 'order_status_id')) .'&order_status_id=\' + this.options[this.selectedIndex].value)"'); ?></div>
<div style="float: right; padding-right: 10px;"><?php echo $system->functions->form_draw_input_field('query', isset($_GET['query']) ? $_GET['query'] : $system->language->translate('title_search', 'Search'), 'text', 'style="width: 175px;" onkeydown=" if (event.keyCode == 13) location=(\''. $system->document->link('', array(), true, array('page', 'query')) .'&query=\' + this.value)"'); ?></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" border="0" align="absmiddle" style="margin-right: 10px;" /><?php echo $system->language->translate('title_orders', 'Orders'); ?></h1>

<script>
  $("input[name=query]").live("click", function(event) {
    if ($(this).val() == "<?php echo $system->language->translate('title_search', 'Search'); ?>") {
      $(this).val("");
    }
  });
  $("input[name=query]").live("blur", function(event) {
    if ($(this).val() == "") {
      $(this).val("<?php echo $system->language->translate('title_search', 'Search'); ?>");
    }
  });
</script>

<?php echo $system->functions->form_draw_form_begin('orders_form', 'post'); ?>
<table cellpadding="5" cellspacing="0" border="0" width="100%" class="dataTable">
  <tr class="header">
    <th nowrap="nowrap"><?php echo $system->functions->form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_customer_name', 'Customer Name'); ?></th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_country', 'Country'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_order_status', 'Order Status'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_amount', 'Amount'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_date', 'Date'); ?></th>
    <th nowrap="nowrap">&nbsp;</th>
  </tr>
<?php
  $orders_query = $system->database->query(
    "select o.*, osi.name as order_status_name from ". DB_TABLE_ORDERS ." o
    left join ". DB_TABLE_ORDERS_STATUS_INFO ." osi on (osi.order_status_id = o.order_status_id and osi.language_code = '". $system->language->selected['code'] ."')
    where o.id
    ". ((!empty($_GET['query'])) ? "and (o.customer_email like '%". $system->database->input($_GET['query']) ."%' or o.customer_firstname like '%". $system->database->input($_GET['query']) ."%' or o.customer_lastname like '%". $system->database->input($_GET['query']) ."%')" : "") ."
    ". ((!empty($_GET['order_status_id'])) ? "and o.order_status_id = '". (int)$_GET['order_status_id'] ."'" : "") ."
    order by o.date_created desc;"
  );
  
  if ($system->database->num_rows($orders_query) > 0) {
  
    if ($_GET['page'] > 1) $system->database->seek($orders_query, ($system->settings->get('data_table_rows_per_page', 20) * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($order = $system->database->fetch($orders_query)) {
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr class="<?php echo $rowclass; ?>"<?php echo ($order['order_status_id'] == 0) ? ' style="color: #999;"' : false; ?>>
    <td nowrap="nowrap"><?php echo $system->functions->form_draw_checkbox('orders['.$order['id'].']', $order['id']); ?></td>
    <td nowrap="nowrap" align="left"><?php echo $order['id']; ?></td>
    <td nowrap="nowrap" align="left"><?php echo $order['customer_firstname'] .' '. $order['customer_lastname']; ?><?php echo empty($order['customer_id']) ? ' <em>('. $system->language->translate('title_guest', 'Guest') .')</em>' : ''; ?></td>
    <td nowrap="nowrap" align="left"><?php echo $system->functions->reference_get_country_name($order['customer_country_code']); ?></td>
    <td nowrap="nowrap" align="right"><?php echo ($order['order_status_id'] == 0) ? $system->language->translate('title_uncompleted', 'Uncompleted') : $order['order_status_name']; ?></td>
    <td nowrap="nowrap" align="right"><?php echo $system->currency->format($order['payment_due'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
    <td nowrap="nowrap" align="right"><?php echo strftime($system->language->selected['format_datetime'], strtotime($order['date_created'])); ?></td>
    <td nowrap="nowrap"><a class="fancybox" href="<?php echo $system->document->href_link(WS_DIR_ADMIN . $_GET['app'] .'.app/printable_packing_slip.php', array('order_id' => $order['id'], 'media' => 'print')); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/box.png'; ?>" width="16" height="16" border="0" align="absbottom" /></a> <a class="fancybox" href="<?php echo $system->document->href_link(WS_DIR_ADMIN . $_GET['app'] .'.app/printable_order_copy.php', array('order_id' => $order['id'], 'media' => 'print')); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/printer.png'; ?>" width="16" height="16" border="0" align="absbottom" /></a> <a href="<?php echo $system->document->href_link('', array('doc' => 'edit_order.php', 'order_id' => $order['id']), true); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/edit.png'; ?>" width="16" height="16" border="0" align="absbottom" /></a></td>
  </tr>
<?php
      if (++$page_items == $system->settings->get('data_table_rows_per_page', 20)) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="8" align="left"><?php echo $system->language->translate('title_orders', 'Orders'); ?>: <?php echo $system->database->num_rows($orders_query); ?></td>
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
    if ($(event.target).is('a *')) return;
    if ($(event.target).is('th')) return;
    $(this).find('input:checkbox').trigger('click');
  });
</script>
<?php
  echo $system->functions->form_draw_form_end();
  
  echo $system->functions->draw_pagination(ceil($system->database->num_rows($orders_query)/$system->settings->get('data_table_rows_per_page', 20)));
?>