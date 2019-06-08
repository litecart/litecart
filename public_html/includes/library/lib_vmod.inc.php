<?php
  class vmod {

    public static function check($file) {

      $file = str_replace('\\', '/', $file);

      if (preg_match('#^('. preg_quote(FS_DIR_APP . 'includes/templates/', '#') .'[^/]+)/#', $file, $matches)) {
        if (!file_exists($file)) $file = preg_replace('#^('. preg_quote($matches[1], '#') .')#', FS_DIR_APP . 'includes/templates/default.catalog/', $file);
      }

      if (!class_exists('vqmod', false)) {
        if (is_file(FS_DIR_APP . 'vqmod/vqmod.php')) {
          require_once FS_DIR_APP . 'vqmod/vqmod.php';
          vqmod::$replaces['#^(admin/)#'] = substr(WS_DIR_ADMIN, strlen(WS_DIR_APP));
          vqmod::$replaces['#^(includes/controllers/ctrl_)#'] = 'includes/entities/ent_';
          vqmod::bootup(FS_DIR_APP, true);
        }
      }

      if (class_exists('vqmod', false)) {
        return vqmod::modcheck($file);
      }

      return $file;
    }
  }
