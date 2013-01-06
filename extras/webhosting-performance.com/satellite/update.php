<?php

  ini_set('display_errors', 'On');
  error_reporting(E_ALL);
  ignore_user_abort(true);
  
  $updated = false;
  
  $ch = curl_init('http://www.webhosting-performance.com/api/?action=update');
  curl_setopt($ch, CURLOPT_HEADER, "Expect:\r\n");
  curl_setopt($ch, CURLOPT_TIMEOUT, 15);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    
  $response = curl_exec($ch);
  
  if ($response === false) throw new Exception("Problem reading data from $url, ". curl_error($ch));
  
  $response = unserialize($response);
  if ($response === false) throw new Exception("Problem reading data from $url, $php_errormsg: $response");
  
  if (strtolower($response['status']) != 'ok') die($response['status'] .'.');
  
  if (!isset($response['files']) || !is_array($response['files'])) die('No files listed by API.');
  
  foreach ($response['files'] as $file) {
  
    if (md5($file['source']) == $file['checksum']) {
      
      if (!file_exists($file['filename']) || $file['checksum'] != md5_file($file['filename'])) {
        
        if (file_put_contents($file['filename'], $file['source'])) {
          $output[] = $file['filename'] .': Updated';
          $updated = true;
          
        } else {
          $output[] = $file['filename'] .': Cannot write to file';
        }
      
      } else {
        $output[] = $file['filename'] . ': Already up to date';
      }
      
    } else {
      $output[] = $file['filename'] . ': Cheksum error from api';
    }
  }
  
  if ($updated) file_put_contents('lastrun.dat', '');
  
  echo '<p>'. implode('<br />' . PHP_EOL, $output) .'</p>';
  
  echo '<p>To run the test again, <a href="'. str_replace('update.php', '', $_SERVER['REQUEST_URI']) .'">click here</a>.</p>';
  
?>