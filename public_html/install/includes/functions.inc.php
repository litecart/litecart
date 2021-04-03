<?php

  function perform_action($action, $payload, $on_error='skip') {

    switch ($action) {

      case 'copy':

        foreach ($payload as $source => $target) {

          if (!$files = functions::file_search($source)) {
            if ($on_error == 'skip') continue;
            die("<span class=\"error\">[Error] Could not copy $source</span>" . PHP_EOL . PHP_EOL);
          }

          foreach ($files as $file) {

            echo 'Writing ' . preg_replace('^('. FS_DIR_APP .')', '', $target);

            if (is_dir($file)) {
              if (!is_dir($target)) mkdir($target);
              perform_action($action, [[rtrim($file, '/') . '/*' => rtrim($target, '/') . '/' . basename($file) .'/']], $on_error);
            }

            if (functions::file_copy($source, is_dir($target) ? $target . pathinfo($source, PATHINFO_BASENAME) : $target)) {
              echo ' <span class="ok">[OK]</span><br /><br />' . PHP_EOL . PHP_EOL;
            } else if ($on_error == 'skip') {
              echo ' <span class="warning">[Skipped]</span><br /><br />' . PHP_EOL . PHP_EOL;
            } else {
              die(' <span class="error">[Error]</span><br /><br />' . PHP_EOL . PHP_EOL);
            }
          }
        }

        break;

      case 'delete':

        foreach ($payload as $source) {
          foreach (functions::file_search($source) as $file) {

            echo 'Delete ' . preg_replace('^('. FS_DIR_APP .')', '', $file);

            if (functions::file_delete($file)) {
              echo ' <span class="ok">[OK]</span><br /><br />' . PHP_EOL . PHP_EOL;
            } else if ($on_error == 'skip') {
              echo ' <span class="warning">[Skipped]</span><br /><br />' . PHP_EOL . PHP_EOL;
            } else {
              die(' <span class="error">[Error]</span><br /><br />' . PHP_EOL . PHP_EOL);
            }
          }
        }

        break;

      case 'move':
      case 'rename':

        foreach ($payload as $source => $target) {

          if (!$files = functions::file_search($source)) {
            if ($on_error == 'skip') continue;
            die("<span class=\"error\">[Error] Could not move $source</span>" . PHP_EOL . PHP_EOL);
          }

          foreach ($files as $file) {
            echo 'Move '. preg_replace('^('. FS_DIR_APP .')', '', $source) ." to ". preg_replace('^('. FS_DIR_APP .')', '', $target);

            if (rename($source, is_dir($target) ? $target . pathinfo($source, PATHINFO_BASENAME) : $target)) {
              echo ' <span class="ok">[OK]</span><br /><br />' . PHP_EOL . PHP_EOL;
            } else if ($on_error == 'skip') {
              echo ' <span class="warning">[Skipped]</span><br /><br />' . PHP_EOL . PHP_EOL;
            } else {
              die(' <span class="error">[Error]</span><br /><br />' . PHP_EOL . PHP_EOL);
            }
          }
        }

        break;

      case 'modify':

        foreach ($payload as $source => $operations) {

          if (!$files = functions::file_search($source)) {
            if ($on_error == 'skip') continue;
            die("<span class=\"error\">[Error] Could not modify $source</span>");
          }

          foreach ($files as $file) {

            echo 'Modify ' . preg_replace('^('. FS_DIR_APP .')', '', $file);

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
