<?php
  class view {
    public $snippets = array();
    public $html = '';
    
    public function load($view) {
      $file = FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . document::$template .'/'. $view .'.inc.php';
      $html = $this->_process_view($file, $snippets);
    }
    
    public function stitch($view=null) {
      
      if ($view !== null) {
        $file = FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . document::$template .'/'. $view .'.inc.php';
        if (!is_file($file)) $file = FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/'. $view .'.inc.php';
        $this->html = $this->_process_view($file, $this->snippets);
      }
      
      if (empty($this->html)) return;
      
      if (!empty($this->snippets)) {
        
        $search_replace = array();
        foreach (array_keys($this->snippets) as $key) {
          if (!is_string($this->snippets[$key])) continue;
          $search_replace['<!--snippet:'.$key.'-->'] = &$this->snippets[$key];
          $search_replace['{$'.$key.'}'] = &$this->snippets[$key];
          $search_replace['{snippet:'.$key.'}'] = &$this->snippets[$key];
        }
        
        $failsafe_count = 0;
        while (!isset($count) || $count > 0) {
          $this->html = str_replace(array_keys($search_replace), array_values($search_replace), $this->html, $count);
          if (++$failsafe_count == 3) {
            trigger_error('Failsafe activated due to possible endless loop while stitching content' . PHP_EOL . print_r($this->snippets, true), E_USER_NOTICE);
            break;
          }
        }
      }
      
      return $this->html;
    }
    
  // Method to process isolated PHP logic in a view
    private function _process_view($_file) {
    
    // Extract snippets
      if (!empty($this->snippets)) {
        extract($this->snippets);
      }
      
      ob_start();
      include vmod::check($_file);
      $html = ob_get_clean();
      
      return $html;
    }
  }
?>