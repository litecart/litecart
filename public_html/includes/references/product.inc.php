<?php
  
  class ref_product {
    
    private $system;
    private $_data = array();
    private $_language_code;
    private $_currency_code;
    
    function __construct($product_id, $currency_code=null) {
      global $system;
    
      $this->system = $system;
      $this->_currency_code = $this->system->currency->selected['code'];
      
      if (empty($product_id)) trigger_error('Missing product id');
      
      $this->_data['id'] = (int)$product_id;
    }
    
    public function __get($name) {
      
      if (array_key_exists($name, $this->_data)) {
        return $this->_data[$name];
      }
      
      $this->load($name);
      
      return $this->_data[$name];
    }
    
    public function __set($name, $value) {
      trigger_error('Setting data ('. $name .') is prohibited', E_USER_ERROR);
    }
    
    private function load($type='') {
      
      switch($type) {
      
        case 'name':
        case 'description':
        case 'short_description':
        case 'head_title':
        case 'meta_description':
        case 'meta_keywords':
        case 'attributes':
          
          $query = $this->system->database->query(
            "select language_code, name, description, short_description, attributes, head_title, meta_description, meta_keywords from ". DB_TABLE_PRODUCTS_INFO ."
            where product_id = '". (int)$this->_data['id'] ."'
            and language_code in ('". implode("', '", array_keys($this->system->language->languages)) ."');"
          );
          
          while ($row = $this->system->database->fetch($query)) {
            foreach ($row as $key => $value) $this->_data[$key][$row['language_code']] = $value;
          }
          
        // Fix missing translations
          foreach (array('name', 'description', 'short_description', 'attributes', 'head_title', 'meta_description', 'meta_keywords') as $key) {
            foreach (array_keys($this->system->language->languages) as $language_code) {
              if (empty($this->_data[$key][$language_code])) {
                if (!empty($this->_data[$key][$this->system->settings->get('default_language_code')])) {
                  $this->_data[$key][$language_code] = $this->_data[$key][$this->system->settings->get('default_language_code')];
                } else { 
                  $this->_data[$key][$language_code] = '';
                }
              }
            }
          }
          
          break;
          
        case 'campaign':
        
          $this->_data['campaign'] = array();
          
          $products_campaigns_query = $this->system->database->query(
            "select * from ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
            where product_id = '". (int)$this->_data['id'] ."'
            and (start_date = '0000-00-00 00:00:00' or start_date <= '". date('Y-m-d H:i:s') ."')
            and (end_date = '0000-00-00 00:00:00' or end_date >= '". date('Y-m-d H:i:s') ."')
            order by end_date asc
            limit 1;"
          );
          $products_campaigns = $this->system->database->fetch($products_campaigns_query);
          
          if (!empty($products_campaigns[$this->system->currency->selected['code']])) {
            $this->_data['campaign']['price'] = $this->system->currency->convert($products_campaigns[$this->_currency_code], $this->_currency_code, $this->system->settings->get('store_currency_code'));
          } else {
            $this->_data['campaign']['price'] = $products_campaigns[$this->_currency_code];
          }
          
          break;

        case 'delivery_status':
          
          $this->_data['delivery_status'] = array();
          
          $query = $this->system->database->query(
            "select name, language_code from ". DB_TABLE_DELIVERY_STATUS_INFO ."
            where delivery_status_id = '". (int)$this->_data['delivery_status_id'] ."';"
          );
          
          while ($row = $this->system->database->fetch($query)) {
            $this->_data['delivery_status']['name'][$row['language_code']] = $row['name'];
          }
          
          if (empty($this->_data['delivery_status']['name'])) return;
          
        // Fix missing translations
          foreach (array('name') as $key) {
            foreach (array_keys($this->system->language->languages) as $language_code) {
              if (empty($this->_data['delivery_status'][$key][$language_code])) $this->_data['delivery_status'][$key][$language_code] = $this->_data['delivery_status'][$key][$this->system->settings->get('default_language_code')];
            }
          }
          
          break;
          
        case 'designer':
          
          $this->_data['designer'] = array();
          
          $query = $this->system->database->query(
            "select id, name, image from ". DB_TABLE_DESIGNERS ."
            where id = '". (int)$this->designer_id ."'
            limit 1;"
          );
          $this->_data['designer'] = $this->system->database->fetch($query);
          
          break;
          
        case 'images':
          
          $this->_data['images'] = array();
          
          $query = $this->system->database->query(
            "select * from ". DB_TABLE_PRODUCTS_IMAGES."
            where product_id = '". (int)$this->_data['id'] ."'
            order by priority asc, id asc;"
          );
          while ($row = $this->system->database->fetch($query)) {
            $this->_data['images'][$row['id']] = $row['filename'];
          }
          
          break;
          
        case 'manufacturer':
          
          $this->_data['manufacturer'] = array();
          
          $query = $this->system->database->query(
            "select id, name, image from ". DB_TABLE_MANUFACTURERS ."
            where id = '". (int)$this->manufacturer_id ."'
            limit 1;"
          );
          $this->_data['manufacturer'] = $this->system->database->fetch($query);
          
          break;
          
        case 'options':
          
          $this->_data['options'] = array();
          
          $products_options_query = $this->system->database->query(
            "select * from ". DB_TABLE_PRODUCTS_OPTIONS ."
            where product_id = '". (int)$this->_data['id'] ."'
            order by priority;"
          );
          
          while ($product_option = $this->system->database->fetch($products_options_query)) {
            
          // Set group
            if (isset($this->_data['options'][$product_option['group_id']]['function']) == false) {
              $option_group_query = $this->system->database->query(
                "select * from ". DB_TABLE_OPTION_GROUPS ."
                where id = '". (int)$product_option['group_id'] ."'
                limit 1;"
              );
              $option_group = $this->system->database->fetch($option_group_query);
              foreach (array('id', 'function', 'required', 'sort') as $key) {
                $this->_data['options'][$product_option['group_id']][$key] = $option_group[$key];
              }
            }
            
          // Set group info
            if (isset($this->_data['options'][$product_option['group_id']]['name']) == false) {
              $option_group_info_query = $this->system->database->query(
                "select name, description, language_code from ". DB_TABLE_OPTION_GROUPS_INFO ." pcgi
                where group_id = '". (int)$product_option['group_id'] ."';"
              );
              while ($option_group_info = $this->system->database->fetch($option_group_info_query)) {
                foreach (array('name', 'description') as $key) {
                  $this->_data['options'][$product_option['group_id']][$key][$option_group_info['language_code']] = $option_group_info[$key];
                }
              }
              
            // Fix missing value translations
              foreach (array('name', 'description') as $key) {
                foreach (array_keys($this->system->language->languages) as $language_code) {
                  if (!isset($this->_data['options'][$product_option['group_id']][$key][$language_code])) $this->_data['options'][$product_option['group_id']][$key] = $this->_data['options'][$product_option['group_id']][$key][$this->system->settings->get('default_language_code')];
                }
              }
            }
            
           // Set values
            if (isset($this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']]['value']) == false) {
              $option_value_query = $this->system->database->query(
                "select id, value, priority from ". DB_TABLE_OPTION_VALUES ."
                where id = '". (int)$product_option['value_id'] ."'
                limit 1;"
              );
              $option_value = $this->system->database->fetch($option_value_query);
              foreach (array_keys($option_value) as $key) {
                $this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']][$key] = $option_value[$key];
              }
            }
            
          // Set value translations
            if (isset($this->_data['options'][$product_option['group_id']]['values']['name']) == false) {
              $option_values_info_query = $this->system->database->query(
                "select name, language_code from ". DB_TABLE_OPTION_VALUES_INFO ." pcvi
                where value_id = '". (int)$product_option['value_id'] ."';"
              );
              while ($option_value_info = $this->system->database->fetch($option_values_info_query)) {
                $this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']]['name'][$option_value_info['language_code']] = $option_value_info['name'];
              }
              
            // Fix missing translations
              foreach (array('name') as $key) {
                foreach (array_keys($this->system->language->languages) as $language_code) {
                  if (empty($this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']][$key][$this->system->settings->get('default_language_code')])) break;
                  if (isset($this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']][$key][$language_code]) == false) $this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']][$key] = $this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']][$key][$this->system->settings->get('default_language_code')];
                }
              }
            }
            
          // Set price adjust
            if ($product_option[$this->_currency_code] > 0) {
              
              switch ($product_option['price_operator']) {
                case '+':
                  $product_option['price_adjust'] = $product_option[$this->_currency_code];
                  break;
                case '*':
                  $product_option['price_adjust'] = (empty($this->campaign['price']) == false ? $this->campaign['price'] : $this->price) - (empty($this->campaign['price']) == false ? $this->campaign['price'] : $this->price) * $configuration[$this->_currency_code];
                  break;
              }
            } else {
              $product_option['price_adjust'] = 0;
            }
            
            $this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']]['price_adjust'] = $product_option['price_adjust'];
            
          // Set product option priority
            $this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']]['priority_product'] = $product_option['priority'];
          }
          
        // Sort options
          foreach (array_keys($this->_data['options']) as $key) {
            switch ($this->_data['options'][$key]['sort']) {
              case 'priority':
                if (!function_exists('custom_sort_product_options_priority')) {
                  function custom_sort_product_options_priority($a, $b) {
                    if ($a['priority'] == $b['priority']) {
                      return 0;
                    }
                    return ($a['priority'] < $b['priority']) ? -1 : 1;
                  }
                }
                usort($this->_data['options'][$key]['values'], 'custom_sort_product_options_priority');
                break;
              case 'product':
                if (!function_exists('custom_sort_product_options_product')) {
                  function custom_sort_product_options_product($a, $b) {
                    if ($a['priority_product'] == $b['priority_product']) {
                      return 0;
                    }
                    return ($a['priority_product'] < $b['priority_product']) ? -1 : 1;
                  }
                }
                usort($this->_data['options'][$key]['values'], 'custom_sort_product_options_product');
                break;
              case 'alphabetical':
              default:
                if (!function_exists('custom_sort_product_options_alphabetical')) {
                  function custom_sort_product_options_alphabetical($a, $b) {
                    global $system;
                    if ($a['name'][$system->language->selected['code']] == $b['name'][$system->language->selected['code']]) {
                      return 0;
                    }
                    return ($a['name'][$system->language->selected['code']] < $b['name'][$system->language->selected['code']]) ? -1 : 1;
                  }
                }
                usort($this->_data['options'][$key]['values'], 'custom_sort_product_options_alphabetical');
                break;
            }
          }
          
          break;
          
        case 'options_stock':
          
          $this->_data['options_stock'] = array();
          
          $query = $this->system->database->query(
            "select * from ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ."
            where product_id = '". (int)$this->_data['id'] ."'
            ". (!empty($option_id) ? "and id = '". (int)$option_id ."'" : '') ."
            order by priority asc;"
          );
          
          while ($row = $this->system->database->fetch($query)) {
            
            if (empty($row['tax_class_id'])) {
              $row['tax_class_id'] = $this->tax_class_id;
            }
            
            if (empty($row['sku'])) {
              $row['sku'] = $this->sku;
            }
            
            if (empty($row['weight'])) {
              $row['weight'] = $this->weight;
              $row['weight_class'] = $this->weight_class;
            }
            
            if (empty($row['dim_x'])) {
              $row['dim_x'] = $this->dim_x;
              $row['dim_y'] = $this->dim_y;
              $row['dim_z'] = $this->dim_z;
              $row['dim_class'] = $this->dim_class;
            }
            
            $row['name'] = array();
            
            foreach (explode(',', $row['combination']) as $combination) {
              list($group_id, $value_id) = explode('-', $combination);
              
              $options_values_query = $this->system->database->query(
                "select distinct ovi.value_id, ovi.name, ovi.language_code from ". DB_TABLE_OPTION_VALUES_INFO ." ovi
                where ovi.value_id = '". (int)$value_id ."'
                and language_code in ('". implode("', '", array_keys($this->system->language->languages)) ."');"
              );
              
              while($option_value = $this->system->database->fetch($options_values_query)) {
              
                if (isset($row['name'][$option_value['language_code']])) {
                  $row['name'][$option_value['language_code']] .= ', ';
                } else {
                  $row['name'][$option_value['language_code']] = '';
                }
                $row['name'][$option_value['language_code']] .= $option_value['name'];
              }
            }
            
          // Fix missing translations
            foreach (array('name') as $key) {
              foreach (array_keys($this->system->language->languages) as $language_code) {
                if (empty($row[$key][$language_code])) $row[$key][$language_code] = $row[$key][$this->system->settings->get('default_language_code')];
              }
            }
            
            $this->_data['options_stock'][$row['id']] = $row;
          }
          
          break;
          
        case 'price':
        
          $this->_data['price'] = 0;
          
          $products_prices_query = $this->system->database->query(
            "select * from ". DB_TABLE_PRODUCTS_PRICES ."
            where product_id = '". (int)$this->_data['id'] ."'
            limit 1;"
          );
          $product_price = $this->system->database->fetch($products_prices_query);
          
          if (!empty($product_price[$this->system->currency->selected['code']])) {
            $this->_data['price'] = $this->system->currency->convert($product_price[$this->_currency_code], $this->_currency_code, $this->system->settings->get('store_currency_code'));
          } else {
            $this->_data['price'] = $product_price[$this->_currency_code];
          }
          
          break;
          
        case 'sold_out_status':
          
          $this->_data['sold_out_status'] = array();
          
          $query = $this->system->database->query(
            "select id, orderable from ". DB_TABLE_SOLD_OUT_STATUS ."
            where id = '". (int)$this->_data['sold_out_status_id'] ."'
            limit 1;"
          );
          $this->_data['sold_out_status'] = $this->system->database->fetch($query);
          
          if (empty($this->_data['sold_out_status'])) return;
          
          $query = $this->system->database->query(
            "select name, language_code from ". DB_TABLE_SOLD_OUT_STATUS_INFO ."
            where sold_out_status_id = '". (int)$this->_data['sold_out_status_id'] ."';"
          );
          
          while ($row = $this->system->database->fetch($query)) {
            $this->_data['sold_out_status']['name'][$row['language_code']] = $row['name'];
          }
          
        // Fix missing translations
          foreach (array('name') as $key) {
            foreach (array_keys($this->system->language->languages) as $language_code) {
              if (empty($this->_data['sold_out_status'][$key][$language_code])) $this->_data['sold_out_status'][$key][$language_code] = $this->_data['sold_out_status'][$key][$this->system->settings->get('default_language_code')];
            }
          }
          
          break;
          
        default:
          
          if (isset($this->_data['date_added'])) return;
          
          $query = $this->system->database->query(
            "select * from ". DB_TABLE_PRODUCTS ."
            where id = '". (int)$this->_data['id'] ."'
            limit 1;"
          );
          
          $row = $this->system->database->fetch($query);
          
          if ($this->system->database->num_rows($query) == 0) trigger_error('Invalid product id ('. $this->_data['id'] .')', E_USER_ERROR);
          
          foreach ($row as $key => $value) $this->_data[$key] = $value;
          
          $this->_data['categories'] = explode(',', $this->_data['categories']);
          $this->_data['product_groups'] = explode(',', $this->_data['product_groups']);
          
          break;
      }
    }
  }
  
?>