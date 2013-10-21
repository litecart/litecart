<?php
  header('Content-type: text/plain; charset=utf-8');
  
  error_reporting(version_compare(PHP_VERSION, '5.4.0', '>=') ? E_ALL & ~E_STRICT : E_ALL);
  ini_set('ignore_repeated_errors', 'On');
  ini_set('log_errors', 'Off');
  ini_set('display_errors', 'On');
  ini_set('html_errors', 'Off');

  if (empty($_POST['install'])) {
    header('Location: index.php');
    exit;
  }
  
// Function to get object from a relative path to this script
  function get_absolute_path($path=null) {
    if (empty($path)) $path = dirname(__FILE__);
    $path = realpath($path);
    $path = str_replace('\\', '/', $path);
    $parts = array_filter(explode('/', $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
      if ('.' == $part) continue;
      if ('..' == $part) {
        array_pop($absolutes);
      } else {
        $absolutes[] = $part;
      }
    }
    return ((strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') ? '' : '/') . implode('/', $absolutes);
  }
  
  $installation_path = get_absolute_path(dirname(__FILE__) .'/..') .'/';
  
  ### Set ###################################
  
  $_POST['admin_folder'] = str_replace('\\', '/', $_POST['admin_folder']);
  $_POST['admin_folder'] = rtrim($_POST['admin_folder'], '/') . '/';
  
  ### Config ###################################
  
  echo 'Writing config file...';
  
  $config = file_get_contents('config');
  
  $map = array(
    '{ADMIN_FOLDER}' => rtrim($_POST['admin_folder'], '/'),
    '{DB_SERVER}' => $_POST['db_server'],
    '{DB_USERNAME}' => $_POST['db_username'],
    '{DB_PASSWORD}' => $_POST['db_password'],
    '{DB_DATABASE}' => $_POST['db_database'],
    '{DB_TABLE_PREFIX}' => $_POST['db_table_prefix'],
    '{DB_DATABASE_CHARSET}' => 'utf8',
    '{DB_PERSISTENT_CONNECTIONS}' => 'false',
    '{CLIENT_IP}' => $_POST['client_ip'],
    '{PASSWORD_SALT}' => substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ", 10)), 0, 128),
  );
  
  foreach ($map as $search => $replace) {
    $config = str_replace($search, $replace, $config);
  }
  
  define('PASSWORD_SALT', $map['{PASSWORD_SALT}']); // we need it for later
  
  file_put_contents('../includes/config.inc.php', $config);
  
  echo ' [Done]' . PHP_EOL;
  
  ### Database > Connection ################################### 

  echo 'Connecting to database...';
  
  define('DB_SERVER', $_POST['db_server']);
  define('DB_USERNAME', $_POST['db_username']);
  define('DB_PASSWORD', $_POST['db_password']);
  define('DB_DATABASE', $_POST['db_database']);
  define('DB_TABLE_PREFIX', $_POST['db_table_prefix']);
  define('DB_DATABASE_CHARSET', 'utf8');
  define('DB_PERSISTENT_CONNECTIONS', 'false');
  
  require('database.class.php');
  $database = new database(null);
  
  echo ' [Done]' . PHP_EOL;
  
  ### Database > Cleaning ###################################
  
  echo 'Cleaning database...';
  
  $sql = file_get_contents('clean.sql');
  $sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, $sql);
  
  $sql = explode('-- --------------------------------------------------------', $sql);
  
  foreach ($sql as $query) {
    $query = preg_replace('/--.*\s/', '', $query);
    $database->query($query);
  }
  
  echo ' [Done]' . PHP_EOL;
  
  ### Database > Tables > Structure ###################################
  
  echo 'Writing database tables...';
  
  $sql = file_get_contents('structure.sql');
  $sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, $sql);
  
  $sql = explode('-- --------------------------------------------------------', $sql);
  
  foreach ($sql as $query) {
    $query = preg_replace('/--.*\s/', '', $query);
    $database->query($query);
  }
  
  echo ' [Done]' . PHP_EOL;
  
  ### Database > Tables > Data ###################################
  
  echo 'Writing database table data...';
  
  $sql = file_get_contents('data.sql');
  $sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, $sql);
  
  $map = array(
    '{STORE_NAME}' => $_POST['store_name'],
    '{STORE_EMAIL}' => $_POST['store_email'],
    '{STORE_TIME_ZONE}' => $_POST['store_time_zone'],
  );
  
  foreach ($map as $search => $replace) {
    $sql = str_replace($search, $replace, $sql);
  }
  
  $sql = explode('-- --------------------------------------------------------', $sql);
  
  foreach ($sql as $query) {
    $query = preg_replace('/--.*\s/', '', $query);
    $database->query($query);
  }
  
  echo ' [Done]' . PHP_EOL;
  
  ### Database > Tables > Demo Data ###################################
  
  if (!empty($_POST['demo_data'])) {
    echo 'Writing demo data...';
    
    $sql = file_get_contents('demo.sql');
    
    if (!empty($sql)) {
      $sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, $sql);
       
      $sql = explode('-- --------------------------------------------------------', $sql);
      
      foreach ($sql as $query) {
        $query = preg_replace('/--.*\s/', '', $query);
        $database->query($query);
      }
    }
    
    echo ' [Done]' . PHP_EOL;
  }
  
  ### Files > Demo Data ###################################
  
  if (!empty($_POST['demo_data'])) {
  
    function xcopy($source, $target) {
      if (is_dir($source)) {
        $source = rtrim($source, '/') . '/';
        $target = rtrim($target, '/') . '/';
        if (!file_exists($target)) mkdir($target);
        $dir = opendir($source);
        while(($file = readdir($dir)) !== false) {
          if ($file == '.' || $file == '..') continue;
          xcopy($source.$file, $target.$file);
        }
      } else if (!file_exists($target)) {
        copy($source, $target);
      }
    }
    
    echo 'Copying demo files...';
    xcopy('demo_data/', $installation_path);
    echo ' [Done]' . PHP_EOL;
  }
  
  ### .htaccess mod rewrite ###################################
  
  echo 'Setting mod_rewrite base path...';
  
  $htaccess = file_get_contents('htaccess');
  
  $base_dir = str_replace(get_absolute_path($_SERVER['DOCUMENT_ROOT']), '', $installation_path);
  
  $htaccess = str_replace('{BASE_DIR}', $base_dir, $htaccess);
  
  file_put_contents('../.htaccess', $htaccess) or die();
  
  echo ' [Done]' . PHP_EOL;
  
  ### Admin > Folder ###################################
  
  rename('../admin/', '../'.$_POST['admin_folder']);
  
  ### Admin > .htaccess Protection ###################################
  
  echo 'Securing admin folder...';
    
  $htaccess = '# Denied content' . PHP_EOL
            . '<FilesMatch "\.(htaccess|htpasswd|inc.php)$">' . PHP_EOL
            . '  Order Allow,Deny' . PHP_EOL
            . '  Deny from all' . PHP_EOL
            . '</FilesMatch>' . PHP_EOL
            . PHP_EOL
            . 'AuthType Basic' . PHP_EOL
            . 'AuthName "Restricted Area"' . PHP_EOL
            . 'AuthUserFile ' . $installation_path . $_POST['admin_folder'] . '.htpasswd' . PHP_EOL
            . 'Require valid-user' . PHP_EOL;
  
  file_put_contents('../'. $_POST['admin_folder'] .'.htaccess', $htaccess) or die();
  
  echo ' [Done]' . PHP_EOL;
  
  ### Admin > .htpasswd Users ###################################
  
  echo 'Granting admin access for user '. $_POST['username'] .'...';
  
  $htpasswd = $_POST['username'] .':{SHA}'. base64_encode(sha1($_POST['password'], true)) . PHP_EOL;
  file_put_contents('../'. $_POST['admin_folder'] . '.htpasswd', $htpasswd) or die();
  
  echo ' [Done]' . PHP_EOL;
  
  ### Admin > Database > Users ###################################
  
  require('../includes/functions/password.inc.php');
  
  $database->query(
    "insert into ". str_replace('`lc_', '`'.DB_TABLE_PREFIX, '`lc_users`') ."
    (`id`, `status`, `username`, `password`, `date_updated`, `date_created`)
    values ('1', '1', '". $database->input($_POST['username']) ."', '". password_checksum('1', $_POST['password']) ."', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
  );
  
  ## Windows OS Adjustments ###################################
  
  if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
    $database->query(
      "update ". str_replace('`lc_', '`'.DB_TABLE_PREFIX, '`lc_languages`') ."
      set locale = 'english',
      charset = 'ISO-8859-1'
      where code = 'en'
      limit 1;"
    );
    $database->query(
      "update ". str_replace('`lc_', '`'.DB_TABLE_PREFIX, '`lc_currencies`') ."
      set suffix = ' EUR'
      where code = 'EUR'
      limit 1;"
    );
  }
  
  ### ###################################
  
  echo PHP_EOL . 'Installation complete! Please delete the ~/install/ folder.' . PHP_EOL
     . PHP_EOL . 'You may now log in to the administration area and start configuring your store.' . PHP_EOL;
  
?>