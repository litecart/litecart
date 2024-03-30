<?php

  breadcrumbs::add(language::translate('title_csv_import_export', 'CSV Import/Export'));

  if (isset($_POST['import'])) {

    try {

      ob_clean();

      header('Content-type: text/plain; charset='. mb_http_output());

      if (!isset($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
        throw new Exception(language::translate('error_must_select_file_to_upload', 'You must select a file to upload'));
      }

      if (!empty($_FILES['file']['error'])) {
        throw new Exception(language::translate('error_uploaded_file_rejected', 'An uploaded file was rejected for unknown reason'));
      }

      echo "CSV Import\r\n"
         . "----------\r\n";

      $csv = file_get_contents($_FILES['file']['tmp_name']);

      if (!$csv = functions::csv_decode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'])) {
        throw new Exception(language::translate('error_failed_decoding_csv', 'Failed decoding CSV'));
      }

      if (!empty($_POST['reset'])) {

        echo PHP_EOL
           . 'Wiping data...' . PHP_EOL . PHP_EOL;

        database::multi_query(
          "truncate ". DB_TABLE_PREFIX ."pages;
          truncate ". DB_TABLE_PREFIX ."pages_info;"
        );
      }

      $updated = 0;
      $inserted = 0;
      $line = 1;

      foreach ($csv as $row) {
        $line++;

      // Find page
        if (!empty($row['id']) && $page = database::query("select id from ". DB_TABLE_PREFIX ."pages where id = ". (int)$row['id'] ." limit 1;")->fetch()) {
          $page = new ent_page($page['id']);
        }

        if (!empty($page->data['id'])) {

          if (empty($_POST['overwrite'])) {
            echo "Skip updating existing page on line $line" . PHP_EOL;
            continue;
          }

          echo 'Updating existing page '. fallback($row['name'], "on line $line") . PHP_EOL;
          $updated++;

        } else {

          if (empty($_POST['insert'])) {
            echo "Skip inserting new page on line $line" . PHP_EOL;
            continue;
          }

          echo 'Inserting new page: '. fallback($row['name'], "on line $line") . PHP_EOL;
          $inserted++;

          if (!empty($row['id'])) {
            database::query(
              "insert into ". DB_TABLE_PREFIX ."pages (id, date_created)
              values (". (int)$row['id'] .", '". date('Y-m-d H:i:s') ."');"
            );
            $page = new ent_page($row['id']);
          } else {
            $page = new ent_page();
          }
        }

        $row['dock'] = preg_split('#\s*,\s*#', $row['dock'], -1, PREG_SPLIT_NO_EMPTY);

      // Set page data

        foreach ([
          'parent_id',
          'status',
          'dock',
        ] as $field) {
          if (isset($row[$field])) {
            $page->data[$field] = $row[$field];
          }
        }

        foreach ([
          'title',
          'content',
          'head_title',
          'meta_description',
        ] as $field) {
          if (isset($row[$field])) {
            $page->data[$field][$row['language_code']] = $row[$field];
          }
        }

        $page->save();
      }

      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['export'])) {

    try {

      if (empty($_POST['language_code'])) {
        throw new Exception(language::translate('error_must_select_a_language', 'You must select a language'));
      }

      $csv = database::query(
        "select p.*, pi.title, pi.content, pi.head_title, pi.meta_description, '". database::input($_POST['language_code']) ."' as language_code
        from ". DB_TABLE_PREFIX ."pages p
        left join ". DB_TABLE_PREFIX ."pages_info pi on (pi.page_id = p.id and pi.language_code = '". database::input($_POST['language_code']) ."')
        order by pid;"
      )->export($result)->fetch_all();

      if (!$csv) {
        $csv = [array_fill_keys($result->fields(), '')];
      }

      ob_clean();

      if ($_POST['output'] == 'screen') {
        header('Content-type: text/plain; charset='. $_POST['charset']);
      } else {
        header('Content-type: application/csv; charset='. $_POST['charset']);
        header('Content-Disposition: attachment; filename=pages-'. $_POST['language_code'] .'.csv');
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
    <div class="row" style="max-width: 980px;">

      <div class="col-xl-6">
        <?php echo functions::form_begin('import_form', 'post', '', true); ?>

          <fieldset>
            <legend><?php echo language::translate('title_import', 'Import'); ?></legend>

            <div class="form-group">
              <label><?php echo language::translate('title_csv_file', 'CSV File'); ?></label>
              <?php echo functions::form_input_file('file', 'accept=".csv, .dsv, .tab, .tsv"'); ?>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
                <?php echo functions::form_select('delimiter', ['' => language::translate('title_auto', 'Auto') .' ('. language::translate('text_default', 'default') .')', ',' => ',',  ';' => ';', "\t" => 'TAB', '|' => '|'], true); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
                <?php echo functions::form_select('enclosure', ['"' => '" ('. language::translate('text_default', 'default') .')'], true); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
                <?php echo functions::form_select('escapechar', ['"' => '" ('. language::translate('text_default', 'default') .')', '\\' => '\\'], true); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
                <?php echo functions::form_select_encoding('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
              </div>
            </div>

            <div class="form-group">
              <?php echo functions::form_input_checkbox('reset', ['1', language::translate('text_wipe_storage_clean_before_inserting_data', 'Wipe storage clean before inserting data')], true); ?>
              <?php echo functions::form_input_checkbox('insert', ['1', language::translate('text_insert_new_entries', 'Insert new entries')], true); ?>
              <?php echo functions::form_input_checkbox('overwrite', ['1', language::translate('text_overwrite_existing_entries', 'Overwrite existing entries')], true); ?>
            </div>

            <?php echo functions::form_button('import', language::translate('title_import', 'Import'), 'submit'); ?>
          </fieldset>

        <?php echo functions::form_end(); ?>
      </div>

      <div class="col-xl-6">
        <?php echo functions::form_begin('export_form', 'post'); ?>

          <fieldset>
            <legend><?php echo language::translate('title_export', 'Export'); ?></legend>

            <div class="form-group">
              <label><?php echo language::translate('title_language', 'Language'); ?></label>
              <?php echo functions::form_select_language('language_code', true); ?>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
                <?php echo functions::form_select('delimiter', [',' => ', ('. language::translate('text_default', 'default') .')', ';' => ';', "\t" => 'TAB', '|' => '|'], true); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
                <?php echo functions::form_select('enclosure', ['"' => '" ('. language::translate('text_default', 'default') .')'], true); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
                <?php echo functions::form_select('escapechar', ['"' => '" ('. language::translate('text_default', 'default') .')', '\\' => '\\'], true); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
                <?php echo functions::form_select_encoding('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_line_ending', 'Line Ending'); ?></label>
                <?php echo functions::form_select('eol', ['Win', 'Mac', 'Linux'], true); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_output', 'Output'); ?></label>
                <?php echo functions::form_select('output', ['file' => language::translate('title_file', 'File'), 'screen' => language::translate('title_screen', 'Screen')], true); ?>
              </div>
            </div>

            <?php echo functions::form_button('export', language::translate('title_export', 'Export'), 'submit'); ?>
          </fieldset>

        <?php echo functions::form_end(); ?>
      </div>
    </div>
  </div>
</div>

<script>
  $('form[name="import_form"] input[name="reset"]').click(function(){
    if ($(this).is(':checked') && !confirm("<?php echo language::translate('text_are_you_sure', 'Are you sure?'); ?>")) return false;
  });

  $('form[name="import_form"] input[name="insert"]').change(function(){
    $('form[name="import_form"] input[name="reset"]').prop('checked', false).prop('disabled', !$(this).is(':checked'));
  }).trigger('change');
</script>