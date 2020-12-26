<?php

  class length {

    public static $classes = [
      'm' => [
        'name' => 'Metres',
        'unit' => 'm',
        'value' => 1,
        'decimals' => 2,
      ],
      'cm' => [
        'name' => 'Centimetres',
        'unit' => 'cm',
        'value' => 100,
        'decimals' => 0,
      ],
      'dm' => [
        'name' => 'Decimetres',
        'unit' => 'dm',
        'value' => 10,
        'decimals' => 2,
      ],
      'ft' => [
        'name' => 'Feet',
        'unit' => 'ft',
        'value' => 3.2808,
        'decimals' => 2,
      ],
      'in' => [
        'name' => 'Inches',
        'unit' => 'in',
        'value' => 39.37,
        'decimals' => 2,
      ],
      'km' => [
        'name' => 'Kilometres',
        'unit' => 'km',
        'value' => 0.001,
        'decimals' => 2,
      ],
      'mi' => [
        'name' => 'Miles',
        'unit' => 'mi',
        'value' => 0.00062137119224,
        'decimals' => 2,
      ],
      'mm' => [
        'name' => 'Millimetres',
        'unit' => 'mm',
        'value' => 1000,
        'decimals' => 0,
      ],
      'yd' => [
        'name' => 'Yards',
        'unit' => 'yd',
        'value' => 1.0936133,
        'decimals' => 2,
      ],
    ];

    public static function convert($value, $from, $to) {

      if ($value == 0) return 0;

      if ($from == $to) return (float)$value;

      if (!isset(self::$classes[$from])) {
        trigger_error('The unit '. $from .' is not a valid length class.', E_USER_WARNING);
        return;
      }

      if (!isset(self::$classes[$to])) {
        trigger_error('The unit '. $to .' is not a valid length class.', E_USER_WARNING);
        return;
      }

      if (self::$classes[$from]['value'] == 0 || self::$classes[$to]['value'] == 0) return;

      return $value * (self::$classes[$to]['value'] / self::$classes[$from]['value']);
    }

    public static function format($value, $class) {

      if (!isset(self::$classes[$class])) {
        trigger_error('The unit '. $class .' is not a valid length class.', E_USER_WARNING);
        return;
      }

      $decimals = self::$classes[$class]['decimals'];

      return number_format((float)$value, (int)$decimals, language::$selected['decimal_point'], language::$selected['thousands_sep']) .' '. self::$classes[$class]['unit'];
    }
  }
