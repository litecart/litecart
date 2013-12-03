<?php
  
  class ref_category {
    private $_cache_id;
    private $_cache = array();
    
    function __construct($category_id) {
      
      if (empty($category_id)) trigger_error('Missing category id', E_USER_WARNING);
      
      $this->_cache_id = cache::cache_id('category_'.(int)$category_id);
      
      $cache = cache::get($this->_cache_id, 'file');
      
      $this->_cache = array_merge(array('id' => (int)$category_id), $cache ? $cache : array());
    }
    
    public function __get($name) {
      
      if (array_key_exists($name, $this->_cache)) {
        return $this->_cache[$name];
      }
      
      $this->_data[$name] = null;
      $this->load($name);
      
      return $this->_cache[$name];
    }
    
    public function __isset($name) {
      return $this->__get($name);
    }
    
    public function __set($name, $value) {
      trigger_error('Setting data is prohibited', E_USER_WARNING);
    }
    
    private function load($type='') {
    
      switch($type) {
      
        case 'name':
        case 'description':
        case 'short_description':
        case 'head_title':
        case 'meta_description':
        case 'meta_keywords':
        case 'h1_title':
          
          $this->_cache['info'] = array();
          
          $query = database::query(
            "select * from ". DB_TABLE_CATEGORIES_INFO ."
            where category_id = '". (int)$this->_cache['id'] ."'
            and language_code in ('". implode("', '", array_keys(language::$languages)) ."');"
          );
          
          $fields = array(
            'name',
            'description',
            'short_description',
            'head_title',
            'meta_description',
            'meta_keywords',
            'h1_title',
          );
          
          while ($row = database::fetch($query)) {
            foreach ($fields as $key) $this->_cache[$key][$row['language_code']] = $row[$key];
          }
          
        // Fix missing translations
          foreach ($fields as $key) {
            foreach (array_keys(language::$languages) as $language_code) {
              if (empty($this->_cache[$key][$language_code])) $this->_cache[$key][$language_code] = $this->_cache[$key][settings::get('default_language_code')];
            }
          }
          
          break;
          
        case 'products_alphabetical':
        
          $this->_cache['products_alphabetical'] = array();
          
          $query = database::query(
            "select p.id, p.image, p.tax_class_id, pi.name, pp.". database::input(currency::$selected['code']) ." as price, pc_tmp.campaign_price, m.name as manufacturer_name
            from ". DB_TABLE_PRODUCTS ." p
            join ". DB_TABLE_PRODUCTS_INFO ." pi on (pi.product_id = p.id and language_code = '". database::input(language::$selected['code']) ."')
            join ". DB_TABLE_PRODUCTS_PRICES ." pp on (pp.product_id = p.id)
            join ". DB_TABLE_MANUFACTURERS ." m on (m.id = p.manufacturer_id)
            left outer join (
              select pc.product_id, pc.". database::input(currency::$selected['code']) ." as campaign_price
              from ". DB_TABLE_PRODUCTS_CAMPAIGNS ." pc
              where (pc.start_date = '0000-00-00 00:00:00' or pc.start_date <= '". date('Y-m-d H:i:s') ."')
              and (pc.end_date = '0000-00-00 00:00:00' or pc.end_date >= '". date('Y-m-d H:i:s') ."')
              order by pc.end_date asc
              limit 1
            ) pc_tmp on (pc_tmp.product_id = p.id)
            where p.status
            and find_in_set ('". (int)$this->_cache['id'] ."', p.categories)
            order by pi.name asc;"
          );
          
          while ($row = database::fetch($query)) {
            $this->_cache['products_alphabetical'][$row['id']] = $row;
          }
          
          break;
          
        case 'subcategories':
        
          $this->_cache['subcategories'] = array();
          
          $query = database::query(
            "select id, name from ". DB_TABLE_CATEGORIES ."
            where parent_id = '". (int)$this->_cache['id'] ."';"
          );
          
          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) $this->_cache['subcategories'][] = $row['id'];
          }
          
          break;
          
        default:
          
          if (isset($this->_cache['date_added'])) return;
          
          $query = database::query(
            "select * from ". DB_TABLE_CATEGORIES ."
            where id = '". (int)$this->_cache['id'] ."'
            limit 1;"
          );
          
          $row = database::fetch($query);
          
          if (database::num_rows($query) == 0) trigger_error('Invalid category id ('. $this->_cache['id'] .')', E_USER_ERROR);
          
          foreach ($row as $key => $value) $this->_cache[$key] = $value;
          
          break;
      }
      
      cache::set($this->_cache_id, 'file', $this->_cache);
    }
  }
  
?>