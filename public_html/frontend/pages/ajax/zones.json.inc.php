<?php

  if (!isset($_GET['country_code'])) exit;

  $zones = database::query(
    "select code, name
    from ". DB_TABLE_PREFIX ."zones
    where country_code = '". database::input($_GET['country_code']) ."'
    order by name asc;"
  )->fetch_all();

  ob_clean();
  header('Content-type: application/json; charset='. mb_http_output());
  echo json_encode($zones, JSON_UNESCAPED_SLASHES);
  exit;
