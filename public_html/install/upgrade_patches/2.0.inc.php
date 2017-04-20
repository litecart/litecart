<?php

// Delete old files
  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . 'orders.app/printable_packing_slip.php',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . 'orders.app/printable_order_copy.php',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . 'sales.widget/',
    FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'errors.log',
    FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'performance.log',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'no_image.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'icons/',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'fancybox/',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'responsiveslider/',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jquery/jquery-1.12.4.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jquery/jquery-migrate-1.4.1.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jquery/jquery.animate_from_to-1.0.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jquery/jquery.cookie.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jquery/jquery.tabs.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'select2/',
    FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_account.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_search.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_most_popular_products.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order_action/',
    FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order_success/',
    FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'mod_order_action.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'mod_order_success.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.admin/images/fancybox/',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.admin/images/loader.png',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.admin/styles/',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.admin/views/doc.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.admin/views/login.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/fonts/',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/cursors/',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/images/fancybox/',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/images/cart.png',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/images/cart_filled.png',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/images/loader.png',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/styles/',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/box_account.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/box_categories.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/box_category.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/box_create_account.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/box_customer_service.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/box_edit_account.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/box_information.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/box_login.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/box_manufacturer.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/box_manufacturers.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/box_most_popular_products.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/box_order_history.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/box_order_success.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/box_page.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/box_regional_settings.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/box_search.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/box_search_results.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/box_slider.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/index.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/printable_order_copy.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/views/printable_packing_slip.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'column_left.inc.php',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }

// Copy new files
  $copy_files = array(
    'data/default/public_html/data/bad_urls.txt' => FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'bad_urls.txt',
    'data/default/public_html/images/no_image.png' => FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'no_image.png',
  );

  foreach ($copy_files as $source => $destination) {
    if (!file_xcopy($source, $destination)) {
      die('<span class="error">[Error]</span></p>');
    }
  }

// Modify some files
  $modified_files = array(
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'config.inc.php',
      'search'  => "  define('WS_DIR_INCLUDES',    WS_DIR_HTTP_HOME . 'includes/');" . PHP_EOL,
      'replace' => "  define('WS_DIR_INCLUDES',    WS_DIR_HTTP_HOME . 'includes/');" . PHP_EOL
                 . "  define('WS_DIR_LOGS',        WS_DIR_HTTP_HOME . 'logs/');" . PHP_EOL,
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'config.inc.php',
      'search'  => "  ini_set('error_log', FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'errors.log');" . PHP_EOL,
      'replace' => "  ini_set('error_log', FS_DIR_HTTP_ROOT . WS_DIR_LOGS . 'errors.log');" . PHP_EOL,
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'config.inc.php',
      'search'  => "  define('DB_TABLE_MANUFACTURERS_INFO',                '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'manufacturers_info`');",
      'replace' => "  define('DB_TABLE_MANUFACTURERS_INFO',                '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'manufacturers_info`');" . PHP_EOL
                 . "  define('DB_TABLE_MODULES',                           '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'modules`');",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'config.inc.php',
      'search'  => "  define('DB_TABLE_SLIDES',                            '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'slides`');",
      'replace' => "  define('DB_TABLE_SLIDES',                            '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'slides`');" . PHP_EOL
                 . "  define('DB_TABLE_SLIDES_INFO',                       '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'slides_info`');",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . '.htaccess',
      'search'  => '<FilesMatch "\.(css|js)$">',
      'replace' => '<FilesMatch "\.(css|js|svg)$">',
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . '.htaccess',
      'search'  => '<FilesMatch "\.(css|gif|ico|jpg|jpeg|js|pdf|png|ttf)$">',
      'replace' => '<FilesMatch "\.(css|gif|ico|jpg|jpeg|js|pdf|png|svg|ttf)$">',
    )
  );

  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span></p>');
    }
  }

// Delete Deprecated Modules
  $module_types_query = database::query(
    "select * from ". DB_TABLE_SETTINGS ."
    where `key` in ('order_action_modules', 'order_success_modules');"
  );
  while($module_type = database::fetch($module_types_query)) {
    foreach (explode(';', $module_type['value']) as $module) {
      database::query(
        "delete from ". DB_TABLE_SETTINGS ."
        where `key` = '". database::input($module) ."';"
      );
    }
    database::query(
      "delete from ". DB_TABLE_SETTINGS ."
      where `key` = '". database::input($module_type['key']) ."'
      limit 1;"
    );
  }

// Migrate Modules
  database::query(
    "update ". DB_TABLE_SETTINGS ."
    set `key` = 'job_modules'
    where `key` = 'jobs_modules';"
  );

  $installed_modules_query = database::query(
    "select * from ". DB_TABLE_SETTINGS ."
    where `key` in ('job_modules', 'customer_modules', 'order_modules', 'order_total_modules', 'shipping_modules', 'payment_modules');"
  );

  while($installed_modules = database::fetch($installed_modules_query)) {
    foreach (explode(';', $installed_modules['value']) as $module) {

      $module_query = database::query(
        "select * from ". DB_TABLE_SETTINGS ."
        where `key` = '". database::input($module) ."'
        limit 1;"
      );

      $module = database::fetch($module_query);

      if (empty($module)) continue;

      $type = preg_replace('#^(.*)_modules$#', '$1', $installed_modules['key']);
      $module['settings'] = unserialize($module['value']);

      if (isset($module['settings']['status'])) {
        $status = in_array(strtolower($module['settings']['status']), array('1', 'active', 'enabled', 'on', 'true', 'yes')) ? 1 : 0;
      } else {
        $status = 1;
      }

      if (isset($module['settings']['priority'])) {
        $priority = (int)$module['settings']['priority'];
      } else if (isset($module['settings']['sort_order'])) {
        $priority = (int)$module['settings']['sort_order'];
      } else {
        $priority = 0;
      }

      mb_convert_variables('UTF-8', null, $module['settings']);

      database::query(
        "insert into `". DB_DATABASE ."`.`". DB_TABLE_PREFIX . "modules`
        (module_id, type, status, settings, priority, date_updated, date_created)
        values ('". database::input($module['key']) ."', '". database::input($type) ."', ". (int)$status .", '". database::input(json_encode($module['settings'])) ."', ". (int)$priority .", '". $module['date_updated'] ."', '". $module['date_created'] ."');"
      );

      database::query(
        "delete from ". DB_TABLE_SETTINGS ."
        where `key` = '". database::input($module['key']) ."'
        limit 1;"
      );
    }
  }

// Collect all languages
  $languages_query = database::query(
    "select * from ". DB_TABLE_LANGUAGES ."
    where status
    order by priority, name;"
  );

  $all_languages = array();
  while ($row = database::fetch($languages_query)) {
    $all_languages[] = $row['code'];
  }

// Update slides
  $slides_query = database::query(
    "select * from ". DB_TABLE_SLIDES .";"
  );

  while($slide = database::fetch($slides_query)) {

    if (!empty($slide['language_code'])) {
      $languages = array($slide['language_code']);
    } else {
      $languages = $all_languages;
    }

    foreach ($languages as $language_code) {
      database::query(
        "insert into `". DB_DATABASE ."`.`". DB_TABLE_PREFIX . "slides_info`
        (slide_id, language_code, caption, link)
        values (". (int)$slide['id'] .", '". database::input($language_code) ."', '". database::input($slide['caption']) ."', '". database::input($slide['link']) ."');"
      );
    }
  }

  database::query("alter table ". DB_TABLE_SLIDES ." change column `language_code` `languages` varchar(32) not null, drop column `caption`, drop column `link`;");
