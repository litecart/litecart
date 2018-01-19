<?php
  $box_manufacturer_links_cache_id = cache::cache_id('box_manufacturer_links', array('language', isset($_GET['manufacturer_id']) ? $_GET['manufacturer_id'] : ''));
  if (cache::capture($box_manufacturer_links_cache_id, 'file')) {

    $manufacturers_query = database::query(
      "select a.id, a.name, a.date_created from ". DB_TABLE_MANUFACTURERS ." a
      left join ". DB_TABLE_MANUFACTURERS_INFO ." ai on (a.id = ai.manufacturer_id and ai.language_code = '". language::$selected['code'] ."')
      where status
      order by a.name;"
    );

    if (database::num_rows($manufacturers_query)) {

      $box_manufacturer_links = new view();

      $box_manufacturer_links->snippets['manufacturers'] = array();

      while ($manufacturer = database::fetch($manufacturers_query)) {
        $box_manufacturer_links->snippets['manufacturers'][] = array(
          'id' => $manufacturer['id'],
          'name' => $manufacturer['name'],
          'link' => document::ilink('manufacturer', array('manufacturer_id' => $manufacturer['id'])),
          'date_created' => $manufacturer['date_created'],
          'active' => (isset($_GET['manufacturer_id']) && $_GET['manufacturer_id'] == $manufacturer['id']) ? true : false,
        );
      }

      echo $box_manufacturer_links->stitch('views/box_manufacturer_links');
    }

    cache::end_capture($box_manufacturer_links_cache_id);
  }
