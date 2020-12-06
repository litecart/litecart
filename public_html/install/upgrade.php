<?php
  // Automatic upgrade: upgrade.php?upgrade=true&redirect={url}

  if (php_sapi_name() == 'cli') {

    if ((!isset($argv[1])) || ($argv[1] == 'help') || ($argv[1] == '-h') || ($argv[1] == '--help')) {
      echo "\nLiteCart® 2.2.6\n"
      . "Copyright (c) ". date('Y') ." LiteCart AB\n"
      . "https://www.litecart.net/\n"
      . "Usage: php install.php [options]\n\n"
      . "Options:\n"
      . "  --from_version       Manually set version migrating from. Omit for auto detection\n"
      . "  --development_type   Set development type 'standard' or 'development' (Default: standard)\n";
      exit;
    }

    $options = array(
      'from_version::', 'development_type::'
    );

    $_REQUEST = getopt(null, $options);
    $_REQUEST['upgrade'] = true;

  } else {
    require_once(__DIR__ . '/includes/header.inc.php');
  }

  error_reporting(version_compare(PHP_VERSION, '5.4.0', '<') ? E_ALL | E_STRICT : E_ALL);
  ini_set('ignore_repeated_errors', 'On');
  ini_set('log_errors', 'Off');
  ini_set('display_errors', 'On');
  ini_set('html_errors', 'On');

  if (preg_match('#^(off|false|0)$#i', ini_get('safe_mode'))) {
    ignore_user_abort(true);
    set_time_limit(300);
  }

  if (!is_file(__DIR__ . '/../includes/config.inc.php')) {
    echo '<h2>No Installation Detected</h2>' . PHP_EOL
       . '<p>Warning: No configuration file was found.</p>' . PHP_EOL
       . '<p><a class="btn btn-default" href="index.php">Click here to install instead</a></p>' . PHP_EOL;
    require('includes/footer.inc.php');
    return;
  }

  require_once(__DIR__ . '/../includes/config.inc.php');
  if (!defined('FS_DIR_APP')) define('FS_DIR_APP', FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME);
  if (!defined('FS_DIR_ADMIN')) define('FS_DIR_ADMIN', FS_DIR_HTTP_ROOT . WS_DIR_ADMIN);
  if (!defined('WS_DIR_APP')) define('WS_DIR_APP', WS_DIR_HTTP_HOME);

  require_once(FS_DIR_APP . 'includes/error_handler.inc.php');
  require_once(FS_DIR_APP . 'includes/library/lib_database.inc.php');
  require_once(__DIR__ . '/includes/functions.inc.php');

// Set platform name
  preg_match('#define\(\'PLATFORM_NAME\', \'([^\']+)\'\);#', file_get_contents(FS_DIR_APP . 'includes/app_header.inc.php'), $matches);
  define('PLATFORM_NAME', isset($matches[1]) ? $matches[1] : false);

// Set platform version
  preg_match('#define\(\'PLATFORM_VERSION\', \'([^\']+)\'\);#', file_get_contents(FS_DIR_APP . 'includes/app_header.inc.php'), $matches);
  define('PLATFORM_VERSION', isset($matches[1]) ? $matches[1] : false);

  if (!PLATFORM_VERSION) die('Could not identify target version.');

// Get current platform database version
  $platform_database_version_query = database::query(
    "select `value` from ". DB_TABLE_SETTINGS ."
    where `key` = 'platform_database_version'
    limit 1;"
  );

  if ($platform_database_version = database::fetch($platform_database_version_query)) {
    define('PLATFORM_DATABASE_VERSION', $platform_database_version['value']);
  }

