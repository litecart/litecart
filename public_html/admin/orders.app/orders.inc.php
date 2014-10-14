<?php
  if (!isset($_GET['order_status_id'])) $_GET['order_status_id'] = '';
  if (!isset($_GET['page'])) $_GET['page'] = 1;
  
  functions::draw_fancybox('a.fancybox', array(
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
  
  if (!empty($_POST['order_action']) && !empty($_POST['orders'])) {
    if (!empty($_POST['orders'])) {
      list($module_id, $option_id) = explode(':', $_POST['order_action']);
      $order_action = new mod_order_action();
      $options = $order_action->options();
      echo $order_action->modules[$module_id]->$options[$module_id]['options'][$option_id]['function']($_POST['orders']);
      return;
    } else {
      notices::$data['errors'][] = language::translate('error_must_select_orders', 'You must select orders to perform the operation');
    }
  }
  
?>
<style>
#order-actions li {
  vertical-align: middle;
}
#order-actions li fieldset {
  border: 1px #ccc solid;
}
#order-actions li fieldset legend {
  color: #999;
}
</style>

<div style="float: right;"><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_order', 'redirect' => $_SERVER['REQUEST_URI']), true), language::translate('title_create_new_order', 'Create New Order'), '', 'add'); ?></div>
<div style="float: right; padding-right: 10px;"><?php echo functions::form_draw_order_status_list('order_status_id', true, false, 'onchange="location=(\''. document::link('', array(), true, array('page', 'order_status_id')) .'&order_status_id=\' + this.options[this.selectedIndex].value)"'); ?></div>
<div style="float: right; padding-right: 10px;"><?php echo functions::form_draw_form_begin('search_form', 'get', '', false, 'onsubmit="return false;"') . functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'"  onkeydown=" if (event.keyCode == 13) location=(\''. document::link('', array(), true, array('page', 'query')) .'&query=\' + this.value)"') . functions::form_draw_form_end(); ?></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo language::translate('title_orders', 'Orders'); ?></h1>

