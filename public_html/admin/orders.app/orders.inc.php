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
  
  if (!empty($_POST['order_action'])) {
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
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_orders', 'Orders'); ?></h1>

<?php echo functions::form_draw_form_begin('orders_form', 'post'); ?>
<table width="100%" class="dataTable">
  <tr class="header">
    <th><?php echo functions::form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th><?php echo language::translate('title_id', 'ID'); ?></th>
    <th><?php echo language::translate('title_customer_name', 'Customer Name'); ?></th>
    <th width="100%"><?php echo language::translate('title_tax_id', 'Tax ID'); ?></th>
    <th><?php echo language::translate('title_country', 'Country'); ?></th>
    <th><?php echo language::translate('title_payment_method', 'Payment Method'); ?></th>
    <th style="text-align: center;"><?php echo language::translate('title_tax', 'Tax'); ?></th>
    <th style="text-align: center;"><?php echo language::translate('title_amount', 'Amount'); ?></th>
    <th><?php echo language::translate('title_date', 'Date'); ?></th>
    <th style="text-align: center;"><?php echo language::translate('title_order_status', 'Order Status'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php
  if (!empty($_GET['query'])) {
    $sql_find = array(
      "o.id = '". database::input($_GET['query']) ."'",
      "o.uid = '". database::input($_GET['query']) ."'",
      "o.customer_email like '%". database::input($_GET['query']) ."%'",
      "o.customer_firstname like '%". database::input($_GET['query']) ."%'",
      "o.customer_lastname like '%". database::input($_GET['query']) ."%'",
      "o.payment_transaction_id like '%". database::input($_GET['query']) ."%'",
      "o.shipping_tracking_id like '%". database::input($_GET['query']) ."%'",
    );
  }
  
  $orders_query = database::query(
    "select o.*, os.color as order_status_color, os.icon as order_status_icon, osi.name as order_status_name from ". DB_TABLE_ORDERS ." o
    left join ". DB_TABLE_ORDER_STATUSES ." os on (os.id = o.order_status_id)
    left join ". DB_TABLE_ORDER_STATUSES_INFO ." osi on (osi.order_status_id = o.order_status_id and osi.language_code = '". language::$selected['code'] ."')
    where o.id
    ". ((!empty($_GET['order_status_id'])) ? "and o.order_status_id = '". (int)$_GET['order_status_id'] ."'" : "") ."
    ". ((!empty($sql_find)) ? "and (". implode(" or ", $sql_find) .")" : "") ."
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
      
      if (empty($order['order_status_id'])) {
        $order['order_status_icon'] = 'minus';
        $order['order_status_color'] = '#cccccc';
      }
      
      if (empty($order['order_status_icon'])) $order['order_status_icon'] = 'circle-thin';
      if (empty($order['order_status_color'])) $order['order_status_color'] = '#cccccc';
?>
  <tr class="<?php echo $rowclass; ?><?php echo ($order['order_status_id'] == 0) ? ' semi-transparent' : ''; ?>">
    <td><?php echo functions::draw_fonticon($order['order_status_icon'], 'style="color: '. $order['order_status_color'] .';"'); ?> <?php echo functions::form_draw_checkbox('orders['.$order['id'].']', $order['id'], (isset($_POST['orders']) && in_array($order['id'], $_POST['orders'])) ? $order['id'] : false); ?></td>
    <td><?php echo $order['id']; ?></td>
    <td><a href="<?php echo document::href_link('', array('doc' => 'edit_order', 'order_id' => $order['id']), true); ?>"><?php echo $order['customer_company'] ? $order['customer_company'] : $order['customer_firstname'] .' '. $order['customer_lastname']; ?><?php echo empty($order['customer_id']) ? ' <em>('. language::translate('title_guest', 'Guest') .')</em>' : ''; ?></a></td>
    <td><?php echo $order['customer_tax_id']; ?></td>
    <td><?php echo functions::reference_get_country_name($order['customer_country_code']); ?></td>
    <td><?php echo $order['payment_option_name']; ?></td>
    <td style="text-align: right;"><?php echo currency::format($order['tax_total'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
    <td style="text-align: right;"><?php echo currency::format($order['payment_due'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
    <td style="text-align: right;"><?php echo strftime(language::$selected['format_datetime'], strtotime($order['date_created'])); ?></td>
    <td style="text-align: center;"><?php echo ($order['order_status_id'] == 0) ? language::translate('title_unprocessed', 'Unprocessed') : $order['order_status_name']; ?></td>
    <td>
      <a class="fancybox" href="<?php echo document::href_link(WS_DIR_ADMIN . $_GET['app'] .'.app/printable_packing_slip.php', array('order_id' => $order['id'], 'media' => 'print')); ?>"><?php echo functions::draw_fonticon('fa-file-text-o'); ?></a>
      <a class="fancybox" href="<?php echo document::href_link(WS_DIR_ADMIN . $_GET['app'] .'.app/printable_order_copy.php', array('order_id' => $order['id'], 'media' => 'print')); ?>"><?php echo functions::draw_fonticon('fa-print'); ?></a>
      <a href="<?php echo document::href_link('', array('doc' => 'edit_order', 'order_id' => $order['id'], 'redirect' => $_SERVER['REQUEST_URI']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a>
    </td>
  </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="11"><?php echo language::translate('title_orders', 'Orders'); ?>: <?php echo database::num_rows($orders_query); ?></td>
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
        echo '<button name="order_action" value="'. $module_id.':'.$option_id .'" type="submit" formtarget="'. (!empty($order_action_options[$module_id]['options'][$option_id]['target']) ? $order_action_options[$module_id]['options'][$option_id]['target'] : '_self') .'">'. $order_action_options[$module_id]['options'][$option_id]['title'] .'</button>' . PHP_EOL;
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