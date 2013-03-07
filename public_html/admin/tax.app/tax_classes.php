<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
?>
<div style="float: right;"><a class="button" href="<?php echo $system->document->href_link('', array('doc' => 'edit_tax_class.php'), true); ?>"><?php echo $system->language->translate('title_add_new_tax_class', 'Add New Tax Class'); ?></a></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle;" style="margin-right: 10px;" /><?php echo $system->language->translate('title_tax_classs', 'Tax Classes'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('tax_classs_form', 'post'); ?>
<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th><?php echo $system->functions->form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_name', 'Name'); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_description', 'Description'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php

  $tax_classses_query = $system->database->query(
    "select * from ". DB_TABLE_TAX_CLASSES ."
    order by name asc;"
  );

  if ($system->database->num_rows($tax_classses_query) > 0) {
    
  // Jump to data for current page
    if ($_GET['page'] > 1) $system->database->seek($tax_classses_query, ($system->settings->get('data_table_rows_per_page') * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($tax_class = $system->database->fetch($tax_classses_query)) {
    
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr class="<?php echo $rowclass; ?>">
    <td><?php echo $system->functions->form_draw_checkbox('tax_classes['. $tax_class['id'] .']', $tax_class['id']); ?></td>
    <td align="left"><?php echo $tax_class['id']; ?></td>
    <td align="left" nowrap="nowrap"><?php echo $tax_class['name']; ?></td>
    <td align="left" style="color: #999;"><?php echo $tax_class['description']; ?></td>
    <td align="right"><a href="<?php echo $system->document->href_link('', array('doc' => 'edit_tax_class.php', 'tax_class_id' => $tax_class['id']), true); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/edit.png'; ?>" width="16" height="16" alt="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" title="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" /></a></td>
  </tr>
<?php
      if (++$page_items == $system->settings->get('data_table_rows_per_page')) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="5" align="left"><?php echo $system->language->translate('title_tax_classes', 'Tax Classes'); ?>: <?php echo $system->database->num_rows($tax_classses_query); ?></td>
  </tr>
</table>

<script type="text/javascript">
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
  echo $system->functions->form_draw_form_end();
  
// Display page links
  echo $system->functions->draw_pagination(ceil($system->database->num_rows($tax_classses_query)/$system->settings->get('data_table_rows_per_page')));
  
?>