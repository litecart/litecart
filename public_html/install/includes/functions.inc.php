<?php

// Function to absolute path from relative path
  function file_absolute_path($path=null) {
    if (empty($path)) $path = dirname(__FILE__);
    $path = realpath($path);
    $path = str_replace('\\', '/', $path);
    $parts = array_filter(explode('/', $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
      if ('.' == $part) continue;
      if ('..' == $part) {
        array_pop($absolutes);
      } else {
        $absolutes[] = $part;
      }
    }
    return ((strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') ? '' : '/') . implode('/', $absolutes);
  }
  
// Function to delete recursive data
  function file_delete($path) {
    if (!file_exists($path)) return true;
    if (!is_dir($path) || is_link($path)) return unlink($path);
    foreach (scandir($path) as $file) {
      if ($file == '.' || $file == '..') continue;
      if (!file_delete($path .'/'. $file)) {
        chmod($path .'/'. $file, 0777);
        if (!file_delete($path .'/'. $file)) return false;
      }
    }
    return rmdir($path); 
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
      if (!copy($source, $target)) $errors = true;
    }
    return  empty($errors) ? true : false;
  }

?>