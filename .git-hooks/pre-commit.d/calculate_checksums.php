<?php

  $checksums_file = 'public_html/install/checksums.md5';
  $tracked_files = preg_split('#(\r\n?|\n)#', shell_exec('git ls-files'), -1, PREG_SPLIT_NO_EMPTY);
  $committed_files = preg_split('#(\r\n?|\n)#', shell_exec('git diff --cached --name-only 2>&1'), -1, PREG_SPLIT_NO_EMPTY);

// Create list of files for checksums
  $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('public_html/', FilesystemIterator::UNIX_PATHS | RecursiveDirectoryIterator::SKIP_DOTS));

  $checksums = [];
  foreach ($iterator as $file) {
    if ($file->isDir()) continue;
    if (!in_array($file->getPathname(), $tracked_files)) continue;
    if ($file->getPathname() == $checksums_file) continue;
    $checksums[preg_replace('#^public_html/#', '', $file->getPathname())] = '';
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
    $committed_files = $tracked_files;
  }

// Update checksums for committed and tracked files
  foreach ($committed_files as $file) {
    $short_file = preg_replace('#^public_html/#', '', $file);
    if (isset($checksums[$short_file])) {
      echo 'Updating checksum for '. $file . PHP_EOL;
      $blob = shell_exec('git cat-file blob :'. $short_file .' 2>&1');
      $checksums[$short_file] = md5($blob);
    }
  }

// Store checksums
  $output = '';
  foreach ($checksums as $short_file => $checksum) {
    $contents = preg_replace('#(\r\n?|\n)#', "\n", file_get_contents('public_html/'.$short_file));
    $output .= md5($contents) ."\t". $short_file . PHP_EOL;
  }

  file_put_contents($checksums_file, $output);

  if (in_array($checksums_file, $committed_files)) {
    echo 'Updating staged checksums file '. $checksums_file . PHP_EOL;
    shell_exec('git add '. $checksums_file);
  }
