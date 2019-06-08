<?php

  $box_manufacturer_logotypes_cache_token = cache::token('box_manufacturer_logotypes', array(), 'file');
  if (cache::capture($box_manufacturer_logotypes_cache_token)) {

    $manufacturers_query = database::query(
      "select id, image, name from ". DB_TABLE_MANUFACTURERS ."
      where status
      and featured
      and image != ''
      order by rand();"
    );

    if (database::num_rows($manufacturers_query)) {

      $box_manufacturer_logotypes = new ent_view();

      $box_manufacturer_logotypes->snippets['logotypes'] = array();

      while ($manufacturer = database::fetch($manufacturers_query)) {
        $box_manufacturer_logotypes->snippets['logotypes'][] = array(
          'title' => $manufacturer['name'],
          'link' => document::ilink('manufacturer', array('manufacturer_id' => $manufacturer['id'])),
          'image' => array(
            'original' => 'images/' . $manufacturer['image'],
            'thumbnail' => functions::image_thumbnail(FS_DIR_APP . 'images/' . $manufacturer['image'], 0, 30, 'FIT'),
            'thumbnail_2x' => functions::image_thumbnail(FS_DIR_APP . 'images/' . $manufacturer['image'], 0, 60, 'FIT'),
          ),
        );
      }

      echo $box_manufacturer_logotypes->stitch('views/box_manufacturer_logotypes');
    }

    cache::end_capture($box_manufacturer_logotypes_cache_token);
  }
