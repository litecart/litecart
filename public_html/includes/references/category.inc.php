<?php
  
  class ref_category {
    
    private $system;
    private $_data = array();
    
    function __construct($category_id) {
      global $system;
    
      $this->system = $system;
      
      if (empty($category_id)) trigger_error('Missing category id');
      
      $this->_data['id'] = (int)$category_id;
    }
    
    public function __get($name) {
      
      if (array_key_exists($name, $this->_data)) {
        return $this->_data[$name];
      }
      
      $this->load($name);
      
      return $this->_data[$name];
    }
    
    public function __set($name, $value) {
      $this->system->functions->error_trigger_traced('Setting data is prohibited', E_USER_ERROR);
    }
    
    private function load($type='') {
    
      switch($type) {
      
        case 'name':
        case 'description':
        case 'short_description':
        case 'head_title':
        case 'meta_description':
        case 'meta_keywords':
          
          $this->_data['info'] = array();
          
          $query = $this->system->database->query(
            "select language_code, name, description, short_description, head_title, meta_description, meta_keywords from ". DB_TABLE_CATEGORIES_INFO ."
            where category_id = '". (int)$this->_data['id'] ."'
            and language_code in ('". implode("', '", array_keys($this->system->language->languages)) ."');"
          );
          
          while ($row = $this->system->database->fetch($query)) {
            foreach ($row as $key => $value) $this->_data[$key][$row['language_code']] = $value;
          }
          
        // Fix missing translations
          foreach (array('name', 'description', 'short_description', 'head_title', 'meta_description', 'meta_keywords') as $key) {
            foreach (array_keys($this->system->language->languages) as $language_code) {
              if (empty($this->_data[$key][$language_code])) $this->_data[$key][$language_code] = $this->_data[$key][$this->system->settings->get('default_language_code')];
            }
          }
          
          break;
          
        case 'products_alphabetical':
        
          $this->_data['products_alphabetical'] = array();
          
          $query = $this->system->database->query(
            "select p.id, p.image, p.tax_class_id, pi.name, pp.". $this->system->database->input($this->system->currency->selected['code']) ." as price, pc_tmp.campaign_price, m.name as manufacturer_name
            from ". DB_TABLE_PRODUCTS ." p
            join ". DB_TABLE_PRODUCTS_INFO ." pi on (pi.product_id = p.id and language_code = '". $this->system->database->input($this->system->language->selected['code']) ."')
            join ". DB_TABLE_PRODUCTS_PRICES ." pp on (pp.product_id = p.id)
            join ". DB_TABLE_MANUFACTURERS ." m on (m.id = p.manufacturer_id)
            left outer join (
              select pc.product_id, pc.". $this->system->database->input($this->system->currency->selected['code']) ." as campaign_price
              from ". DB_TABLE_PRODUCTS_CAMPAIGNS ." pc
              where (pc.start_date = '0000-00-00 00:00:00' or pc.start_date <= '". date('Y-m-d H:i:s') ."')
              and (pc.end_date = '0000-00-00 00:00:00' or pc.end_date >= '". date('Y-m-d H:i:s') ."')
              order by pc.end_date asc
              limit 1
            ) pc_tmp on (pc_tmp.product_id = p.id)
            where p.status
            and find_in_set ('". (int)$this->_data['id'] ."', p.categories)
            order by pi.name asc;"
          );
          
          while ($row = $this->system->database->fetch($query)) {
            $this->_data['products_alphabetical'][$row['id']] = $row;
          }
          
          break;
          
        case 'subcategories':
        
          $this->_data['subcategories'] = array();
          
          $query = $this->system->database->query(
            "select id, name from ". DB_TABLE_CATEGORIES ."
            where parent_id = '". (int)$this->_data['id'] ."';"
          );
          
          while ($row = $this->system->database->fetch($query)) {
            foreach ($row as $key => $value) $this->_data['subcategories'][] = $row['id'];
          }
          
          break;
          
        default:
          
          if (isset($this->_data['date_added'])) return;
          
          $query = $this->system->database->query(
            "select * from ". DB_TABLE_CATEGORIES ."
            where id = '". (int)$this->_data['id'] ."'
            limit 1;"
          );
          
          $row = $this->system->database->fetch($query);
          
          if ($this->system->database->num_rows($query) == 0) trigger_error('Invalid category id ('. $this->_data['id'] .')', E_USER_ERROR);
          
          foreach ($row as $key => $value) $this->_data[$key] = $value;
          
          break;
      }
    }
  }
  
?>