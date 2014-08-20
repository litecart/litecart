<?php
  class view {
    public $snippets = array();
    public $html = '';
    
    public function load($view) {
      $file = FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . document::$template .'/layouts/'. $view .'.inc.php';
      $html = $this->_process_view($file, $snippets);
    }
    
    public function stitch($type, $view=null) {
      
      if ($view !== null) {
        $file = FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . document::$template .'/layouts/'. $view .'.inc.php';
        $this->html = $this->_process_view($file, $this->snippets);
      }
      
    // Compatibility with old snippets syntax
      if (!empty($this->snippets)) {
        if (preg_match_all('/(<!--snippet:.*-->|\{\$.*\}|\{snippet:.*\})/', $this->html, $matches)) {
          
          $matches[0] = array_unique($matches[0]);
          foreach ($matches[0] as $match) {
            
            $key = preg_replace(array('/<!--snippet:([^-->]+)-->/', '/\{\$([^\}]+)\}/', '/\{snippet:([^\}]+)\}/'), '$1', $match);
            
            if (isset($this->snippets[$key])) {
              if (is_array($this->snippets[$key])) {
                $this->html = str_replace($match, implode(PHP_EOL, $this->snippets[$key]), $this->html);
              } else {
                $this->html = str_replace($match, $this->snippets[$key], $this->html);
              }
            }
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
      include vqmod::modcheck($_file);
      $html = ob_get_clean();
      
      return $html;
    }
  }
?>