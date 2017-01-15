<?php

  class cm_local_database {
    public $id = __CLASS__;
    public $name = 'Local Database - Get Address';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $website = 'http://www.litecart.net';
    public $version = '1.0';

    public function get_address($data) {

      if (empty($this->settings['status'])) return;

      $customers_query = database::query(
        "select distinct tax_id, company, firstname, lastname, address1, address2, postcode, city, country_code, zone_code, phone
        from ". DB_TABLE_CUSTOMERS ."
        where status
        ". (!empty($data['tax_id']) ? "and tax_id = '". database::input($data['tax_id']) ."'" : null) ."
        ". (!empty($data['company']) ? "and company = '". database::input($data['company']) ."'" : null) ."
        ". (!empty($data['firstname']) ? "and firstname = '". database::input($data['firstname']) ."'" : null) ."
        ". (!empty($data['lastname']) ? "and lastname = '". database::input($data['lastname']) ."'" : null) ."
        ". (!empty($data['address1']) ? "and address1 = '". database::input($data['address1']) ."'" : null) ."
        ". (!empty($data['address2']) ? "and address1 = '". database::input($data['address2']) ."'" : null) ."
        ". (!empty($data['postcode']) ? "and city = '". database::input($data['postcode']) ."'" : null) ."
        ". (!empty($data['city']) ? "and city = '". database::input($data['city']) ."'" : null) ."
        ". (!empty($data['country_code']) ? "and country_code = '". database::input($data['country_code']) ."'" : null) ."
        ". (!empty($data['zone_code']) ? "and zone_code = '". database::input($data['zone_code']) ."'" : null) ."
        ". (!empty($data['email']) ? "and email = '". database::input($data['email']) ."'" : null) ."
        ". (!empty($data['phone']) ? "and phone = '". database::input($data['phone']) ."'" : null) ."
        limit 2;"
      );

      if (database::num_rows($customers_query) != 1) return;

      $address = database::fetch($customers_query);

    // Return company
      if (!empty($address['company'])) {
        return $data + array(
          'tax_id' => $address['tax_id'],
          'company' => $address['company'],
          'address1' => $address['address1'],
          'address2' => $address['address2'],
          'postcode' => $address['postcode'],
          'city' => $address['city'],
          'country_code' => $address['country_code'],
          'zone_code' => $address['zone_code'],
        );

    // Return individual
      } else {
        return $data + array(
          'tax_id' => $address['tax_id'],
          'company' => $address['company'],
          'city' => $address['city'],
          'country_code' => $address['country_code'],
          'zone_code' => $address['zone_code'],
        );
      }
    }

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
          'default_value' => '1',
          'title' => language::translate(__CLASS__.':title_priority', 'Priority'),
          'description' => language::translate(__CLASS__.':description_priority', 'Process this module by the given priority value.'),
          'function' => 'int()',
        ),
      );
    }

    public function install() {}

    public function uninstall() {}
  }

?>