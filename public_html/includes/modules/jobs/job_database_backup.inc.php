<?php

  class job_database_backup {
    public $id = __CLASS__;
    public $name = 'Database Backup';
    public $description = '';
    public $author = 'TiM International';
    public $version = '1.0';
    public $support_link = 'http://www.google.com/finance/converter';
    public $website = 'http://www.tim-international.net';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = $system;
    }
    
    public function process() {
      
      if ($this->settings['status'] != 'Enabled') return;
      
      if (empty($this->settings['backup_directory'])) return;
      
      if (empty($this->settings['backup_filename'])) return;
      
      switch ($this->settings['backup_frequency']) {
        case 'Hourly':
          if (strtotime($this->system->settings->get('database_backups_last_run')) > strtotime('-1 hour')) return; 
          break;
        case 'Daily':
          if (strtotime($this->system->settings->get('database_backups_last_run')) > strtotime('-1 day')) return; 
          break;
        case 'Weekly':
          if (strtotime($this->system->settings->get('database_backups_last_run')) > strtotime('-1 week')) return; 
          break;
        case 'Monthly':
          if (strtotime($this->system->settings->get('database_backups_last_run')) > strtotime('-1 month')) return; 
          break;
        default:
          return;
      }
      
      $this->backup();
      
      $this->cleanup();
      
      $this->system->database->query(
        "update ". DB_TABLE_SETTINGS ."
        set value = '". date('Y-m-d H:i:s') . "'
        where `key` = 'database_backups_last_run'
        limit 1;"
      );
    }
    
    function backup() {
      
      if (!file_exists(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $this->settings['backup_directory'])) mkdir($this->settings['backup_directory']);
      
      $backup_file = FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $this->settings['backup_directory'] . strftime($this->settings['backup_filename']);
      
      $fp = fopen($backup_file, 'w');

      $tables_query = $this->system->database->query('show tables');
      while ($tables = $this->system->database->fetch($tables_query)) {
        list(, $table) = each($tables);

        $output = PHP_EOL . 'drop table if exists ' . $table . ';' . PHP_EOL
                . 'create table ' . $table . ' (' . PHP_EOL;

        $table_list = array();
        $fields_query = $this->system->database->query("show fields from " . $table);
        while ($fields = $this->system->database->fetch($fields_query)) {
          $table_list[] = $fields['Field'];

          $output .= '  `' . $fields['Field'] . '` ' . $fields['Type'];

          if (strlen($fields['Default']) > 0) $output .= ' default \'' . $fields['Default'] . '\'';

          if ($fields['Null'] != 'YES') $output .= ' not null';

          if (isset($fields['Extra'])) $output .= ' ' . $fields['Extra'];

          $output .= ',' . PHP_EOL;
        }

        $output = preg_replace("/,".PHP_EOL."$/", '', $output);
        
        $index = array();
        $keys_query = $this->system->database->query("show keys from " . $table);
        while ($keys = $this->system->database->fetch($keys_query)) {
          $kname = $keys['Key_name'];

          if (!isset($index[$kname])) {
            $index[$kname] = array('unique' => !$keys['Non_unique'],
                                   'fulltext' => ($keys['Index_type'] == 'FULLTEXT' ? '1' : '0'),
                                   'columns' => array());
          }

          $index[$kname]['columns'][] = $keys['Column_name'];
        }

        while (list($kname, $info) = each($index)) {
          $output .= ',' . PHP_EOL;

          $columns = implode($info['columns'], ', ');

          if ($kname == 'PRIMARY') {
            $output .= '  PRIMARY KEY (' . $columns . ')';
          } elseif ( $info['fulltext'] == '1' ) {
            $output .= '  FULLTEXT ' . $kname . ' (' . $columns . ')';
          } elseif ($info['unique']) {
            $output .= '  UNIQUE ' . $kname . ' (' . $columns . ')';
          } else {
            $output .= '  KEY ' . $kname . ' (' . $columns . ')';
          }
        }

        $output .= PHP_EOL . ');' . PHP_EOL . PHP_EOL;
        fputs($fp, $output);
        
        if (!in_array($table, explode(',', $this->settings['ignore_tables']))) {
          $rows_query = $this->system->database->query("select `" . implode('`, `', $table_list) . "` from " . $table);
          while ($rows = $this->system->database->fetch($rows_query)) {
            $output = 'insert into ' . $table . ' (`' . implode('`, `', $table_list) . '`) values (';

            reset($table_list);
            while (list(,$i) = each($table_list)) {
              if (!isset($rows[$i])) {
                $output .= 'NULL, ';
              } elseif (!empty($rows[$i])) {
                $row = addslashes($rows[$i]);
                $row = preg_replace('/'.PHP_EOL."#/", PHP_EOL.'\#', $row);

                $output .= '\'' . $row . '\', ';
              } else {
                $output .= '\'\', ';
              }
            }

            $output = preg_replace('/, $/', '', $output) . ');' . PHP_EOL;
            fputs($fp, $output);
          }
        }
      }

      fclose($fp);
    }
    
    function cleanup() {
    
      foreach (glob(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $this->settings['backup_directory'] . '*.sql') as $backup) {
      
        if (file_exists($backup)) {
          if (filemtime($backup) < strtotime('-'. $this->settings['expire_days'] .' days')) {
            unlink($backup);
          }
        } else {
          error_log('Backup job failed deleting an old backup. File does not exist: '. $backup);
        }
      }
    }
    
    function settings() {
      
      return array(
        array(
          'key' => 'status',
          'default_value' => 'Enabled',
          'title' => $this->system->language->translate(__CLASS__.':title_status', 'Status'),
          'description' => $this->system->language->translate(__CLASS__.':description_status', 'Enables or disables the module.'),
          'function' => 'radio("Enabled", "Disabled")',
        ),
        array(
          'key' => 'backup_directory',
          'default_value' => 'data/backups/',
          'title' => $this->system->language->translate(__CLASS__.':title_backup_directory', 'Backup Directory'),
          'description' => $this->system->language->translate(__CLASS__.':description_backup_directory', 'Path to the directory where the backups are stored with trailing slash. Relative to the system base path.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'backup_filename',
          'default_value' => 'db-%y%m%d.sql',
          'title' => $this->system->language->translate(__CLASS__.':title_backup_filename', 'Backup Filename'),
          'description' => $this->system->language->translate(__CLASS__.':description_backup_filename', 'The strftime() supported filename of the backup.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'backup_frequency',
          'default_value' => 'Daily',
          'title' => $this->system->language->translate(__CLASS__.':title_backup_frequency', 'Backup Frequency'),
          'description' => $this->system->language->translate(__CLASS__.':description_backup_frequency', 'How often the database should be backed up.'),
          'function' => 'radio("Hourly","Daily","Weekly","Monthly")',
        ),
        array(
          'key' => 'expire_days',
          'default_value' => '10',
          'title' => $this->system->language->translate(__CLASS__.':title_expire_days', 'Expire Days'),
          'description' => $this->system->language->translate(__CLASS__.':description_expire_days', 'Delete backups older than the given amount of days.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'ignore_tables',
          'default_value' => DB_TABLE_PREFIX . 'seo_links_cache',
          'title' => $this->system->language->translate(__CLASS__.':title_ignore_tables', 'Ignore Tables'),
          'description' => $this->system->language->translate(__CLASS__.':description_ignore_tables', 'A coma separated list of tables NOT to backup content data from.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'priority',
          'default_value' => '0',
          'title' => $this->system->language->translate(__CLASS__.':title_priority', 'Priority'),
          'description' => $this->system->language->translate(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
          'function' => 'int()',
        ),
      );
    }
    
    public function install() {
      $this->system->database->query(
        "insert into ". DB_TABLE_SETTINGS ."
        (title, description, `key`, value, date_created, date_updated)
        values ('Databas Backups Last Run', 'Time when database backups where last made by the background job.', 'database_backups_last_run', '', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
      );
    }
    
    public function uninstall() {
      $this->system->database->query(
        "delete from ". DB_TABLE_SETTINGS ."
        where `key` = 'database_backups_last_run'
        limit 1;"
      );
    }
  }
  
?>