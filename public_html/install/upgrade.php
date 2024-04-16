<?php
  // Automatic upgrade: upgrade.php?upgrade=true&redirect={url}

  @set_time_limit(900);
  ini_set('memory_limit', -1);
  ini_set('display_errors', 'On');
  mb_internal_encoding('UTF-8');
  mb_http_output('UTF-8');

  if (php_sapi_name() == 'cli') {

    if (!isset($argv[1]) || ($argv[1] == 'help') || ($argv[1] == '-h') || ($argv[1] == '--help') || ($argv[1] == '/?')) {
      echo "\nLiteCart® 3.0.0\n"
      . "Copyright (c) ". date('Y') ." LiteCart AB\n"
      . "https://www.litecart.net/\n"
      . "Usage: php ". basename(__FILE__) ." [options]\n\n"
      . "Options:\n"
      . "  --from_version       Manually set version migrating from. Omit for auto detection\n"
      . "  --development_type   Set development type 'standard' or 'development' (Default: standard)\n"
      . "  --backup             Backup the database before running upgrade (Default: true)\n"
      . "  --cleanup            Delete the install/ directory after finising the upgrade.\n\n";
      exit;
    }

    $options = [
      'from_version::',
      'development_type::',
      'backup::',
      'cleanup::',
    ];

    $_REQUEST = getopt('', $options);
    $_REQUEST['upgrade'] = true;

    if (isset($_REQUEST['cleanup'])) {
      $_REQUEST['cleanup'] = true;
    }
  }

  require_once __DIR__ . '/includes/header.inc.php';
  require_once __DIR__ . '/includes/functions.inc.php';

// Include config
  if (is_file(__DIR__ . '/../storage/config.inc.php')) {
    include(__DIR__ . '/../storage/config.inc.php');

  } else if (is_file(__DIR__ . '/../includes/config.inc.php')) { // <3.0
    include(__DIR__ . '/../includes/config.inc.php');

  } else {
    echo '<h2>No Installation Detected</h2>' . PHP_EOL
       . '<p>Warning: No configuration file was found.</p>' . PHP_EOL
       . '<p><a class="btn btn-default" href="index.php">Click here to install instead</a></p>' . PHP_EOL;
    require('includes/footer.inc.php');
    return;
  }

  if (!defined('FS_DIR_APP')) {
    define('FS_DIR_APP', FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME); // Prior to 2.2.x
  }

  if (!defined('FS_DIR_STORAGE')) {
    define('FS_DIR_STORAGE', FS_DIR_APP . 'storage/'); // Prior to 3.0.0
  }

  if (!defined('WS_DIR_STORAGE')) {
    define('WS_DIR_STORAGE', WS_DIR_APP); // Prior to 2.5.x
  }

  error_reporting(version_compare(PHP_VERSION, '5.4.0', '<') ? E_ALL | E_STRICT : E_ALL);
  ini_set('ignore_repeated_errors', 'On');
  ini_set('log_errors', 'Off');
  ini_set('display_errors', 'On');
  ini_set('html_errors', 'On');

  ignore_user_abort(true);

  require_once FS_DIR_APP . 'includes/error_handler.inc.php';
  require_once FS_DIR_APP . 'includes/functions/func_file.inc.php';
  require_once FS_DIR_APP . 'includes/nodes/nod_database.inc.php';
  require_once FS_DIR_APP . 'includes/nodes/nod_event.inc.php';
  require_once FS_DIR_APP . 'includes/nodes/nod_functions.inc.php';
  require FS_DIR_APP . 'includes/nodes/nod_stats.inc.php';

// Set platform name
  preg_match('#define\(\'PLATFORM_NAME\', \'([^\']+)\'\);#', file_get_contents(__DIR__.'/../includes/app_header.inc.php'), $matches);
  define('PLATFORM_NAME', isset($matches[1]) ? $matches[1] : false);

