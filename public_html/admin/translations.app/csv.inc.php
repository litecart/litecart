<?php

  if (!empty($_POST['import'])) {

    if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {

      $csv = file_get_contents($_FILES['file']['tmp_name']);

      $csv = functions::csv_decode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset']);

      $num_inserted_translations = 0;
      $num_updated_translations = 0;

      foreach ($csv as $row) {

        $translation_query = database::query(
          "select * from ". DB_TABLE_TRANSLATIONS ."
          where code = '". $row['code'] ."'
          limit 1;"
        );
        $translation = database::fetch($translation_query);

        if (empty($translation)) {

          if (!empty($_POST['insert'])) {
            database::query(
              "insert into ". DB_TABLE_TRANSLATIONS ."
              (code)
              values ('". database::input($row['code']) ."');"
            );
            foreach (array_slice(array_keys($row), 1) as $language_code) {
              if (empty($translation['text_'.$language_code]) || !empty($_POST['overwrite'])) {
                database::query(
                  "update ". DB_TABLE_TRANSLATIONS ."
                  set text_". $language_code ." = '". database::input($row[$language_code], true) ."'
                  where code = '". $row['code'] ."'
                  limit 1;"
                );
                $num_inserted_translations++;
              }
            }
          }

        } else {

          foreach (array_slice(array_keys($row), 1) as $language_code) {
            if (!empty($_POST['overwrite']) || (empty($translation['text_'.$language_code]) && !empty($row[$language_code]))) {
              database::query(
                "update ". DB_TABLE_TRANSLATIONS ."
                set text_". $language_code ." = '". database::input($row[$language_code], true) ."'
                where code = '". $row['code'] ."'
                limit 1;"
              );
              $num_updated_translations++;
            }
          }
        }
      }

      cache::clear_cache('translations');

      notices::add('success', sprintf(language::translate('success_d_translations_imported', 'Inserted %d new translations, updated %d translations'), $num_inserted_translations, $num_updated_translations));

      header('Location: '. document::link('', array('app' => $_GET['app'], 'doc' => $_GET['doc'])));
      exit;
    }
  }

  if (!empty($_POST['export'])) {

    if (empty($_POST['language_codes'])) notices::add('errors', language::translate('error_must_select_at_least_one_language', 'You must select at least one language'));

    if (empty(notices::$data['errors'])) {

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
    }
  }

?>
<h1><?php echo $app_icon; ?> <?php echo language::translate('title_csv_import_export', 'CSV Import/Export'); ?></h1>

<p><strong><?php echo language::translate('description_scan_before_importing_translations', 'It is recommended to always scan your installation for unregistered translations before performing an import or export.'); ?></strong></p>

<div class="row">
  <div class="col-md-3">

    <fieldset>
      <legend><?php echo language::translate('title_import_from_csv', 'Import From CSV'); ?></legend>

      <?php echo functions::form_draw_form_begin('import_form', 'post', '', true); ?>

        <div class="form-group">
          <label><?php echo language::translate('title_csv_file', 'CSV File'); ?></label>
          <?php echo functions::form_draw_file_field('file'); ?></td>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
          <?php echo functions::form_draw_select_field('delimiter', array(array(language::translate('title_auto', 'Auto') .' ('. language::translate('text_default', 'default') .')', ''), array(','),  array(';'), array('TAB', "\t"), array('|')), true, false); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
          <?php echo functions::form_draw_select_field('enclosure', array(array('" ('. language::translate('text_default', 'default') .')', '"')), true, false); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?><br />
          <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true, false); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
          <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8', false); ?>
        </div>

        <div class="form-group">
          <div class="checkbox"><label><?php echo functions::form_draw_checkbox('insert', '1', isset($_POST['insert']) ? true : '1'); ?> <?php echo language::translate('text_insert_new_entries', 'Insert new entries'); ?></label></div>
          <div class="checkbox"><label><?php echo functions::form_draw_checkbox('overwrite', '1', isset($_POST['insert']) ? true : ''); ?> <?php echo language::translate('text_overwrite_existing_entries', 'Overwrite existing entries'); ?></label></div>
        </div>

        <?php echo functions::form_draw_button('import', language::translate('title_import', 'Import'), 'submit'); ?>

      <?php echo functions::form_draw_form_end(); ?>

    </fieldset>
  </div>

  <div class="col-md-3">
    <fieldset>
      <legend><?php echo language::translate('title_export_to_csv', 'Export To CSV'); ?></legend>

      <?php echo functions::form_draw_form_begin('export_form', 'post'); ?>

        <div class="form-group">
          <label><?php echo language::translate('title_languages', 'Languages'); ?></label>
          <?php echo functions::form_draw_languages_list('language_codes[]', true, true).' '; ?></td>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
          <?php echo functions::form_draw_select_field('delimiter', array(array(', ('. language::translate('text_default', 'default') .')', ','), array(';'), array('TAB', "\t"), array('|')), true, false); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
          <?php echo functions::form_draw_select_field('enclosure', array(array('" ('. language::translate('text_default', 'default') .')', '"')), true, false); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
          <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true, false); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
          <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8', false); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_line_ending', 'Line Ending'); ?></label>
          <?php echo functions::form_draw_select_field('eol', array(array('Win'), array('Mac'), array('Linux')), true, false); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_output', 'Output'); ?></label>
          <?php echo functions::form_draw_select_field('output', array(array(language::translate('title_file', 'File'), 'file'), array(language::translate('title_screen', 'Screen'), 'screen')), true, false); ?>
        </div>

        <?php echo functions::form_draw_button('export', language::translate('title_export', 'Export'), 'submit'); ?>

      <?php echo functions::form_draw_form_end(); ?>
    </fieldset>
  </div>
</div>