<?php echo functions::form_draw_form_begin('orders_form', 'post'); ?>
<table width="100%" class="dataTable">
  <tr class="header">
    <th nowrap="nowrap"><?php echo functions::form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th nowrap="nowrap" align="left"><?php echo language::translate('title_id', 'ID'); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo language::translate('title_customer_name', 'Customer Name'); ?></th>
    <th nowrap="nowrap" align="left"><?php echo language::translate('title_country', 'Country'); ?></th>
    <th nowrap="nowrap" align="left"><?php echo language::translate('title_payment_method', 'Payment Method'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo language::translate('title_amount', 'Amount'); ?></th>
    <th nowrap="nowrap" align="left"><?php echo language::translate('title_date', 'Date'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo language::translate('title_order_status', 'Order Status'); ?></th>
    <th nowrap="nowrap">&nbsp;</th>
  </tr>
<?php
  $orders_query = database::query(
    "select o.*, osi.name as order_status_name from ". DB_TABLE_ORDERS ." o
    left join ". DB_TABLE_ORDER_STATUSES_INFO ." osi on (osi.order_status_id = o.order_status_id and osi.language_code = '". language::$selected['code'] ."')
    where o.id
    ". ((!empty($_GET['query'])) ? "and (o.id = '". database::input($_GET['query']) ."' or o.uid = '". database::input($_GET['query']) ."' or o.customer_email like '%". database::input($_GET['query']) ."%' or o.customer_firstname like '%". database::input($_GET['query']) ."%' or o.customer_lastname like '%". database::input($_GET['query']) ."%')" : "") ."
    ". ((!empty($_GET['order_status_id'])) ? "and o.order_status_id = '". (int)$_GET['order_status_id'] ."'" : "") ."
    order by o.date_created desc;"
  );
  
  if (database::num_rows($orders_query) > 0) {
  
    if ($_GET['page'] > 1) database::seek($orders_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($order = database::fetch($orders_query)) {
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr class="<?php echo $rowclass; ?>"<?php echo ($order['order_status_id'] == 0) ? ' style="opacity: 0.5;"' : false; ?>>
    <td nowrap="nowrap"><?php echo functions::form_draw_checkbox('orders['.$order['id'].']', $order['id'], (isset($_POST['orders']) && in_array($order['id'], $_POST['orders'])) ? $order['id'] : false); ?></td>
    <td nowrap="nowrap" align="left"><?php echo $order['id']; ?></td>
    <td nowrap="nowrap" align="left"><a href="<?php echo document::href_link('', array('doc' => 'edit_order', 'order_id' => $order['id']), true); ?>"><?php echo $order['customer_company'] ? $order['customer_company'] : $order['customer_firstname'] .' '. $order['customer_lastname']; ?><?php echo empty($order['customer_id']) ? ' <em>('. language::translate('title_guest', 'Guest') .')</em>' : ''; ?></a></td>
    <td nowrap="nowrap" align="left"><?php echo functions::reference_get_country_name($order['customer_country_code']); ?></td>
    <td nowrap="nowrap" align="left"><?php echo $order['payment_option_name']; ?></td>
    <td nowrap="nowrap" align="right"><?php echo currency::format($order['payment_due'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
    <td nowrap="nowrap" align="right"><?php echo strftime(language::$selected['format_datetime'], strtotime($order['date_created'])); ?></td>
    <td nowrap="nowrap" align="center"><?php echo ($order['order_status_id'] == 0) ? language::translate('title_unprocessed', 'Unprocessed') : $order['order_status_name']; ?></td>
    <td nowrap="nowrap">
      <a class="fancybox" href="<?php echo document::href_link(WS_DIR_ADMIN . $_GET['app'] .'.app/printable_packing_slip.php', array('order_id' => $order['id'], 'media' => 'print')); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/box.png'; ?>" width="16" height="16" border="0" align="absbottom" /></a>
      <a class="fancybox" href="<?php echo document::href_link(WS_DIR_ADMIN . $_GET['app'] .'.app/printable_order_copy.php', array('order_id' => $order['id'], 'media' => 'print')); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/print.png'; ?>" width="16" height="16" border="0" align="absbottom" /></a>
      <a href="<?php echo document::href_link('', array('doc' => 'edit_order', 'order_id' => $order['id'], 'redirect' => $_SERVER['REQUEST_URI']), true); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/edit.png'; ?>" width="16" height="16" align="absbottom" /></a>
    </td>
  </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="9" align="left"><?php echo language::translate('title_orders', 'Orders'); ?>: <?php echo database::num_rows($orders_query); ?></td>
  </tr>
</table>

<p>
  <ul id="order-actions" class="list-horizontal">
<?php
  $order_action = new mod_order_action();
  
  $order_action_options = $order_action->options();
  
  if (!empty($order_action_options)) {
    foreach (array_keys($order_action_options) as $module_id) {
      echo '<li><fieldset>' . PHP_EOL
         . '  <legend>'. $order_action_options[$module_id]['name'] .'</legend>' . PHP_EOL;
      foreach (array_keys($order_action_options[$module_id]['options']) as $option_id) {
        echo '<button name="order_action" value="'. $module_id.':'.$option_id .'">'. $order_action_options[$module_id]['options'][$option_id]['title'] .'</button>' . PHP_EOL;
      }
      echo '</fieldset></li>' . PHP_EOL;
    }
  }
?>
  </ul>
  <script>
  $(".dataTable input[name^='orders[']").change(function() {
    if ($(".dataTable input[name^='orders[']:checked").length > 0) {
      $("#order-actions button").removeAttr('disabled');
    } else {
      $("#order-actions button").attr('disabled', 'disabled');
    }
  });
  $(".dataTable input[name^='orders[']").trigger('change');
  </script>
</p>

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
  echo functions::form_draw_form_end();
  
  echo functions::draw_pagination(ceil(database::num_rows($orders_query)/settings::get('data_table_rows_per_page')));
?>