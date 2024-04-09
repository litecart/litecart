<?php

  if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
    $_GET['page'] = 1;
  }

  document::$title[] = language::translate('title_sold_out_statuses', 'Sold-Out Statuses');

  breadcrumbs::add(language::translate('title_sold_out_statuses', 'Sold-Out Statuses'));

// Table Rows, Total Number of Rows, Total Number of Pages
  $sold_out_statuses = database::query(
    "select sos.id, sos.orderable, sosi.name from ". DB_TABLE_PREFIX ."sold_out_statuses sos
    left join ". DB_TABLE_PREFIX ."sold_out_statuses_info sosi on (sos.id = sosi.sold_out_status_id and sosi.language_code = '". database::input(language::$selected['code']) ."')
    order by sosi.name asc;"
  )->fetch_page($_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_sold_out_statuses', 'Sold Out Statuses'); ?>
    </div>
  </div>

  <div class="card-action">
    <?php echo functions::form_button_link(document::ilink(__APP__.'/edit_sold_out_status'), language::translate('title_create_new_status', 'Create New Status'), '', 'add'); ?>
  </div>

  <?php echo functions::form_begin('sold_out_statuses_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
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
          <td><?php echo functions::form_checkbox('delivery_statuses[]', $sold_out_status['id']); ?></td>
          <td><?php echo $sold_out_status['id']; ?></td>
          <td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_sold_out_status', ['sold_out_status_id' => $sold_out_status['id']]); ?>"><?php echo $sold_out_status['name']; ?></a></td>
          <td class="text-center"><?php if (!empty($sold_out_status['hidden'])) echo functions::draw_fonticon('fa-check'); ?></td>
          <td class="text-center"><?php if (!empty($sold_out_status['orderable'])) echo functions::draw_fonticon('fa-check'); ?></td>
          <td style="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_sold_out_status', ['sold_out_status_id' => $sold_out_status['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="6"><?php echo language::translate('title_sold_out_statuses', 'Sold Out Statuses'); ?>: <?php echo language::number_format($num_rows); ?></td>
        </tr>
      </tfoot>
    </table>

  <?php echo functions::form_end(); ?>

  <?php if ($num_pages > 1) { ?>
  <div class="card-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
  <?php } ?>
</div>
