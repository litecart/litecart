<?php
  $box_brand_links_cache_token = cache::token('box_brand_links', ['language', fallback($_GET['brand_id'])]);
  if (cache::capture($box_brand_links_cache_token)) {

    $brands_query = database::query(
      "select a.id, a.name, a.date_created from ". DB_TABLE_PREFIX ."brands a
      left join ". DB_TABLE_PREFIX ."brands_info ai on (a.id = ai.brand_id and ai.language_code = '". language::$selected['code'] ."')
      where status
      order by a.name;"
    );

    if (database::num_rows($brands_query)) {

      $box_brand_links = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_brand_links.inc.php');

      $box_brand_links->snippets['brands'] = [];

      while ($brand = database::fetch($brands_query)) {
        $box_brand_links->snippets['brands'][] = [
          'id' => $brand['id'],
          'name' => $brand['name'],
          'link' => document::ilink('brand', ['brand_id' => $brand['id']]),
          'date_created' => $brand['date_created'],
          'active' => (isset($_GET['brand_id']) && $_GET['brand_id'] == $brand['id']),
        ];
      }

      echo $box_brand_links->render();
    }

    cache::end_capture($box_brand_links_cache_token);
  }
