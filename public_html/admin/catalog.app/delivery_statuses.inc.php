<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
?>
<ul class="list-inline pull-right">
  <li><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_delivery_status'), true), language::translate('title_create_new_status', 'Create New Status'), '', 'add'); ?></li>
</ul>

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_delivery_statuses', 'Delivery Statuses'); ?></h1>

<?php echo functions::form_draw_form_begin('delivery_statuses_form', 'post'); ?>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
        <th><?php echo language::translate('title_id', 'ID'); ?></th>
        <th width="100%"><?php echo language::translate('title_name', 'Name'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
<?php
  $delivery_status_query = database::query(
    "select ds.id, dsi.name from ". DB_TABLE_DELIVERY_STATUSES ." ds
    left join ". DB_TABLE_DELIVERY_STATUSES_INFO ." dsi on (ds.id = dsi.delivery_status_id and dsi.language_code = '". language::$selected['code'] ."')
    order by dsi.name asc;"
  );

  if (database::num_rows($delivery_status_query) > 0) {

    if ($_GET['page'] > 1) database::seek($delivery_status_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

    $page_items = 0;
    while ($delivery_status = database::fetch($delivery_status_query)) {
?>
      <tr>
        <td><?php echo functions::form_draw_checkbox('delivery_statuses['. $delivery_status['id'] .']', $delivery_status['id']); ?></td>
        <td><?php echo $delivery_status['id']; ?></td>
        <td><a href="<?php echo document::href_link('', array('doc' => 'edit_delivery_status', 'delivery_status_id' => $delivery_status['id']), true); ?>"><?php echo $delivery_status['name']; ?></a></td>
        <td style="text-align: right;"><a href="<?php echo document::href_link('', array('doc' => 'edit_delivery_status', 'delivery_status_id' => $delivery_status['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
      </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
    </tbody>
    <tfoot>
      <tr>
      <td colspan="5"><?php echo language::translate('title_delivery_statuses', 'Delivery Statuses'); ?>: <?php echo database::num_rows($delivery_status_query); ?></td>
    </tr>
  </table>

<?php echo functions::form_draw_form_end(); ?>

<?php echo functions::draw_pagination(ceil(database::num_rows($delivery_status_query)/settings::get('data_table_rows_per_page'))); ?>