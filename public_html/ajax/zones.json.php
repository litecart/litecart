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
  
  $output = '[';
  while ($zone = $system->database->fetch($zones_query)) {
    $output .= '{"code":"'. $zone['code'] .'","name":"'. $zone['name'] .'"},';
  }
  $output = rtrim($output, ',');
  $output .= ']';
  
  echo $output;
  
?>