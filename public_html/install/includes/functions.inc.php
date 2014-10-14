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
  function file_delete($target) {
    
    if (!file_exists($target)) return true;
    
    if (!is_dir($target) || is_link($target)) {
      
      echo 'Delete '. $target . '<br />' . PHP_EOL;
      
      $parent_permissions = null;
      if (!is_writable($target)) {
        $parent_permissions = substr(sprintf('%o', @fileperms(dirname($target))), -4);
        @chmod(dirname($target), 0777);
      }
      
      if (!is_writable($target)) {
        @chmod($target, 0777);
      }
      
      $result = unlink($target);
      
      if (!empty($parent_permissions)) @chmod(dirname($target), $parent_permissions);
      
      return $result;
    } 
    
    foreach (scandir($target) as $file) {
      if ($file == '.' || $file == '..') continue;
      if (!file_delete($target .'/'. $file)) return false;
    }
    
    echo 'Delete '. $target . '<br />' . PHP_EOL;
    
    $parent_permissions = null;
    if (!is_writable($target)) {
      $parent_permissions = substr(sprintf('%o', @fileperms(dirname($target))), -4);
      @chmod(dirname($target), 0777);
    }
    
    $target_permissions = null;
    if (!is_writable($target)) {
      $parent_permissions = substr(sprintf('%o', @fileperms($target)), -4);
      @chmod($target, 0777);
    }
    
    $result = rmdir($path);
    
    if (!empty($parent_permissions)) @chmod(dirname($target), $parent_permissions);
    if (!empty($target_permissions)) @chmod($target, $target_permissions);
    
    return $result;
  }
  
// Function to modify file
  function file_modify($file, $search, $replace) {
    
    echo 'Modify '. $file . '<br />' . PHP_EOL;
    
    if (!is_file($file)) return false;
    
    $target_permissions = null;
    if (!is_writable($file)) {
      $target_permissions = substr(sprintf('%o', @fileperms($file)), -4);
      @chmod($file, 0777);
    }
    
    $contents = file_get_contents($file);
    $contents = preg_replace('#\R#u', PHP_EOL, $contents);
    $contents = str_replace($search, $replace, $contents);
    $result = file_put_contents($file, $contents);
    
    if (!empty($target_permissions)) @chmod($file, $target_permissions);
    
    return $result;
  }
  
// Function to rename file or folder
  function file_rename($source, $target) {
    
    $source_parent_permissions = null;
    if (!is_writable($source)) {
      $source_parent_permissions = substr(sprintf('%o', @fileperms(dirname($source))), -4);
      @chmod(dirname($source), 0777);
    }
    
    $target_parent_permissions = null;
    if (!is_writable($target)) {
      $target_parent_permissions = substr(sprintf('%o', @fileperms($target)), -4);
      @chmod($target, 0777);
    }
    
    $result = rename($source, $target);
    
    if (!empty($source_parent_permissions)) @chmod(dirname($source), $source_parent_permissions);
    if (!empty($target_parent_permissions)) @chmod($target, $target_parent_permissions);
    
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
      
      $parent_permissions = null;
      if (!is_writable($target)) {
        $parent_permissions = substr(sprintf('%o', @fileperms(dirname($target))), -4);
        @chmod(dirname($target), 0777);
      }
      
      $target_permissions = null;
      if (is_file($target)) {
        $target_permissions = substr(sprintf('%o', @fileperms($target)), -4);
        @chmod($target, 0777);
      }
      
      if (!copy($source, $target)) $errors = true;
      
      if (!empty($parent_permissions)) @chmod(dirname($target), $parent_permissions);
      if (!empty($target_permissions)) @chmod($target, $target_permissions);
    }
    
    return empty($errors) ? true : false;
  }

?>