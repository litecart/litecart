<?php

  ### Backup > Database ##################################################

  if (isset($_REQUEST['backup']) && !preg_match('#^(0|false|no|off)$#i', $_REQUEST['backup'])) {

    try {

      echo '<p>Backing up the database... ';

      if (!file_exists(FS_DIR_STORAGE . 'backups/')) {
        if (!mkdir(FS_DIR_STORAGE . 'backups/', 0777)) {
          throw new Exception('Could not create folder for backups');
        }
      }


      $backup_file = FS_DIR_STORAGE . 'backups/'. PLATFORM_NAME . '-' . PLATFORM_DATABASE_VERSION . '-Database-'. date('Ymd-Hi') .'.sql';

      if (!$backup_handle = fopen($backup_file, 'wb')) {
        throw new Exception("Cannot open backup file for writing ($backup_file)");
      }

      if (!flock($backup_handle, LOCK_EX)) {
        throw new Exception("Could not acquire an exclusive lock for writing to file ($backup_file)");
      }

      $separator = '-- -----';
      $use_separator = false;

      database::query('SHOW TABLES')->each(function($table) use ($backup_handle, $separator, &$use_separator) {

        $table = array_shift($table);

        if (!preg_match('#^'. preg_quote(DB_TABLE_PREFIX, '#') .'#', $table)) {
          return;
        }

        // Drop Table
        $output = (!empty($use_separator) ? $separator . PHP_EOL : '')
                . "DROP TABLE IF EXISTS `" . $table . "`;" . PHP_EOL;

        $use_separator = true;

        // Create Table
        $query = database::query("SHOW CREATE TABLE `" . $table . "`;");
        if ($row = database::fetch($query)) {
          $output .= $separator . PHP_EOL
                   . $row['Create Table'] . ';' . PHP_EOL;
        }

        fwrite($backup_handle, $output);

        if (!empty($ignore_tables) && in_array($table, $ignore_tables)) {
          return;
        }

        // Insert Data
        $columns = database::query(
          "SHOW COLUMNS FROM `" . $table ."`"
        )->fetch_all('Field');

        $rows = database::query(
          "SELECT `" . implode('`, `', $columns) . "` FROM `" . $table ."`"
        )->fetch_all();

        if (!$rows) {
          return;
        }

        $output = $separator . PHP_EOL
                . "INSERT INTO `" . $table . "` (`" . implode("`, `", $columns) . "`) VALUES " . PHP_EOL;

        foreach ($rows as $row) {
          foreach ($columns as $column) {
            if (!isset($row[$column])) {
              $row[$column] = 'NULL';
            } elseif ($row[$column] != '') {
              $row[$column] = "'". addcslashes($row[$column], "\\'\r\n") ."'";
            } else {
              $row[$column] = "''";
            }
          }

          $output .= "(". implode(", ", $row) . ")," . PHP_EOL;
        }

        $output = rtrim($output, ", \r\n") . ";" . PHP_EOL;

        fwrite($backup_handle, $output);
      });

      flock($backup_handle, LOCK_UN);
      fclose($backup_handle);

      echo '<span class="ok">[OK]</span> '. $backup_file .'</p>' . PHP_EOL . PHP_EOL;

    } catch (Exception $e) {
      echo implode(PHP_EOL, [
        '<span class="error">[Error]</span>',
        '<div class="error-message">'. $e->getMessage() .'</div></p>',
        '',
        '',
      ]);
    }
  }
