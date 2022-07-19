<?php

  class http_client {
    public $follow_redirects = false;
    public $timeout = 20;
    public $last_request;
    public $last_response;
    public static $stats = [
      'duration' => 0,
      'requests' => 0,
    ];

    public function call($method, $url='', $data=null, $headers=[], $asynchronous=false) {

      $this->last_request = [];
      $this->last_response = [];

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

      if (!empty($data)) {
        $data = is_array($data) ? http_build_query($data) : $data;
      }

      if (!empty($parts['user']) && empty($headers['Authorization'])) {
        $headers['Authorization'] = 'Basic ' . base64_encode($parts['user'] .':'. fallback($parts['pass']));
      }

      if (empty($headers['User-Agent'])) {
        $headers['User-Agent'] = PLATFORM_NAME.'/'.PLATFORM_VERSION;
      }

      if (empty($headers['Content-Type']) && !empty($data)) {
        $headers['Content-Type'] = 'application/x-www-form-urlencoded';
      }

      if (empty($headers['Content-Length'])) {
        $headers['Content-Length'] = ($data != '') ? mb_strlen($data) : 0;
      }

      if (empty($headers['Connection'])) {
        $headers['Connection'] = 'Close';
      }

      $request_headers = "$method $parts[path]" . ((isset($parts['query'])) ? '?' . $parts['query'] : '') ." HTTP/1.1\r\n"
                       . "Host: $parts[host]\r\n";

      foreach ($headers as $key => $value) {
        $request_headers .= "$key: $value\r\n";
      }

      $timestamp = microtime(true);

      $this->last_request = [
        'timestamp' => time(),
        'head' => $request_headers,
        'body' => $data,
      ];

      if (!$socket = stream_socket_client(strtr('scheme://host:port', $parts), $errno, $errstr, $this->timeout)) {
        return;
      }

      stream_set_timeout($socket, $this->timeout);

      fwrite($socket, $request_headers . "\r\n" . $data);

      $response = '';
      while (!feof($socket)) {

        if ((microtime(true) - $timestamp) > $this->timeout) {
          trigger_error('Timeout during retrieval', E_USER_WARNING);
          return false;
        }

        $response .= fgets($socket);
      }

      fclose($socket);

      $response_headers = substr($response, 0, strpos($response, "\r\n\r\n") + 2);
      $response_body = substr($response, strpos($response, "\r\n\r\n") + 4);

    // Decode chunked data
      if (preg_match('#Transfer-Encoding:\s?Chunked#i', $response_headers)) {
        $response_body = $this->http_decode_chunked_data($response_body);
      }

      preg_match('#HTTP/\d(\.\d)?\s(\d{3})#', $response_headers, $matches);
      $status_code = $matches[2];

      $this->last_response = [
        'timestamp' => time(),
        'status_code' => $status_code,
        'head' => $response_headers,
        'body' => $response_body,
        'duration' => round(microtime(true) - $timestamp, 3),
        'bytes' => strlen($response_headers . "\r\n" . $response_body),
      ];

      file_put_contents(functions::file_realpath('storage://logs/http_request_last-'. $parts['host'] .'.log'),
        '##'. str_pad(' ['. date('Y-m-d H:i:s', $this->last_request['timestamp']) .'] Request ', 70, '#', STR_PAD_RIGHT) . PHP_EOL . PHP_EOL .
        $this->last_request['head'] . "\r\n" .
        $this->last_request['body'] . "\r\n\r\n" .
        '##'. str_pad(' ['. date('Y-m-d H:i:s', $this->last_response['timestamp']) .'] Response — '. $this->last_response['bytes'] .' bytes transferred in '. $this->last_response['duration'] .' s ', 72, '#', STR_PAD_RIGHT) . PHP_EOL . PHP_EOL .
        $this->last_response['head'] . "\r\n" .
        $this->last_response['body']
      );

      self::$stats['requests']++;
      self::$stats['duration'] += microtime(true) - $timestamp;

    // Redirect
      if ($status_code == 301) {
        if (!$this->follow_redirects) {
          trigger_error('Destination is redirecting to another destination but follow_redirects is disabled', E_USER_WARNING);
        } else if (preg_match('#^Location:\s?(.*)?$#im', $response_headers, $matches)) {
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
        $length = (int)hexdec(substr($data, 0, $position));
        $result .= substr($data, $position + 2, $length);
        $data = substr($data, $position + 2 + $length);
      }
      return $result;
    }
  }
