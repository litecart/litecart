 <?php

  class ctrl_order {
    public $data;

    public function __construct($order_id=null) {

      if (!empty($order_id)) {
        $this->load((int)$order_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = array();

      $fields_query = database::query(
        "show fields from ". DB_TABLE_ORDERS .";"
      );

      while ($field = database::fetch($fields_query)) {

        if (preg_match('#^(customer|shipping|payment)_#', $field['Field'], $matches)) {

          switch ($field['Field']) {
            case 'shipping_company':
            case 'shipping_firstname':
            case 'shipping_lastname':
            case 'shipping_address1':
            case 'shipping_address2':
            case 'shipping_postcode':
            case 'shipping_city':
            case 'shipping_country_code':
            case 'shipping_zone_code':
              $field = preg_replace('#^('. preg_quote($matches[1], '#') .'_)#', '', $field['Field']);
              $this->data['customer']['shipping_address'][$field] = null;
              break;

            case 'payment_due':
              $this->data['payment_due'] = null;
              break;

            default:
              $field = preg_replace('#^('. preg_quote($matches[1], '#') .'_)#', '', $field['Field']);
              $this->data[$matches[1]][$field] = null;
              break;
          }

        } else {
          $this->data[$field['Field']] = null;
        }
      }

      $this->data = array_merge($this->data, array(
        'uid' => uniqid(),
        'weight_class' => settings::get('store_weight_class'),
        'currency_code' => currency::$selected['code'],
        'currency_value' => currency::$selected['value'],
        'language_code' => language::$selected['code'],
        'items' => array(),
        'order_total' => array(),
        'comments' => array(),
        'subtotal' => array('amount' => 0, 'tax' => 0),
      ));
    }

    private function load($order_id) {

      $this->reset();

      $order_query = database::query(
        "select * from ". DB_TABLE_ORDERS ."
        where id = ". (int)$order_id ."
        limit 1;"
      );

      if ($order = database::fetch($order_query)) {
        $this->data = array_intersect_key(array_merge($this->data, $order), $this->data);
      } else {
        trigger_error('Could not find order in database (ID: '. (int)$order_id .')', E_USER_ERROR);
      }

      foreach($order as $field => $value) {
        if (preg_match('#^(customer|shipping|payment)_#', $field, $matches)) {

          switch ($field) {
            case 'shipping_company':
            case 'shipping_firstname':
            case 'shipping_lastname':
            case 'shipping_address1':
            case 'shipping_address2':
            case 'shipping_postcode':
            case 'shipping_city':
            case 'shipping_country_code':
            case 'shipping_zone_code':
              $field = preg_replace('#^('. preg_quote($matches[1], '#') .'_)#', '', $field);
              $this->data['customer']['shipping_address'][$field] = $value;
              break;

            case 'payment_due':
              $this->data['payment_due'] = $value;
              break;
          }
        }
      }

      $order_items_query = database::query(
        "select * from ". DB_TABLE_ORDERS_ITEMS ."
        where order_id = '". (int)$order_id ."'
        order by id;"
      );
      while ($item = database::fetch($order_items_query)) {
        $item['options'] = unserialize($item['options']);
        $this->data['items'][$item['id']] = $item;
      }

      $order_totals_query = database::query(
        "select * from ". DB_TABLE_ORDERS_TOTALS ."
        where order_id = '". (int)$order_id ."'
        order by priority;"
      );
      while ($row = database::fetch($order_totals_query)) {
        $this->data['order_total'][$row['id']] = $row;
      }

      $order_comments_query = database::query(
        "select * from ". DB_TABLE_ORDERS_COMMENTS ."
        where order_id = '". (int)$order_id ."'
        order by id;"
      );
      while ($row = database::fetch($order_comments_query)) {
        $this->data['comments'][$row['id']] = $row;
      }
    }

    public function save() {

    // Re-calculate total if there are changes
      $this->refresh_total();

      if (empty($this->data['uid'])) $this->data['uid'] = uniqid();

    // Previous order status
      $previous_order_status_query = database::query(
        "select os.*, osi.name from ". DB_TABLE_ORDERS ." o
        left join ". DB_TABLE_ORDER_STATUSES ." os on (os.id = o.order_status_id)
        left join ". DB_TABLE_ORDER_STATUSES_INFO ." osi on (osi.order_status_id = o.order_status_id and osi.language_code = '". database::input($this->data['language_code']) ."')
        where o.id = ". (int)$this->data['id'] ."
        limit 1;"
      );
      $previous_order_status = database::fetch($previous_order_status_query);

    // Current order status
      $current_order_status_query = database::query(
        "select os.*, osi.name, osi.email_message from ". DB_TABLE_ORDER_STATUSES ." os
        left join ". DB_TABLE_ORDER_STATUSES_INFO ." osi on (os.id = osi.order_status_id and osi.language_code = '". database::input($this->data['language_code']) ."')
        where os.id = ". (int)$this->data['order_status_id'] ."
        limit 1;"
      );
      $current_order_status = database::fetch($current_order_status_query);

    // Log order status change as comment
      if (isset($previous_order_status['id']) && isset($current_order_status['id']) && $current_order_status['id'] != $previous_order_status['id']) {
        if (!empty($this->data['id'])) {
          $this->data['comments'][] = array(
            'author' => 'system',
            'text' => sprintf(language::translate('text_order_status_changed_to_s', 'Order status changed to %s'), $current_order_status['name']),
            'hidden' => 1,
          );
        }
      }

    // Link guests to customer profile
      if (empty($this->data['customer']['id']) && !empty($this->data['customer']['email'])) {
        $customers_query = database::query(
          "select id from ". DB_TABLE_CUSTOMERS ."
          where email = '". database::input($this->data['customer']['email']) ."'
          limit 1;"
        );
        $customer = database::fetch($customers_query);
        if (!empty($customer['id'])) {
          $this->data['customer']['id'] = $customer['id'];
        }
      }

    // Insert/update order
      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_ORDERS ."
          (uid, client_ip, date_created)
          values ('". database::input($this->data['uid']) ."', '". database::input($_SERVER['REMOTE_ADDR']) ."', '". database::input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_ORDERS ." set
        order_status_id = ". (int)$this->data['order_status_id'] .",
        customer_id = ". (int)$this->data['customer']['id'] .",
        customer_email = '". database::input($this->data['customer']['email']) ."',
        customer_phone = '". database::input($this->data['customer']['phone']) ."',
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
        shipping_company = '". database::input($this->data['customer']['shipping_address']['company']) ."',
        shipping_firstname = '". database::input($this->data['customer']['shipping_address']['firstname']) ."',
        shipping_lastname = '". database::input($this->data['customer']['shipping_address']['lastname']) ."',
        shipping_address1 = '". database::input($this->data['customer']['shipping_address']['address1']) ."',
        shipping_address2 = '". database::input($this->data['customer']['shipping_address']['address2']) ."',
        shipping_city = '". database::input($this->data['customer']['shipping_address']['city']) ."',
        shipping_postcode = '". database::input($this->data['customer']['shipping_address']['postcode']) ."',
        shipping_country_code = '". database::input($this->data['customer']['shipping_address']['country_code']) ."',
        shipping_zone_code = '". database::input($this->data['customer']['shipping_address']['zone_code']) ."',
        shipping_option_id = '". database::input($this->data['shipping']['option_id']) ."',
        shipping_option_name = '". database::input($this->data['shipping']['option_name']) ."',
        shipping_tracking_id = '". database::input($this->data['shipping']['tracking_id']) ."',
        payment_option_id = '". database::input($this->data['payment']['option_id']) ."',
        payment_option_name = '". database::input($this->data['payment']['option_name']) ."',
        payment_transaction_id = '". database::input($this->data['payment']['transaction_id']) ."',
        language_code = '". database::input($this->data['language_code']) ."',
        currency_code = '". database::input($this->data['currency_code']) ."',
        currency_value = ". (float)$this->data['currency_value'] .",
        weight_total = ". (float)$this->data['weight_total'] .",
        weight_class = '". database::input($this->data['weight_class']) ."',
        payment_due = ". (float)$this->data['payment_due'] .",
        tax_total = ". (float)$this->data['tax_total'] .",
        date_updated = '". date('Y-m-d H:i:s') ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

    // Delete order items
      $previous_order_items_query = database::query(
        "select * from ". DB_TABLE_ORDERS_ITEMS ."
        where order_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", array_column($this->data['items'], 'id')) ."');"
      );
      while($previous_order_item = database::fetch($previous_order_items_query)) {
        database::query(
          "delete from ". DB_TABLE_ORDERS_ITEMS ."
          where order_id = '". (int)$this->data['id'] ."'
          and id = '". (int)$previous_order_item['id'] ."'
          limit 1;"
        );

      // Restock
        if (!empty($previous_order_status['is_sale'])) {
          functions::catalog_stock_adjust($previous_order_item['product_id'], $previous_order_item['option_stock_combination'], $previous_order_item['quantity']);
        }
      }

    // Insert/update order items
      if (!empty($this->data['items'])) {
        foreach (array_keys($this->data['items']) as $key) {
          if (empty($this->data['items'][$key]['id'])) {
            database::query(
              "insert into ". DB_TABLE_ORDERS_ITEMS ."
              (order_id)
              values ('". (int)$this->data['id'] ."');"
            );
            $this->data['items'][$key]['id'] = database::insert_id();

          // Update purchase count
            if (!empty($this->data['items'][$key]['product_id'])) {
              database::query(
                "update ". DB_TABLE_PRODUCTS ."
                set purchases = purchases + ". (float)$this->data['items'][$key]['quantity'] ."
                where id = ". (int)$this->data['items'][$key]['product_id'] ."
                limit 1;"
              );
            }
          }

        // Get previous quantity
          $previous_order_item_query = database::query(
            "select * from ". DB_TABLE_ORDERS_ITEMS ."
            where id = '". (int)$this->data['items'][$key]['id'] ."'
            and order_id = '". (int)$this->data['id'] ."';"
          );
          $previous_order_item = database::fetch($previous_order_item_query);

        // Adjust stock
          if (!empty($previous_order_status['is_sale'])) {
            functions::catalog_stock_adjust($previous_order_item['product_id'], $previous_order_item['option_stock_combination'], $previous_order_item['quantity']);
          }
          if (!empty($current_order_status['is_sale'])) {
            functions::catalog_stock_adjust($this->data['items'][$key]['product_id'], $this->data['items'][$key]['option_stock_combination'], -$this->data['items'][$key]['quantity']);
          }

          database::query(
            "update ". DB_TABLE_ORDERS_ITEMS ."
            set product_id = '". (int)$this->data['items'][$key]['product_id'] ."',
            option_stock_combination = '". database::input($this->data['items'][$key]['option_stock_combination']) ."',
            options = '".  (isset($this->data['items'][$key]['options']) ? database::input(serialize($this->data['items'][$key]['options'])) : '') ."',
            name = '". database::input($this->data['items'][$key]['name']) ."',
            sku = '". database::input($this->data['items'][$key]['sku']) ."',
            quantity = '". (float)$this->data['items'][$key]['quantity'] ."',
            price = '". (float)$this->data['items'][$key]['price'] ."',
            tax = '". (float)$this->data['items'][$key]['tax'] ."',
            weight = '". (isset($this->data['items'][$key]['weight']) ? (float)$this->data['items'][$key]['weight'] : '') ."',
            weight_class = '". (isset($this->data['items'][$key]['weight_class']) ? database::input($this->data['items'][$key]['weight_class']) : '') ."'
            where order_id = '". (int)$this->data['id'] ."'
            and id = '". (int)$this->data['items'][$key]['id'] ."'
            limit 1;"
          );
        }
      }

    // Delete order total items
      database::query(
        "delete from ". DB_TABLE_ORDERS_TOTALS ."
        where order_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", array_column($this->data['order_total'], 'id')) ."');"
      );

    // Insert/update order total
      if (!empty($this->data['order_total'])) {
        $i = 0;
        foreach (array_keys($this->data['order_total']) as $key) {
          if (empty($this->data['order_total'][$key]['id'])) {
            database::query(
              "insert into ". DB_TABLE_ORDERS_TOTALS ."
              (order_id)
              values ('". (int)$this->data['id'] ."');"
            );
            $this->data['order_total'][$key]['id'] = database::insert_id();
          }
          database::query(
            "update ". DB_TABLE_ORDERS_TOTALS ."
            set title = '". database::input($this->data['order_total'][$key]['title']) ."',
            module_id = '". database::input($this->data['order_total'][$key]['module_id']) ."',
            value = '". (float)$this->data['order_total'][$key]['value'] ."',
            tax = '". (float)$this->data['order_total'][$key]['tax'] ."',
            calculate = '". (empty($this->data['order_total'][$key]['calculate']) ? 0 : 1) ."',
            priority = '". database::input(++$i) ."'
            where order_id = '". (int)$this->data['id'] ."'
            and id = '". (int)$this->data['order_total'][$key]['id'] ."'
            limit 1;"
          );
        }
      }

    // Delete comments
      database::query(
        "delete from ". DB_TABLE_ORDERS_COMMENTS ."
        where order_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", array_column($this->data['comments'], 'id')) ."');"
      );

    // Insert/update comments
      if (!empty($this->data['comments'])) {
        foreach (array_keys($this->data['comments']) as $key) {
          if (empty($this->data['comments'][$key]['id'])) {
            database::query(
              "insert into ". DB_TABLE_ORDERS_COMMENTS ."
              (order_id, date_created)
              values ('". (int)$this->data['id'] ."', '". date('Y-m-d H:i:s') ."');"
            );
            $this->data['comments'][$key]['id'] = database::insert_id();
            $this->data['comments'][$key]['date_created'] = date('Y-m-d H:i:s');
          }
          database::query(
            "update ". DB_TABLE_ORDERS_COMMENTS ."
            set author = '". (!empty($this->data['comments'][$key]['author']) ? database::input($this->data['comments'][$key]['author']) : 'system') ."',
              text = '". database::input($this->data['comments'][$key]['text']) ."',
              hidden = '". (!empty($this->data['comments'][$key]['hidden']) ? 1 : 0) ."'
            where order_id = '". (int)$this->data['id'] ."'
            and id = '". (int)$this->data['comments'][$key]['id'] ."'
            limit 1;"
          );
        }
      }

    // Send update notice email
      if (empty($previous_order_status['id']) || (isset($current_order_status['id']) && $current_order_status['id'] != $previous_order_status['id'])) {
        if (!empty($current_order_status['notify'])) {

        // Prepare email body
          if (empty($current_order_status['email_message']) || trim(strip_tags($current_order_status['email_message'])) == '') {
            $email_body = $this->draw_printable_copy();
          } else {
            $email_body = $this->inject_email_message($current_order_status['email_message']);
          }

          functions::email_send(
            null,
            $this->data['customer']['email'],
            strtr(language::translate('email_subject_order_updated', 'Order %order_id has updated to %order_status', $this->data['language_code']), array(
              '%order_id' => (int)$this->data['id'],
              '%order_status' => $current_order_status['name'],
            )),
            $email_body,
            true
          );
        }
      }

      cache::clear_cache('order');
      cache::clear_cache('product');
    }

    public function delete() {
      if (empty($this->data['id'])) return;

    // Empty order first..
      $this->data['items'] = array();
      $this->data['order_total'] = array();
      $this->refresh_total();
      $this->save();

    // ..then delete
      database::query(
        "delete from ". DB_TABLE_ORDERS ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
    }

    public function refresh_total() {
      $this->data['subtotal'] = array('amount' => 0, 'tax' => 0);
      $this->data['payment_due'] = 0;
      $this->data['tax_total'] = 0;
      $this->data['weight_total'] = 0;

      foreach ($this->data['items'] as $item) {
        $this->data['subtotal']['amount'] += $item['price'] * $item['quantity'];
        $this->data['subtotal']['tax'] += $item['tax'] * $item['quantity'];
        $this->data['payment_due'] += ($item['price'] + $item['tax']) * $item['quantity'];
        $this->data['tax_total'] += $item['tax'] * $item['quantity'];
        $this->data['weight_total'] += weight::convert($item['weight'], $item['weight_class'], $this->data['weight_class']) * $item['quantity'];
      }

      foreach ($this->data['order_total'] as $order_total) {
        if (!empty($order_total['calculate'])) {
          $this->data['payment_due'] += ($item['price'] + $item['tax']);
          $this->data['tax_total'] += $item['tax'];
        }
      }
    }

    public function add_item($item) {

      $item = array(
        'id' => '',
        'product_id' => $item['product_id'],
        'options' => $item['options'],
        'option_stock_combination' => $item['option_stock_combination'],
        'name' => $item['name'],
        'sku' => $item['sku'],
        'price' => $item['price'],
        'tax' => $item['tax'],
        'quantity' => $item['quantity'],
        'weight' => isset($item['weight']) ? $item['weight'] : '',
        'weight_class' => isset($item['weight_class']) ? $item['weight_class'] : '',
      );

      $i = 1;
      while (isset($this->data['items']['new_'.$i])) $i++;
      $this->data['items']['new_'.$i] = $item;

      $this->data['subtotal']['amount'] += $item['price'] * $item['quantity'];
      $this->data['subtotal']['tax'] += $item['tax'] * $item['quantity'];
      $this->data['payment_due'] += ($item['price'] + $item['tax']) * $item['quantity'];
      $this->data['tax_total'] += $item['tax'] * $item['quantity'];
      $this->data['weight_total'] += weight::convert($item['weight'], $item['weight_class'], $this->data['weight_class']) * $item['quantity'];
    }

    public function add_ot_row($row) {

      $row = array(
        'id' => 0,
        'module_id' => $row['id'],
        'title' =>  $row['title'],
        'value' => $row['value'],
        'tax' => $row['tax'],
        'calculate' => !empty($row['calculate']) ? 1 : 0,
      );

      $i = 1;
      while (isset($this->data['order_total']['new_'.$i])) $i++;
      $this->data['order_total']['new_'.$i] = $row;

      if (!empty($row['calculate'])) {
        $this->data['payment_due'] += $row['value'] + $row['tax'];
        $this->data['tax_total'] += $row['tax'];
      }
    }

    public function validate() {

    // Validate items
      if (empty($this->data['items'])) return language::translate('error_order_missing_items', 'The order does not contain any items');

    // Validate customer details
      try {
        if (empty($this->data['customer']['firstname'])) throw new Exception(language::translate('error_missing_firstname', 'You must enter a first name.'));
        if (empty($this->data['customer']['lastname'])) throw new Exception(language::translate('error_missing_lastname', 'You must enter a last name.'));
        if (empty($this->data['customer']['address1'])) throw new Exception(language::translate('error_missing_address1', 'You must enter an address.'));
        if (empty($this->data['customer']['city'])) throw new Exception(language::translate('error_missing_city', 'You must enter a city.'));
        if (empty($this->data['customer']['country_code'])) throw new Exception(language::translate('error_missing_country', 'You must select a country.'));
        if (empty($this->data['customer']['email'])) throw new Exception(language::translate('error_missing_email', 'You must enter your email address.'));
        if (empty($this->data['customer']['phone'])) throw new Exception(language::translate('error_missing_phone', 'You must enter your phone number.'));

        if (reference::country($this->data['customer']['country_code'])->postcode_format) {
          if (!empty($this->data['customer']['postcode'])) {
            if (!preg_match('#'. reference::country($this->data['customer']['country_code'])->postcode_format .'#i', $this->data['customer']['postcode'])) {
              throw new Exception(language::translate('error_invalid_postcode_format', 'Invalid postcode format.'));
            }
          } else {
            throw new Exception(language::translate('error_missing_postcode', 'You must enter a postcode.'));
          }
        }

        if (reference::country($this->data['customer']['country_code'])->zones) {
          if (empty($this->data['customer']['zone_code']) && reference::country($this->data['customer']['country_code'])->zones) throw new Exception(language::translate('error_missing_zone', 'You must select a zone.'));
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

          if (reference::country($this->data['customer']['country_code'])->zones) {
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
      if (!empty($GLOBALS['shipping'])) {
        if (!empty($GLOBALS['shipping']->modules) && count($GLOBALS['shipping']->options()) > 0) {
          if (empty($GLOBALS['shipping']->data['selected']['id'])) {
            return language::translate('error_no_shipping_method_selected', 'No shipping method selected');
          } else {
            list($module_id, $option_id) = explode(':', $GLOBALS['shipping']->data['selected']['id']);
            if (empty($GLOBALS['shipping']->data['options'][$module_id]['options'][$option_id])) {
              return language::translate('error_invalid_shipping_method_selected', 'Invalid shipping method selected');
            }
          }
        }
      }

    // Validate payment option
      if (!empty($GLOBALS['payment'])) {
        if (!empty($GLOBALS['payment']->modules) && count($GLOBALS['payment']->options()) > 0) {
          if (empty($GLOBALS['payment']->data['selected']['id'])) {
            return language::translate('error_no_payment_method_selected', 'No payment method selected');
          } else {
            list($module_id, $option_id) = explode(':', $GLOBALS['payment']->data['selected']['id']);
            if (empty($GLOBALS['payment']->data['options'][$module_id]['options'][$option_id])) {
              return language::translate('error_invalid_payment_method_selected', 'Invalid payment method selected');
            }
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

    public function inject_email_message($html) {

      $order_status_query = database::query(
        "select os.*, osi.name, osi.email_message from ". DB_TABLE_ORDER_STATUSES ." os
        left join ". DB_TABLE_ORDER_STATUSES_INFO ." osi on (os.id = osi.order_status_id and osi.language_code = '". database::input($this->data['language_code']) ."')
        where os.id = ". (int)$this->data['order_status_id'] ."
        limit 1;"
      );
      $order_status = database::fetch($order_status_query);

      $aliases = array(
        '%order_id' => $this->data['id'],
        '%firstname' => $this->data['customer']['firstname'],
        '%lastname' => $this->data['customer']['lastname'],
        '%billing_address' => nl2br(functions::format_address($this->data['customer'])),
        '%payment_transaction_id' => !empty($this->data['payment_transaction_id']) ? $this->data['payment_transaction_id'] : '-',
        '%shipping_address' => nl2br(functions::format_address($this->data['customer']['shipping_address'])),
        '%shipping_address' => nl2br(functions::format_address($this->data['customer']['shipping_address'])),
        '%shipping_tracking_id' => !empty($this->data['shipping_tracking_id']) ? $this->data['shipping_tracking_id'] : '-',
        '%order_copy_url' => document::ilink('printable_order_copy', array('order_id' => $this->data['id'], 'checksum' => functions::general_order_public_checksum($this->data['id']))),
        '%order_status' => $order_status['name'],
      );

      $html = strtr($html, $aliases);

      return $html;
    }

    public function email_order_copy($email) {

      if (empty($email)) return;

      $session_language = language::$selected['code'];
      language::set($this->data['language_code']);

      $action_button = '<div itemscope itemtype="https://schema.org/EmailMessage" style="display:none">' . PHP_EOL
                     . '  <div itemprop="potentialAction" itemscope itemtype="https://schema.org/ViewAction">' . PHP_EOL
                     . '    <link itemprop="target url" href="'. document::href_ilink('printable_order_copy', array('order_id' => $this->data['id'], 'checksum' => functions::general_order_public_checksum($this->data['id']))) .'" />' . PHP_EOL
                     . '    <meta itemprop="name" content="'. htmlspecialchars(language::translate('title_view_order', 'View Order')) .'" />' . PHP_EOL
                     . '  </div>' . PHP_EOL
                     . '  <meta itemprop="description" content="'. htmlspecialchars(language::translate('title_view_printable_order_copy', 'View printable order copy')) .'" />' . PHP_EOL
                     . '</div>';

      functions::email_send(
        null,
        $email,
        language::translate('title_order_copy', 'Order Copy') .' #'. $this->data['id'],
        $this->draw_printable_copy() . $action_button,
        true
      );

      language::set($session_language);
    }

    public function draw_printable_copy() {

      $session_language = language::$selected['code'];
      language::set($this->data['language_code']);

      $printable_order_copy = new view();
      $printable_order_copy->snippets['order'] = $this->data;
      $output = $printable_order_copy->stitch('pages/printable_order_copy');

      language::set($session_language);

      return $output;
    }

    public function draw_printable_packing_slip() {

      $session_language = language::$selected['code'];
      language::set($this->data['language_code']);

      $printable_packing_slip = new view();
      $printable_packing_slip->snippets['order'] = $this->data;
      $output = $printable_packing_slip->stitch('pages/printable_packing_slip');

      language::set($session_language);

      return $output;
    }
  }

?>