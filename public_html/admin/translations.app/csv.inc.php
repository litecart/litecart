<?php
  
  if (!empty($_POST['import'])) {
  
    if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
    
      if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) {
        
        setlocale(LC_ALL, 'en_US.'. $_POST['charset']); // Needed for fgetcsv to work in iso-8859-1 mode
        
        while (($data = fgetcsv($handle, 0, $_POST['delimiter'])) !== FALSE) {
          
          if (empty($keys)) {
            $keys = $data;
            foreach ($keys as $key => $value) {
              $keys[trim($key)] = (string)$system->database->input($value);
            }
            continue;
            
          } else {
            if (count($keys) != count($data)) {
              die('Error: Invalid column amount');
            }
            $data = array_combine($keys, $data);
            
            foreach ($data as $key => $value) {
              if (strtolower($system->language->selected['charset']) == strtolower($_POST['charset'])) {
                $data[$key] = $system->database->input($value, true);
              } else if (strtolower($system->language->selected['charset']) == 'utf-8' && strtolower($_POST['charset']) != 'utf-8') {
                $data[$key] = utf8_encode($system->database->input($value, true));
              } else if (strtolower($system->language->selected['charset']) != 'utf-8' && strtolower($_POST['charset']) == 'utf-8') {
                $data[$key] = utf8_decode($system->database->input($value, true));
              }
            }
            
            $translation_query = $system->database->query(
              "select code from ". DB_TABLE_TRANSLATIONS ."
              where code = '". $data['code'] ."'
              limit 1;"
            );

            if ($system->database->num_rows($translation_query) > 0) {
              foreach (array_slice($keys, 1) as $language_code) {
                $system->database->query(
                  "update ". DB_TABLE_TRANSLATIONS ."
                  set text_". $language_code ." = '". $data[$language_code] ."'
                  where code = '". $data['code'] ."'
                  limit 1;"
                );
              }
            } else {
              $system->database->query(
                "insert into ". DB_TABLE_TRANSLATIONS ."
                (code, text_". implode(", text_", array_slice($keys, 1)) .")
                values ('". implode("', '", $data) ."');"
              );

            }
          }
        }
        fclose($handle);
      }
      
      $system->notices->add('success', $system->language->translate('success_translations_imported', 'Translations successfully imported.'));
      
      header('Location: '. $system->document->link('', array('app' => $_GET['app'], 'doc' => $_GET['doc'])));
      exit;
    }
  }
  
  if (!empty($_POST['export'])) {
  
    switch ($_POST['eol']) {
      case 'Win':
        $_POST['EOL'] = "\r\n";
        break;
      case 'Mac':
        $_POST['EOL'] = "\r";
        break;
      case 'Linux':
        $_POST['EOL'] = "\n";
        break;
      default:
        $_POST['EOL'] = PHP_EOL;
        break;
    }
    
    $_POST['language_codes'] = array_filter($_POST['language_codes']);
    
    if (empty($_POST['language_codes'])) die('Error: You must select at least one language');
    
    $translations_query = $system->database->query(
      "select * from ". DB_TABLE_TRANSLATIONS ."
      order by date_created asc;"
    );
    
    if ($_POST['output'] == 'screen') {
      header('Content-type: text/plain; charset='. $_POST['charset']);
    } else {
      header('Content-type: application/csv; charset='. $_POST['charset']);
      header("Content-Disposition: attachment; filename=translations-". implode('-', $_POST['language_codes']) .".csv");
    }
    echo implode($_POST['delimiter'], array_merge(array('code'), $_POST['language_codes'])) . $_POST['EOL'];
    
    while ($translation = $system->database->fetch($translations_query)) {
    
      $columns = array($translation['code']);
      foreach ($_POST['language_codes'] as $language_code) {
        $columns[] = $translation['text_'.$language_code];
      }
      
      foreach (array_keys($columns) as $key) {
        if (strpos($columns[$key], $_POST['delimiter']) !== false || strpos($columns[$key], "\r") !== false || strpos($columns[$key], "\n") !== false) {
          $columns[$key] = $_POST['wrapper'] . str_replace($_POST['wrapper'], $_POST['escapechar'].$_POST['wrapper'], $columns[$key]) . $_POST['wrapper'];
        }
      }
      
      //echo mb_convert_encoding(implode($_POST['delimiter'], $columns), $_POST['charset']) . $_POST['EOL'];
      
      if (strtolower($system->language->selected['charset']) == strtolower($_POST['charset'])) {
        echo implode($_POST['delimiter'], $columns) . $_POST['EOL'];
      } else if (strtolower($system->language->selected['charset']) == 'utf-8' && strtolower($_POST['charset']) != 'utf-8') {
        echo utf8_decode(implode($_POST['delimiter'], $columns)) . $_POST['EOL'];
      } else if (strtolower($system->language->selected['charset']) != 'utf-8' && strtolower($_POST['charset']) == 'utf-8') {
        echo utf8_encode(implode($_POST['delimiter'], $columns)) . $_POST['EOL'];
      }
      
    }
    exit;
  }

