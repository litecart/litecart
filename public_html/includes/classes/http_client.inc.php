<?php

  class http_client {
    public $follow_redirects = false;
    public $timeout = 20;
    public $last_request;
    public $last_response;

    public function call($method, $url='', $data=null, $headers=array(), $asynchronous=false) {

    // Backwards compatibility (where first param was URL supporting only GET/POST)
      if (strpos($method, '://') !== false) {
        list($url, $data, $headers) = func_get_args();
        $method = !empty($data) ? 'POST' : 'GET';
      }

      $this->last_request = array();
      $this->last_response = array();

      $parts = parse_url($url);

      if (empty($parts['host'])) {
        trigger_error('No host to connect to', E_USER_WARNING);
        return;
      }

      if (empty($method)) $method = 'GET';
      if (empty($parts['scheme']) || $parts['scheme'] == 'http') $parts['scheme'] = 'tcp';
      if ($parts['scheme'] == 'https') $parts['scheme'] = 'ssl';
      if (empty($parts['port'])) $parts['port'] = ($parts['scheme'] == 'ssl') ? 443 : 80;
      if (empty($parts['path'])) $parts['path'] = '/';

      $data = (!empty($data) && is_array($data)) ? http_build_query($data) : $data;

      if (!empty($parts['user']) && !empty($parts['pass']) && empty($headers['Basic'])) {
        $headers['Authorization'] = 'Basic ' . base64_encode($parts['user'] .':'. $parts['pass']);
      }

      if (empty($headers['Content-Type']) && !empty($data)) {
        $headers['Content-Type'] = 'application/x-www-form-urlencoded';
      }

      if (!empty($data) && empty($headers['Content-Length'])) {
        $headers['Content-Length'] = strlen($data);
      }

      if (empty($headers['Connection'])) {
        $headers['Connection'] = 'Close';
      }

      $out = $method ." ". $parts['path'] . ((isset($parts['query'])) ? '?' . $parts['query'] : '') ." HTTP/1.1\r\n" .
             "Host: ". $parts['host'] ."\r\n";

      foreach ($headers as $key => $value) {
        $out .= "$key: $value\r\n";
      }

      $found_body = false;
      $response_header = '';
      $response_body = '';
      $microtime_start = microtime(true);

      $this->last_request['head'] = $out;
      $this->last_request['body'] = $data;
      $this->last_request['timestamp'] = time();

      if (!$socket = stream_socket_client(strtr('scheme://host:port', $parts), $errno, $errstr, $this->timeout)) {
        trigger_error('Error calling URL ('. $url .'): '. $errstr, E_USER_WARNING);
        return;
      }

      stream_set_timeout($socket, $this->timeout);

      fwrite($socket, $out . "\r\n");
      fwrite($socket, $data);

      while (!feof($socket)) {

        if ((microtime(true) - $microtime_start) > $this->timeout) {
          trigger_error('Timout during retrieval', E_USER_WARNING);
          return false;
        }

        $line = fgets($socket);
        if ($line == "\r\n") {
          $found_body = true;
          continue;
        }

        if ($found_body) {
          if ($asynchronous) return true;
          $response_body .= $line;
          continue;
        }

        $response_header .= $line;
      }

      fclose($socket);

    // Decode chunked data
      if (preg_match('#Transfer-Encoding:\s?Chunked#i', $response_header)) {
        $response_body = $this->http_decode_chunked_data($response_body);
      }

      preg_match('#HTTP/1\.(1|0)\s(\d{3})#', $response_header, $matches);
      $status_code = $matches[2];

      $this->last_response['timestamp'] = time();
      $this->last_response['status_code'] = $status_code;
      $this->last_response['head'] = $response_header . "\r\n";
      $this->last_response['duration'] = round(microtime(true) - $microtime_start, 3);
      $this->last_response['bytes'] = strlen($response_header . "\r\n" . $response_body);
      $this->last_response['body'] = $response_body;

      file_put_contents(FS_DIR_HTTP_ROOT . WS_DIR_LOGS . 'http_request_last.log',
        "## [". date('Y-m-d H:i:s', $this->last_request['timestamp']) ."] Request ##############################\r\n\r\n" .
        $this->last_request['head'] .
        $this->last_request['body'] ."\r\n\r\n" .
        "## [". date('Y-m-d H:i:s', $this->last_response['timestamp']) ."] Response — ". (float)$this->last_response['bytes'] ." kb transferred in ". (float)$this->last_response['duration'] ." s ##############################\r\n\r\n" .
        $this->last_response['head'] .
        $this->last_response['body'] ."\r\n\r\n"
      );

    // Redirect
      if ($status_code == 301) {
        if (!$this->follow_redirects) {
          trigger_error('Destination is redirecting to another destination but follow_redirects is disabled', E_USER_WARNING);
        } else if (preg_match('#^Location:\s?(.*)?$#im', $line, $matches)) {
          $redirect_url = !empty($matches[1]) ? trim($matches[1]) : $url;
          return $this->call($method, $redirect_url, $data, $headers);
        } else {
          trigger_error('Destination is redirecting to a null destination', E_USER_WARNING);
        }
      }

      return $response_body;
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
