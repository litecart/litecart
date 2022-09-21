<?php

  document::$snippets['title'][] = language::translate('title_scan_translations', 'Scan Translations');

  breadcrumbs::add(language::translate('title_translations', 'Translations'));
  breadcrumbs::add(language::translate('title_scan_translations', 'Scan Translations'));

  if (!empty($_POST['scan'])) {

    ob_start();

    $dir_iterator = new RecursiveDirectoryIterator(FS_DIR_APP);
    $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

    $found_files = 0;
    $found_translations = 0;
    $new_translations = 0;
    $updated_translations = 0;
    $translation_keys = [];
    $deleted_translations = 0;

    foreach ($iterator as $file) {
      if (!preg_match('#\.php$#', $file)) continue;

      $found_files++;
      $contents = file_get_contents($file);

      $regexp = [
        'language::translate\((?:(?!\$)',
        '(?:(__CLASS__)?\.)?',
        '(?:[\'"])([^\'"]+)(?:[\'"])',
        '(?:,?\s+(?:[\'"])([^\'"]+)?(?:[\'"]))?',
        '(?:,?\s+?(?:[\'"])([^\'"]+)?(?:[\'"]))?',
        ')\)',
      ];
      $regexp = '/'. implode($regexp) .'/s';

      preg_match_all($regexp, $contents, $matches);
      $translations = [];

      if (!empty($matches)) {
        for ($i=0; $i<count($matches[1]); $i++) {
          if ($matches[1][$i]) {
            $key = substr(pathinfo($file, PATHINFO_BASENAME), 0, strpos(pathinfo($file, PATHINFO_BASENAME), '.')) . $matches[2][$i];
          } else {
            $key = $matches[2][$i];
          }
          $translations[$key] = str_replace(["\\r", "\\n"], ["\r", "\n"], $matches[3][$i]);
          $translation_keys[] = $key;
        }
      }

      foreach ($translations as $code => $translation) {

        $found_translations++;

        $translations_query = database::query(
          "select text_en from ". DB_TABLE_PREFIX ."translations
          where code = '". database::input($code) ."'
          limit 1;"
        );

        if (!$row = database::fetch($translations_query)) {

          $new_translations++;

          database::query(
            "insert into ". DB_TABLE_PREFIX ."translations
            (code, text_en, html, date_created)
            values ('". database::input($code) ."', '". database::input($translation, true) ."', '". (($translation != strip_tags($translation)) ? 1 : 0) ."', '". date('Y-m-d H:i:s') ."');"
          );

          echo  $code . ' [ADDED]<br/>' . PHP_EOL;

        } else if (empty($row['text_en']) && !empty($translation) && !empty($_POST['update'])) {

          $updated_translations++;

          database::query(
            "update ". DB_TABLE_PREFIX ."translations
            set text_en = '". database::input($translation, true) ."'
            where code = '". database::input($code) ."'
            and text_en = ''
            limit 1;"
          );

          echo  $code . ' [UPDATED]<br/>' . PHP_EOL;
        }
      }
    }

    if (!empty($_POST['clean'])) {
      $settings_groups_query = database::query(
        "select `key` from ". DB_TABLE_PREFIX ."settings_groups;"
      );

      while ($group = database::fetch($settings_groups_query)) {
        $translation_keys[] = 'settings_group:title_'.$group['key'];
        $translation_keys[] = 'settings_group:description__'.$group['key'];
      }

      $settings_query = database::query(
        "select `key` from ". DB_TABLE_PREFIX ."settings
        where setting_group_key != '';"
      );

      while ($setting = database::fetch($settings_query)) {
        $translation_keys[] = 'settings_key:title_'.$setting['key'];
        $translation_keys[] = 'settings_key:description_'.$setting['key'];
      }

      $translations_query = database::query(
        "select code from ". DB_TABLE_PREFIX ."translations;"
      );

      while ($translation = database::fetch($translations_query)) {
        if (!in_array($translation['code'], $translation_keys)) {
          database::query(
            "delete from ". DB_TABLE_PREFIX ."translations
            where code = '". database::input($translation['code']) ."'
            limit 1;"
          );
          echo $translation['code'] . ' [DELETED]<br/>' . PHP_EOL;
          $deleted_translations++;
        }
      }
    }

    cache::clear_cache('translations');

    echo sprintf(language::translate('text_found_d_translations', 'Found %d translations in %d files'), $found_translations, $found_files) . PHP_EOL;
    echo sprintf(language::translate('text_added_d_new_translations', 'Added %d new translations'), $new_translations) . PHP_EOL;
    echo sprintf(language::translate('text_updated_d_translations', 'Updated %d translations'), $updated_translations) . PHP_EOL;
    echo sprintf(language::translate('text_deleted_d_translations', 'Deleted %d translations'), $deleted_translations) . PHP_EOL;

    $log = ob_get_clean();
  }
?>
<style>
pre {
  white-space: pre-line;
}
</style>

<div class="panel panel-app">
  <div class="panel-heading">
    <div class="panel-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_scan_files_for_translations', 'Scan Files For Translations'); ?>
    </div>
  </div>

  <div class="panel-body">
    <div class="row">
      <div class="col-md-6">
        <?php echo functions::form_draw_form_begin('scan_form', 'post'); ?>

          <p><?php echo language::translate('description_scan_for_translations', 'This will scan your files for translations. New translations will be added to the database.'); ?></p>

          <p><label><?php echo functions::form_draw_checkbox('update', '1'); ?> <?php echo language::translate('text_update_empty_translations', 'Update empty translations if applicable'); ?></label></p>

          <p><label><?php echo functions::form_draw_checkbox('clean', '1'); ?> <?php echo language::translate('text_delete_translations_not_present', 'Delete translations no longer present in files'); ?></label></p>

          <p><?php echo functions::form_draw_button('scan', language::translate('title_scan', 'Scan'), 'submit', 'onclick="if(!confirm(\''. str_replace('\'', '\\\'', language::translate('warning_backup_translations', 'Warning: Always backup your translations before continuing.')) .'\')) return false;"'); ?></p>

        <?php echo functions::form_draw_form_end(); ?>
      </div>

      <?php if (!empty($_POST['scan'])) { ?>
      <div class="col-md-6">
        <pre>
          <?php echo $log; ?>
        </pre>
      </div>
      <?php } ?>
    </div>
  </div>
</div>
