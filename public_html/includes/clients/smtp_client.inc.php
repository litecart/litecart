<?php

  class smtp_client {
    private $_socket;
    private $_host;
    private $_username;
    private $_password;
    private $_log_handle;
    private $_last_response;

    function __construct($host, $port=25, $username='', $password='') {

      if ($port == 465) {
        $this->_host = "ssl://$host:$port";
      } else {
        $this->_host = "tcp://$host:$port";
      }

      $this->_username = $username;
      $this->_password = $password;

      $this->_log_handle = fopen('storage://logs/last_smtp.log', 'w');
    }

    function __destruct() {
      if (is_resource($this->_socket)) $this->disconnect();
    }

    public function connect() {

      $stream_context = $context = stream_context_create([
        'ssl' => [
          // set some SSL/TLS specific options
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
        ],
      ]);

      fwrite($this->_log_handle, "Connecting to $this->_host ...\r\n");
      $this->_socket = stream_socket_client($this->_host, $errno, $errstr, 3, STREAM_CLIENT_CONNECT, $stream_context);

      if ($errno) {
        throw new Exception('Could not connect to socket '. $this->_host .': '. $errstr);
      }

      if (empty($this->_socket)) {
        throw new Exception('Failed opening socket connection to '. $this->_host);
      }

      stream_set_blocking($this->_socket, true);
      stream_set_timeout($this->_socket, 6);

      $this->read(220)
           ->write("EHLO {$_SERVER['SERVER_NAME']}\r\n", 250);

      if (preg_match('#250.STARTTLS#', $this->_last_response)) {
        $this->write("STARTTLS\r\n", 220);
        if (!stream_socket_enable_crypto($this->_socket, true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT)) {
        //if (!stream_socket_enable_crypto($this->_socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
          throw new Exception('Could not start TLS encryption');
        }
        $this->write("EHLO {$_SERVER['SERVER_NAME']}\r\n", 250);
      }

      if (!empty($this->_username)) {

        $auths = [];
        if (preg_match('#250.AUTH (.*?)\R#', $this->_last_response, $matches)) {
          $auths = explode(' ', $matches[1]);
        }

        switch (true) {

          case (in_array('CRAM-MD5', $auths));
            $this->write("AUTH CRAM-MD5\r\n", 334);
            $challenge = base64_decode(substr($this->_last_response, 4));
            $this->write(base64_encode($this->_username .' '. hash_hmac('md5', $this->_password, $challenge)) . "\r\n", 235);
            break;

          case (in_array('LOGIN', $auths)):
            $this->write("AUTH LOGIN\r\n", 334)
                 ->write(base64_encode($this->_username) . "\r\n", 334)
                 ->write(base64_encode($this->_password) . "\r\n", 235);
            break;

          case (in_array('PLAIN', $auths)):
            $this->write("AUTH PLAIN\r\n", 334)
                 ->write(base64_encode("\0" . $this->_username . "\0" . $this->_password) . "\r\n", 235);
            break;

          default:
            throw new Exception('No supported authentication methods ('. implode(', ', $auths).')');
        }
      }

      return $this;
    }

    public function disconnect() {

      if (!is_resource($this->_socket)) return;

      $this->write("QUIT\r\n");
      fwrite($this->_log_handle, "\r\n");

      fclose($this->_socket);
      fclose($this->_log_handle);

      return $this;
    }

    public function read($expected_response=null) {

      $response = $buffer = '';
      while (substr($buffer, 3, 1) != ' ') {
        if (!$buffer = fgets($this->_socket, 256)) throw new Exception('No response from socket');
        fwrite($this->_log_handle, "< $buffer");
        $response .= $buffer;
      }

      $this->_last_response = $response;

      if (substr($response, 0, 3) != $expected_response) {
        copy('storage://logs/last_smtp.log', 'storage://logs/last_smtp_error.log');
        throw new Exception('Unexpected socket response; '. $response);
      }

      return $this;
    }

    public function write($data, $expected_response=null) {

      fwrite($this->_log_handle, "> $data");
      $result = fwrite($this->_socket, $data);

      if ($expected_response !== null) {
        $this->read($expected_response);
      }

      return $this;
    }

    ###################################################################

    public function send($sender, $recipients, $data='') {

      if (!is_array($recipients)) $recipients = [$recipients];

      if (!is_resource($this->_socket)) $this->connect();

      $this->write("MAIL FROM: <$sender>\r\n", 250);

      foreach ($recipients as $recipient) {
        $this->write("RCPT TO: <$recipient>\r\n", 250);
  }

      $this->write("DATA\r\n", 354)
           ->write("$data\r\n")
           ->write(".\r\n", 250);

      return true;
    }
  }
