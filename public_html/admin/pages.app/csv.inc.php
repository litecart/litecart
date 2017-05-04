<?php

  if (!empty($_POST['export'])) {

    if (empty($_POST['language_code'])) notices::add('errors', language::translate('error_must_select_a_language', 'You must select a language'));

    if (empty(notices::$data['errors'])) {

      ob_clean();

      $csv = array();

      $pages_query = database::query("select id from ". DB_TABLE_PAGES ." order by id;");
      while ($page = database::fetch($pages_query)) {
        $page = reference::page($page['id']);

        $csv[] = array(
          'id' => $page->id,
          'status' => $page->status,
          'dock' => implode(',', $page->dock),
          'title' => $page->title[$_POST['language_code']],
          'content' => $page->content[$_POST['language_code']],
          'head_title' => $page->head_title[$_POST['language_code']],
          'meta_description' => $page->meta_description[$_POST['language_code']],
          'priority' => $page->priority,
          'language_code' => $_POST['language_code'],
        );
      }

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
    }
  }

  if (!empty($_POST['import'])) {

    if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {

      ob_clean();

      header('Content-type: text/plain; charset='. language::$selected['charset']);

      echo "CSV Import\r\n"
         . "----------\r\n";

      $csv = file_get_contents($_FILES['file']['tmp_name']);

      if (empty($_POST['delimiter'])) {
        preg_match('/^([^(\r|\n)]+)/', $csv, $matches);
        if (strpos($matches[1], ',') !== false) {
          $_POST['delimiter'] = ',';
        } elseif (strpos($matches[1], ';') !== false) {
          $_POST['delimiter'] = ';';
        } elseif (strpos($matches[1], "\t") !== false) {
          $_POST['delimiter'] = "\t";
        } elseif (strpos($matches[1], '|') !== false) {
          $_POST['delimiter'] = '|';
        } else {
          trigger_error('Unable to determine CSV delimiter', E_USER_ERROR);
        }
      }

      $csv = functions::csv_decode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset']);

      $line = 0;
      foreach ($csv as $row) {
        $line++;

      // Find page
        if (!empty($row['id'])) {
          $page_query = database::query(
            "select id from ". DB_TABLE_PAGES ."
            where id = '". (int)$row['id'] ."'
            limit 1;"
          );
        } else {
          echo "[Skipped] Could not identify page on line $line. Missing ID.\r\n";
          continue;
        }

        $page = database::fetch($page_query);

      // No page, let's create it
        if (empty($page)) {
          if (empty($_POST['insert'])) {
            echo "[Skipped] New page on line $line was not inserted to database.\r\n";
            continue;
          }
          $page = new ctrl_page();
          echo "Inserting new page '{$row['title']}'\r\n";

      // Get page
        } else {
          $page = new ctrl_page($page['id']);
          echo "Updating existing page '{$row['title']}'\r\n";
        }

        if (isset($row['dock'])) $row['dock'] = explode(',', $row['dock']);

      // Set new page data
        foreach (array('status', 'dock') as $field) {
          if (isset($row[$field])) $page->data[$field] = $row[$field];
        }

      // Set page info data
        foreach (array('title', 'content', 'head_title', 'meta_description') as $field) {
          if (isset($row[$field])) $page->data[$field][$row['language_code']] = $row[$field];
        }

        $page->save();
      }

      exit;
    }
  }

?>
<h1><?php echo $app_icon; ?> <?php echo language::translate('title_csv_import_export', 'CSV Import/Export'); ?></h1>

<div class="row">
  <div class="col-md-6">

    <div class="row">
      <div class="col-md-6">
        <fieldset class="well">
          <legend><?php echo language::translate('title_import_from_csv', 'Import From CSV'); ?></legend>

          <?php echo functions::form_draw_form_begin('import_pages_form', 'post', '', true); ?>

            <div class="form-group">
              <label><?php echo language::translate('title_csv_file', 'CSV File'); ?></label>
              <?php echo functions::form_draw_file_field('file'); ?>
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
              <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
              <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true, false); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
              <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8', false); ?>
            </div>

            <div class="form-group">
              <label><?php echo functions::form_draw_checkbox('insert', 'true', true); ?> <?php echo language::translate('text_insert_new_pages', 'Insert new pages'); ?></label>
            </div>

            <?php echo functions::form_draw_button('import', language::translate('title_import', 'Import'), 'submit'); ?>

          <?php echo functions::form_draw_form_end(); ?>
        </fieldset>
      </div>

      <div class="col-md-6">
        <fieldset class="well">
          <legend><?php echo language::translate('title_export_to_csv', 'Export To CSV'); ?></legend>

          <?php echo functions::form_draw_form_begin('export_pages_form', 'post'); ?>

            <div class="form-group">
              <label><?php echo language::translate('title_language', 'Language'); ?></label>
              <?php echo functions::form_draw_languages_list('language_code', true, false).' '; ?>
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
  </div>
</div>