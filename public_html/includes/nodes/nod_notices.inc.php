<?php

  class notices {

    public static $data;

    public static function init() {
      if (empty(session::$data['notices'])) {
        session::$data['notices'] = [
          'errors' => [],
          'warnings' => [],
          'notices' => [],
          'success' => [],
        ];
      }

      self::$data = &session::$data['notices'];

      event::register('after_capture', [__CLASS__, 'after_capture']);
    }

    public static function after_capture() {

      notices::$data = array_filter(notices::$data);

      if (!empty(notices::$data)) {
        $notices = new ent_view(FS_DIR_TEMPLATE . 'partials/notices.inc.php');
        $notices->snippets['notices'] = notices::$data;
        document::$snippets['notices'] = $notices;
        self::reset();
      }
    }

    ######################################################################

    public static function reset(string $type='') {

      if ($type) {
        self::$data[$type] = [];

      } else {
        if (!empty(self::$data)) {
          foreach (self::$data as $type => $container) {
            self::$data[$type] = [];
          }
        }
      }
    }

    public static function add(string $type, string $msg, string $key='') {
      if ($key) self::$data[$type][$key] = $msg;
      else self::$data[$type][] = $msg;
    }

    public static function remove(string $type, string $key) {
      unset(self::$data[$type][$key]);
    }

    public static function get(string $type) {
      if (!isset(self::$data[$type])) return false;
      return self::$data[$type];
    }

    public static function dump(string $type) {
      $stack = self::$data[$type];
      self::$data[$type] = [];
      return $stack;
    }
  }
