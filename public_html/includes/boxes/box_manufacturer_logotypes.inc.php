<?php

  $box_manufacturer_logotypes_cache_token = cache::token('box_manufacturer_logotypes', [], 'file');
  if (cache::capture($box_manufacturer_logotypes_cache_token)) {

    $manufacturers_query = database::query(
      "select id, image, name from ". DB_TABLE_PREFIX ."manufacturers
      where status
      and featured
      and image != ''
      order by rand();"
    );

    if (database::num_rows($manufacturers_query)) {

      $box_manufacturer_logotypes = new ent_view();

      $box_manufacturer_logotypes->snippets['logotypes'] = [];

      while ($manufacturer = database::fetch($manufacturers_query)) {
        $box_manufacturer_logotypes->snippets['logotypes'][] = [
          'title' => $manufacturer['name'],
          'link' => document::ilink('manufacturer', ['manufacturer_id' => $manufacturer['id']]),
          'image' => [
            'original' => 'images/' . $manufacturer['image'],
            'thumbnail' => functions::image_thumbnail(FS_DIR_APP . 'images/' . $manufacturer['image'], 0, 128, 'FIT'),
            'thumbnail_2x' => functions::image_thumbnail(FS_DIR_APP . 'images/' . $manufacturer['image'], 0, 256, 'FIT'),
          ],
        ];
      }

      echo $box_manufacturer_logotypes->stitch('views/box_manufacturer_logotypes');
    }

    cache::end_capture($box_manufacturer_logotypes_cache_token);
  }
