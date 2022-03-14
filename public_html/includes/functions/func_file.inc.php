<?php

  function file_copy($source, $target, &$results=[]) {

    $source = str_replace('\\', '/', $source);
    $target = str_replace('\\', '/', $target);

    if (strpos($source, '*') !== false) {

      foreach (file_search($source) as $file) {
        $base_source = preg_replace('#^(.*/).*?$#', '$1', strtok($source, '*'));
        file_copy($file, rtrim($target, '/') .'/'. preg_replace('#^'. preg_quote($base_source, '#') .'#', '', $file), $results);
      }
    } else {

      if (is_dir($source)) {
        if (!is_dir($target)) {
          $results[$target] = mkdir($target);
          if (!$results[$target]) return false;
        }

        foreach (scandir($source) as $file) {
          if ($file == '.' || $file == '..') continue;
          file_copy(rtrim($source, '/') .'/'. $file, rtrim($target, '/') .'/'. $file, $results);
        }
      }

      if (is_file($source) || is_link($source)) {
        $results[$target] = copy($source, $target);
      }
    }

    return in_array(false, $results) ? false : true;
  }

  function file_delete($source, &$results=[]) {

  // Resolve glob
    if (strpos($source, '*') !== false) {
      foreach (file_search($source) as $file) {
        file_delete($file, $results);
      }
      return in_array(false, $results) ? false : true;
    }

    if (!file_exists($source)) {
      $results[$source] = null;
      return in_array(false, $results) ? false : true;

    } else if (is_dir($source)) {
      file_delete(rtrim($source, '/') .'/*', $results);
      $results[$source] = rmdir($source);

    } else if (is_file($source) || is_link($source)) {
      $results[$source] = unlink($source);
    }

    return in_array(false, $results) ? false : true;
  }

  function file_is_binary($file) {

    $fh  = fopen($file, "r");
    $blk = fread($fh, 512);
    fclose($fh);

    return (substr_count($blk, "^ -~")/512 > 0.3) or (substr_count($blk, "\x00") > 0);
  }

  function file_move($source, $target, &$results=[]) {

    $source = str_replace('\\', '/', $source);
    $target = str_replace('\\', '/', $target);

    if (strpos($source, '*') !== false) {

      foreach (file_search($source) as $file) {
        $base_source = preg_replace('#^(.*/).*?$#', '$1', strtok($source, '*'));
        file_move($file, rtrim($target, '/') .'/'. preg_replace('#^'. preg_quote($base_source, '#') .'#', '', $file), $results);
      }

    } else {

      if (file_exists($source) && !file_exists($target)) {
        $results[$target] = rename($source, $target);
      } else {
        $results[$target] = false;
      }
    }

    return in_array(false, $results) ? false : true;
  }

  function file_path($path) {

    $path = str_replace('\\', '/', $path);
    $parts = array_filter(explode('/', $path), 'strlen');

    $absolutes = [];
    foreach ($parts as $part) {
      if ('.' == $part) continue;
      if ('..' == $part) {
        array_pop($absolutes);
      } else {
        $absolutes[] = $part;
      }
    }

    return implode('/', $absolutes);
  }

  function file_permissions($file) {
    return '-'.strtr(substr(decoct(fileperms($file)), -3), [
      '0'	=> '---', // No Permission
      '1'	=> '--x', // Execute
      '2' => '-w-', // Write
      '3' => '-wx', // Execute + Write
      '4' => 'r--', // Read
      '5' => 'r-x', // Read + Execute
      '6' => 'rw-', // Read + Write
      '7' => 'rwx', // Read + Write + Execute
    ]);
  }

  function file_realpath($path) {
    $path = str_replace('\\', '/', realpath($path));
    if (is_dir($path)) $path = rtrim($path, '/') . '/';
    return $path;
  }

// Search files (Supports dual globstar **)
  function file_search($pattern, $flags=0) {

    if (strpos($pattern, '**') !== false) {

      list($leading, $trailing) = preg_split('#\*\*#', $pattern);
      $patterns = [$leading . $trailing];
      $leading .= '/*';

      while ($dirs = glob($leading, GLOB_ONLYDIR)) {
        $leading .= '/*';
        foreach ($dirs as $dir) {
          $patterns[] = $dir . $trailing;
        }
      }

      $files = [];
      foreach ($patterns as $pat) {
        $files = array_merge($files, file_search($pat, $flags));
      }

    } else {
      $files = glob($pattern, $flags);
    }

    $files = array_unique($files);
    sort($files);

    return $files;
  }

  function file_size($file) {

    if (is_file($file)) {
      return filesize($file);
    }

    if (is_dir($file)) {
      $size = 0;
      foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($file)) as $f) {
        $size += $f->getSize();
      }
      return $size;
    }

    return false;
  }

  function file_format_size(int $size) {
    switch (true) {
      case ($size == 0): return '-';
      case ($size < 1000): return language::number_format($size) . ' B';
      case (($size/1024) < 1000): return language::number_format($size/1024, 2) . ' kB';
      case (($size/1024/1024) < 1000): return language::number_format($size/1024/1024, 2) . ' MB';
      case (($size/1024/1024/1024) < 1000): return language::number_format($size/1024/1024/1024, 2) . ' GB';
    }
  }

  function file_relative_path($target, $base = null) {

    if ($base === null) $base = getcwd();

    $base = explode('/', rtrim(str_replace('\\', '/', $base), '/'));
    $target = explode('/', rtrim(str_replace('\\', '/', $target), '/'));

    while (count($base) && count($target) && ($base[0] == $target[0])) {
      array_shift($base);
      array_shift($target);
    }

    return str_pad('', count($base) * 3, '../') . implode('/', $target);
  }

// Strip paths from logic e.g. ./ ../
  function file_strip_path($path) {
    $new_path = [];

    foreach (explode('/', $path) as $part) {
      if (empty($part) || $part === '.') continue;

      if ($part !== '..') array_push($new_path, $part);
      else if (count($new_path) > 0) array_pop($new_path);
      else throw new \Exception('Climbing above the root is not permitted.');
    }

    return join('/', $new_path);
  }
