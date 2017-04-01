<?php

  class security {

    private static $_bad_urls;
    private static $_blacklist;
    private static $_whitelist;
    private static $_ban_time = '12 hours';
    private static $_trigger;

    public static function construct() {

      if (!file_exists(FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'blacklist.txt')) file_put_contents(FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'blacklist.txt', '');
      if (!file_exists(FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'whitelist.txt')) file_put_contents(FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'whitelist.txt', '');

      self::$_bad_urls = FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'bad_urls.txt';
      self::$_blacklist = FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'blacklist.txt';
      self::$_whitelist = FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'whitelist.txt';
    }

    //public static function load_dependencies() {
    //}

    public static function initiate() {

    // Bad Bot Trap - Establish trigger
      if (!isset(session::$data['bottrap']['trigger'])) session::$data['bottrap']['trigger'] = array();
      self::$_trigger = &session::$data['bottrap']['trigger'];

      if (empty(self::$_trigger['key']) || empty(self::$_trigger['expires']) || self::$_trigger['expires'] < date('Y-m-d H:i:s')) {
        session::$data['bottrap']['trigger'] = array(
          'key' => substr(str_shuffle('abcdefghjklmnopqrstuvwxyzabcdefghjklmnopqrstuvwxyzabcdefghjklmnopqrstuvwxyz'), 0, rand(6, 8)),
          'expires' => date('Y-m-d H:i:s', strtotime('+5 minutes')),
        );
      }
    }

    public static function startup() {

    // Check if client is blacklisted
      if (settings::get('security_blacklist')) {
        if (self::is_blacklisted()) {
          http_response_code(403);
          die('<html>' . PHP_EOL
            . '<head>' . PHP_EOL
            . '<title>Blacklisted</title>' . PHP_EOL
            . '</head>' . PHP_EOL
            . '<body style="font-size: 20px;">' . PHP_EOL
            . '<h1>Banned</h1>' . PHP_EOL
            . '<p>You have been banned due to a bad client behavior.<br />' . PHP_EOL
            . '  The data recorded can be used for tracing requests back to you.<br />' . PHP_EOL
            . '  We take all spam, hacking, hijacking or intrusion attempts seriously.</p>' . PHP_EOL
            . '<p>If you feel this an error, kindly send an email to the hostmaster at '. settings::get('store_email') .'.</p>' . PHP_EOL
            . '</body>' . PHP_EOL
            . '</html>' . PHP_EOL
          );
        }
      }

    // Check if client is accessing a blacklisted URL
      if (settings::get('security_bad_urls')) {
        if (self::is_accessing_bad_url()) {
          self::ban('Bad URL');
        }
      }

    // XSS Protection - Check if request contains known XSS
      if (settings::get('security_xss')) {

        $checksum_get = md5(serialize($_GET));
        $checksum_post = md5(serialize($_POST));

        if (!function_exists('sanitize_string')) {
          function sanitize_string(&$item, &$key) {
            $filter_list = array(
              //'/<script(.*?)>(.*?)<\/script>/s' => '',  // Enabling this will prevent administrators from storing javascripts in the WYSIWYG editor
              '/eval(?:[\s]+)?\((.*)\)/s' => '',
              '/base64_/' => '',
              '/union(?:[\s]+)?select/s' => '',
            );

            $item = preg_replace(array_keys($filter_list), array_values($filter_list), $item);
          }
        }
        array_walk_recursive($_GET, 'sanitize_string');
        array_walk_recursive($_POST, 'sanitize_string');

        if (md5(serialize($_GET)) != $checksum_get) {
          self::ban('XSS - HTTP GET');
        }

        if (md5(serialize($_POST)) != $checksum_post) {
          self::ban('XSS - HTTP POST');
        }
      }

    // Session Protection
      if (settings::get('security_session_hijacking')) {
        if ($_SERVER['REMOTE_ADDR'] != session::$data['last_ip'] && $_SERVER['HTTP_USER_AGENT'] != session::$data['last_agent']) { // Decreased session security due to iOS AJAX, GoogleBot, and mobile networks
          error_log('Session hijacking attempt from '. $_SERVER['REMOTE_ADDR'] .' ['. $_SERVER['HTTP_USER_AGENT'] .'] on '. $_SERVER['REQUEST_URI'] .'. Expected '. session::$data['last_ip'] .' ['. session::$data['last_agent'] .']');
          session::clear();
          sleep(3);
          http_response_code(400);
          header('Location: ' . $_SERVER['REQUEST_URI']);
          exit;
        }
      }

    // HTTP POST Protection
      if (settings::get('security_http_post')) {
        if (!empty($_POST) && (!defined('REQUIRE_POST_TOKEN') || REQUIRE_POST_TOKEN) && (!isset(route::$route['post_security']) || route::$route['post_security'])) {
          if (!isset($_POST['token']) || $_POST['token'] != form::session_post_token()) {
            error_log('Warning: Blocked a forbidden form data submission as an invalid HTTP POST token was submitted by '. $_SERVER['REMOTE_ADDR'] .' ['. (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '') .'] requesting '. $_SERVER['REQUEST_URI'] .'.');
            session::clear();
            sleep(3);
            http_response_code(400);
            die('HTTP POST Error: The form submit token is either invalid or issued for another session identity. Your request has therefore not been processed. Please try again.');
          }
        }
      }

    // Bad Bot Trap - If caught in bot trap, ban the current client
      if (settings::get('security_bot_trap')) {
        if (isset($_GET[self::$_trigger['key']])) self::ban('Stuck in bot trap at '. $_SERVER['REQUEST_URI']);
      }
    }

    //public static function before_capture() {
    //}

    public static function after_capture() {

    // Bad Bot Trap - Rig the trap
      if (settings::get('security_bot_trap')) {
        if (document::$layout == 'default') {
          $GLOBALS['content'] = '<a rel="nofollow" href="'. document::link(WS_DIR_HTTP_HOME, array(self::$_trigger['key'] => '')) .'" style="display: none;"><img src="data:image/gif;base64,R0lGODlhAQABAPAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" alt="" style="width: 1px; height: 1px; border: none;" /></a>' . PHP_EOL
                               . $GLOBALS['content'];
        }
      }
    }

    //public static function prepare_output() {
    //}

    //public static function before_output() {
    //}

    //public static function shutdown() {
    //}

    ######################################################################

    public static function is_blacklisted() {

      $blacklisted = false;

      $hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);

      $rows = file(self::$_blacklist);

      foreach (array_keys($rows) as $key) {

        if (preg_match('/^(?:[\s]+)?$/', $rows[$key], $matches)) continue;
        if (preg_match('/^(?:[\s]+)?#/', $rows[$key], $matches)) continue;

        if (preg_match('/\[expires="(.*)"\]/', $rows[$key], $matches)) {
          if ($matches[1] < date('Y-m-d H:i:s')) {
            continue;
          }
        }

        if (preg_match('/\[ip="'. $_SERVER['REMOTE_ADDR'] .'"\]/', $rows[$key], $matches)) {
          $blacklisted = true;
          break;
        }

        if (preg_match('/\[hostname="'. $hostname .'"\]/', $rows[$key], $matches)) {
          $blacklisted = true;
          break;
        }
      }

      if (self::is_whitelisted()) return false;

      return $blacklisted;
    }

    public static function is_whitelisted($list='') {

      $hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
      $longip = ip2long($_SERVER['REMOTE_ADDR']);

      $rows = file(self::$_whitelist);

      foreach (array_keys($rows) as $key) {
        $has_rules = false;

        if (preg_match('/^(?:[\s]+)?$/', $rows[$key], $matches)) continue;
        if (preg_match('/^(?:[\s]+)?#/', $rows[$key], $matches)) continue;

        if (preg_match('/\[expires="(.*)"\]/', $rows[$key], $matches)) {
          if ($matches[1] < date('Y-m-d H:i:s')) {
            continue;
          }
        }

        if (preg_match('/\[ip="([^\]]+)-([^\]]+)"\]/', $rows[$key], $matches)) {
          $has_rules = true;
          if ($longip < ip2long($matches[1]) || $longip > ip2long($matches[2])) continue;
        } else if (preg_match('/\[ip="([^\]]+)"\]/', $rows[$key], $matches)) {
          $has_rules = true;
          if ($matches[1] != $_SERVER['REMOTE_ADDR']) continue;
        }

        if (preg_match('/\[hostname="([^\]]+)"\]/', $rows[$key], $matches)) {
          $has_rules = true;
          if (substr($hostname, 0 - strlen($matches[1])) != $matches[1]) continue;
        }

        if (preg_match('/\[agent="([^\]]+)"\]/i', $rows[$key], $matches)) {
          $has_rules = true;
          if (isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], $matches[1]) === false) continue;
        }

        if (empty($has_rules)) continue;

        return true;
      }

      return false;
    }

    public static function is_accessing_bad_url() {
      $bad_urls = file_get_contents(self::$_bad_urls);
      $bad_urls = preg_replace('#\R+#', "\n", $bad_urls);

      return preg_match('#^/'. preg_quote(route::$request, '#') .'$#m', $bad_urls);
    }

    public static function ban($reason='', $time='') {

      if (self::is_blacklisted()) return;
      if (self::is_whitelisted()) return;
      if (empty($time)) $time = self::$_ban_time;

      $hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);

      error_log('A bad client with a suspected bad behaviour was banned for '. $time .'.' . PHP_EOL
              . (!empty($reason) ? "  Reason: $reason" . PHP_EOL : '')
              . '  URI: '. $_SERVER['REQUEST_URI'] . PHP_EOL
              . '  Address: '. $_SERVER['REMOTE_ADDR'] .' ('. $hostname .')' . PHP_EOL
              . '  Agent: '. $_SERVER['HTTP_USER_AGENT']
              . '  Date: '. date('r')
              , 0);

      $row = $_SERVER['REQUEST_METHOD'] .' '. $_SERVER['REQUEST_URI'] .' '. $_SERVER['SERVER_PROTOCOL'] .' '
           . '[reason="'. htmlspecialchars($reason) .'"]'
           . '[date="'. date('Y-m-d H:i:s') .'"]'
           . '[ip="' . $_SERVER['REMOTE_ADDR'] .'"]'
           . '[hostname="'. $hostname .'"]'
           . '[agent="'. $_SERVER['HTTP_USER_AGENT'] .'"]'
           . '[referer="'. ((!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '') .'"]'
           . '[expires="'. date('Y-m-d H:i:s', strtotime('+ '. self::$_ban_time)) .'"]'
           . PHP_EOL;

      file_put_contents(self::$_blacklist, $row, FILE_APPEND);

      session::clear();
      sleep(3);
      http_response_code(400);
      exit;
    }
  }
