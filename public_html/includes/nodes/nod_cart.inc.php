<?php

  class cart {

    public static $uid;
    public static $cart;
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

      self::$cart = new ent_shopping_cart(self::$uid, customer::$data['id'], true);
      self::$items = &self::$cart->data['items'];
      self::$total = &self::$cart->data['total'];

      database::query(
        "delete from ". DB_TABLE_PREFIX ."shopping_carts_items
        where date_created < '". date('Y-m-d H:i:s', strtotime('-3 months')) ."';"
      );

    // Add product to cart
      if (!empty($_POST['add_cart_product'])) {
        try {

          $result = self::$cart->add_product($_POST['product_id'], isset($_POST['stock_item_id']) ? $_POST['stock_item_id'] : '', isset($_POST['quantity']) ? $_POST['quantity'] : 1, true);

          if ($result) self::$cart->save();

          if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            header('Location: '. $_SERVER['REQUEST_URI']);
            exit;
          }

        } catch (Exception $e) {
          notices::add('errors', $e->getMessage());
        }
      }

    // Remove cart item
      if (!empty($_POST['remove_cart_item'])) {
        try {

          self::$cart->remove_item($_POST['remove_cart_item']);

          if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            header('Location: '. $_SERVER['REQUEST_URI']);
            exit;
          }

        } catch (Exception $e) {
          notices::add('errors', $e->getMessage());
        }
      }

    // Update cart item
      if (!empty($_POST['update_cart_item'])) {
        try {

          self::$cart->update_item($_POST['update_cart_item'], isset($_POST['item'][$_POST['update_cart_item']]['quantity']) ? $_POST['item'][$_POST['update_cart_item']]['quantity'] : 1);

          if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            header('Location: '. $_SERVER['REQUEST_URI']);
            exit;
          }

        } catch (Exception $e) {
          notices::add('errors', $e->getMessage());
        }
      }

    // Clear cart items
      if (!empty($_POST['clear_cart_items'])) {
        try {

          self::clear();

          if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            header('Location: '. $_SERVER['REQUEST_URI']);
            exit;
          }

        } catch (Exception $e) {
          notices::add('errors', $e->getMessage());
        }
      }
    }

    function clear() {
      if (self::$cart->data['id']) {
        self::$cart->delete();
      } else {
        self::$cart->reset();
      }
    }
  }
