<?php
  require_once('../includes/app_header.inc.php');
  
  header('Content-type: application/json; charset='. $system->language->selected['charset']);
  
  if (!isset($_GET['country_code'])) exit;
  
  $zones_query = $system->database->query(
    "select * from ". DB_TABLE_ZONES ."
    where country_code = '". $system->database->input($_GET['country_code']) ."'
    order by name asc;"
  );
  
  if ($system->database->num_rows($zones_query) == 0) {
    exit;
  }
  
  $json = array();
  while ($zone = $system->database->fetch($zones_query)) {
    $json[] = array(
      'code' => $zone['code'],
      'name' => $zone['name'],
    );
  }
  
  mb_convert_variables($system->language->selected['charset'], 'UTF-8', $json);
  $json = json_encode($json);
  
  mb_convert_variables('UTF-8', $system->language->selected['charset'], $json);
  echo $json;
  
?>