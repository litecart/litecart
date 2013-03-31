<?php

  class volume {
    private $system;
    public $class = '';
    public $classes = array();
    
    public function __construct(&$system) {
      $this->system = &$system;
    }
    
    public function load_dependencies() {
      $this->classes = array(
        'L' => array(
          'name' => 'Litres',
          'unit' => 'L',
          'value' => 1,
          'decimals' => 2,
        ),
        'tbs' => array(
          'name' => 'Tablespoons',
          'unit' => 'tbs',
          'value' => 200,
          'decimals' => 0,
        ),
        'tsp' => array(
          'name' => 'Teaspoons',
          'unit' => 'tsp',
          'value' => 200,
          'decimals' => 0,
        ),
        'dL' => array(
          'name' => 'Decilitres',
          'unit' => 'dL',
          'value' => 0.1,
          'decimals' => 1,
        ),
        'cL' => array(
          'name' => 'Centilitres',
          'unit' => 'cL',
          'value' => 0.01,
          'decimals' => 0,
        ),
        'mL' => array(
          'name' => 'Millilitres',
          'unit' => 'mL',
          'value' => 0.001,
          'decimals' => 0,
        ),
        'm3' => array(
          'name' => 'Cubic Metres',
          'unit' => 'm3',
          'value' => 0.001,
          'decimals' => 3,
        ),
        'dm3' => array(
          'name' => 'Cubic Decimetres',
          'unit' => 'dm3',
          'value' => 1,
          'decimals' => 3,
        ),
        'cm3' => array(
          'name' => 'Cubic Centimetres',
          'unit' => 'cm3',
          'value' => 1000,
          'decimals' => 3,
        ),
        'oz' => array(
          'name' => 'Ounces (US, liquid)',
          'unit' => 'oz',
          'value' => 33.814022701,
          'decimals' => 0,
        ),
        'gal' => array(
          'name' => 'Gallons (US, liquid)',
          'unit' => 'gal',
          'value' =>  0.26417205236,
          'decimals' => 2,
        ),
        'pt' => array(
          'name' => 'Pints (UK, liquid)',
          'unit' => 'pt',
          'value' => 1.7597539864,
          'decimals' => 2,
        ),
        'qt' => array(
          'name' => 'Quarts (US, liquid)',
          'unit' => 'qt',
          'value' => 1.0566882094,
          'decimals' => 2,
        ),
      );
    }
    
    //public function initiate() {
    //}
    
    public function startup() {
      $this->class = &$this->system->customer->data['volume_class'];
    }
    
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
      
      if (!isset($this->classes[$from])) trigger_error('The unit '. $from .' is not a valid volume class.', E_USER_WARNING);
      if (!isset($this->classes[$to])) trigger_error('The unit '. $to .' is not a valid volume class.', E_USER_WARNING);
      
      return $value * ($this->classes[$to]['value'] / $this->classes[$from]['value']);
    }

    public function format($value, $class) {
    
      if (!isset($this->classes[$class])) {
        trigger_error('Invalid volume class ('. $class .')', E_USER_WARNING);
        return;
      }
      
      $num_decimals = $this->classes[$class]['decimals'];
      if (round($value) == $value) $num_decimals = 0;
      
      return number_format($value, $this->classes[$class]['decimals'], $this->system->language->selected['decimal_point'], $this->system->language->selected['thousands_sep']) .' '. $this->classes[$class]['unit'];
    }
  }
  
?>