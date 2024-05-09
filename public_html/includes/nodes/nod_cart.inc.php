<?php

  class cart {

    public static $items = [];
    public static $total = [];

    public static function init() {

    // Set cart UID
      if (!empty($_COOKIE['cart']['uid'])) {
        session::$data['cart_uid'] = $_COOKIE['cart']['uid'];
      } else if (empty(session::$data['cart_uid'])) {
        session::$data['cart_uid'] = uniqid();
      }

      // Update cart cookie
      if (!empty($_COOKIE['cookies_accepted']) || !settings::get('cookie_policy')) {
        header('Set-Cookie: cart[uid]='. session::$data['cart_uid'] .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+3 months')) .'; SameSite=Lax', false);
      }

    // Clean up old shopping cart items
      database::query(
        "delete from ". DB_TABLE_PREFIX ."shopping_carts_items
        where date_created < '". date('Y-m-d H:i:s', strtotime('-3 months')) ."';"
      );

    // Load items
      self::load();

    // Event handler for adding product to cart
      if (!empty($_POST['add_cart_product'])) {
        self::add_product($_POST['product_id'], isset($_POST['stock_option_id']) ? $_POST['stock_option_id'] : 0, isset($_POST['quantity']) ? $_POST['quantity'] : 1);
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }

    // Event handler for removing product from cart
      if (!empty($_POST['remove_cart_item'])) {
        self::remove($_POST['remove_cart_item']);
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }

    // Event handler for updating product in cart
      if (!empty($_POST['update_cart_item'])) {
        self::update($_POST['update_cart_item'], isset($_POST['item'][$_POST['update_cart_item']]['quantity']) ? $_POST['item'][$_POST['update_cart_item']]['quantity'] : 1);
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }

    // Event handler for clearing all cart items
      if (!empty($_POST['clear_cart_items'])) {
        self::clear();
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
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
        "delete from ". DB_TABLE_PREFIX ."shopping_carts_items
        where cart_uid = '". database::input(session::$data['cart_uid']) ."';"
      );
    }

    public static function load() {

      self::reset();

      if (!empty(customer::$data['id'])) {
        database::query(
          "update ". DB_TABLE_PREFIX ."shopping_carts
          set uid = '". database::input(session::$data['cart_uid']) ."',
            customer_id = ". (int)customer::$data['id'] ."
          where customer_id = ". (int)customer::$data['id'] ."
          or uid = '". database::input(session::$data['cart_uid']) ."';"
        );
      }

      database::query(
        "select * from ". DB_TABLE_PREFIX ."shopping_carts_items
        where cart_uid = '". database::input(session::$data['cart_uid']) ."';"
      )->each(function($item){

      // Remove duplicate cart item if present
        if (!empty(self::$items[$item['key']])) {
          database::query(
            "delete from ". DB_TABLE_PREFIX ."shopping_carts_items
            where cart_uid = '". database::input(session::$data['cart_uid']) ."'
            and id = ". (int)$item['id'] ."
            limit 1;"
          );
        }

        self::add_product($item['product_id'], unserialize($item['options']), $item['quantity'], true, $item['key']);

        if (isset(self::$items[$item['key']])){
          self::$items[$item['key']]['id'] = $item['id'];
        }
      });
    }

    public static function add_product($product_id, $stock_option_id=0, $quantity=1, $force=false, $item_key=null) {

      $product = reference::product($product_id);
      $quantity = round((float)$quantity, $product->quantity_unit ? (int)$product->quantity_unit['decimals'] : 0, PHP_ROUND_HALF_UP);

    // Set item key
      if (!$item_key) {
        if (!empty($product->quantity_unit['separate'])) {
          $item_key = uniqid();
        } else {
          $item_key = md5(json_encode([$product->id, $options]));
        }
      }

      $item = [
        'id' => null,
        'product_id' => (int)$product->id,
        'stock_option_id' => $stock_option_id,
        'image' => $product->image,
        'name' => $product->name,
        'code' => $product->code,
        'sku' => $product->sku,
        'mpn' => $product->mpn,
        'gtin' => $product->gtin,
        'taric' => $product->taric,
        'price' => $product->final_price,
        //'extras' => 0,
        'tax' => $product->tax,
        'tax_class_id' => $product->tax_class_id,
        'quantity' => $quantity,
        'quantity_min' => $product->quantity_min ? $product->quantity_min : '0',
        'quantity_max' => ($product->quantity_max > 0) ? $product->quantity_max : null,
        'quantity_step' => ($product->quantity_step > 0) ? $product->quantity_step : null,
        'quantity_unit' => [
          'name' => !empty($product->quantity_unit['name']) ? $product->quantity_unit['name'] : '',
          'decimals' => !empty($product->quantity_unit['decimals']) ? $product->quantity_unit['decimals'] : '',
          'separate' => !empty($product->quantity_unit['separate']) ? $product->quantity_unit['separate'] : '',
        ],
        'weight' => $product->weight,
        'weight_unit' => $product->weight_unit,
        'length' => $product->dim_x,
        'width' => $product->dim_y,
        'height' => $product->dim_z,
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

        if ($product->quantity_min > 0 && $quantity < $product->quantity_min) {
          throw new Exception(strtr(language::translate('error_must_purchase_min_items', 'You must purchase a minimum of %num for this item'), ['%num' => (float)$product->quantity_min]));
        }

        if ($product->quantity_max > 0 && $quantity > $product->quantity_max) {
          throw new Exception(strtr(language::translate('error_cannot_purchase_more_than_max_items', 'You cannot purchase more than %num of this item'), ['%num' => (float)$product->quantity_max]));
        }

        if ($product->quantity_step > 0 && ($quantity % $product->quantity_step) != 0) {
          throw new Exception(strtr(language::translate('error_can_only_purchase_sets_for_item', 'You can only purchase sets by %num for this item'), ['%num' => (float)$product->quantity_step]));
        }

        if (empty($product->sold_out_status['orderable']) && ($product->quantity_available - $quantity - (isset(self::$items[$item_key]) ? self::$items[$item_key]['quantity'] : 0)) < 0) {
          throw new Exception(strtr(language::translate('error_only_n_remaining_products_available_for_purchase', 'There are only %quantity remaining products available for purchase'), ['%quantity' => round((float)$product->quantity_available, isset($product->quantity_unit['decimals']) ? (int)$product->quantity_unit['decimals'] : 0)]));
        }

      } catch(Exception $e) {
        $item['error'] = $e->getMessage();
        if (!$force) {
          notices::add('errors', $e->getMessage());
          return false;
        }
      }

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
          "update ". DB_TABLE_PREFIX ."shopping_carts_items
          set quantity = ". (float)self::$items[$item_key]['quantity'] .",
          date_updated = '". date('Y-m-d H:i:s') ."'
          where cart_uid = '". database::input(session::$data['cart_uid']) ."'
          and `key` = '". database::input($item_key) ."'
          limit 1;"
        );
      } else {
        if (!$force) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."shopping_carts_items
            (customer_id, cart_uid, `key`, product_id, options, quantity, date_updated, date_created)
            values (". (int)customer::$data['id'] .", '". database::input(session::$data['cart_uid']) ."', '". database::input($item_key) ."', ". (int)$item['product_id'] .", '". database::input(serialize($item['options'])) ."', ". (float)$item['quantity'] .", '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
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
        "delete from ". DB_TABLE_PREFIX ."shopping_carts_items
        where cart_uid = '". database::input(session::$data['cart_uid']) ."'
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
