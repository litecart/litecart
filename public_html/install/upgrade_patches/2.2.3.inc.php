<?php

  $modified_files = array(
    array(
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "  define('DB_TABLE_PRODUCTS_OPTIONS',                  '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'products_options`');" . PHP_EOL,
      'replace' => "  define('DB_TABLE_PRODUCTS_OPTIONS',                  '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'products_options`');" . PHP_EOL
                 . "  define('DB_TABLE_PRODUCTS_OPTIONS_VALUES',           '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'products_options_values`');" . PHP_EOL,
    ),
    array(
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "  define('DB_TABLE_OPTION_GROUPS',                     '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'option_groups`');" . PHP_EOL
                 . "  define('DB_TABLE_OPTION_GROUPS_INFO',                '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'option_groups_info`');" . PHP_EOL
                 . "  define('DB_TABLE_OPTION_VALUES',                     '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'option_values`');" . PHP_EOL
                 . "  define('DB_TABLE_OPTION_VALUES_INFO',                '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'option_values_info`');" . PHP_EOL,
      'replace' => "" . PHP_EOL,
    ),
  );

  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span><br />Could not find: '. $modification['search'] .'</p>');
    }
  }

// Reconstruct product options

  database::query(
    "RENAME TABLE ". DB_TABLE_PREFIX ."products_options TO ". DB_TABLE_PREFIX ."products_options_values"
  );

  database::query(
    "ALTER TABLE ". DB_TABLE_PREFIX ."products_options_values
    ADD INDEX `priority` (`priority`),
    DROP COLUMN `date_updated`,
    DROP COLUMN `date_created`,
    DROP INDEX `product_option`,
    ADD UNIQUE INDEX `product_option_value` (`product_id`, `group_id`, `value_id`);"
  );

  database::query(
    "CREATE TABLE ". DB_TABLE_PREFIX ."products_options (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `product_id` INT(11) NOT NULL,
      `group_id` INT(11) NOT NULL,
      `function` VARCHAR(32) NOT NULL,
      `required` TINYINT(1) NOT NULL,
      `sort` VARCHAR(16) NOT NULL,
      `priority` TINYINT(2) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE INDEX `product_option` (`product_id`, `group_id`),
      INDEX `product_id` (`product_id`),
      INDEX `priority` (`priority`)
    );"
  );

  database::query(
    "INSERT INTO ". DB_TABLE_PREFIX ."products_options (product_id, group_id, `function`, required, sort)
    SELECT product_id, group_id, ov.`function`, ov.required, ov.sort FROM `lc_products_options_values` pov
    LEFT JOIN `lc_option_groups` ov ON (ov.id = pov.group_id)
    GROUP BY pov.product_id, pov.group_id;"
  );

  database::query(
    "UPDATE `lc_products_options` SET sort = 'custom' WHERE sort = 'product';"
  );

// Move option groups into attribute groups
// Update values in products_options and products_options_values

  $option_groups_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."option_groups
    where id = ". (int)$group_id ."
    limit 1;"
  );

  if ($group = database::fetch($option_groups_query)) {

    database::query(
      "insert into ". DB_TABLE_PREFIX ."attribute_groups
      (code) values (option_". (int)$group['id'] .");"
    );

    $attribute_group_id = database::insert_id();

    database::query(
      "insert into ". DB_TABLE_PREFIX ."attribute_groups_info (group_id, language_code, name)
      select group_id, language_code, name from ". DB_TABLE_PREFIX ."attribute_groups_info
      where group_id = ". (int)$group['id'] .";"
    );

    database::query(
      "update ". DB_TABLE_PREFIX ."products_options
      set group_id = ". (int)$attribute_group_id ."
      where group_id = ". (int)$group['id'] .";"
    );

    $option_values_query = database::query(
      "select * from ". DB_TABLE_PREFIX ."option_values
      where group_id = ". (int)$group['id'] .";"
    );

    while ($value = database::fetch($values_query)) {

      database::query(
        "insert into ". DB_TABLE_PREFIX ."attribute_values
        (group_id) values (". (int)$group['id'] .");"
      );

      $attribute_value_id = database::insert_id();

      database::query(
        "insert into ". DB_TABLE_PREFIX ."attribute_values_info (value_id, language_code, name)
        select '". (int)$attribute_value_id ."', language_code, name from ". DB_TABLE_PREFIX ."option_values_info
        where value_id = ". (int)$value['id'] .";"
      );

      database::query(
        "update ". DB_TABLE_PREFIX ."option_values
        set group_id = ". (int)$attribute_group_id .",
          value_id = ". (int)$attribute_value_id ."
        where group_id = ". (int)$group['id'] ."
        and value_id = ". (int)$value['id'] .";"
      );

    // Update stock combination
    }
  }

// Delete option groups

  database::query(
    "drop table ". DB_TABLE_PREFIX ."option_groups;"
  );

  database::query(
    "drop table ". DB_TABLE_PREFIX ."option_groups_info;"
  );

  database::query(
    "drop table ". DB_TABLE_PREFIX ."option_values;"
  );

  database::query(
    "drop table ". DB_TABLE_PREFIX ."option_values_info;"
  );
