<?php

  class weight {

    public static $units = [
      'kg' => [
        'name' => 'Kilograms',
        'unit' => 'kg',
        'value' => 1,
        'decimals' => 2,
      ],
      'g' => [
        'name' => 'Grams',
        'unit' => 'g',
        'value' => 1000,
        'decimals' => 0,
      ],
      'dwt' => [
        'name' => 'Pennyweights',
        'unit' => 'dwt',
        'value' => 643.01493137256,
        'decimals' => 0,
      ],
      'lb' => [
        'name' => 'Pounds',
        'unit' => 'lb',
        'value' => 2.2046,
        'decimals' => 2,
      ],
      'oz' => [
        'name' => 'Ounces',
        'unit' => 'oz',
        'value' => 35.274,
        'decimals' => 1,
      ],
      'st' => [
        'name' => 'Stones',
        'unit' => 'st',
        'value' => 0.1575,
        'decimals' => 2,
      ],
    ];

    public static function convert(float $value, string $from='', string $to='') {

      if ($value == 0) return 0;

      if ($from == $to) return $value;

      if (!isset(self::$units[$from])) {
        $from = settings::get('site_weight_unit');
      }

      if (!isset(self::$units[$to])) {
        $to = settings::get('site_weight_unit');
      }

      if (self::$units[$from]['value'] == 0 || self::$units[$to]['value'] == 0) return 0;

      return $value * (self::$units[$to]['value'] / self::$units[$from]['value']);
    }

    public static function format(float $value, string $unit) {

      if (!isset(self::$units[$class])) {
        trigger_error('Invalid weight unit ('. $unit .')', E_USER_WARNING);
        return;
      }

      $decimals = self::$units[$class]['decimals'];

      $formatted_value = number_format($value, $decimals, language::$selected['decimal_point'], language::$selected['thousands_sep']) .' '. self::$units[$class]['unit'];
      $formatted_value = preg_replace('#'. preg_quote(language::$selected['decimal_point'], '#') .'0+$#', '', $formatted_value);

      return $formatted_value;
    }
  }
