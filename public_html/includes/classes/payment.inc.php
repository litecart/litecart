<?php

  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'module.inc.php');
  
  class payment extends module {
    public $options;
    public $window;
    public $data;

    public function __construct() {
      global $system;
      
      $this->system = &$system;
      
      parent::set_type('payment');
      
    // Link data to session object
      if (!isset($this->system->session->data['payment']) || !is_array($this->system->session->data['payment'])) {
        $this->system->session->data['payment'] = array();
      }
      $this->data = &$this->system->session->data['payment'];
      
      if (empty($this->data['selected'])) {
        $this->data['selected'] = array();
      }
      
    // Load modules
      $this->load();
      
      if (!isset($this->data['userdata'])) {
        $this->data['userdata'] = array();
      }
      
    // Attach userdata to module
      if (!empty($this->data['selected'])) {
        list($module_id, $option_id) = explode(':', $this->data['selected']['id']);
        $this->modules[$module_id]->userdata = &$this->data['userdata'][$module_id];
      }
    }
    
    public function options($items=null, $subtotal=null, $tax=null, $currency_code=null, $customer=null) {
      global $shipping;
      
      if ($items === null) $items = $this->system->cart->data['items'];
      if ($subtotal === null) $subtotal = $this->system->cart->data['total']['value'];
      if ($tax === null) $tax = $this->system->cart->data['total']['tax'];
      if ($currency_code === null) $currency_code = $this->system->currency->selected['code'];
      if ($customer === null) $customer = $this->system->customer->data;
      
      $cart_checksum = sha1(serialize($this->system->cart->data) . @serialize($shipping->data['selected']));
      
      //if (isset($this->data['order_checksum']) && $this->data['order_checksum'] == $cart_checksum) {
      //  return $this->data['options'];
      //}
      
      $this->data['options'] = array();
      $this->data['order_checksum'] = $cart_checksum;
      
      if (empty($this->modules)) return;
      
      foreach ($this->modules as $module) {
        
        $module_options = $module->options($items, $subtotal, $tax, $currency_code, $customer);
        
        if (!empty($module_options['options'])) {
          
          $this->data['options'][$module->id] = $module_options;
          $this->data['options'][$module->id]['id'] = $module->id;
          $this->data['options'][$module->id]['options'] = array();
          
          foreach ($module_options['options'] as $option) {
            $this->data['options'][$module->id]['options'][$option['id']] = $option;
          }
        }
      }
      
      return $this->data['options'];
    }
    
    public function select($module_id, $option_id) {
      
      if (!isset($this->data['options'][$module_id]['options'][$option_id])) {
        $this->data['selected'] = array();
        $this->system->notices->add('errors', $this->system->language->translate('error_invalid_payment_option', 'Cannot set an invalid payment option.'));
        return;
      }
      
      $this->data['userdata'][$module_id] = $_POST;
      
      if (method_exists($this->modules[$module_id], 'select')) {
        if ($error = $this->modules[$module_id]->select($option_id)) {
          $this->system->notices->add('errors', $error);
        }
      }
      
      $this->data['selected'] = array(
        'id' => $module_id.':'.$option_id,
        'icon' => $this->data['options'][$module_id]['options'][$option_id]['icon'],
        'title' => $this->data['options'][$module_id]['title'],
        'name' => $this->data['options'][$module_id]['options'][$option_id]['name'],
        'cost' => $this->data['options'][$module_id]['options'][$option_id]['cost'],
        'tax_class_id' => $this->data['options'][$module_id]['options'][$option_id]['tax_class_id'],
        'confirm' => $this->data['options'][$module_id]['options'][$option_id]['confirm'],
      );
    }
    
    public function set_cheapest() {
      
      foreach ($this->data['options'] as $module) {
        foreach ($module['options'] as $option) {
          if (!isset($cheapest_amount) || $option['cost'] < $cheapest_amount) {
            $cheapest_amount = $option['cost'];
            $module_id = $module['id'];
            $option_id = $option['id'];
          }
        }
      }
      
      $this->select($module_id, $option_id);
    }
    
    public function transfer() {
      
      if (empty($this->data['selected'])) trigger_error('Error: No payment option selected', E_USER_ERROR);
      
      list($module_id, $option_id) = explode(':', $this->data['selected']['id']);
      
      if (!method_exists($this->modules[$module_id], 'transfer')) return;
      
      $gateway = $this->modules[$module_id]->transfer();
      
      if (empty($gateway['action'])) return;
      
    // Gateway redirect
      if (strtolower($gateway['method']) == 'post') {
        echo '<p><img src="'. WS_DIR_IMAGES .'icons/16x16/loading.gif" width="16" height="16" /> '. $this->system->language->translate('title_redirecting', 'Redirecting') .'...</p>' . PHP_EOL
           . '<form name="gateway_form" method="post" action="'. $gateway['action'].'">' . PHP_EOL;
        if (is_array($gateway['fields'])) {
          foreach ($gateway['fields'] as $key => $value) {
            echo '  ' . $this->system->functions->form_draw_hidden_field($key, $value) . PHP_EOL;
          }
        } else {
          echo $gateway['fields'];
        }
        echo '</form>' . PHP_EOL
           . '<script language="javascript">' . PHP_EOL;
        if (!empty($gateway['delay'])) {
          echo '  var t=setTimeout(function(){' . PHP_EOL
             . '    document.forms["gateway_form"].submit();' . PHP_EOL
             . '  }, '. ($gateway['delay']*1000) .');' . PHP_EOL;
        } else {
          echo '  document.forms["gateway_form"].submit();' . PHP_EOL;
        }
        echo '</script>';
      } else {
        header('Location: '. $gateway['action']);
      }
      exit;
    }
    
    public function run($method_name, $module_id='') {
    
      if (empty($module_id)) {
        if (empty($this->data['selected']['id'])) return;
        list($module_id, $option_id) = explode(':', $this->data['selected']['id']);
      }
      
      if (method_exists($this->modules[$module_id], $method_name)) {
        return $this->modules[$module_id]->$method_name();
      }
    }
  }

?>