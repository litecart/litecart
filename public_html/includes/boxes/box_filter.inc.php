<?php

  if (!in_array(route::$route['page'], array('category', 'manufacturer'))) return;

  $box_filter_cache_token = cache::token('box_filter', array('language', 'get'), 'file');
  if (cache::capture($box_filter_cache_token)) {

    $box_filter = new view();

    $box_filter->snippets = array(
      'manufacturers' => array(),
    );

  // Manufacturers
    if (empty($_GET['manufacturer_id'])) {
      $manufacturers_query = database::query(
        "select distinct m.id, m.name from ". DB_TABLE_PRODUCTS ." p
        left join ". DB_TABLE_MANUFACTURERS ." m on m.id = p.manufacturer_id ".
        (!empty($_GET['category_id']) ? " left join " . DB_TABLE_PRODUCTS_TO_CATEGORIES . " pc on pc.product_id = p.id " : "")."
        where p.status
        and manufacturer_id
        ". (!empty($_GET['category_id']) ? "and pc.category_id = " . (int)$_GET['category_id']  : "") ."
        order by m.name asc;"
      );
      if (database::num_rows($manufacturers_query)) {

        while($manufacturer = database::fetch($manufacturers_query)) {
          $box_filter->snippets['manufacturers'][] = array(
            'id' => $manufacturer['id'],
            'name' => $manufacturer['name'],
            'href' => document::ilink('manufacturer', array('manufacturer_id' => $manufacturer['id'])),
          );
        }
      }
    }

    echo $box_filter->stitch('views/box_filter');

    cache::end_capture($box_filter_cache_token);
  }
