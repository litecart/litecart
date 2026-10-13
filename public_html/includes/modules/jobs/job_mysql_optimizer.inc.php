<?php

  #[AllowDynamicProperties]
  class job_mysql_optimizer {
    public $id = __CLASS__;
    public $name = 'MySQL Optimizer';
    public $description = 'Defragment your MySQL database';
    public $author = 'LiteCart Dev Team';
    public $version = '1.0';
    public $website = 'https://www.litecart.net';
    public $priority = 0;

    public function process($force, $last_run) {

      if (empty($force)) {
        if (empty($this->settings['status'])) return;

        if (!empty($last_run) && ($last_run = strtotime($last_run)) !== false) {
          switch ($this->settings['frequency']) {

            case 'Daily':
              if (date('Ymd', $last_run) == date('Ymd')) return;
              break;

            case 'Weekly':
              if (date('oW', $last_run) == date('oW')) return;
              break;

            case 'Monthly':
            default:
              if (date('Ym', $last_run) == date('Ym')) return;
              break;
          }
        }
      }

      // Rebuilding a large table can take longer than max_execution_time
      if (function_exists('set_time_limit')) {
      	@set_time_limit(0);
     	}

      $min_free_bytes = max(0, (float)($this->settings['min_free_mb'])) * 1048576;
      $min_free_percent = max(0, (float)($this->settings['min_free_percent']));

      echo 'Optimizing MySQL Tables...' . PHP_EOL . PHP_EOL;

      $optimized = 0;
      $skipped = 0;
      $largest_skipped = ['name' => '', 'free' => 0];

      database::query(
        "select TABLE_NAME, ENGINE, DATA_LENGTH, INDEX_LENGTH, DATA_FREE
        from `information_schema`.`TABLES`
        where TABLE_SCHEMA = '". DB_DATABASE ."'
        and TABLE_TYPE = 'BASE TABLE'
        and TABLE_NAME like '". DB_TABLE_PREFIX ."%'
        order by TABLE_NAME;"
      )->each(function($table) use ($min_free_bytes, $min_free_percent, &$optimized, &$skipped, &$largest_skipped) {

        // Match the prefix in PHP - in a LIKE pattern the '_' in 'lc_' would match any character
        if (substr($table['TABLE_NAME'], 0, strlen(DB_TABLE_PREFIX)) != DB_TABLE_PREFIX) return;

        $size = (int)$table['DATA_LENGTH'] + (int)$table['INDEX_LENGTH'];
        $free = (int)$table['DATA_FREE'];
        $percent = $size ? round($free / $size * 100, 1) : 0;

        // Aria (MariaDB) never reports its unused space - DATA_FREE stays 0 however fragmented the table is - so Aria tables are always optimized on the scheduled run
        $has_free_space_statistics = (strtolower((string)$table['ENGINE']) != 'aria');

        // Only rebuild tables that actually have unused (fragmented) space
        if ($has_free_space_statistics && ($free < $min_free_bytes || $percent < $min_free_percent)) {
          $skipped++;
          if ($free > $largest_skipped['free']) {
            $largest_skipped = ['name' => $table['TABLE_NAME'], 'free' => $free];
          }
          return;
        }

        echo '  '. $table['TABLE_NAME'] .' ('. $table['ENGINE'] .', '. self::format_bytes($size) .', '. ($has_free_space_statistics ? self::format_bytes($free) .' unused' : 'unused space not reported') .')... ';

        $status = '';
        $messages = [];

        database::query(
          "optimize table `". $table['TABLE_NAME'] ."`;"
        )->each(function($row) use (&$status, &$messages) {
          switch (strtolower($row['Msg_type'])) {
            case 'status':
              $status = $row['Msg_text'];
              break;
            case 'error':
            case 'warning':
              $messages[] = ucfirst($row['Msg_type']) .': '. $row['Msg_text'];
              break;
            // 'note' and 'info' are ignored, e.g. InnoDB's "Table does not support optimize, doing recreate + analyze instead"
          }
        });

        echo ($status != '' ? $status : 'Done') . PHP_EOL;

        foreach ($messages as $message) {
          echo '    '. $message . PHP_EOL;
        }

        $optimized++;
      });

      if (!$optimized) {
        echo '  No tables above the threshold ('. self::format_bytes($min_free_bytes) .' and '. $min_free_percent .'% unused space)';
        if ($largest_skipped['name'] != '') {
          echo ' - most unused space: '. $largest_skipped['name'] .' ('. self::format_bytes($largest_skipped['free']) .')';
        }
        echo PHP_EOL;
      }

      echo PHP_EOL . 'Done! '. $optimized .' table(s) optimized, '. $skipped .' skipped.';
    }

    private static function format_bytes($bytes) {
      if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) .' GB';
      if ($bytes >= 1048576) return round($bytes / 1048576, 1) .' MB';
      if ($bytes >= 1024) return round($bytes / 1024) .' kB';
      return (int)$bytes .' B';
    }

    function settings() {

      return [
        [
          'key' => 'status',
          'default_value' => '1',
          'title' => language::translate(__CLASS__.':title_status', 'Status'),
          'description' => language::translate(__CLASS__.':description_status', 'Enables or disables the module.'),
          'function' => 'toggle("e/d")',
        ],
        [
          'key' => 'frequency',
          'default_value' => 'Monthly',
          'title' => language::translate(__CLASS__.':title_frequency', 'Frequency'),
          'description' => language::translate(__CLASS__.':description_frequency', 'How often the job should be processed.'),
          'function' => 'radio("Daily","Weekly","Monthly")',
        ],
        [
          'key' => 'min_free_mb',
          'default_value' => '10',
          'title' => language::translate(__CLASS__.':title_min_free_mb', 'Minimum unused space (MB)'),
          'description' => language::translate(__CLASS__.':description_min_free_mb', 'Only optimize tables with at least this much unused (fragmented) space. 0 optimizes all tables. Aria tables do not report unused space and are always optimized.'),
          'function' => 'number()',
        ],
        [
          'key' => 'min_free_percent',
          'default_value' => '10',
          'title' => language::translate(__CLASS__.':title_min_free_percent', 'Minimum unused space (%)'),
          'description' => language::translate(__CLASS__.':description_min_free_percent', 'Only optimize tables where the unused space is at least this share of the table size. 0 disables this check.'),
          'function' => 'number()',
        ],
        [
          'key' => 'priority',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_priority', 'Priority'),
          'description' => language::translate(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
          'function' => 'number()',
        ],
      ];
    }
  }
