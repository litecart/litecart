<?php
  header('Content-type: application/json; charset='. language::$selected['charset']);

  if (!isset($_GET['option_group_id'])) exit;

  $option_values_query = database::query(
    "select pcv.id, pcvi.name from ". DB_TABLE_OPTION_VALUES ." pcv
    left join ". DB_TABLE_OPTION_VALUES_INFO ." pcvi on (pcvi.value_id = pcv.id and pcvi.language_code = '". database::input(language::$selected['code']) ."')
    where pcv.group_id = '". (int)$_GET['option_group_id'] ."'
    order by pcv.priority;"
  );

  if (database::num_rows($option_values_query) == 0) {
    exit;
  }

  $json = array();
  while ($configuration_value = database::fetch($option_values_query)) {
    $json[] = array(
      'id' => $configuration_value['id'],
      'name' => $configuration_value['name'],
    );
  }

  language::convert_characters($json, language::$selected['charset'], 'UTF-8');
  $json = json_encode($json);

  language::convert_characters($json, 'UTF-8', language::$selected['charset']);
  echo $json;

  exit;
?>