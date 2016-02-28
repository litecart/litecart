<?php
  
  class ref_category {
    
    private $_id;
    private $_cache_id;
    private $_data = array();
    
    function __construct($category_id) {
      
      $this->_id = (int)$category_id;
      $this->_cache_id = cache::cache_id('category_'.(int)$category_id);
      
      if ($cache = cache::get($this->_cache_id, 'file')) {
        $this->_data = $cache;
      }
    }
    
    public function __get($name) {
      
      if (array_key_exists($name, $this->_data)) {
        return $this->_data[$name];
      }
      
      $this->_data[$name] = null;
      $this->load($name);
      
      return $this->_data[$name];
    }
    
    public function __isset($name) {
      return $this->__get($name);
    }
    
    public function __set($name, $value) {
      trigger_error('Setting data is prohibited', E_USER_WARNING);
    }
    
    private function load($field='') {
    
      switch($field) {
      
        case 'name':
        case 'description':
        case 'short_description':
        case 'head_title':
        case 'meta_description':
        case 'h1_title':
          
          $this->_data['info'] = array();
          
          $query = database::query(
            "select * from ". DB_TABLE_CATEGORIES_INFO ."
            where category_id = '". (int)$this->_id ."'
            and language_code in ('". implode("', '", array_keys(language::$languages)) ."');"
          );
          
          $fields = array(
            'name',
            'description',
            'short_description',
            'head_title',
            'meta_description',
            'h1_title',
          );
          
          while ($row = database::fetch($query)) {
            foreach ($fields as $key) {
              if (isset($row[$key])) $this->_data[$key][$row['language_code']] = $row[$key];
            }
          }
          
        // Fix missing translations
          foreach ($fields as $key) {
            foreach (array_keys(language::$languages) as $language_code) {
              if (empty($this->_data[$key][$language_code])) {
                $this->_data[$key][$language_code] = isset($this->_data[$key][settings::get('default_language_code')]) ? $this->_data[$key][settings::get('default_language_code')] : null;
              }
            }
          }
          
          break;
          
        case 'products':
        
          $this->_data['products'] = array();
          
          $query = database::query(
            "select id from ". DB_TABLE_PRODUCTS ."
            where status
            and find_in_set ('". (int)$this->_id ."', categories);"
          );
          
          while ($row = database::fetch($query)) {
            $this->_data['products'][$row['id']] = catalog::product($row);
          }
          
          break;
          
        case 'subcategories':
        
          $this->_data['subcategories'] = array();
          
          $query = database::query(
            "select id, name from ". DB_TABLE_CATEGORIES ."
            where parent_id = '". (int)$this->_id ."';"
          );
          
          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) {
              $this->_data['subcategories'][$row['id']] = $row['id'];
            }
          }
          
          break;
          
        default:
          
          if (isset($this->_data['date_added'])) return;
          
          $query = database::query(
            "select * from ". DB_TABLE_CATEGORIES ."
            where id = '". (int)$this->_id ."'
            limit 1;"
          );
          
          $row = database::fetch($query);
          
          if (database::num_rows($query) == 0) return;
          
          foreach ($row as $key => $value) $this->_data[$key] = $value;
          
          break;
      }
      
      cache::set($this->_cache_id, 'file', $this->_data);
    }
  }
  
?>