<?php
  
  if (!isset($_GET['query'])) $_GET['query'] = '';
  if (!isset($_GET['language_code'])) $_GET['language_code'] = 'sv';
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
    
    cache::clear_cache('translations');
    
    notices::add('success', language::translate('success_changes_saved', 'Changes were successfully saved.'));
    
    header('Location: '. document::link('', array(), true));
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

<div style="float: right;">
  <?php echo functions::form_draw_form_begin('search_form', 'get'); ?>
    <?php if (!empty($_GET)) foreach ($_GET as $key => $value) { if (!in_array($key, array('query', 'system'))) echo functions::form_draw_hidden_field($key, $value); } ?>
    <?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword') .'"'); ?>
  <?php echo functions::form_draw_form_end(); ?>
</div>

<h1 style="margin-top: 0px;"><?php echo $app_icon; ?><?php echo language::translate('title_search_translations', 'Search Translations'); ?></h1>

<?php echo functions::form_draw_form_begin('translation_form', 'post'); ?>

  <table align="center" width="100%" class="dataTable">
    <tr class="header">
      <th><?php echo language::translate('title_code', 'Code');?></th>
      <?php foreach (array_keys(language::$languages) as $language_code) echo '<th>'. language::$languages[$language_code]['name'] .'</th>'; ?>
      <th>&nbsp;</th>
    </tr>
<?php
  $sql_where_fields = "";
  foreach (array_keys(language::$languages) as $language) {
    $sql_where_fields .= " or text_".$language ." like '%". str_replace('%', "\\%", database::input($_GET['query'])) ."%' ";
  }

  $translations_query = database::query(
    "select * from ". DB_TABLE_TRANSLATIONS ."
    where code like '%". str_replace('%', "\\%", database::input($_GET['query'])) ."%'". $sql_where_fields ."
    order by date_created desc;"
  );

  if (database::num_rows($translations_query) > 0) {
    
  // Jump to data for current page
    if ($_GET['page'] > 1) database::seek($translations_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($row=database::fetch($translations_query)) {
    
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
      
      $row['pages'] = rtrim($row['pages'], ',');
?>
    <tr class="<?php echo $rowclass; ?>">
      <td><?php echo $row['code']; ?><br />
        <a href="javascript:alert('<?php echo str_replace(',', "\\n", $row['pages']); ?>');"><?php echo sprintf(language::translate('text_shared_by_pages', 'Shared by %d pages'), substr_count($row['pages'], ',')+1); ?></a><br />
        <?php echo functions::form_draw_checkbox('translations['. $row['code'] .'][html]', '1', (isset($_POST['translations'][$row['code']]['html']) ? $_POST['translations'][$row['code']]['html'] : $row['html'])); ?> <?php echo language::translate('text_html_enabled', 'HTML enabled'); ?>
      </td>
      <?php foreach (array_keys(language::$languages) as $language_code) echo '<td>'. functions::form_draw_hidden_field('translations['. $row['code'] .'][id]', $row['id']) . functions::form_draw_textarea('translations['. $row['code'] .'][text_'.$language_code.']', $row['text_'.$language_code], 'rows="2" style="width: 200px" tabindex="'. language::$languages[$language_code]['id'].str_pad($page_items, 4, '0', STR_PAD_LEFT) .'"') .'</td>'; ?>
      <td style="text-align: right;"><a href="javascript:delete_translation('<?php echo $row['id']; ?>');" onclick="if (!confirm('<?php echo language::translate('text_are_you_sure', 'Are you sure?'); ?>')) return false;" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fontawesome_icon('times-circle', 'style="color: #cc3333;"', 'fa-lg'); ?></a></td>
    </tr>
<?php      
        if (++$page_items == settings::get('data_table_rows_per_page')) break;
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
  <p style="text-align: right;"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?></p>

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
  echo functions::draw_pagination(ceil(database::num_rows($translations_query)/settings::get('data_table_rows_per_page')));
?>