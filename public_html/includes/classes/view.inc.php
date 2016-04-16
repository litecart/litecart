<?php
  class view {
    public $snippets = array();
    public $html = '';
    
    public function stitch($view=null, $cleanup=false) {
      
      if (!empty($view)) {
        $file = FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . document::$template .'/'. $view .'.inc.php';
        if (!is_file($file)) $file = FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.catalog/'. $view .'.inc.php';
        $this->html = $this->_process_view($file, $this->snippets);
      }
      
      if (empty($this->html)) return null;
      
      if (!empty($this->snippets)) {
        
        $search_replace = array();
        foreach (array_keys($this->snippets) as $key) {
          if (!is_string($this->snippets[$key])) continue;
          $search_replace['<!--snippet:'.$key.'-->'] = &$this->snippets[$key];
          $search_replace['{$'.$key.'}'] = &$this->snippets[$key];
          $search_replace['{snippet:'.$key.'}'] = &$this->snippets[$key];
        }
        
        foreach(array_keys($search_replace) as $key) {
          $search_replace[$key] = str_replace(array_keys($search_replace), array_values($search_replace), $search_replace[$key]);
        }

        $this->html = str_replace(array_keys($search_replace), array_values($search_replace), $this->html, $count);
      }
      
    // Clean orphan snippets
      if ($cleanup) {
        $search = array(
          '/\{snippet:[^\}]+\}/',
          '/<!--snippet:[^-->]+-->/',
        );

        $this->html = preg_replace($search, '', $this->html);
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