<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
?>
<div style="float: right;"><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_quantity_unit'), true), language::translate('title_add_new_unit', 'Add New Unit'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_quantity_units', 'Quantity Units'); ?></h1>

<?php echo functions::form_draw_form_begin('quantity_units_form', 'post'); ?>
<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle'); ?></th>
    <th><?php echo language::translate('title_id', 'ID'); ?></th>
    <th><?php echo language::translate('title_name', 'Name'); ?></th>
    <th width="100%"><?php echo language::translate('title_description', 'Description'); ?></th>
    <th>&nbsp;</th>
  </tr>
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
  <tr class="row">
    <td><?php echo functions::form_draw_checkbox('quantity_units['. $quantity_unit['id'] .']', $quantity_unit['id']); ?></td>
    <td><?php echo $quantity_unit['id']; ?></td>
    <td><a href="<?php echo document::href_link('', array('doc' => 'edit_quantity_unit', 'quantity_unit_id' => $quantity_unit['id']), true); ?>"><?php echo $quantity_unit['name']; ?></a></td>
    <td><?php echo $quantity_unit['description']; ?></td>
    <td style="text-align: right;"><a href="<?php echo document::href_link('', array('doc' => 'edit_quantity_unit', 'quantity_unit_id' => $quantity_unit['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
  </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="5"><?php echo language::translate('title_quantity_units', 'Quantity Units'); ?>: <?php echo database::num_rows($quantity_units_query); ?></td>
  </tr>
</table>

<script>
  $(".dataTable .checkbox-toggle").click(function() {
    $(this).closest("form").find(":checkbox").each(function() {
      $(this).attr('checked', !$(this).attr('checked'));
    });
    $(".dataTable .checkbox-toggle").attr("checked", true);
  });

  $('.dataTable tr').click(function(event) {
    if ($(event.target).is('input:checkbox')) return;
    if ($(event.target).is('a, a *')) return;
    if ($(event.target).is('th')) return;
    $(this).find('input:checkbox').trigger('click');
  });
</script>
<?php
  echo functions::form_draw_form_end();

  echo functions::draw_pagination(ceil(database::num_rows($quantity_units_query)/settings::get('data_table_rows_per_page')));
?>