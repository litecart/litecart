<?php

  $box_manufacturer_logotypes_cache_id = cache::cache_id('box_manufacturer_logotypes', array());
  if (cache::capture($box_manufacturer_logotypes_cache_id, 'file')) {

    $manufacturers_query = database::query(
      "select id, image, name from ". DB_TABLE_MANUFACTURERS ."
      where status
      and image != ''
      order by rand();"
    );

    if (database::num_rows($manufacturers_query)) {

      $box_manufacturer_logotypes = new view();

      $box_manufacturer_logotypes->snippets['logotypes'] = array();

      while ($manufacturer = database::fetch($manufacturers_query)) {
        $box_manufacturer_logotypes->snippets['logotypes'][] = array(
          'title' => $manufacturer['name'],
          'link' => document::ilink('manufacturer', array('manufacturer_id' => $manufacturer['id'])),
          'image' => functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer['image'], 0, 30, 'FIT'),
        );
      }

      echo $box_manufacturer_logotypes->stitch('views/box_manufacturer_logotypes');
    }

    cache::end_capture($box_manufacturer_logotypes_cache_id);
  }
?>