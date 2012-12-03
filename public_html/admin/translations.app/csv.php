<?php
  
  $delimiter = ';';
  
  if (!empty($_POST['import'])) {
  
    if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
    
      if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) {
        
        setlocale(LC_ALL, 'en_US.ISO-8859-1'); // Needed for fgetcsv to work in iso-8859-1 mode
        while (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
          
          if (empty($keys)) {
            $keys = $data;
            foreach ($keys as $key => $value) {
              $keys[trim($key)] = (string)$system->database->input($value);
            }
            continue;
            
          } else {
            $data = array_combine($keys, $data);
            
            foreach ($data as $key => $value) {
              if (strtolower($system->language->selected['charset']) == 'utf-8') {
                $data[$key] = utf8_encode($system->database->input($value, true));
              } else {
                $data[$key] = $system->database->input($value, true);
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
    
    $_POST['language_codes'] = array_filter($_POST['language_codes']);
    
    if (empty($_POST['language_codes'])) die('Error: You must select at least one language');
    
    $translations_query = $system->database->query(
      "select * from ". DB_TABLE_TRANSLATIONS ."
      order by date_created asc;"
    );
    
    //header('Content-type: text/plain; charset='. $system->language->selected['charset']);
    header('Content-type: application/csv; charset=iso-8859-1');
    header("Content-Disposition: attachment; filename=translations-". implode('-', $_POST['language_codes']) .".csv");

    echo 'code;'. implode(';', $_POST['language_codes']) . PHP_EOL;
    
    while ($translation = $system->database->fetch($translations_query)) {
    
      $translations = array();
      foreach ($_POST['language_codes'] as $language_code) {
        $translations[] = str_replace('"', '\"', $translation['text_'.$language_code]);
      }
      
      if (strtolower($system->language->selected['charset']) == 'utf-8') {
        echo utf8_decode('"'. $translation['code'] .'"'.$delimiter.'"'. implode('"'.$delimiter.'"', $translations) .'"') . PHP_EOL;
      } else {
        echo '"'. $translation['code'] .'"'.$delimiter.'"'. implode('"'.$delimiter.'"', $translations) .'"'. PHP_EOL;
      }
    }
    exit;
  }

?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" border="0" align="absmiddle" style="margin-right: 10px;" /><?php echo $system->language->translate('title_csv_import_export_translations', 'CSV Import/Export Translations'); ?></h1>
<p><?php echo $system->language->translate('title_example', 'Example'); ?>:<br />
  <pre>code;en;sv
"title_catalog";"Catalog";"Katalog"
"title_modules";"Modules";"Moduler"</pre>
</pre>
</p>

<table border="0" cellpadding="5" cellspacing="0"  width="100%">
  <tr>
    <td width="50%">
      <?php echo $system->functions->form_draw_form_begin('import_form', 'post', '', true); ?>
      <h2><?php echo $system->language->translate('title_import_to_csv', 'Import From CSV'); ?></h2>
      <table border="0" cellpadding="5" cellspacing="0">
        <tr>
          <td><?php echo $system->language->translate('title_csv_file', 'CSV File'); ?></br>
            <?php echo $system->functions->form_draw_file_field('file'); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->functions->form_draw_button('import', $system->language->translate('title_import', 'Import'), 'submit'); ?></td>
        </tr>
      </table>
      <?php echo $system->functions->form_draw_form_end(); ?>
    </td>
    <td width="50%">
      <?php echo $system->functions->form_draw_form_begin('export_form', 'post'); ?>
      <h2><?php echo $system->language->translate('title_export_to_csv', 'Export To CSV'); ?></h2>
      <table border="0" cellpadding="5" cellspacing="0">
        <tr>
          <td>
            <?php echo $system->language->translate('title_languages', 'Languages'); ?><br />
            <table border="0" cellpadding="5" cellspacing="0" style="margin: -5px;">
              <tr>
                <td><?php for ($i=0; $i<count($system->language->languages); $i++) echo $system->functions->form_draw_languages_list('language_codes[]').' '; ?></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2"><?php echo $system->functions->form_draw_button('export', $system->language->translate('title_export', 'Export'), 'submit'); ?></td>
        </tr>
      </table>
      <?php echo $system->functions->form_draw_form_end(); ?>
    </td>
  </tr>
</table>