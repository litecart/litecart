<?php

  class volume {

    public static $classes = array(
      'L' => array(
        'name' => 'Litres',
        'unit' => 'L',
        'value' => 1,
        'decimals' => 2,
      ),
      'cL' => array(
        'name' => 'Centilitres',
        'unit' => 'cL',
        'value' => 0.01,
        'decimals' => 0,
      ),
      'dL' => array(
        'name' => 'Decilitres',
        'unit' => 'dL',
        'value' => 0.1,
        'decimals' => 1,
      ),
      'dm3' => array(
        'name' => 'Cubic Decimetres',
        'unit' => 'dm3',
        'value' => 1,
        'decimals' => 2,
      ),
      'cm3' => array(
        'name' => 'Cubic Centimetres',
        'unit' => 'cm3',
        'value' => 1000,
        'decimals' => 3,
      ),
      'ft3' => array(
        'name' => 'Cubic Feet',
        'unit' => 'ft3',
        'value' => 0.035314666721,
        'decimals' => 0,
      ),
      'gal' => array(
        'name' => 'Gallons (US, liquid)',
        'unit' => 'gal',
        'value' =>  0.26417205236,
        'decimals' => 2,
      ),
      'in3' => array(
        'name' => 'Cubic Inches',
        'unit' => 'in3',
        'value' => 61.023744095,
        'decimals' => 0,
      ),
      'm3' => array(
        'name' => 'Cubic Metres',
        'unit' => 'm3',
        'value' => 0.001,
        'decimals' => 3,
      ),
      'mL' => array(
        'name' => 'Millilitres',
        'unit' => 'mL',
        'value' => 0.001,
        'decimals' => 0,
      ),
      'oz' => array(
        'name' => 'Ounces (US, liquid)',
        'unit' => 'oz',
        'value' => 33.814022701,
        'decimals' => 0,
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
      'tbs' => array(
        'name' => 'Tablespoons',
        'unit' => 'tbs',
        'value' => 66.666666667,
        'decimals' => 0,
      ),
      'tsp' => array(
        'name' => 'Teaspoons',
        'unit' => 'tsp',
        'value' => 200,
        'decimals' => 0,
      ),
      'yd3' => array(
        'name' => 'Cubic Yards',
        'unit' => 'yd3',
        'value' => 0.0013079506193,
        'decimals' => 3,
      ),
    );

    public static function convert($value, $from, $to) {

      if ($value == 0) return 0;

      if ($from == $to) return (float)$value;

      if (!isset(self::$classes[$from])) {
        trigger_error('The unit '. $from .' is not a valid volume class.', E_USER_WARNING);
        return;
      }

      if (!isset(self::$classes[$to])) {
        trigger_error('The unit '. $to .' is not a valid volume class.', E_USER_WARNING);
        return;
      }

      if (self::$classes[$from]['value'] == 0 || self::$classes[$to]['value'] == 0) return;

      return $value * (self::$classes[$to]['value'] / self::$classes[$from]['value']);
    }

    public static function format($value, $class) {

      if (!isset(self::$classes[$class])) {
        trigger_error('Invalid volume class ('. $class .')', E_USER_WARNING);
        return;
      }

      $decimals = self::$classes[$class]['decimals'];
      $formatted = rtrim(rtrim(number_format((float)$value, (int)$decimals, language::$selected['decimal_point'], language::$selected['thousands_sep']), '0'), language::$selected['decimal_point']);

      return $formatted .' '. self::$classes[$unit]['unit'];
    }
  }
