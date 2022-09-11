<?php

  echo PHP_EOL
     . PHP_EOL
     . 'Processing PHP lint checker' . PHP_EOL;

  $tracked_files = preg_split('#(\r\n?|\n)#', shell_exec('git ls-files'), -1, PREG_SPLIT_NO_EMPTY);
  $commmitted_files = preg_split('#(\r\n?|\n)#', shell_exec('git diff --cached --name-only 2>&1'), -1, PREG_SPLIT_NO_EMPTY);

  foreach ($commmitted_files as $file) {
    if (!in_array($file, $tracked_files)) continue;
    if (!preg_match('#\.php$#', $file)) continue;
    system('php -l '. $file, $result_code);
    if (!empty($result_code)) exit($result_code);
  }
