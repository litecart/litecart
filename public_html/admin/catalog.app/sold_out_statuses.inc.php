<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
?>
<div style="float: right;"><?php echo $system->functions->form_draw_link_button($system->document->link('', array('doc' => 'edit_sold_out_status'), true), $system->language->translate('title_create_new_status', 'Create New Status'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo $system->language->translate('title_sold_out_statuses', 'Sold Out Statuses'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('sold_out_statuses_form', 'post'); ?>
<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th><?php echo $system->functions->form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_name', 'Name'); ?></th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_orderable', 'Orderable'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php

  $sold_out_status_query = $system->database->query(
    "select sos.id, sos.orderable, sosi.name from ". DB_TABLE_SOLD_OUT_STATUSES ." sos
    left join ". DB_TABLE_SOLD_OUT_STATUSES_INFO ." sosi on (sos.id = sosi.sold_out_status_id and sosi.language_code = '". $system->language->selected['code'] ."')
    order by sosi.name asc;"
  );

  if ($system->database->num_rows($sold_out_status_query) > 0) {
    
  // Jump to data for current page
    if ($_GET['page'] > 1) $system->database->seek($sold_out_status_query, ($system->settings->get('data_table_rows_per_page') * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($sold_out_status = $system->database->fetch($sold_out_status_query)) {
    
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr class="<?php echo $rowclass; ?>">
    <td><?php echo $system->functions->form_draw_checkbox('delivery_statuses['. $sold_out_status['id'] .']', $sold_out_status['id']); ?></td>
    <td align="left"><?php echo $sold_out_status['id']; ?></td>
    <td align="left" nowrap="nowrap"><a href="<?php echo $system->document->href_link('', array('doc' => 'edit_sold_out_status', 'sold_out_status_id' => $sold_out_status['id']), true); ?>"><?php echo $sold_out_status['name']; ?></a></td>
    <td align="center" nowrap="nowrap"><?php echo !empty($sold_out_status['orderable']) ? 'x' : ''; ?></td>
    <td align="right"><a href="<?php echo $system->document->href_link('', array('doc' => 'edit_sold_out_status', 'sold_out_status_id' => $sold_out_status['id']), true); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/edit.png'; ?>" width="16" height="16" alt="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" title="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" /></a></td>
  </tr>
<?php
      if (++$page_items == $system->settings->get('data_table_rows_per_page')) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="5" align="left"><?php echo $system->language->translate('title_sold_out_statuses', 'Sold Out Statuses'); ?>: <?php echo $system->database->num_rows($sold_out_status_query); ?></td>
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
  echo $system->functions->form_draw_form_end();
  
  echo $system->functions->draw_pagination(ceil($system->database->num_rows($sold_out_status_query)/$system->settings->get('data_table_rows_per_page')));
?>