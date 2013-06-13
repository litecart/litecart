<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
  
?>
<div style="float: right;"><?php echo $system->functions->form_draw_link_button($system->document->link('', array('doc' => 'edit_discount_code'), true), $system->language->translate('title_create_new_code', 'Create New Code'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo $system->language->translate('title_discount_codes', 'Discount Codes'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('discount_codes_form', 'post'); ?>
<table width="100%" class="dataTable">
  <tr class="header">
    <th nowrap="nowrap"><?php echo $system->functions->form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_code', 'Code'); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_discount', 'Discount'); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_description', 'Description'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_amount', 'Valid From'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_date', 'Valid To'); ?></th>
    <th nowrap="nowrap">&nbsp;</th>
  </tr>
<?php
  $discount_codes_query = $system->database->query(
    "select * from ". DB_TABLE_DISCOUNT_CODES ."
    order by date_created desc;"
  );
  
  if ($system->database->num_rows($discount_codes_query) > 0) {
  
    if ($_GET['page'] > 1) $system->database->seek($discount_codes_query, ($system->settings->get('data_table_rows_per_page') * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($discount_code = $system->database->fetch($discount_codes_query)) {
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr class="<?php echo $rowclass . ($discount_code['status'] ? false : ' semi-transparent'); ?>">
    <td nowrap="nowrap"><img src="<?php echo WS_DIR_IMAGES .'icons/16x16/'. (!empty($discount_code['status']) ? 'on.png' : 'off.png') ?>" width="16" height="16" align="absbottom" /> <?php echo $system->functions->form_draw_checkbox('discount_codes['.$discount_code['id'].']', $discount_code['id'], (isset($_POST['discount_codes']) && in_array($discount_code['id'], $_POST['discount_codes'])) ? $discount_code['id'] : false); ?></td>
    <td nowrap="nowrap" align="left"><?php echo $discount_code['id']; ?></td>
    <td nowrap="nowrap" align="left"><?php echo $discount_code['code']; ?></td>
    <td nowrap="nowrap" align="left"><?php echo (strpos($discount_code['discount'], '%') !== false) ? $discount_code['discount'] : $system->currency->format($discount_code['discount'], true, false, $system->currency->selected['code']); ?></td>
    <td nowrap="nowrap" align="left"><?php echo $discount_code['description']; ?></td>
    <td nowrap="nowrap" align="right"><?php echo strftime($system->language->selected['format_datetime'], strtotime($discount_code['date_valid_from'])); ?></td>
    <td nowrap="nowrap" align="right"><?php echo strftime($system->language->selected['format_datetime'], strtotime($discount_code['date_valid_to'])); ?></td>
    <td nowrap="nowrap"><a href="<?php echo $system->document->href_link('', array('doc' => 'edit_discount_code', 'discount_code_id' => $discount_code['id']), true); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/edit.png'; ?>" width="16" height="16" align="absbottom" /></a></td>
  </tr>
<?php
      if (++$page_items == $system->settings->get('data_table_rows_per_page')) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="8" align="left"><?php echo $system->language->translate('title_discount_codes', 'Discount Codes'); ?>: <?php echo $system->database->num_rows($discount_codes_query); ?></td>
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
  
  echo $system->functions->draw_pagination(ceil($system->database->num_rows($discount_codes_query)/$system->settings->get('data_table_rows_per_page')));
?>