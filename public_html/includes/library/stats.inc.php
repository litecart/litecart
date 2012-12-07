<?php
  
  class stats {
  
    private $data;
    private $page_parse_start;
    
    private $system;
    
    public function __construct(&$system) {
      $this->system = &$system;
    
    // Set time stamp for execution
      $this->page_parse_start = microtime(true);
    }
    
    //public function load_dependencies() {
    //}
    
    //public function startup() {
    //}
    
    //public function before_capture() {
    //}
    
    public function capture() {
      $this->capture_parse_start = microtime(true);
    }
    
    public function after_capture() {
      if ($this->get('page_parse_time') > 5) {
        $this->system->notices->add('warnings', sprintf($this->system->language->translate('text_long_execution_time', 'We apologize for the inconvenience that the server seems temporary overloaded right now.'), number_format($page_parse_time, 1, ',', ' ')));
        error_log('Warning: Long page execution time '. number_format($page_parse_time, 3, ',', ' ') .' s - '. $_SERVER['REQUEST_URI']);
      }
    }
    
    public function prepare_output() {
    
    // Capture parse time
      $page_parse_time = microtime(true) - $this->page_parse_start;
      $this->set('page_capture_time', $page_parse_time);
      
    // Memory peak usage
      $this->set('memory_peak_usage', memory_get_peak_usage() / 1024 / 1024);
      
    // Page parse time
      $page_parse_time = microtime(true) - $this->page_parse_start;
      $this->set('page_parse_time', $page_parse_time);
      
    // Add stats to snippet
      $this->system->document->snippets['stats'] = '<p><strong>System Statistics:</strong></p>' . PHP_EOL
                                           . '<p>'. $this->system->language->translate('title_page_parse_time', 'Page Parse Time') .': ' . number_format($this->get('page_parse_time'), 6, '.', ' ') . ' s<br />' . PHP_EOL
                                           . '  '. $this->system->language->translate('title_page_capture_time', 'Page Capture Time') .': ' . number_format($this->get('page_capture_time'), 6, '.', ' ') . ' s<br />' . PHP_EOL
                                           . '  '. $this->system->language->translate('title_included_files', 'Included Files') .': ' . count(get_included_files()) . '<br />' . PHP_EOL
                                           . '  '. $this->system->language->translate('title_memory_limit', 'Memory Limit') .': ' . ini_get('memory_limit') . '<br />' . PHP_EOL
                                           . '  '. $this->system->language->translate('title_memory_peak', 'Memory Peak') .': ' . number_format($this->get('memory_peak_usage'), 2, '.', ' ') . ' MB<br />' . PHP_EOL
                                           . '  '. $this->system->language->translate('title_database_queries', 'Database Queries') .': ' . number_format($this->get('database_queries'), 0, '.', ' ') . ' queries<br />' . PHP_EOL
                                           . '  '. $this->system->language->translate('title_database_parse_time', 'Database Parse Time') .': ' . number_format($this->get('database_execution_time'), 6, '.', ' ') . ' s (' . number_format($this->get('database_execution_time')/$this->get('page_parse_time')*100, 0, '.', ' ') . ' %)<br />' . PHP_EOL
                                           . '</p>';
    }
    
    public function before_output() {
    }
    
    //public function shutdown() {
    //}
    
    ######################################################################
    
    public function set($key, $value) {
      $this->data[$key] = $value;
    }
    
    public function get($key) {
      if (isset($this->data[$key])) return $this->data[$key];
    }
  }
  
?>