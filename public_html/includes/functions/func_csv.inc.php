<?php

  function csv_encode($array, $delimiter=',', $enclosure='"', $escape='\\', $charset='utf-8', $eol="\r\n") {
    
    $fp = fopen('php://temp', 'r+');
    
    $array = array_merge(array(array_keys($array[0])), $array);
    
    foreach ($array as $row) {
      foreach (array_keys($row) as $key) {
        if (strpbrk($row[$key], $delimiter.$enclosure.$escape."\r\n") !== false) {
          $row[$key] = $enclosure . str_replace($enclosure, $escape.$enclosure, $row[$key]) . $enclosure;
        }
      }
      fputs($fp, implode($delimiter, $row) . $eol); // Don't use fputcsv as EOL and escape char can not be customized
    }
    
    $output = '';
    rewind($fp);
    while(!feof($fp)) $output .= fgets($fp);
    fclose($fp);
    
    if (strtolower(language::$selected['charset']) == 'utf-8' && strtolower($charset) != 'utf-8') {
      $output = utf8_decode($output);
    } else if (strtolower(language::$selected['charset']) != 'utf-8' && strtolower($charset) == 'utf-8') {
      $output = utf8_encode($output);
    }
    
    return preg_replace('/(\r\n|\r|\n)/', $eol, $output);
  }
  
  function csv_decode($string, $delimiter=',', $enclosure='"', $escape='\\', $charset='utf-8') {

    $output = array();
    
    $ini_eol = ini_get('auto_detect_line_endings');
    ini_set('auto_detect_line_endings', true);
    
    if (strtolower(language::$selected['charset']) == 'utf-8' && strtolower($charset) != 'utf-8') {
      $string = utf8_encode($string);
    } else if (strtolower(language::$selected['charset']) != 'utf-8' && strtolower($charset) == 'utf-8') {
      $string = utf8_decode($string);
    }
    
    $fp = fopen('php://temp', 'r+');
    fputs($fp, $string);
    rewind($fp);
    
    while ($row = fgetcsv($fp, 0, $delimiter, $enclosure, $escape)) {
      if (empty($headers)) {
        $headers = $row;
      } else {
        $output[] = array_combine($headers, $row);
      }
    }
    
    fclose($fp);
    
    ini_set('auto_detect_line_endings', $ini_eol);
    
    return $output;
  }

?>