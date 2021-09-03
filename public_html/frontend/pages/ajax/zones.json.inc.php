<?php

  if (!isset($_GET['country_code'])) exit;

  $zones_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."zones
    where country_code = '". database::input($_GET['country_code']) ."'
    order by name asc;"
  );

  $json = [];

  while ($zone = database::fetch($zones_query)) {
    $json[] = [
      'code' => $zone['code'],
      'name' => $zone['name'],
    ];
  }

  ob_clean();
  header('Content-type: application/json; charset='. mb_http_output());
  echo json_encode($json, JSON_UNESCAPED_SLASHES);
  exit;
