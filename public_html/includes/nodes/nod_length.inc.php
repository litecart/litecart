<?php

  class length {

    public static $units = [
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
        'decimals' => 1,
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

    public static function convert($value, $from='', $to='') {

      if ((float)$value == 0) return 0;

      if ($from == $to) return (float)$value;

      if (!isset(self::$units[$from])) {
        $from = settings::get('store_length_unit');
      }

      if (!isset(self::$units[$to])) {
        $to = settings::get('store_length_unit');
      }

      if ((float)self::$units[$from]['value'] == 0 || (float)self::$units[$to]['value'] == 0){
        return 0;
      }

      return $value * (self::$units[$to]['value'] / self::$units[$from]['value']);
    }

    public static function format($value, $unit) {

      if (!isset(self::$units[$unit])) {
        trigger_error('Invalid length unit ('. $unit .')', E_USER_WARNING);
        return;
      }

      $decimals = self::$units[$unit]['decimals'];

      $formatted_value = language::number_format((float)$value, (int)$decimals) .' '. self::$units[$unit]['unit'];
      $formatted_value = preg_replace('#'. preg_quote(language::$selected['decimal_point'], '#') .'0+$#', '', $formatted_value);

      return $formatted_value;
    }
  }
