<?php
  header('Content-type: application/json; charset='. language::$selected['charset']);

  try {

    if (!isset($_GET['option_group_id'])) throw new Exception('Missing option_group_id');

    $option_values_query = database::query(
      "select pcv.id, pcvi.name from ". DB_TABLE_OPTION_VALUES ." pcv
      left join ". DB_TABLE_OPTION_VALUES_INFO ." pcvi on (pcvi.value_id = pcv.id and pcvi.language_code = '". database::input(language::$selected['code']) ."')
      where pcv.group_id = ". (int)$_GET['option_group_id'] ."
      order by pcv.priority;"
    );

    if (!database::num_rows($option_values_query)) throw new Exception('Option group has no values');

    $json = array();
    while ($value = database::fetch($option_values_query)) {
      $json[] = array(
        'id' => $value['id'],
        'name' => $value['name'],
      );
    }

  } catch(Exception $e) {
    http_response_code(400);
    $json = array('error' => $e->getMessage());
  }

  language::convert_characters($json, language::$selected['charset'], 'UTF-8');
  $json = json_encode($json, JSON_UNESCAPED_SLASHES);

  language::convert_characters($json, 'UTF-8', language::$selected['charset']);
  echo $json;

  exit;