?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" border="0" align="absmiddle" style="margin-right: 10px;" /><?php echo $system->language->translate('title_csv_import_export', 'CSV Import/Export'); ?></h1>
<p><?php echo $system->language->translate('title_example', 'Example'); ?>:<br />
  <pre>code;en;sv
title_catalog;Catalog;Katalog</pre>
</pre>
</p>

<div id="import-wrapper" style="margin-bottom: 20px;">
  <?php echo $system->functions->form_draw_form_begin('import_form', 'post', '', true); ?>
  <h2><?php echo $system->language->translate('title_import_from_csv', 'Import From CSV'); ?></h2>
  <p><?php echo $system->language->translate('title_csv_file', 'CSV File'); ?></br>
    <?php echo $system->functions->form_draw_file_field('file'); ?></p>
  <p>
    <table border="0" cellpadding="5" cellspacing="0" style="margin: -5px;">
      <tr>
        <td><?php echo $system->language->translate('title_column_wrapper', 'Column Wrapper'); ?><br />
          <?php echo $system->functions->form_draw_select_field('wrapper', array(array('"'))); ?></td>
        <td><?php echo $system->language->translate('title_delimiter', 'Delimiter'); ?><br />
          <?php echo $system->functions->form_draw_select_field('delimiter', array(array(';'), array(','), array('TAB', "\t"))); ?></td>
        <td><?php echo $system->language->translate('title_escape_character', 'Escape Character'); ?><br />
          <?php echo $system->functions->form_draw_select_field('escapechar', array(array("\""), array("\\"))); ?></td>
        <td><?php echo $system->language->translate('title_charset', 'Charset'); ?><br />
          <?php echo $system->functions->form_draw_select_field('charset', array(array('UTF-8'), array('ISO-8859-1'))); ?></td>
      </tr>
    </table>
  </p>
  <p><?php echo $system->functions->form_draw_button('import', $system->language->translate('title_import', 'Import'), 'submit'); ?></p>
  <?php echo $system->functions->form_draw_form_end(); ?>
</div>

<div id="import-wrapper">
  <?php echo $system->functions->form_draw_form_begin('export_form', 'post'); ?>
  <h2><?php echo $system->language->translate('title_export_to_csv', 'Export To CSV'); ?></h2>
  <p><?php echo $system->language->translate('title_languages', 'Languages'); ?><br />
    <table border="0" cellpadding="5" cellspacing="0" style="margin: -5px;">
      <tr>
        <?php for ($i=0; $i<count($system->language->languages); $i++) { ?>
        <td><?php echo $system->functions->form_draw_languages_list('language_codes[]').' '; ?></td>
        <?php } ?>
      </tr>
    </table>
  </p>
  <p>
    <table border="0" cellpadding="5" cellspacing="0" style="margin: -5px;">
      <tr>
        <td><?php echo $system->language->translate('title_column_wrapper', 'Column Wrapper'); ?><br />
          <?php echo $system->functions->form_draw_select_field('wrapper', array(array('"'))); ?></td>
        <td><?php echo $system->language->translate('title_delimiter', 'Delimiter'); ?><br />
          <?php echo $system->functions->form_draw_select_field('delimiter', array(array(';'), array(','), array('TAB', "\t"), array('|'))); ?></td>
        <td><?php echo $system->language->translate('title_escape_character', 'Escape Character'); ?><br />
          <?php echo $system->functions->form_draw_select_field('escapechar', array(array("\""), array("\\"))); ?></td>
      </tr>
      <tr>
        <td><?php echo $system->language->translate('title_line_ending', 'Line Ending'); ?><br />
          <?php echo $system->functions->form_draw_select_field('eol', array(array('Win'), array('Mac'), array('Linux'))); ?></td>
        <td><?php echo $system->language->translate('title_charset', 'Charset'); ?><br />
          <?php echo $system->functions->form_draw_select_field('charset', array(array('UTF-8'), array('ISO-8859-1'))); ?></td>
        <td><?php echo $system->language->translate('title_output', 'Output'); ?><br />
          <?php echo $system->functions->form_draw_select_field('output', array(array($system->language->translate('title_file', 'File'), 'file'), array($system->language->translate('title_screen', 'Screen'), 'screen'))); ?></td>
      </tr>
    </table>
  </p>
  <p><?php echo $system->functions->form_draw_button('export', $system->language->translate('title_export', 'Export'), 'submit'); ?></p>
  <?php echo $system->functions->form_draw_form_end(); ?>
</div>