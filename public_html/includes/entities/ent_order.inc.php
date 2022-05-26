<?php

  class ent_order {
    public $data;
    public $previous;

    public function __construct($order_id=null) {

      if (!empty($order_id)) {
        $this->load($order_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."orders;"
      );

      while ($field = database::fetch($fields_query)) {

        switch ($field['Field']) {
          case 'customer_id':
          case 'customer_email':
          case 'customer_tax_id':
          case 'customer_company':
          case 'customer_firstname':
          case 'customer_lastname':
          case 'customer_address1':
          case 'customer_address2':
          case 'customer_postcode':
          case 'customer_city':
          case 'customer_country_code':
          case 'customer_zone_code':
          case 'customer_phone':
            $this->data['customer'][preg_replace('#^(customer_)#', '', $field['Field'])] = database::create_variable($field['Type']);
            break;

          case 'shipping_company':
          case 'shipping_firstname':
          case 'shipping_lastname':
          case 'shipping_address1':
          case 'shipping_address2':
          case 'shipping_postcode':
          case 'shipping_city':
          case 'shipping_country_code':
          case 'shipping_zone_code':
          case 'shipping_phone':
            $this->data['customer']['shipping_address'][preg_replace('#^(shipping_)#', '', $field['Field'])] = database::create_variable($field['Type']);
            break;

          case 'payment_option_id':
          case 'payment_option_name':
            $this->data['payment_option'][preg_replace('#^(payment_option_)#', '', $field['Field'])] = database::create_variable($field['Type']);
            break;

          case 'shipping_option_id':
          case 'shipping_option_name':
            $this->data['shipping_option'][preg_replace('#^(shipping_option_)#', '', $field['Field'])] = database::create_variable($field['Type']);
            break;

          default:
            $this->data[$field['Field']] = database::create_variable($field['Type']);
            break;
        }
      }

      $this->data = array_merge($this->data, [
        'uid' => uniqid(),
        'weight_class' => settings::get('store_weight_class'),
        'currency_code' => currency::$selected['code'],
        'currency_value' => currency::$selected['value'],
        'language_code' => language::$selected['code'],
        'items' => [],
        'order_total' => [],
        'comments' => [],
        'subtotal' => ['amount' => 0, 'tax' => 0],
        'display_prices_including_tax' => settings::get('default_display_prices_including_tax'),
      ]);

      $this->previous = $this->data;
    }

    public function load($order_id) {

      if (!preg_match('#^[0-9]+$#', $order_id)) throw new Exception('Invalid order (ID: '. $order_id .')');

      $this->reset();

      $order_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."orders
        where id = ". (int)$order_id ."
        limit 1;"
      );

      if ($order = database::fetch($order_query)) {
        $this->data = array_replace($this->data, array_intersect_key($order, $this->data));
      } else {
        throw new Exception('Could not find order in database (ID: '. (int)$order_id .')');
      }

      foreach ($order as $field => $value) {

        switch ($field) {
          case 'customer_id':
          case 'customer_email':
          case 'customer_tax_id':
          case 'customer_company':
          case 'customer_firstname':
          case 'customer_lastname':
          case 'customer_address1':
          case 'customer_address2':
          case 'customer_postcode':
          case 'customer_city':
          case 'customer_country_code':
          case 'customer_zone_code':
          case 'customer_phone':
            $this->data['customer'][preg_replace('#^(customer_)#', '', $field)] = $value;
            break;

          case 'shipping_company':
          case 'shipping_firstname':
          case 'shipping_lastname':
          case 'shipping_address1':
          case 'shipping_address2':
          case 'shipping_postcode':
          case 'shipping_city':
          case 'shipping_country_code':
          case 'shipping_zone_code':
          case 'shipping_phone':
            $this->data['customer']['shipping_address'][preg_replace('#^(shipping_)#', '', $field)] = $value;
            break;

          case 'payment_option_id':
          case 'payment_option_name':
            $this->data['payment_option'][preg_replace('#^(payment_option_)#', '', $field)] = $value;
            break;

          case 'shipping_option_id':
          case 'shipping_option_name':
            $this->data['shipping_option'][preg_replace('#^(shipping_option_)#', '', $field)] = $value;
            break;
        }
      }

      $order_items_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."orders_items
        where order_id = ". (int)$order_id ."
        order by id;"
      );

      while ($item = database::fetch($order_items_query)) {
        $item['options'] = unserialize($item['options']);
        $item['quantity'] = (float)$item['quantity']; // Turn "1.0000" to 1
        $item['price'] = (float)$item['price']; // Turn "1.0000" to 1
        $item['tax'] = (float)$item['tax']; // Turn "1.0000" to 1
        $this->data['items'][$item['id']] = $item;
      }

      $order_totals_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."orders_totals
        where order_id = ". (int)$order_id ."
        order by priority;"
      );

      while ($row = database::fetch($order_totals_query)) {
        $row['value'] = (float)$row['value']; // Turns "1.0000" to 1
        $row['tax'] = (float)$row['tax']; // Turns "1.0000" to 1
        $this->data['order_total'][$row['id']] = $row;
      }

      $order_comments_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."orders_comments
        where order_id = ". (int)$order_id ."
        order by id;"
      );

      while ($row = database::fetch($order_comments_query)) {
        $this->data['comments'][$row['id']] = $row;
      }

      $this->previous = $this->data;
    }

    public function save() {

    // Re-calculate total if there are changes
      $this->refresh_total();

    // Log order status change as comment
      if (!empty($this->previous['id']) && ($this->data['order_status_id'] != $this->previous['order_status_id'])) {
        $this->data['comments'][] = [
          'author' => 'system',
          'text' => strtr(language::translate('text_user_changed_order_status_to_new_status', 'Order status changed to %new_status by %username', settings::get('store_language_code')), [
            '%username' => !empty(user::$data['username']) ? user::$data['username'] : 'system',
            '%new_status' => reference::order_status($this->data['order_status_id'], settings::get('store_language_code'))->name,
          ]),
          'hidden' => 1,
        ];
      }

    // Link guests to customer profile
      if (empty($this->data['customer']['id']) && !empty($this->data['customer']['email'])) {
        $customers_query = database::query(
          "select id from ". DB_TABLE_PREFIX ."customers
          where email = '". database::input($this->data['customer']['email']) ."'
          limit 1;"
        );

        if ($customer = database::fetch($customers_query)) {
          $this->data['customer']['id'] = $customer['id'];
        }
      }

      if (empty($this->data['public_key'])) {
        $this->data['public_key'] = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', mt_rand(5, 10))), 0, 32);
      }

    // Insert/update order
      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."orders
          (uid, client_ip, user_agent, domain, date_created)
          values ('". database::input($this->data['uid']) ."', '". database::input($_SERVER['REMOTE_ADDR']) ."', '". database::input($_SERVER['HTTP_USER_AGENT']) ."', '". database::input($_SERVER['HTTP_HOST']) ."', '". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );

        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."orders set
        starred = ". (int)$this->data['starred'] .",
        unread = ". (int)$this->data['unread'] .",
        order_status_id = ". (int)$this->data['order_status_id'] .",
        customer_id = ". (int)$this->data['customer']['id'] .",
        customer_email = '". database::input($this->data['customer']['email']) ."',
        customer_tax_id = '". database::input($this->data['customer']['tax_id']) ."',
        customer_company = '". database::input($this->data['customer']['company']) ."',
        customer_firstname = '". database::input($this->data['customer']['firstname']) ."',
        customer_lastname = '". database::input($this->data['customer']['lastname']) ."',
        customer_address1 = '". database::input($this->data['customer']['address1']) ."',
        customer_address2 = '". database::input($this->data['customer']['address2']) ."',
        customer_city = '". database::input($this->data['customer']['city']) ."',
        customer_postcode = '". database::input($this->data['customer']['postcode']) ."',
        customer_country_code = '". database::input($this->data['customer']['country_code']) ."',
        customer_zone_code = '". database::input($this->data['customer']['zone_code']) ."',
        customer_phone = '". database::input($this->data['customer']['phone']) ."',
        shipping_company = '". database::input($this->data['customer']['shipping_address']['company']) ."',
        shipping_firstname = '". database::input($this->data['customer']['shipping_address']['firstname']) ."',
        shipping_lastname = '". database::input($this->data['customer']['shipping_address']['lastname']) ."',
        shipping_address1 = '". database::input($this->data['customer']['shipping_address']['address1']) ."',
        shipping_address2 = '". database::input($this->data['customer']['shipping_address']['address2']) ."',
        shipping_city = '". database::input($this->data['customer']['shipping_address']['city']) ."',
        shipping_postcode = '". database::input($this->data['customer']['shipping_address']['postcode']) ."',
        shipping_country_code = '". database::input($this->data['customer']['shipping_address']['country_code']) ."',
        shipping_zone_code = '". database::input($this->data['customer']['shipping_address']['zone_code']) ."',
        shipping_phone = '". database::input($this->data['customer']['shipping_address']['phone']) ."',
        shipping_option_id = '". database::input($this->data['shipping_option']['id']) ."',
        shipping_option_name = '". database::input($this->data['shipping_option']['name']) ."',
        shipping_tracking_id = '". database::input($this->data['shipping_tracking_id']) ."',
        shipping_tracking_url = '". database::input($this->data['shipping_tracking_url']) ."',
        payment_option_id = '". database::input($this->data['payment_option']['id']) ."',
        payment_option_name = '". database::input($this->data['payment_option']['name']) ."',
        payment_transaction_id = '". database::input($this->data['payment_transaction_id']) ."',
        reference = '". database::input($this->data['reference']) ."',
        language_code = '". database::input($this->data['language_code']) ."',
        currency_code = '". database::input($this->data['currency_code']) ."',
        currency_value = ". (float)$this->data['currency_value'] .",
        weight_total = ". (float)$this->data['weight_total'] .",
        weight_class = '". database::input($this->data['weight_class']) ."',
        display_prices_including_tax = ". (int)$this->data['display_prices_including_tax'] .",
        payment_due = ". (float)$this->data['payment_due'] .",
        tax_total = ". (float)$this->data['tax_total'] .",
        public_key = '". database::input($this->data['public_key']) ."',
        date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

    // Restock previous items
      if (!empty($this->previous['order_status_id']) && !empty(reference::order_status($this->previous['order_status_id'], $this->data['language_code'])->is_sale)) {
        foreach ($this->previous['items'] as $previous_order_item) {
          if (empty($previous_order_item['product_id'])) continue;
          $product = new ent_product($previous_order_item['product_id']);
          $product->adjust_quantity((float)$previous_order_item['quantity'], $previous_order_item['option_stock_combination']);
        }
      }

    // Delete order items
      database::query(
        "delete from ". DB_TABLE_PREFIX ."orders_items
        where order_id = ". (int)$this->data['id'] ."
        and id not in ('". implode("', '", array_column($this->data['items'], 'id')) ."');"
      );

    // Insert/update order items
      foreach ($this->data['items'] as &$item) {
        if (empty($item['id'])) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."orders_items
            (order_id)
            values (". (int)$this->data['id'] .");"
          );
          $item['id'] = database::insert_id();

        // Update purchase count
          if (!empty($item['product_id'])) {
            database::query(
              "update ". DB_TABLE_PREFIX ."products
              set purchases = purchases + ". (float)$item['quantity'] ."
              where id = ". (int)$item['product_id'] ."
              limit 1;"
            );
          }
        }

      // Withdraw stock
        if (!empty($this->data['order_status_id']) && !empty(reference::order_status($this->data['order_status_id'])->is_sale) && !empty($item['product_id'])) {
          $product = new ent_product($item['product_id']);
          $product->adjust_quantity(-(float)$item['quantity'], $item['option_stock_combination']);
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."orders_items
          set product_id = ". (int)$item['product_id'] .",
          option_stock_combination = '". database::input($item['option_stock_combination']) ."',
          options = '". (!empty($item['options']) ? database::input(serialize($item['options'])) : '') ."',
          name = '". database::input($item['name']) ."',
          sku = '". database::input($item['sku']) ."',
          gtin = '". database::input($item['gtin']) ."',
          taric = '". database::input($item['taric']) ."',
          quantity = ". (float)$item['quantity'] .",
          price = ". (float)$item['price'] .",
          tax = ". (float)$item['tax'] .",
          weight = ". (float)$item['weight'] .",
          weight_class = '". database::input($item['weight_class']) ."',
          dim_x = ". (float)$item['dim_x'] .",
          dim_y = ". (float)$item['dim_y'] .",
          dim_z = ". (float)$item['dim_z'] .",
          dim_class = '". database::input($item['dim_class']) ."'
          where order_id = ". (int)$this->data['id'] ."
          and id = ". (int)$item['id'] ."
          limit 1;"
        );
      } unset($item);

    // Delete order total items
      database::query(
        "delete from ". DB_TABLE_PREFIX ."orders_totals
        where order_id = ". (int)$this->data['id'] ."
        and id not in ('". implode("', '", array_column($this->data['order_total'], 'id')) ."');"
      );

    // Insert/update order total
      $i = 0;
      foreach ($this->data['order_total'] as &$row) {
        if (empty($row['id'])) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."orders_totals
            (order_id)
            values (". (int)$this->data['id'] .");"
          );
          $row['id'] = database::insert_id();
        }
        database::query(
          "update ". DB_TABLE_PREFIX ."orders_totals
          set title = '". database::input($row['title']) ."',
          module_id = '". database::input($row['module_id']) ."',
          value = '". (float)$row['value'] ."',
          tax = '". (float)$row['tax'] ."',
          calculate = '". (empty($row['calculate']) ? 0 : 1) ."',
          priority = ". ++$i ."
          where order_id = ". (int)$this->data['id'] ."
          and id = ". (int)$row['id'] ."
          limit 1;"
        );
      } unset($row);

    // Delete comments
      database::query(
        "delete from ". DB_TABLE_PREFIX ."orders_comments
        where order_id = ". (int)$this->data['id'] ."
        and id not in ('". implode("', '", array_column($this->data['comments'], 'id')) ."');"
      );

    // Insert/update comments
      if (!empty($this->data['comments'])) {

        $notify_comments = [];

        foreach ($this->data['comments'] as &$comment) {

          if (empty($comment['author'])) $comment['author'] = 'system';

          if (empty($comment['id'])) {
            database::query(
              "insert into ". DB_TABLE_PREFIX ."orders_comments
              (order_id, date_created)
              values (". (int)$this->data['id'] .", '". ($comment['date_created'] = date('Y-m-d H:i:s')) ."');"
            );
            $comment['id'] = database::insert_id();

            if ($comment['author'] == 'staff' && !empty($comment['notify']) && empty($comment['hidden'])) {
              $notify_comments[] = $comment;
            }
          }

          database::query(
            "update ". DB_TABLE_PREFIX ."orders_comments
            set author = '". (!empty($comment['author']) ? database::input($comment['author']) : 'system') ."',
              text = '". database::input($comment['text']) ."',
              hidden = '". (!empty($comment['hidden']) ? 1 : 0) ."'
            where order_id = ". (int)$this->data['id'] ."
            and id = ". (int)$comment['id'] ."
            limit 1;"
          );
        } unset($comment);

        if (!empty($notify_comments)) {

          $subject = '['. language::translate('title_order', 'Order') .' #'. $this->data['id'] .'] ' . language::translate('title_new_comments_added', 'New Comments Added', $this->data['language_code']);

          $message = language::translate('text_new_comments_added_to_your_order', 'New comments added to your order', $this->data['language_code']) . ":\r\n\r\n";
          foreach ($notify_comments as $comment) {
            $message .= language::strftime(language::$selected['format_datetime'], strtotime($comment['date_created'])) ." – ". trim($comment['text']) . "\r\n\r\n";
          }

          $email = new ent_email();
          $email->add_recipient($this->data['customer']['email'], $this->data['customer']['firstname'] .' '. $this->data['customer']['lastname'])
                ->set_subject($subject)
                ->add_body($message)
                ->send();
        }
      }

    // Send order status email notification
      if (!empty($this->previous) && ($this->data['order_status_id'] != $this->previous['order_status_id'])) {
        if (!empty(reference::order_status($this->data['order_status_id'])->notify)) {
          $this->send_email_notification();
        }
      }

      $order_modules = new mod_order();
      $order_modules->update($this);

      $this->previous = $this->data;

      cache::clear_cache('order');
      cache::clear_cache('category');
      cache::clear_cache('manufacturer');
      cache::clear_cache('products');
    }

    public function refresh_total() {
      $this->data['subtotal'] = ['amount' => 0, 'tax' => 0];
      $this->data['payment_due'] = 0;
      $this->data['tax_total'] = 0;
      $this->data['weight_total'] = 0;

      foreach ($this->data['items'] as $item) {
        $this->data['subtotal']['amount'] += (float)$item['price'] * (float)$item['quantity'];
        $this->data['subtotal']['tax'] += (float)$item['tax'] * (float)$item['quantity'];
        $this->data['payment_due'] += ((float)$item['price'] + (float)$item['tax']) * (float)$item['quantity'];
        $this->data['tax_total'] += (float)$item['tax'] * (float)$item['quantity'];
        $this->data['weight_total'] += weight::convert((float)$item['weight'], $item['weight_class'], $this->data['weight_class']) * (float)$item['quantity'];
      }

      foreach ($this->data['order_total'] as &$row) {
        if ($row['module_id'] == 'ot_subtotal') {
          $row['value'] = (float)$this->data['subtotal']['amount'];
          $row['tax'] = (float)$this->data['subtotal']['tax'];
        }

        if (empty($row['calculate'])) continue;
        $this->data['payment_due'] += (float)$row['value'] + (float)$row['tax'];
        $this->data['tax_total'] += (float)$row['tax'];
      } unset($row);
    }

    public function add_item($item) {

      $fields = [
        'product_id',
        'options',
        'option_stock_combination',
        'name',
        'sku',
        'gtin',
        'taric',
        'price',
        'tax',
        'quantity',
        'weight',
        'weight_class',
        'dim_x',
        'dim_y',
        'dim_z',
        'dim_class',
        'error',
      ];

      $i = 1;
      while (isset($this->data['items']['new_'.$i])) $i++;
      $item_key = 'new_'.$i;

      $this->data['items']['new_'.$i]['id'] = '';

      foreach ($fields as $field) {
        $this->data['items']['new_'.$i][$field] = isset($item[$field]) ? $item[$field] : '';
      }

      $this->data['subtotal']['amount'] += (float)$item['price'] * (float)$item['quantity'];
      $this->data['subtotal']['tax'] += (float)$item['tax'] * (float)$item['quantity'];
      $this->data['payment_due'] += ((float)$item['price'] + (float)$item['tax']) * (float)$item['quantity'];
      $this->data['tax_total'] += (float)$item['tax'] * (float)$item['quantity'];
      $this->data['weight_total'] += weight::convert((float)$item['weight'], $item['weight_class'], $this->data['weight_class']) * (float)$item['quantity'];
    }

    public function add_ot_row($row) {

      $row = [
        'id' => 0,
        'module_id' => $row['id'],
        'title' =>  $row['title'],
        'value' => (float)$row['value'],
        'tax' => (float)$row['tax'],
        'calculate' => !empty($row['calculate']) ? 1 : 0,
      ];

      $i = 1;
      while (isset($this->data['order_total']['new_'.$i])) $i++;
      $this->data['order_total']['new_'.$i] = $row;

      if (!empty($row['calculate'])) {
        $this->data['payment_due'] += $row['value'] + $row['tax'];
        $this->data['tax_total'] += $row['tax'];
      }
    }

    public function validate($shipping = null, $payment = null) {

    // Validate items
      if (empty($this->data['items'])) return language::translate('error_order_missing_items', 'The order does not contain any items');

      foreach ($this->data['items'] as $item) {
        if (!empty($item['error'])) return language::translate('error_cart_contains_errors', 'Your cart contains errors');
      }

    // Validate customer details
      try {
        if (empty($this->data['customer']['firstname'])) throw new Exception(language::translate('error_missing_firstname', 'You must enter a first name.'));
        if (empty($this->data['customer']['lastname'])) throw new Exception(language::translate('error_missing_lastname', 'You must enter a last name.'));
        if (empty($this->data['customer']['address1'])) throw new Exception(language::translate('error_missing_address1', 'You must enter an address.'));
        if (empty($this->data['customer']['city'])) throw new Exception(language::translate('error_missing_city', 'You must enter a city.'));
        if (empty($this->data['customer']['country_code'])) throw new Exception(language::translate('error_missing_country', 'You must select a country.'));
        if (empty($this->data['customer']['email'])) throw new Exception(language::translate('error_missing_email', 'You must enter an email address.'));
        if (empty($this->data['customer']['phone'])) throw new Exception(language::translate('error_missing_phone', 'You must enter a phone number.'));

        if (!functions::validate_email($this->data['customer']['email'])) throw new Exception(language::translate('error_invalid_email_address', 'Invalid email address'));

        if (reference::country($this->data['customer']['country_code'])->tax_id_format) {
          if (!empty($this->data['customer']['tax_id'])) {
            if (!preg_match('#'. reference::country($this->data['customer']['country_code'])->tax_id_format .'#i', $this->data['customer']['tax_id'])) {
              throw new Exception(language::translate('error_invalid_tax_id_format', 'Invalid tax ID format'));
            }
          }
        }

        if (reference::country($this->data['customer']['country_code'])->postcode_format) {
          if (!empty($this->data['customer']['postcode'])) {
            if (!preg_match('#'. reference::country($this->data['customer']['country_code'])->postcode_format .'#i', $this->data['customer']['postcode'])) {
              throw new Exception(language::translate('error_invalid_postcode_format', 'Invalid postcode format'));
            }
          } else {
            throw new Exception(language::translate('error_missing_postcode', 'You must enter a postcode'));
          }
        }

        if (settings::get('customer_field_zone') && reference::country($this->data['customer']['country_code'])->zones) {
          if (empty($this->data['customer']['zone_code']) && reference::country($this->data['customer']['country_code'])->zones) throw new Exception(language::translate('error_missing_zone', 'You must select a zone.'));
        }

        if (empty($this->data['customer']['id'])) {
          $customer_query = database::query(
            "select id from ". DB_TABLE_PREFIX ."customers
            where email = '". database::input($this->data['customer']['email']) ."'
            and status = 0
            limit 1;"
          );

          if (database::num_rows($customer_query)) {
            throw new Exception(language::translate('error_customer_account_is_disabled', 'The customer account is disabled'));
          }
        }

      } catch (Exception $e) {
        return language::translate('title_customer_details', 'Customer Details') .': '. $e->getMessage();
      }

      try {
        if (!empty($this->data['customer']['different_shipping_address'])) {
          if (empty($this->data['customer']['shipping_address']['firstname'])) throw new Exception(language::translate('error_missing_firstname', 'You must enter a first name.'));
          if (empty($this->data['customer']['shipping_address']['lastname'])) throw new Exception(language::translate('error_missing_lastname', 'You must enter a last name.'));
          if (empty($this->data['customer']['shipping_address']['address1'])) throw new Exception(language::translate('error_missing_address1', 'You must enter an address.'));
          if (empty($this->data['customer']['shipping_address']['city'])) throw new Exception(language::translate('error_missing_city', 'You must enter a city.'));
          if (empty($this->data['customer']['shipping_address']['country_code'])) throw new Exception(language::translate('error_missing_country', 'You must select a country.'));

          if (reference::country($this->data['customer']['shipping_address']['country_code'])->postcode_format) {
            if (!empty($this->data['customer']['shipping_address']['postcode'])) {
              if (!preg_match('#'. reference::country($this->data['customer']['shipping_address']['country_code'])->postcode_format .'#i', $this->data['customer']['shipping_address']['postcode'])) {
                throw new Exception(language::translate('error_invalid_postcode_format', 'Invalid postcode format.'));
              }
            } else {
              throw new Exception(language::translate('error_missing_postcode', 'You must enter a postcode.'));
            }
          }

          if (settings::get('customer_field_zone') && reference::country($this->data['customer']['shipping_address']['country_code'])->zones) {
            if (empty($this->data['customer']['shipping_address']['zone_code']) && reference::country($this->data['customer']['shipping_address']['country_code'])->zones) return language::translate('error_missing_zone', 'You must select a zone.');
          }
        }

      } catch (Exception $e) {
        return language::translate('title_shipping_address', 'Shipping Address') .': '. $e->getMessage();
      }

    // Additional customer validation
      $mod_customer = new mod_customer();
      $result = $mod_customer->validate($this->data['customer']);

      if (!empty($result['error'])) {
        return $result['error'];
      }

    // Validate shipping option
      if (!empty($shipping->modules) && count($shipping->options($this->data['items'], $this->data['currency_code'], $this->data['customer']))) {
        if (empty($this->data['shipping_option']['id'])) {
          return language::translate('error_no_shipping_method_selected', 'No shipping method selected');
        } else {
          list($module_id, $option_id) = explode(':', $this->data['shipping_option']['id']);
          if (empty($shipping->data['options'][$module_id]['options'][$option_id])) {
            return language::translate('error_invalid_shipping_method_selected', 'Invalid shipping method selected');
          }
          if (!empty($shipping->data['options'][$module_id]['options'][$option_id]['error'])) {
            return language::translate('error_shipping_method_contains_error', 'The selected shipping method contains errors');
          }
        }
      }

    // Validate payment option
      if (!empty($payment->modules) && count($payment->options($this->data['items'], $this->data['currency_code'], $this->data['customer']))) {
        if (empty($this->data['payment_option']['id'])) {
          return language::translate('error_no_payment_method_selected', 'No payment method selected');
        } else {
          list($module_id, $option_id) = explode(':', $this->data['payment_option']['id']);
          if (empty($payment->data['options'][$module_id]['options'][$option_id])) {
            return language::translate('error_invalid_payment_method_selected', 'Invalid payment method selected');
          }
          if (!empty($payment->data['options'][$module_id]['options'][$option_id]['error'])) {
            return language::translate('error_payment_method_contains_error', 'The selected payment method contains errors');
          }
        }
      }

    // Additional order validation
      $mod_order = new mod_order();
      $result = $mod_order->validate($this);

      if (!empty($result['error'])) {
        return $result['error'];
      }

      return false;
    }

    public function email_order_copy($recipient, $bccs=[], $language_code='') {

      if (empty($recipient)) return;
      if (empty($language_code)) $language_code = $this->data['language_code'];

      $order_status = $this->data['order_status_id'] ? reference::order_status($this->data['order_status_id'], $language_code) : '';

      $aliases = [
        '%order_id' => $this->data['id'],
        '%firstname' => $this->data['customer']['firstname'],
        '%lastname' => $this->data['customer']['lastname'],
        '%billing_address' => functions::format_address($this->data['customer']),
        '%payment_transaction_id' => !empty($this->data['payment_transaction_id']) ? $this->data['payment_transaction_id'] : '-',
        '%shipping_address' => functions::format_address($this->data['customer']['shipping_address']),
        '%shipping_tracking_id' => !empty($this->data['shipping_tracking_id']) ? $this->data['shipping_tracking_id'] : '-',
        '%shipping_tracking_url' => !empty($this->data['shipping_tracking_url']) ? $this->data['shipping_tracking_url'] : '',
        '%order_items' => null,
        '%payment_due' => currency::format($this->data['payment_due'], true, $this->data['currency_code'], $this->data['currency_value']),
        '%order_copy_url' => document::ilink('order', ['order_id' => $this->data['id'], 'public_key' => $this->data['public_key']], false, [], $language_code),
        '%order_status' => !empty($order_status) ? $order_status->name : null,
        '%store_name' => settings::get('store_name'),
        '%store_url' => document::ilink('', [], false, [], $language_code),
      ];

      foreach ($this->data['items'] as $item) {

        if (!empty($item['product_id'])) {
          $product = reference::product($item['product_id'], $language_code);

          $options = [];
          if (!empty($item['options'])) {
            foreach ($item['options'] as $k => $v) {
              $options[] = $k .': '. $v;
            }
          }

          $aliases['%order_items'] .= (float)$item['quantity'] .' x '. $product->name . (!empty($options) ? ' ('. implode(', ', $options) .')' : '') . "\r\n";

        } else {
          $aliases['%order_items'] .= (float)$item['quantity'] .' x '. $item['name'] . (!empty($options) ? ' ('. implode(', ', $options) .')' : '') . "\r\n";
        }
      }

      $aliases['%order_items'] = trim($aliases['%order_items']);

      $subject = '['. language::translate('title_order', 'Order', $language_code) .' #'. $this->data['id'] .'] '. language::translate('title_order_confirmation', 'Order Confirmation', $language_code);

      $message = "Thank you for your purchase!\r\n\r\n"
               . "Your order #%order_id has successfully been created with a total of %payment_due for the following ordered items:\r\n\r\n"
               . "%order_items\r\n\r\n"
               . "A printable order copy is available here:\r\n"
               . "%order_copy_url\r\n\r\n"
               . "Regards,\r\n"
               . "%store_name\r\n"
               . "%store_url\r\n";

      $message = strtr(language::translate('email_order_confirmation', $message, $language_code), $aliases);

      if (!empty(language::$languages[$this->data['language_code']]) && language::$languages[$this->data['language_code']]['direction'] == 'rtl') {
        $message = "\xe2\x80\x8f" . $message;
      }

      $email = new ent_email();

      if (!empty($bccs)) {
        foreach ($bccs as $bcc) {
          $email->add_bcc($bcc);
        }
      }

      $email->add_recipient($recipient)
            ->set_subject($subject)
            ->add_body($message)
            ->send();
    }

    public function send_email_notification() {

      if (empty($this->data['order_status_id'])) return;

      $order_status = reference::order_status($this->data['order_status_id'], $this->data['language_code']);

      $aliases = [
        '%order_id' => $this->data['id'],
        '%new_status' => $order_status->name,
        '%firstname' => $this->data['customer']['firstname'],
        '%lastname' => $this->data['customer']['lastname'],
        '%billing_address' => nl2br(functions::format_address($this->data['customer'])),
        '%payment_transaction_id' => !empty($this->data['payment_transaction_id']) ? $this->data['payment_transaction_id'] : '-',
        '%shipping_address' => nl2br(functions::format_address($this->data['customer']['shipping_address'])),
        '%shipping_tracking_id' => !empty($this->data['shipping_tracking_id']) ? $this->data['shipping_tracking_id'] : '-',
        '%shipping_tracking_url' => !empty($this->data['shipping_tracking_url']) ? $this->data['shipping_tracking_url'] : '',
        '%order_items' => null,
        '%payment_due' => currency::format($this->data['payment_due'], true, $this->data['currency_code'], $this->data['currency_value']),
        '%order_copy_url' => document::ilink('order', ['order_id' => $this->data['id'], 'public_key' => $this->data['public_key']], false, [], $this->data['language_code']),
        '%order_status' => $order_status->name,
        '%store_name' => settings::get('store_name'),
        '%store_url' => document::ilink('', [], false, [], $this->data['language_code']),
      ];

      foreach ($this->data['items'] as $item) {

        if (!empty($item['product_id'])) {
          $product = reference::product($item['product_id'], $this->data['language_code']);

          $options = [];
          if (!empty($item['options'])) {
            foreach ($item['options'] as $k => $v) {
              $options[] = $k .': '. $v;
            }
          }

          $aliases['%order_items'] .= (float)$item['quantity'] .' x '. $product->name . (!empty($options) ? ' ('. implode(', ', $options) .')' : '') . "<br />\r\n";

        } else {
          $aliases['%order_items'] .= (float)$item['quantity'] .' x '. $item['name'] . (!empty($options) ? ' ('. implode(', ', $options) .')' : '') . "<br />\r\n";
        }
      }

      $subject = strtr($order_status->email_subject, $aliases);
      $message = strtr($order_status->email_message, $aliases);

      if (empty($subject)) $subject = '['. language::translate('title_order', 'Order', $this->data['language_code']) .' #'. $this->data['id'] .'] '. $order_status->name;
      if (empty($message)) $message = strtr(language::translate('text_order_status_changed_to_new_status', 'Order status changed to %new_status', $this->data['language_code']), $aliases);

      if (!empty(language::$languages[$this->data['language_code']]) && language::$languages[$this->data['language_code']]['direction'] == 'rtl') {
        $message = '<div dir="rtl">' . PHP_EOL
                 . $message . PHP_EOL
                 . '</div>';
      }

      $email = new ent_email();
      $email->add_recipient($this->data['customer']['email'], $this->data['customer']['firstname'] .' '. $this->data['customer']['lastname'])
            ->set_subject($subject)
            ->add_body($message, true)
            ->send();
    }

    public function delete() {

      if (empty($this->data['id'])) return;

      $order_modules = new mod_order();
      $order_modules->delete($this->previous);

    // Empty order first..
      $this->data['items'] = [];
      $this->data['order_total'] = [];
      $this->refresh_total();
      $this->save();

    // ..then delete
      database::query(
        "delete from ". DB_TABLE_PREFIX ."orders
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->reset();

      cache::clear_cache('order');
      cache::clear_cache('category');
      cache::clear_cache('manufacturer');
      cache::clear_cache('products');
    }
  }
