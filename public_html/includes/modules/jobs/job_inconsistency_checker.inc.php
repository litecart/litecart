<?php

  class job_inconsistency_checker {
    public $id = __CLASS__;
    public $name = 'Inconsistency Checker';
    public $description = '';
    public $author = 'ACME Corporation';
    public $version = '1.0';
    public $support_link = '';
    public $website = 'http://www.tim-international.net';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = $system;
    }
    
    public function process() {
      
      if ($this->settings['status'] != 'Enabled') return;
      if (empty($this->settings['email_receipient'])) return;
      
      switch ($this->settings['check_frequency']) {
        case 'Daily':
          if (strtotime($this->system->settings->get('inconsistency_checker_last_run')) > strtotime('-1 day')) return; 
          break;
        case 'Weekly':
          if (strtotime($this->system->settings->get('inconsistency_checker_last_run')) > strtotime('-1 week')) return; 
          break;
        case 'Monthly':
          if (strtotime($this->system->settings->get('inconsistency_checker_last_run')) > strtotime('-1 month')) return; 
          break;
      }
      
      $log = $this->inconsistency_check();
      
      if (!empty($log)) {
        $from = $this->system->settings->get('store_email');
        $to = $this->settings['email_receipient'];
        $result = $this->system->functions->email_send($from, $to, 'Inconsistency check for '. $this->system->settings->get('store_name'), $log);
      }
    }
    
    private function inconsistency_check() {
      
      $errors = array();
      
    // List languages
      $language_codes = array();
      $query = $this->system->database->query(
        "select * from ". DB_TABLE_LANGUAGES ."
        order by code;"
      );
      while ($row = $this->system->database->fetch($query)) {
        $language_codes[] = $row['code'];
      }

    // List products  
      $product_ids = array();
      $query = $this->system->database->query(
        "select * from ". DB_TABLE_PRODUCTS ."
        order by id;"
      );
      while ($row = $this->system->database->fetch($query)) {
        $product_ids[] = $row['id'];
      }
      
    // Check for duplicate product info
      $query = $this->system->database->query(
        "select id, product_id, language_code from ". DB_TABLE_PRODUCTS_INFO ."
        order by product_id;"
      );
      while ($row = $this->system->database->fetch($query)) {
        $tmpquery = $this->system->database->query(
          "select id from ". DB_TABLE_PRODUCTS_INFO ."
          where id != '". $row['id'] ."'
          and product_id = '". $row['product_id'] ."'
          and language_code = '". $row['language_code'] ."'
          limit 1;"
        );
        if ($this->system->database->num_rows($tmpquery)) {
          $errors['duplicate_product_info_language_'.$row['language_code']][] = DB_TABLE_PRODUCTS_INFO.'->id->'. $row['id'] .': Duplicate content for product '. $row['product_id'] .' language_code '. $row['language_code'];
        }
      }
      
    // Check for orphan product info
      $query = $this->system->database->query(
        "select id, product_id from ". DB_TABLE_PRODUCTS_INFO ."
        where product_id not in (". implode(', ', $product_ids) .")
        group by product_id
        order by product_id;"
      );
      while ($row = $this->system->database->fetch($query)) {
        if (!in_array($row['product_id'], $product_ids)) {
          $errors['orphan_product_info'][] = DB_TABLE_PRODUCTS_INFO.'->id->'. $row['id'] .': Orphan product_id '. $row['product_id'];
          if ($this->settings['fix'] == 'true') {
            $this->system->database->query(
              "delete from ". DB_TABLE_PRODUCTS_INFO ."
              where product_id = '". $row['product_id'] ."';"
            );
          }
        }
      }
      
    // Check for orphan product prices
      $query = $this->system->database->query(
        "select id, product_id from ". DB_TABLE_PRODUCTS_PRICES ."
        where product_id not in (". implode(', ', $product_ids) .")
        group by product_id
        order by product_id;"
      );
      while ($row = $this->system->database->fetch($query)) {
        if (!in_array($row['product_id'], $product_ids)) {
          $errors['orphan_product_prices'][] = DB_TABLE_PRODUCTS_PRICES.'->id->'. $row['id'] .': Orphan product_id '. $row['product_id'];
          if ($this->settings['fix'] == 'true') {
            $this->system->database->query(
              "delete from ". DB_TABLE_PRODUCTS_PRICES ."
              where product_id = '". $row['product_id'] ."';"
            );
          }
        }
      }
      
    // Check for orphan product options
      $query = $this->system->database->query(
        "select id, product_id from ". DB_TABLE_PRODUCTS_OPTIONS ."
        where product_id not in (". implode(', ', $product_ids) .")
        group by product_id
        order by product_id;"
      );
      while ($row = $this->system->database->fetch($query)) {
        if (!in_array($row['product_id'], $product_ids)) {
          $errors['orphan_product_options'][] = DB_TABLE_PRODUCTS_OPTIONS.'->id->'. $row['id'] .': Orphan product_id '. $row['product_id'];
          if ($this->settings['fix'] == 'true') {
            $this->system->database->query(
              "delete from ". DB_TABLE_PRODUCTS_OPTIONS ."
              where product_id = '". $row['product_id'] ."';"
            );
          }
        }
      }
      
    // Check for orphan product options stock
      $query = $this->system->database->query(
        "select id, product_id from ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ."
        where product_id not in (". implode(', ', $product_ids) .")
        group by product_id
        order by product_id;"
      );
      while ($row = $this->system->database->fetch($query)) {
        if (!in_array($row['product_id'], $product_ids)) {
          $errors['orphan_product_options'][] = DB_TABLE_PRODUCTS_OPTIONS_STOCK.'->id->'. $row['id'] .': Orphan product_id '. $row['product_id'];
          if ($this->settings['fix'] == 'true') {
            $this->system->database->query(
              "delete from ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ."
              where product_id = '". $row['product_id'] ."';"
            );
          }
        }
      }
      
    // Check for orphan language in product info 
      $query = $this->system->database->query(
        "select id, product_id, language_code from ". DB_TABLE_PRODUCTS_INFO ."
        order by product_id;"
      );
      while ($row = $this->system->database->fetch($query)) {
        if (!in_array($row['language_code'], $language_codes)) {
          $errors['orphan_product_info_language_'.$row['language_code']][] = DB_TABLE_PRODUCTS_INFO.'->id->'. $row['id'] .': Orphan language_code '. $row['language_code'] .' for product '. $row['product_id'];
          if ($this->settings['fix'] == 'true') {
            $this->system->database->query(
              "delete from ". DB_TABLE_PRODUCTS_INFO ."
              where language_code = '". $row['language_code'] ."';"
            );
          }
        }
      }
      
    // Check for missing product names
      $query = $this->system->database->query(
        "select id, product_id, language_code from ". DB_TABLE_PRODUCTS_INFO ."
        where name = ''
        order by product_id;"
      );
      while ($row = $this->system->database->fetch($query)) {
        if (!in_array($row['product_id'], $product_ids)) $errors['missing_product_names_'.$row['language_code']][] = DB_TABLE_PRODUCTS_INFO.'->id->'. $row['id'] .': Missing '. $row['language_code'] .' name for product_id '. $row['product_id'];
      }
      
    // Check for orphan images
      $query = $this->system->database->query(
        "select id, product_id, filename from ". DB_TABLE_PRODUCTS_IMAGES ."
        where product_id not in (". implode(', ', $product_ids) .")
        order by product_id;"
      );
      while ($row = $this->system->database->fetch($query)) {
        if (!in_array($row['product_id'], $product_ids)) {
          $errors['orphan_images'][] = DB_TABLE_PRODUCTS_IMAGES.'->id->'. $row['id'] .': Orphan product image '. $row['filename'] .' for product_id '. $row['product_id'];
          if ($this->settings['fix'] == 'true') {
            $this->system->database->query(
              "delete from ". DB_TABLE_PRODUCTS_IMAGES ."
              where id = '". $row['id'] ."'
              limit 1;"
            );
          }
        }
      }
      
    // Check for low resolution and missing product images
      $query = $this->system->database->query(
        "select id, product_id, filename from ". DB_TABLE_PRODUCTS_IMAGES ."
        order by product_id, filename;"
      );
      while ($row = $this->system->database->fetch($query)) {
        if (empty($row['filename'])) {
          $errors['missing_product_images'][] = DB_TABLE_PRODUCTS_IMAGES.'->id->'. $row['id'] .': Missing image for product_id '. $row['product_id'];
          if ($this->settings['fix'] == 'true') {
            $this->system->database->query(
              "delete from ". DB_TABLE_PRODUCTS_IMAGES ."
              where id = '". $row['id'] ."'
              limit 1;"
            );
          }
        } elseif (!file_exists(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $row['filename'])) {
          $errors['missing_product_images'][] = DB_TABLE_PRODUCTS_IMAGES.'->id->'. $row['id'] .': Missing image '. $row['filename'] .' for product_id '. $row['product_id'];
        } else {
          $imginfo = getimagesize(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $row['filename']);
          if ($imginfo[0] < 300 || $imginfo[1] < 300) {
            $errors['low_res_product_images'][] = DB_TABLE_PRODUCTS_IMAGES.'->id->'. $row['id'] .': Low resolution image '. $row['filename'] .' for product_id '. $row['product_id'] .' ('. $imginfo[0] .'x'. $imginfo[1] .')';
          }
        }
      }
      
    // Check for missing category images
      $query = $this->system->database->query(
        "select id, image from ". DB_TABLE_CATEGORIES ."
        where image != ''
        order by id;"
      );
      while ($row = $this->system->database->fetch($query)) {
        if (!file_exists(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $row['image'])) $errors['missing_category_images'][] = DB_TABLE_CATEGORIES.'->id->'. $row['id'] .': Missing image '. $row['filename'] .' for product_id '. $row['product_id'];
      }
      
      $output = '';
      
      $i=0;
      foreach ($errors as $error_group) {
        foreach ($error_group as $error) {
          $i++;
          $output .= '[#'. str_pad($i, 5, '0', STR_PAD_LEFT) . '] ' . $error . PHP_EOL;
        }
      }
      
      return $output;
    }
    
    function settings() {
      
      return array(
        array(
          'key' => 'status',
          'default_value' => 'Enabled',
          'title' => $this->system->language->translate(__CLASS__.':title_status', 'Status'),
          'description' => $this->system->language->translate(__CLASS__.':description_status', 'Enables or disables the module.'),
          'function' => 'radio("Enabled", "Disabled")',
        ),
        array(
          'key' => 'fix',
          'default_value' => 'true',
          'title' => $this->system->language->translate(__CLASS__.':title_fix', 'Fix'),
          'description' => $this->system->language->translate(__CLASS__.':description_fix', 'Fixes inconsistency issues automatically.'),
          'function' => 'radio("true", "false")',
        ),
        array(
          'key' => 'check_frequency',
          'default_value' => 'Weekly',
          'title' => $this->system->language->translate(__CLASS__.':title_check_frequency', 'Check Frequency'),
          'description' => $this->system->language->translate(__CLASS__.':description_check_frequency', 'How often the inconsistency check should be performed.'),
          'function' => 'radio("Daily","Weekly","Monthly")',
        ),
        array(
          'key' => 'email_receipient',
          'default_value' => $this->system->settings->get('store_email'),
          'title' => $this->system->language->translate(__CLASS__.':title_email_receipient', 'E-mail Receipient'),
          'description' => $this->system->language->translate(__CLASS__.':description_email_receipient', 'Send inconsistency reports to the given e-mail address.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'priority',
          'default_value' => '0',
          'title' => $this->system->language->translate(__CLASS__.':title_priority', 'Priority'),
          'description' => $this->system->language->translate(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
          'function' => 'int()',
        ),
      );
    }
    
    public function install() {
      $this->system->database->query(
        "insert into ". DB_TABLE_SETTINGS ."
        (title, description, `key`, value, date_created, date_updated)
        values ('Inconsistency Last Reported', 'Time when inconsistency check was last performed by the background job.', 'inconsistency_checker_last_run', '', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
      );
    }
    
    public function uninstall() {
      $this->system->database->query(
        "delete from ". DB_TABLE_SETTINGS ."
        where `key` = 'inconsistency_checker_last_run'
        limit 1;"
      );
    }
  }
  
?>