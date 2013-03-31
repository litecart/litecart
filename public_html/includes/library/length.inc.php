<?php

  class length {
    public $classes = array();
    private $system;
    
    public function __construct(&$system) {
      $this->system = &$system;
    }
    
    public function load_dependencies() {
      $this->classes = array(
        'm' => array(
          'name' => 'Metres',
          'unit' => 'm',
          'value' => 1,
        ),
        'dm' => array(
          'name' => 'Decimetres',
          'unit' => 'dm',
          'value' => 10,
        ),
        'cm' => array(
          'name' => 'Centimetres',
          'unit' => 'cm',
          'value' => 100,
        ),
        'mm' => array(
          'name' => 'Millimetres',
          'unit' => 'mm',
          'value' => 1000,
        ),
        'ft' => array(
          'name' => 'Feet',
          'unit' => 'ft',
          'value' => 3.2808,
        ),
        'in' => array(
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
      
      if ($value == 0) return 0;
      
      if ($from == $to) return $value;
      
      if (!isset($this->classes[$from])) trigger_error('The unit '. $from .' is not a valid length class.', E_USER_WARNING);
      if (!isset($this->classes[$to])) trigger_error('The unit '. $to .' is not a valid length class.', E_USER_WARNING);
      
      return $value * ($this->classes[$to]['value'] / $this->classes[$from]['value']);
    }

    public function format($value, $unit) {
    
      if (!isset($this->classes[$unit])) {
        trigger_error('The unit '. $unit .' is not a valid length class.', E_USER_WARNING);
        return;
      }
      
      $num_decimals = $this->classes[$class]['decimals'];
      if (round($value) == $value) $num_decimals = 0;
      
      return number_format($value, 2, $this->system->language->selected['decimal_point'], $this->system->language->selected['thousands_sep']) .' '. $this->classes[$unit]['unit'];
    }
  }
  
?>