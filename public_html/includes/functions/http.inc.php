<?php
  
  if (!function_exists('http_request')) {
    function http_request($url, $post_fields=false, $headers=false, $asynchronous=false, $follow_redirects=true, $return='body') {
      trigger_error('http_request() is deprecated due to name collision with pecl_http package. Use http_fetch() instead.', E_USER_DEPRECATED);
      return http_fetch($url, $post_fields, $headers, $asynchronous, $follow_redirects, $return);
    }
  }
  
  function http_fetch($url, $post_fields=false, $headers=false, $asynchronous=false, $follow_redirects=true, $return='body') {
    
    if (ini_get('allow_url_fopen')) {
      $parts = parse_url($url);
      
      $parts['protocol'] = (substr($url, 0, 8) == 'https://') ? 'ssl://' : false;
      if (empty($parts['port'])) $parts['port'] = (substr($url, 0, 8) == 'https://') ? 443 : 80;
      
      if (empty($parts['host'])) {
        trigger_error('No host to connect to in url "'. $url .'"', E_USER_WARNING);
        return;
      }
      
      $fp = fsockopen($parts['protocol'] . $parts['host'], $parts['port'], $errno, $errstr, 30);
      
      if (!$fp) {
        trigger_error('Error contacting URL ('. $url .'), '. $errstr, E_USER_WARNING);
        return;
      }
      
      $post_string = is_array($post_fields) ? http_build_query($post_fields) : $post_fields;
      
      $out = ($post_string ? "POST " : "GET ") . $parts['path'] . ((isset($parts['query'])) ? "?" . $parts['query'] : '') ." HTTP/1.1\r\n"
           . "Host: ". $parts['host'] ."\r\n"
           . (!empty($post_string) ? "Content-Type: application/x-www-form-urlencoded\r\n" : '')
           . (!empty($headers) ? implode("\r\n", $headers) . "\r\n" : '')
           . "Content-Length: ". strlen($post_string) ."\r\n"
           . "Connection: Close\r\n"
           . "\r\n" . $post_string;
      
      fwrite($fp, $out);
      
      $found_body = false;
      $response_header = '';
      $response_body = '';
      $start = microtime(true);
      $timeout = 30;
      
      while (!feof($fp)) {
        if ((microtime(true) - $start) > $timeout) break;
      
        $row = fgets($fp);
        if ($row == "\r\n") {
          $found_body = true;
          continue;
        }
        if ($found_body) {
          if ($asynchronous) break;
          $response_body .= $row;
        } else {
          if ($follow_redirects && stristr($row, "location:") != false) {
            $redirect_url = preg_replace("/location:/i", "", trim($row));
            if ($redirect_url == '') $redirect_url = $url;
            return http_fetch($redirect_url, $post_fields, $headers, $asynchronous, $follow_redirects, $return);
          }
          $response_header .= $row;
        }
      }
      
      fclose($fp);
      
    // Make sure HTTP 200 OK
      preg_match('/HTTP\/1\.[1|0]\s(\d{3})/', $response_header, $matches);
      if (!isset($matches[1]) || $matches[1] != '200') return false;
      
      if ($asynchronous) return true;
      
    // Decode chunked data
      if (preg_match('/Transfer-Encoding: chunked/i', $response_header)) {
        $response_body = http_decode_chunked_data($response_body);
      }
      
      switch ($return) {
        case 'both':
          return trim($response_header . $response_body);
          break;
        case 'header':
          return trim($response_header);
          break;
        case 'body':
        default:
          return trim($response_body);
          break;
      }
      
    } else if (function_exists('curl_init')) {
    
      $ch = curl_init();
      
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
      curl_setopt($ch, CURLOPT_POST, $post_fields ? true : false);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields ? http_build_query($post_fields) : false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, $asynchronous ? false : true);
      curl_setopt($ch, CURLOPT_TIMEOUT, $asynchronous ? 1 : 30);
      
      switch ($return) {
        case 'both':
          curl_setopt($ch, CURLOPT_HEADER, true);
          break;
        case 'header':
          curl_setopt($ch, CURLOPT_HEADER, true);
          curl_setopt($ch, CURLOPT_NOBODY, true);
          break;
        case 'body':
        default:
          curl_setopt($ch, CURLOPT_HEADER, false);
          break;
      }
      
      $result = curl_exec($ch);
      
      curl_close($ch);
      
      return $result;
      
    } else {
      trigger_error('No HTTP components available', E_USER_ERROR);
    }
  }
  
  function http_decode_chunked_data($in) {
    $out = '';
    while($in != '') {
      $lf_pos = strpos($in, "\012");
      if($lf_pos === false) {
        $out .= $in;
        break;
      }
      $chunk_hex = trim(substr($in, 0, $lf_pos));
      $sc_pos = strpos($chunk_hex, ';');
      if($sc_pos !== false)
        $chunk_hex = substr($chunk_hex, 0, $sc_pos);
      if($chunk_hex == '') {
        $out .= substr($in, 0, $lf_pos);
        $in = substr($in, $lf_pos + 1);
        continue;
      }
      $chunk_len = hexdec($chunk_hex);
      if($chunk_len) {
        $out .= substr($in, $lf_pos + 1, $chunk_len);
        $in = substr($in, $lf_pos + 2 + $chunk_len);
      } else {
        $in = '';
      }
    }
    return $out;
  }
  
?>