<?php

// Function to get object from a relative path to this script
  function get_absolute_path($path) {
    $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
      if ('.' == $part) continue;
      if ('..' == $part) {
        array_pop($absolutes);
      } else {
        $absolutes[] = $part;
      }
    }
    return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $absolutes);
  }
  
  $document_root = get_absolute_path(dirname(__FILE__) . '/..') . '/';
  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LiteCart Installer</title>
<style>
body {
  margin: 10px;
  
  background: #31353e;
  
  font-size: 12px;
  font-family: Arial, Helvetica,sans-serif;
  color: #333;
}
a:link, a:visited{
  color: #3863c5;
  text-decoration: none;
}
a:hover, a:active{
  color: #c00;
  text-decoration: underline;
}
#body-wrapper {
  margin-left: auto;
  margin-right: auto;
  margin-bottom: 10px;
  
  max-width: 640px;
  
  padding: 10px;
  
  border: 1px solid rgba(128,128,128,0.5);
  
  border-radius: 15px 15px 15px 15px;
  -moz-border-radius: 15px 15px 15px 15px;
  -webkit-border-radius: 15px;
  
  box-shadow: 0 2px 6px rgba(0,0,0,0.5), inset 0 1px rgba(255,255,255,0.3), inset 0 10px rgba(255,255,255,0.2), inset 0 10px 20px rgba(255,255,255,0.25), inset 0 -15px 30px rgba(0,0,0,0.3);
  -o-box-shadow: 0 2px 6px rgba(0,0,0,0.5), inset 0 1px rgba(255,255,255,0.3), inset 0 10px rgba(255,255,255,0.2), inset 0 10px 20px rgba(255,255,255,0.25), inset 0 -15px 30px rgba(0,0,0,0.3);
  -webkit-box-shadow: 0 2px 6px rgba(0,0,0,0.5), inset 0 1px rgba(255,255,255,0.3), inset 0 10px rgba(255,255,255,0.2), inset 0 10px 20px rgba(255,255,255,0.25), inset 0 -15px 30px rgba(0,0,0,0.3);
  -moz-box-shadow: 0 2px 6px rgba(0,0,0,0.5), inset 0 1px rgba(255,255,255,0.3), inset 0 10px rgba(255,255,255,0.2), inset 0 10px 20px rgba(255,255,255,0.25), inset 0 -15px 30px rgba(0,0,0,0.3);
}
#body{
  padding: 20px;
  background-color: #fff;
  border: 1px #919191 solid;
  
  border-radius: 15px;
  -moz-border-radius: 15px;
  -webkit-border-radius: 15px;
}
</style>
</head>
<body>
<div id="body-wrapper">
  <div id="body">
  <img src="../images/logotype.png" height="60" align="right" />
  <h1>Install LiteCart</h1>
  <h2>System Requirements</h2>
  <ul>
    <li>Linux Machine</li>
    <li>PHP 5.1+
      <ul>
        <li>Register globals = off</li>
      </ul>
    </li>
    <li>Apcahe compatible HTTP daemon
      <ul>
        <li>mod_auth (SHA-supported)</li>
        <li>mod_redirect</li>
        </ul>
    </li>
    </ul>
  <h2>Client Requirements</h2>
  <ul>
    <li>XHTML</li>
    <li>CSS 3 (IE9+)</li>
  </ul>
<h2>Writables</h2>
  <ul>
<?php
  $files = array(
    'cache/',
    'data/',
    'images/',
    'includes/config.inc.php',
  );
  foreach($files as $file) {
    if (is_writable('../'.$file)) {
      echo '    <li>'. $file .' <span style="color: #0a0;">[OK]</span></li>' . PHP_EOL;
    } else {
      echo '    <li>'. $file .' <span style="color: #f00;">[Read-Only, please make writable]</span></li>' . PHP_EOL;
    }
  }
?>
  </ul>
  
  <h2>Installation Parameters</h2>
  <form id="form1" name="form1" method="post" action="install.php">
    <h3>MySQL</h3>
    <table border="0" cellspacing="0" cellpadding="5">
      <tr>
        <td><strong>Hostname</strong><br />
          <input name="db_server" type="text" value="localhost" style="width: 175px;" />
        </td>
        <td><strong>Database</strong><br />
        <input type="text" name="db_database" style="width: 175px;" /></td>
      </tr>
      <tr>
        <td><strong>Username</strong><br />
        <input type="text" name="db_username" style="width: 175px;" /></td>
        <td><strong>Password</strong><br />
        <input type="text" name="db_password" style="width: 175px;" /></td>
      </tr>
      <tr>
        <td><strong>Table Prefix</strong><br />
        <input name="db_table_prefix" type="text" value="lc_" style="width: 75px;" /></td>
        <td><strong>Demo Data</strong><br />
          <input name="demo_data" type="checkbox" value="true" checked="CHECKED" /> Install demo data</td>
      </tr>
    </table>
    <h3>Store Information</h3>
    <table border="0" cellspacing="0" cellpadding="5">
      <tr>
        <td><strong>Store Name</strong><br />
          <input name="store_name" type="text" value="My Store" style="width: 175px;" /></td>
        <td><strong>Store E-mail</strong><br />
          <input name="store_email" type="text" value="store@email.com" style="width: 175px;" /></td>
      </tr>
      <tr>
        <td><strong>Time Zone</strong><br />
          <select name="store_timezone" style="width: 175px;">
<?php
  $zones = timezone_identifiers_list();
       
  foreach ($zones as $zone) {
    $zone = explode('/', $zone); // 0 => Continent, 1 => City
   
    // Only use "friendly" continent names
    if (in_array($zone[0], array('Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific'))) {
      if (!empty($zone[1])) {
        echo '<option>'. $zone[0]. '/' . $zone[1]  .'</option>';
      }
   }
  }
?>
        </select></td>
        <td>&nbsp;</td>
      </tr>
    </table>
    <h3>Administration</h3>
    <table border="0" cellspacing="0" cellpadding="5">
      <tr>
        <td><strong>Folder Name</strong><br />
          <input name="admin_folder" type="text" value="admin/" style="width: 175px;" /></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td><strong>Username</strong><br />
          <input name="username" type="text" id="username" value="admin" style="width: 175px;" /></td>
        <td><strong>Password</strong><br />
          <input name="password" type="text" id="password" style="width: 175px;" /></td>
      </tr>
    </table>
    <h3>File System</h3>
    <table border="0" cellspacing="0" cellpadding="5">
      <tr>
        <td><strong>Installation Path</strong><br />
          <input name="installation_path" type="text"  value="<?php echo $document_root; ?>" style="width: 360px;" /></td>
        </tr>
    </table>
    <p>
      <input type="submit" name="install" value="Install Now" />
  </p>
  </form>
  </div>
</div>
<body>
</body>
</html>