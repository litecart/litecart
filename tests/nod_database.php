<?php

  include_once __DIR__.'/../public_html/includes/app_header.inc.php';

  try {

    ########################################################################
    ## input — string escaping
    ########################################################################

    $escaped = database::input("O'Reilly");

    if (strpos($escaped, "'") !== false && strpos($escaped, "\\'") === false && strpos($escaped, "''") === false) {
      throw new Exception('input: Single quote not escaped in "'. $escaped .'"');
    }

    ########################################################################
    ## input — null passthrough
    ########################################################################

    $escaped = database::input(null);

    if ($escaped !== null) {
      throw new Exception('input: null should pass through, got '. var_export($escaped, true));
    }

    ########################################################################
    ## input — integer passthrough
    ########################################################################

    $escaped = database::input(42);

    if ($escaped !== 42) {
      throw new Exception('input: integer should pass through, got '. var_export($escaped, true));
    }

    ########################################################################
    ## input — array escaping (recursive)
    ########################################################################

    $escaped = database::input(["test's", "normal"]);

    if (!is_array($escaped) || count($escaped) !== 2) {
      throw new Exception('input: array escaping should return array with 2 elements');
    }

    ########################################################################
    ## input_like — wildcard escaping
    ########################################################################

    $escaped = database::input_like('100% match_test');

    if (strpos($escaped, '\\%') === false) {
      throw new Exception('input_like: % should be escaped, got "'. $escaped .'"');
    }

    if (strpos($escaped, '\\_') === false) {
      throw new Exception('input_like: _ should be escaped, got "'. $escaped .'"');
    }

    ########################################################################
    ## query — basic SELECT
    ########################################################################

    $result = database::query(
      "select 1 + 1 as total;"
    );

    if (!$result || !$result->num_rows) {
      throw new Exception('query: Basic SELECT should return a result');
    }

    $row = $result->fetch();

    if ($row['total'] != 2) {
      throw new Exception('query: Expected 2, got '. $row['total']);
    }

    ########################################################################
    ## query — parameter substitution (integer)
    ########################################################################

    $result = database::query(
      "select :val as result;",
      [':val' => 42]
    );

    $row = $result->fetch();

    if ((int)$row['result'] !== 42) {
      throw new Exception('query: Parameter substitution for integer failed, got '. $row['result']);
    }

    ########################################################################
    ## query — parameter substitution (string with escaping)
    ########################################################################

    $result = database::query(
      "select :val as result;",
      [':val' => "test'injection"]
    );

    $row = $result->fetch();

    if ($row['result'] !== "test'injection") {
      throw new Exception('query: String parameter should be escaped and returned correctly');
    }

    ########################################################################
    ## fetch — single column shorthand
    ########################################################################

    $value = database::query(
      "select 'hello' as greeting;"
    )->fetch('greeting');

    if ($value !== 'hello') {
      throw new Exception('fetch(column): Expected "hello", got '. var_export($value, true));
    }

    ########################################################################
    ## fetch_all — multiple rows
    ########################################################################

    $rows = database::query(
      "select 1 as n union all select 2 union all select 3;"
    )->fetch_all();

    if (count($rows) !== 3) {
      throw new Exception('fetch_all: Expected 3 rows, got '. count($rows));
    }

    ########################################################################
    ## num_rows
    ########################################################################

    $count = database::query(
      "select 1 union all select 2;"
    )->num_rows;

    if ($count !== 2) {
      throw new Exception('num_rows: Expected 2, got '. $count);
    }

    ########################################################################
    ## each — callback iteration
    ########################################################################

    $values = database::query(
      "select 1 as n union all select 2 union all select 3;"
    )->each(function($row) {
      return (int)$row['n'] * 10;
    });

    if ($values !== [10, 20, 30]) {
      throw new Exception('each: Expected [10, 20, 30], got '. var_export($values, true));
    }

    ########################################################################
    ## create_variable — type mapping
    ########################################################################

    $int_var = database::create_variable(['Type' => 'int(11)', 'Default' => null]);

    if ($int_var !== 0) {
      throw new Exception('create_variable: int with null default should be 0, got '. var_export($int_var, true));
    }

    $float_var = database::create_variable(['Type' => 'decimal(10,2)', 'Default' => null]);

    if ($float_var !== 0.0) {
      throw new Exception('create_variable: decimal with null default should be 0.0, got '. var_export($float_var, true));
    }

    $str_var = database::create_variable(['Type' => 'varchar(255)', 'Default' => null]);

    if ($str_var !== '') {
      throw new Exception('create_variable: varchar with null default should be empty string, got '. var_export($str_var, true));
    }

    ########################################################################
    ## transaction — rollback works
    ########################################################################

    database::query("start transaction;");

    database::query(
      "insert into ". DB_TABLE_PREFIX ."settings_groups
      (". DB_TABLE_PREFIX ."settings_groups.`key`, name, description, priority)
      values ('test_tx_". uniqid() ."', 'TX Test', '', 999);"
    );

    $inserted = database::insert_id();

    if (!$inserted) {
      throw new Exception('transaction: INSERT should return an ID');
    }

    database::query("rollback;");

    $found = database::query(
      "select id from ". DB_TABLE_PREFIX ."settings_groups
      where id = ". (int)$inserted ."
      limit 1;"
    )->num_rows;

    if ($found) {
      throw new Exception('transaction: ROLLBACK should have removed the inserted row');
    }

    return true;

  } catch (Exception $e) {

    echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
    return false;
  }
