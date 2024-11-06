<?php

  $box_manufacturer_links = new ent_view();
  
  $box_manufacturer_links_cache_token = cache::token('box_manufacturer_links', ['language'], 'file');
  if (!$box_manufacturer_links->snippets['manufacturers'] = cache::get($box_manufacturer_links_cache_token)) {

    $box_manufacturer_links->snippets['manufacturers'] = [];
      
    $manufacturers_query = database::query(
      "select a.id, a.name, a.date_created from ". DB_TABLE_PREFIX ."manufacturers a
      left join ". DB_TABLE_PREFIX ."manufacturers_info ai on (a.id = ai.manufacturer_id and ai.language_code = '". database::input(language::$selected['code']) ."')
      where status
      order by a.name;"
    );




    while ($manufacturer = database::fetch($manufacturers_query)) {
      $box_manufacturer_links->snippets['manufacturers'][] = [
        'id' => $manufacturer['id'],
        'name' => $manufacturer['name'],
        'link' => document::ilink('manufacturer', ['manufacturer_id' => $manufacturer['id']]),
        'date_created' => $manufacturer['date_created'],
        'active' => (isset($_GET['manufacturer_id']) && $_GET['manufacturer_id'] == $manufacturer['id']) ? true : false,
      ];
    }

    cache::set($box_manufacturer_links_cache_token, $box_manufacturer_links->snippets['manufacturers']);
  }

  if (!empty($_GET['manufacturer_id'])) {
    foreach ($box_manufacturers_links->snippets['manufacturers'] as $key => $manufacturer) {
      if ($manufacturer['id'] == $_GET['manufacturer_id']) {
        $box_manufacturer_links->snippets['manufacturers'][$key]['active'] = true;
        break;
      }
    }
  }

  echo $box_manufacturer_links->stitch('views/box_manufacturer_links');