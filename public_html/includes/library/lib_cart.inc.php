<?php

  class cart {

    public static $data = array();
    public static $items = array();
    public static $total = array();

    public static function init() {

      if (!isset(session::$data['cart']) || !is_array(session::$data['cart'])) {
        session::$data['cart'] = array(
          'uid' => null,
        );
      }

      self::$data = &session::$data['cart'];

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
        if (!empty($_COOKIE['cookies_accepted']) || !settings::get('cookie_policy')) {
          header('Set-Cookie: cart[uid]='. self::$data['uid'] .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+3 months')) .'; SameSite=Lax', false);
        }
      }

      database::query(
        "delete from ". DB_TABLE_CART_ITEMS ."
        where date_created < '". date('Y-m-d H:i:s', strtotime('-3 months')) ."';"
      );

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
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }

      if (!empty($_POST['remove_cart_item'])) {
        self::remove($_POST['remove_cart_item']);
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }

      if (!empty($_POST['update_cart_item'])) {
        self::update($_POST['update_cart_item'], isset($_POST['item'][$_POST['update_cart_item']]['quantity']) ? $_POST['item'][$_POST['update_cart_item']]['quantity'] : 1);
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }

      if (!empty($_POST['clear_cart_items'])) {
        self::clear();
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }
    }

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
          "update ". DB_TABLE_CART_ITEMS ." set
          cart_uid = '". database::input(self::$data['uid']) ."',
          customer_id = ". (int)customer::$data['id'] ."
          where cart_uid = '". database::input(self::$data['uid']) ."'
          or customer_id = ". (int)customer::$data['id'] .";"
        );
      }

      $cart_items_query = database::query(
        "select * from ". DB_TABLE_CART_ITEMS ."
        where cart_uid = '". database::input(self::$data['uid']) ."';"
      );

      while ($item = database::fetch($cart_items_query)) {

      // Remove duplicate cart item if present
        if (!empty(self::$items[$item['key']])) {
          database::query(
            "delete from ". DB_TABLE_CART_ITEMS ."
            where cart_uid = '". database::input(self::$data['uid']) ."'
            and id = ". (int)$item['id'] ."
            limit 1;"
          );
        }

        self::add_product($item['product_id'], unserialize($item['options']), $item['quantity'], true, $item['key']);
        if (isset(self::$items[$item['key']])) self::$items[$item['key']]['id'] = $item['id'];
      }
    }

    public static function add_product($product_id, $options, $quantity=1, $force=false, $item_key=null) {

      $product = reference::product($product_id);

    // Set item key
      if (empty($item_key)) {
        if (!empty($product->quantity_unit['separate'])) {
          $item_key = uniqid();
        } else {
          $item_key = md5(json_encode(array($product->id, $options)));
        }
      }

      $item = array(
        'id' => null,
        'product_id' => (int)$product->id,
        'options' => $options,
        'option_stock_combination' => '',
        'image' => $product->image,
        'name' => $product->name,
        'code' => $product->code,
        'sku' => $product->sku,
        'mpn' => $product->mpn,
        'gtin' => $product->gtin,
        'taric' => $product->taric,
        'price' => (!empty($product->campaign) && $product->campaign['price'] > 0) ? $product->campaign['price'] : $product->price,
        'extras' => 0,
        'tax' => tax::get_tax((!empty($product->campaign) && $product->campaign['price'] > 0) ? $product->campaign['price'] : $product->price, $product->tax_class_id),
        'tax_class_id' => $product->tax_class_id,
        'quantity' => !empty($product->quantity_unit['decimals']) ? round((float)$quantity, (int)$product->quantity_unit['decimals'], PHP_ROUND_HALF_UP) : $quantity,
        'quantity_unit' => array(
          'name' => !empty($product->quantity_unit['name']) ? $product->quantity_unit['name'] : '',
          'decimals' => !empty($product->quantity_unit['decimals']) ? $product->quantity_unit['decimals'] : '',
          'separate' => !empty($product->quantity_unit['separate']) ? $product->quantity_unit['separate'] : '',
        ),
        'weight' => $product->weight,
        'weight_class' => $product->weight_class,
        'dim_x' => $product->dim_x,
        'dim_y' => $product->dim_y,
        'dim_z' => $product->dim_z,
        'dim_class' => $product->dim_class,
        'error' => '',
      );

      try {
        if (!$product->id) {
          throw new Exception(language::translate('error_item_not_a_valid_product', 'The item is not a valid product'));
        }

        if ($product->status == 0) {
          throw new Exception(language::translate('error_product_currently_not_available_for_purchase', 'The product is currently not available for purchase'));
        }

        if ($product->date_valid_from > date('Y-m-d H:i:s')) {
          throw new Exception(strtr(language::translate('error_product_cannot_be_purchased_until_date', 'The product cannot be purchased until %date'), array('%date' => language::strftime(language::$selected['format_date'], strtotime($product->date_valid_from)))));
        }

        if ($product->date_valid_to > '1971' && $product->date_valid_to < date('Y-m-d H:i:s')) {
          throw new Exception(strtr(language::translate('error_product_can_no_longer_be_purchased', 'The product can no longer be purchased as of %date'), array('%date' => language::strftime(language::$selected['format_date'], strtotime($product->date_valid_to)))));
        }

        if ($quantity <= 0) {
          throw new Exception(language::translate('error_invalid_item_quantity', 'Invalid item quantity'));
        }

        //if (($product->quantity - $quantity) < 0 && empty($product->sold_out_status['orderable'])) {
        if (($product->quantity - $quantity - (isset(self::$items[$item_key]) ? self::$items[$item_key]['quantity'] : 0)) < 0 && empty($product->sold_out_status['orderable'])) {
          throw new Exception(strtr(language::translate('error_only_n_remaining_products_in_stock', 'There are only %quantity remaining products in stock.'), array('%quantity' => round((float)$product->quantity, isset($product->quantity_unit['decimals']) ? (int)$product->quantity_unit['decimals'] : 0))));
        }

      // Remove empty options
        $array_filter_recursive = function($array) use (&$array_filter_recursive) {

          foreach ($array as $i => $value) {
            if (is_array($value)) $array[$i] = $array_filter_recursive($value);
          }

          return array_filter($array, function($v) {
            if (is_array($v)) return !empty($v);
            return strlen(trim($v));
          });
        };

        $options = $array_filter_recursive($options);

      // Build options structure
        $sanitized_options = array();
        foreach ($product->options as $option) {

        // Check group
          $possible_groups = array_filter(array_unique(reference::attribute_group($option['group_id'])->name));
          $matched_groups = array_intersect(array_keys($options), array_values($possible_groups));
          $matched_group = array_shift($matched_groups);

          if (empty($matched_group)) {
            if (!empty($option['required'])) {
              throw new Exception(language::translate('error_set_product_options', 'Please set your product options'));
            } else {
              continue;
            }
          }

        // Check values
          switch ($option['function']) {

            case 'checkbox':

              $selected_values = preg_split('#\s*,\s*#', $options[$matched_group], -1, PREG_SPLIT_NO_EMPTY);

              $matched_values = array();
              foreach ($option['values'] as $value) {
                $possible_values = array_unique(
                  array_merge(
                    array($value['name']),
                    !empty(reference::attribute_group($option['group_id'])->values[$value['value_id']]) ? array_filter(array_values(reference::attribute_group($option['group_id'])->values[$value['value_id']]['name']), 'strlen') : array()
                  )
                );

                if ($matched_value = array_intersect($selected_values, $possible_values)) {
                  $matched_values[] = $matched_value;
                  $item['extras'] += $value['price_adjust'];
                }
              }
              break;

            case 'radio':
            case 'select':

              foreach ($option['values'] as $value) {
                $possible_values = array_unique(
                  array_merge(
                    array($value['name']),
                    !empty(reference::attribute_group($option['group_id'])->values[$value['value_id']]) ? array_filter(array_values(reference::attribute_group($option['group_id'])->values[$value['value_id']]['name']), 'strlen') : array()
                  )
                );

                if ($matched_value = array_intersect(array($options[$matched_group]), $possible_values)) {
                  $matched_value = $matched_value[0];
                  $item['extras'] += $value['price_adjust'];
                  break;
                }
              }
              break;
          }

          if (!empty($option['required']) && (!isset($matched_value) || $matched_value == '')) {
            throw new Exception(language::translate('error_product_options_contains_errors', 'The product options contains errors'));
          }

          if (empty($matched_group) || (empty($matched_values) && empty($matched_value))) continue;

          $sanitized_options[] = array(
            'group_id' => $option['id'],
            'value_id' => !empty($value['id']) ? $value['id'] : 0,
            'combination' => $option['group_id'] .'-'. (!empty($value['value_id']) ? $value['value_id'] : 0),
            'name' => $matched_group,
            'value' => !empty($matched_values) ? $matched_values : $matched_value,
          );
        }
      // Options stock
        foreach ($product->options_stock as $option_stock) {

          $option_match = true;
          foreach (explode(',', $option_stock['combination']) as $pair) {
            if (!in_array($pair, array_column($sanitized_options, 'combination'))) {
              $option_match = false;
              break;
            }
          }

          if ($option_match) {
            //if (($option_stock['quantity'] - $quantity) < 0 && empty($product->sold_out_status['orderable'])) {
            if (($option_stock['quantity'] - $quantity - (isset(self::$items[$item_key]) ? self::$items[$item_key]['quantity'] : 0)) < 0 && empty($product->sold_out_status['orderable'])) {
              throw new Exception(language::translate('error_not_enough_products_in_stock_for_option', 'Not enough products in stock for the selected option') . ' ('. round((float)$option_stock['quantity'], isset($product->quantity_unit['decimals']) ? (int)$product->quantity_unit['decimals'] : 0) .')');
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

      } catch(Exception $e) {
        $item['error'] = $e->getMessage();
        if (!$force) {
          notices::add('errors', $e->getMessage());
          return false;
        }
      }

    // Convert options array to string
      if (!empty($item['options'])) {
        foreach (array_keys($item['options']) as $key) {
          if (is_array($item['options'][$key])) $item['options'][$key] = implode(', ', $item['options'][$key]);
        }
      }

    // Adjust price with extras
      $item['price'] += $item['extras'];
      $item['tax'] += tax::get_tax($item['extras'], $product->tax_class_id);

    // Round amounts (Gets rid of hidden decimals)
      if (settings::get('round_amounts')) {
        $item['price'] = currency::round($item['price'], currency::$selected['code']);
        $item['tax'] = currency::round($item['tax'], currency::$selected['code']);
      }

    // Add item or append to existing
      if (isset(self::$items[$item_key])) {
        self::$items[$item_key]['quantity'] += $quantity;
      } else {
        self::$items[$item_key] = $item;
      }

      if (!empty(self::$items[$item_key]['id'])) {
        database::query(
          "update ". DB_TABLE_CART_ITEMS ."
          set quantity = ". (float)self::$items[$item_key]['quantity'] .",
          date_updated = '". date('Y-m-d H:i:s') ."'
          where cart_uid = '". database::input(self::$data['uid']) ."'
          and `key` = '". database::input($item_key) ."'
          limit 1;"
        );
      } else {
        if (!$force) {
          database::query(
            "insert into ". DB_TABLE_CART_ITEMS ."
            (customer_id, cart_uid, `key`, product_id, options, quantity, date_updated, date_created)
            values (". (int)customer::$data['id'] .", '". database::input(self::$data['uid']) ."', '". database::input($item_key) ."', ". (int)$item['product_id'] .", '". database::input(serialize($item['options'])) ."', ". (float)$item['quantity'] .", '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
          );
          self::$items[$item_key]['id'] = database::insert_id();
        }
      }

      self::_calculate_total();

      if (!$force) {
        notices::add('success', language::translate('success_product_added_to_cart', 'Your product was successfully added to the cart.'));
      }

      return true;
    }

    public static function update($item_key, $quantity, $force=false) {

      if (!isset(self::$items[$item_key])) {
        notices::add('errors', 'The item does not exist in cart.');
        return;
      }

      if (self::$items[$item_key]['quantity'] == $quantity) {
        return;
      }

      if ($quantity <= 0) {
        self::remove($item_key, true);
        return;
        }

    // Re-add quantity for validation
      $item = self::$items[$item_key];
      self::$items[$item_key]['quantity'] = 0;
      self::add_product($item['product_id'], $item['options'], $quantity, true, $item_key);

      self::_calculate_total();

      if (!$force) {
        header('Location: '. document::link());
        exit;
      }
    }

    public static function remove($item_key, $force=false) {

      if (!isset(self::$items[$item_key])) return;

      database::query(
        "delete from ". DB_TABLE_CART_ITEMS ."
        where cart_uid = '". database::input(self::$data['uid']) ."'
        and id = ". (int)self::$items[$item_key]['id'] ."
        limit 1;"
      );

      unset(self::$items[$item_key]);

      self::_calculate_total();

      if (!$force) {
        header('Location: '. document::link());
        exit;
      }
    }

    private static function _calculate_total() {

      self::$total = array(
        'items' => 0,
        'value' => 0,
        'tax' => 0,
      );

      foreach (self::$items as $item) {
        $num_items = $item['quantity'];

        if (!empty($item['quantity_unit']['separate'])) {
          $num_items = 1;
        }

        self::$total['value'] += $item['price'] * $item['quantity'];
        self::$total['tax'] += $item['tax'] * $item['quantity'];
        self::$total['items'] += $num_items;
      }
    }
  }
