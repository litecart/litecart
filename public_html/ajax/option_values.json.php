<?php
  require_once('../includes/app_header.inc.php');
  header('Content-type: text/plain; charset='. $system->language->selected['charset']);
  
  if (!isset($_GET['option_group_id'])) exit;
  
  $option_values_query = $system->database->query(
    "select pcv.id, pcvi.name from ". DB_TABLE_OPTION_VALUES ." pcv
    left join ". DB_TABLE_OPTION_VALUES_INFO ." pcvi on (pcvi.value_id = pcv.id and pcvi.language_code = '". $system->database->input($system->language->selected['code']) ."')
    where pcv.group_id = '". (int)$_GET['option_group_id'] ."'
    order by pcv.priority;"
  );
  
  if ($system->database->num_rows($option_values_query) == 0) {
    exit;
  }
  
  $output = '[';
  while ($configuration_value = $system->database->fetch($option_values_query)) {
    $output .= '{"id":"'. $configuration_value['id'] .'","name":"'. $configuration_value['name'] .'"},';
  }
  $output = rtrim($output, ',');
  $output .= ']';
  
  echo $output;
  
?>