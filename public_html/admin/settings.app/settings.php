<?php
  if (empty($_GET['setting_group_key'])) $_GET['setting_group_key'] = 'store_info';
  if (empty($_GET['page'])) $_GET['page'] = 1;

  if (!empty($_POST['save'])) {
    $system->database->query(
      "update ". DB_TABLE_SETTINGS ."
      set
        `value` = '". $system->database->input($_POST['value']) ."',
        date_updated = '". date('Y-m-d H:i:s') ."'
      where `key` = '". $system->database->input($_POST['key']) ."'
      limit 1;"
    );
    
    $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes were successfully saved.'));
    
    header('Location: '. $system->document->link('', array(), true, array('action')));
    exit;
  }
  
  $settings_groups_query = $system->database->query(
    "select * from ". DB_TABLE_SETTINGS_GROUPS ."
    order by priority, `key`;"
  );
  while ($group = $system->database->fetch($settings_groups_query)) {
    if ($_GET['setting_group_key'] == $group['key']) $setting_group = $group;
  }
?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle;" style="margin-right: 10px;" /><?php echo $system->language->translate('title_settings', 'Settings'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('settings_form', 'post'); ?>
<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th nowrap="nowrap" align="left" width="250"><?php echo $system->language->translate('title_key', 'Key'); ?></th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_value', 'Value'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php

  $settings_query = $system->database->query(
    "select * from ". DB_TABLE_SETTINGS ."
    where `setting_group_key` = '". $setting_group['key'] ."'
    order by priority, `key` asc;"
  );

  if ($system->database->num_rows($settings_query) > 0) {
    
  // Jump to data for current page
    if ($_GET['page'] > 1) $system->database->seek($settings_query, ($system->settings->get('data_table_rows_per_page', 20) * ($_GET['Page']-1)));
    
    $page_items = 0;
    while ($setting = $system->database->fetch($settings_query)) {
    
    if (!isset($rowclass) || $rowclass == 'even') {
      $rowclass = 'odd';
    } else {
      $rowclass = 'even';
    }
    
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && $_GET['key'] == $setting['key']) {
?>
  <tr class="<?php echo $rowclass; ?>">
    <td align="left" nowrap="nowrap"><u><?php echo $system->language->translate('settings_key_title:'.$setting['key'], $setting['title']); ?></u><br /><?php echo $system->language->translate('settings_key_description:'.$setting['key'], $setting['description'], ''); ?></td>
    <td align="left" valign="middle"><?php echo $system->functions->form_draw_hidden_field('key', $setting['key']) . $system->functions->form_draw_function($setting['function'], 'value', $setting['value']); ?></td>
    <td align="right" valign="middle" nowrap="nowrap"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'disk'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="location=\''. $system->document->link('', array(), true, array('action', 'key')) .'\'"'); ?></td>
  </tr>
<?php
	} else {
?>
  <tr class="<?php echo $rowclass; ?>">
    <td align="left" nowrap="nowrap"><?php echo $system->language->translate('settings_key_title:'.$setting['key'], $setting['title']); ?></td>
    <td align="left"><?php echo nl2br((strlen($setting['value']) > 50) ? substr($setting['value'], 0, 50) : $setting['value']); ?></td>
    <td align="right" nowrap="nowrap"><a href="<?php echo $system->document->href_link('', array('action' => 'edit', 'key' => $setting['key']), true); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/edit.png'; ?>" width="16" height="16" alt="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" title="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" /></a></td>
  </tr>
<?php
    }
    
    // Escape if enough page items
      if (++$page_items == $system->settings->get('data_table_rows_per_page', 20)) break;
    }
  } else {
?>
  <tr class="odd">
    <td colspan="3" align="left" nowrap="nowrap"><?php echo $system->language->translate('text_no_entries_in_database', 'There are no entries in the database.'); ?></td>
  </tr>
<?php
}
?>
</table>
<?php
  echo $system->functions->form_draw_form_end();
  
// Display page links
  echo $system->functions->draw_pagination(ceil($system->database->num_rows($settings_query)/$system->settings->get('data_table_rows_per_page', 20)));
?>