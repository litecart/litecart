<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  document::$snippets['title'][] = language::translate('title_order_statuses', 'Order Statuses');

  breadcrumbs::add(language::translate('title_order_statuses', 'Order Statuses'));

// Table Rows
  $order_statuses = [];

  $order_statuses_query = database::query(
    "select os.*, osi.name, os.priority from ". DB_TABLE_PREFIX ."order_statuses os
    left join ". DB_TABLE_PREFIX ."order_statuses_info osi on (os.id = osi.order_status_id and language_code = '". database::input(language::$selected['code']) ."')
    order by os.priority, osi.name asc;"
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
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_order_statuses', 'Order Statuses'); ?>
  </div>

  <div class="panel-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::link(WS_DIR_ADMIN, ['doc' => 'edit_order_status'], true), language::translate('title_create_new_order_status', 'Create New Order Status'), '', 'add'); ?></li>
    </ul>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('order_statuses_form', 'post'); ?>

      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th></th>
          <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
          <th><?php echo language::translate('title_sales', 'Sales'); ?></th>
          <th><?php echo language::translate('title_archived', 'Archived'); ?></th>
          <th><?php echo language::translate('title_notify', 'Notify'); ?></th>
          <th><?php echo language::translate('title_priority', 'Priority'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($order_statuses as $order_status) { ?>
        <tr>
          <td><?php echo functions::form_draw_checkbox('order_statuses[]', $order_status['id']); ?></td>
          <td><?php echo $order_status['id']; ?></td>
          <td><?php echo functions::draw_fonticon($order_status['icon'], 'style="color: '. $order_status['color'] .';"'); ?></td>
          <td><a href="<?php echo document::href_link('', ['doc' => 'edit_order_status', 'order_status_id' => $order_status['id']], true); ?>"><?php echo $order_status['name']; ?></a></td>
          <td class="text-center"><?php echo !empty($order_status['is_sale']) ? functions::draw_fonticon('fa-check') : ''; ?></td>
          <td class="text-center"><?php echo empty($order_status['is_archived']) ? '' : functions::draw_fonticon('fa-check'); ?></td>
          <td class="text-center"><?php echo !empty($order_status['notify']) ? functions::draw_fonticon('fa-check') : ''; ?></td>
          <td class="text-center"><?php echo $order_status['priority']; ?></td>
          <td class="text-end"><a href="<?php echo document::href_link('', ['doc' => 'edit_order_status', 'order_status_id' => $order_status['id']], true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>

      <tfoot>
        <tr>
        <td colspan="9"><?php echo language::translate('title_order_statuses', 'Order Statuses'); ?>: <?php echo $num_rows; ?></td>
        </tr>
      </tfoot>
    </table>

    <?php echo functions::form_draw_form_end(); ?>
  </div>

  <div class="panel-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
</div>
