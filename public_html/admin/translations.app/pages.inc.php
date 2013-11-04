<?php
  if (isset($_GET['script']) && $_GET['script'] != '') {
    if (!file_exists(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $_GET['script'])) {
  
      database::query(
        "update ". DB_TABLE_TRANSLATIONS ."
        set pages = replace(pages, '\'". database::input($_GET['script']) ."\',', '');"
      );
      notices::add('success', 'The page do no longer exist and was removed from list, please select another.');
      header('Location: '. document::link('', array('app' => $_GET['app'], 'doc' => $_GET['doc'])));
    }
  }
  
  if (!isset($_GET['page'])) $_GET['page'] = 1;
  
  if (isset($_POST['save'])) {
  
    foreach ($_POST['translations'] as $translation) {
      $sql_update_fields = '';
      foreach (array_keys(language::$languages) as $language) {
        $sql_update_fields .= "text_".$language ." = '". database::input(trim($translation['text_'.$language]), !empty($translation['html']) ? true : false) ."', ";
      }
      database::query(
        "update ". DB_TABLE_TRANSLATIONS ."
        set
        html = ". (!empty($translation['html']) ? 1 : 0) .",
          ". $sql_update_fields ."
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". database::input($translation['id']) ."'
        limit 1;"
      );
    }
    
    notices::add('success', language::translate('success_changes_saved', 'Changes were successfully saved.'));
    
    header('Location: '. document::link('', array('app' => $_GET['app'], 'doc' => $_GET['doc']), true));
    exit;
  }
  
  if (isset($_POST['delete'])) {
    database::query(
      "delete from ". DB_TABLE_TRANSLATIONS ."
      where id = '". database::input($_POST['translation_id']) ."'
      limit 1;"
    );
    
    notices::add('success', language::translate('success_translated_deleted', 'Translation was successfully deleted'));
    
    header('Location: '. document::link('', array(), true));
    exit;
  }
?>

<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo language::translate('title_translations_by_page', 'Translations by Page'); ?></h1>
<?php
  $pages_query = database::query("select distinct pages from ". DB_TABLE_TRANSLATIONS .";");
  $pages = array();
  while ($row = database::fetch($pages_query)) {
    $slices = explode(',', $row['pages']);
    foreach ($slices as $slice) {
      if ($slice != '') $pages[$slice] = $slice;
    }
  }
  function custom_sort_pages($a, $b) {
    
    if (strpos($a, '/') && !strpos($b, '/')) {
      return -1;
    } else if (!strpos($a, '/') && strpos($b, '/')) {
      return 1;
    } else {
      return ($a < $b) ? -1 : 1;
    }
  }
  usort($pages, 'custom_sort_pages');
  
  $options = array(array('-- '. language::translate('title_choose', 'Choose') .' --', ''));
  foreach ($pages as $page) {
    $options[] = array(str_replace("'", '', $page));
  }
  
  echo functions::form_draw_select_field('script', $options, isset($_GET['script']) ? $_GET['script'] : false, false, 'onchange="location=(\''. document::link('', array(), true) .'&script=\' + this.options[this.selectedIndex].value)"');
