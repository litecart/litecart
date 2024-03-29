<?php

  $box_brand_logotypes_cache_token = cache::token('box_brand_logotypes', []);
  if (cache::capture($box_brand_logotypes_cache_token)) {

    $box_brand_logotypes = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_brand_logotypes.inc.php');

    $box_brand_logotypes->snippets['brands'] = database::query(
      "select id, image, name from ". DB_TABLE_PREFIX ."brands
      where status
      and featured
      and (image is not null and image != '')
      order by rand();"
    )->fetch_custom(function($brand){
      return [
        'name' => $brand['name'],
        'link' => document::ilink('brand', ['brand_id' => $brand['id']]),
        'image' => $brand['image'] ? 'storage://images/' . $brand['image'] : '',
      ];
    });

    if ($box_brand_logotypes->snippets['brands']) {
      echo $box_brand_logotypes->render();
    }

    cache::end_capture($box_brand_logotypes_cache_token);
  }
