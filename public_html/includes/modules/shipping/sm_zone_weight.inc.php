<?php

  #[AllowDynamicProperties]
  class sm_zone_weight {
    public $id = __CLASS__;
    public $name = 'Zone Based Shipping';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $version = '1.0';
    public $website = 'https://www.litecart.net';

    public function __construct() {
      $this->name = language::translate(__CLASS__.':title', 'Zone Based Shipping');
    }

    public function options($items, $subtotal, $tax, $currency_code, $customer) {

      if (empty($this->settings['status'])) return;

    // Calculate cart weight
      $total_weight = 0;

      foreach ($items as $item) {
        $total_weight += weight::convert($item['quantity'] * $item['weight'], $item['weight_class'], $this->settings['weight_class']);
      }

      $options = [];

    // Step through each rate table
      foreach ([1, 2, 3] as $i) {

        if (empty($this->settings['geo_zone_id_'.$i])) {
          continue;
        }

        $name = language::translate(__CLASS__.':title_option_name_zone_'.$i, '');

        if (!$name) {
          $name = reference::geo_zone($this->settings['geo_zone_id_'.$i])->name;
        }

        if (!reference::country($customer['shipping_address']['country_code'])->in_geo_zone($this->settings['geo_zone_id_'.$i], $customer['shipping_address'])) continue;

        $cost = $this->calculate_cost($this->settings['weight_rate_table_'.$i], $total_weight);

        $options[] = [
          'id' => 'zone_'.$i,
          'icon' => $this->settings['icon'],
          'name' => $name,
          'description' => language::translate(__CLASS__.':title_option_description_zone_'.$i, ''),
          'fields' => '',
          'cost' => $cost,
          'tax_class_id' => $this->settings['tax_class_id'],
          'exclude_cheapest' => false,
        ];
      }

      $name = language::translate(__CLASS__.':title_option_name_zone_x', '');

    // Fall back to zone_x
      if (!$options) {

        if (empty($this->settings['weight_rate_table_x'])) {
          return;
        }

        $cost = $this->calculate_cost($this->settings['weight_rate_table_x'], $total_weight);

        $options[] = [
          'id' => 'zone_x',
          'icon' => $this->settings['icon'],
          'name' => !empty($name) ? $name : reference::country($customer['shipping_address']['country_code'])->name,
          'description' => language::translate(__CLASS__.':title_option_description_zone_x', ''),
          'fields' => '',
          'cost' => (float)$cost + (float)$this->settings['handling_fee'],
          'tax_class_id' => $this->settings['tax_class_id'],
        ];
      }

      $options = [
        'title' => $this->name,
        'options' => $options,
      ];

      return $options;
    }

    private function calculate_cost($rate_table, $shipping_weight) {

      if (empty($rate_table)) return 0;

      $cost = 0;

      switch ($this->settings['method']) {

        case '<':
        case '&lt;':
          foreach (array_reverse(preg_split('#[|;]#', $rate_table, -1, PREG_SPLIT_NO_EMPTY)) as $rate) {
            list($rate_weight, $rate_cost) = explode(':', $rate);
            if ($shipping_weight < $rate_weight) {
              $cost = $rate_cost;
            }
          }
          break;

        case '<=':
        case '&lt;=':
          foreach (array_reverse(preg_split('#[|;]#', $rate_table, -1, PREG_SPLIT_NO_EMPTY)) as $rate) {
            list($rate_weight, $rate_cost) = explode(':', $rate);
            if ($shipping_weight <= $rate_weight) {
              $cost = $rate_cost;
            }
          }
          break;

        case '>':
        case '&gt;':
          foreach (preg_split('#[|;]#', $rate_table, -1, PREG_SPLIT_NO_EMPTY) as $rate) {
            list($rate_weight, $rate_cost) = explode(':', $rate);
            if ($shipping_weight > $rate_weight) {
              $cost = $rate_cost;
            }
          }
          break;

        case '>=':
        case '&gt;=':
        default:
          foreach (preg_split('#[|;]#', $rate_table, -1, PREG_SPLIT_NO_EMPTY) as $rate) {
            list($rate_weight, $rate_cost) = explode(':', $rate);
            if ($shipping_weight >= $rate_weight) {
              $cost = $rate_cost;
            }
          }
          break;
      }

      return $cost;
    }

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
          'function' => 'text()',
          'description' => language::translate(__CLASS__.':description_icon', 'Path to an image to be displayed.'),
        ],
        [
          'key' => 'weight_class',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_weight_class', 'Weight Class'),
          'description' => language::translate(__CLASS__.':description_weight_class', 'The weight class for the rate table.'),
          'function' => 'weight_class()',
        ],
        [
          'key' => 'geo_zone_id_1',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_zone', 'Zone') .' 1: '. language::translate(__CLASS__.':title_geo_zone', 'Geo Zone'),
          'description' => language::translate(__CLASS__.':description_geo_zone', 'Geo zone to which the cost applies.'),
          'function' => 'geo_zone()',
        ],
        [
          'key' => 'weight_rate_table_1',
          'default_value' => '5:8.95;10:15.95',
          'title' => language::translate(__CLASS__.':title_zone', 'Zone') .' 1: '. language::translate(__CLASS__.':title_weight_rate_table', 'Weight Rate Table'),
          'description' => language::translate(__CLASS__.':description_weight_rate_table', 'Ascending rate table of the shipping cost. The format must be weight:cost;weight:cost;.. (E.g. 5:8.95;10:15.95;..)'),
          'function' => 'text()',
        ],
        [
          'key' => 'geo_zone_id_2',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_zone', 'Zone') .' 2: '. language::translate(__CLASS__.':title_geo_zone', 'Geo Zone'),
          'description' => language::translate(__CLASS__.':description_geo_zone', 'Geo zone to which the cost applies.'),
          'function' => 'geo_zone()',
        ],
        [
          'key' => 'weight_rate_table_2',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_zone', 'Zone') .' 2: '. language::translate(__CLASS__.':title_weight_rate_table', 'Weight Rate Table'),
          'description' => language::translate(__CLASS__.':description_weight_rate_table', 'Ascending rate table of the shipping cost. The format must be weight:cost;weight:cost;.. (E.g. 5:8.95;10:15.95;..)'),
          'function' => 'text()',
        ],
        [
          'key' => 'geo_zone_id_3',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_zone', 'Zone') .' 3: '. language::translate(__CLASS__.':title_geo_zone', 'Geo Zone'),
          'description' => language::translate(__CLASS__.':description_geo_zone', 'Geo zone to which the cost applies.'),
          'function' => 'geo_zone()',
        ],
        [
          'key' => 'weight_rate_table_3',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_zone', 'Zone') .' 3: '. language::translate(__CLASS__.':title_weight_rate_table', 'Weight Rate Table'),
          'description' => language::translate(__CLASS__.':description_weight_rate_table', 'Ascending rate table of the shipping cost. The format must be weight:cost;weight:cost;.. (E.g. 5:8.95;10:15.95;..)'),
          'function' => 'text()',
        ],
        [
          'key' => 'weight_rate_table_x',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_non_matched_zones', 'Non-matched Zones') .': '. language::translate(__CLASS__.':title_weight_rate_table', 'Weight Rate Table'),
          'description' => language::translate(__CLASS__.':description_weight_rate_table', 'Ascending rate table of the shipping cost. The format must be weight:cost;weight:cost;.. (E.g. 5:8.95;10:15.95;..)'),
          'function' => 'text()',
        ],
        [
          'key' => 'method',
          'default_value' => '>=',
          'title' => language::translate(__CLASS__.':title_method', 'Method'),
          'description' => language::translate(__CLASS__.':description_method', 'The calculation method that should to be used for the rate tables where a condition is met for shipping weight. E.g. weight < table'),
          'function' => 'select("&lt;","&lt;=","&gt;","&gt;=")',
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
          'description' => language::translate(__CLASS__.':description_tax_class', 'The tax class for the shipping cost.'),
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

    public function install() {}

    public function uninstall() {}
  }
