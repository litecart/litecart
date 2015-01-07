<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
?>
<div style="float: right;"><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_page'), true), language::translate('title_create_new_page', 'Create New Page'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo language::translate('title_pages', 'Pages'); ?></h1>

<?php echo functions::form_draw_form_begin('pages_form', 'post'); ?>
<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th><?php echo functions::form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th><?php echo language::translate('title_id', 'ID'); ?></th>
    <th width="100%"><?php echo language::translate('title_title', 'Title'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php

  $pages_query = database::query(
    "select p.*, pi.title from ". DB_TABLE_PAGES ." p
    left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
    order by p.priority, pi.title;"
  );

  if (database::num_rows($pages_query) > 0) {
    
  // Jump to data for current page
    if ($_GET['page'] > 1) database::seek($pages_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($page = database::fetch($pages_query)) {
    
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr class="<?php echo $rowclass . ($page['status'] ? false : ' semi-transparent'); ?>">
    <td><img src="<?php echo WS_DIR_IMAGES .'icons/16x16/'. (!empty($page['status']) ? 'on.png' : 'off.png') ?>" width="16" height="16" align="absbottom" /> <?php echo functions::form_draw_checkbox('delivery_statuses['. $page['id'] .']', $page['id']); ?></td>
    <td><?php echo $page['id']; ?></td>
    <td><a href="<?php echo document::href_link('', array('doc' => 'edit_page', 'pages_id' => $page['id']), true); ?>"><?php echo $page['title']; ?></a></td>
    <td style="text-align: right;"><a href="<?php echo document::href_link('', array('doc' => 'edit_page', 'pages_id' => $page['id']), true); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/edit.png'; ?>" width="16" height="16" alt="<?php echo language::translate('title_edit', 'Edit'); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>" /></a></td>
  </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="6"><?php echo language::translate('title_pages', 'Pages'); ?>: <?php echo database::num_rows($pages_query); ?></td>
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
  
  echo functions::draw_pagination(ceil(database::num_rows($pages_query)/settings::get('data_table_rows_per_page')));
?>