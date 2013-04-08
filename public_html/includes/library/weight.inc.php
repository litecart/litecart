<?php

  class weight {
    private $system;
    public $class = '';
    public $classes = array();
    
    public function __construct(&$system) {
      $this->system = &$system;
    }
    
    public function load_dependencies() {
      $this->classes = array(
        'kg' => array(
          'name' => 'Kilograms',
          'unit' => 'kg',
          'value' => 1,
          'decimals' => 2,
        ),
        'g' => array(
          'name' => 'grams',
          'unit' => 'g',
          'value' => 1000,
          'decimals' => 0,
        ),
        'lb' => array(
          'name' => 'Pounds',
          'unit' => 'lb',
          'value' => 2.2046,
          'decimals' => 2,
        ),
        'oz' => array(
          'name' => 'Ounces',
          'unit' => 'oz',
          'value' => 35.274,
          'decimals' => 1,
        ),
        'st' => array(
          'name' => 'Stones',
          'unit' => 'st',
          'value' => 0.1575,
        ),
      );
    }
    
    //public function initiate() {
    //}
    
    public function startup() {
      $this->class = &$this->system->customer->data['weight_class'];
    }
    
    //public function before_capture() {
    //}
    
    //public function after_capture() {
    //}
    
    //public function prepare_output() {
    //}
    
    //public function before_output() {
    //}
    
    //public function shutdown() {
    //}
    
    ######################################################################
    
    public function convert($value, $from, $to) {
      
      if ($value == 0) return 0;
      
      if ($from == $to) return $value;
      
      if (!isset($this->classes[$from])) trigger_error('The unit '. $from .' is not a valid weight class.', E_USER_WARNING);
      if (!isset($this->classes[$to])) trigger_error('The unit '. $to .' is not a valid weight class.', E_USER_WARNING);
      
      return $value * ($this->classes[$to]['value'] / $this->classes[$from]['value']);
    }

    public function format($value, $class) {
    
      if (!isset($this->classes[$class])) {
        trigger_error('Invalid weight class ('. $class .')', E_USER_WARNING);
        return;
      }
      
      $num_decimals = $this->classes[$class]['decimals'];
      if (round($value) == $value) $num_decimals = 0;
      
      return number_format($value, $this->classes[$class]['decimals'], $this->system->language->selected['decimal_point'], $this->system->language->selected['thousands_sep']) .' '. $this->classes[$class]['unit'];
    }
  }
  
?>