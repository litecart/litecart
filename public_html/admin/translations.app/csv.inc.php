<?php

  document::$snippets['title'][] = language::translate('title_import_export_csv', 'Import/Export CSV');

  breadcrumbs::add(language::translate('title_import_export_csv', 'Import/Export CSV'));

  if (isset($_POST['import'])) {

    try {
      if (!isset($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
        throw new Exception(language::translate('error_must_select_file_to_upload', 'You must select a file to upload'));
      }

      $csv = file_get_contents($_FILES['file']['tmp_name']);

      if (!$csv = functions::csv_decode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'])) {
        throw new Exception(language::translate('error_failed_decoding_csv', 'Failed decoding CSV'));
      }

      if (empty($csv[0]['code'])) throw new Exception(language::translate('error_missing_code_column', 'Missing column for code'));

      $language_codes = array_diff(array_keys($csv[0]), ['code']);

      $updated = 0;
      $inserted = 0;
      $line = 0;

      foreach ($csv as $row) {
        $line++;

        $translation_query = database::query(
          "select * from ". DB_TABLE_PREFIX ."translations
          where code = '". database::input($row['code']) ."'
          limit 1;"
        );

        if ($translation = database::fetch($translation_query)) {

          foreach ($language_codes as $language_code) {

            if (empty($row[$language_code])) continue;
            if (empty($_POST['update']) && empty($_POST['append'])) continue;
            if (empty($translation['text_'.$language_code]) && empty($_POST['append'])) continue;
            if (!empty($translation['text_'.$language_code]) && empty($_POST['update'])) continue;

            if (!in_array($language_code, array_keys(language::$languages))) continue;

            database::query(
              "update ". DB_TABLE_PREFIX ."translations
              set text_". $language_code ." = '". database::input($row[$language_code], true) ."'
              where code = '". database::input($row['code']) ."'
              limit 1;"
            );

            $updated++;
          }

        } else {

          if (empty($_POST['insert'])) continue;

          database::query(
            "insert into ". DB_TABLE_PREFIX ."translations
            (code) values ('". database::input($row['code']) ."');"
          );

          foreach ($language_codes as $language_code) {

            if (empty($row[$language_code])) continue;

            if (!in_array($language_code, array_keys(language::$languages))) continue;

            database::query(
              "update ". DB_TABLE_PREFIX ."translations
              set text_". $language_code ." = '". database::input($row[$language_code], true) ."'
              where code = '". database::input($row['code']) ."'
              limit 1;"
            );

            $inserted++;
          }
        }
      }

      cache::clear_cache('translations');

      notices::add($updated ? 'success' : 'notice', strtr(language::translate('success_updated_n_existing_entries', 'Updated %n existing entries'), ['%n' => $updated]));
      notices::add($inserted ? 'success' : 'notice', strtr(language::translate('success_insert_n_new_entries', 'Inserted %n new entries'), ['%n' => $inserted]));

      header('Location: '. document::link(WS_DIR_ADMIN, ['app' => $_GET['app'], 'doc' => $_GET['doc']]));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['export'])) {

    try {
      if (empty($_POST['language_codes'])) throw new Exception(language::translate('error_must_select_at_least_one_language', 'You must select at least one language'));

      $csv = [];

      $_POST['language_codes'] = array_filter($_POST['language_codes']);

      $translations_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."translations
        order by date_created asc;"
      );

      while ($translation = database::fetch($translations_query)) {

        $row = ['code' => $translation['code']];
        foreach ($_POST['language_codes'] as $language_code) {
          $row[$language_code] = $translation['text_'.$language_code];
        }

        $csv[] = $row;
      }

      ob_clean();

      if ($_POST['output'] == 'screen') {
        header('Content-Type: text/plain; charset='. $_POST['charset']);
      } else {
        header('Content-Type: application/csv; charset='. $_POST['charset']);
        header('Content-Disposition: attachment; filename=translations-'. implode('-', $_POST['language_codes']) .'.csv');
      }

      switch($_POST['eol']) {
        case 'Linux':
          echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\r");
          break;
        case 'Mac':
          echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\n");
          break;
        case 'Win':
        default:
          echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\r\n");
          break;
      }

      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_csv_import_export', 'CSV Import/Export'); ?>
    </div>
  </div>

  <div class="card-body">
    <div class="row">

      <div class="col-sm-6 col-lg-4">
        <?php echo functions::form_draw_form_begin('import_form', 'post', '', true); ?>

          <fieldset>
            <legend><?php echo language::translate('title_import', 'Import'); ?></legend>

            <div class="form-group">
              <label><?php echo language::translate('title_csv_file', 'CSV File'); ?></label>
              <?php echo functions::form_draw_file_field('file'); ?></td>
            </div>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
                <?php echo functions::form_draw_select_field('delimiter', [[language::translate('title_auto', 'Auto') .' ('. language::translate('text_default', 'default') .')', ''], [','],  [';'], ['TAB', "\t"], ['|']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
                <?php echo functions::form_draw_select_field('enclosure', [['" ('. language::translate('text_default', 'default') .')', '"']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
                <?php echo functions::form_draw_select_field('escapechar', [['" ('. language::translate('text_default', 'default') .')', '"'], ['\\', '\\']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
                <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
              </div>
            </div>

            <div class="form-group">
              <div class="checkbox">
                <label><?php echo functions::form_draw_checkbox('update', '1', true); ?> <?php echo language::translate('title_update_existing', 'Update Existing'); ?></label>
              </div>
              <div class="checkbox">
                <label><?php echo functions::form_draw_checkbox('insert', '1', true); ?> <?php echo language::translate('text_insert_new', 'Insert New'); ?></label>
              </div>
              <div class="checkbox">
                <label><?php echo functions::form_draw_checkbox('append', '1', isset($_POST['append']) ? true : '1'); ?> <?php echo language::translate('text_append_missing', 'Append Missing'); ?></label>
              </div>
            </div>

            <p><?php echo language::translate('description_scan_before_importing_translations', 'It is recommended to always scan your installation for unregistered translations before performing an import or export.'); ?></p>

            <?php echo functions::form_draw_button('import', language::translate('title_import', 'Import'), 'submit'); ?>
          </fieldset>

        <?php echo functions::form_draw_form_end(); ?>
      </div>

      <div class="col-sm-6 col-lg-4">
        <?php echo functions::form_draw_form_begin('export_form', 'post'); ?>

          <fieldset>
            <legend><?php echo language::translate('title_export', 'Export'); ?></legend>

            <div class="form-group">
              <label><?php echo language::translate('title_languages', 'Languages'); ?></label>
              <?php echo functions::form_draw_languages_list('language_codes[]', true, true); ?></td>
            </div>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
                <?php echo functions::form_draw_select_field('delimiter', [[', ('. language::translate('text_default', 'default') .')', ','], [';'], ['TAB', "\t"], ['|']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
                <?php echo functions::form_draw_select_field('enclosure', [['" ('. language::translate('text_default', 'default') .')', '"']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
                <?php echo functions::form_draw_select_field('escapechar', [['" ('. language::translate('text_default', 'default') .')', '"'], ['\\', '\\']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
                <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_line_ending', 'Line Ending'); ?></label>
                <?php echo functions::form_draw_select_field('eol', [['Win'], ['Mac'], ['Linux']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_output', 'Output'); ?></label>
                <?php echo functions::form_draw_select_field('output', [[language::translate('title_file', 'File'), 'file'], [language::translate('title_screen', 'Screen'), 'screen']], true); ?>
              </div>
            </div>

            <?php echo functions::form_draw_button('export', language::translate('title_export', 'Export'), 'submit'); ?>
          </fieldset>

        <?php echo functions::form_draw_form_end(); ?>
      </div>
    </div>
  </div>
</div>