// Set platform version
  preg_match('#define\(\'PLATFORM_VERSION\', \'([^\']+)\'\);#', file_get_contents(__DIR__.'/../includes/app_header.inc.php'), $matches);
  define('PLATFORM_VERSION', isset($matches[1]) ? $matches[1] : false);

  if (!PLATFORM_VERSION) die('Could not identify target version.');

// Get current platform database version
  $platform_database_version = database::query(
    "select `value` from ". DB_TABLE_PREFIX ."settings
    where `key` = 'platform_database_version'
    limit 1;"
  )->fetch('value');

  define('PLATFORM_DATABASE_VERSION', $platform_database_version);

// List supported upgrades
  $supported_versions = ['1.0' => '1.0'];
  foreach (glob(__DIR__ . '/upgrade_patches/*') as $file) {
    if (preg_match('#/([^/]+).(?:inc.php|sql)$#', $file, $matches)) {
      $supported_versions[$matches[1]] = $matches[1];
    }
  }

  usort($supported_versions, function($a, $b) {
    return version_compare($a, $b, '>') ? 1 : -1;
  });

  if (empty($_REQUEST['development_type'])) {
    if (is_file($file = FS_DIR_APP . 'includes/templates/default.catalog/.development')) {
      $_REQUEST['development_type'] = file_get_contents($file);
    }
  }

  if (!empty($_REQUEST['upgrade'])) {

    ob_start(function($buffer) {
      if (php_sapi_name() == 'cli') {
        $buffer = strip_tags($buffer);
      }
      return $buffer;
    });

    try {

      echo '<h1>Upgrade '. PLATFORM_VERSION .'</h1>' . PHP_EOL . PHP_EOL;

      ### PHP > Check Version ################################################

      echo '<p>Checking PHP version... ';

      if (version_compare(PHP_VERSION, '5.4', '<')) {
        throw new Exception(PHP_VERSION .' <span class="error">[Error] PHP 5.4+ minimum requirement</span></p>' . PHP_EOL . PHP_EOL);
      } else if (version_compare(PHP_VERSION, '7.2', '<=')) {
        echo PHP_VERSION .' <span class="warning">[Warning] PHP '. PHP_VERSION .' has reached <a href="https://www.php.net/supported-versions.php" target="_blank">end of life</a>.</span></p>' . PHP_EOL . PHP_EOL;
      } else {
        echo PHP_VERSION .' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
      }

      ### PHP > Check PHP Extensisons ###############################

      echo '<p>Checking for PHP extensions... ';

      $extensions = ['apcu', 'dom', 'fileinfo', 'gd', 'imagick', 'intl', 'json', 'libxml', 'mbstring', 'mysqli', 'mysqlnd', 'openssl', 'SimpleXML', 'zip'];

      if ($missing_extensions = array_diff($extensions, get_loaded_extensions())) {
        echo '<span class="warning">[Warning] Some important PHP extensions are missing ('. implode(', ', $missing_extensions) .'). It is recommended that you enable them in php.ini.</span></p>' . PHP_EOL . PHP_EOL;
      } else {
        echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
      }

      ### Backup > Database ##################################################

      if (isset($_REQUEST['backup']) && !preg_match('#^(0|false|no|off)$#i', $_REQUEST['backup'])) {

        echo '<p>Backing up the database... ';

        if (!file_exists(FS_DIR_STORAGE . 'backups/')) {
          if (!mkdir(FS_DIR_STORAGE . 'backups/', 0777)) {
            throw new Exception('Could not create folder for backups');
          }
        }

        $platform_database_version = database::query(
          "select `value` from ". DB_TABLE_SETTINGS ."
          where `key` = 'platform_database_version'
          limit 1;"
        )->fetch('value');

        $backup_file = FS_DIR_STORAGE . 'backups/'. PLATFORM_NAME .'-'. strftime('%Y%m%d-%H%M%S') .'-database-'. $platform_database_version .'.sql';

        if (!$backup_handle = fopen($backup_file, 'wb')) {
          throw new Exception("Cannot open backup file for writing ($backup_file)");
        }

        if (!flock($backup_handle, LOCK_EX)) {
          throw new Exception("Could not aquire an exlusive lock for writing to file ($backup_file)");
        }

        $separator = '-- --------------------------------------------------------';

        $tables_query = database::query('SHOW TABLES');
        while ($table = database::fetch($tables_query)) {
          $table = array_shift($table);

          if (!preg_match('#^'. preg_quote(DB_TABLE_PREFIX, '#') .'#', $table)) continue;

          if (!empty($use_initial_separator)) {
            $output .= $separator . PHP_EOL;
            $use_initial_separator = true;
          }

        // Drop Table
          $output = "DROP TABLE IF EXISTS `" . $table . "`;" . PHP_EOL;

        // Create Table
          $query = database::query("SHOW CREATE TABLE `" . $table . "`;");
          if ($row = database::fetch($query)) {
            $output = $separator . PHP_EOL
                    . $row['Create Table'] . ';' . PHP_EOL;
          }

          fwrite($backup_handle, $output);

          if (!empty($ignore_tables) && in_array($table, $ignore_tables)) continue;

        // Insert Data
          $columns = database::query(
            "SHOW COLUMNS FROM `" . $table ."`"
          )->fetch_all('Field');

          $rows_query = database::query(
            "SELECT `" . implode('`, `', $columns) . "` FROM `" . $table ."`"
          );

          if (!database::num_rows($rows_query)) continue;

          $output = $separator . PHP_EOL
                  . "INSERT INTO `" . $table . "` (`" . implode("`, `", $columns) . "`) VALUES " . PHP_EOL;

          while ($row = database::fetch($rows_query)) {

            foreach ($columns as $column) {
              if (!isset($row[$column])) {
                $row[$column] = "NULL, ";
              } elseif ($row[$column] != '') {
                $row[$column] = "'". addcslashes($row[$column], "\\'\r\n") ."'";
              } else {
                $row[$column] = "'', ";
              }
            }

            $output .= "(". implode(", ", $row) . ")," . PHP_EOL;
          }

          $output = rtrim($output, ", ") . ";" . PHP_EOL;

          fwrite($backup_handle, $output);
        }

        flock($backup_handle, LOCK_UN);
        fclose($backup_handle);

        echo '<span class="ok">[OK]</span> '. $backup_file .'</p>' . PHP_EOL . PHP_EOL;
      }

      ### App > Check Version ################################################

      echo '<p>Checking application database version... ';

      if (defined('PLATFORM_DATABASE_VERSION')) {
        echo PLATFORM_DATABASE_VERSION .' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
      } else if (!empty($_REQUEST['from_version'])) {
        define('PLATFORM_DATABASE_VERSION', $_REQUEST['from_version']);
        echo $_REQUEST['from_version'] . ' (User Defined) <span class="warning">[OK]</span></p>' . PHP_EOL . PHP_EOL;
      } else {
        throw new Exception(' <span class="error">[Undetected]</span></p>' . PHP_EOL . PHP_EOL);
      }

      ### Installer > Update ########################################

      if (!empty($_REQUEST['skip_updates'])) {
        echo '<p>Checking for updates... ';

        require_once FS_DIR_APP . 'includes/clients/http_client.inc.php';
        $client = new http_client();

        $update_file = function($file) use ($client) {
          $local_file = preg_replace('#^admin/#', BACKEND_ALIAS.'/', $file);
          $response = $client->call('GET', 'https://raw.githubusercontent.com/litecart/litecart/'. PLATFORM_VERSION .'/public_html/'. $file);
          if ($client->last_response['status_code'] != 200) return false;
          if (!is_dir(dirname(FS_DIR_APP . $local_file))) {
            mkdir(dirname(FS_DIR_APP . $local_file), 0777, true);
          }
          file_put_contents(FS_DIR_APP . $local_file, $response);
          return true;
        };

        $calculate_md5 = function($file) {
          $local_file = preg_replace('#^admin/#', BACKEND_ALIAS.'/', $file);
          if (!is_file(FS_DIR_APP . $local_file)) return;
          $contents = preg_replace('#(\r\n?|\n)#', "\n", file_get_contents(FS_DIR_APP . $local_file));
          return md5($contents);
        };

        if ($update_file('install/checksums.md5')) {

          $files_updated = 0;
          foreach (file(FS_DIR_APP . 'install/checksums.md5', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            list($checksum, $file) = explode("\t", $line);
            if ($calculate_md5($file) != $checksum) {
              if ($update_file($file)) $files_updated++;
            }
          }

          if (!empty($files_updated)) {
            echo 'Updated '. $files_updated .' file(s) <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
          }
        }
      }

      #############################################

      $current_version = PLATFORM_DATABASE_VERSION;

      foreach ($supported_versions as $version) {

        if (version_compare(PLATFORM_DATABASE_VERSION, $version, '>=')) {
          continue;
        }

        if (version_compare($current_version, '3.0.0', '>=')) {
          database::query('START TRANSACTION;');
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
        }

        if (file_exists(__DIR__ . '/upgrade_patches/'. $version .'.inc.php')) {
          echo '<p>Upgrading system to '. $version .'...</p>' . PHP_EOL . PHP_EOL;
          include(__DIR__ . '/upgrade_patches/'. $version .'.inc.php');
        }

        if (version_compare($current_version, '3.0.0', '>=')) {
          database::query('COMMIT;');
        }

        echo '<p>Set platform database version...';

        database::query(
          "update ". str_replace('`lc_', '`'.DB_TABLE_PREFIX, '`lc_settings`') ."
          set `value` = '". database::input($version) ."'
          where `key` = 'platform_database_version'
          limit 1;"
        );

        echo ' <strong>'. $version .'</strong></p>' . PHP_EOL . PHP_EOL;

        $current_version = $version;
      }

      #############################################

      echo '<p>Preparing CSS files...</p>' . PHP_EOL . PHP_EOL;

      perform_action('delete', [
        FS_DIR_APP . 'backend/template/less/',
      ]);

      if (!empty($_REQUEST['development_type']) && $_REQUEST['development_type'] == 'advanced') {

        file_put_contents(FS_DIR_APP . 'includes/templates/default.catalog/.development', 'advanced');

        perform_action('delete', [
          FS_DIR_APP . 'frontend/templates/*/css/app.css',
          FS_DIR_APP . 'frontend/templates/*/css/checkout.css',
          FS_DIR_APP . 'frontend/templates/*/css/framework.css',
          FS_DIR_APP . 'frontend/templates/*/css/printable.css',
          FS_DIR_APP . 'frontend/templates/*/js/app.js',
        ]);

      } else {

        file_put_contents(FS_DIR_APP . 'includes/templates/default.catalog/.development', 'standard');

        perform_action('delete', [
          FS_DIR_APP . 'frontend/templates/*/css/*.min.css',
          FS_DIR_APP . 'frontend/templates/*/css/*.min.css.map',
          FS_DIR_APP . 'frontend/templates/*/less/',
          FS_DIR_APP . 'frontened/templates/default.catalog/js/*.min.js.map',
          FS_DIR_APP . 'frontend/templates/default.catalog/less/',
        ]);

        perform_action('modify', [
          FS_DIR_APP . 'frontend/templates/*/layouts/*.inc.php' => [
            ['search' => 'app.min.css',       'replace' => 'app.css'],
            ['search' => 'checkout.min.css',  'replace' => 'checkout.css'],
            ['search' => 'framework.min.css', 'replace' => 'framework.css'],
            ['search' => 'printable.min.css', 'replace' => 'printable.css'],
            ['search' => 'app.min.js',        'replace' => 'app.js'],
          ],
        ]);
      }

      echo PHP_EOL;

      #############################################

      echo '<p>Reset error log... ';

      if (file_put_contents(FS_DIR_STORAGE . 'logs/errors.log', '') !== false) {
        echo ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
      } else {
        echo ' <span class="error">[Failed]</span></p>' . PHP_EOL . PHP_EOL;
      }

      #############################################

      echo '<p>Clear cache... ';

      database::query(
        "update ". DB_TABLE_PREFIX ."settings
        set value = '1'
        where `key` = 'cache_clear'
        limit 1;"
      );

      perform_action('delete', [
        FS_DIR_STORAGE . 'addons/.cache/*.php',
        FS_DIR_STORAGE . 'addons/.cache/.checked',
        FS_DIR_STORAGE . 'addons/.cache/.modifications',
      ]);

      echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

      ### Cleanup ##########################################

      if (!empty($_REQUEST['cleanup'])) {

        echo '<p>Cleanup... ';

        perform_action('delete', [
          FS_DIR_APP . 'install/',
        ]);

        echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
      }

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

    echo ob_get_clean();

    if (php_sapi_name() == 'cli') exit;

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
  <h1>Upgrade <?php echo PLATFORM_VERSION; ?></h1>

  <h2>Application</h2>

  <div class="row">
    <div class="form-group col-md-6">
      <label>MySQL/MariaDB Server</label>
      <div class="form-control">
        <?php echo DB_SERVER; ?>
      </div>
    </div>

    <div class="form-group col-md-6">
      <label>MySQL/MariaDB Database</label>
      <div class="form-control">
        <?php echo DB_DATABASE; ?>
      </div>
    </div>
  </div>

  <h3>Backup</h3>

  <div class="form-group">
    <label class="form-check">
      <input type="checkbox" name="backup" value="true" checked /> Backup my database before performing the upgrade.
    </label>
  </div>

  <div class="row">
  <?php if (defined('PLATFORM_DATABASE_VERSION')) { ?>
    <div class="form-group col-md-3">
      <label>Current Version</label>
      <div class="form-control"><?php echo PLATFORM_DATABASE_VERSION; ?></div>
    </div>
    <?php } else { ?>
    <div class="form-group col-md-3">
      <label>Select the <?php echo PLATFORM_NAME; ?> version you are upgrading from:</label>
      <select class="form-control" name="from_version">
        <option value="">-- Select Version --</option>
        <?php foreach ($supported_versions as $version) echo '<option value="'. $version .'"'. ((isset($_REQUEST['from_version']) && $_REQUEST['from_version'] == $version) ? 'selected' : '') .'>'. PLATFORM_NAME .' '. $version .'</option>' . PHP_EOL; ?>
      </select>
    </div>
    <?php } ?>

    <div class="form-group col-md-3">
      <label>New Version</label>
      <div class="form-control"><?php echo PLATFORM_VERSION; ?></div>
  </div>

    <div class="form-group col-md-6">
      <label style="margin-top: 2.25em;">
        <input type="checkbox" class="form-check" name="skip_updates" value="0" /> Skip downloading the latest updates
      </label>
    </div>
  </div>

  <h2>Development</h2>

  <div class="form-group" style="display: flex;">
    <label>
      <input name="development_type" value="standard" type="radio" checked />
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
          (Requires a <a href="https://www.litecart.net/addons/163/developer-kit" target="_blank">LESS compiler</a>)
        </small>
      </div>
    </label>
  </div>

  <button class="btn btn-success btn-block" type="submit" name="upgrade" value="true" onclick="if(!confirm('Warning! The procedure cannot be undone.')) return false;" style="font-size: 1.5em; padding: 0.5em;" />Upgrade To <?php echo PLATFORM_NAME; ?> <?php echo PLATFORM_VERSION; ?></button>
</form>
<?php
  require('includes/footer.inc.php');
