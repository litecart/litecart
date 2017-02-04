<?php
  if (!isset($_GET['query'])) $_GET['query'] = '';
  if (empty($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['languages'])) $_GET['languages'] = array_keys(language::$languages);

  if (isset($_POST['save']) && !empty($_POST['translations'])) {

    foreach ($_POST['translations'] as $translation) {
      $sql_update_fields = '';
      foreach ($_GET['languages'] as $language_code) {
        $sql_update_fields .= "text_".database::input($language_code) ." = '". database::input(trim($translation['text_'.database::input($language_code)]), !empty($translation['html']) ? true : false) ."', " . PHP_EOL;
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

  if (isset($_POST['delete']) && !empty($_POST['translation_id'])) {
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

  $languages_query = database::query(
    "select * from ". DB_TABLE_LANGUAGES ."
    order by priority;"
  );

  $languages = array();
  while ($language = database::fetch($languages_query)) {
    $languages[$language['code']] = $language;
  }

  $selected_languages = $languages;
  if (!empty($_GET['languages'])) {
    foreach (array_keys($selected_languages) as $language_code) {
      if (!in_array($language_code, $_GET['languages'])) unset($selected_languages[$language_code]);
    }
  }

  functions::draw_lightbox();
?>
<style>
ul.filter li {
  display: table-cell;
  vertical-align: middle;
}

.pagination {
  display: inline-block;
}
</style>

<?php if (count($selected_languages) > 1) { ?>
<div class="pull-right">
  <button type="button" class="btn btn-default translator-tool" data-toggle="lightbox" data-target="#translator-tool" data-width="980px"><?php echo language::translate('title_translator_tool', 'Translator Tool'); ?></button>
</div>
<?php } ?>

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_search_translations', 'Search Translations'); ?></h1>

<?php echo functions::form_draw_form_begin('search_form', 'get', document::link('')); ?>
<?php echo functions::form_draw_hidden_field('app') . functions::form_draw_hidden_field('doc'); ?>
<ul class="filter list-inline pull-right" style="margin: 0 auto;">
  <li>
    <?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword') .'"'); ?>
  </li>

  <li>
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

  $options = array(array('-- '. language::translate('title_all_scripts', 'All Scripts') .' --', ''));
  foreach ($pages as $page) {
    $options[] = array(str_replace("'", '', $page));
  }

  echo functions::form_draw_select_field('script', $options, isset($_GET['script']) ? $_GET['script'] : false, false, 'style="width: 250px;"');
?>
  </li>

  <li>
    <label><?php echo functions::form_draw_checkbox('modules', 'true'); ?> <?php echo language::translate('text_inlcude_modules', 'Include modules'); ?></label><br />
    <label><?php echo functions::form_draw_checkbox('untranslated', 'true'); ?> <?php echo language::translate('text_only_untranslated', 'Only untranslated'); ?></label>
  </li>

  <li>
    <label><?php echo language::translate('title_languages', 'Languages'); ?></label>
    <div><?php foreach (array_keys($languages) as $language_code) echo '<span style="padding: 0.25em;">'. functions::form_draw_checkbox('languages[]', $language_code) .' '. $language_code .'</span>'; ?></div>
  </li>

  <li><?php echo functions::form_draw_button('filter', language::translate('title_filter', 'Filter'), 'submit'); ?></li>
</ul>
<?php echo functions::form_draw_form_end(); ?>

<?php echo functions::form_draw_form_begin('translation_form', 'post'); ?>

  <table class="table table-striped">
    <thead>
      <tr>
        <th><?php echo language::translate('title_code', 'Code');?></th>
        <?php foreach ($_GET['languages'] as $language_code) echo '<th>'. $languages[$language_code]['name'] .'</th>'; ?>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
<?php
  $translations_query = database::query(
    "select * from ". DB_TABLE_TRANSLATIONS ."
    where code != ''
    ". (!empty($_GET['query']) ? "and (code like '%". str_replace('%', "\\%", database::input($_GET['query'])) ."%' or `text_". implode("` like '%". database::input($_GET['query']) ."%' or `text_", database::input($_GET['languages'])) ."` like '%". database::input($_GET['query']) ."%')" : null) ."
    ". (!empty($_GET['untranslated']) ? "and (`text_". implode("` = '' or `text_", database::input($_GET['languages'])) ."` = '')" : null) ."
    ". (!empty($_GET['script']) ? "and pages like '%". $_GET['script'] ."%'" : null) ."
    ". (empty($_GET['modules']) ? "and (code not like '". implode("_%:%' and code not like '", array('cm', 'job', 'oa', 'ot', 'os', 'pm', 'sm')) ."_%:%')" : null) ."
    order by date_created desc;"
  );

  if (database::num_rows($translations_query) > 0) {

    if ($_GET['page'] > 1) database::seek($translations_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

    $page_items = 0;
    while ($row=database::fetch($translations_query)) {

      $row['pages'] = rtrim($row['pages'], ',');
?>
      <tr>
        <td><?php echo $row['code']; ?><br />
          <small style="color: #999;"><a href="javascript:alert('<?php echo str_replace(',', "\\n", $row['pages']); ?>');"><?php echo sprintf(language::translate('text_shared_by_pages', 'Shared by %d pages'), substr_count($row['pages'], ',')+1); ?></a><br />
          <?php echo functions::form_draw_checkbox('translations['. $row['code'] .'][html]', '1', (isset($_POST['translations'][$row['code']]['html']) ? $_POST['translations'][$row['code']]['html'] : $row['html'])); ?> <?php echo language::translate('text_html_enabled', 'HTML enabled'); ?></small>
        </td>
        <?php foreach ($_GET['languages'] as $key => $language_code) echo '<td>'. functions::form_draw_hidden_field('translations['. $row['code'] .'][id]', $row['id']) . functions::form_draw_textarea('translations['. $row['code'] .'][text_'.$language_code.']', $row['text_'.$language_code], 'rows="2" tabindex="'. $key.str_pad($page_items+1, 2, '0', STR_PAD_LEFT) .'"') .'</td>'; ?>
        <td style="text-align: right;"><a class="delete" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
      </tr>
<?php
        if (++$page_items == settings::get('data_table_rows_per_page')) break;
      }
    } else {
?>
      <tr>
        <td colspan="<?php echo 2+count(language::$languages); ?>" align="left" nowrap="nowrap"><?php echo language::translate('text_no_entries_found_in_database', 'No entries found in database'); ?></td>
      </tr>
<?php
    }
?>
    </tbody>
  </table>

  <p style="display: inline; float: right;">
    <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', 'tabindex="9999"', 'save'); ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>

<?php echo functions::draw_pagination(ceil(database::num_rows($translations_query)/settings::get('data_table_rows_per_page'))); ?>

<?php if (count($selected_languages) > 1) { ?>
<div id="translator-tool" style="display: none;">
  <h2><?php echo language::translate('title_translator_tool', 'Translator Tool'); ?></h2>

  <div class="row">
    <div class="col-md-6">
      <div class="form-group">
        <label><?php echo language::translate('title_from_language', 'From Language'); ?></label>
<?php
  $options = array(array('-- '. language::translate('title_select', 'Select') .' --'));
  foreach ($selected_languages as $language) {
    $options[] = array($language['name'], $language['code']);
  }
?>
        <?php echo functions::form_draw_select_field('from_language_code', $options, $_GET['languages'][0]); ?>
      </div>
      <div class="form-group">
        <label><?php echo language::translate('title_to_language', 'To Language'); ?></label>
        <?php echo functions::form_draw_select_field('to_language_code', $options); ?>
      </div>
      <div class="form-group">
        <label><?php echo language::translate('text_copy_below_to_translation_service', 'Copy below to translation service'); ?></label>
        <textarea class="form-control" name="source" style="height: 320px;" readonly="readonly"></textarea>
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group">
        <label><?php echo language::translate('text_paste_your_translated_result_below', 'Paste your translated result below'); ?></label>
        <textarea class="form-control" name="result" style="height: 455px;"></textarea>
      </div>
    </div>
  </div>

  <ul class="list-unstyled">
    <li><a href="https://translate.google.com" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?> Google Translate</a></li>
    <li><a href="https://www.bing.com/translator" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?> Bing Translate</a></li>
  </ul>

  <p>
    <button type="button" class="btn btn-primary" name="prefill_fields"><?php echo language::translate('title_prefill_fields', 'Prefill Fields'); ?></button>
  </p>

</div>

<script>
  var delimiter = "\r\n----------\r\n";

  $('#translator-tool select').change(function(e){

    var box = $(this).closest('.row');
    var from_language_code = $(this).closest('.row').find('select[name="from_language_code"]').val();
    var to_language_code = $(this).closest('.row').find('select[name="to_language_code"]').val();
    var translations = [];

    if (!from_language_code || !to_language_code) return;

    $.each($(':input[name$="[text_'+ from_language_code +']"]'), function(i){
      var source = $(this).closest('tr').find(':input[name^="translations"][name$="[text_'+ from_language_code +']"]');
      var target = $(this).closest('tr').find(':input[name^="translations"][name$="[text_'+ to_language_code +']"]');

      if ($(source).val() && !$(target).val()) {
        translations.push('{{'+ i +'}} = ' + $(source).val());
      }
    });

    translations = translations.join(delimiter);

    $(box).find(':input[name="source"]').val(translations);
  });

  $('#translator-tool :input[name="source"]').focus(function(e){
    $(this).select();
  });

  $('#translator-tool button[name="prefill_fields"]').click(function(){
    var box = $(this).closest('div');

    var translated = $(box).find(':input[name="result"]').val();
    translated = translated.split(delimiter.trim());

    if ($(box).find('select[name="to_language_code"]').val() == '') {
      alert('You must specify which language you are translating');
      return false;
    }

    $.each(translated, function(i){
      var matches = translated[i].trim().match(/^\{\{([0-9]+)\}\} = (.*)$/);
      var index = matches[1];
      var translation = matches[2].trim();

      $(':input[name$="[text_'+ $(box).find('select[name="to_language_code"]').val() +']"]:eq('+ index +')').val(translation).css('border', '1px solid #f00');
    });

    $.featherlight.close();
  });

  $('.delete').click(function(e){
    e.preventDefault();

    if (!confirm('<?php echo language::translate('text_are_you_sure', 'Are you sure?'); ?>')) return false;

    var form = '<?php echo str_replace(array("\r", "\n"), '', functions::form_draw_form_begin('delete_translation_form', 'post')); ?>'
             + '<?php echo str_replace(array("\r", "\n"), '', form_draw_hidden_field('translation_id', 'insert_translation_id')); ?>'
             + '<?php echo str_replace(array("\r", "\n"), '', functions::form_draw_hidden_field('delete', 'true')); ?>'
             + '<?php echo str_replace(array("\r", "\n"), '', functions::form_draw_form_end()); ?>';

    form = form.replace(/insert_translation_id/g, $(this).closest('tr').find('input[name$="[id]"]').val());

    $(document.body).append(form);

    $(form).submit();
  });
</script>
<?php } ?>