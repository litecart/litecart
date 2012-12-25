<?php

  class ctrl_product {
    public $data;
    
    public function __construct($product_id='') {
      global $system;
      
      $this->system = &$system;
      
      if (!empty($product_id)) {
        $this->load($product_id);
      } else {
        $this->reset();
      }
    }
    
    public function reset() {
      
      $this->data = array();
      
      $products_query = $this->system->database->query(
        "show fields from ". DB_TABLE_PRODUCTS .";"
      );
      while ($field = $this->system->database->fetch($products_query)) {
        $this->data[$field['Field']] = '';
      }
      
      $this->data['categories'] = array();
      $this->data['product_groups'] = array();
      $this->data['images'] = array();
      $this->data['prices'] = array();
      $this->data['campaigns'] = array();
      $this->data['options'] = array();
      $this->data['options_stock'] = array();
      
      $products_info_query = $this->system->database->query(
        "show fields from ". DB_TABLE_PRODUCTS_INFO .";"
      );
      
      while ($field = $this->system->database->fetch($products_info_query)) {
        if (in_array($field['Field'], array('id', 'product_id', 'language_code'))) continue;
        $this->data[$field['Field']] = array();
        foreach (array_keys($this->system->language->languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = '';
        }
      }
    }
    
    public function load($product_id) {
      
      $this->reset();
      
    // Product
      $products_query = $this->system->database->query(
        "select * from ". DB_TABLE_PRODUCTS ."
        where id = '". (int)$product_id ."'
        limit 1;"
      );
      $product = $this->system->database->fetch($products_query);
      
      foreach ($product as $key => $value) {
        $this->data[$key] = $value;
      }
      
      $this->data['categories'] = explode(',', $this->data['categories']);
      $this->data['product_groups'] = explode(',', $this->data['product_groups']);
      
    // Info
      $products_info_query = $this->system->database->query(
        "select * from ". DB_TABLE_PRODUCTS_INFO ."
        where product_id = '". (int)$product_id ."';"
      );
      while ($product_info = $this->system->database->fetch($products_info_query)) {
        foreach ($product_info as $key => $value) {
          if (in_array($key, array('id', 'product_id', 'language_code'))) continue;
          $this->data[$key][$product_info['language_code']] = $value;
        }
      }
      
    // Prices
      $products_prices_query = $this->system->database->query(
        "select * from ". DB_TABLE_PRODUCTS_PRICES ."
        where product_id = '". (int)$this->data['id'] ."';"
      );
      while ($product_price = $this->system->database->fetch($products_prices_query)) {
        foreach (array_keys($this->system->currency->currencies) as $currency_code) {
          $this->data['prices'][$currency_code] = $product_price[$currency_code];
        }
      }
      
    // Campaigns
      $product_campaigns_query = $this->system->database->query(
        "select * from ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
        where product_id = '". (int)$this->data['id'] ."'
        order by start_date;"
      );
      while ($product_campaign = $this->system->database->fetch($product_campaigns_query)) {
        $this->data['campaigns'][$product_campaign['id']] = $product_campaign;
      }
      
    // Options
      $products_options_query = $this->system->database->query(
        "select * from ". DB_TABLE_PRODUCTS_OPTIONS ."
        where product_id = '". (int)$this->data['id'] ."'
        order by priority asc;"
      );
      while($option = $this->system->database->fetch($products_options_query)) {
        $this->data['options'][$option['id']] = $option;
      }
      
    // Options stock
      $products_options_stock_query = $this->system->database->query(
        "select * from ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ." po
        where po.product_id = '". (int)$this->data['id'] ."'
        order by priority;"
      );
      while($option_stock = $this->system->database->fetch($products_options_stock_query)) {
      
        $this->data['options_stock'][$option_stock['id']] = $option_stock;
        $this->data['options_stock'][$option_stock['id']]['name'] = array();
        
        foreach (explode(',', $option_stock['combination']) as $combination) {
          list($group_id, $value_id) = explode('-', $combination);
          
          $options_values_query = $this->system->database->query(
            "select ovi.value_id, ovi.name, ovi.language_code from ". DB_TABLE_OPTION_VALUES_INFO ." ovi
            where ovi.value_id = '". (int)$value_id ."';"
          );
          while($option_value = $this->system->database->fetch($options_values_query)) {
            if (!isset($this->data['options_stock'][$option_stock['id']]['name'][$option_value['language_code']])) {
              $this->data['options_stock'][$option_stock['id']]['name'][$option_value['language_code']] = '';
            } else {
              $this->data['options_stock'][$option_stock['id']]['name'][$option_value['language_code']] .= ', ';
            }
            $this->data['options_stock'][$option_stock['id']]['name'][$option_value['language_code']] .= $option_value['name'];
          }
        }
      }
      
    // Images
      $products_images_query = $this->system->database->query(
        "select * from ". DB_TABLE_PRODUCTS_IMAGES."
        where product_id = '". (int)$this->data['id'] ."'
        order by priority asc, id asc;"
      );
      while($image = $this->system->database->fetch($products_images_query)) {
        $this->data['images'][$image['id']] = $image;
      }
    }
    
    public function save() {
    
      if (empty($this->data['id'])) {
        $this->system->database->query(
          "insert into ". DB_TABLE_PRODUCTS ."
          (date_created)
          values ('". $this->system->database->input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = $this->system->database->insert_id();
      }
      
    // Calculate product quantity from options
      if (!empty($this->data['options_stock'])) {
        $this->data['quantity'] = 0;
        foreach ($this->data['options_stock'] as $option) {
          $this->data['quantity'] += @$option['quantity'];
        }
      }
      
    // Extract first image
      if (!empty($this->data['images'])){
        $image = array_shift(array_values($this->data['images']));
        $this->data['image'] = $image['filename'];
      } else {
        $this->data['image'];
      }
      
    // Cleanup empty elements in categories
      //$this->data['categories'] = array_filter($this->data['categories']);
      foreach(array_keys($this->data['categories']) as $key){
        if ($this->data['categories'][$key] == '') unset($this->data['categories'][$key]);
      }
      $this->data['categories'] = array_unique($this->data['categories']);
      
      $this->system->database->query(
        "update ". DB_TABLE_PRODUCTS ." set
        status = '". (int)$this->data['status'] ."',
        manufacturer_id = '". (int)$this->data['manufacturer_id'] ."',
        designer_id = '". (int)$this->data['designer_id'] ."',
        supplier_id = '". (int)$this->data['supplier_id'] ."',
        delivery_status_id = '". (int)$this->data['delivery_status_id'] ."',
        sold_out_status_id = '". (int)$this->data['sold_out_status_id'] ."',
        categories = '". $this->system->database->input(implode(',', $this->data['categories'])) ."',
        product_groups = '". $this->system->database->input(implode(',', $this->data['product_groups'])) ."',
        keywords = '". $this->system->database->input(rtrim(trim($this->data['keywords']), ',')) ."',
        ". ((isset($image_filename)) ? "image='". $image_filename ."'," : false) ."
        quantity = '". $this->system->database->input($this->data['quantity']) ."',
        purchase_price = '". $this->system->database->input($this->data['purchase_price']) ."',
        tax_class_id = '". $this->system->database->input($this->data['tax_class_id']) ."',
        code = '". $this->system->database->input($this->data['code']) ."',
        sku = '". $this->system->database->input($this->data['sku']) ."',
        upc = '". $this->system->database->input($this->data['upc']) ."',
        taric = '". $this->system->database->input($this->data['taric']) ."',
        dim_x = '". $this->system->database->input($this->data['dim_x']) ."',
        dim_y = '". $this->system->database->input($this->data['dim_y']) ."',
        dim_z = '". $this->system->database->input($this->data['dim_z']) ."',
        dim_class = '". $this->system->database->input($this->data['dim_class']) ."',
        weight = '". $this->system->database->input($this->data['weight']) ."',
        weight_class = '". $this->system->database->input($this->data['weight_class']) ."',
        image = '". $this->system->database->input($this->data['image']) ."',
        date_valid_from = ". (empty($this->data['date_valid_from']) ? "NULL" : "'". date('Y-m-d H:i:s', strtotime($this->data['date_valid_from'])) ."'") .",
        date_valid_to = ". (empty($this->data['date_valid_to']) ? "NULL" : "'". date('Y-m-d H:i:s', strtotime($this->data['date_valid_to'])) ."'") .",
        ". ((isset($this->data['date_created'])) ? "date_created = '". $this->system->database->input($this->data['date_created']) ."'," : "") ."
        date_updated = '". date('Y-m-d H:i:s') ."'
        where id='". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      foreach (array_keys($this->system->language->languages) as $language_code) {
        
        $products_info_query = $this->system->database->query(
          "select * from ". DB_TABLE_PRODUCTS_INFO ."
          where product_id = '". (int)$this->data['id'] ."'
          and language_code = '". $this->system->database->input($language_code) ."'
          limit 1;"
        );
        $product_info = $this->system->database->fetch($products_info_query);
        
        if (empty($product_info)) {
          $this->system->database->query(
            "insert into ". DB_TABLE_PRODUCTS_INFO ."
            (product_id, language_code)
            values ('". (int)$this->data['id'] ."', '". $language_code ."');"
          );
        }
        
        $this->system->database->query(
          "update ". DB_TABLE_PRODUCTS_INFO ." set
          name = '". $this->system->database->input($this->data['name'][$language_code]) ."',
          short_description = '". $this->system->database->input($this->data['short_description'][$language_code]) ."',
          description = '". $this->system->database->input($this->data['description'][$language_code], true) ."',
          head_title = '". $this->system->database->input($this->data['head_title'][$language_code]) ."',
          meta_description = '". $this->system->database->input($this->data['meta_description'][$language_code]) ."',
          meta_keywords = '". $this->system->database->input($this->data['meta_keywords'][$language_code]) ."',
          attributes = '". $this->system->database->input($this->data['attributes'][$language_code]) ."'
          where product_id = '". (int)$this->data['id'] ."'
          and language_code = '". $this->system->database->input($language_code) ."'
          limit 1;"
        );
      }
      
      foreach (array_keys($this->system->currency->currencies) as $currency_code) {
        
        $products_prices_query = $this->system->database->query(
          "select * from ". DB_TABLE_PRODUCTS_PRICES ."
          where product_id = '". (int)$this->data['id'] ."'
          limit 1;"
        );
        $product_price = $this->system->database->fetch($products_prices_query);
        
        if (empty($product_price)) {
          $this->system->database->query(
            "insert into ". DB_TABLE_PRODUCTS_PRICES ."
            (product_id)
            values ('". (int)$this->data['id'] ."');"
          );
        }
        
        $sql_currency_prices = "";
        foreach (array_keys($this->system->currency->currencies) as $currency_code) {
          $sql_currency_prices .= $currency_code ." = '". (!empty($this->data['prices'][$currency_code]) ? (float)$this->data['prices'][$currency_code] : 0) ."', ";
        }
        $sql_currency_prices = rtrim($sql_currency_prices, ', ');
        
        $this->system->database->query(
          "update ". DB_TABLE_PRODUCTS_PRICES ." set
          $sql_currency_prices
          where product_id = '". (int)$this->data['id'] ."'
          limit 1;"
        );
      }
      
    // Delete campaigns
      $this->system->database->query(
        "delete from ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
        where product_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", @array_keys($this->data['campaigns'])) ."');"
      );
      
    // Update campaigns
      if (!empty($this->data['campaigns'])) {
        foreach (array_keys($this->data['campaigns']) as $key) {
          if (empty($this->data['campaigns'][$key]['id'])) {
            $this->system->database->query(
              "insert into ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
              (product_id)
              values ('". (int)$this->data['id'] ."');"
            );
            $this->data['campaigns'][$key]['id'] = $this->system->database->insert_id();
          }
          
          $sql_currency_campaigns = "";
          foreach (array_keys($this->system->currency->currencies) as $currency_code) {
            $sql_currency_campaigns .= $currency_code ." = '". (float)$this->data['campaigns'][$key][$currency_code] ."', ";
          }
          $sql_currency_campaigns = rtrim($sql_currency_campaigns, ', ');
          
          $this->system->database->query(
            "update ". DB_TABLE_PRODUCTS_CAMPAIGNS ." set
            start_date = ". (empty($this->data['campaigns'][$key]['start_date']) ? "NULL" : "'". date('Y-m-d H:i:s', strtotime($this->data['campaigns'][$key]['start_date'])) ."'") .",
            end_date = ". (empty($this->data['campaigns'][$key]['end_date']) ? "NULL" : "'". date('Y-m-d H:i:s', strtotime($this->data['campaigns'][$key]['end_date'])) ."'") .",
            $sql_currency_campaigns
            where product_id = '". (int)$this->data['id'] ."'
            and id = '". (int)$this->data['campaigns'][$key]['id'] ."'
            limit 1;"
          );
        }
      }
      
    // Delete options
      $this->system->database->query(
        "delete from ". DB_TABLE_PRODUCTS_OPTIONS ."
        where product_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", @array_keys($this->data['options'])) ."');"
      );
      
    // Update options
      if (!empty($this->data['options'])) {
        $i = 0;
        foreach (array_keys($this->data['options']) as $key) {
          $i++;
          
          if (empty($this->data['options'][$key]['id'])) {
            $this->system->database->query(
              "insert into ". DB_TABLE_PRODUCTS_OPTIONS ."
              (product_id, date_created)
              values ('". (int)$this->data['id'] ."', '". date('Y-m-d H:i:s') ."');"
            );
            $this->data['options'][$key]['id'] = $this->system->database->insert_id();
          }
          
          $sql_currency_options = "";
          foreach (array_keys($this->system->currency->currencies) as $currency_code) {
            $sql_currency_options .= $currency_code ." = '". (float)$this->data['options'][$key][$currency_code] ."', ";
          }
          
          $this->system->database->query(
            "update ". DB_TABLE_PRODUCTS_OPTIONS ."
            set group_id = '". $this->system->database->input($this->data['options'][$key]['group_id']) ."',
                value_id = '". $this->system->database->input($this->data['options'][$key]['value_id']) ."',
                price_operator = '". $this->system->database->input($this->data['options'][$key]['price_operator']) ."',
                $sql_currency_options
                priority = '". (int)$i ."',
                date_updated = '". date('Y-m-d H:i:s') ."'
            where product_id = '". (int)$this->data['id'] ."'
            and id = '". (int)$this->data['options'][$key]['id'] ."'
            limit 1;"
          );
        }
      }
      
    // Delete stock options
      $this->system->database->query(
        "delete from ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ."
        where product_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", @array_keys($this->data['options_stock'])) ."');"
      );
      
    // Update stock options
      if (!empty($this->data['options_stock'])) {
        $i = 0;
        foreach (array_keys($this->data['options_stock']) as $key) {
          if (empty($this->data['options_stock'][$key]['id'])) {
            $this->system->database->query(
              "insert into ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ."
              (product_id, date_created)
              values ('". (int)$this->data['id'] ."', '". date('Y-m-d H:i:s') ."');"
            );
            $this->data['options_stock'][$key]['id'] = $this->system->database->insert_id();
          }
          
        // Ascending option combination
          $combinations = explode(',', $this->data['options_stock'][$key]['combination']);
          if (!function_exists('custom_sort_combinations')) {
            function custom_sort_combinations($a, $b) {
              $a = explode('-', $a);
              $b = explode('-', $b);
              if ($a[0] == $b[0]) {
                return ($a[1] < $b[1]) ? -1 : 1;
              }
              return ($a[0] < $b[0]) ? -1 : 1;
            }
          }
          usort($combinations, 'custom_sort_combinations');
          $this->data['options_stock'][$key]['combination'] = implode(',', $combinations);
          
          $this->system->database->query(
            "update ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ." 
            set combination = '". $this->system->database->input($this->data['options_stock'][$key]['combination']) ."',
            sku = '". $this->system->database->input(@$this->data['options_stock'][$key]['sku']) ."',
            weight = '". $this->system->database->input(@$this->data['options_stock'][$key]['weight']) ."',
            weight_class = '". $this->system->database->input(@$this->data['options_stock'][$key]['weight_class']) ."',
            dim_x = '". $this->system->database->input(@$this->data['options_stock'][$key]['dim_x']) ."',
            dim_y = '". $this->system->database->input(@$this->data['options_stock'][$key]['dim_y']) ."',
            dim_z = '". $this->system->database->input(@$this->data['options_stock'][$key]['dim_z']) ."',
            dim_class = '". $this->system->database->input(@$this->data['options_stock'][$key]['dim_class']) ."',
            quantity = '". $this->system->database->input(@$this->data['options_stock'][$key]['quantity']) ."',
            priority = '". $i++ ."',
            date_updated =  '". date('Y-m-d H:i:s') ."'
            where product_id = '". (int)$this->data['id'] ."'
            and id = '". (int)$this->data['options_stock'][$key]['id'] ."'
            limit 1;"
          );
        }
      }
      
    // Delete images
      $products_images_query = $this->system->database->query(
        "select * from ". DB_TABLE_PRODUCTS_IMAGES."
        where product_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", @array_keys($this->data['images'])) ."');"
      );
      while ($product_image = $this->system->database->fetch($products_images_query)) {
        if (is_file(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product_image['filename'])) unlink(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product_image['filename']);
        $this->system->functions->image_delete_cache(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product_image['filename']);
        $this->system->database->query(
          "delete from ". DB_TABLE_PRODUCTS_IMAGES ."
          where product_id = '". (int)$this->data['id'] ."'
          and id = '". (int)$product_image['id'] ."'
          limit 1;"
        );
      }
      
    // Update images
      if (!empty($this->data['images'])) {
        $image_priority = 1;
        foreach (array_keys($this->data['images']) as $key) {
          if (empty($this->data['images'][$key]['id'])) {
            $this->system->database->query(
              "insert into ". DB_TABLE_PRODUCTS_IMAGES ."
              (product_id)
              values ('". (int)$this->data['id'] ."');"
            );
            $this->data['images'][$key]['id'] = $this->system->database->insert_id();
          }
          if (!empty($this->data['images'][$key]['new_filename']) && !is_file(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['images'][$key]['new_filename'])) {
            $this->system->functions->image_delete_cache(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['images'][$key]['filename']);
            $this->system->functions->image_delete_cache(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['images'][$key]['new_filename']);
            rename(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['images'][$key]['filename'], FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['images'][$key]['new_filename']);
            $this->data['images'][$key]['filename'] = $this->data['images'][$key]['new_filename'];
          }
          $this->system->database->query(
            "update ". DB_TABLE_PRODUCTS_IMAGES ."
            set filename = '". $this->data['images'][$key]['filename'] ."',
                priority = '". $image_priority++ ."'
            where product_id = '". (int)$this->data['id'] ."'
            and id = '". (int)$this->data['images'][$key]['id'] ."'
            limit 1;"
          );
        }
      }
      
      $this->system->cache->set_breakpoint();
    }
    
    public function delete() {
    
      if (empty($this->data['id'])) return;
    
      $this->data['images'] = array();
      $this->data['options_stock'] = array();
      $this->data['campaigns'] = array();
      $this->save();
      
      $this->system->database->query(
        "delete from ". DB_TABLE_PRODUCTS ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $this->system->database->query(
        "delete from ". DB_TABLE_PRODUCTS_INFO ."
        where product_id = '". (int)$this->data['id'] ."';"
      );
      
      $this->system->database->query(
        "delete from ". DB_TABLE_PRODUCTS_PRICES ."
        where product_id = '". (int)$this->data['id'] ."';"
      );
      
      $this->system->cache->set_breakpoint();
    }
    
    public function add_image($file, $filename='') {
      
      if (empty($file)) return;
      
      if (!empty($filename)) $filename = 'products/' . $filename;
      
      if (empty($this->data['id'])) {
        $this->save();
      }
      
      if (!is_dir(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'products/')) mkdir(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'products/', 0777);
      
      require_once(FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . 'image.inc.php');
      
      if (substr($file, 0, 8) == 'https://' || substr($file, 0, 7) == 'http://') {
        $image = new ctrl_image();
        if (!$image->load_from_string($this->system->functions->http_request($file))) return false;
      } else {
        if (!$image = new ctrl_image($file)) return false;
      }
      
    // 456-Fancy-product-title-N.jpg
      $i=1;
      while (empty($filename) || is_file(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename)) {
        $filename = 'products/' . $this->data['id'] .'-'. $this->system->functions->general_url_friendly($this->data['name'][$this->system->settings->get('store_language_code')]) .'-'. $i++ .'.'. $image->type();
      }
      
      $priority = count($this->data['images'])+1;
      
      if (!$image->resample(1024, 1024, 'FIT_ONLY_BIGGER')) return false;
      
      if (!$image->write(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename, '', 90)) return false;
      
      $this->system->functions->image_delete_cache(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename);
      
      $this->system->database->query(
        "insert into ". DB_TABLE_PRODUCTS_IMAGES ."
        (product_id, filename, priority)
        values ('". (int)$this->data['id'] ."', '". $this->system->database->input($filename) ."', '". (int)$priority ."');"
      );
      $image_id = $this->system->database->insert_id();
      
      $this->data['images'][$image_id] = array(
        'id' => $image_id,
        'filename' => $filename,
        'priority' => $priority,
      );
    }
  }

?>