?>
<?php
  if (!empty($_GET['script'])) {
?>
<?php echo functions::form_draw_form_begin('translation_form', 'post'); ?>
<p><a href="<?php echo document::href_link('', array('action' => 'edit_all'), true, array('id')); ?>"><?php echo language::translate('text_edit_all_on_page', 'Edit all on page'); ?></a></p>
<table align="center" width="100%" class="dataTable">
  <tr class="header">
    <th align="left"><?php echo language::translate('title_code', 'Code');?></th>
    <?php foreach (array_keys(language::$languages) as $language_code) echo '<th nowrap="nowrap" align="left">'. language::$languages[$language_code]['name'] .'</th>'; ?>
    <th>&nbsp;</th>
  </tr>
<?php
  $translations_query = database::query(
    "select * from ". DB_TABLE_TRANSLATIONS ."
    where pages like '%\'". $_GET['script'] ."\'%'
    order by code asc;"
  );

  if (database::num_rows($translations_query) > 0) {
    
  // Jump to data for current page
    if ($_GET['page'] > 1) database::seek($translations_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($row=database::fetch($translations_query)) {
    
    // Keep track of items per page
      $page_items++;
    
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
    
    if (isset($_GET['action']) && ($_GET['action'] == 'edit_all' || ($_GET['action'] == 'edit' && isset($_GET['id']) && $_GET['id'] == $row['id']))) {
?>
  <tr class="<?php echo $rowclass; ?>">
    <td align="left"><?php echo $row['code']; ?><br />
    <?php echo functions::form_draw_checkbox('translations['. $row['code'] .'][html]', '1', (isset($_POST['translations'][$row['code']]['html']) ? $_POST['translations'][$row['code']]['html'] : $row['html'])); ?> <?php echo language::translate('text_html_enabled', 'HTML enabled'); ?></td>
    <?php foreach (array_keys(language::$languages) as $language_code) echo '<td>'. functions::form_draw_hidden_field('translations['. $row['code'] .'][id]', $row['id']) . functions::form_draw_textarea('translations['. $row['code'] .'][text_'.$language_code.']', $row['text_'.$language_code], 'rows="2" style="width: 200px"') .'</td>'; ?>
    <td align="right" valign="middle"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="location=\''.document::link('', array(), true, array('action', 'id')).'\'"'); ?></td>
  </tr>
<?php
      } else {
?>
  <tr class="<?php echo $rowclass; ?>">
    <td align="left"><?php echo $row['code']; ?></td>
    <?php foreach (array_keys(language::$languages) as $language_code) echo '<td>'. functions::form_draw_static_field('', (strlen($row['text_'.$language_code]) > 300) ? substr($row['text_'.$language_code], 0, 250).' ...' : $row['text_'.$language_code]) .'</td>'; ?>
    <td align="right"><a href="<?php echo document::href_link('', array('app' => $_GET['app'], 'doc' => $_GET['doc'], 'script' => $_GET['script'], 'action' => 'edit', 'id' => $row['id'], 'page' => $_GET['page'])); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/edit.png'; ?>" width="16" height="16" border="0" title="<?php echo language::translate('title_edit', 'Edit'); ?>" alt="<?php echo language::translate('title_edit', 'Edit'); ?>" /></a> <a href="javascript:delete_translation('<?php echo $row['id']; ?>');" onclick="if (!confirm('<?php echo language::translate('text_are_you_sure', 'Are you sure?'); ?>')) return false;"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" alt="<?php echo language::translate('text_remove', 'Remove'); ?>" /></a></td>
  </tr>
<?php
      }
      
      // Escape if enough page items
        if ($page_items == settings::get('data_table_rows_per_page')) break;
      }
    } else {
?>
  <tr class="odd">
    <td colspan="<?php echo 2+count(language::$languages); ?>" align="left" nowrap="nowrap"><?php echo language::translate('text_no_entries_found_in_database', 'No entries found in database'); ?></td>
  </tr>
<?php
    }
?>
</table>
<<<<<<< HEAD
<?php echo functions::form_draw_form_end(); ?>
<script type="text/javascript">
=======
<?php echo $system->functions->form_draw_form_end(); ?>
<script>
>>>>>>> 1cbd6a3dd73b38fa0cd257f81d704997820abbc9
  function delete_translation(id) {
    var form = $('<?php
      echo str_replace(array("\r", "\n"), '', functions::form_draw_form_begin('delete_translation_form', 'post')
                                            . functions::form_draw_hidden_field('translation_id', '\'+ id +\'')
                                            . functions::form_draw_hidden_field('delete', 'true')
                                            . functions::form_draw_form_end()
      );
    ?>');
    $(document.body).append(form);
    form.submit();
  }
</script>
<?php
  // Display page links
    echo functions::draw_pagination(ceil(database::num_rows($translations_query)/settings::get('data_table_rows_per_page')));
  }
?>