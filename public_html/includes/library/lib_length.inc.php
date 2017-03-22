<?php

  class length {
    public static $classes = array();

    public static function construct() {
    }

    public static function load_dependencies() {
      self::$classes = array(
        'm' => array(
          'name' => 'Metres',
          'unit' => 'm',
          'value' => 1,
          'decimals' => 2,
        ),
        'cm' => array(
          'name' => 'Centimetres',
          'unit' => 'cm',
          'value' => 100,
          'decimals' => 0,
        ),
        'dm' => array(
          'name' => 'Decimetres',
          'unit' => 'dm',
          'value' => 10,
          'decimals' => 2,
        ),
        'ft' => array(
          'name' => 'Feet',
          'unit' => 'ft',
          'value' => 3.2808,
          'decimals' => 2,
        ),
        'in' => array(
          'name' => 'Inches',
          'unit' => 'in',
          'value' => 39.37,
          'decimals' => 2,
        ),
        'km' => array(
          'name' => 'Kilometres',
          'unit' => 'km',
          'value' => 0.001,
          'decimals' => 2,
        ),
        'mi' => array(
          'name' => 'Miles',
          'unit' => 'mi',
          'value' => 0.00062137119224,
          'decimals' => 2,
        ),
        'mm' => array(
          'name' => 'Millimetres',
          'unit' => 'mm',
          'value' => 1000,
          'decimals' => 0,
        ),
        'yd' => array(
          'name' => 'Yards',
          'unit' => 'yd',
          'value' => 1.0936133,
          'decimals' => 2,
        ),
      );
    }

    //public static function initiate() {
    //}

    //public static function startup() {
    //}

    //public static function before_capture() {
    //}

    //public static function after_capture() {
    //}

    //public static function prepare_output() {
    //}

    //public static function before_output() {
    //}

    //public static function shutdown() {
    //}

    ######################################################################

    public static function convert($value, $from, $to) {

      if ($value == 0) return 0;

      if ($from == $to) return $value;

      if (!isset(self::$classes[$from])) trigger_error('The unit '. $from .' is not a valid length class.', E_USER_WARNING);
      if (!isset(self::$classes[$to])) trigger_error('The unit '. $to .' is not a valid length class.', E_USER_WARNING);

      return $value * (self::$classes[$to]['value'] / self::$classes[$from]['value']);
    }

    public static function format($value, $unit) {

      if (!isset(self::$classes[$unit])) {
        trigger_error('The unit '. $unit .' is not a valid length class.', E_USER_WARNING);
        return;
      }

      $num_decimals = self::$classes[$class]['decimals'];
      if (round($value) == $value) $num_decimals = 0;

      return number_format($value, language::$selected['decimal_point'], language::$selected['thousands_sep']) .' '. self::$classes[$unit]['unit'];
    }
  }
