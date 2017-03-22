<?php

  class http_client {
    private $_config = array();
    public $last_request = array();
    public $last_response = array();

    public function __construct($config=array()) {

      $this->_config = array(
        'asynchronous' => false,
        'follow_redirects' => true,
      );

      foreach ($this->_config as $key => $value) {
        if (isset($config[$key])) $this->_config[$key] = $config[$key];
      }

      return $this;
    }

    public function call($url, $post_data=null, $headers=array()) {

      $this->last_request = array();
      $this->last_response = array();

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

      $request_body = (!empty($post_data) && is_array($post_data)) ? http_build_query($post_data) : $post_data;

      $request_head = (!empty($request_body) ? "POST " : "GET ") . $parts['path'] . ((isset($parts['query'])) ? "?" . $parts['query'] : '') ." HTTP/1.1\r\n"
           . "Host: ". $parts['host'] ."\r\n"
           . (!empty($request_body) ? "Content-Type: application/x-www-form-urlencoded\r\n" : '')
           . (!empty($headers) ? implode("\r\n", $headers) . "\r\n" : '')
           . "Content-Length: ". strlen($request_body) ."\r\n"
           . "Connection: Close\r\n"
           . "\r\n";

      $this->last_request['headers'] = $request_head;
      $this->last_request['body'] = $request_body;

      fwrite($fp, $request_head . $request_body);

      $found_body = false;
      $response_head = '';
      $response_body = '';
      $start = microtime(true);
      $timeout = 20;

      while (!feof($fp)) {
        if ((microtime(true) - $start) > $timeout) break;

        $row = fgets($fp);
        if ($row == "\r\n") {
          $found_body = true;
          continue;
        }
        if ($found_body) {
          if ($this->_config['asynchronous']) break;
          $response_body .= $row;
        } else {
          if ($this->_config['follow_redirects'] && stristr($row, "location:") != false) {
            $redirect_url = preg_replace("/location:/i", "", trim($row));
            if (empty($redirect_url)) $redirect_url = $url;
            $redirect_url = trim($url);
            return $this->call($redirect_url, $post_data, $headers);
          }
          $response_head .= $row;
        }
      }

      fclose($fp);

      $this->last_response['headers'] = $response_head;

    // Make sure HTTP 200 OK
      preg_match('/HTTP\/1\.(1|0)\s(\d{3})/', $response_head, $matches);
      if (!isset($matches[2]) || $matches[2] != '200') return false;

      $this->last_response['status_code'] = $matches[2];

      if ($this->_config['asynchronous']) return true;

    // Decode chunked data
      if (preg_match('/Transfer-Encoding: chunked/i', $response_head)) {
        $response_body = $this->http_decode_chunked_data($response_body);
      }

      $this->last_response['body'] = $response_body;

      return trim($response_body);
    }

    public function http_decode_chunked_data($in) {

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
  }
