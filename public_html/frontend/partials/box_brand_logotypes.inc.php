<?php

  $box_brand_logotypes_cache_token = cache::token('box_brand_logotypes', []);
  if (cache::capture($box_brand_logotypes_cache_token)) {

    $brands_query = database::query(
      "select id, image, name from ". DB_TABLE_PREFIX ."brands
      where status
      and featured
      and (image is not null and image != '')
      order by rand();"
    );

    if (database::num_rows($brands_query)) {

      $box_brand_logotypes = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_brand_logotypes.inc.php');

      $box_brand_logotypes->snippets['brands'] = [];

      while ($brand = database::fetch($brands_query)) {
        $box_brand_logotypes->snippets['brands'][] = [
          'name' => $brand['name'],
          'link' => document::ilink('brand', ['brand_id' => $brand['id']]),
          'image' => $brand['image'] ? 'storage://images/' . $brand['image'] : '',
        ];
      }

      echo $box_brand_logotypes->render();
    }

    cache::end_capture($box_brand_logotypes_cache_token);
  }
