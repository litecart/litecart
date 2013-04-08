<?php
  
  class cart {
    
    private $system;
    public $data = array();
    public $cache = array();
    public $checksum;
    
    public function __construct(&$system) {
      $this->system = &$system;
    }
    
    public function load_dependencies() {
    
      if (!isset($this->system->session->data['cart']) || !is_array($this->system->session->data['cart'])) {
        $this->data = &$this->system->session->data['cart'];
        $this->system->session->data['cart'] = array(
          'items' => array(),
          'comments' => array(),
        );
      }
      
      $this->data = &$this->system->session->data['cart'];
    }
    
    //public function initiate() {
    //}
    
    public function startup() {
    
      if (!empty($_POST['add_cart_product'])) {
        $this->add_product($_POST['product_id'], (isset($_POST['options']) ? $_POST['options'] : array()), (isset($_POST['quantity']) ? $_POST['quantity'] : 1));
      }
      
      if (!empty($_POST['remove_cart_item'])) {
        $this->remove($_POST['key']);
      }
      
      if (!empty($_POST['update_cart_item'])) {
        $this->update($_POST['key'], (isset($_POST['quantity']) ? $_POST['quantity'] : 1));
      }
      
      if (!empty($_POST['clear_cart_items'])) {
        $this->reset();
      }
      
      $this->calculate_total();
      $this->checksum();
    }
    
    //public function before_capture() {
    //}
    
    //public function after_capture() {
    //}
    
    //public function prepare_output() {
    //}
    
    //public function before_output() {
    //}
    
    //public function shutdown() {
    //}
    
    ######################################################################
    
    public function load() {
      if (empty($this->system->customer->data['id'])) return;
      
      $cache_id = $this->system->cache->cache_id('cart_'.$this->system->customer->data['id']);
      $cart_data = $this->system->cache->get($cache_id, 'file', 60*60*24*365);
      
      if (empty($cart_data)) return;
      
      foreach ($cart_data as $item) $this->add_product($item['product_id'], $item['options'], $item['quantity'], true);
      
      if (!empty($_COOKIE['cart']['items'])) {
        $cart_data = unserialize($_COOKIE['cart']['items']);
        foreach ($_COOKIE['cart']['items'] as $item) $this->add_product($item['product_id'], $item['options'], $item['quantity'], true);
      }
    }
    
    public function save() {
      
      $cart_data = array();
      
      foreach (array_keys($this->data['items']) as $key) {
        
        $item_key = md5(serialize(array($this->data['items'][$key]['product_id'], $this->data['items'][$key]['options'])));
        
        $cart_data[$item_key] = array(
          'product_id' => $this->data['items'][$key]['product_id'],
          'options' => $this->data['items'][$key]['options'],
          'quantity' => $this->data['items'][$key]['quantity'],
        );
      }
      
      setcookie('cart', '', mktime(0, 0, 0, 1, 1, 2000));
      
      if (!empty($this->system->customer->data['id'])) {
        
        $cache_id = $this->system->cache->cache_id('cart_'.$this->system->customer->data['id']);
        $this->system->cache->set($cache_id, 'file', $cart_data);
        
      } else {
      
        setcookie('cart_items', serialize($cart_data), time() + (60*60*24*365));
      }
    }
    
    public function add_product($product_id, $options, $quantity=1, $silent=false) {
      
      if ($quantity <= 0) {
        if (!$silent) $this->system->notices->add('errors', 'Cannot add product to cart. Invalid product_id');
        return;
      }
      
      $item_key = md5(serialize(array($product_id, $options)));
      
      $product = new ref_product($product_id);
      
      if ($product->status == 0) {
        if (!$silent) $this->system->notices->add('errors', $this->system->language->translate('text_product_not_available_for_purchase', 'The product is not available for purchase'));
        return;
      }
      
      if (substr($product->date_valid_from, 0, 10) != '0000-00-00 00:00:00' && $product->date_valid_from > date('Y-m-d H:i:s')) {
        if (!$silent) $this->system->notices->add('errors', sprintf($this->system->language->translate('text_product_cannot_be_purchased_until_s', 'The product cannot be purchased until %s'), strftime($this->system->language->selected['format_date'], strtotime($product->date_valid_from))));
        return;
      }
      
      if (substr($product->date_valid_to, 0, 10) != '0000-00-00' && $product->date_valid_to < date('Y-m-d H:i:s')) {
        if (!$silent) $this->system->notices->add('errors', $this->system->language->translate('text_product_can_no_longer_be_purchased', 'The product can no longer be purchased'));
        return;
      }
      
      if (($product->quantity - $quantity) < 0 && empty($product->sold_out_status['orderable'])) {
        if (!$silent) $this->system->notices->add('errors', $this->system->language->translate('text_not_enough_products_in_stock', 'There are not enough products in stock.'));
        return;
      }
      
      $item = array(
        'id' => '',
        'product_id' => (int)$product_id,
        'options' => $options,
        'option_stock_combination' => '',
        'image' => $product->image,
        'name' => $product->name,
        'code' => $product->code,
        'sku' =>  $product->sku,
        'upc' =>  $product->upc,
        'taric' =>  $product->taric,
        'price' => $product->campaign['price'] ? $product->campaign['price'] : $product->price,
        'tax_class_id' => $product->tax_class_id,
        'quantity' => (int)$quantity,
        'weight' => $product->weight,
        'weight_class' => $product->weight_class,
        'dim_x' => $product->dim_x,
        'dim_y' => $product->dim_y,
        'dim_z' => $product->dim_z,
        'dim_class' => $product->dim_class,
      );
      
      $options = array_filter($options);
      $selected_options = array();
      
      if (count($product->options) > 0) {
        foreach (array_keys($product->options) as $key) {
          
          if ($product->options[$key]['required'] != 0) {
            if (empty($options[$product->options[$key]['name'][$this->system->language->selected['code']]])) {
              if (!$silent) $this->system->notices->add('errors', $this->system->language->translate('error_set_product_options', 'Please set your product options') . ' ('. $product->options[$key]['name'][$this->system->language->selected['code']] .')');
              return;
            }
          }
          
          if (!empty($options[$product->options[$key]['name'][$this->system->language->selected['code']]])) {
            switch ($product->options[$key]['function']) {
              case 'checkbox':
              
                $valid_values = array();
                foreach ($product->options[$key]['values'] as $value) {
                  $valid_values[] = $value['name'][$this->system->language->selected['code']];
                  if (in_array($value['name'][$this->system->language->selected['code']], $options[$product->options[$key]['name'][$this->system->language->selected['code']]])) {
                    $selected_options[] = $product->options[$key]['id'].'-'.$value['id'];
                    $item['price'] += $value['price_adjust'];
                  }
                }
                
                foreach ($options[$product->options[$key]['name'][$this->system->language->selected['code']]] as $current_value) {
                  if (!in_array($current_value, $valid_values)) {
                    if (!$silent) $this->system->notices->add('errors', $this->system->language->translate('error_product_options_contains_errors', 'The product options contains errors'));
                    return;
                  }
                }
                break;
              
              case 'input':
              case 'textarea':
                $selected_options[] = $product->options[$key]['id'].'-'.$product->options[$key]['values'][0]['id'];
                $item['price'] += $product->options[$key]['values'][0]['price_adjust'];
                break;
              
              case 'radio':
              case 'select':
              
                $valid_values = array();
                foreach ($product->options[$key]['values'] as $value) {
                  $valid_values[] = $value['name'][$this->system->language->selected['code']];
                  if ($value['name'][$this->system->language->selected['code']] == $options[$product->options[$key]['name'][$this->system->language->selected['code']]]) {
                    $selected_options[] = $product->options[$key]['id'].'-'.$value['id'];
                    $item['price'] += $value['price_adjust'];
                  }
                }
                
                if (!in_array($options[$product->options[$key]['name'][$this->system->language->selected['code']]], $valid_values)) {
                  if (!$silent) $this->system->notices->add('errors', $this->system->language->translate('error_product_options_contains_errors', 'The product options contains errors'));
                  return;
                }
                
                break;
            }
          }
        }
      }
      
      if (!empty($item['options'])) {
        foreach (array_keys($item['options']) as $key) {
          if (is_array($item['options'][$key])) $item['options'][$key] = implode(', ', $item['options'][$key]);
        }
      }
      
    // Match options with options stock
      if (count($product->options_stock) > 0) {
        foreach ($product->options_stock as $option_stock) {
        
          $option_match = true;
          foreach (explode(',', $option_stock['combination']) as $pair) {
            if (!in_array($pair, $selected_options)) {
              $option_match = false;
            }
          }
          
          if ($option_match) {
            if (($option_stock['quantity'] - $quantity) < 0 && empty($product->sold_out_status['orderable'])) {
              if (!$silent) $this->system->notices->add('errors', $this->system->language->translate('text_not_enough_products_in_stock_for_options', 'There are not enough products for the selected options.'));
              return;
            }
            
            $item['option_stock_combination'] = $option_stock['combination'];
            if (!empty($option_stock['sku'])) $item['sku'] = $option_stock['sku'];
            if (!empty($option_stock['weight'])) $item['weight'] = $option_stock['weight'];
            if (!empty($option_stock['weight_class'])) $item['weight_class'] = $option_stock['weight_class'];
            if (!empty($option_stock['dim_x'])) $item['dim_x'] = $option_stock['dim_x'];
            if (!empty($option_stock['dim_y'])) $item['dim_y'] = $option_stock['dim_y'];
            if (!empty($option_stock['dim_z'])) $item['dim_z'] = $option_stock['dim_z'];
            if (!empty($option_stock['dim_class'])) $item['dim_class'] = $option_stock['dim_class'];
            break;
          }
        }
      }
      
      if (isset($this->data['items'][$item_key]) && $options == $this->data['items'][$item_key]['options']) {
        
        $this->update($item_key, $this->data['items'][$item_key]['quantity'] + $quantity);
        
      } else {
        
        $this->data['items'][$item_key] = $item;
        
        $this->calculate_total();
        $this->checksum();
        $this->save();
      }
      
      if (!$silent) {
        $this->system->notices->add('success', $this->system->language->translate('success_product_added_to_cart', 'Your product was successfully added to the cart.'));
        return;
      }
    }
    
    public function update($item_key, $quantity) {
    
      if (!isset($this->data['items'][$item_key])) {
        $this->system->notices->add('errors', 'The product does not exist in cart.');
        return;
      }
      
      $product = new ref_product($this->data['items'][$item_key]['product_id']);
      
      /*
      if (!empty($this->data['items'][$item_key]['options']) && ($product->options[$this->data['items'][$item_key]['option_id']]['quantity'] - $quantity) < 0 && empty($product->sold_out_status['orderable'])) {
        $this->system->notices->add('errors', $this->system->language->translate('text_not_enough_products_option_in_stock', 'There are not enough products of the selected option in stock.'));
        return;
      } else if (($product->quantity - $quantity) < 0 && empty($product->sold_out_status['orderable'])) {
        $this->system->notices->add('errors', $this->system->language->translate('text_not_enough_products_in_stock', 'There are not enough products in stock.'));
        return;
      }
      */
    
      if ($quantity > 0) {
        $this->data['items'][$item_key]['quantity'] = (int)$quantity;
      } else {
        $this->remove($item_key);
      }
      
      $this->calculate_total();
      $this->checksum();
      $this->save();
    }

    public function remove($item_key) {
    
      if (isset($this->data['items'][$item_key])) {
        unset($this->data['items'][$item_key]);
      }
      
      $this->calculate_total();
      $this->checksum();
      $this->save();
      
      header('Location: '. $this->system->document->link('', array(), true));
      exit;
    }

    public function reset() {
    
      $this->data['items'] = array();
      
      $this->calculate_total();
      $this->checksum();
      $this->save();
    }
    
    private function calculate_total() {
      
      $total_value = 0;
      $total_tax = 0;
      $total_items = 0;
      $total_weight = 0;
      $total_physical = 0;
      $total_virtual = 0;
      
      foreach ($this->data['items'] as $item) {
        $total_value += $item['price'] * $item['quantity'];
        $total_tax += $this->system->tax->get_tax($item['price'], $item['tax_class_id']) * $item['quantity'];
        $total_items += $item['quantity'];
        //if ($item['type'] == 'physical') {
        //  $total_weight += $item['quantity'] * $this->system->weight->convert($item['weight'], $item['weight_class'], $this->system->settings->get('store_weight_class'));
          $total_physical += $item['quantity'];
        //} else if ($item['type'] == 'virtual') {
        //  $total_virtual += $item['quantity'];
        //}
      }
      
      $this->data['total'] = array(
        'value' => $total_value,
        'tax' => $total_tax,
        'items' => $total_items,
        'physical' => $total_physical,
        'virtual' => $total_virtual,
        'weight' => $total_weight,
      );
    }
    
    public function checksum() {
      $this->data['checksum'] = sha1(serialize(array_merge($this->data['items'], $this->system->language->selected)));
    }
  }
  
?>