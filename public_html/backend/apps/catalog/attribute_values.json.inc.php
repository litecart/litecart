<?php

  try {

    if (!isset($_GET['group_id'])) throw new Exception('Missing group_id');

    $attribute_group = database::query(
      "select * from ". DB_TABLE_PREFIX ."attribute_groups
      where id = ". (int)$_GET['group_id'] ."
      limit 1;"
    )->fetch();

    if (!$attribute_group) {
      throw new Exception('Invalid group_id');
    }

    $json = database::query(
      "select av.id, avi.name from ". DB_TABLE_PREFIX ."attribute_values av
      left join ". DB_TABLE_PREFIX ."attribute_values_info avi on (avi.value_id = av.id and avi.language_code = '". database::input(language::$selected['code']) ."')
      where av.group_id = ". (int)$_GET['group_id'] ."
      order by ". (($attribute_group['sort'] == 'alphabetical') ? "cast(avi.name as unsigned), avi.name" : "av.priority") .";"
    )->fetch_all();

  } catch(Exception $e) {
    http_response_code(400);
    $json = ['error' => $e->getMessage()];
  }

  ob_clean();
  header('Content-type: application/json; charset='. mb_http_output());
  echo json_encode($json, JSON_UNESCAPED_SLASHES);
  exit;
