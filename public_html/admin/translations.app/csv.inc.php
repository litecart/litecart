<?php
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

      $language_codes = array_diff(array_keys($csv[0]), array('code'));

      $num_inserted_translations = 0;
      $num_updated_translations = 0;

      foreach ($csv as $row) {

        $translation_query = database::query(
          "select * from ". DB_TABLE_TRANSLATIONS ."
          where code = '". database::input($row['code']) ."'
          limit 1;"
        );

        if (!$translation = database::fetch($translation_query)) {

          if (!empty($_POST['insert'])) {

            database::query(
              "insert into ". DB_TABLE_TRANSLATIONS ."
              (code) values ('". database::input($row['code']) ."');"
            );

            foreach ($language_codes as $language_code) {

              if (empty($row[$language_code])) continue;

              if (!in_array($language_code, array_keys(language::$languages))) continue;

              database::query(
                "update ". DB_TABLE_TRANSLATIONS ."
                set text_". $language_code ." = '". database::input($row[$language_code], true) ."'
                where code = '". database::input($row['code']) ."'
                limit 1;"
              );

              $num_inserted_translations++;
            }
          }

        } else {

          foreach ($language_codes as $language_code) {

            if (empty($row[$language_code])) continue;

            if (empty($_POST['overwrite']) && empty($_POST['append'])) continue;
            if (empty($translation['text_'.$language_code]) && empty($_POST['append'])) continue;
            if (!empty($translation['text_'.$language_code]) && empty($_POST['overwrite'])) continue;

            if (!in_array($language_code, array_keys(language::$languages))) continue;

            database::query(
              "update ". DB_TABLE_TRANSLATIONS ."
              set text_". $language_code ." = '". database::input($row[$language_code], true) ."'
              where code = '". database::input($row['code']) ."'
              limit 1;"
            );

            $num_updated_translations++;
          }
        }
      }

      cache::clear_cache('translations');

      notices::add('success', sprintf(language::translate('success_d_translations_imported', 'Inserted %d new translations, updated %d translations'), $num_inserted_translations, $num_updated_translations));

      header('Location: '. document::link(WS_DIR_ADMIN, array('app' => $_GET['app'], 'doc' => $_GET['doc'])));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['export'])) {

    try {
      if (empty($_POST['language_codes'])) throw new Exception(language::translate('error_must_select_at_least_one_language', 'You must select at least one language'));

      $csv = array();

      $_POST['language_codes'] = array_filter($_POST['language_codes']);

      $translations_query = database::query(
        "select * from ". DB_TABLE_TRANSLATIONS ."
        order by date_created asc;"
      );

      while ($translation = database::fetch($translations_query)) {

        $row = array('code' => $translation['code']);
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
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_csv_import_export', 'CSV Import/Export'); ?>
  </div>

  <ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#tab-import"><?php echo language::translate('title_import_from_csv', 'Import From CSV'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-export"><?php echo language::translate('title_export_to_csv', 'Export To CSV'); ?></a></li>
  </ul>

  <div class="panel-body">
    <div class="tab-content" style="max-width: 640px;">

      <div id="tab-import" class="tab-pane active">
        <?php echo functions::form_draw_form_begin('import_form', 'post', '', true); ?>

          <div class="form-group">
            <label><?php echo language::translate('title_csv_file', 'CSV File'); ?></label>
            <?php echo functions::form_draw_file_field('file'); ?></td>
          </div>

          <div class="row">
            <div class="form-group col-sm-6">
              <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
              <?php echo functions::form_draw_select_field('delimiter', array(array(language::translate('title_auto', 'Auto') .' ('. language::translate('text_default', 'default') .')', ''), array(','),  array(';'), array('TAB', "\t"), array('|')), true); ?>
            </div>

            <div class="form-group col-sm-6">
              <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
              <?php echo functions::form_draw_select_field('enclosure', array(array('" ('. language::translate('text_default', 'default') .')', '"')), true); ?>
            </div>

            <div class="form-group col-sm-6">
              <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
              <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true); ?>
            </div>

            <div class="form-group col-sm-6">
              <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
              <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
            </div>
          </div>

          <div class="form-group">
            <div class="checkbox"><label><?php echo functions::form_draw_checkbox('insert', '1', true); ?> <?php echo language::translate('text_insert_new_entries', 'Insert new entries'); ?></label></div>
            <div class="checkbox"><label><?php echo functions::form_draw_checkbox('overwrite', '1', true); ?> <?php echo language::translate('text_overwrite_existing_entries', 'Overwrite existing entries'); ?></label></div>
            <div class="checkbox"><label><?php echo functions::form_draw_checkbox('append', '1', isset($_POST['append']) ? true : '1'); ?> <?php echo language::translate('text_append_missing_entries', 'Append missing entries'); ?></label></div>
          </div>

          <p><?php echo language::translate('description_scan_before_importing_translations', 'It is recommended to always scan your installation for unregistered translations before performing an import or export.'); ?></p>

          <?php echo functions::form_draw_button('import', language::translate('title_import', 'Import'), 'submit'); ?>

        <?php echo functions::form_draw_form_end(); ?>
      </div>

      <div id="tab-export" class="tab-pane">

        <?php echo functions::form_draw_form_begin('export_form', 'post'); ?>

          <div class="form-group">
            <label><?php echo language::translate('title_languages', 'Languages'); ?></label>
            <?php echo functions::form_draw_languages_list('language_codes[]', true, true).' '; ?></td>
          </div>

          <div class="row">
            <div class="form-group col-sm-6">
              <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
              <?php echo functions::form_draw_select_field('delimiter', array(array(', ('. language::translate('text_default', 'default') .')', ','), array(';'), array('TAB', "\t"), array('|')), true); ?>
            </div>

            <div class="form-group col-sm-6">
              <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
              <?php echo functions::form_draw_select_field('enclosure', array(array('" ('. language::translate('text_default', 'default') .')', '"')), true); ?>
            </div>

            <div class="form-group col-sm-6">
              <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
              <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true); ?>
            </div>

            <div class="form-group col-sm-6">
              <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
              <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
            </div>

            <div class="form-group col-sm-6">
              <label><?php echo language::translate('title_line_ending', 'Line Ending'); ?></label>
              <?php echo functions::form_draw_select_field('eol', array(array('Win'), array('Mac'), array('Linux')), true); ?>
            </div>

            <div class="form-group col-sm-6">
              <label><?php echo language::translate('title_output', 'Output'); ?></label>
              <?php echo functions::form_draw_select_field('output', array(array(language::translate('title_file', 'File'), 'file'), array(language::translate('title_screen', 'Screen'), 'screen')), true); ?>
            </div>
          </div>

          <?php echo functions::form_draw_button('export', language::translate('title_export', 'Export'), 'submit'); ?>

        <?php echo functions::form_draw_form_end(); ?>
      </div>
    </div>
  </div>
</div>
