<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
?>
<ul class="list-inline pull-right">
  <li><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_sold_out_status'), true), language::translate('title_create_new_status', 'Create New Status'), '', 'add'); ?></li>
</ul>

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_sold_out_statuses', 'Sold Out Statuses'); ?></h1>

<?php echo functions::form_draw_form_begin('sold_out_statuses_form', 'post'); ?>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
        <th><?php echo language::translate('title_id', 'ID'); ?></th>
        <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
        <th><?php echo language::translate('title_orderable', 'Orderable'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
<?php

  $sold_out_status_query = database::query(
    "select sos.id, sos.orderable, sosi.name from ". DB_TABLE_SOLD_OUT_STATUSES ." sos
    left join ". DB_TABLE_SOLD_OUT_STATUSES_INFO ." sosi on (sos.id = sosi.sold_out_status_id and sosi.language_code = '". language::$selected['code'] ."')
    order by sosi.name asc;"
  );

  if (database::num_rows($sold_out_status_query) > 0) {

    if ($_GET['page'] > 1) database::seek($sold_out_status_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

    $page_items = 0;
    while ($sold_out_status = database::fetch($sold_out_status_query)) {
?>
      <tr>
        <td><?php echo functions::form_draw_checkbox('delivery_statuses['. $sold_out_status['id'] .']', $sold_out_status['id']); ?></td>
        <td><?php echo $sold_out_status['id']; ?></td>
        <td><a href="<?php echo document::href_link('', array('doc' => 'edit_sold_out_status', 'sold_out_status_id' => $sold_out_status['id']), true); ?>"><?php echo $sold_out_status['name']; ?></a></td>
        <td class="text-center"><?php echo !empty($sold_out_status['orderable']) ? 'x' : ''; ?></td>
        <td style="text-right"><a href="<?php echo document::href_link('', array('doc' => 'edit_sold_out_status', 'sold_out_status_id' => $sold_out_status['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
      </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="5"><?php echo language::translate('title_sold_out_statuses', 'Sold Out Statuses'); ?>: <?php echo database::num_rows($sold_out_status_query); ?></td>
      </tr>
    </tfoot>
  </table>

<?php echo functions::form_draw_form_end(); ?>

<?php echo functions::draw_pagination(ceil(database::num_rows($sold_out_status_query)/settings::get('data_table_rows_per_page'))); ?>