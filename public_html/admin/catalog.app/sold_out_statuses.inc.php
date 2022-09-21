<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  document::$snippets['title'][] = language::translate('title_sold_out_statuses', 'Sold-Out Statuses');

  breadcrumbs::add(language::translate('title_sold_out_statuses', 'Sold-Out Statuses'));

// Table Rows
  $sold_out_statuses = [];

  $sold_out_status_query = database::query(
    "select sos.id, sos.hidden, sos.orderable, sosi.name from ". DB_TABLE_PREFIX ."sold_out_statuses sos
    left join ". DB_TABLE_PREFIX ."sold_out_statuses_info sosi on (sos.id = sosi.sold_out_status_id and sosi.language_code = '". database::input(language::$selected['code']) ."')
    order by sosi.name asc;"
  );

  if ($_GET['page'] > 1) database::seek($sold_out_status_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($sold_out_status = database::fetch($sold_out_status_query)) {
    $sold_out_statuses[] = $sold_out_status;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($sold_out_status_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));
?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_sold_out_statuses', 'Sold Out Statuses'); ?>
    </div>
  </div>

  <div class="card-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::link(WS_DIR_ADMIN, ['doc' => 'edit_sold_out_status'], true), language::translate('title_create_new_status', 'Create New Status'), '', 'add'); ?></li>
    </ul>
  </div>

  <?php echo functions::form_draw_form_begin('sold_out_statuses_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
          <th><?php echo language::translate('title_hidden', 'Hidden'); ?></th>
          <th><?php echo language::translate('title_orderable', 'Orderable'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($sold_out_statuses as $sold_out_status) { ?>
        <tr>
          <td><?php echo functions::form_draw_checkbox('delivery_statuses[]', $sold_out_status['id']); ?></td>
          <td><?php echo $sold_out_status['id']; ?></td>
          <td><a href="<?php echo document::href_link('', ['doc' => 'edit_sold_out_status', 'sold_out_status_id' => $sold_out_status['id']], true); ?>"><?php echo $sold_out_status['name']; ?></a></td>
          <td class="text-center"><?php echo !empty($sold_out_status['hidden']) ? functions::draw_fonticon('fa-check') : ''; ?></td>
          <td class="text-center"><?php echo !empty($sold_out_status['orderable']) ? functions::draw_fonticon('fa-check') : ''; ?></td>
          <td style="text-end"><a href="<?php echo document::href_link('', ['doc' => 'edit_sold_out_status', 'sold_out_status_id' => $sold_out_status['id']], true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="6"><?php echo language::translate('title_sold_out_statuses', 'Sold Out Statuses'); ?>: <?php echo $num_rows; ?></td>
        </tr>
      </tfoot>
    </table>

  <?php echo functions::form_draw_form_end(); ?>

  <?php if ($num_pages > 1) { ?>
  <div class="card-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
  <?php } ?>
</div>