// List supported upgrades
  $supported_versions = array('1.0' => '1.0');
  foreach (glob(__DIR__ . '/upgrade_patches/*') as $file) {
    if (preg_match('#/([^/]+).(?:inc.php|sql)$#', $file, $matches)) {
      $supported_versions[$matches[1]] = $matches[1];
    }
  }

  usort($supported_versions, function($a, $b) {
    return version_compare($a, $b, '>');
  });

  if (!empty($_REQUEST['upgrade'])) {

    ob_start();

    try {

      echo '<h1>Upgrade</h1>' . PHP_EOL . PHP_EOL;

      ### PHP > Check Version #######################################

      echo '<p>Checking PHP version... ';

      if (version_compare(PHP_VERSION, '5.4', '<')) {
        throw new Exception(PHP_VERSION .' <span class="error">[Error] PHP 5.4+ minimum requirement</span></p>' . PHP_EOL . PHP_EOL);
      } else if (version_compare(PHP_VERSION, '7.1', '<=')) {
        echo PHP_VERSION .' <span class="warning">[Warning] PHP '. PHP_VERSION .' has reached <a href="https://www.php.net/supported-versions.php" target="_blank">end of life</a>.</span></p>' . PHP_EOL . PHP_EOL;
      } else {
        echo PHP_VERSION .' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
      }
      ### App > Check Version #######################################

      echo '<p>Checking application database version... ';

      if (defined('PLATFORM_DATABASE_VERSION')) {
        echo PLATFORM_DATABASE_VERSION .' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
      } else if (!empty($_REQUEST['from_version'])) {
        define('PLATFORM_DATABASE_VERSION', $_REQUEST['from_version']);
        echo $_REQUEST['from_version'] . ' (User Defined) <span class="warning">[OK]</span></p>' . PHP_EOL . PHP_EOL;
      } else {
        throw new Exception(' <span class="error">[Undetected]</span></p>' . PHP_EOL . PHP_EOL);
      }

      #############################################

      foreach ($supported_versions as $version) {

        if (version_compare(PLATFORM_DATABASE_VERSION, $version, '>=')) {
          if (file_exists(__DIR__ . '/upgrade_patches/'. $version .'.sql')) unlink(__DIR__ . '/upgrade_patches/'. $version .'.sql');
          if (file_exists(__DIR__ . '/upgrade_patches/'. $version .'.inc.php')) unlink(__DIR__ . '/upgrade_patches/'. $version .'.inc.php');
          continue;
        }

        if (file_exists(__DIR__ . '/upgrade_patches/'. $version .'.sql')) {
          echo '<p>Upgrading database to '. $version .'...</p>' . PHP_EOL . PHP_EOL;
          $sql = file_get_contents(__DIR__ . '/upgrade_patches/'. $version .'.sql');
          $sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, $sql);

          $sql = explode('-- --------------------------------------------------------', $sql);

          foreach ($sql as $query) {
            $query = preg_replace('#^-- .*?\R+#m', '', $query);
            if (!empty($query)) {
              database::query($query);
            }
          }

          unlink(__DIR__ . '/upgrade_patches/'. $version .'.sql');
        }

        if (file_exists(__DIR__ . '/upgrade_patches/'. $version .'.inc.php')) {
          echo '<p>Upgrading system to '. $version .'...</p>' . PHP_EOL . PHP_EOL;
          include(__DIR__ . '/upgrade_patches/'. $version .'.inc.php');
          unlink(__DIR__ . '/upgrade_patches/'. $version .'.inc.php');
        }

        echo '<p>Set platform database version...';

        database::query(
          "update ". str_replace('`lc_', '`'.DB_TABLE_PREFIX, '`lc_settings`') ."
          set `value` = '". database::input($version) ."'
          where `key` = 'platform_database_version'
          limit 1;"
        );

        echo ' <strong>'. $version .'</strong></p>' . PHP_EOL . PHP_EOL;
      }

      #############################################

      echo '<p>Preparing CSS files...</p>' . PHP_EOL . PHP_EOL;

      if (!empty($_REQUEST['development_type']) && $_REQUEST['development_type'] == 'advanced') {

        $files_to_delete = array(
          FS_DIR_APP . 'includes/templates/default.catalog/css/app.css',
          FS_DIR_APP . 'includes/templates/default.catalog/css/checkout.css',
          FS_DIR_APP . 'includes/templates/default.catalog/css/framework.css',
          FS_DIR_APP . 'includes/templates/default.catalog/css/printable.css',
        );

        foreach ($files_to_delete as $file) {
          file_delete($file);
        }

      } else {

        $files_to_delete = array(
          FS_DIR_APP . 'includes/templates/default.catalog/css/*.min.css',
          FS_DIR_APP . 'includes/templates/default.catalog/css/*.min.css.map',
          FS_DIR_APP . 'includes/templates/default.catalog/js/*.min.js',
          FS_DIR_APP . 'includes/templates/default.catalog/js/*.min.js.map',
          FS_DIR_APP . 'includes/templates/default.catalog/less/',
        );

        foreach ($files_to_delete as $file) {
          file_delete($file);
        }

        foreach (glob(FS_DIR_APP . 'includes/templates/default.catalog/layouts/*.inc.php') as $file) {
          $contents = file_get_contents($file);
          $search_replace = array(
            'app.min.css'  => 'app.css',
            'checkout.min.css'  => 'checkout.css',
            'framework.min.css' => 'framework.css',
            'printable.min.css' => 'printable.css',
            'app.min.js' => 'app.js',
          );
          file_put_contents($file, strtr($contents, $search_replace));
        }
      }

      echo PHP_EOL;

      #############################################

      echo '<p>Clear cache... ';

      database::query(
        "update ". DB_TABLE_SETTINGS ."
        set value = '1'
        where `key` = 'cache_clear'
        limit 1;"
      );

      foreach (glob(FS_DIR_APP . 'vqmod/vqcache/*.php') as $file) {
        if (is_file($file)) unlink($file);
      }

      if (is_file($file = FS_DIR_APP . 'vqmod/checked.cache')) unlink($file);
      if (is_file($file = FS_DIR_APP . 'vqmod/mods.cache')) unlink($file);

      echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

      #############################################

      echo '<h2>Complete</h2>' . PHP_EOL . PHP_EOL
         . '<p style="font-weight: bold;">Upgrade complete! Please delete the <strong>~/install/</strong> folder.</p>' . PHP_EOL . PHP_EOL;

      if (!empty($_REQUEST['redirect'])) {
        header('Location: '. $_REQUEST['redirect']);
        exit;
      }

    } catch (Exception $e) {
      echo $e->getMessage();
    }

    $buffer = ob_get_clean();

    if (php_sapi_name() == 'cli') {
      echo strip_tags($buffer);
      exit;
    }

    echo $buffer;

    require('includes/footer.inc.php');
    exit;
  }


