 <?php
  
  class ctrl_order {
    public $data;
    
    public function __construct($action='new', $order_id='') {
      
      if (!isset(session::$data['order'])) session::$data['order'] = array();
      $this->data = &session::$data['order'];
      
      switch ($action) {
        case 'load':
          if (empty($order_id)) trigger_error('Unknown order id', E_USER_ERROR);
          self::load((int)$order_id);
          break;
        case 'new':
          self::reset();
          break;
        case 'import_session':
          self::import_session();
          break;
        case 'resume':
        default:
          break;
      }
    }
    
    public function reset() {
      
      $this->data = array(
        'id' => '',
        'uid' => uniqid(),
        'items' => array(),
        'weight_total' => 0,
        'weight_class' => settings::get('store_weight_class'),
        'currency_code' => currency::$selected['code'],
        'currency_value' => currency::$selected['value'],
        'language_code' => language::$selected['code'],
        'customer' => array(
          'id' => '',
          'email' => '',
          'desired_password' => '',
          'phone' => '',
          'tax_id' => '',
          'company' => '',
          'firstname' => '',
          'lastname' => '',
          'address1' => '',
          'address2' => '',
          'city' => '',
          'postcode' => '',
          'country_code' => '',
          'zone_code' => '',
          'shipping_address' => array(
            'company' => '',
            'firstname' => '',
            'lastname' => '',
            'address1' => '',
            'address2' => '',
            'city' => '',
            'postcode' => '',
            'country_code' => '',
            'zone_code' => '',
          ),
        ),
        'shipping_option' => array(),
        'shipping_tracking_id' => '',
        'payment_option' => array(),
        'payment_transaction_id' => '',
        'order_total' => array(),
        'tax_total' => 0,
        'weight_total' => 0,
        'weight_class' => settings::get('store_weight_class'),
        'payment_due' => 0,
        'order_status_id' => 0,
        'comments' => array(),
      );
    }
    
    private function import_session() {
      global $shipping, $payment, $order_total;
      
      self::reset();
      
      $this->data['weight_class'] = settings::get('store_weight_class');
      $this->data['currency_code'] = currency::$selected['code'];
      $this->data['currency_value'] = currency::$currencies[currency::$selected['code']]['value'];
      $this->data['language_code'] = language::$selected['code'];
      
      $this->data['customer'] = customer::$data;
      
      if (!empty($shipping->data['selected'])) {
        $this->data['shipping_option'] = array(
          'id' => $shipping->data['selected']['id'],
          'name' => $shipping->data['selected']['title'] .' ('. $shipping->data['selected']['name'] .')',
        );
      }
      
      if (!empty($payment->data['selected'])) {
        $this->data['payment_option'] = array(
          'id' => $payment->data['selected']['id'],
          'name' => $payment->data['selected']['title'] .' ('. $payment->data['selected']['name'] .')',
        );
      }
      
      foreach (cart::$items as $item) {
        self::add_item($item);
      }
      
      foreach ($order_total->rows as $row) {
        self::add_ot_row($row);
      }
    }
    
    private function load($order_id) {
      
      self::reset();
      
      $order_query = database::query(
        "select * from ". DB_TABLE_ORDERS ."
        where id = '". (int)$order_id ."'
        limit 1;"
      );
      $order = database::fetch($order_query);
      if (empty($order)) trigger_error('Could not find order in database (ID: '. (int)$order_id .')', E_USER_ERROR);
      
      $key_map = array(
        'id' => 'id',
        'weight_total' => 'weight_total',
        'weight_class' => 'weight_class',
        'currency_code' => 'currency_code',
        'currency_value' => 'currency_value',
        'language_code' => 'language_code',
        'payment_due' => 'payment_due',
        'tax_total' => 'tax_total',
        'order_status_id' => 'order_status_id',
        'shipping_tracking_id' => 'shipping_tracking_id',
        'payment_transaction_id' => 'payment_transaction_id',
        'client_ip' => 'client_ip',
        'date_updated' => 'date_updated',
        'date_created' => 'date_created',
      );
      foreach ($key_map as $skey => $tkey){
        $this->data[$tkey] = $order[$skey];
      }
      
      $key_map = array(
        'customer_id' => 'id',
        'customer_email' => 'email',
        'customer_tax_id' => 'tax_id',
        'customer_company' => 'company',
        'customer_firstname' => 'firstname',
        'customer_lastname' => 'lastname',
        'customer_address1' => 'address1',
        'customer_address2' => 'address2',
        'customer_postcode' => 'postcode',
        'customer_city' => 'city',
        'customer_phone' => 'phone',
        'customer_mobile' => 'mobile',
        'customer_country_code' => 'country_code',
        'customer_zone_code' => 'zone_code',
      );
      foreach ($key_map as $skey => $tkey){
        $this->data['customer'][$tkey] = $order[$skey];
      }
      
      $key_map = array(
        'shipping_company' => 'company',
        'shipping_firstname' => 'firstname',
        'shipping_lastname' => 'lastname',
        'shipping_address1' => 'address1',
        'shipping_address2' => 'address2',
        'shipping_postcode' => 'postcode',
        'shipping_city' => 'city',
        'shipping_country_code' => 'country_code',
        'shipping_zone_code' => 'zone_code',
      );
      foreach ($key_map as $skey => $tkey){
        $this->data['customer']['shipping_address'][$tkey] = $order[$skey];
      }
      
      $key_map = array(
        'shipping_option_id' => 'id',
        'shipping_option_name' => 'name',
      );
      foreach ($key_map as $skey => $tkey){
        $this->data['shipping_option'][$tkey] = $order[$skey];
      }
      
      $key_map = array(
        'payment_option_id' => 'id',
        'payment_option_name' => 'name',
      );
      foreach ($key_map as $skey => $tkey){
        $this->data['payment_option'][$tkey] = $order[$skey];
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
      self::calculate_total();
      
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
            'text' => sprintf(language::translate('text_order_status_changed_to_s', 'Order status changed to %s'), $current_order_status['name']),
            'hidden' => 1,
          );
        }
      }
      
    // Link guests to customer profile
      if (empty($this->data['customer']['id'])) {
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
        
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
          $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
          $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        
        database::query(
          "insert into ". DB_TABLE_ORDERS ."
          (uid, client_ip, date_created)
          values ('". database::input($this->data['uid']) ."', '". database::input($ip) ."', '". database::input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }
      
      database::query(
        "update ". DB_TABLE_ORDERS ." set
        order_status_id = '". (int)$this->data['order_status_id'] ."',
        customer_id = '". (int)$this->data['customer']['id'] ."',
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
        shipping_option_id = '". ((!empty($this->data['shipping_option'])) ? database::input($this->data['shipping_option']['id']) : false) ."',
        shipping_option_name = '". ((!empty($this->data['shipping_option'])) ? database::input($this->data['shipping_option']['name']) : false) ."',
        shipping_tracking_id = '". ((!empty($this->data['shipping_tracking_id'])) ? database::input($this->data['shipping_tracking_id']) : false) ."',
        payment_option_id = '". ((!empty($this->data['payment_option'])) ? database::input($this->data['payment_option']['id']) : false) ."',
        payment_option_name = '". ((!empty($this->data['payment_option'])) ? database::input($this->data['payment_option']['name']) : false) ."',
        payment_transaction_id = '". ((!empty($this->data['payment_transaction_id'])) ? database::input($this->data['payment_transaction_id']) : false) ."',
        language_code = '". database::input($this->data['language_code']) ."',
        currency_code = '". database::input($this->data['currency_code']) ."',
        currency_value = '". (float)$this->data['currency_value'] ."',
        weight_total = '". (float)$this->data['weight_total'] ."',
        weight_class = '". database::input($this->data['weight_class']) ."',
        payment_due = '". (float)$this->data['payment_due'] ."',
        tax_total = '". (float)$this->data['tax_total'] ."',
        date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
    // Build array of item ids
      $item_ids = array();
      if (!empty($this->data['items'])) {
        foreach (array_keys($this->data['items']) as $key) {
          if (!empty($this->data['items'][$key]['id'])) $item_ids[] = $this->data['items'][$key]['id'];
        }
      }
      
    // Delete order items
      $previous_order_items_query = database::query(
        "select * from ". DB_TABLE_ORDERS_ITEMS ."
        where order_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", $item_ids) ."');"
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
      
    // Build array of order total ids
      $order_total_ids = array();
      if (!empty($this->data['order_total'])) {
        foreach (array_keys($this->data['order_total']) as $key) {
          if (!empty($this->data['order_total'][$key]['id'])) $order_total_ids[] = $this->data['order_total'][$key]['id'];
        }
      }
      
    // Delete order total items
      database::query(
        "delete from ". DB_TABLE_ORDERS_TOTALS ."
        where order_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", $order_total_ids) ."');;"
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
      
    // Build array of comments ids
      $comments_ids = array();
      if (!empty($this->data['comments'])) {
        foreach (array_keys($this->data['comments']) as $key) {
          if (!empty($this->data['comments'][$key]['id'])) $comments_ids[] = $this->data['comments'][$key]['id'];
        }
      }
      
    // Delete comments
      database::query(
        "delete from ". DB_TABLE_ORDERS_COMMENTS ."
        where order_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", $comments_ids) ."');"
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
            set author = '". database::input($this->data['comments'][$key]['author']) ."',
              text = '". database::input($this->data['comments'][$key]['text']) ."',
              hidden = '". (empty($this->data['comments'][$key]['hidden']) ? 0 : 1) ."'
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
            strtr(language::translate('email_subject_order_updated', 'Order Update: %s', $this->data['language_code']), array(
              'id' => (int)$this->data['id'],
              'status' => $current_order_status['name'],
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
      self::calculate_total();
      self::save();
      
    // ..then delete
      database::query(
        "delete from ". DB_TABLE_ORDERS ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
    }
    
    public function calculate_total() {
      $this->data['payment_due'] = 0;
      $this->data['tax_total'] = 0;
      $this->data['weight_total'] = 0;
      
      foreach ($this->data['items'] as $item) {
        self::add_cost($item['price'], $item['tax'], $item['quantity']);
        $this->data['weight_total'] += weight::convert($item['weight'], $item['weight_class'], $this->data['weight_class']) * $item['quantity'];
      }
      
      foreach ($this->data['order_total'] as $order_total) {
        if (!empty($order_total['calculate'])) {
          self::add_cost($order_total['value'], $order_total['tax']);
        }
      }
    }
    
    public function add_item($item) {
      
      $key_i = 1;
      while (isset($this->data['items']['new'.$key_i])) $key_i++;
      
      $this->data['items']['new'.$key_i] = array(
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
      
      $this->data['weight_total'] += $item['quantity'] * weight::convert($item['weight'], $item['weight_class'], settings::get('store_weight_class'));
      
      self::add_cost($item['price'] * $item['quantity'], $this->data['items']['new'.$key_i]['tax'] * $item['quantity']);
    }
    
    public function add_ot_row($row) {
      
      $key_i = 1;
      while (isset($this->data['order_total']['new'.$key_i])) $key_i++;
      
      $this->data['order_total']['new'.$key_i] = array(
        'id' => 0,
        'module_id' => $row['id'],
        'title' =>  $row['title'],
        'value' => $row['value'],
        'tax' => $row['tax'],
        'calculate' => !empty($row['calculate']) ? 1 : 0,
      );
      
      if (!empty($row['calculate'])) self::add_cost($row['value'], $row['tax']);
    }
    
    private function add_cost($gross, $tax, $quantity=1) {
      $this->data['payment_due'] += $gross * $quantity;
      $this->data['payment_due'] += $tax * $quantity;
      $this->data['tax_total'] += $tax * $quantity;
    }
    
    public function checkout_forbidden() {
      
      if (empty($this->data['items'])) return language::translate('error_order_missing_items', 'Your order does not contain any items');
      
      $required_fields = array(
        'email',
        'firstname',
        'lastname',
        'address1',
        'city',
        'country_code',
        'phone',
      );
      
      if (functions::reference_get_postcode_required($this->data['customer']['country_code'])) $required_fields[] = 'postcode';
      if (functions::reference_country_num_zones($this->data['customer']['country_code'])) $required_fields[] = 'zone_code';
      
      foreach ($required_fields as $field) {
        if (empty($this->data['customer'][$field])) return language::translate('error_insufficient_customer_information', 'Insufficient customer information, please fill out all necessary fields.') /*. ' ('.$field.')'*/;
      }
      
      if ($this->data['customer']['different_shipping_address']) {
        $required_fields = array(
          'firstname',
          'lastname',
          'address1',
          'city',
          'country_code',
        );
        if (functions::reference_get_postcode_required($this->data['customer']['shipping_address']['country_code'])) $required_fields[] = 'postcode';
        if (functions::reference_country_num_zones($this->data['customer']['shipping_address']['country_code'])) $required_fields[] = 'zone_code';
        
        foreach ($required_fields as $field) {
          if (empty($this->data['customer']['shipping_address'][$field])) return language::translate('error_insufficient_customer_information', 'Insufficient customer information, please fill out all necessary fields.') /*. ' (shipping_address['.$field.'])'*/;
        }
      }
      
      return false;
    }
    
    public function inject_email_message($html) {
      
      $aliases = array(
        '%order_id' => $this->data['id'],
        '%firstname' => $this->data['customer']['firstname'],
        '%lastname' => $this->data['customer']['lastname'],
        '%billing_address' => nl2br(functions::format_address($this->data['customer'])),
        '%payment_transaction_id' => !empty($this->data['payment_transaction_id']) ? $this->data['payment_transaction_id'] : '-',
        '%shipping_address' => nl2br(functions::format_address($this->data['customer']['shipping_address'])),
        '%shipping_address' => nl2br(functions::format_address($this->data['customer']['shipping_address'])),
        '%shipping_tracking_id' => !empty($this->data['shipping_tracking_id']) ? $this->data['shipping_tracking_id'] : '-',
        '%order_copy_url' => document::ilink('printable_order_copy', array('order_id' => $this->data['id'], 'checksum' => functions::general_order_public_checksum($this->data['id'])))
      );
    
      $html = str_replace(array_keys($aliases), array_values($aliases), $html);
    
      return $html;
    }
    
    public function email_order_copy($email) {
    
      if (empty($email)) return;
    
      functions::email_send(
        null,
        $email,
        language::translate('title_order_copy', 'Order Copy') .' #'. $this->data['id'],
        self::draw_printable_copy(),
        true
      );
    }
    
    public function draw_printable_copy() {
    
      $printable_order_copy = new view();
      
      $printable_order_copy->snippets['order'] = $this->data;
      
      return $printable_order_copy->stitch('views/printable_order_copy');
    }
    
    public function draw_printable_packing_slip() {
    
      $printable_packing_slip = new view();
      
      $printable_packing_slip->snippets['order'] = $this->data;
      
      return $printable_packing_slip->stitch('views/printable_packing_slip');
    }
  }

?>