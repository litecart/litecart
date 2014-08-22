<?php
  class view {
    public $snippets = array();
    public $html = '';
    
    public function load($view) {
      $file = FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . document::$template .'/layouts/'. $view .'.inc.php';
      $html = $this->_process_view($file, $snippets);
    }
    
    
    public function stitch($view=null) {
      
      if ($view !== null) {
        $file = FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . document::$template .'/layouts/'. $view .'.inc.php';
        $this->html = $this->_process_view($file, $this->snippets);
      }
      
      if (empty($this->html)) return;
      
      if (!empty($this->snippets)) {
        
        $search_replace = array();
        foreach (array_keys($this->snippets) as $key) {
          if (!is_string($this->snippets[$key])) continue;
          $search_replace['#(<!--snippet:'. preg_quote($key, '#') .'-->|\{\$'. preg_quote($key, '#') .'\}|\{snippet:'. preg_quote($key, '#') .'\})#'] = &$this->snippets[$key];
        }
        
        while (!isset($count) || $count > 0) {
          $this->html = preg_replace(array_keys($search_replace), array_values($search_replace), $this->html, -1, $count);
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
      include vqmod::modcheck($_file);
      $html = ob_get_clean();
      
      return $html;
    }
  }
?>