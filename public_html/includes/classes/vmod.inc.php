<?php
  class vmod {
    
    public static function check($file) {
    
      if (!class_exists('vqmod', false)) {
        if (is_file(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'vqmod/vqmod.php')) {
          require_once FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'vqmod/vqmod.php';
          vqmod::$replaces['#^(admin/)#'] = substr(WS_DIR_ADMIN, strlen(WS_DIR_HTTP_HOME));
          vqmod::bootup(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME, true);
        }
      }
      
      if (class_exists('vqmod', false)) {
        return vqmod::modcheck($file);
      }
      
      return $file;
    }
  }
?>