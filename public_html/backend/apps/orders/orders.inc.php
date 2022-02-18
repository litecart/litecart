<?php

  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;
  if (!isset($_GET['order_status_id'])) $_GET['order_status_id'] = '';
  if (empty($_GET['sort'])) $_GET['sort'] = 'date_created';

  if (!empty($_GET['date_from'])) $_GET['date_from'] = date('Y-m-d', strtotime($_GET['date_from']));
  if (!empty($_GET['date_to'])) $_GET['date_to'] = date('Y-m-d', strtotime($_GET['date_to']));

  if (!empty($_GET['date_from']) && !empty($_GET['date_to']) && $_GET['date_from'] > $_GET['date_to']) {
    list($_GET['date_from'], $_GET['date_to']) = [$_GET['date_to'], $_GET['date_from']];
  }

  if (!empty($_GET['date_from'])) {
    $date_first_order = database::fetch(database::query("select min(date_created) from ". DB_TABLE_PREFIX ."orders limit 1;"));
    $date_first_order = date('Y-m-d', strtotime($date_first_order['min(date_created)']));
    if (empty($date_first_order)) $date_first_order = date('Y-m-d');
    if ($_GET['date_from'] < $date_first_order) $_GET['date_from'] = $date_first_order;
  }

  if (!empty($_GET['date_from']) && $_GET['date_from'] > date('Y-m-d')) $_GET['date_from'] = date('Y-m-d');
  if (!empty($_GET['date_to']) && $_GET['date_to'] > date('Y-m-d')) $_GET['date_to'] = date('Y-m-d');

  document::$snippets['title'][] = language::translate('title_orders', 'Orders');

  breadcrumbs::add(language::translate('title_orders', 'Orders'));

  if (isset($_POST['star']) || isset($_POST['unstar'])) {
    database::query(
      "update ". DB_TABLE_PREFIX ."orders
      set starred = ". (isset($_POST['star']) ? 1 : 0) ."
      where id = ". (int)$_POST['order_id'] ."
      limit 1;"
    );
    exit;
  }

  if (!empty($_POST['set_order_status'])) {
    try {
      if (empty($_POST['orders'])) throw new Exception(language::translate('error_must_select_orders', 'You must select orders to perform the operation'));

      foreach ($_POST['orders'] as $order_id) {
        $order = new ent_order($order_id);
        $order->data['order_status_id'] = $_POST['order_status_id'];
        $order->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));

      header('Location: '. $_SERVER['REQUEST_URI']);
      exit;

    } catch(Exception $e) {
      notices::$data['errors'][] = $e->getMessage();
    }
  }

  if (!empty($_POST['order_action'])) {

    try {

      if (empty($_POST['orders'])) throw new Exception(language::translate('error_must_select_orders', 'You must select orders to perform the operation'));

      list($module_id, $action_id) = explode(':', $_POST['order_action']);

      $order_action = new mod_order();

      $actions = $order_action->actions();

      if (!method_exists($order_action->modules[$module_id], $actions[$module_id]['actions'][$action_id]['function'])) {
        throw new Exception(language::translate('error_method_doesnt_exist', 'The method doesn\'t exist'));
      }

      sort($_POST['orders']);

      if ($result = call_user_func([$order_action->modules[$module_id], $actions[$module_id]['actions'][$action_id]['function']], $_POST['orders'])) {
        echo $result;
        return;
      }

      header('Location: '. $_SERVER['REQUEST_URI']);
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows
  $orders = [];

  if (!empty($_GET['query'])) {
    $sql_where_query = [
      "o.id = '". database::input($_GET['query']) ."'",
      "o.reference like '%". database::input($_GET['query']) ."%'",
      "o.customer_email like '%". database::input($_GET['query']) ."%'",
      "o.customer_tax_id like '%". database::input($_GET['query']) ."%'",
      "concat(o.customer_company, '\\n', o.customer_firstname, ' ', o.customer_lastname, '\\n', o.customer_address1, '\\n', o.customer_address2, '\\n', o.customer_postcode, '\\n', o.customer_city) like '%". database::input($_GET['query']) ."%'",
      "concat(o.shipping_company, '\\n', o.shipping_firstname, ' ', o.shipping_lastname, '\\n', o.shipping_address1, '\\n', o.shipping_address2, '\\n', o.shipping_postcode, '\\n', o.shipping_city) like '%". database::input($_GET['query']) ."%'",
      "o.payment_option_id like '%". database::input($_GET['query']) ."%'",
      "o.payment_option_name like '%". database::input($_GET['query']) ."%'",
      "o.payment_transaction_id like '". database::input($_GET['query']) ."'",
      "o.shipping_option_id like '%". database::input($_GET['query']) ."%'",
      "o.shipping_option_name like '%". database::input($_GET['query']) ."%'",
      "o.shipping_tracking_id like '". database::input($_GET['query']) ."'",
      "o.id in (
        select order_id from ". DB_TABLE_PREFIX ."orders_items
        where name like '%". database::input($_GET['query']) ."%'
        or sku like '%". database::input($_GET['query']) ."%'
      )",
    ];
  }

  switch($_GET['sort']) {
    case 'id':
      $sql_sort = "o.starred desc, o.id desc";
      break;
    case 'country':
      $sql_sort = "o.starred desc, o.customer_country_code";
      break;
    case 'customer':
      $sql_sort = "o.starred desc, if(o.customer_company, o.customer_company, concat(o.customer_firstname, ' ', o.customer_lastname)) asc";
      break;
    case 'order_status':
      $sql_sort = "o.starred desc, field(os.state,'created','on_hold','ready','delayed','processing','dispatched','in_transit','delivered','returning','returned','cancelled'), osi.name";
      break;
    case 'payment_method':
      $sql_sort = "o.starred desc, o.payment_option_name asc";
      break;
    default:
      $sql_sort = "if(o.starred, 1, 0) desc, o.date_created desc, o.id desc";
      break;
  }

  switch($_GET['order_status_id']) {
    case '':
      $sql_where_order_status = "and (os.is_archived is null or os.is_archived = 0 or unread = 1)";
      break;
    case 'archived':
      $sql_where_order_status = "and (os.is_archived = 1)";
      break;
    case 'all':
      break;
    default:
      $sql_where_order_status =  "and o.order_status_id = ". (int)$_GET['order_status_id'];
      break;
  }

  $orders_query = database::query(
    "select o.*, os.color as order_status_color, os.icon as order_status_icon, osi.name as order_status_name from ". DB_TABLE_PREFIX ."orders o
    left join ". DB_TABLE_PREFIX ."order_statuses os on (os.id = o.order_status_id)
    left join ". DB_TABLE_PREFIX ."order_statuses_info osi on (osi.order_status_id = o.order_status_id and osi.language_code = '". database::input(language::$selected['code'])."')
    where o.id
    ". (!empty($sql_where_query) ? "and (". implode(" or ", $sql_where_query) .")" : "") ."
    ". fallback($sql_where_order_status) ."
    ". (!empty($_GET['date_from']) ? "and o.date_created >= '". date('Y-m-d 00:00:00', strtotime($_GET['date_from'])) ."'" : '') ."
    ". (!empty($_GET['date_to']) ? "and o.date_created <= '". date('Y-m-d 23:59:59', strtotime($_GET['date_to'])) ."'" : '') ."
    order by $sql_sort;"
  );

  if ($_GET['page'] > 1) database::seek($orders_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($order = database::fetch($orders_query)) {

    if (empty($order['order_status_id'])) {
      $order['order_status_icon'] = 'fa-minus';
      $order['order_status_color'] = '#ccc';
    }

    if (empty($order['order_status_icon'])) $order['order_status_icon'] = 'fa-circle-thin';
    if (empty($order['order_status_color'])) $order['order_status_color'] = '#ccc';

    $order['css_classes'] = [];
    if (empty($order['order_status_id'])) $order['css_classes'][]= 'semi-transparent';
    if (!empty($order['unread'])) $order['css_classes'][]= 'bold';


    $items_query = database::query(
      "select oi.product_id, oi.sku, oi.quantity, si.quantity as stock_quantity
      from ". DB_TABLE_PREFIX ."orders_items oi
      join ". DB_TABLE_PREFIX ."stock_items si on (si.id = oi.stock_item_id)
      where oi.order_id = ". (int)$order['id'] .";"
    );

    $order['sufficient_stock'] = database::num_rows($items_query) ? true : null;

    while ($item = database::fetch($items_query)) {
      if ($item['quantity'] < $item['stock_quanity']) {
        $order['sufficient_stock'] = false;
        break;
      }
    }

    $orders[] = $order;

    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($orders_query);

// Pagination
  $num_pages = ceil($num_rows / settings::get('data_table_rows_per_page'));

// Order Statuses
  $order_status_options = [
    [
      'label' => language::translate('title_collections', 'Collections'),
      'options' => [
        '' => language::translate('title_current', 'Current Orders'),
        'archived' => language::translate('title_archived_orders', 'Archived Orders'),
        'all' => language::translate('title_all_orders', 'All Orders'),
      ],
    ],
    [
      'label' => language::translate('title_order_statuses', 'Order Statuses'),
      'options' => [],
    ],
  ];

  $order_statuses_query = database::query(
    "select os.*, osi.name, o.num_orders from ". DB_TABLE_PREFIX ."order_statuses os
    left join ". DB_TABLE_PREFIX ."order_statuses_info osi on (os.id = osi.order_status_id and language_code = '". database::input(language::$selected['code']) ."')
    left join (
      select order_status_id, count(id) as num_orders
      from ". DB_TABLE_PREFIX ."orders
      group by order_status_id
    ) o on (o.order_status_id = os.id)
    order by field(state,'created','on_hold','ready','delayed','processing','dispatched','in_transit','delivered','returning','returned','cancelled',''), osi.name asc;"
  );

  while ($order_status = database::fetch($order_statuses_query)) {
    $order_status_options[1]['options'][$order_status['id']] = $order_status['name'] . ' ('. language::number_format($order_status['num_orders']) .')';
  }

// Actions
  $order_actions = [];

  $mod_order = new mod_order();
  if ($modules = $mod_order->actions()) {

    foreach ($modules as $module) {
      $order_actions[] = $module;
    }
  }

?>
<style>
table tr.bold {
  font-weight: bold;
}

table .fa-star-o:hover {
  transform: scale(1.5);
}
table .fa-star:hover {
  transform: scale(1.5);
}

#order-actions li {
  vertical-align: middle;
}
</style>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_orders', 'Orders'); ?>
    </div>
  </div>

  <div class="card-action">
    <?php echo functions::form_draw_link_button(document::ilink(__APP__.'/edit_order', ['redirect_url' => $_SERVER['REQUEST_URI']]), language::translate('title_create_new_order', 'Create New Order'), '', 'add'); ?>
  </div>

  <?php echo functions::form_draw_form_begin('search_form', 'get'); ?>
    <div class="card-filter">
      <div class="expandable"><?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword').'"'); ?></div>
      <?php echo functions::form_draw_select_optgroup_field('order_status_id', $order_status_options, true, 'style="width: auto;"'); ?>
      <div class="input-group" style="max-width: 380px;">
        <?php echo functions::form_draw_date_field('date_from', true); ?>
        <span class="input-group-text"> - </span>
        <?php echo functions::form_draw_date_field('date_to', true); ?>
      </div>
      <?php echo functions::form_draw_button('filter', language::translate('title_search', 'Search'), 'submit'); ?>
    </div>
  <?php echo functions::form_draw_form_end(); ?>

  <?php echo functions::form_draw_form_begin('orders_form', 'post'); ?>

    <table class="table table-striped table-hover table-sortable data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th></th>
          <th data-sort="id" class="text-end"><?php echo language::translate('title_id', 'ID'); ?></th>
          <th></th>
          <th data-sort="customer" class="main"><?php echo language::translate('title_customer_name', 'Customer Name'); ?></th>
          <th data-sort="country"><?php echo language::translate('title_country', 'Country'); ?></th>
          <th data-sort="payment_method"><?php echo language::translate('title_payment_method', 'Payment Method'); ?></th>
          <th data-sort="order_status"><?php echo language::translate('title_order_status', 'Order Status'); ?></th>
          <th class="text-center"><?php echo language::translate('title_amount', 'Amount'); ?></th>
          <th><?php echo language::translate('title_in_stock', 'In Stock'); ?></th>
          <th data-sort="date_created"><?php echo language::translate('title_date', 'Date'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($orders as $order) { ?>
        <tr class="<?php echo implode(' ', $order['css_classes']); ?>" data-id="<?php echo $order['id']; ?>">
          <td><?php echo functions::form_draw_checkbox('orders[]', $order['id'], (isset($_POST['orders']) && in_array($order['id'], $_POST['orders'])) ? $order['id'] : false); ?></td>
          <td><?php echo functions::draw_fonticon($order['order_status_icon'].' fa-fw', 'style="color: '. $order['order_status_color'] .';"'); ?></td>
          <td class="text-end"><?php echo $order['id']; ?></td>
          <td><?php echo (!empty($order['starred'])) ? functions::draw_fonticon('fa-star', 'style="color: #f2b01e;"') : functions::draw_fonticon('fa-star-o', 'style="color: #ccc;"'); ?></td>
          <td><a href="<?php echo document::href_ilink(__APP__.'/edit_order', ['order_id' => $order['id'], 'redirect_url' => $_SERVER['REQUEST_URI']]); ?>"><?php echo $order['customer_company'] ? $order['customer_company'] : $order['customer_firstname'] .' '. $order['customer_lastname']; ?><?php echo empty($order['customer_id']) ? ' <em>('. language::translate('title_guest', 'Guest') .')</em>' : ''; ?></a> <span style="opacity: 0.5;"><?php echo $order['customer_tax_id']; ?></span></td>
          <td><?php echo !empty($order['customer_country_code']) ? reference::country($order['customer_country_code'])->name : ''; ?></td>
          <td><?php echo $order['payment_option_name']; ?></td>
          <td><?php echo $order['order_status_id'] ? $order['order_status_name'] : language::translate('title_uncompleted', 'Uncompleted'); ?></td>
          <td class="text-end"><?php echo currency::format($order['total'], false, $order['currency_code'], $order['currency_value']); ?></td>
          <td class="text-center"><?php if (!is_null($order['sufficient_stock'])) echo $order['sufficient_stock'] ? functions::draw_fonticon('fa-check', 'style="color: #88cc44;"') : functions::draw_fonticon('fa-times', 'style="color: #ff6644;"'); ?></td>
          <td class="text-end"><?php echo language::strftime(language::$selected['format_datetime'], strtotime($order['date_created'])); ?></td>
          <td>
            <a class="btn btn-default btn-sm" href="<?php echo document::href_ilink('f:printable_packing_slip', ['order_id' => $order['id'], 'public_key' => $order['public_key']]); ?>" target="_blank" title="<?php echo language::translate('title_packing_slip', 'Packing Slip'); ?>"><?php echo functions::draw_fonticon('fa-file-text-o'); ?></a>
            <a class="btn btn-default btn-sm" href="<?php echo document::href_ilink('f:printable_order_copy', ['order_id' => $order['id'], 'public_key' => $order['public_key']]); ?>" target="_blank" title="<?php echo language::translate('title_order_copy', 'Order Copy'); ?>"><?php echo functions::draw_fonticon('fa-print'); ?></a>
            <a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_order', ['order_id' => $order['id'], 'redirect_url' => $_SERVER['REQUEST_URI']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a>
          </td>
        </tr>
        <?php } ?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="12"><?php echo language::translate('title_orders', 'Orders'); ?>: <?php echo language::number_format($num_rows); ?></td>
        </tr>
      </tfoot>
    </table>

    <div class="card-body">
      <ul id="actions" class="list-inline">
        <li>
          <fieldset>
            <legend><?php echo language::translate('title_set_order_status', 'Set Order Status'); ?></legend>
            <div class="form-group">
              <div class="input-group">
                <?php echo functions::form_draw_order_statuses_list('order_status_id', true); ?>
                <button class="btn btn-default" name="set_order_status" value="true" type="submit" formtarget="_self"><?php echo language::translate('title_set', 'Set'); ?></button>
              </div>
            </div>
          </fieldset>
        </li>
        <?php if ($order_actions) foreach ($order_actions as $module) { ?>
        <li>
          <fieldset title="<?php echo functions::escape_html($module['description']); ?>">
            <legend><?php echo $module['name']; ?></legend>
            <div class="btn-group">
              <?php foreach ($module['actions'] as $action) echo functions::form_draw_button('order_action', [$module['id'].':'.$action['id'], $action['title']], 'submit', 'formtarget="'. functions::escape_html($action['target']) .'" title="'. functions::escape_html($action['description']) .'"'); ?>
            </div>
          </fieldset>
        </li>
        <?php } ?>
      </ul>
    </div>

  <?php echo functions::form_draw_form_end(); ?>

  <?php if ($num_pages > 1) { ?>
  <div class="card-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
  <?php } ?>
</div>

<script>
  $('input[name="query"]').keypress(function(e) {
    if (e.which == 13) {
      e.preventDefault();
      $(this).closest('form').submit();
    }
  });

  $('form[name="search_form"] select').change(function(){
    $(this).closest('form').submit();
  });

  $('.data-table input[type="checkbox"]').change(function() {
    $('#actions fieldset').prop('disabled', !$('.data-table [type="checkbox"]:checked').length);
  }).first().trigger('change');

  $('table').on('click', '.fa-star-o', function(e){
    e.stopPropagation();
    var star = this;
    $.post('', 'star&order_id='+$(star).closest('tr').data('id'), function(data) {
      $(star).replaceWith('<?php echo functions::draw_fonticon('fa-star', 'style="color: #f2b01e;"'); ?>');
    });
    return false;
  });

  $('table').on('click', '.fa-star', function(e){
    var star = this;
    $.post('', 'unstar&order_id='+$(star).closest('tr').data('id'), function(data) {
      $(star).replaceWith('<?php echo functions::draw_fonticon('fa-star-o', 'style="color: #ccc;"'); ?>');
    });
    return false;
  });
</script>
