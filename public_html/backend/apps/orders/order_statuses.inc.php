<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  document::$snippets['title'][] = language::translate('title_order_statuses', 'Order Statuses');

  breadcrumbs::add(language::translate('title_order_statuses', 'Order Statuses'));


  if (!empty($_POST['change'])) {

    try {

      if (empty($_POST['from_order_status_id'])) throw new Exception(language::translate('error_missing_from_order_status', 'Please select a from order status'));
      if (empty($_POST['to_order_status_id'])) throw new Exception(language::translate('error_missing_to_order_status', 'Please select a to order status'));

      database::query(
        "update ". DB_TABLE_PREFIX ."orders
        set order_status_id = ". (int)$_POST['to_order_status_id'] ."
        where order_status_id = ". (int)$_POST['from_order_status_id'] .";"
      );

      $affected_rows = database::affected_rows();

      notices::add('success', strtr(language::translate('success_changed_order_status_for_n_orders', 'Changed order status for %num orders'), ['%num' => $affected_rows]));

      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows
  $order_statuses = [];

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

  if ($_GET['page'] > 1) database::seek($order_statuses_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($order_status = database::fetch($order_statuses_query)) {
    if (empty($order_status['icon'])) $order_status['icon'] = 'fa-circle-thin';
    if (empty($order_status['color'])) $order_status['color'] = '#cccccc';

    $order_statuses[] = $order_status;

    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($order_statuses_query);

// Pagination
  $num_pages = ceil($num_rows / settings::get('data_table_rows_per_page'));

  $states = [
    'created' => language::translate('title_created', 'Created'),
    'on_hold' => language::translate('title_on_hold', 'On Hold'),
    'ready' => language::translate('title_ready', 'Ready'),
    'delayed' => language::translate('title_delayed', 'Delayed'),
    'processing' => language::translate('title_processing', 'Processing'),
    'dispatched' => language::translate('title_dispatched', 'Dispatched'),
    'in_transit' => language::translate('title_in_transit', 'In Transit'),
    'delivered' => language::translate('title_delivered', 'Delivered'),
    'returning' => language::translate('title_returning', 'Returning'),
    'returned' => language::translate('title_returned', 'Returned'),
    'cancelled' => language::translate('title_cancelled', 'Cancelled'),
  ];

?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_order_statuses', 'Order Statuses'); ?>
    </div>
  </div>

  <div class="card-action">
    <?php echo functions::form_draw_link_button(document::ilink(__APP__.'/edit_order_status'), language::translate('title_create_new_order_status', 'Create New Order Status'), '', 'add'); ?>
  </div>

  <?php echo functions::form_draw_form_begin('order_statuses_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th></th>
          <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
          <th><?php echo language::translate('title_orders', 'Orders'); ?></th>
          <th><?php echo language::translate('title_status_state', 'State'); ?></th>
          <th><?php echo language::translate('title_sales', 'Sales'); ?></th>
          <th><?php echo language::translate('title_archived', 'Archived'); ?></th>
          <th><?php echo language::translate('title_notify', 'Notify'); ?></th>
          <th><?php echo language::translate('title_track', 'Track'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($order_statuses as $order_status) { ?>
        <tr>
          <td><?php echo functions::form_draw_checkbox('order_statuses['. $order_status['id'] .']', $order_status['id']); ?></td>
          <td><?php echo $order_status['id']; ?></td>
          <td class="text-center"><?php echo functions::draw_fonticon($order_status['icon'], 'style="color: '. $order_status['color'] .';"'); ?></td>
          <td><a href="<?php echo document::href_ilink(__APP__.'/edit_order_status', ['order_status_id' => $order_status['id']]); ?>"><?php echo $order_status['name']; ?></a></td>
          <td class="text-end"><?php echo language::number_format($order_status['num_orders'], 0); ?></td>
          <td><?php echo strtr($order_status['state'], $states); ?></td>
          <td class="text-center"><?php echo !empty($order_status['is_sale']) ? functions::draw_fonticon('fa-check') : '-'; ?></td>
          <td class="text-center"><?php echo !empty($order_status['is_archived']) ? functions::draw_fonticon('fa-check') : '-'; ?></td>
          <td class="text-center"><?php echo !empty($order_status['notify']) ? functions::draw_fonticon('fa-check') : '-'; ?></td>
          <td class="text-center"><?php echo !empty($order_status['is_trackable']) ? functions::draw_fonticon('fa-check') : '-'; ?></td>
          <td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_order_status', ['order_status_id' => $order_status['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>

      <tfoot>
        <tr>
        <td colspan="11"><?php echo language::translate('title_order_statuses', 'Order Statuses'); ?>: <?php echo $num_rows; ?></td>
        </tr>
      </tfoot>
    </table>

  <?php echo functions::form_draw_form_end(); ?>

  <div class="card-body">
    <?php echo functions::form_draw_form_begin('order_statuses_form', 'post'); ?>
      <fieldset>
        <legend>Change order status for orders<?php //echo language::translate('title_', ''); ?></legend>

        <div class="row">
          <div class="col-md-2">
            <label><?php echo language::translate('title_from_order_status', 'From Order Status'); ?></label>
            <?php echo functions::form_draw_order_statuses_list('from_order_status_id', true); ?>
          </div>

          <div class="col-md-2">
            <label><?php echo language::translate('title_to_order_status', 'To Order Status'); ?></label>
            <?php echo functions::form_draw_order_statuses_list('to_order_status_id', true); ?>
          </div>

          <div class="col-md-1">
            <br />
            <?php echo functions::form_draw_button('change', [1, language::translate('title_change', 'Change')], 'submit'); ?>
          </div>
        </div>
      </fieldset>
    <?php echo functions::form_draw_form_end(); ?>
  </div>

  <?php if ($num_pages > 1) { ?>
  <div class="card-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
  <?php } ?>

</div>
