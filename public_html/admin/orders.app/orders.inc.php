<?php
  if (!isset($_GET['order_status_id'])) $_GET['order_status_id'] = '';
  if (!isset($_GET['page'])) $_GET['page'] = 1;

  if (!empty($_POST['order_action'])) {
    if (!empty($_POST['orders'])) {
      list($module_id, $action_id) = explode(':', $_POST['order_action']);

      $order_action = new mod_order();

      $actions = $order_action->actions();

      if (!method_exists($order_action->modules[$module_id], $actions[$module_id]['actions'][$action_id]['function'])) {
        notices::$data['errors'][] = language::translate('error_method_doesnt_exist', 'The method doesn\'t exist');
        return;
      }

      echo call_user_func(array($order_action->modules[$module_id], $actions[$module_id]['actions'][$action_id]['function']), $_POST['orders']);
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

<?php echo functions::form_draw_form_begin('search_form', 'get') . functions::form_draw_hidden_field('app', true) . functions::form_draw_hidden_field('doc', true); ?>
<ul class="list-inline pull-right">
  <li><?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword').'"'); ?></li>
  <li><?php echo functions::form_draw_order_status_list('order_status_id', true); ?></li>
  <li><?php echo functions::form_draw_select_field('payment_option_name', $payment_options, true); ?></li>
  <li><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_order', 'redirect' => $_SERVER['REQUEST_URI']), true), language::translate('title_create_new_order', 'Create New Order'), '', 'add'); ?></li>
</ul>
<?php echo functions::form_draw_form_end(); ?>

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_orders', 'Orders'); ?></h1>

<?php echo functions::form_draw_form_begin('orders_form', 'post'); ?>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
        <th>&nbsp;</th>
        <th><?php echo language::translate('title_id', 'ID'); ?></th>
        <th class="main"><?php echo language::translate('title_customer_name', 'Customer Name'); ?></th>
        <th><?php echo language::translate('title_country', 'Country'); ?></th>
        <th><?php echo language::translate('title_payment_method', 'Payment Method'); ?></th>
        <th class="text-center"><?php echo language::translate('title_tax', 'Tax'); ?></th>
        <th class="text-center"><?php echo language::translate('title_amount', 'Amount'); ?></th>
        <th class="text-center"><?php echo language::translate('title_order_status', 'Order Status'); ?></th>
        <th><?php echo language::translate('title_date', 'Date'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
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
    <tr class="<?php echo empty($order['order_status_id']) ? 'semi-transparent' : null; ?>">
      <td><?php echo functions::form_draw_checkbox('orders['.$order['id'].']', $order['id'], (isset($_POST['orders']) && in_array($order['id'], $_POST['orders'])) ? $order['id'] : false); ?></td>
      <td><?php echo functions::draw_fonticon($order['order_status_icon'].' fa-fw', 'style="color: '. $order['order_status_color'] .';"'); ?></td>
      <td><?php echo $order['id']; ?></td>
      <td><a href="<?php echo document::href_link('', array('doc' => 'edit_order', 'order_id' => $order['id']), true); ?>"><?php echo $order['customer_company'] ? $order['customer_company'] : $order['customer_firstname'] .' '. $order['customer_lastname']; ?><?php echo empty($order['customer_id']) ? ' <em>('. language::translate('title_guest', 'Guest') .')</em>' : ''; ?></a> <span style="opacity: 0.5;"><?php echo $order['customer_tax_id']; ?></span></td>
      <td><?php echo !empty($order['customer_country_code']) ? reference::country($order['customer_country_code'])->name : ''; ?></td>
      <td><?php echo $order['payment_option_name']; ?></td>
      <td class="text-right"><?php echo ($order['tax_total'] != 0) ? currency::format($order['tax_total'], false, $order['currency_code'], $order['currency_value']) : '-'; ?></td>
      <td class="text-right"><?php echo currency::format($order['payment_due'], false, $order['currency_code'], $order['currency_value']); ?></td>
      <td class="text-center"><?php echo ($order['order_status_id'] == 0) ? language::translate('title_unprocessed', 'Unprocessed') : $order['order_status_name']; ?></td>
      <td class="text-right"><?php echo strftime(language::$selected['format_datetime'], strtotime($order['date_created'])); ?></td>
      <td>
        <a href="<?php echo document::href_link('', array('app' => 'orders', 'doc' => 'printable_packing_slip', 'order_id' => $order['id'], 'media' => 'print')); ?>" target="_blank"><?php echo functions::draw_fonticon('fa-file-text-o'); ?></a>
        <a href="<?php echo document::href_link('', array('app' => 'orders', 'doc' => 'printable_order_copy', 'order_id' => $order['id'], 'media' => 'print')); ?>" target="_blank"><?php echo functions::draw_fonticon('fa-print'); ?></a>
        <a href="<?php echo document::href_link('', array('doc' => 'edit_order', 'order_id' => $order['id'], 'redirect' => $_SERVER['REQUEST_URI']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a>
      </td>
    </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
    </tbody>
    <tfoot>
      <tr>
    <td colspan="11"><?php echo language::translate('title_orders', 'Orders'); ?>: <?php echo database::num_rows($orders_query); ?></td>
      </tr>
    </tfoot>
  </table>

  <p>
    <ul id="order-actions" class="list-inline">
<?php
  $order_action = new mod_order();

  if ($modules = $order_action->actions()) {
    foreach ($modules as $module) {
      echo '<li>' . PHP_EOL
         . '  <fieldset title="'. htmlspecialchars($module['description']) .'">' . PHP_EOL
         . '    <legend>'. $module['name'] .'</legend>' . PHP_EOL
         . '    <div class="btn-group">' . PHP_EOL;
      foreach ($module['actions'] as $action) {
        echo '      ' . functions::form_draw_button('order_action', array($module['id'].':'.$action['id'], $action['title']), 'submit', 'formtarget="'. (!empty($action['target']) ? $action['target'] : '_self') .'"') . PHP_EOL;
      }
      echo '    </div>' . PHP_EOL
         . '  </fieldset>' . PHP_EOL
         . '</li>' . PHP_EOL;
    }
  }
?>
    </ul>
  </p>

<?php echo functions::form_draw_form_end(); ?>

<?php echo functions::draw_pagination(ceil(database::num_rows($orders_query)/settings::get('data_table_rows_per_page'))); ?>

<script>
  $('select[name="order_status_id"] option[value=""]').text('-- <?php echo language::translate('title_order_status', ''); ?> --');

  $('input[name="query"]').keypress(function(e) {
    if (e.which == 13) {
      e.preventDefault();
      $(this).closest('form').submit();
    }
  });

  $('form[name="search_form"] select').change(function(){
    $(this).closest('form').submit();
  });

  $('.data-table input[name^="orders["]').change(function() {
    if ($('.data-table input[name^="orders["]:checked').length > 0) {
      $('#order-actions button').removeAttr('disabled');
    } else {
      $('#order-actions button').attr('disabled', 'disabled');
    }
  }).trigger('change');
</script>