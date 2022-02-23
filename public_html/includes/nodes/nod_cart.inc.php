<?php

  class cart {

    private static $_instance;

    public static $cart;
    public static $uid;
    public static $items;
    public static $total;

    public static function init() {

    // Recover a previous cart uid if possible
      if (!empty($_COOKIE['cart']['uid'])) {
        session::$data['cart_uid'] = $_COOKIE['cart']['uid'];
      } else if (empty(session::$data['cart_uid'])) {
        session::$data['cart_uid'] = uniqid();
      }

      self::$uid = &session::$data['cart_uid'];

    // Update cart cookie
      if (!empty($_COOKIE['cookies_accepted']) || !settings::get('cookie_policy')) {
        header('Set-Cookie: cart[uid]='. self::$uid .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+3 months')) .'; SameSite=Lax', false);
      }

      try {
        self::$_instance = new ent_shopping_cart(self::$uid);
      } catch (Exception $e) {
        self::$_instance = new ent_shopping_cart();
        self::$_instance->data['customer'] = array_replace(self::$_instance->data['customer'], array_intersect_key(customer::$data, self::$_instance->data['customer']));
      }

      self::$cart = &self::$_instance;
      self::$items = &self::$_instance->data['items'];
      self::$total = [
        'num_items' => &self::$_instance->data['num_items'],
        'subtotal' => &self::$_instance->data['subtotal'],
        'subtotal_tax' => &self::$_instance->data['subtotal_tax'],
      ];

      database::query(
        "delete from ". DB_TABLE_PREFIX ."shopping_carts_items
        where date_created < '". date('Y-m-d H:i:s', strtotime('-3 months')) ."';"
      );

      try {

      // Add product to cart
        if (!empty($_POST['add_cart_product'])) {

          $result = self::$_instance->add_product($_POST['product_id'], isset($_POST['stock_item_id']) ? $_POST['stock_item_id'] : '', isset($_POST['quantity']) ? $_POST['quantity'] : 1, true);

          if ($result) self::$_instance->save();

          if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            header('Location: '. $_SERVER['REQUEST_URI']);
            exit;
          }
        }

      // Remove cart item
        if (!empty($_POST['remove_cart_item'])) {

          self::$_instance->remove_item($_POST['remove_cart_item']);

          if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            header('Location: '. $_SERVER['REQUEST_URI']);
            exit;
          }
        }

      // Update cart item
        if (!empty($_POST['update_cart_item'])) {

          self::$_instance->update_item($_POST['update_cart_item'], isset($_POST['item'][$_POST['update_cart_item']]['quantity']) ? $_POST['item'][$_POST['update_cart_item']]['quantity'] : 1);

          if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            header('Location: '. $_SERVER['REQUEST_URI']);
            exit;
          }
        }

      // Clear cart items
        if (!empty($_POST['clear_cart_items'])) {

          self::clear();

          if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            header('Location: '. $_SERVER['REQUEST_URI']);
            exit;
          }
        }

      } catch (Exception $e) {
        notices::add('errors', $e->getMessage());
      }
    }

    public static function __callStatic($name, $arguments) {
      return call_user_func_array(self::$_instance, $name, $arguments);
    }

    function clear() {
      if (self::$_instance->data['id']) {
        self::delete();
      } else {
        self::reset();
      }
    }
  }
