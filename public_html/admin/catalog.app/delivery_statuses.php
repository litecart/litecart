<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
?>
<div style="float: right;"><a class="button" href="<?php echo $system->document->href_link('', array('doc' => 'edit_delivery_status.php'), true); ?>"><?php echo $system->language->translate('title_create_new_delivery_status', 'Create New Delivery Status'); ?></a></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle;" style="margin-right: 10px;" /><?php echo $system->language->translate('title_delivery_statuses', 'Delivery Statuses'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('delivery_statuses_form', 'post'); ?>
<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th><?php echo $system->functions->form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_name', 'Name'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php

  $delivery_status_query = $system->database->query(
    "select ds.id, dsi.name from ". DB_TABLE_DELIVERY_STATUS ." ds
    left join ". DB_TABLE_DELIVERY_STATUS_INFO ." dsi on (ds.id = dsi.delivery_status_id and dsi.language_code = '". $system->language->selected['code'] ."')
    order by dsi.name asc;"
  );

  if ($system->database->num_rows($delivery_status_query) > 0) {
    
  // Jump to data for current page
    if ($_GET['page'] > 1) $system->database->seek($delivery_status_query, ($system->settings->get('data_table_rows_per_page') * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($delivery_status = $system->database->fetch($delivery_status_query)) {
    
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr class="<?php echo $rowclass; ?>">
    <td><?php echo $system->functions->form_draw_checkbox('delivery_statuses['. $delivery_status['id'] .']', $delivery_status['id']); ?></td>
    <td align="left"><?php echo $delivery_status['id']; ?></td>
    <td align="left" nowrap="nowrap"><?php echo $delivery_status['name']; ?></td>
    <td align="right"><a href="<?php echo $system->document->href_link('', array('doc' => 'edit_delivery_status.php', 'delivery_status_id' => $delivery_status['id']), true); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/edit.png'; ?>" width="16" height="16" alt="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" title="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" /></a></td>
  </tr>
<?php
      if (++$page_items == $system->settings->get('data_table_rows_per_page')) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="5" align="left"><?php echo $system->language->translate('title_delivery_statuses', 'Delivery Statuses'); ?>: <?php echo $system->database->num_rows($delivery_status_query); ?></td>
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
  
  echo $system->functions->draw_pagination(ceil($system->database->num_rows($delivery_status_query)/$system->settings->get('data_table_rows_per_page')));
?>