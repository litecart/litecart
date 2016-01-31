<?php
  
  class cart {
    
    public static $data = array();
    public static $items = array();
    public static $total = array();
    
    //public static function construct() {
    //}
    
    public static function load_dependencies() {
      
      if (!isset(session::$data['cart']) || !is_array(session::$data['cart'])) {
        session::$data['cart'] = array(
          'uid' => null,
        );
      }
      
      self::$data = &session::$data['cart'];
    }
    
    public static function initiate() {
      
    // Recover a previous cart uid if possible
      if (empty(self::$data['uid'])) {
        if (!empty($_COOKIE['cart']['uid'])) {
          self::$data['uid'] = $_COOKIE['cart']['uid'];
        } else {
          self::$data['uid'] = uniqid();
        }
      }
      
    // Update cart cookie
      if (!isset($_COOKIE['cart']['uid']) || $_COOKIE['cart']['uid'] != self::$data['uid']) {
        setcookie('cart[uid]', self::$data['uid'], strtotime('+1 years'), WS_DIR_HTTP_HOME);
      }
      
      database::query(
        "delete from ". DB_TABLE_CART_ITEMS ."
        where date_created < '". date('Y-m-d H:i:s', strtotime('-1 years')) ."';"
      );
    }
    
    public static function startup() {
      
    // Load/Refresh
      self::load();
      
      
      if (!empty($_POST['add_cart_product'])) {
        
        $options = !empty($_POST['options']) ? $_POST['options'] : array();
        if (!empty($options)) {
          foreach (array_keys($options) as $key) {
            if (is_array($options[$key])) $options[$key] = implode(', ', $options[$key]);
          }
        }
        
        self::add_product($_POST['product_id'], $options, (isset($_POST['quantity']) ? $_POST['quantity'] : 1));
      }
      
      if (!empty($_POST['remove_cart_item'])) {
        self::remove($_POST['key']);
      }
      
      if (!empty($_POST['update_cart_item'])) {
        self::update($_POST['key'], (isset($_POST['quantity']) ? $_POST['quantity'] : 1));
      }
      
      if (!empty($_POST['clear_cart_items'])) {
        self::clear();
      }
    }
    
    //public static function before_capture() {
    //}
    
    //public static function after_capture() {
    //}
    
    //public static function prepare_output() {
    //}
    
    //public static function before_output() {
    //}
    
    //public static function shutdown() {
    //}
    
    ######################################################################
    
    public static function reset() {
    
      self::$items = array();
      
      self::_calculate_total();
    }
    
    public static function clear() {
      
      self::reset();
      
      database::query(
        "delete from ". DB_TABLE_CART_ITEMS ."
        where cart_uid = '". database::input(self::$data['uid']) ."';"
      );
    }
    
    public static function load() {
      
      self::reset();
      
      if (!empty(customer::$data['id'])) {
        database::query(
          "update ". DB_TABLE_CART_ITEMS ."
          set cart_uid = '". database::input(self::$data['uid']) ."'
          where customer_id = ".(int)customer::$data['id'] .";"
        );
        database::query(
          "update ". DB_TABLE_CART_ITEMS ."
          set customer_id = ".(int)customer::$data['id'] ."
          where cart_uid = '". database::input(self::$data['uid']) ."';"
        );
      }
      
      $cart_items_query = database::query(
        "select * from ". DB_TABLE_CART_ITEMS ."
        where cart_uid = '". database::input(self::$data['uid']) ."';"
      );
      
      while ($item = database::fetch($cart_items_query)) {
        self::add_product($item['product_id'], unserialize($item['options']), $item['quantity'], true, $item['key']);
      }
    }
    
    public static function add_product($product_id, $options, $quantity=1, $silent=false, $item_key=null) {
      
      if ($quantity <= 0) {
        if (!$silent) notices::add('errors', language::translate('error_cannot_add_to_cart_invalid_quantity', 'Cannot add product to cart. Invalid quantity'));
        return;
      }
      
      $product = catalog::product($product_id);
      
      if ($product->status == 0) {
        if (!$silent) notices::add('errors', language::translate('text_product_not_available_for_purchase', 'The product is not available for purchase'));
        return;
      }
      
      if ($product->date_valid_from > date('Y-m-d H:i:s')) {
        if (!$silent) notices::add('errors', sprintf(language::translate('text_product_cannot_be_purchased_until_s', 'The product cannot be purchased until %s'), language::strftime(language::$selected['format_date'], strtotime($product->date_valid_from))));
        return;
      }
      
      if ($product->date_valid_to > '1971' && $product->date_valid_to < date('Y-m-d H:i:s')) {
        if (!$silent) notices::add('errors', language::translate('text_product_can_no_longer_be_purchased', 'The product can no longer be purchased'));
        return;
      }
      
      if (($product->quantity - $quantity) < 0 && empty($product->sold_out_status['orderable'])) {
        if (!$silent) {
          notices::add('errors', language::translate('text_not_enough_products_in_stock', 'There are not enough products in stock.') .' ('. round($product->quantity, $product->quantity_unit['decimals']) .')');
          return;
        } else {
          $quantity = $product->quantity;
        }
      }
      
      $item = array(
        'id' => '',
        'product_id' => (int)$product_id,
        'options' => $options,
        'option_stock_combination' => '',
        'image' => $product->image,
        'name' => $product->name[language::$selected['code']],
        'code' => $product->code,
        'sku' =>  $product->sku,
        'gtin' =>  $product->gtin,
        'taric' =>  $product->taric,
        'price' => $product->campaign['price'] ? $product->campaign['price'] : $product->price,
        'extras' => 0,
        'tax' => tax::get_tax($product->campaign['price'] ? $product->campaign['price'] : $product->price, $product->tax_class_id),
        'tax_class_id' => $product->tax_class_id,
        'quantity' => round($quantity, $product->quantity_unit['decimals'], PHP_ROUND_HALF_UP),
        'quantity_unit' => array(
          'name' => $product->quantity_unit['name'][language::$selected['code']],
          'decimals' => $product->quantity_unit['decimals'],
          'separate' => $product->quantity_unit['separate'],
        ),
        'weight' => $product->weight,
        'weight_class' => $product->weight_class,
        'dim_x' => $product->dim_x,
        'dim_y' => $product->dim_y,
        'dim_z' => $product->dim_z,
        'dim_class' => $product->dim_class,
        'error' => '',
      );
      
      if (empty($item_key)) {
        if (!empty($product->quantity_unit['separate'])) {
          $item_key = uniqid();
        } else {
          $item_key = md5(serialize(array($product_id, $options)));
        }
      }
      
      $options = array_filter($options);
      $selected_options = array();
      
      if (count($product->options) > 0) {
        foreach (array_keys($product->options) as $key) {
          
          if ($product->options[$key]['required'] != 0) {
            if (empty($options[$product->options[$key]['name'][language::$selected['code']]])) {
              if (!$silent) notices::add('errors', language::translate('error_set_product_options', 'Please set your product options') . ' ('. $product->options[$key]['name'][language::$selected['code']] .')');
              return;
            }
          }
          
          if (!empty($options[$product->options[$key]['name'][language::$selected['code']]])) {
            switch ($product->options[$key]['function']) {
              
              case 'checkbox':
                $valid_values = array();
                foreach ($product->options[$key]['values'] as $value) {
                  $valid_values[] = $value['name'][language::$selected['code']];
                  if (in_array($value['name'][language::$selected['code']], explode(', ', $options[$product->options[$key]['name'][language::$selected['code']]]))) {
                    $selected_options[] = $product->options[$key]['id'].'-'.$value['id'];
                    $item['extras'] += $value['price_adjust'];
                  }
                }
                
                foreach (explode(', ', $options[$product->options[$key]['name'][language::$selected['code']]]) as $current_value) {
                  if (!in_array($current_value, $valid_values)) {
                    if (!$silent) notices::add('errors', language::translate('error_product_options_contains_errors', 'The product options contains errors'));
                    return;
                  }
                }
                break;
              
              case 'input':
              case 'textarea':
                $values = array_values($product->options[$key]['values']);
                $value = array_shift($values);
                $selected_options[] = $product->options[$key]['id'].'-'.$value['id'];
                $item['extras'] += $value['price_adjust'];
                break;
              
              case 'radio':
              case 'select':
              
                $valid_values = array();
                foreach ($product->options[$key]['values'] as $value) {
                  $valid_values[] = $value['name'][language::$selected['code']];
                  if ($value['name'][language::$selected['code']] == $options[$product->options[$key]['name'][language::$selected['code']]]) {
                    $selected_options[] = $product->options[$key]['id'].'-'.$value['id'];
                    $item['extras'] += $value['price_adjust'];
                  }
                }
                
                if (!in_array($options[$product->options[$key]['name'][language::$selected['code']]], $valid_values)) {
                  if (!$silent) notices::add('errors', language::translate('error_product_options_contains_errors', 'The product options contains errors'));
                  return;
                }
                
                break;
            }
          }
        }
      }
      
      $item['price'] += $item['extras'];
      $item['tax'] += tax::get_tax($item['extras'], $product->tax_class_id);
      
      if (!empty($item['options'])) {
        foreach (array_keys($item['options']) as $key) {
          if (is_array($item['options'][$key])) $item['options'][$key] = implode(', ', $item['options'][$key]);
        }
      }
      
    // Match options with options stock
      if (count($product->options_stock) > 0) {
        foreach ($product->options_stock as $option_stock) {
          
          $option_match = true;
          foreach (explode(',', $option_stock['combination']) as $pair) {
            if (!in_array($pair, $selected_options)) {
              $option_match = false;
            }
          }
          
          if ($option_match) {
            if (($option_stock['quantity'] - $quantity) < 0 && empty($product->sold_out_status['orderable'])) {
              if (!$silent) {
                notices::add('errors', language::translate('text_not_enough_products_in_stock_for_options', 'There are not enough products for the selected options.') . ' ('. round($option_stock['quantity'], $product->quantity_unit['decimals']) .')');
                return;
              } else {
                $quantity = $option_stock['quantity'];
              }
            }
            
            $item['option_stock_combination'] = $option_stock['combination'];
            if (!empty($option_stock['sku'])) $item['sku'] = $option_stock['sku'];
            if (!empty($option_stock['weight'])) $item['weight'] = $option_stock['weight'];
            if (!empty($option_stock['weight_class'])) $item['weight_class'] = $option_stock['weight_class'];
            if (!empty($option_stock['dim_x'])) $item['dim_x'] = $option_stock['dim_x'];
            if (!empty($option_stock['dim_y'])) $item['dim_y'] = $option_stock['dim_y'];
            if (!empty($option_stock['dim_z'])) $item['dim_z'] = $option_stock['dim_z'];
            if (!empty($option_stock['dim_class'])) $item['dim_class'] = $option_stock['dim_class'];
            break;
          }
        }
      }
      
      if (settings::get('round_amounts')) {
        $item['price'] = currency::round($item['price'], currency::$selected['code']);
        $item['tax'] = currency::round($item['tax'], currency::$selected['code']);
      }
      
      if (isset(self::$items[$item_key])) {
        
        self::update($item_key, self::$items[$item_key]['quantity'] + $quantity, $silent);
        
      } else {
        
        self::$items[$item_key] = $item;
        
        if (!$silent) {
          database::query(
            "insert into ". DB_TABLE_CART_ITEMS ."
            (customer_id, cart_uid, `key`, product_id, options, quantity, date_updated, date_created)
            values (". (int)customer::$data['id'] .", '". database::input(self::$data['uid']) ."', '". database::input($item_key) ."', ". (int)$item['product_id'] .", '". database::input(serialize($item['options'])) ."', ". (float)$item['quantity'] .", '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
          );
        }
        
        self::_calculate_total();
      }
      
      if (!$silent) {
        notices::add('success', language::translate('success_product_added_to_cart', 'Your product was successfully added to the cart.'));
        return;
      }
    }
    
    public static function update($item_key, $quantity, $silent=false) {
      
      if (!isset(self::$items[$item_key])) {
        notices::add('errors', 'The product does not exist in cart.');
        return;
      }
      
      if (!empty(self::$items[$item_key]['product_id'])) {
        $product = catalog::product(self::$items[$item_key]['product_id']);
        
      // Stock
        if (empty($product->sold_out_status['orderable'])) {
          if (!empty(self::$items[$item_key]['option_stock_combination'])) {
            foreach (array_keys($product->options_stock) as $key) {
              if ($product->options_stock[$key]['combination'] == self::$items[$item_key]['option_stock_combination']) {
                if (($product->options_stock[$key]['quantity'] - $quantity) < 0) {
                  if (!$silent) notices::add('errors', language::translate('text_not_enough_products_option_in_stock', 'There are not enough products of the selected option in stock.') . ' ('. round($product->options_stock[$key]['quantity'], $product->quantity_unit['decimals']) .')');
                  return;
                }
              }
            }
          } else if (($product->quantity - $quantity) < 0) {
            if (!$silent) notices::add('errors', language::translate('text_not_enough_products_in_stock', 'There are not enough products in stock.') . ' ('. round($product->quantity, $product->quantity_unit['decimals']) .')');
            return;
          }
        }
      }
      if ($quantity <= 0) {
        self::remove($item_key);
        return;
      }
      
      if (self::$items[$item_key]['quantity'] != $quantity) {
        self::$items[$item_key]['quantity'] = $quantity;
        
        if (!$silent) {
          database::query(
            "update ". DB_TABLE_CART_ITEMS ."
            set quantity = ". (float)self::$items[$item_key]['quantity'] .",
            date_updated = '". date('Y-m-d H:i:s') ."'
            where cart_uid = '". database::input(self::$data['uid']) ."'
            and `key` = '". database::input($item_key) ."'
            limit 1;"
          );
        }
      }
      
      self::_calculate_total();
    }
    
    public static function remove($item_key) {
      
      if (isset(self::$items[$item_key])) {
        unset(self::$items[$item_key]);
      }
      
      database::query(
        "delete from ". DB_TABLE_CART_ITEMS ."
        where `key` = '". database::input($item_key) ."'
        and cart_uid = '". database::input(self::$data['uid']) ."'
        limit 1;"
      );
      
      self::_calculate_total();
      
      header('Location: '. document::ilink());
      exit;
    }
    
    private static function _calculate_total() {
      
      $total_value = 0;
      $total_tax = 0;
      $total_items = 0;
      $total_weight = 0;
      
      foreach (self::$items as $item) {
        $num_items = $item['quantity'];
        
        if (!empty($item['quantity_unit']['decimals'])) {
          $num_items = 1;
        }
        
        $total_value += $item['price'] * $item['quantity'];
        $total_tax += tax::get_tax($item['price'], $item['tax_class_id']) * $item['quantity'];
        $total_items += $num_items;
      }
      
      self::$total = array(
        'value' => $total_value,
        'tax' => $total_tax,
        'items' => $total_items,
        'weight' => $total_weight,
      );
    }
  }
  
?>