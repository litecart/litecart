<?php

// Delete files
  $deleted_files = array(
    FS_DIR_APP . 'includes/templates/default.catalog/pages/page.inc.php',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      echo '<span class="error">[Skipped]</span></p>';
    }
  }

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
    ADD COLUMN `attribute_group_id` INT NOT NULL DEFAULT 0 AFTER `group_id`,
    ADD COLUMN `attribute_value_id` INT NOT NULL DEFAULT 0 AFTER `value_id`,
    ADD `custom_value` VARCHAR(64) NOT NULL AFTER `value_id`,
    ADD INDEX `priority` (`priority`),
    DROP COLUMN `date_updated`,
    DROP COLUMN `date_created`,
    DROP INDEX `product_option`;"
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
    SELECT pov.product_id, pov.group_id, ov.`function`, ov.required, ov.sort FROM `". DB_TABLE_PREFIX ."products_options_values` pov
    LEFT JOIN `". DB_TABLE_PREFIX ."option_groups` ov ON (ov.id = pov.group_id)
    GROUP BY pov.product_id, pov.group_id;"
  );

  database::query(
    "UPDATE `". DB_TABLE_PREFIX ."products_options` SET sort = 'custom' WHERE sort = 'product';"
  );

// Move option groups into attribute groups

  $setting_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."settings
    where `key` = 'store_language_code'
    limit 1;"
  );

  $store_language_code = database::fetch($setting_query, 'value');

  $option_groups_query = database::query(
    "select og.*, ogi.name from ". DB_TABLE_PREFIX ."option_groups og
    left join ". DB_TABLE_PREFIX ."option_groups_info ogi on (ogi.group_id = og.id and language_code = '". database::input($store_language_code) ."')
    order by id;"
  );

  if ($option_group = database::fetch($option_groups_query)) {

    if ($option_group['function'] == 'input') $option_group['function'] = 'text';

    $attribute_groups_query = database::query(
      "select ag.*, agi.name from ". DB_TABLE_PREFIX ."attribute_groups ag
      left join ". DB_TABLE_PREFIX ."attribute_groups_info agi on (agi.group_id = ag.id and agi.language_code = '". database::input($store_language_code) ."')
      where agi.name = '". database::input($option_group['name']) ."'
      limit 1;"
    );

    if ($attribute_group = database::fetch($attribute_groups_query)) {
      $attribute_group_id = $attribute_group['id'];

    } else {
      database::query(
        "insert into ". DB_TABLE_PREFIX ."attribute_groups
        (code, date_created) values ('option_". (int)$option_group['id'] ."', '". date('Y-m-d H:i:s') ."');"
      );

      $attribute_group_id = database::insert_id();

    // Make certain the attribute group id does not collide with a previous option group id
      while (database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."option_groups where id = ". (int)$attribute_group_id ." limit 1;"))) {
        database::query(
          "update ". DB_TABLE_PREFIX ."attribute_groups
          set id = ". ($attribute_group_id+1) ."
          where id = ". (int)$attribute_group_id ."
          limit 1;"
        );
        $attribute_group_id++;
      }

      database::query(
        "insert into ". DB_TABLE_PREFIX ."attribute_groups_info (group_id, language_code, name)
        select '". $attribute_group_id ."', language_code, name from ". DB_TABLE_PREFIX ."option_groups_info
        where group_id = ". (int)$option_group['id'] .";"
      );
    }

    database::query(
      "update ". DB_TABLE_PREFIX ."products_options
      set
        group_id = ". (int)$attribute_group_id .",
        `function` = '". database::input($option_group['function']) ."',
        `sort` = '". database::input($option_group['sort']) ."'
      where group_id = ". (int)$option_group['id'] .";"
    );

  // Update values in products_options and products_options_values
    $option_values_query = database::query(
      "select ov.*, ovi.name from ". DB_TABLE_PREFIX ."option_values ov
      left join ". DB_TABLE_PREFIX ."option_values_info ovi on (ovi.value_id = ov.id and ovi.language_code = '". database::input($store_language_code) ."')
      where ov.group_id = ". (int)$option_group['id'] .";"
    );

    while ($option_value = database::fetch($option_values_query)) {

      $attribute_values_query = database::query(
        "select agv.*, agvi.name from ". DB_TABLE_PREFIX ."attribute_values agv
        left join ". DB_TABLE_PREFIX ."attribute_values_info agvi on (agvi.value_id = agv.id and agvi.language_code = '". database::input($store_language_code) ."')
        where agv.group_id = ". (int)$attribute_group_id ."
        and agvi.name = '". database::input($option_value['name']) ."'
        limit 1;"
      );

      if ($attribute_value = database::fetch($attribute_values_query)) {

        $attribute_value_id = $attribute_value['id'];

      } else {

        database::query(
          "insert into ". DB_TABLE_PREFIX ."attribute_values
          (group_id, date_created) values (". (int)$attribute_group_id .", '". date('Y-m-d H:i:s') ."');"
        );

        $attribute_value_id = database::insert_id();

      // Make certain the attribute value id does not collide with a previous option value id
        while (database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."option_values where id = ". (int)$attribute_value_id ." limit 1;"))) {
          database::query(
            "update ". DB_TABLE_PREFIX ."attribute_values
            set id = ". ($attribute_value_id+1) ."
            where id = ". (int)$attribute_value_id ."
            limit 1;"
          );
          $attribute_value_id++;
        }

        database::query(
          "insert into ". DB_TABLE_PREFIX ."attribute_values_info (value_id, language_code, name)
          select '". (int)$attribute_value_id ."', language_code, name from ". DB_TABLE_PREFIX ."option_values_info
          where value_id = ". (int)$option_value['id'] .";"
        );
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."products_options_values
        set attribute_group_id = ". (int)$attribute_group_id .",
          attribute_value_id = ". (int)$attribute_value_id ."
        where group_id = ". (int)$option_group['id'] ."
        and value_id = ". (int)$option_value['id'] .";"
      );

    // Update stock options
      database::query(
        "update ". DB_TABLE_PREFIX ."products_options_stock
        set combination = regexp_replace(combination, '(:|,)". $option_group['id'] ."-". $option_value['id'] ."', '$1". $attribute_group_id ."-". $attribute_value_id ."');"
      );

    // Update order items
      database::query(
        "update ". DB_TABLE_PREFIX ."orders_items
        set option_stock_combination = regexp_replace(option_stock_combination, '(:|,)". $option_group['id'] ."-". $option_value['id'] ."', '$1". $attribute_group_id ."-". $attribute_value_id ."');"
      );
    }
  }

  database::query(
    "ALTER TABLE ". DB_TABLE_PREFIX ."products_options_values
    DROP COLUMN `group_id`,
    DROP COLUMN `value_id`,
    CHANGE COLUMN `attribute_group_id` `group_id` INT(11) NOT NULL AFTER `product_id`,
    CHANGE COLUMN `attribute_value_id` `value_id` INT(11) NOT NULL AFTER `group_id`,
    ADD UNIQUE INDEX `product_option_value` (`id`, `product_id`, `group_id`);"
  );

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

// Move/rename cache files

  foreach (glob(FS_DIR_APP . 'cache/*') as $file) {

    $new_file = $file;

    if (preg_match('#^(.*/)_cache_(.*)_([0-9a-z]{32})$#', $file, $matches)) {
      $new_file = $matches[1] . substr($matches[3], 0, 2) .'/'. $matches[3] . '_'. $matches[2] .'.cache';

    } else if (preg_match('#^(.*/)([0-9a-z]{40}.*\.(jpe?g|gif|png|webp))$#', $file, $matches)) {
      $new_file = $matches[1] . substr($matches[2], 0, 2) .'/'. $matches[2];
    }

    else continue;

    if ($new_file != $file) {

      if (!file_exists(dirname($new_file))) {
        mkdir(dirname($new_file));
      }

      rename($file, $new_file);
    }
  }
