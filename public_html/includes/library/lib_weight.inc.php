<?php

  class weight {

    public static $classes = array(
      'kg' => array(
        'name' => 'Kilograms',
        'unit' => 'kg',
        'value' => 1,
        'decimals' => 2,
      ),
      'g' => array(
        'name' => 'Grams',
        'unit' => 'g',
        'value' => 1000,
        'decimals' => 0,
      ),
      'dwt' => array(
        'name' => 'Pennyweights',
        'unit' => 'dwt',
        'value' => 643.01493137256,
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
        'decimals' => 2,
      ),
    );

    public static function convert($value, $from, $to) {

      if ($value == 0) return 0;

      if ($from == $to) return (float)$value;

      if (!isset(self::$classes[$from])) trigger_error('The unit '. $from .' is not a valid weight class.', E_USER_WARNING);
      if (!isset(self::$classes[$to])) trigger_error('The unit '. $to .' is not a valid weight class.', E_USER_WARNING);

      return $value * (self::$classes[$to]['value'] / self::$classes[$from]['value']);
    }

    public static function format($value, $class) {

      if (!isset(self::$classes[$class])) {
        trigger_error('Invalid weight class ('. $class .')', E_USER_WARNING);
        return;
      }

      $num_decimals = self::$classes[$class]['decimals'];
      if (round($value) == $value) $num_decimals = 0;

      return number_format($value, self::$classes[$class]['decimals'], language::$selected['decimal_point'], language::$selected['thousands_sep']) .' '. self::$classes[$class]['unit'];
    }
  }
