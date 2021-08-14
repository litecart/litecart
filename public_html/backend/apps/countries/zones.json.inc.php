<?php
  header('Content-type: application/json; charset='. language::$selected['charset']);

  if (empty($_GET['country_code'])) {
    http_response_code(500);
    exit;
  }

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

  language::convert_characters($json, language::$selected['charset'], 'UTF-8');
  $json = json_encode($json, JSON_UNESCAPED_SLASHES);

  language::convert_characters($json, 'UTF-8', language::$selected['charset']);
  echo $json;

  exit;
