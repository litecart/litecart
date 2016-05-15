<?php
  header('Content-type: application/json; charset='. language::$selected['charset']);

  if (!isset($_GET['country_code'])) exit;

  $zones_query = database::query(
    "select * from ". DB_TABLE_ZONES ."
    where country_code = '". database::input($_GET['country_code']) ."'
    order by name asc;"
  );

  if (database::num_rows($zones_query) == 0) die('{}');

  $json = array();
  while ($zone = database::fetch($zones_query)) {
    $json[] = array(
      'code' => $zone['code'],
      'name' => $zone['name'],
    );
  }

  mb_convert_variables(language::$selected['charset'], 'UTF-8', $json);
  $json = json_encode($json);

  mb_convert_variables('UTF-8', language::$selected['charset'], $json);
  echo $json;

  exit;
?>