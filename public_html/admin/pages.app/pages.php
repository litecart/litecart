<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
?>
<div style="float: right;"><a class="button" href="<?php echo $system->document->link('', array('doc' => 'edit_page.php'), true); ?>"><?php echo $system->language->translate('title_create_new_page', 'Create New Page'); ?></a></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" border="0" align="absmiddle" style="margin-right: 10px;" /><?php echo $system->language->translate('title_pages', 'Pages'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('pages_form', 'post'); ?>
<table width="100%" border="0" align="center" cellpadding="5" cellspacing="0" class="dataTable">
  <tr class="header">
    <th><?php echo $system->functions->form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_title', 'Title'); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_menu', 'Menu'); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_support', 'Support'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php

  $pages_query = $system->database->query(
    "select p.*, pi.title from ". DB_TABLE_PAGES ." p
    left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". $system->language->selected['code'] ."')
    order by p.priority, pi.title;"
  );

  if ($system->database->num_rows($pages_query) > 0) {
    
  // Jump to data for current page
    if ($_GET['page'] > 1) $system->database->seek($pages_query, ($system->settings->get('data_table_rows_per_page', 20) * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($page = $system->database->fetch($pages_query)) {
    
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr class="<?php echo $rowclass . ($page['status'] ? false : ' semi-transparent'); ?>">
    <td nowrap="nowrap"><img src="<?php echo WS_DIR_IMAGES .'icons/16x16/'. (!empty($page['status']) ? 'on.png' : 'off.png') ?>" width="16" height="16" border="0" align="absbottom" /> <?php echo $system->functions->form_draw_checkbox('delivery_statuses['. $page['id'] .']', $page['id']); ?></td>
    <td align="left" valign="top"><?php echo $page['id']; ?></td>
    <td align="left" valign="top" nowrap="nowrap"><?php echo $page['title']; ?></td>
    <td align="center" valign="top" nowrap="nowrap"><?php echo !empty($page['dock_menu']) ? 'x' : ''; ?></td>
    <td align="center" valign="top" nowrap="nowrap"><?php echo !empty($page['dock_support']) ? 'x' : ''; ?></td>
    <td align="right"><a href="<?php echo $system->document->link('', array('doc' => 'edit_page.php', 'pages_id' => $page['id']), true); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/edit.png'; ?>" width="16" height="16" border="0" title="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" /></a></td>
  </tr>
<?php
      if (++$page_items == $system->settings->get('data_table_rows_per_page', 20)) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="6" align="left"><?php echo $system->language->translate('title_pages', 'Pages'); ?>: <?php echo $system->database->num_rows($pages_query); ?></td>
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
    if ($(event.target).is('a *')) return;
    if ($(event.target).is('th')) return;
    $(this).find('input:checkbox').trigger('click');
  });
</script>

<?php
  echo $system->functions->form_draw_form_end();
  
  echo $system->functions->draw_pagination(ceil($system->database->num_rows($pages_query)/$system->settings->get('data_table_rows_per_page', 20)));
?>