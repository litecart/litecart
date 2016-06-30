<?php
  if (!isset($_GET['order_status_id'])) $_GET['order_status_id'] = '';
  if (!isset($_GET['page'])) $_GET['page'] = 1;

  functions::draw_fancybox('a.fancybox', array(
    'type'          => 'iframe',
    'padding'       => '40',
    'width'         => 640,
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

      if (!method_exists($order_action->modules[$module_id], $options[$module_id]['options'][$option_id]['function'])) {
        notices::$data['errors'][] = language::translate('error_method_doesnt_exist', 'The method doesn\'t exist');
        return;
      }

      echo call_user_func(array($order_action->modules[$module_id], $options[$module_id]['options'][$option_id]['function']), $_POST['orders']);
      return;

    } else {
      notices::$data['errors'][] = language::translate('error_must_select_orders', 'You must select orders to perform the operation');
    }
  }

  $payment_options_query = database::query(
    "select distinct payment_option_name
    from ". DB_TABLE_ORDERS ." o
    where payment_option_name != ''
    order by payment_option_name asc"
  );

  $payment_options = array(array('-- '. language::translate('title_payment_method', 'Payment Method') .' --', ''));
  while ($payment_option = database::fetch($payment_options_query)) {
    $payment_options[] = array($payment_option['payment_option_name'], $payment_option['payment_option_name']);
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

<?php echo functions::form_draw_form_begin('filter_form', 'get'); ?>
  <?php echo functions::form_draw_hidden_field('app'); ?>
  <?php echo functions::form_draw_hidden_field('doc'); ?>
  <ul class="list-horizontal" style="float: right;">
    <li><?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'" style="width: 200px;"'); ?></li>
    <li><?php echo strtr(functions::form_draw_order_status_list('order_status_id', true, false, 'onchange="$(this).closest(\'form\').submit();"'), array('-- '. language::translate('title_select', 'Select') .' --' => '-- '. language::translate('title_order_status', 'Order Status') .' --')); ?></li>
    <li><?php echo functions::form_draw_select_field('payment_option_name', $payment_options, true, false, 'onchange="$(this).closest(\'form\').submit();"'); ?></li>
    <li style="padding-left: 1em;">
      <?php echo functions::form_draw_date_field('date_from', true, 'style="width: 130px;"'); ?> - <?php echo functions::form_draw_date_field('date_to', true, 'style="width: 130px;"'); ?>
    </li>
    <li style="padding-left: 1em;"><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_order', 'redirect' => $_SERVER['REQUEST_URI']), true), language::translate('title_create_new_order', 'Create New Order'), '', 'add'); ?></li>
  </ul>
<?php echo functions::form_draw_form_end(); ?>

<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_orders', 'Orders'); ?></h1>

