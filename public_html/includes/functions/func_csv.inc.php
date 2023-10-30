<?php

  function csv_encode($array, $delimiter=',', $enclosure='"', $escape='"', $charset='utf-8', $eol="\r\n") {

    $output = '';

  // Collect columns
    $columns = [];
    foreach ($array as $row) {
      foreach (array_keys($row) as $column) {
        if (!in_array($column, $columns)) $columns[] = $column;
      }
    }

  // Collect rows and order by column order
    foreach (array_keys($array) as $row) {
      $line = [];
      foreach ($columns as $column) {
        $line[$column] = isset($array[$row][$column]) ? $array[$row][$column] : '';
      }
      $array[$row] = $line;
    }

  // Prepend column header
    array_unshift($array, array_combine($columns, $columns));

  // Build output
    foreach ($array as $row) {
      foreach (array_keys($row) as $column) {
        if (strpbrk($row[$column], $delimiter.$enclosure.$escape."\r\n") !== false) {
          $row[$column] = $enclosure . str_replace($enclosure, $escape.$enclosure, $row[$column]) . $enclosure;
        }
      }
      $output .= implode($delimiter, $row) . $eol; // Don't use fputcsv(); as the EOL and escape char cannot be customized
    }

  // Convert charset
    $output = language::convert_characters($output, mb_internal_encoding(), $charset);

    return preg_replace('#(\r\n?|\n)#', $eol, $output);
  }

  function csv_decode($string, $delimiter='', $enclosure='"', $escape='"', $charset='utf-8') {

    $output = [];

  // Remove Byte Order Mark (BOM) if any
    $string = preg_replace("#^(\xEF\xBB\xBF)+#", '', $string);

  // Convert EOL format
    $string = preg_replace('#(\r\n?|\n)#', PHP_EOL, $string);

  // Convert charset
    $string = language::convert_characters($string, $charset, mb_internal_encoding());

  // Trim preceeding and trailing whitespace
    $string = trim($string, "\r\n ");

  // Auto-detect delimiter
    if (empty($delimiter)) {
      preg_match('#^.*$#m', $string, $matches);
      foreach ([',', ';', "\t", '|', chr(124)] as $char) {
        if (strpos($matches[0], $char) !== false) {
          $delimiter = $char;
          break;
        }
      }

      if (empty($delimiter)) {
        trigger_error('Unable to determine CSV delimiter', E_USER_ERROR);
      }
    }

  // Decode CSV using temporary buffer for file handle
    $fp = fopen('php://temp', 'r+');
    fputs($fp, $string);
    rewind($fp);

    $line = 0;
    while ($row = fgetcsv($fp, 0, $delimiter, $enclosure, $escape)) {
      $line++;

      if (empty($headers)) {
        $headers = $row;
        continue;
      }

      if (count($headers) != count($row)) {
        trigger_error('Inconsistent amount of columns on line '. $line .' (Expected '. count($headers) .' columns - Found '. count($row) .')', E_USER_WARNING);
        return false;
      }

      $output[] = array_combine($headers, $row);
    }

    fclose($fp);

    return $output;
  }
