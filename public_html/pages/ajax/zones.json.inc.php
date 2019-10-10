<?php
  header('Content-type: application/json; charset='. language::$selected['charset']);

  if (!isset($_GET['country_code'])) exit;

  $zones_query = database::query(
    "select * from ". DB_TABLE_ZONES ."
    where country_code = '". database::input($_GET['country_code']) ."'
    order by name asc;"
  );

  $json = array();

  while ($zone = database::fetch($zones_query)) {
    $json[] = array(
      'code' => $zone['code'],
      'name' => $zone['name'],
    );
  }

  language::convert_characters($json, language::$selected['charset'], 'UTF-8');
  $json = json_encode($json, JSON_UNESCAPED_SLASHES);

  language::convert_characters($json, 'UTF-8', language::$selected['charset']);
  echo $json;

  exit;
