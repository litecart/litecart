<?php
  if (!isset($_GET['query'])) $_GET['query'] = '';
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['languages'])) $_GET['languages'] = array_slice(array_keys(language::$languages), 0, 2);

  if (!empty($_GET['languages'])) {
    foreach (array_keys($_GET['languages']) as $key) {
      if (!in_array($_GET['languages'][$key], array_keys(language::$languages))) unset($_GET['languages'][$key]);
    }
  }

  document::$snippets['title'][] = language::translate('title_search_translations', 'Search Translations');

  breadcrumbs::add(language::translate('title_translations', 'Translations'));
  breadcrumbs::add(language::translate('title_search_translations', 'Search Translations'));

  if (isset($_POST['save']) && !empty($_POST['translations'])) {

    foreach ($_POST['translations'] as $translation) {
      $sql_update_fields = '';
      foreach ($_GET['languages'] as $language_code) {
        $sql_update_fields .= "text_".database::input($language_code) ." = '". database::input(trim($translation['text_'.database::input($language_code)]), !empty($translation['html']) ? true : false) ."', " . PHP_EOL;
      }
      database::query(
        "update ". DB_TABLE_PREFIX ."translations
        set
        html = ". (!empty($translation['html']) ? 1 : 0) .",
          ". $sql_update_fields ."
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = ". (int)$translation['id'] ."
        limit 1;"
      );
    }

    cache::clear_cache('translations');

    notices::add('success', language::translate('success_changes_saved', 'Changes saved'));

    header('Location: '. document::link(WS_DIR_ADMIN, [], true));
    exit;
  }

  if (isset($_POST['delete']) && !empty($_POST['translation_id'])) {

    database::query(
      "delete from ". DB_TABLE_PREFIX ."translations
      where id = '". database::input($_POST['translation_id']) ."'
      limit 1;"
    );

    cache::clear_cache('translations');

    echo json_encode(['status' => 'ok']);
    exit;
  }

// Languages
  $languages_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."languages
    where code in ('". implode("', '", database::input($_GET['languages'])) ."')
    order by priority;"
  );

  $languages = [];
  while ($language = database::fetch($languages_query)) {
    $languages[$language['code']] = $language;
  }

