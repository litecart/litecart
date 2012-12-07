<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;
  
  if (!empty($_POST['enable']) || !empty($_POST['disable'])) {
  
    if (!empty($_POST['languages'])) {
      foreach ($_POST['languages'] as $key => $value) $_POST['languages'][$key] = $system->database->input($value);
      $system->database->query(
        "update ". DB_TABLE_LANGUAGES ."
        set status = '". ((!empty($_POST['enable'])) ? 1 : 0) ."'
        where id in ('". implode("', '", $_POST['languages']) ."');"
      );
    }
    
    header('Location: '. $system->document->link());
    exit;
  }
?>
<div style="float: right;"><a class="button" href="<?php echo $system->document->href_link('', array('doc' => 'edit_language.php'), true); ?>"><?php echo $system->language->translate('title_add_new_language', 'Add New Language'); ?></a></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" border="0" align="absmiddle" style="margin-right: 10px;" /><?php echo $system->language->translate('title_languages', 'Languages'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('languages_form', 'post'); ?>
<table width="100%" border="0" align="center" cellpadding="5" cellspacing="0" class="dataTable">
  <tr class="header">
    <th><?php echo $system->functions->form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_code', 'Code'); ?></th>
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_name', 'Name'); ?></th>
    <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_priority', 'Priority'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php
  $languages_query = $system->database->query(
    "select * from ". DB_TABLE_LANGUAGES ."
    order by status desc, priority, name;"
  );

  if ($system->database->num_rows($languages_query) > 0) {
    
    if ($_GET['page'] > 1) $system->database->seek($languages_query, ($system->settings->get('data_table_rows_per_page', 20) * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($language = $system->database->fetch($languages_query)) {
    
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
  <tr class="<?php echo $rowclass . ($language['status'] ? false : ' semi-transparent'); ?>">
    <td nowrap="nowrap"><img src="<?php echo WS_DIR_IMAGES .'icons/16x16/'. (!empty($language['status']) ? 'on.png' : 'off.png') ?>" width="16" height="16" border="0" align="absbottom" /> <?php echo $system->functions->form_draw_checkbox('languages['. $language['id'] .']', $language['id']); ?></td>
    <td align="left" valign="top"><?php echo $language['id']; ?></td>
    <td align="center" valign="top"><?php echo $language['code']; ?></td>
    <td align="left" valign="top"><?php echo $language['name']; ?></td>
    <td align="right" valign="top"><?php echo $language['priority']; ?></td>
    <td align="right"><a href="<?php echo $system->document->href_link('', array('doc' => 'edit_language.php', 'language_code' => $language['code'], 'page' => $_GET['page']), true); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/edit.png'; ?>" width="16" height="16" border="0" title="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" /></a></td>
  </tr>
<?php
      if (++$page_items == $system->settings->get('data_table_rows_per_page', 20)) break;
    }
  }
?>
  <tr class="footer">
    <td colspan="6" align="left"><?php echo $system->language->translate('title_languages', 'Languages'); ?>: <?php echo $system->database->num_rows($languages_query); ?></td>
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

<p><?php echo $system->functions->form_draw_button('enable', $system->language->translate('title_enable', 'Enable'), 'submit'); ?> <?php echo $system->functions->form_draw_button('disable', $system->language->translate('title_disable', 'Disable'), 'submit'); ?></p>

<?php
  echo $system->functions->form_draw_form_end();
  
  echo $system->functions->draw_pagination(ceil($system->database->num_rows($languages_query)/$system->settings->get('data_table_rows_per_page', 20)));
?>