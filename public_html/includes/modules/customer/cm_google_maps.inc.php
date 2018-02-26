<?php

  class cm_google_maps {
    public $id = __CLASS__;
    public $name = 'Google Maps - Get Address';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $website = 'http://www.litecart.net';
    public $version = '1.0';

    public function get_address($data) {

      if (empty($this->settings['status'])) return;

      if (!in_array($data['trigger'], array('company', 'address1', 'postcode', 'city'))) return;

      $proceed = false;
      if (empty($data['address1'])) $proceed = true;
      if (empty($data['city'])) $proceed = true;
      if (empty($data['postcode'])) $proceed = true;
      if (empty($data['country_code'])) $proceed = true;

      $address = array(
        !empty($data['company']) ? $data['company'] : false,
        !empty($data['address1']) ? $data['address1'] : false,
        !empty($data['country_code']) ? $data['country_code'] : false .'-'. !empty($data['postcode']) ? $data['postcode'] : false,
        !empty($data['city']) ? $data['city'] : false,
        !empty($data['country_code']) ? reference::country($data['country_code'])->name : false,
      );

      $params = array(
        'address' => implode(' ', $address),
        'sensor' => 'false',
      );

      $url = 'http://maps.googleapis.com/maps/api/geocode/json?'. http_build_query($params);

      $client = new http_client();
      $response = $client->call('GET', $url);

      if (empty($response)) return;

      $response = json_decode($response, true);

      if (empty($response['status']) || (string)$response['status'] != 'OK') return;

      foreach ($response['results'] as $result) {
        foreach ($result['address_components'] as $component) {
          foreach ($component['types'] as $type) {
            switch($type) {
              case 'route':
                $treasures['address1'][] = $component['long_name'];
                break;
              case 'street_number':
                $treasures['street_number'][] = $component['long_name'];
                break;
              case 'postal_code':
                $treasures['postcode'][] = $component['long_name'];
                break;
              //case 'locality':
              case 'postal_town': // Be aware as postal_town is not always present
                $treasures['city'][] = $component['long_name'];
                break;
              case 'country':
                $treasures['country_code'][] = $component['short_name'];
                break;
            }
          }
        }
      }

      foreach (array_keys($treasures) as $key) {
        $treasures[$key] = array_unique($treasures[$key]);
        $treasures[$key] = array_filter($treasures[$key]);
        if (count($treasures[$key]) > 1) {
          unset($treasures[$key]);
          continue;
        }

        $treasures[$key] = implode('', $treasures[$key]);
      }

      if (!empty($data['address1']) && !empty($treasures['address1']) && !preg_match('#^'. preg_quote($treasures['address1'], '#') .'#i', $data['address1'])) return;
      if (!empty($data['city']) && !empty($treasures['city']) && !preg_match('#^'. preg_quote($treasures['city'], '#') .'#i', $data['city'])) return;
      if (!empty($data['postcode']) && !empty($treasures['postcode']) && preg_replace('# #', '', $treasures['postcode']) != preg_replace('# #', '', $treasures['postcode'])) return;
      if (!empty($data['country_code']) && !empty($treasures['country_code']) && $treasures['country_code'] != $treasures['country_code']) return;

      $output = array(
        'address1' => !empty($treasures['address1']) ? $treasures['address1'] . (!empty($treasures['street_number']) ? ' ' . $treasures['street_number'] : '') : '',
        //'address1' => !empty($treasures['address1']) ? $treasures['address1'] : '',
        'postcode' => !empty($treasures['postcode']) ? $treasures['postcode'] : '',
        'city' => !empty($treasures['city']) ? $treasures['city'] : '',
        'country_code' => !empty($treasures['country_code']) ? $treasures['country_code'] : '',
        'source' => 'Google Maps'
      );

      if (strtolower(language::$selected['charset']) != 'utf-8') {
        $output = array_walk($output, 'utf8_decode');
      }

      return $output;
    }

    public function before_process() {}

    public function after_process() {}

    function settings() {
      return array(
        array(
          'key' => 'status',
          'default_value' => '1',
          'title' => language::translate(__CLASS__.':title_status', 'Status'),
          'description' => language::translate(__CLASS__.':description_status', 'Enables or disables the module.'),
          'function' => 'toggle("e/d")',
        ),
        array(
          'key' => 'priority',
          'default_value' => '99',
          'title' => language::translate(__CLASS__.':title_priority', 'Priority'),
          'description' => language::translate(__CLASS__.':description_priority', 'Process this module by the given priority value.'),
          'function' => 'int()',
        ),
      );
    }

    public function install() {}

    public function uninstall() {}
  }
