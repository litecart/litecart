<?php

  class cart {

    public static $data = [];
    public static $items = [];
    public static $total = [];

    public static function init() {

      if (!isset(session::$data['cart']) || !is_array(session::$data['cart'])) {
        session::$data['cart'] = [
          'uid' => null,
        ];
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
        "delete from ". DB_TABLE_PREFIX ."cart_items
        where date_created < '". date('Y-m-d H:i:s', strtotime('-3 months')) ."';"
      );

    // Load/Refresh
      self::load();

      if (!empty($_POST['add_cart_product'])) {
        self::add_product($_POST['product_id'], isset($_POST['stock_item_id']) ? $_POST['stock_item_id'] : '', isset($_POST['quantity']) ? $_POST['quantity'] : 1);
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
          header('Location: '. $_SERVER['REQUEST_URI']);
          exit;
        }
      }

      if (!empty($_POST['remove_cart_item'])) {
        self::remove($_POST['remove_cart_item']);
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
          header('Location: '. $_SERVER['REQUEST_URI']);
          exit;
        }
      }

      if (!empty($_POST['update_cart_item'])) {
        self::update($_POST['update_cart_item'], isset($_POST['item'][$_POST['update_cart_item']]['quantity']) ? $_POST['item'][$_POST['update_cart_item']]['quantity'] : 1);
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
          header('Location: '. $_SERVER['REQUEST_URI']);
          exit;
        }
      }

      if (!empty($_POST['clear_cart_items'])) {
        self::clear();
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
          header('Location: '. $_SERVER['REQUEST_URI']);
          exit;
        }
      }
    }

    ######################################################################

    public static function reset() {

      self::$items = [];

      self::_calculate_total();
    }

    public static function clear() {

      self::reset();

      database::query(
        "delete from ". DB_TABLE_PREFIX ."cart_items
        where cart_uid = '". database::input(self::$data['uid']) ."';"
      );
    }

    public static function load() {

      self::reset();

      if (!empty(customer::$data['id'])) {
        database::query(
          "update ". DB_TABLE_PREFIX ."cart_items set
          cart_uid = '". database::input(self::$data['uid']) ."',
          customer_id = ". (int)customer::$data['id'] ."
          where cart_uid = '". database::input(self::$data['uid']) ."'
          or customer_id = ". (int)customer::$data['id'] .";"
        );
      }

      $cart_items_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."cart_items
        where cart_uid = '". database::input(self::$data['uid']) ."';"
      );

      while ($item = database::fetch($cart_items_query)) {

      // Remove duplicate cart item if present
        if (!empty(self::$items[$item['key']])) {
          database::query(
            "delete from ". DB_TABLE_PREFIX ."cart_items
            where cart_uid = '". database::input(self::$data['uid']) ."'
            and id = ". (int)$item['id'] ."
            limit 1;"
          );
        }

        self::add_product($item['product_id'], $item['stock_item_id'], $item['quantity'], true, $item['key']);
        if (isset(self::$items[$item['key']])) self::$items[$item['key']]['id'] = $item['id'];
      }
    }

    public static function add_product($product_id, $stock_item_id='', $quantity=1, $force=false, $item_key=null) {

      $product = reference::product($product_id);
      $stock_item = reference::stock_item($stock_item_id);
      $quantity = round((float)$quantity, $product->quantity_unit ? (int)$product->quantity_unit['decimals'] : 0, PHP_ROUND_HALF_UP);

    // Set item key
      if (empty($item_key)) {
        if (!empty($product->quantity_unit['separate'])) {
          $item_key = uniqid();
        } else {
          $item_key = md5(json_encode([$product->id]));
        }
      }

      $item = [
        'id' => null,
        'product_id' => (int)$product_id,
        'stock_item_id' => (int)$stock_item_id,
        'image' => $product->image,
        'name' => $product->name,
        'description' => '',
        'data' => '',
        'code' => $product->code,
        'sku' => $product->sku,
        'mpn' => $product->mpn,
        'gtin' => $product->gtin,
        'taric' => $product->taric,
        'price' => (!empty($product->campaign) && $product->campaign['price'] > 0) ? $product->campaign['price'] : $product->price,
        'extras' => 0,
        'tax' => tax::get_tax((!empty($product->campaign) && $product->campaign['price'] > 0) ? $product->campaign['price'] : $product->price, $product->tax_class_id),
        'tax_class_id' => $product->tax_class_id,
        'quantity' => $quantity,
        'quantity_unit' => [
          'name' => !empty($product->quantity_unit['name']) ? $product->quantity_unit['name'] : '',
          'decimals' => !empty($product->quantity_unit['decimals']) ? $product->quantity_unit['decimals'] : '',
          'separate' => !empty($product->quantity_unit['separate']) ? $product->quantity_unit['separate'] : '',
        ],
        'weight' => $product->weight,
        'weight_unit' => $product->weight_unit,
        'length' => $product->length,
        'width' => $product->width,
        'height' => $product->height,
        'length_unit' => $product->length_unit,
        'error' => '',
      ];

      try {
        if (!$product->id) {
          throw new Exception(language::translate('error_item_not_a_valid_product', 'The item is not a valid product'));
        }

        if (!$product->status) {
          throw new Exception(language::translate('error_product_currently_not_available_for_purchase', 'The product is currently not available for purchase'));
        }

        if (!empty($product->date_valid_from) && $product->date_valid_from > date('Y-m-d H:i:s')) {
          throw new Exception(strtr(language::translate('error_product_cannot_be_purchased_until_date', 'The product cannot be purchased until %date'), ['%date' => language::strftime(language::$selected['format_date'], strtotime($product->date_valid_from))]));
        }

        if (!empty($product->date_valid_to) && $product->date_valid_to > 1970 && $product->date_valid_to < date('Y-m-d H:i:s')) {
          throw new Exception(strtr(language::translate('error_product_can_no_longer_be_purchased', 'The product can no longer be purchased as of %date'), ['%date' => language::strftime(language::$selected['format_date'], strtotime($product->date_valid_to))]));
        }

        if ($quantity <= 0) {
          throw new Exception(language::translate('error_invalid_item_quantity', 'Invalid item quantity'));
        }

        if (($product->quantity - $quantity - (isset(self::$items[$item_key]) ? self::$items[$item_key]['quantity'] : 0)) < 0 && empty($product->sold_out_status['orderable'])) {
          throw new Exception(strtr(language::translate('error_only_n_remaining_products_in_stock', 'There are only %quantity remaining products in stock.'), ['%quantity' => round((float)$product->quantity, isset($product->quantity_unit['decimals']) ? (int)$product->quantity_unit['decimals'] : 0)]));
        }

      // Stock Option
        if ($product->stock_options) {

          if (empty($stock_item_id) || !in_array($stock_item_id, array_column($product->stock_options, 'stock_item_id'))) {
            throw new Exception(language::translate('error_missing_or_invalid_stock_option', 'Missing or invalid stock option'));
          }

          if (!empty($stock_item->sold_out_status) && empty($stock_item->sold_out_status['orderable'])) {
            if (($stock_item->quantity - $quantity - (isset(self::$items[$item_key]) ? self::$items[$item_key]['quantity'] : 0)) < 0) {
              throw new Exception(language::translate('error_not_enough_products_in_stock_for_option', 'Not enough products in stock for the selected option'));
            }
          }

          if (!empty($stock_item->sku)) $item['sku'] = $stock_item->sku;
          if (!empty($stock_item->weight)) $item['weight'] = (float)$stock_item->weight;
          if (!empty($stock_item->weight_unit)) $item['weight_unit'] = $stock_item->weight_unit;
          if (!empty($stock_item->length)) $item['length'] = (float)$stock_item->length;
          if (!empty($stock_item->width)) $item['width'] = (float)$stock_item->width;
          if (!empty($stock_item->height)) $item['height'] = (float)$stock_item->height;
          if (!empty($stock_item->length_unit)) $item['length_unit'] = $stock_item->length_unit;
        }

      } catch (Exception $e) {
        $item['error'] = $e->getMessage();
        if (!$force) {
          notices::add('errors', $e->getMessage());
          return false;
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
          "update ". DB_TABLE_PREFIX ."cart_items
          set quantity = ". (float)self::$items[$item_key]['quantity'] .",
          date_updated = '". date('Y-m-d H:i:s') ."'
          where cart_uid = '". database::input(self::$data['uid']) ."'
          and `key` = '". database::input($item_key) ."'
          limit 1;"
        );
      } else {
        if (!$force) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."cart_items
            (customer_id, cart_uid, `key`, product_id, stock_item_id, quantity, date_updated, date_created)
            values (". (int)customer::$data['id'] .", '". database::input(self::$data['uid']) ."', '". database::input($item_key) ."', ". (int)$item['product_id'] .", ". (int)$item['stock_item_id'] .", ". (float)$item['quantity'] .", '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
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
      self::add_product($item['product_id'], $quantity, true, $item_key);

      self::_calculate_total();

      if (!$force) {
        header('Location: '. document::link());
        exit;
      }
    }

    public static function remove($item_key, $force=false) {

      if (!isset(self::$items[$item_key])) return;

      database::query(
        "delete from ". DB_TABLE_PREFIX ."cart_items
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

      self::$total = [
        'items' => 0,
        'value' => 0,
        'tax' => 0,
      ];

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
