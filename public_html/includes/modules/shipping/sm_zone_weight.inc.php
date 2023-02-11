<?php

  #[AllowDynamicProperties]
  class sm_zone_weight {
    public $name = 'Zone Based Shipping';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $version = '1.0';
    public $website = 'https://www.litecart.net';

    public function __construct() {
      $this->name = language::translate(__CLASS__.':title_zone_based_shipping', 'Zone Based Shipping');
    }

    public function options($items, $subtotal, $tax, $currency_code, $customer) {

      if (empty($this->settings['status'])) return;

    // Calculate cart weight
      $total_weight = 0;
      foreach ($items as $item) {
        $total_weight += weight::convert($item['quantity'] * $item['weight'], $item['weight_unit'], $this->settings['weight_unit']);
      }

      $options = [];

      for ($i=1; $i <= 3; $i++) {

        if (empty($this->settings['geo_zone_id_'.$i])) continue;
        if (!reference::country($customer['shipping_address']['country_code'])->in_geo_zone($this->settings['geo_zone_id_'.$i], $customer['shipping_address'])) continue;

        $fee = $this->calculate_fee($this->settings['weight_rate_table_'.$i], $total_weight);

        $options[] = [
          'id' => 'zone_'.$i,
          'icon' => $this->settings['icon'],
          'name' => language::translate(__CLASS__.':title_option_name_zone_'.$i, $this->name),
          'description' => language::translate(__CLASS__.':title_option_description_zone_'.$i, ''),
          'fields' => '',
          'fee' => $fee,
          'tax_class_id' => $this->settings['tax_class_id'],
          'exclude_cheapest' => false,
        ];
      }

      if (empty($options)) {

        if (empty($this->settings['weight_rate_table_x'])) return;

        $fee = $this->calculate_fee($this->settings['weight_rate_table_x'], $total_weight);

        $options[] = [
          'id' => 'zone_x',
          'icon' => $this->settings['icon'],
          'name' => language::translate(__CLASS__.':title_option_name_zone_x', $this->name),
          'description' => language::translate(__CLASS__.':title_option_description_zone_x', ''),
          'fields' => '',
          'cost' => (float)$cost + (float)$this->settings['handling_fee'],
          'tax_class_id' => $this->settings['tax_class_id'],
        ];
      }

      return $options;
    }

    private function calculate_fee($rate_table, $shipping_weight) {

      if (empty($rate_table)) return 0;

      $fee = 0;

      switch ($this->settings['method']) {

        case '<':
        case '&lt;':
        case 'ITEM_WEIGHT_LOWER_THAN_VALUE':
          foreach (array_reverse(preg_split('#[\|;]#', $rate_table, -1, PREG_SPLIT_NO_EMPTY)) as $rate) {
            list($rate_weight, $rate_fee) = explode(':', $rate);
            if ($shipping_weight < $rate_weight) {
              $fee = $rate_fee;
            }
          }
          break;

        case '<=':
        case '&lt;=':
        case 'ITEM_WEIGHT_LOWER_THAN_OR_EQUALS_VALUE':
          foreach (array_reverse(preg_split('#[\|;]#', $rate_table, -1, PREG_SPLIT_NO_EMPTY)) as $rate) {
            list($rate_weight, $rate_fee) = explode(':', $rate);
            if ($shipping_weight <= $rate_weight) {
              $fee = $rate_fee;
            }
          }
          break;

        case '>':
        case '&gt;':
        case 'ITEM_WEIGHT_HIGHER_THAN_VALUE':
          foreach (preg_split('#[|;]#', $rate_table, -1, PREG_SPLIT_NO_EMPTY) as $rate) {
            list($rate_weight, $rate_fee) = explode(':', $rate);
            if ($shipping_weight > $rate_weight) {
              $fee = $rate_fee;
            }
          }
          break;

        case '>=':
        case '&gt;=':
        case 'ITEM_WEIGHT_HIGHER_THAN_OR_EQUALS_VALUE':
        default:
          foreach (preg_split('#[|;]#', $rate_table, -1, PREG_SPLIT_NO_EMPTY) as $rate) {
            list($rate_weight, $rate_fee) = explode(':', $rate);
            if ($shipping_weight >= $rate_weight) {
              $fee = $rate_fee;
            }
          }
          break;
      }

      return $fee;
    }

    public function select() {}

    public function after_process() {}

    public function settings() {
      return [
        [
          'key' => 'status',
          'default_value' => '1',
          'title' => language::translate(__CLASS__.':title_status', 'Status'),
          'description' => language::translate(__CLASS__.':description_status', ''),
          'function' => 'toggle("e/d")',
        ],
        [
          'key' => 'icon',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_icon', 'Icon'),
          'description' => language::translate(__CLASS__.':description_icon', 'Path to an image to be displayed.'),
          'function' => 'text()',
        ],
        [
          'key' => 'weight_unit',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_weight_unit', 'Weight Unit'),
          'description' => language::translate(__CLASS__.':description_weight_unit', 'The weight unit for the rate table.'),
          'function' => 'weight_unit()',
        ],
        [
          'key' => 'geo_zone_id_1',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_zone', 'Zone') .' 1: '. language::translate(__CLASS__.':title_geo_zone', 'Geo Zone'),
          'description' => language::translate(__CLASS__.':description_geo_zone', 'Geo zone to which the fee applies.'),
          'function' => 'geo_zone()',
        ],
        [
          'key' => 'weight_rate_table_1',
          'default_value' => '5:8.95;10:15.95',
          'title' => language::translate(__CLASS__.':title_zone', 'Zone') .' 1: '. language::translate(__CLASS__.':title_weight_rate_table', 'Weight Rate Table'),
          'description' => language::translate(__CLASS__.':description_weight_rate_table', 'Ascending rate table of the shipping fee. The format must be weight:fee;weight:fee;.. (E.g. 5:8.95;10:15.95;..)'),
          'function' => 'text()',
        ],
        [
          'key' => 'geo_zone_id_2',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_zone', 'Zone') .' 2: '. language::translate(__CLASS__.':title_geo_zone', 'Geo Zone'),
          'description' => language::translate(__CLASS__.':description_geo_zone', 'Geo zone to which the fee applies.'),
          'function' => 'geo_zone()',
        ],
        [
          'key' => 'weight_rate_table_2',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_zone', 'Zone') .' 2: '. language::translate(__CLASS__.':title_weight_rate_table', 'Weight Rate Table'),
          'description' => language::translate(__CLASS__.':description_weight_rate_table', 'Ascending rate table of the shipping fee. The format must be weight:fee;weight:fee;.. (E.g. 5:8.95;10:15.95;..)'),
          'function' => 'text()',
        ],
        [
          'key' => 'geo_zone_id_3',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_zone', 'Zone') .' 3: '. language::translate(__CLASS__.':title_geo_zone', 'Geo Zone'),
          'description' => language::translate(__CLASS__.':description_geo_zone', 'Geo zone to which the fee applies.'),
          'function' => 'geo_zone()',
        ],
        [
          'key' => 'weight_rate_table_3',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_zone', 'Zone') .' 3: '. language::translate(__CLASS__.':title_weight_rate_table', 'Weight Rate Table'),
          'description' => language::translate(__CLASS__.':description_weight_rate_table', 'Ascending rate table of the shipping fee. The format must be weight:fee;weight:fee;.. (E.g. 5:8.95;10:15.95;..)'),
          'function' => 'text()',
        ],
        [
          'key' => 'weight_rate_table_x',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_non_matched_zones', 'Non-matched Zones') .': '. language::translate(__CLASS__.':title_weight_rate_table', 'Weight Rate Table'),
          'description' => language::translate(__CLASS__.':description_weight_rate_table', 'Ascending rate table of the shipping fee. The format must be weight:fee;weight:fee;.. (E.g. 5:8.95;10:15.95;..)'),
          'function' => 'text()',
        ],
        [
          'key' => 'method',
          'default_value' => '>=',
          'title' => language::translate(__CLASS__.':title_method', 'Method'),
          'description' => language::translate(__CLASS__.':description_method', 'The calculation method that should to be used for the rate tables where a condition is met for shipping weight. E.g. weight < table'),
          'function' => 'select("ITEM_WEIGHT_LOWER_THAN_VALUE","ITEM_WEIGHT_LOWER_THAN_OR_EQUALS_VALUE","ITEM_WEIGHT_HIGHER_THAN_VALUE","ITEM_WEIGHT_HIGHER_THAN_OR_EQUALS_VALUE")',
        ],
        [
          'key' => 'handling_fee',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_handling_fee', 'Handling Fee'),
          'description' => language::translate(__CLASS__.':description_handling_fee', 'Enter your handling fee for the shipment.'),
          'function' => 'decimal()',
        ],
        [
          'key' => 'tax_class_id',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_tax_class', 'Tax Class'),
          'description' => language::translate(__CLASS__.':description_tax_class', 'The tax class for the shipping fee.'),
          'function' => 'tax_class()',
        ],
        [
          'key' => 'priority',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_priority', 'Priority'),
          'description' => language::translate(__CLASS__.':description_priority', 'Process this module by the given priority value.'),
          'function' => 'number()',
        ],
      ];
    }
  }