<?php echo functions::form_draw_form_begin('orders_form', 'post'); ?>
<table width="100%" class="dataTable">
  <tr class="header">
    <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle'); ?></th>
    <th></th>
    <th><?php echo language::translate('title_id', 'ID'); ?></th>
    <th width="100%"><?php echo language::translate('title_customer_name', 'Customer Name'); ?></th>
    <th><?php echo language::translate('title_country', 'Country'); ?></th>
    <th><?php echo language::translate('title_payment_method', 'Payment Method'); ?></th>
    <th style="text-align: center;"><?php echo language::translate('title_tax', 'Tax'); ?></th>
    <th style="text-align: center;"><?php echo language::translate('title_amount', 'Amount'); ?></th>
    <th style="text-align: center;"><?php echo language::translate('title_order_status', 'Order Status'); ?></th>
    <th><?php echo language::translate('title_date', 'Date'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php
  if (!empty($_GET['query'])) {
    $sql_where_query = array(
      "o.id = '". database::input($_GET['query']) ."'",
      "o.uid = '". database::input($_GET['query']) ."'",
      "o.customer_email like '%". database::input($_GET['query']) ."%'",
      "o.customer_tax_id like '%". database::input($_GET['query']) ."%'",
      "o.customer_company like '%". database::input($_GET['query']) ."%'",
      "concat(o.customer_firstname, ' ', o.customer_lastname) like '%". database::input($_GET['query']) ."%'",
      "o.payment_transaction_id like '". database::input($_GET['query']) ."'",
      "o.shipping_tracking_id like '". database::input($_GET['query']) ."'",
    );
  }

  $orders_query = database::query(
    "select o.*, os.color as order_status_color, os.icon as order_status_icon, osi.name as order_status_name from ". DB_TABLE_ORDERS ." o
    left join ". DB_TABLE_ORDER_STATUSES ." os on (os.id = o.order_status_id)
    left join ". DB_TABLE_ORDER_STATUSES_INFO ." osi on (osi.order_status_id = o.order_status_id and osi.language_code = '". language::$selected['code'] ."')
    where o.id
    ". (!empty($sql_where_query) ? "and (". implode(" or ", $sql_where_query) .")" : "") ."
    ". (!empty($_GET['order_status_id']) ? "and o.order_status_id = '". (int)$_GET['order_status_id'] ."'" : "and (os.is_archived is null or os.is_archived = 0)") ."
    ". (!empty($_GET['payment_option_name']) ? "and o.payment_option_name = '". database::input($_GET['payment_option_name']) ."'" : '') ."
    ". (!empty($_GET['date_from']) ? "and o.date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', strtotime($_GET['date_from'])), date('d', strtotime($_GET['date_from'])), date('Y', strtotime($_GET['date_from'])))) ."'" : '') ."
    ". (!empty($_GET['date_to']) ? "and o.date_created <= '". date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', strtotime($_GET['date_to'])), date('d', strtotime($_GET['date_to'])), date('Y', strtotime($_GET['date_to'])))) ."'" : '') ."
    order by o.date_created desc, o.id desc;"
  );

  if (database::num_rows($orders_query) > 0) {

    if ($_GET['page'] > 1) database::seek($orders_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

    $page_items = 0;
    while ($order = database::fetch($orders_query)) {

      if (empty($order['order_status_id'])) {
        $order['order_status_icon'] = 'fa-minus';
        $order['order_status_color'] = '#cccccc';
      }

      if (empty($order['order_status_icon'])) $order['order_status_icon'] = 'fa-circle-thin';
      if (empty($order['order_status_color'])) $order['order_status_color'] = '#cccccc';
?>
  <tr class="row<?php echo ($order['order_status_id'] == 0) ? ' semi-transparent' : null; ?>">
    <td><?php echo functions::form_draw_checkbox('orders['.$order['id'].']', $order['id'], (isset($_POST['orders']) && in_array($order['id'], $_POST['orders'])) ? $order['id'] : false); ?></td>
    <td><?php echo functions::draw_fonticon($order['order_status_icon'].' fa-fw', 'style="color: '. $order['order_status_color'] .';"'); ?></td>
    <td><?php echo $order['id']; ?></td>
    <td><a href="<?php echo document::href_link('', array('doc' => 'edit_order', 'order_id' => $order['id']), true); ?>"><?php echo $order['customer_company'] ? $order['customer_company'] : $order['customer_firstname'] .' '. $order['customer_lastname']; ?><?php echo empty($order['customer_id']) ? ' <em>('. language::translate('title_guest', 'Guest') .')</em>' : ''; ?></a> <span style="opacity: 0.5;"><?php echo $order['customer_tax_id']; ?></span></td>
    <td><?php echo functions::reference_get_country_name($order['customer_country_code']); ?></td>
    <td><?php echo $order['payment_option_name']; ?></td>
    <td style="text-align: right;"><?php echo ($order['tax_total'] != 0) ? currency::format($order['tax_total'], false, false, $order['currency_code'], $order['currency_value']) : '-'; ?></td>
    <td style="text-align: right;"><?php echo currency::format($order['payment_due'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
    <td style="text-align: center;"><?php echo ($order['order_status_id'] == 0) ? language::translate('title_unprocessed', 'Unprocessed') : $order['order_status_name']; ?></td>
    <td style="text-align: right;"><?php echo language::strftime(language::$selected['format_datetime'], strtotime($order['date_created'])); ?></td>
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

  echo functions::draw_pagination(ceil(database::num_rows($orders_query)/settings::get('data_table_rows_per_page')));
?>