?>
<style>
html {
  display: table;
  width: 100%;
}
body {
  display: table-cell;
  vertical-align: middle;
}
.glass-edges {
  max-width: 640px;
}
input[name="development_type"] {
  display: none;
}
input[name="development_type"] + div {
  display: inline-block;
  padding: 15px;
  margin: 7.5px;
  border: 1px solid rgba(0,0,0,0.1);
  border-radius: 15px;
  width: 250px;
  height: 145px;
  text-align: center;
  cursor: pointer;
}
input[name="development_type"] + div .type {
  font-size: 1.5em;
  line-height: 1.5em;
}
input[name="development_type"] + div .title {
  font-size: 1.25em;
  font-weight: bold;
  line-height: 1.5em;
}
input[name="development_type"]:checked + div {
  border-color: #333;
}
</style>

<form name="upgrade_form" method="post">
  <h1>Upgrade</h1>

  <?php if (defined('PLATFORM_DATABASE_VERSION')) { ?>
  <div class="form-group">
    <label>Version</label>
    <ul class="list-inline" style="font-size: 2em;">
      <li><?php echo PLATFORM_DATABASE_VERSION; ?></li>
      <li>→</li>
      <li><?php echo PLATFORM_VERSION; ?></li>
    </ul>
  </div>
  <?php } else { ?>
  <div class="form-group">
    <label>Select the <?php echo PLATFORM_NAME; ?> version you are upgrading from:</label>
    <select class="form-control" name="from_version">
      <option value="">-- Select Version --</option>
      <?php foreach ($supported_versions as $version) echo '<option value="'. $version .'"'. ((isset($_REQUEST['from_version']) && $_REQUEST['from_version'] == $version) ? 'selected="selected"' : '') .'>'. PLATFORM_NAME .' '. $version .'</option>' . PHP_EOL; ?>
    </select>
  </div>
  <?php } ?>

  <h3>Development</h3>

  <div class="form-group" style="display: flex;">
    <label>
      <input name="development_type" value="standard" type="radio" checked="checked" />
      <div>
        <div class="type">Standard</div>
        <div class="title">
          .css<br />
          .js
        </div>
        <small class="description">(Uncompressed files)</small>
      </div>
    </label>

    <label>
      <input name="development_type" value="advanced" type="radio" />
      <div>
        <div class="type">Advanced</div>
        <div class="title">
          .less + .min.css<br />
          .js + .min.js
        </div>
        <small class="description">
          (Requires <a href="https://www.litecart.net/sv/addons/163/developer-kit" target="_blank">Developer Kit</a>)
        </small>
      </div>
    </label>
  </div>

  <p class="alert alert-danger">Backup your files <strong><u>and</u></strong> database <strong><u>before</u></strong> you continue!</p>

  <button class="btn btn-success btn-block" type="submit" name="upgrade" value="true" onclick="if(!confirm('Warning! The procedure cannot be undone.')) return false;" style="font-size: 1.5em; padding: 0.5em;" />Upgrade To <?php echo PLATFORM_NAME; ?> <?php echo PLATFORM_VERSION; ?></button>
</form>
<?php
  require('includes/footer.inc.php');
