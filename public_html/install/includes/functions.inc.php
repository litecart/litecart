<?php

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
          echo 'Copying '. preg_replace('#^('. FS_DIR_APP .')#', '', $source) .' to '. preg_replace('#^('. FS_DIR_APP .')#', '', $target);

          if (file_copy($source, $target, $results)) {
            echo ' <span class="ok">[OK]</span><br /><br />' . PHP_EOL . PHP_EOL;
          } else if ($on_error == 'skip') {
            echo ' <span class="warning">[Skipped]</span><br /><br />' . PHP_EOL . PHP_EOL;
          } else {
            die(' <span class="error">[Error]</span><br /><br />' . PHP_EOL . PHP_EOL);
          }
        }

        break;

      case 'delete':

        foreach ($payload as $source => $target) {
          echo 'Deleting '. preg_replace('#^('. FS_DIR_APP .')#', '', $source);

          if (file_delete($file, $results)) {
            echo ' <span class="ok">[OK]</span><br /><br />' . PHP_EOL . PHP_EOL;
          } else if ($on_error == 'skip') {
            echo ' <span class="warning">[Skipped]</span><br /><br />' . PHP_EOL . PHP_EOL;
          } else {
            die(' <span class="error">[Error]</span><br /><br />' . PHP_EOL . PHP_EOL);
          }
        }

        break;

      case 'move':
      case 'rename':

        foreach ($payload as $source => $target) {
          echo 'Moving '. preg_replace('#^('. FS_DIR_APP .')#', '', $source) .' to '. preg_replace('#^('. FS_DIR_APP .')#', '', $target);

          if (file_move($source, $target, $results)) {
            echo ' <span class="ok">[OK]</span><br /><br />' . PHP_EOL . PHP_EOL;
          } else if ($on_error == 'skip') {
            echo ' <span class="warning">[Skipped]</span><br /><br />' . PHP_EOL . PHP_EOL;
          } else {
            die(' <span class="error">[Error]</span><br /><br />' . PHP_EOL . PHP_EOL);
          }
        }

        break;

      case 'modify':

        foreach ($payload as $source => $operations) {

          if (!$files = file_search($source)) {
            if ($on_error == 'skip') continue;
            die("<span class=\"error\">[Error] Could not modify $source</span>");
          }

          foreach ($files as $file) {

            echo 'Modifying ' . preg_replace('#^('. FS_DIR_APP .')#', '', $file);

            $contents = file_get_contents($file);
            $contents = preg_replace('#(\r\n?|\n)#u', PHP_EOL, $contents);

            foreach ($operations as $operation) {
              if (!empty($operations['regex'])) {
                $contents = preg_replace($operation['search'], $operation['replace'], $contents);
              } else {
                $contents = str_replace($operation['search'], $operation['replace'], $contents);
              }
            }

            if (file_put_contents($file, $contents)) {
              echo ' <span class="ok">[OK]</span><br /><br />' . PHP_EOL . PHP_EOL;
            } else if ($on_error == 'skip') {
              echo ' <span class="warning">[Skipped]</span><br /><br />' . PHP_EOL . PHP_EOL;
            } else {
              die(' <span class="error">[Error]</span><br /><br />' . PHP_EOL . PHP_EOL);
            }
          }
        }

        break;

      default:
        trigger_error("Unknown action ($action)", E_USER_ERROR);

    }
  }
