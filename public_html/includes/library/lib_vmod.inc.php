<?php

  class vmod {

    private static $_time_elapsed = 0;
    private static $_root;

    public static function init() {

      $timestamp = microtime(true);

      self::$_root = rtrim(str_replace('\\', '/', realpath(__DIR__.'/../../')), '/') . '/';

      if (!is_file(self::$_root . 'vqmod/vqmod.php')) return;

      require_once self::$_root . 'vqmod/vqmod.php';

      vqmod::$replaces['#^includes/controllers/ctrl_#'] = 'includes/entities/ent_';

      $config = file_get_contents(self::$_root.'includes/config.inc.php');
      preg_match('#define\(\'BACKEND_ALIAS\',\s+\'(.*?)\'\);#', $config, $matches);
      vqmod::$replaces['#^admin/#'] = $matches[1] . '/';

      vqmod::bootup();

      self::$_time_elapsed += microtime(true) - $timestamp;
    }

    public static function check($file) {

      if (!class_exists('vqmod', false)) return $file;

      $timestamp = microtime(true);

      $file = str_replace('\\', '/', $file);

      if (preg_match('#^('. preg_quote(self::$_root . 'includes/templates/', '#') .'[^/]+)/#', $file, $matches)) {
        if (!file_exists($file)) $file = preg_replace('#^('. preg_quote($matches[1], '#') .')#', self::$_root . 'includes/templates/default.catalog/', $file);
      }

      $modified_file = vqmod::modcheck($file);

      self::$_time_elapsed += microtime(true) - $timestamp;

      return !empty($modified_file) ? $modified_file : $file;
    }

    public static function get_time_elapsed() {
      return self::$_time_elapsed;
    }
  }
