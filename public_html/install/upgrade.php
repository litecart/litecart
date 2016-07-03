<?php
  // Automatic upgrade: upgrade.php?from_version={version}&upgrade=true&redirect={url}

  ob_start();

  require_once('../includes/config.inc.php');
  require_once('includes/header.inc.php');
  require_once('includes/functions.inc.php');
  require_once('includes/database.class.php');

// Turn on errors
  error_reporting(version_compare(PHP_VERSION, '5.4.0', '>=') ? E_ALL & ~E_STRICT : E_ALL);
  ini_set('ignore_repeated_errors', 'On');
  ini_set('log_errors', 'Off');
  ini_set('display_errors', 'On');
  ini_set('html_errors', 'On');

// Set platform name
  preg_match('#define\(\'PLATFORM_NAME\', \'([^\']+)\'\);#', file_get_contents('../includes/app_header.inc.php'), $matches);
  define('PLATFORM_NAME', isset($matches[1]) ? $matches[1] : false);

// Set platform version
  preg_match('#define\(\'PLATFORM_VERSION\', \'([^\']+)\'\);#', file_get_contents('../includes/app_header.inc.php'), $matches);
  define('PLATFORM_VERSION', isset($matches[1]) ? $matches[1] : false);

  if (!PLATFORM_VERSION) die('Could not identify target version.');

  $database = new database(null);

// Get current platform database version
  $platform_database_version_query = $database->query(
    "select `value` from ". DB_TABLE_SETTINGS ."
    where `key` = 'platform_database_version'
    limit 1;"
  );
  $platform_database_version = $database->fetch($platform_database_version_query);

  if (!empty($platform_database_version)) {
    define('PLATFORM_DATABASE_VERSION', $platform_database_version['value']);
    if (empty($_POST['from_version'])) $_POST['from_version'] = PLATFORM_DATABASE_VERSION;
  }

// List supported upgrades
  $supported_versions = array('1.0' => '1.0');
  foreach (glob("upgrade_patches/*") as $file) {
    preg_match('/\/(.*).(inc.php|sql)$/', $file, $matches);
    $supported_versions[$matches[1]] = $matches[1];
  }
  usort($supported_versions, function($a, $b) {
    return version_compare($a, $b, '>');
  });

  echo '<h1>Upgrade</h1>' . PHP_EOL;

  if (!empty($_REQUEST['upgrade'])) {

    if (empty($_REQUEST['from_version'])) die('You must select the version you are migrating from.');

    #############################################

    foreach ($supported_versions as $version) {
      if (version_compare($_REQUEST['from_version'], $version, '<')) {
        if (file_exists('upgrade_patches/'. $version .'.sql')) {
          echo '<p>Upgrading database to '. $version .'... ';
            $sql = file_get_contents('upgrade_patches/'. $version .'.sql');
            $sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, $sql);

            $sql = explode('-- --------------------------------------------------------', $sql);

            foreach ($sql as $query) {
              $query = preg_replace('/--.*\s/', '', $query);
              $database->query($query);
            }
          echo '<span class="ok">[OK]</span></p>' . PHP_EOL;
        }

        if (file_exists('upgrade_patches/'. $version .'.inc.php')) {
          echo '<p>Upgrading system to '. $version .'... ';
          include('upgrade_patches/'. $version .'.inc.php');
          echo '<span class="ok">[OK]</span></p>' . PHP_EOL;
        }
      }
    }

    #############################################

    echo '<p>Set platform database version...';

    if (defined('PLATFORM_VERSION')) {

      $database->query(
        "update ". str_replace('`lc_', '`'.DB_TABLE_PREFIX, '`lc_settings`') ."
        set `value` = '". $database->input(PLATFORM_VERSION) ."'
        where `key` = 'platform_database_version'
        limit 1;"
      );

      echo ' <strong>'. PLATFORM_VERSION .'</strong></p>' . PHP_EOL;

    } else {
      echo ' <span class="error">[Error: Not defined]</span></p>' . PHP_EOL;
    }

    #############################################

    echo '<p>Clear cache... ';

    $database->query(
      "update ". DB_TABLE_SETTINGS ."
      set value = '1'
      where `key` = 'cache_clear'
      limit 1;"
    );

    foreach(glob(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'vqmod/vqcache/*.php') as $file){
      if (is_file($file)) unlink($file);
    }

    echo '<span class="ok">[OK]</span></p>' . PHP_EOL;

    #############################################

    if (!empty($_REQUEST['redirect'])) {
      header('Location: '. $_REQUEST['redirect']);
      exit;
    }

    echo PHP_EOL . '<h2>Complete</h2>' . PHP_EOL
       . '<p style="font-weight: bold;">Upgrade complete! Please delete the <strong>~/install/</strong> folder.</p>' . PHP_EOL;

    require('includes/footer.inc.php');
    exit;
  }

?>
<p>Upgrade from an old installation to <?php echo PLATFORM_NAME; ?> <?php echo PLATFORM_VERSION; ?>.</p>
<p><strong style="color: #f00;">Backup your files and database before you continue!<br />Selecting the wrong version might damage your data.</strong></p>
<form name="upgrade_form" method="post">
  <h3>Version Migrating From</h3>
  <table>
    <tr>
      <td>Select the <?php echo PLATFORM_NAME; ?> version you are migrating from:<br />
        <select name="from_version">
          <option value="">-- Select Version --</option>
          <?php foreach ($supported_versions as $version) echo '<option value="'. $version .'">'. $version . ((defined('PLATFORM_DATABASE_VERSION') && PLATFORM_DATABASE_VERSION == $version) ? ' (Detected)' : '') .'</option>' . PHP_EOL; ?>
        </select>
      </td>
    </tr>
  </table>
  <p><input type="submit" name="upgrade" value="Upgrade To <?php echo PLATFORM_VERSION; ?>" onclick="if(!confirm('Warning! The procedure cannot be undone.')) return false;" /></p>
</form>
<?php
  require('includes/footer.inc.php');
?>