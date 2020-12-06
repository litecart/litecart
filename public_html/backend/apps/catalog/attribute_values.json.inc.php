<?php

  try {

    if (!isset($_GET['group_id'])) throw new Exception('Missing group_id');

    $attribute_group_query = database::query(
      "select * from ". DB_TABLE_ATTRIBUTE_GROUPS ."
      where id = ". (int)$_GET['group_id'] ."
      limit 1;"
    );

    if (!$attribute_group = database::fetch($attribute_group_query)) {
      throw new Exception('Invalid group_id');
    }

    $attribute_values_query = database::query(
      "select av.id, avi.name from ". DB_TABLE_PREFIX ."attribute_values av
      left join ". DB_TABLE_PREFIX ."attribute_values_info avi on (avi.value_id = av.id and avi.language_code = '". database::input(language::$selected['code']) ."')
      where av.group_id = ". (int)$_GET['group_id'] ."
      order by ". (($attribute_group['sort'] == 'alphabetical') ? "cast(avi.name as unsigned), avi.name" : "av.priority") .";"
    );

    $json = [];
    while ($value = database::fetch($attribute_values_query)) {
      $json[] = [
        'id' => $value['id'],
        'name' => $value['name'],
      ];
    }

  } catch(Exception $e) {
    http_response_code(400);
    $json = ['error' => $e->getMessage()];
  }

  ob_clean();
  header('Content-type: application/json; charset='. language::$selected['charset']);
  echo json_encode($json, JSON_UNESCAPED_SLASHES);
  exit;