// Table Rows
  $translations = [];

  $translations_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."translations
    where code != ''
    ". ((!empty($_GET['endpoint']) && $_GET['endpoint'] == 'frontend') ? "and frontend = 1" : null) ."
    ". ((!empty($_GET['endpoint']) && $_GET['endpoint'] == 'backend') ? "and backend = 1" : null) ."
    ". (!empty($_GET['query']) ? "and (code like '%". str_replace('%', "\\%", database::input($_GET['query'])) ."%' or `text_". implode("` like '%". database::input($_GET['query']) ."%' or `text_", database::input($_GET['languages'])) ."` like '%". database::input($_GET['query']) ."%')" : null) ."
    ". (!empty($_GET['untranslated']) ? "and (`text_". implode("` = '' or `text_", database::input($_GET['languages'])) ."` = '')" : null) ."
    ". (empty($_GET['modules']) ? "and (code not like '". implode("_%:%' and code not like '", ['cm', 'job', 'om', 'ot', 'pm', 'sm']) ."_%:%')" : null) ."
    order by date_updated desc;"
  );

  if ($_GET['page'] > 1) database::seek($translations_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($translation = database::fetch($translations_query)) {
    $translations[] = $translation;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($translations_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));

  functions::draw_lightbox();
?>
<style>
ul.filter li {
  display: table-cell;
  vertical-align: middle;
}
th:not(:last-child) {
  min-width: 250px;
}
</style>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_search_translations', 'Search Translations'); ?>
    </div>
  </div>

  <?php echo functions::form_draw_form_begin('search_form', 'get'); ?>
    <?php echo functions::form_draw_hidden_field('app', true); ?>
    <?php echo functions::form_draw_hidden_field('doc', true); ?>
    <div class="card-filter">
      <?php if (count($_GET['languages']) > 1) { ?>
      <div>
        <button type="button" class="btn btn-default translator-tool" data-toggle="lightbox" data-target="#translator-tool" data-width="980px"><?php echo language::translate('title_translator_tool', 'Translator Tool'); ?></button>
      </div>
      <?php } ?>
      <div class="expandable"><?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'"'); ?></div>
      <div><?php echo functions::form_draw_select_field('endpoint', [['-- '. language::translate('title_all', 'All') .' --', ''], [language::translate('title_frontend', 'Frontend'), 'frontend'], [language::translate('title_backend', 'Backend'), 'backend']]); ?></div>
      <div>
        <label><?php echo functions::form_draw_checkbox('modules', 'true'); ?> <?php echo language::translate('text_inlcude_modules', 'Include modules'); ?></label><br />
        <label><?php echo functions::form_draw_checkbox('untranslated', 'true'); ?> <?php echo language::translate('text_only_untranslated', 'Only untranslated'); ?></label>
      </div>
      <div>
        <label><?php echo language::translate('title_languages', 'Languages'); ?></label>
        <div><?php foreach (array_keys(language::$languages) as $language_code) echo '<span style="padding: 0.25em;">'. functions::form_draw_checkbox('languages[]', $language_code) .' '. $language_code .'</span>'; ?></div>
      </div>
      <div><?php echo functions::form_draw_button('filter', language::translate('title_search', 'Search'), 'submit'); ?></div>
    </div>
  <?php echo functions::form_draw_form_end(); ?>

  <div class="card-body">
    <?php echo functions::form_draw_form_begin('translation_form', 'post'); ?>

      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th><?php echo language::translate('title_code', 'Code'); ?></th>
              <?php foreach ($_GET['languages'] as $language_code) echo '<th style="width: 480px;">'. $languages[$language_code]['name'] .'</th>'; ?>
              <th></th>
            </tr>
          </thead>

          <tbody>
            <?php foreach ($translations as $translation) { ?>
            <tr>
              <td><?php echo $translation['code']; ?><br />
                <small style="color: #999;"><?php echo functions::form_draw_checkbox('translations['. $translation['code'] .'][html]', '1', (isset($_POST['translations'][$translation['code']]['html']) ? $_POST['translations'][$translation['code']]['html'] : $translation['html'])); ?> <?php echo language::translate('text_html_enabled', 'HTML enabled'); ?></small>
              </td>
              <?php foreach ($_GET['languages'] as $key => $language_code) { ?>
              <td>
                <?php echo functions::form_draw_hidden_field('translations['. $translation['code'] .'][id]', $translation['id']); ?>
                <?php echo functions::form_draw_textarea('translations['. $translation['code'] .'][text_'.$language_code.']', $translation['text_'.$language_code], 'rows="2" dir="'. language::$languages[$language_code]['direction'] .'" tabindex="'. $key.str_pad($page_items+1, 2, '0', STR_PAD_LEFT) .'"'); ?>
              </td>
              <?php } ?>
              <td style="text-align: end;"><a class="delete" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
            </tr>
            <?php } ?>
          </tbody>

          <tfoot>
            <tr>
              <td colspan="<?php echo 3 + count($_GET['languages']); ?>"><?php echo language::translate('title_translations', 'Translations'); ?>: <?php echo $num_rows; ?></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="card-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', 'tabindex="9999"', 'save'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>

    <?php echo functions::draw_pagination(ceil(database::num_rows($translations_query)/settings::get('data_table_rows_per_page'))); ?>
  </div>
</div>

<div id="translator-tool" style="display: none;">
  <h2><?php echo language::translate('title_translator_tool', 'Translator Tool'); ?></h2>

  <div class="row">
    <div class="col-md-6">
      <div class="form-group">
        <label><?php echo language::translate('title_from_language', 'From Language'); ?></label>
<?php
  $options = [['-- '. language::translate('title_select', 'Select') .' --']];
  foreach ($_GET['languages'] as $language_code) {
    $options[] = [language::$languages[$language_code]['name'], $language_code];
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
        <textarea class="form-control" name="source" style="height: 320px;" readonly></textarea>
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

  $('textarea[name^="translations"]').on('input', function(){
    $(this).height('auto').height($(this).prop('scrollHeight') + 'px');
  }).trigger('input');

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

    if (!window.confirm('<?php echo language::translate('text_are_you_sure', 'Are you sure?'); ?>')) return false;

    var row = $(this).closest('tr');

    $.ajax({
      type: 'post',
      data: 'translation_id=' + $(row).find('input[name$="[id]"]').val() + '&delete=true',
      cache: false,
      async: true,
      dataType: 'json',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType('text/html;charset=' + $('meta[charset]').attr('charset'));
      },
      error: function(jqXHR, textStatus, errorThrown) {
        alert('An error occurred');
      },
      success: function(json) {
        if (json['status'] && json['status'] == 'ok') {
          $(row).remove();
        }
      }
    });
  });
</script>
