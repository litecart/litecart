<?php
  ini_set('display_errors', 'On');
  
// Function to get object from a relative path to this script
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
    return ((substr(PHP_OS, 0, 3) == 'WIN') ? '' : '/') . implode('/', $absolutes);
  }
  
  $document_root = get_absolute_path(dirname(__FILE__) . '/..') .'/';
  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LiteCart Installer</title>
<style>
body {
  margin: 10px;
  
  background: none repeat scroll 0% 0% rgb(184, 211, 244);
  
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
  border: 1px rgba(128,128,128,0.5) solid;
  
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
  
  <h1>Installer</h1>
  
  <h2>System Requirements</h2>
  <ul>
    <li>Linux Machine <?php echo (PHP_OS == 'Linux') ? '<span style="color: #0a0;">[OK]</span>' : '<span style="color: #f00;">['. PHP_OS .']</span>'; ?></li>
    <li>PHP 5.3+ <?php echo version_compare(PHP_VERSION, '5.3', '>=') ? '<span style="color: #0a0;">['. PHP_VERSION .']</span>' : '<span style="color: #f00;">['. PHP_VERSION .']</span>'; ?>
      <ul>
        <li>Register globals = Off <?php echo (in_array(strtolower(ini_get('register_globals')), array('off', 'false', '', '0'))) ? '<span style="color: #0a0;">[OK]</span>' : '<span style="color: #f00;">[On]</span>'; ?></li>
        <li>Extensions
          <ul>
            <li>curl <?php echo extension_loaded('curl') ? '<span style="color: #0a0;">[OK]</span>' : '<span style="color: #f00;">[Missing]</span>'; ?></li>
            <li>exif <?php echo extension_loaded('exif') ? '<span style="color: #0a0;">[OK]</span>' : '<span style="color: #f00;">[Missing]</span>'; ?></li>
            <li>mbstring <?php echo extension_loaded('mbstring') ? '<span style="color: #0a0;">[OK]</span>' : '<span style="color: #f00;">[Missing]</span>'; ?></li>
            <li>mysql / mysqli <?php echo (extension_loaded('mysql') || extension_loaded('mysqli')) ? '<span style="color: #0a0;">[OK]</span>' : '<span style="color: #f00;">[Missing]</span>'; ?></li>
            <li>gd <?php echo extension_loaded('gd') ? '<span style="color: #0a0;">[OK]</span>' : '<span style="color: #f00;">[Missing]</span>'; ?></li>
          </ul>
        </li>
      </ul>
    </li>
    <li>Apacahe 2 compatible HTTP daemon
      <ul>
        <li>Allow, Deny</li>
        <li>mod_auth (SHA-supported)</li>
        <li>mod_redirect</li>
      </ul>
    </li>
    <li>MySQL 5.5+</li>
  </ul>
  
  <h2>Client Requirements</h2>
  <ul>
    <li>HTML 5</li>
    <li>CSS 3 (IE9+)</li>
  </ul>
  
  <h2>Writables</h2>
    <ul>
<?php
  $files = array(
    'admin/.htaccess',
    'admin/.htpasswd',
    'cache/',
    'data/',
    'images/',
    'includes/config.inc.php',
    '.htaccess',
  );
  foreach($files as $file) {
    if (file_exists($file) && is_writable('../' . $file)) {
      echo '      <li>~/'. $file .' <span style="color: #0a0;">[OK]</span></li>' . PHP_EOL;
    } else if (is_writable('../' . pathinfo($file, PATHINFO_DIRNAME))) {
      echo '      <li>~/'. $file .' <span style="color: #0a0;">[OK]</span></li>' . PHP_EOL;
    } else {
      echo '      <li>~/'. $file .' <span style="color: #f00;">[Read-only, please make path writable]</span></li>' . PHP_EOL;
    }
  }
?>
  </ul>
  
  <h2>Installation Parameters</h2>
  
<?php
  if (file_exists('../includes/config.inc.php')) {
    echo '<p style="color: #f00; font-weight: bold;">Attention: An existing installation has been detected. If you continue the existing installation will be overwritten.</p>';
  }
?>
  
  <form id="form1" name="form1" method="post" action="install.php">
    <h3>File System</h3>
    <table>
      <tr>
        <td><strong>Installation Path</strong><br />
          <?php echo $document_root; ?></td>
      </tr>
    </table>
    <h3>MySQL</h3>
    <table>
      <tr>
        <td><strong>Hostname</strong><br />
          <input name="db_server" type="text" value="localhost"  />
        </td>
        <td><strong>Database</strong><br />
        <input type="text" name="db_database"  /></td>
      </tr>
      <tr>
        <td><strong>Username</strong><br />
        <input type="text" name="db_username"  /></td>
        <td><strong>Password</strong><br />
        <input type="password" name="db_password"  /></td>
      </tr>
      <tr>
        <td><strong>Table Prefix</strong><br />
        <input name="db_table_prefix" type="text" value="lc_" style="width: 75px;" /></td>
        <td><strong>Demo Data</strong><br />
          <label><input name="demo_data" type="checkbox" value="true" <?php echo (file_get_contents('demo.sql') == '') ? 'disabled="disabled"' : ''; ?> /> Install demo data</label></td>
      </tr>
    </table>
    <h3>Store Information</h3>
    <table>
      <tr>
        <td><strong>Store Name</strong><br />
          <input name="store_name" type="text" value="My Store"  /></td>
        <td><strong>Store E-mail</strong><br />
          <input name="store_email" type="text" value="store@email.com"  /></td>
      </tr>
      <tr>
        <td><strong>Time Zone</strong><br />
          <select name="store_time_zone" >
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
    <table>
      <tr>
        <td><strong>Folder Name</strong><br />
          <input name="admin_folder" type="text" value="admin"  /></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td><strong>Username</strong><br />
          <input name="username" type="text" id="username" value="admin"  /></td>
        <td><strong>Password</strong><br />
          <input name="password" type="text" id="password"  /></td>
      </tr>
    </table>
    <h3>Errors
      <input name="client_ip" type="hidden" value="<?php echo $_SERVER['REMOTE_ADDR']; ?>"  />
    </h3>
    <p>Errros will be hidden for all visitors except you, determined by IP <strong><?php echo $_SERVER['REMOTE_ADDR']; ?></strong>. Some web host providers may not allow  overriding PHP error settings. Blank pages are usually the result of an error and you might need to contact your web host provider how to turn PHP error messages on.</p>
    <p>If your IP address changes, or if you need to add more, these settings can be found in the configuration file.<br />
    </p>
    <p>
      <input type="submit" name="install" value="Install Now" onclick="if(!confirm('This will now install LiteCart. Any existing installations will be overwritten with new data.')) return false;" />
    </p>
  </form>
  </div>
</div>
<body>
</body>
</html>