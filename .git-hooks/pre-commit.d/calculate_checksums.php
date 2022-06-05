<?php

  $checksums_file = 'public_html/install/checksums.md5';
  $tracked_files = preg_split('#(\r\n?|\n)#', shell_exec('git ls-files'), -1, PREG_SPLIT_NO_EMPTY);
  $committed_files = preg_split('#(\r\n?|\n)#', shell_exec('git diff --cached --name-only 2>&1'), -1, PREG_SPLIT_NO_EMPTY);

// Create list of files for checksums
  $checksums = [];
  foreach ($tracked_files as $file) {
    if (!preg_match('#^public_html/#', $file)) continue;
    if ($file == $checksums_file) continue;
    $checksums[preg_replace('#^public_html/#', '', $file)] = '';
  }

// Reinsert checksums
  if (is_file($checksums_file)) {
    $checksum_files = preg_split('#(\r\n?|\n)#', file_get_contents($checksums_file), -1, PREG_SPLIT_NO_EMPTY);
    foreach ($checksum_files as $line) {
      list($checksum, $short_file) = explode("\t", $line);
      if (isset($checksums[$short_file])) {
        $checksums[$short_file] = $checksum;
      }
    }
  }

// If there are no committed files, go through all tracked files
  if (empty($committed_files)) {
    echo 'No committed files detected. Going through all tracked files in repository...' . PHP_EOL;
    $committed_files = $tracked_files;
  }

// Update checksums for committed and tracked files
  foreach ($committed_files as $file) {
    $short_file = preg_replace('#^public_html/#', '', $file);
    if (isset($checksums[$short_file])) {
      echo 'Updating checksum for '. $file . PHP_EOL;
      $tmp_file = tempnam(sys_get_temp_dir(), '_blob');
      shell_exec('git cat-file blob :'. $file .' > '. $tmp_file);
      $blob = file_get_contents($tmp_file);
      $checksums[$short_file] = md5(preg_replace('#(\r\n?|\n)#', "\n", $blob));
      unlink($tmp_file);
    }
  }

// Store checksums
  $output = '';
  foreach ($checksums as $short_file => $checksum) {
    $output .= $checksum ."\t". $short_file . PHP_EOL;
  }

  file_put_contents($checksums_file, $output);
