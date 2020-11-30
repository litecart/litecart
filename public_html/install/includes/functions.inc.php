<?php

  function perform_action($action, $payload, $on_error='skip') {

    switch ($action) {

      case 'move':
      case 'rename':

        foreach ($payload as $source => $target) {

          if (!$files = functions::file_search($source)) {
            if ($on_error == 'skip') continue;
            die("Could not rename $source");
          }

          foreach ($files as $file) {
            echo "Moving $source to $target";

            if (rename($source, is_dir($target) ? $target . pathinfo($source, PATHINFO_BASENAME) : $target)) {
              echo ' <span class="ok">[OK]</span>' . PHP_EOL;
            } else if ($on_error == 'skip') {
              echo ' <span class="warning">[Skipped]</span>' . PHP_EOL;
            } else {
              die(' <span class="error">[Error]</span>');
            }
          }
        }

        break;

      case 'copy':

        foreach ($payload as $source => $target) {

          if (!$files = functions::file_search($source)) {
            if ($on_error == 'skip') continue;
            die("Could not copy $source");
          }

          foreach ($files as $file) {

            echo "Writing $target";

            if (is_dir($source) && !is_dir($target)) mkdir($target);

            if (copy($source, is_dir($target) ? $target . pathinfo($source, PATHINFO_BASENAME) : $target)) {
              echo ' <span class="ok">[OK]</span>' . PHP_EOL;
            } else if ($on_error == 'skip') {
              echo ' <span class="warning">[Skipped]</span>' . PHP_EOL;
            } else {
              die(' <span class="error">[Error]</span>');
            }
          }
        }

        break;

      case 'modify':

        foreach ($payload as $source => $operations) {

          if (!$files = functions::file_search($source)) {
            if ($on_error == 'skip') continue;
            die("Could not modify $source");
          }

          foreach ($files as $file) {

            echo "Modifying $source";

            $contents = file_get_contents($file);
            $contents = preg_replace('#(\r\n?|\n)#u', PHP_EOL, $contents);

            if (!empty($operations['regex'])) {
              $contents = preg_replace($operations['search'], $operations['replace'], $contents);
            } else {
              $contents = str_replace($operations['search'], $operations['replace'], $contents);
            }

            if (file_put_contents($file, $contents)) {
              echo ' <span class="ok">[OK]</span>' . PHP_EOL;
            } else if ($on_error == 'skip') {
              echo ' <span class="warning">[Skipped]</span>' . PHP_EOL;
            } else {
              die(' <span class="error">[Error]</span>');
            }
          }
        }

        break;

      case 'delete':

        foreach ($payload as $source) {
          foreach (functions::file_search($source) as $file) {

            if (is_dir($file)) {
              perform_action($action, [$key => rtrim($file, '/') . '/*'], $on_error);
            }

            echo "Delete $file";

            if (unlink($file)) {
              echo ' <span class="ok">[OK]</span>' . PHP_EOL;
            } else if ($on_error == 'skip') {
              echo ' <span class="warning">[Skipped]</span>' . PHP_EOL;
            } else {
              die(' <span class="error">[Error]</span>');
            }
          }
        }

        break;

      default:
        throw new Exception("Unknown action ($action)");

    }
  }
