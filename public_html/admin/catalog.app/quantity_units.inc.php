<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
?>
<ul class="list-inline pull-right">
  <li><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_quantity_unit'), true), language::translate('title_add_new_unit', 'Add New Unit'), '', 'add'); ?></li>
</ul>

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_quantity_units', 'Quantity Units'); ?></h1>

<?php echo functions::form_draw_form_begin('quantity_units_form', 'post'); ?>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
        <th><?php echo language::translate('title_id', 'ID'); ?></th>
        <th><?php echo language::translate('title_name', 'Name'); ?></th>
        <th class="main"><?php echo language::translate('title_description', 'Description'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
<?php
  $quantity_units_query = database::query(
    "select qu.id, qui.name, qui.description from ". DB_TABLE_QUANTITY_UNITS ." qu
    left join ". DB_TABLE_QUANTITY_UNITS_INFO ." qui on (qu.id = qui.quantity_unit_id and qui.language_code = '". language::$selected['code'] ."')
    order by qu.priority, qui.name asc;"
  );

  if (database::num_rows($quantity_units_query) > 0) {

    if ($_GET['page'] > 1) database::seek($quantity_units_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

    $page_items = 0;
    while ($quantity_unit = database::fetch($quantity_units_query)) {
?>
    <tr>
      <td><?php echo functions::form_draw_checkbox('quantity_units['. $quantity_unit['id'] .']', $quantity_unit['id']); ?></td>
      <td><?php echo $quantity_unit['id']; ?></td>
      <td><a href="<?php echo document::href_link('', array('doc' => 'edit_quantity_unit', 'quantity_unit_id' => $quantity_unit['id']), true); ?>"><?php echo $quantity_unit['name']; ?></a></td>
      <td><?php echo $quantity_unit['description']; ?></td>
      <td class="text-right"><a href="<?php echo document::href_link('', array('doc' => 'edit_quantity_unit', 'quantity_unit_id' => $quantity_unit['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
    </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="5"><?php echo language::translate('title_quantity_units', 'Quantity Units'); ?>: <?php echo database::num_rows($quantity_units_query); ?></td>
      </tr>
    </tfoot>
  </table>

<?php echo functions::form_draw_form_end(); ?>

<?php echo functions::draw_pagination(ceil(database::num_rows($quantity_units_query)/settings::get('data_table_rows_per_page'))); ?>