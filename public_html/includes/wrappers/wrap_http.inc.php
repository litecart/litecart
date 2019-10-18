<?php

  class wrap_http {
    public $follow_redirects = false;
    public $timeout = 20;
    public $last_request;
    public $last_response;

    public function call($method, $url='', $data=null, $headers=array(), $asynchronous=false) {

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

      if (empty($headers['User-Agent'])) {
        $headers['User-Agent'] = PLATFORM_NAME.'/'.PLATFORM_VERSION;
      }

      if (empty($headers['Content-Type']) && !empty($data)) {
        $headers['Content-Type'] = 'application/x-www-form-urlencoded';
      }

      if (empty($headers['Content-Length'])) {
        $headers['Content-Length'] = mb_strlen($data);
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
      $response_headers = '';
      $response_body = '';
      $microtime_start = microtime(true);

      $this->last_request = array(
        'timestamp' => time(),
        'head' => $out,
        'body' => $data,
      );

      if (!$socket = stream_socket_client(strtr('scheme://host:port', $parts), $errno, $errstr, $this->timeout)) {
        return;
      }

      stream_set_timeout($socket, $this->timeout);

      fwrite($socket, $out . "\r\n");
      fwrite($socket, $data);

      while (!feof($socket)) {

        if ((microtime(true) - $microtime_start) > $this->timeout) {
          trigger_error('Timeout during retrieval', E_USER_WARNING);
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

        $response_headers .= $line;
      }

      fclose($socket);

    // Decode chunked data
      if (preg_match('#Transfer-Encoding:\s?Chunked#i', $response_headers)) {
        $response_body = $this->http_decode_chunked_data($response_body);
      }

      preg_match('#HTTP/\d(\.\d)?\s(\d{3})#', $response_headers, $matches);
      $status_code = $matches[2];

      $this->last_response = array(
        'timestamp' => time(),
        'status_code' => $status_code,
        'head' => $response_headers,
        'body' => $response_body,
        'duration' => round(microtime(true) - $microtime_start, 3),
        'bytes' => strlen($response_headers . "\r\n" . $response_body),
      );

      file_put_contents(FS_DIR_APP . 'logs/http_request_last-'. $parts['host'] .'.log',
        '##'. str_pad(' ['. date('Y-m-d H:i:s', $this->last_request['timestamp']) .'] Request ', 70, '#', STR_PAD_RIGHT) . PHP_EOL . PHP_EOL .
        $this->last_request['head'] . PHP_EOL .
        $this->last_request['body'] . PHP_EOL . PHP_EOL .
        '##'. str_pad(' ['. date('Y-m-d H:i:s', $this->last_response['timestamp']) .'] Response — '. (float)$this->last_response['bytes'] .' bytes transferred in '. (float)$this->last_response['duration'] .' s ', 72, '#', STR_PAD_RIGHT) . PHP_EOL . PHP_EOL .
        $this->last_response['head'] . PHP_EOL .
        $this->last_response['body'] . PHP_EOL . PHP_EOL
      );

      if (class_exists('stats', false)) {
        stats::set('http_requests', stats::get('external_requests') + 1);
        stats::set('http_duration', stats::get('http_duration') + $this->last_response['duration']);
      }

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

    public function http_decode_chunked_data($data) {
      for ($result = ''; !empty($data); $data = trim($data)) {
        $position = strpos($data, "\r\n");
        $length = hexdec(substr($data, 0, $position));
        $result .= substr($data, $position + 2, $length);
        $data = substr($data, $position + 2 + $length);
      }
      return $result;
    }
  }
