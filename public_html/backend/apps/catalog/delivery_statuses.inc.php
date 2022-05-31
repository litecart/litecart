<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  document::$snippets['title'][] = language::translate('title_delivery_statuses', 'Delivery Statuses');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
  breadcrumbs::add(language::translate('title_delivery_statuses', 'Delivery Statuses'));

// Table Rows
  $delivery_statuses = [];

  $delivery_statuses_query = database::query(
    "select ds.id, dsi.name from ". DB_TABLE_PREFIX ."delivery_statuses ds
    left join ". DB_TABLE_PREFIX ."delivery_statuses_info dsi on (ds.id = dsi.delivery_status_id and dsi.language_code = '". database::input(language::$selected['code']) ."')
    order by dsi.name asc;"
  );
  if ($_GET['page'] > 1) database::seek($delivery_statuses_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($delivery_status = database::fetch($delivery_statuses_query)) {
    $delivery_statuses[] = $delivery_status;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($delivery_statuses_query);

// Pagination
  $num_pages = ceil($num_rows / settings::get('data_table_rows_per_page'));
?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_delivery_statuses', 'Delivery Statuses'); ?>
    </div>
  </div>

  <div class="card-action">
    <?php echo functions::form_draw_link_button(document::ilink(__APP__.'/edit_delivery_status'), language::translate('title_create_new_status', 'Create New Status'), '', 'add'); ?>
  </div>

  <?php echo functions::form_draw_form_begin('delivery_statuses_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th width="100%"><?php echo language::translate('title_name', 'Name'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($delivery_statuses as $delivery_status) { ?>
        <tr>
          <td><?php echo functions::form_draw_checkbox('delivery_statuses[]', $delivery_status['id']); ?></td>
          <td><?php echo $delivery_status['id']; ?></td>
          <td><a href="<?php echo document::href_ilink(__APP__.'/edit_delivery_status', ['delivery_status_id' => $delivery_status['id']]); ?>"><?php echo $delivery_status['name']; ?></a></td>
          <td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_delivery_status', ['delivery_status_id' => $delivery_status['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>

      <tfoot>
        <tr>
        <td colspan="4"><?php echo language::translate('title_delivery_statuses', 'Delivery Statuses'); ?>: <?php echo language::number_format($num_rows); ?></td>
      </tr>
    </table>

  <?php echo functions::form_draw_form_end(); ?>

  <?php if ($num_pages > 1) { ?>
  <div class="card-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
  <?php } ?>
</div>
