<?php

  class catalog {
    private static $_categories;
    private static $_customers;
    private static $_products;
    private static $_manufacturers;
    private static $_pages;

    //public static function construct() {
    //}

    //public static function load_dependencies() {
    //}

    //public static function initiate() {
    //}

    //public static function startup() {
    //}

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

    public static function category($category_id) {

      if (!isset(self::$_categories[$category_id])) {
        self::$_categories[$category_id] = new ref_category($category_id);
      }

      return self::$_categories[$category_id];
    }

    public static function customer($customer_id) {

      if (!isset(self::$_customers[$customer_id])) {
        self::$_customers[$customer_id] = new ref_customer($customer_id);
      }

      return self::$_customers[$customer_id];
    }

    public static function manufacturer($manufacturer_id) {

      if (!isset(self::$_manufacturers[$manufacturer_id])) {
        self::$_manufacturers[$manufacturer_id] = new ref_manufacturer($manufacturer_id);
      }

      return self::$_manufacturers[$manufacturer_id];
    }

    public static function product($product_id) {

      if (!isset(self::$_products[$product_id])) {
        self::$_products[$product_id] = new ref_product($product_id);
      }

      return self::$_products[$product_id];
    }

    public static function page($page_id) {

      if (!isset(self::$_pages[$page_id])) {
        self::$_pages[$page_id] = new ref_page($page_id);
      }

      return self::$_pages[$page_id];
    }
  }

?>