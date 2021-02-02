<?php

  class volume {

    public static $classes = [
      'L' => [
        'name' => 'Litres',
        'unit' => 'L',
        'value' => 1,
        'decimals' => 2,
      ],
      'cL' => [
        'name' => 'Centilitres',
        'unit' => 'cL',
        'value' => 0.01,
        'decimals' => 0,
      ],
      'dL' => [
        'name' => 'Decilitres',
        'unit' => 'dL',
        'value' => 0.1,
        'decimals' => 1,
      ],
      'dm3' => [
        'name' => 'Cubic Decimetres',
        'unit' => 'dm3',
        'value' => 1,
        'decimals' => 2,
      ],
      'cm3' => [
        'name' => 'Cubic Centimetres',
        'unit' => 'cm3',
        'value' => 1000,
        'decimals' => 3,
      ],
      'ft3' => [
        'name' => 'Cubic Feet',
        'unit' => 'ft3',
        'value' => 0.035314666721,
        'decimals' => 0,
      ],
      'gal' => [
        'name' => 'Gallons (US, liquid)',
        'unit' => 'gal',
        'value' =>  0.26417205236,
        'decimals' => 2,
      ],
      'in3' => [
        'name' => 'Cubic Inches',
        'unit' => 'in3',
        'value' => 61.023744095,
        'decimals' => 0,
      ],
      'm3' => [
        'name' => 'Cubic Metres',
        'unit' => 'm3',
        'value' => 0.001,
        'decimals' => 3,
      ],
      'mL' => [
        'name' => 'Millilitres',
        'unit' => 'mL',
        'value' => 0.001,
        'decimals' => 0,
      ],
      'oz' => [
        'name' => 'Ounces (US, liquid)',
        'unit' => 'oz',
        'value' => 33.814022701,
        'decimals' => 0,
      ],
      'pt' => [
        'name' => 'Pints (UK, liquid)',
        'unit' => 'pt',
        'value' => 1.7597539864,
        'decimals' => 2,
      ],
      'qt' => [
        'name' => 'Quarts (US, liquid)',
        'unit' => 'qt',
        'value' => 1.0566882094,
        'decimals' => 2,
      ],
      'tbs' => [
        'name' => 'Tablespoons',
        'unit' => 'tbs',
        'value' => 66.666666667,
        'decimals' => 0,
      ],
      'tsp' => [
        'name' => 'Teaspoons',
        'unit' => 'tsp',
        'value' => 200,
        'decimals' => 0,
      ],
      'yd3' => [
        'name' => 'Cubic Yards',
        'unit' => 'yd3',
        'value' => 0.0013079506193,
        'decimals' => 3,
      ],
    ];

    public static function convert($value, $from, $to) {

      if ((float)$value == 0) return 0;

      if ($from == $to) return (float)$value;

      if (!isset(self::$classes[$from])) {
        trigger_error('The unit '. $from .' is not a valid volume class.', E_USER_WARNING);
        return;
      }

      if (!isset(self::$classes[$to])) {
        trigger_error('The unit '. $to .' is not a valid volume class.', E_USER_WARNING);
        return;
      }

      if ((float)self::$classes[$from]['value'] == 0 || (float)self::$classes[$to]['value'] == 0) return;

      return $value * (self::$classes[$to]['value'] / self::$classes[$from]['value']);
    }

    public static function format($value, $class) {

      if (!isset(self::$classes[$class])) {
        trigger_error('Invalid volume class ('. $class .')', E_USER_WARNING);
        return;
      }

      $decimals = self::$classes[$class]['decimals'];

      $formatted_value = number_format((float)$value, (int)$decimals, language::$selected['decimal_point'], language::$selected['thousands_sep']) .' '. self::$classes[$class]['unit'];
      $formatted_value = preg_replace('#'. preg_quote(language::$selected['decimal_point'], '#') .'0+$#', '', $formatted_value);

      return $formatted_value;
    }
  }
