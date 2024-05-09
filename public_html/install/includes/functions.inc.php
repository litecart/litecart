<?php

  include __DIR__.'/../../includes/functions/func_file.inc.php';

  function return_bytes($string) {
    sscanf($string, '%u%c', $number, $suffix);
    if (isset($suffix)) {
      $number = $number * pow(1024, strpos(' KMG', strtoupper($suffix)));
    }
    return $number;
  }

  function perform_action($action, $payload, $on_error='skip') {

    switch ($action) {

      case 'copy':

        foreach ($payload as $source => $target) {

          if (defined('DISABLE_FILE_MIGRATIONS') && filter_var(DISABLE_FILE_MIGRATIONS, FILTER_VALIDATE_BOOLEAN)) {
            if (!preg_match('#^'. preg_quote(FS_DIR_STORAGE, '#') .'#', $target)) continue;
          }

          echo 'Copying '. preg_replace('#^('. preg_quote(FS_DIR_STORAGE, '#') .'|'. preg_quote(FS_DIR_APP, '#') .')#', '', $source) .' to '. preg_replace('#^('. preg_quote(FS_DIR_STORAGE, '#') .'|'. preg_quote(FS_DIR_APP, '#') .')#', '', $target);

          if (file_xcopy($source, $target, $results)) {
            echo ' <span class="ok">[OK]</span><br><br>' . PHP_EOL . PHP_EOL;
          } else if ($on_error == 'skip') {
            echo ' <span class="warning">[Skipped]</span><br><br>' . PHP_EOL . PHP_EOL;
          } else {
            die(' <span class="error">[Error]</span><br><br>' . PHP_EOL . PHP_EOL);
          }
        }

        break;

      case 'custom':

        foreach ($payload as $source => $operations) {

          if (defined('DISABLE_FILE_MIGRATIONS') && filter_var(DISABLE_FILE_MIGRATIONS, FILTER_VALIDATE_BOOLEAN)) {
            if (!preg_match('#^'. preg_quote(FS_DIR_STORAGE, '#') .'#', $source)) continue;
          }

          echo 'Performing custom actions on ' . preg_replace('#^('. preg_quote(FS_DIR_STORAGE, '#') .'|'. preg_quote(FS_DIR_APP, '#') .')#', '', $source);

          $results = [];

          if (!$files = file_search($source)) {
            $results[] = false;
          }

          foreach ($files as $file) {

            foreach ($operations as $operation) {
              $results[] = $operation($file);
            }

            $results[] = file_put_contents($file, $contents);
          }

          if (!in_array(false, $results)) {
            echo ' <span class="ok">[OK]</span><br><br>' . PHP_EOL . PHP_EOL;
          } else if ($on_error == 'skip') {
            echo ' <span class="warning">[Skipped]</span><br><br>' . PHP_EOL . PHP_EOL;
          } else {
            die(' <span class="error">[Error]</span><br><br>' . PHP_EOL . PHP_EOL);
          }
        }

        break;

      case 'delete':

        foreach ($payload as $source) {

          if (defined('DISABLE_FILE_MIGRATIONS') && filter_var(DISABLE_FILE_MIGRATIONS, FILTER_VALIDATE_BOOLEAN)) {
            if (!preg_match('#^'. preg_quote(FS_DIR_STORAGE, '#') .'#', $source)) continue;
          }

          echo 'Deleting '. preg_replace('#^('. preg_quote(FS_DIR_STORAGE, '#') .'|'. preg_quote(FS_DIR_APP, '#') .')#', '', $source);

          if (file_delete($source, $results)) {
            echo ' <span class="ok">[OK]</span><br><br>' . PHP_EOL . PHP_EOL;
          } else if ($on_error == 'skip') {
            echo ' <span class="warning">[Skipped]</span><br><br>' . PHP_EOL . PHP_EOL;
          } else {
            die(' <span class="error">[Error]</span><br><br>' . PHP_EOL . PHP_EOL);
          }
        }

        break;

      case 'move':
      case 'rename':

        foreach ($payload as $source => $target) {

          if (defined('DISABLE_FILE_MIGRATIONS') && filter_var(DISABLE_FILE_MIGRATIONS, FILTER_VALIDATE_BOOLEAN)) {
            if (!preg_match('#^'. preg_quote(FS_DIR_STORAGE, '#') .'#', $source)) continue;
          }

          echo 'Moving '. preg_replace('#^('. preg_quote(FS_DIR_STORAGE, '#') .'|'. preg_quote(FS_DIR_APP, '#') .')#', '', $source) .' to '. preg_replace('#^('. preg_quote(FS_DIR_STORAGE, '#') .'|'. preg_quote(FS_DIR_APP, '#') .')#', '', $target);

          if (file_move($source, $target, $results)) {
            echo ' <span class="ok">[OK]</span><br><br>' . PHP_EOL . PHP_EOL;
          } else if ($on_error == 'skip') {
            echo ' <span class="warning">[Skipped]</span><br><br>' . PHP_EOL . PHP_EOL;
          } else {
            die(' <span class="error">[Error]</span><br><br>' . PHP_EOL . PHP_EOL);
          }
        }

        break;

      case 'modify':

        foreach ($payload as $source => $operations) {

          if (defined('DISABLE_FILE_MIGRATIONS') && filter_var(DISABLE_FILE_MIGRATIONS, FILTER_VALIDATE_BOOLEAN)) {
            if (!preg_match('#^'. preg_quote(FS_DIR_STORAGE, '#') .'#', $source)) continue;
          }

          echo 'Modifying ' . preg_replace('#^('. preg_quote(FS_DIR_STORAGE, '#') .'|'. preg_quote(FS_DIR_APP, '#') .')#', '', $source);

          $results = [];

          if (!$files = file_search($source)) {
            $results[] = false;
          }

          foreach ($files as $file) {

            $contents = file_get_contents($file);
            $contents = preg_replace('#(\r\n?|\n)#u', PHP_EOL, $contents);

            foreach ($operations as $operation) {

              if (!empty($operations['regex'])) {
                $contents = preg_replace($operation['search'], $operation['replace'], $contents, -1, $count);
              } else {
                $contents = str_replace($operation['search'], $operation['replace'], $contents, $count);
              }

              $results[] = $count ? true : false;
            }

            $results[] = file_put_contents($file, $contents);
          }

          if (!in_array(false, $results)) {
            echo ' <span class="ok">[OK]</span><br><br>' . PHP_EOL . PHP_EOL;
          } else if ($on_error == 'skip') {
            echo ' <span class="warning">[Skipped]</span><br><br>' . PHP_EOL . PHP_EOL;
          } else {
            die(' <span class="error">[Error]</span><br><br>' . PHP_EOL . PHP_EOL);
          }
        }

        break;

      default:
        trigger_error("Unknown action ($action)", E_USER_ERROR);

    }
  }
