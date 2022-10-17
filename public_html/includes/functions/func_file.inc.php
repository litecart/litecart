<?php

  function file_delete($pattern, $flags=0, &$results=[]) {

    foreach (file_search($pattern, $flags) as $file) {

      if (is_dir($file)) {
        file_delete($file.'*', $flags, $results);
        $results[$file] = rmdir($file);
        continue;
      }

      return $results[$file] = unlink($file);
    }

    return in_array(false, new RecursiveIteratorIterator(new RecursiveArrayIterator($results)));
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

  function file_format_size($size) {
    switch (true) {
      case ($size == 0): return '-';
      case ($size < 1000): return language::number_format($size, 0) . ' B';
      case (($size/1024) < 1000): return language::number_format($size/1024) . ' kB';
      case (($size/1024/1024) < 1000): return language::number_format($size/1024/1024, 2) . ' MB';
      case (($size/1024/1024/1024) < 1000): return language::number_format($size/1024/1024/1024, 2) . ' GB';
    }
  }

  function file_is_binary($file) {

    $fh  = fopen($file, "r");
    $blk = fread($fh, 512);
    fclose($fh);

    return (substr_count($blk, "^ -~")/512 > 0.3) or (substr_count($blk, "\x00") > 0);
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


  function file_relative_path($target, $base = FS_DIR_APP) {

    if ($base === null) $base = getcwd();

    $base = explode('/', rtrim(str_replace('\\', '/', file_realpath($base)), '/'));
    $target = explode('/', rtrim(str_replace('\\', '/', file_realpath($target)), '/'));

    while (count($base) && count($target) && ($base[0] == $target[0])) {
      array_shift($base);
      array_shift($target);
    }

    return str_pad('', count($base) * 3, '../') . implode('/', $target);
  }

// Strip paths from logic e.g. ./ ../
  function file_resolve_path($path) {

    if (empty($path)) return $path;

    $path = str_replace('\\', '/', $path);
    $path = preg_replace('#(?<!:)//+#', '/', $path);

    $new_path = [];

    foreach (explode('/', $path) as $part) {
      if (empty($part) || $part === '.') continue;

      if ($part !== '..') array_push($new_path, $part);
      else if (count($new_path) > 0) array_pop($new_path);
      else throw new \Exception('Climbing above the root is not permitted.');
    }

    return join('/', $new_path);
  }

// Search files (Supports dual globstar **)
  function file_search($glob, $flags=0) {

  // Unixify paths
    $glob = str_replace('\\', '/', $glob);

  // Set basedir and remains
    $basedir = '';
    $remains = $glob;

    for ($i=0; $i<strlen($glob); $i++) {
      if (in_array($glob[$i], ['*', '[', ']', '{', '}'])) break;
      if ($glob[$i] == '/') {
        $basedir = substr($glob, 0, $i+1);
        $remains = substr($glob, $i+1);
      }
    }

  // Halt if basedir does not exist
    if ($basedir && !is_dir($basedir)) {
      return [];
    }

  // If there are no pattern remains, return base directory if valid
    if (!$remains) {
      if (is_dir($basedir)) {
        return [$basedir];
      } else {
        return [];
      }
    }

  // Extract pattern for current directory
    if (($pos = strpos($remains, '/')) !== false) {
      list($pattern, $remains) = [substr($remains, 0, $pos+1), substr($remains, $pos+1)];
    } else {
      list($pattern, $remains) = [$remains, ''];
    }

  // fnmatch() doesn't support GLOB_BRACE. Let's create a regex pattern instead.
    $regex = strtr($pattern, [
      '[!' => '[^',
      '\\' => '\\\\',
      '.'  => '\\.',
      '('  => '\\(',
      ')'  => '\\)',
      '|'  => '\\|',
      '+'  => '\\+',
      '^'  => '\\^',
      '$'  => '\\$',
      '*'  => '[^/]*',
      '**' => '.*',
      '?'  => '.',
    ]);

    if ($flags & GLOB_BRACE) {

      $regex = preg_replace_callback('#\{[^\}]+\}#', function($matches) {
        return strtr($matches[0], ['{' => '(', '}' => ')', ',' => '|']);
      }, $regex);

    } else {
      $regex = strtr($regex, ['{' => '\\{', '}' => '\\}']);
    }

    $regex = '#^'.$regex.'$#';

    $folders = [];
    $files = [];

  // Open directory
    $dh = opendir($basedir ? $basedir : './');

  // Step through each file in directory
    while ($file = readdir($dh)) {
      if (in_array($file, ['.', '..'])) continue;

    // Prepend path
      $file = $basedir . $file;
      $filetype = filetype($file);

      if ($filetype == 'dir') {

      // Resolve double globstars
        if (strpos($pattern, '**') !== false) {
          $folders = array_merge($folders, file_search($file .'/'. $pattern . $remains, $flags));
        }

      // Collect a matching folder
        if (preg_match($regex, basename($file)) || preg_match($regex, basename($file).'/')) {
          if ($remains) {
            $folders = array_merge($folders, file_search($file .'/'. $remains, $flags));
          } else {
            $folders[] = $file .'/';
          }
        }

      } else if ($filetype == 'file') {

      // Skip if not a directory during GLOB_ONLYDIR
        if ($flags & GLOB_ONLYDIR) continue;

      // Collect a matching file
        if (preg_match($regex, basename($file))) {
          $files[] = $file;
        }
      }
    }

  // Merge folders and files into one and same result
    $results = array_merge($folders, $files);

  // Sort results
    asort($results);

    return $results;
  }

  function file_xcopy($source, $target, $flags=0, &$results=[]) {

    if (is_file($source) || is_link($source)) {
      return $results[$target] = copy($source, $target);
    }

    if (is_dir($source)) {
      if (!is_dir($target)) {
        return $results[$target] = mkdir($target);
      }

      foreach (scandir($source) as $file) {
        if ($file == '.' || $file == '..') continue;
        file_xcopy(rtrim($source, '/') .'/'. $file, rtrim($target, '/') .'/'. $file);
      }
    }

    return true;
  }
