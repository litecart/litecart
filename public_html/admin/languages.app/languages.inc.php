<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
  
  if (!empty($_POST['enable']) || !empty($_POST['disable'])) {
  
    if (!empty($_POST['languages'])) {
      foreach ($_POST['languages'] as $key => $value) {
        $language = new ctrl_language($_POST['languages'][$key]);
        $language->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $language->save();
      }
    }
    
    header('Location: '. $system->document->link());
    exit;
  }
?>
<div style="float: right;"><?php echo $system->functions->form_draw_link_button($system->document->link('', array('doc' => 'edit_language'), true), $system->language->translate('title_add_new_language', 'Add New Language'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo $system->language->translate('title_languages', 'Languages'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('languages_form', 'post'); ?>
<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th><?php echo $system->functions->form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_code', 'Code'); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_name', 'Name'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_default_language', 'Default Language'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_store_language', 'Store Language'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_priority', 'Priority'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php
  $languages_query = $system->database->query(
    "select * from ". DB_TABLE_LANGUAGES ."
    order by status desc, priority, name;"
  );

  if ($system->database->num_rows($languages_query) > 0) {
    
    if ($_GET['page'] > 1) $system->database->seek($languages_query, ($system->settings->get('data_table_rows_per_page') * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($language = $system->database->fetch($languages_query)) {
    
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr class="<?php echo $rowclass . ($language['status'] ? false : ' semi-transparent'); ?>">
    <td nowrap="nowrap"><img src="<?php echo WS_DIR_IMAGES .'icons/16x16/'. (!empty($language['status']) ? 'on.png' : 'off.png') ?>" width="16" height="16" align="absbottom" /> <?php echo $system->functions->form_draw_checkbox('languages['. $language['code'] .']', $language['code']); ?></td>
    <td align="left"><?php echo $language['id']; ?></td>
    <td align="center"><?php echo $language['code']; ?></td>
    <td align="left"><a href="<?php echo $system->document->href_link('', array('doc' => 'edit_language', 'language_code' => $language['code'], 'page' => $_GET['page']), true); ?>"><?php echo $language['name']; ?></a></td>
    <td align="center"><?php echo ($language['code'] == $system->settings->get('default_language_code')) ? 'x' : ''; ?></td>
    <td align="center"><?php echo ($language['code'] == $system->settings->get('store_language_code')) ? 'x' : ''; ?></td>
    <td align="right"><?php echo $language['priority']; ?></td>
    <td align="right"><a href="<?php echo $system->document->href_link('', array('doc' => 'edit_language', 'language_code' => $language['code'], 'page' => $_GET['page']), true); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/edit.png'; ?>" width="16" height="16" alt="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" title="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" /></a></td>
  </tr>
<?php
      if (++$page_items == $system->settings->get('data_table_rows_per_page')) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="8" align="left"><?php echo $system->language->translate('title_languages', 'Languages'); ?>: <?php echo $system->database->num_rows($languages_query); ?></td>
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

<p><?php echo $system->functions->form_draw_button('enable', $system->language->translate('title_enable', 'Enable'), 'submit', '', 'on'); ?> <?php echo $system->functions->form_draw_button('disable', $system->language->translate('title_disable', 'Disable'), 'submit', '', 'off'); ?></p>

<?php
  echo $system->functions->form_draw_form_end();
  
  echo $system->functions->draw_pagination(ceil($system->database->num_rows($languages_query)/$system->settings->get('data_table_rows_per_page')));
?>