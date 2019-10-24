<?php

  ob_clean();

  header('Content-type: application/json; charset='. language::$selected['charset']);

  try {

    if (!isset($_GET['group_id'])) throw new Exception('Missing group_id');

    $attribute_values_query = database::query(
      "select av.id, avi.name from ". DB_TABLE_ATTRIBUTE_VALUES ." av
      left join ". DB_TABLE_ATTRIBUTE_VALUES_INFO ." avi on (avi.value_id = av.id and avi.language_code = '". database::input(language::$selected['code']) ."')
      where av.group_id = ". (int)$_GET['group_id'] ."
      order by avi.name;"
    );

    $json = array();
    while ($value = database::fetch($attribute_values_query)) {
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
