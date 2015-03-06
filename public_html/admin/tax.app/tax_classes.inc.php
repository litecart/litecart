<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
?>
<div style="float: right;"><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_tax_class'), true), language::translate('title_add_new_tax_class', 'Add New Tax Class'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?><?php echo language::translate('title_tax_classs', 'Tax Classes'); ?></h1>

<?php echo functions::form_draw_form_begin('tax_classs_form', 'post'); ?>
<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th><?php echo functions::form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th><?php echo language::translate('title_id', 'ID'); ?></th>
    <th><?php echo language::translate('title_name', 'Name'); ?></th>
    <th width="100%"><?php echo language::translate('title_description', 'Description'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php

  $tax_classses_query = database::query(
    "select * from ". DB_TABLE_TAX_CLASSES ."
    order by name asc;"
  );

  if (database::num_rows($tax_classses_query) > 0) {
    
  // Jump to data for current page
    if ($_GET['page'] > 1) database::seek($tax_classses_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($tax_class = database::fetch($tax_classses_query)) {
    
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr class="<?php echo $rowclass; ?>">
    <td><?php echo functions::form_draw_checkbox('tax_classes['. $tax_class['id'] .']', $tax_class['id']); ?></td>
    <td><?php echo $tax_class['id']; ?></td>
    <td><a href="<?php echo document::href_link('', array('doc' => 'edit_tax_class', 'tax_class_id' => $tax_class['id']), true); ?>"><?php echo $tax_class['name']; ?></a></td>
    <td style="color: #999;"><?php echo $tax_class['description']; ?></td>
    <td style="text-align: right;"><a href="<?php echo document::href_link('', array('doc' => 'edit_tax_class', 'tax_class_id' => $tax_class['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fontawesome_icon('pencil'); ?></a></td>
  </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="5"><?php echo language::translate('title_tax_classes', 'Tax Classes'); ?>: <?php echo database::num_rows($tax_classses_query); ?></td>
  </tr>
</table>

<script>
  $(".dataTable input[name='checkbox_toggle']").click(function() {
    $(this).closest("form").find(":checkbox").each(function() {
      $(this).attr('checked', !$(this).attr('checked'));
    });
    $(".dataTable input[name='checkbox_toggle']").attr("checked", true);
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
  
// Display page links
  echo functions::draw_pagination(ceil(database::num_rows($tax_classses_query)/settings::get('data_table_rows_per_page')));
  
?>