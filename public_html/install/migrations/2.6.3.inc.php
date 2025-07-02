<?php

  // Rename images with en-dash or em-dash to dash
  foreach([
    '–' => '-', // en-dash
    '—' => '-', // em-dash
    '--' => '-', // double dash
  ] as $char => $replacement) {

    $files = functions::file_search(FS_DIR_STORAGE . 'images/products/*'.$char.'*');

    if (empty($files)) continue;

    foreach ($files as $file) {

      $char_utf8 = mb_convert_encoding($char, 'UTF-8', 'auto');
      $replacement_utf8 = mb_convert_encoding($replacement, 'UTF-8', 'auto');

      $new_filename = preg_replace('#'.preg_quote($char_utf8,'#').'+#u', $replacement_utf8, $file);
      echo "Renaming image: ". basename($file) .' to '. basename($new_filename) ."\n";
      rename($file, $new_filename);
    }

    database::query(
      "select * from ". DB_TABLE_PREFIX ."products
      where image like '%$char%';"
    )->each(function($product) use ($char, $replacement) {
      database::query(
        "update ". DB_TABLE_PREFIX ."products
        set image = '". database::input(preg_replace('#'.preg_quote($char,'#').'+#u', $replacement, $product['image'])) ."'
        where id = '". (int)$product['id'] ."';"
      );
    });

    database::query(
      "select * ". DB_TABLE_PREFIX ."products_images
      where filename like '%$char%';"
    )->each(function($image) use ($char, $replacement) {
      database::query(
        "update ". DB_TABLE_PREFIX ."products_images
        set filename = '". database::input(preg_replace('#'.preg_quote($char,'#').'+#u', $replacement, $image['filename'])) ."'
        where id = '". (int)$image['id'] ."';"
      );
    });
  }
