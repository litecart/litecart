<?php

  function return_bytes($string) {
    sscanf($string, '%u%c', $number, $suffix);
    if (isset($suffix)) {
      $number = $number * pow(1024, strpos(' KMG', strtoupper($suffix)));
    }
    return $number;
  }

// Function to return absolute path from relative path
  function file_absolute_path($path) {

    if ($path = realpath($path)) {
      $path = str_replace('\\', '/', $path);
    }

    return $path;
  }

// Function to delete recursive data
  function file_delete($target) {

    if (strpos($target, '*') !== false) {
      foreach (glob($target, GLOB_BRACE) as $file) {
        if (preg_match('#(\\\\|/)\.{1,2}$#', $file)) continue;
        file_delete($file);
      }
      return true;
    }

    if (!file_exists($target)) return true;

    if (is_dir($target)) {
      file_delete(rtrim($target, '\\/').'/{,.}*');
      echo 'Delete ' . $target . '<br />' . PHP_EOL;
      return rmdir($target);
    }

    echo 'Delete ' . $target . '<br />' . PHP_EOL;
    return unlink($target);
  }

// Function to modify file
  function file_modify($files, $search, $replace, $regex=false) {

    foreach (glob($files) as $file) {
      echo 'Modify '. $file . '<br />' . PHP_EOL;

      $contents = file_get_contents($file);
      $contents = preg_replace('#\R#u', PHP_EOL, $contents);

      if ($regex) {
        $contents = preg_replace($search, $replace, $contents);
      } else {
        $contents = str_replace($search, $replace, $contents);
      }

      $result = file_put_contents($file, $contents);
    }

    return !empty($result) ? true : false;
  }

// Function to rename file or folder
  function file_rename($source, $target) {

    $result = rename($source, $target);

    return $result;
  }

// Function to copy recursive data
  function file_xcopy($source, $target) {

    $errors = false;

    if (is_dir($source)) {
      $source = rtrim($source, '/') . '/';
      $target = rtrim($target, '/') . '/';

      if (!file_exists($target)) {
        if (!mkdir($target)) $errors = true;
      }

      $dir = opendir($source);
      while (($file = readdir($dir)) !== false) {
        if ($file == '.' || $file == '..') continue;
        if (!file_xcopy($source.$file, $target.$file)) $errors = true;
      }

    } else if (!file_exists($target)) {
      echo 'Write '. $target . '<br />' . PHP_EOL;

      if (!copy($source, $target)) $errors = true;
    }

    return empty($errors) ? true : false;
  }
