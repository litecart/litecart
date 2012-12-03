<?php

  class length {
    public $classes = array();
    private $system;
    
    public function __construct(&$system) {
      $this->system = &$system;
    }
    
    public function load_dependencies() {
      $this->classes = array(
        array(
          'name' => 'Millimeters',
          'unit' => 'mm',
          'value' => 1000,
        ),
        array(
          'name' => 'Centimeters',
          'unit' => 'cm',
          'value' => 100,
        ),
        array(
          'name' => 'Meters',
          'unit' => 'm',
          'value' => 1,
        ),
        array(
          'name' => 'Feet',
          'unit' => 'ft',
          'value' => 3.2808,
        ),
        array(
          'name' => 'Inches',
          'unit' => 'in',
          'value' => 39.37,
        ),
      );
    }
    
    //public function initiate() {
    //}
    
    //public function startup() {
    //}
    
    //public function before_capture() {
    //}
    
    //public function after_capture() {
    //}
    
    //public function prepare_output() {
    //}
    
    public function before_output() {
    }
    
    //public function shutdown() {
    //}
    
    ######################################################################
    
    public function convert($value, $from, $to) {
      if ($from == $to) {
        return $value;
      }
      
      if (isset($this->classes[$from])) trigger_error('The unit '. $from .' is not a valid length unit.', E_USER_WARNING);
      if (isset($this->classes[$to])) trigger_error('The unit '. $to .' is not a valid length unit.', E_USER_WARNING);
      
      return $value * ($to['value'] / $from['value']);
    }

    public function format($value, $unit) {
    
      if (isset($this->classes[$unit])) trigger_error('The unit '. $unit .' is not a valid length unit.', E_USER_WARNING);
      
      return number_format($value, 2, $this->system->language->decimal_point, $this->system->language->thousands_sep) .' '. $this->cache[$unit]['unit'];
    }
    
    public function draw_select_field($name, $input='', $parameters) {
    
      $options = array();
      
      if ($input == '') $input = $this->system->settings->get('store_length_class');
      
      foreach ($this->classes as $class) {
        $options[] = array($class['unit']);
      }
      
      echo $this->system->functions->form_draw_select_field($name, $options, $input, false, false, $parameters);
    }

  }
  
?>