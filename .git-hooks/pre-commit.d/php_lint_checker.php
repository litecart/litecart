<?php

  echo PHP_EOL
     . PHP_EOL
     . 'Processing PHP lint checker' . PHP_EOL;

  $tracked_files = preg_split('#(\r\n?|\n)#', shell_exec('git ls-files'), -1, PREG_SPLIT_NO_EMPTY);
  $committed_files = preg_split('#(\r\n?|\n)#', shell_exec('git diff --cached --name-only 2>&1'), -1, PREG_SPLIT_NO_EMPTY);

  foreach ($committed_files as $file) {
    if (!in_array($file, $tracked_files)) continue;
    if (!preg_match('#\.php$#', $file)) continue;

      $tmp_file = tempnam(sys_get_temp_dir(), '_blob');
      shell_exec('git cat-file blob :'. $file .' > '. $tmp_file);
      $blob = file_get_contents($tmp_file);

      system('php -l '. $file, $result_code);

      unlink($tmp_file);

    if (!empty($result_code)) exit($result_code);
  }
