<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" border="0" align="absmiddle" style="margin-right: 10px;" /><?php echo $system->language->translate('title_scan_files_for_translations', 'Scan Files For Translations'); ?></h1>
<?php
  if (!empty($_POST['scan'])) {
  
    $dir_iterator = new RecursiveDirectoryIterator(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME);
    $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
    
    $found_files = 0;
    $found_translations = 0;
    $new_translations = 0;
    $translation_keys = array();
    $deleted_translations = 0;
    
    foreach ($iterator as $file) {
      if (pathinfo($file, PATHINFO_EXTENSION) != 'php') continue;
      $found_files++;
      $contents = file_get_contents($file);
      //preg_match_all('/system->language->translate\([\s]?[\'"](.*?)[\'"],[\s]?[\'"](.*?)[\'"]\)/', $contents, $matches);
      preg_match_all('/system->language->translate\(((__CLASS__)?\.)?[\'"](.*?)[\'"],[\s]?[\'"](.*?)[\'"]\)/', $contents, $matches);
      $translations = array();
      
      if (!empty($matches)) {
        for ($i=0; $i<count($matches[0]); $i++) {
          if ($matches[2][$i]) {
            $key = substr(pathinfo($file, PATHINFO_BASENAME), 0, strpos(pathinfo($file, PATHINFO_BASENAME), '.')) . $matches[3][$i];
          } else {
            $key = $matches[3][$i];
          }
          $translations[$key] = $matches[4][$i];
          $translation_keys[] = $key;
        }
      }
      
      foreach ($translations as $code => $translation) {
        $found_translations++;
        if ($system->database->num_rows($system->database->query("select text_en from ". DB_TABLE_TRANSLATIONS ." where code = '". $system->database->input($code) ."' limit 1;")) == 0) {
          $new_translations++;
          echo $code ." = ". $translation ."<br />";
          $system->database->query(
            "insert into ". DB_TABLE_TRANSLATIONS ."
            (code, text_en, pages, date_created)
            values ('". $system->database->input($code) ."', '". $system->database->input($translation) ."', '\'". str_replace(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME, '', $file) ."\',', '". date('Y-m-d H:i:s') ."');"
          );
        }
      }
    }
    
    echo '<p>'. sprintf($system->language->translate('text_found_d_translations', 'Found %d translations in %d files.'), $found_translations, $found_files) .'</p>';
    echo '<p>'. sprintf($system->language->translate('text_added_d_new_translations', 'Added %d new translations.'), $new_translations) .'</p>';
    
    $settings_query = $system->database->query(
      "select `key` from ". DB_TABLE_SETTINGS ."
      where setting_group_key != '';"
    );
    while ($setting = $system->database->fetch($settings_query)) {
      $translation_keys[] = 'settings_key_title:'.$setting['key'];
      $translation_keys[] = 'settings_key_description:'.$setting['key'];
    }
    
    $translations_query = $system->database->query(
      "select code from ". DB_TABLE_TRANSLATIONS .";"
    );
    while ($translation = $system->database->fetch($translations_query)) {
      if (!in_array($translation['code'], $translation_keys)) {
        $system->database->query(
          "delete from ". DB_TABLE_TRANSLATIONS ."
          where code = '". $system->database->input($translation['code']) ."'
          limit 1;"
        );
        $deleted_translations++;
      }
    }
    
    echo '<p>'. sprintf($system->language->translate('text_deleted_d_translations', 'Deleted %d translations'), $deleted_translations) .'</p>';
    
  } else {
    echo $system->functions->form_draw_form_begin('scan_form', 'post')
       . '<p>'. $system->language->translate('description_translations_scan', 'This will scan your files for new translations and delete translations no longer present in files.') .'</p>'
       . $system->functions->form_draw_button('scan', $system->language->translate('title_scan_and_clean', 'Scan and Clean'), 'submit', 'onclick="if(!confirm(\''. str_replace('\'', '\\\'', $system->language->translate('warning_backup_translations', 'Warning: Always backup your translations before continuing.')) .'\')) return false;"')
       . $system->functions->form_draw_form_end();
    
  }