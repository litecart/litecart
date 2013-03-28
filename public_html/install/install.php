<?php
  header('Content-type: text/plain; charset=utf-8');
  
  error_reporting(E_ALL);
  ini_set('ignore_repeated_errors', 'On');
  ini_set('log_errors', 'Off');
  ini_set('display_errors', 'On');
  ini_set('html_errors', 'Off');

  if (empty($_POST['install'])) {
    header('Location: index.php');
    exit;
  }
  
  ### Set ###################################
  
  $_POST['admin_folder'] = str_replace('\\', '/', $_POST['admin_folder']);
  $_POST['admin_folder'] = rtrim($_POST['admin_folder'], '/') . '/';
  
  ### Config ###################################
  
  echo 'Writing config file...';
  
  $config = file_get_contents('config');
  
  $map = array(
    '{ADMIN_FOLDER}' => $_POST['admin_folder'],
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
  
  file_put_contents('../includes/config.inc.php', $config);
  
  echo ' [OK]' . PHP_EOL;
  
  ### Database Structure ################################### 
  
  echo 'Writing database tables...';
  
  define('DB_SERVER', $_POST['db_server']);
  define('DB_USERNAME', $_POST['db_username']);
  define('DB_PASSWORD', $_POST['db_password']);
  define('DB_DATABASE', $_POST['db_database']);
  define('DB_TABLE_PREFIX', $_POST['db_table_prefix']);
  define('DB_DATABASE_CHARSET', 'utf8');
  define('DB_PERSISTENT_CONNECTIONS', 'false');
  
  require('database.class.php');
  $database = new database(null);
  /*
  $sql = file_get_contents('structure.sql');
  $sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, $sql);
  
  $sql = explode('-- --------------------------------------------------------', $sql);
  
  foreach ($sql as $query) {
    $query = preg_replace('/--.*\s/', '', $query);
    $database->query($query);
  }
  
  echo ' [OK]' . PHP_EOL;
  
  ### Data ###################################
  
  if (!empty($_POST['demo_data'])) {
    echo 'Writing database table data...';
    
    $sql = file_get_contents('data.sql');
    $sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, $sql);
    
    $sql = explode('-- --------------------------------------------------------', $sql);
    
    foreach ($sql as $query) {
      $query = preg_replace('/--.*\s/', '', $query);
      $database->query($query);
    }
    
    echo ' [OK]' . PHP_EOL;
  }
  
  ### Demo Data ###################################
  
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
    
    echo ' [OK]' . PHP_EOL;
  }
  */
  ### Settings ###################################
  
  echo 'Applying settings...';
  
  $database->query(
    "update ". DB_TABLE_PREFIX."settings
    set value = '". $database->input($_POST['store_name']) ."'
    where `key` = 'store_name'
    limit 1;"
  );
  
  $database->query(
    "update ". DB_TABLE_PREFIX."settings
    set value = '". $database->input($_POST['store_email']) ."'
    where `key` = 'store_email'
    limit 1;"
  );
  
  $database->query(
    "update ". DB_TABLE_PREFIX."settings
    set value = '". $database->input($_POST['store_timezone']) ."'
    where `key` = 'store_timezone'
    limit 1;"
  );
  
  echo ' [OK]' . PHP_EOL;
  
  ### .htaccess mod rewrite ###################################
  
  echo 'Setting mod_rewrite base path...';
  
  $htaccess = file_get_contents('htaccess');
  
  function get_absolute_path($path=null) {
    if (empty($path)) $path = dirname(__FILE__);
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
    return implode('/', $absolutes);
  }
  
  $base_dir = str_replace(rtrim($_POST['installation_path'], '/'), '', get_absolute_path(dirname(__FILE__) .'/..') .'/');
  
  $htaccess = str_replace('{BASE_DIR}', $base_dir, $htaccess);
  
  file_put_contents('../.htaccess', $htaccess) or die();
  
  echo ' [OK]' . PHP_EOL;
  
  ### Admin Folder ###################################
  
  rename('../admin/', '../'.$_POST['admin_folder']);
  
  ### .htaccess Protection ###################################
  
  echo 'Securing admin folder...';
    
  $htaccess = 'AuthType Basic' . PHP_EOL
            . 'AuthName "Restricted Area"' . PHP_EOL
            . 'AuthUserFile ' . $_POST['installation_path'] . $_POST['admin_folder'] . '.htpasswd' . PHP_EOL
            . 'Require valid-user' . PHP_EOL;
  
  file_put_contents('../'. $_POST['admin_folder'] .'.htaccess', $htaccess) or die();
  
  echo ' [OK]' . PHP_EOL;
  
  ### .htpasswd Users ###################################
  
  echo 'Granting admin access for user '. $_POST['username'] .'...';
  
  $htpasswd = $_POST['username'] .':{SHA}'. base64_encode(sha1($_POST['password'], true)) . PHP_EOL;
  file_put_contents('..' . DIRECTORY_SEPARATOR . $_POST['admin_folder'] . '.htpasswd', $htpasswd) or die();
  
  echo ' [OK]' . PHP_EOL;
  
  ### ###################################
  
  echo PHP_EOL . 'Installation complete! Please delete the install/ folder.';
  
?>