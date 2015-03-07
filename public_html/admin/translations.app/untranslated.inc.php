<?php
  
  //$rows_per_page = settings::get('data_table_rows_per_page');
  $rows_per_page = 10;
  
  if (empty($_GET['language_1'])) $_GET['language_1'] = 'en';
  if (empty($_GET['language_2'])) $_GET['language_2'] = settings::get('store_language_code');
  if (isset($_GET['language_1']) && isset($_GET['language_2']) && $_GET['language_1'] == $_GET['language_2']) {
    $_GET['language_1'] = $_GET['language_2'];
    unset($_GET['language_2']);
  }
  if (isset($_GET['language_2']) && $_GET['language_2'] == 'en') {
     $tmp = $_GET['language_1'];
     $_GET['language_1'] = $_GET['language_2'];
     $_GET['language_2'] = $tmp;
  }
  
  if (!isset($_GET['page'])) $_GET['page'] = 1;
  
  if (isset($_POST['save'])) {
  
    foreach ($_POST['translations'] as $translation) {
      database::query(
        "update ". DB_TABLE_TRANSLATIONS ."
        set html = ". (!empty($translation['html']) ? 1 : 0) .",
          text_". $_GET['language_1'] ." = '". database::input(trim($translation['text_'.$_GET['language_1']]), !empty($translation['html']) ? true : false) ."',
          ". (isset($_GET['language_2']) ? "text_". $_GET['language_2'] ." = '". database::input(trim($translation['text_'.$_GET['language_2']]), !empty($translation['html']) ? true : false) ."', " : "") ."
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". database::input($translation['id']) ."'
        limit 1;"
      );
    }
    
    cache::clear_cache('translations');
    
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
    
    cache::clear_cache('translations');
    
    notices::add('success', language::translate('success_translated_deleted', 'Translation was successfully deleted'));
    
    header('Location: '. document::link('', array(), true));
    exit;
  }
?>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_untranslated', 'Untranslated'); ?></h1>

<?php echo functions::form_draw_form_begin('translation_form', 'post'); ?>

  <table class="dataTable" width="100%">
    <tr class="header">
      <th><?php echo functions::form_draw_languages_list('language_1', true, false, 'onchange="location=\'?app='. $_GET['app'] .'&doc='. $_GET['doc'] .'&language_1=\' + $(this).val() + \'&language_2='. (isset($_GET['language_2']) ? $_GET['language_2'] : '') .'\'"'); ?></th>
      <th><?php echo functions::form_draw_languages_list('language_2', true, false, 'onchange="location=\'?app='. $_GET['app'] .'&doc='. $_GET['doc'] .'&language_1='. (isset($_GET['language_1']) ? $_GET['language_1'] : '') .'&language_2=\' + $(this).val()"'); ?></th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
    </tr>
<?php
  $translations_query = database::query(
    "select * from ". DB_TABLE_TRANSLATIONS ."
    where text_".database::input($_GET['language_1']) ." = ''
    ". (isset($_GET['language_2']) ? "or text_".database::input($_GET['language_2']) ." = ''" : "") ."
    order by date_accessed desc;"
  );
  
  if (database::num_rows($translations_query) > 0) {
    
  // Jump to data for current page
    if ($_GET['page'] > 1) database::seek($translations_query, ($rows_per_page * ($_GET['page']-1)));
    
    $i = 0;
    $page_items = 0;
    while ($row=database::fetch($translations_query)) {
      $i++;
    
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
      
      $row['pages'] = rtrim($row['pages'], ',');
?>
    <tr class="<?php echo $rowclass; ?>">
      <td colspan="<?php echo count(language::$languages)+3; ?>">
        <?php echo $row['code']; ?>      
      </td>
    </tr>
    <tr class="<?php echo $rowclass; ?>">
      <td><?php if (!empty($_GET['language_1'])) echo functions::form_draw_hidden_field('translations['. $row['code'] .'][id]', $row['id']) . functions::form_draw_textarea('translations['. $row['code'] .'][text_'.$_GET['language_1'].']', $row['text_'.$_GET['language_1']], 'rows="2" tabindex="'. (1000+$i) .'" style="width: 230px"'); ?></td>
      <td><?php if (!empty($_GET['language_2'])) echo functions::form_draw_hidden_field('translations['. $row['code'] .'][id]', $row['id']) . functions::form_draw_textarea('translations['. $row['code'] .'][text_'.$_GET['language_2'].']', $row['text_'.$_GET['language_2']], 'rows="2" tabindex="'. (2000+$i) .'" style="width: 230px"'); ?></td>
      <td><a href="javascript:alert('<?php echo str_replace(',', "\\n", $row['pages']); ?>');"><?php echo sprintf(language::translate('text_shared_by_pages', 'Shared by %d pages'), substr_count($row['pages'], ',')+1); ?></a><br />
        <?php echo functions::form_draw_checkbox('translations['. $row['code'] .'][html]', '1', (isset($_POST['translations'][$row['code']]['html']) ? $_POST['translations'][$row['code']]['html'] : $row['html'])); ?> <?php echo language::translate('text_html_enabled', 'HTML enabled'); ?>
      </td>
      <td style="text-align: right;"><a href="javascript:delete_translation('<?php echo $row['id']; ?>');" onclick="if (!confirm('<?php echo language::translate('text_are_you_sure', 'Are you sure?'); ?>')) return false;" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fontawesome_icon('times-circle', 'style="color: #cc3333;"', 'fa-lg'); ?></a></td>
    </tr>
<?php      
        if (++$page_items == $rows_per_page) break;
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

  <p><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', 'tabindex="9999"', 'save'); ?></p>

<?php echo functions::form_draw_form_end(); ?>

<script>
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
  echo functions::draw_pagination(ceil(database::num_rows($translations_query)/$rows_per_page));
?>