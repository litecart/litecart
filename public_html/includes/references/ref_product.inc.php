<?php
  
  class ref_product {
    
    private $system;
    private $_data = array();
    private $_currency_code;
    
    function __construct($product_id, $currency_code=null) {
      global $system;
    
      $this->system = &$system;
      $this->_currency_code = !empty($currency_code) ? $currency_code : $this->system->currency->selected['code'];
      
      if (empty($product_id)) trigger_error('Missing product id', E_USER_ERROR);
      
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
          
          if ($products_campaigns[$this->_currency_code] > 0) {
            $this->_data['campaign']['price'] = $this->system->currency->convert($products_campaigns[$this->_currency_code], $this->_currency_code, $this->system->settings->get('store_currency_code'));
          } else {
            $this->_data['campaign']['price'] = $products_campaigns[$this->system->settings->get('store_currency_code')];
          }
          
          break;
          
        case 'categories':
          
          $this->_data['categories'] = array();
          
          if (count($this->category_ids)) {
            foreach ($this->category_ids as $category_id) {
              $query = $this->system->database->query(
                "select name, language_code from ". DB_TABLE_CATEGORIES_INFO ."
                where category_id = '". (int)$category_id ."';"
              );
              
              while ($row = $this->system->database->fetch($query)) {
                $this->_data['categories'][$category_id][$row['language_code']] = $row['name'];
              }
              
            // Fix missing translations
              foreach (array('name') as $key) {
                foreach (array_keys($this->system->language->languages) as $language_code) {
                  if (empty($this->_data['categories'][$category_id][$language_code])) $this->_data['categories'][$category_id][$language_code] = $this->_data['categories'][$category_id][$this->system->settings->get('default_language_code')];
                }
              }
            }
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
          
          // Groups
            if (isset($this->_data['options'][$product_option['group_id']]['function']) == false) {
              $option_group_query = $this->system->database->query(
                "select * from ". DB_TABLE_OPTION_GROUPS ."
                where id = '". (int)$product_option['group_id'] ."'
                limit 1;"
              );
              $option_group = $this->system->database->fetch($option_group_query);
              foreach (array('id', 'function', 'required') as $key) {
                $this->_data['options'][$product_option['group_id']][$key] = $option_group[$key];
              }
            }
            
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
              
            // Fix missing translations
              foreach (array('name', 'description') as $key) {
                foreach (array_keys($this->system->language->languages) as $language_code) {
                  if (!isset($this->_data['options'][$product_option['group_id']][$key][$language_code])) $this->_data['options'][$product_option['group_id']][$key][$language_code] = $this->_data['options'][$product_option['group_id']][$key][$this->system->settings->get('default_language_code')];
                }
              }
            }
            
          // Values
            if (isset($this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']]['value']) == false) {
              $option_value_query = $this->system->database->query(
                "select * from ". DB_TABLE_OPTION_VALUES ."
                where id = '". (int)$product_option['value_id'] ."'
                limit 1;"
              );
              $option_value = $this->system->database->fetch($option_value_query);
              foreach (array('id', 'value') as $key) {
                $this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']][$key] = $option_value[$key];
              }
            }
            
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
                  if (isset($this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']][$key][$language_code]) == false) $this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']][$key][$language_code] = $this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']][$key][$this->system->settings->get('default_language_code')];
                }
              }
            }
            
            $product_option['price_adjust'] = 0;
              
            if ($product_option[$this->_currency_code] > 0) {
              
              switch ($product_option['price_operator']) {
                case '+':
                  $product_option['price_adjust'] = $product_option[$this->_currency_code];
                  break;
                case '*':
                  $product_option['price_adjust'] = (empty($this->campaign['price']) == false ? $this->campaign['price'] : $this->price) - (empty($this->campaign['price']) == false ? $this->campaign['price'] : $this->price) * $configuration[$this->_currency_code];
                  break;
                default:
                  trigger_error('Unknown price operator for option', E_USER_WARNING);
                  break;
              }
            }
            
            $this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']]['price_adjust'] = $product_option['price_adjust'];
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
          
          if ($product_price[$this->_currency_code] > 0) {
            $this->_data['price'] = $this->system->currency->convert($product_price[$this->system->currency->selected['code']], $this->_currency_code, $this->system->settings->get('store_currency_code'));
          } else {
            $this->_data['price'] = $product_price[$this->system->settings->get('store_currency_code')];
          }
          
          break;
          
        case 'product_group_ids':
        case 'product_groups':
          
          $this->_data['product_groups'] = array();
          
          if (count($this->product_group_ids)) {
            foreach ($this->product_group_ids as $pair) {
              
              list($group_id, $value_id) = explode('-', $pair);
              
              $query = $this->system->database->query(
                "select name, language_code from ". DB_TABLE_PRODUCT_GROUPS_INFO ."
                where product_group_id = '". (int)$group_id ."';"
              );
              while ($group = $this->system->database->fetch($query)) {
                $this->_data['product_groups'][$group_id]['name'][$group['language_code']] = $group['name'];
              }
              
            // Fix missing translations
              foreach (array('name') as $key) {
                foreach (array_keys($this->system->language->languages) as $language_code) {
                  if (empty($this->_data['product_groups'][$group_id]['name'][$language_code])) $this->_data['product_groups'][$group_id]['name'][$language_code] = $this->_data['product_groups'][$group_id]['name'][$this->system->settings->get('default_language_code')];
                }
              }
              
              $query = $this->system->database->query(
                "select name, language_code from ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
                where product_group_value_id = '". (int)$value_id ."';"
              );
              while ($value = $this->system->database->fetch($query)) {
                $this->_data['product_groups'][$group_id]['values'][$value_id][$value['language_code']] = $value['name'];
              }
              
            // Fix missing translations
              foreach (array('name') as $key) {
                foreach (array_keys($this->system->language->languages) as $language_code) {
                  if (empty($this->_data['product_groups'][$group_id]['values'][$value_id][$language_code])) $this->_data['product_groups'][$group_id]['values'][$value_id][$language_code] = $this->_data['product_groups'][$group_id]['values'][$value_id][$this->system->settings->get('default_language_code')];
                }
              }
            }
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
          
          $query = $this->system->database->query(
            "select * from ". DB_TABLE_PRODUCTS ."
            where id = '". (int)$this->_data['id'] ."'
            limit 1;"
          );
          $row = $this->system->database->fetch($query);
          
          if ($this->system->database->num_rows($query) == 0) return;
          
          if (!empty($row['categories'])) {
            $row['category_ids'] = explode(',', $row['categories']);
          } else {
            $row['category_ids'] = array();
          }
          unset($row['categories']);
          
          if (!empty($row['product_groups'])) {
            $row['product_group_ids'] = explode(',', $row['product_groups']);
          } else {
            $row['product_group_ids'] = array();
          }
          unset($row['product_groups']);
          
          foreach ($row as $key => $value) $this->_data[$key] = $value;
          
          break;
      }
    }
  }
  
?>