<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
?>
<div style="float: right;"><a class="button" href="<?php echo $system->document->href_link('', array('doc' => 'edit_customer.php'), true); ?>"><?php echo $system->language->translate('title_add_new_customer', 'Add New Customer'); ?></a></div>
<div style="float: right; padding-right: 10px;"><?php echo $system->functions->form_draw_search_field('query', true, 'placeholder="'. $system->language->translate('title_search', 'Search') .'"  onkeydown=" if (event.keyCode == 13) location=(\''. $system->document->link('', array(), true, array('page', 'query')) .'&query=\' + this.value)"'); ?></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo $system->language->translate('title_customers', 'Customers'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('customers_form', 'post'); ?>
<table width="100%" class="dataTable">
  <tr class="header">
    <th nowrap="nowrap">&nbsp;</th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_name', 'Name'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_date_registered', 'Date Registered'); ?></th>
    <th nowrap="nowrap">&nbsp;</th>
  </tr>
<?php
  $customers_query = $system->database->query(
    "select * from ". DB_TABLE_CUSTOMERS ."
    ". ((!empty($_GET['query'])) ? "where (email like '%". $system->database->input($_GET['query']) ."%' or firstname like '%". $system->database->input($_GET['query']) ."%' or lastname like '%". $system->database->input($_GET['query']) ."%')" : "") ."
    order by firstname, lastname desc;"
  );
  
  if ($system->database->num_rows($customers_query) > 0) {
  
    
  // Jump to data for current page
    if ($_GET['page'] > 1) $system->database->seek($customers_query, ($system->settings->get('data_table_rows_per_page') * ($_GET['page']-1)));
  
    $page_items = 0;
    while ($customer = $system->database->fetch($customers_query)) {
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr>
    <td nowrap="nowrap"><?php echo $system->functions->form_draw_checkbox('orders['.$customer['id'].']', $customer['id']); ?></td>
    <td nowrap="nowrap" align="left"><?php echo $customer['id']; ?></td>
    <td nowrap="nowrap" align="left"><?php echo $customer['firstname'] .' '. $customer['lastname']; ?></td>
    <td nowrap="nowrap" align="right"><?php echo strftime($system->language->selected['format_datetime'], strtotime($customer['date_created'])); ?></td>
    <td nowrap="nowrap"><a href="<?php echo $system->document->href_link('', array('doc' => 'edit_customer.php', 'customer_id' => $customer['id']), true); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/edit.png'; ?>" width="16" height="16" align="absbottom" /></a></td>
  </tr>
<?php
      if (++$page_items == $system->settings->get('data_table_rows_per_page')) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="5" align="left"><?php echo $system->language->translate('title_customers', 'Customers'); ?>: <?php echo $system->database->num_rows($customers_query); ?></td>
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
  echo $system->functions->draw_pagination(ceil($system->database->num_rows($customers_query)/$system->settings->get('data_table_rows_per_page')));
  
?>