<?php
  
  class currency {
    private $system;
    public $currencies;
    public $selected;
    
    public function __construct(&$system) {
      $this->system = &$system;
    }
    
    public function load_dependencies() {
      
    // Bind selected to session
      if (!isset($this->system->session->data['currency']) || !is_array($this->system->session->data['currency'])) $this->system->session->data['currency'] = array();
      $this->selected = &$this->system->session->data['currency'];
      
    // Get currencies from database
      $currencies_query = $this->system->database->query(
        "select * from ". DB_TABLE_CURRENCIES ."
        where status
        order by priority;"
      );
      while ($row = $this->system->database->fetch($currencies_query)) {
        $this->currencies[$row['code']] = $row;
      }
      
    // Set currency, if not set
      if (empty($this->selected) || empty($this->currencies[$this->selected['code']]['status'])) $this->set();
    }
    
    //public function initiate() {
    //}
    
    public function startup() {
      if (!empty($_POST['set_currency'])) {
        $this->set($_POST['set_currency']);
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }
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
    
    public function set($code=null) {
      
      if (empty($code)) $code = $this->identify();
      
      if (!isset($this->currencies[$code])) trigger_error('Cannot set unsupported currency ('. $code .')', E_USER_ERROR);
      
      $this->system->session->data['currency'] = $this->currencies[$code];
      setcookie('currency_code', $code, time()+(60*60*24*30), WS_DIR_HTTP_HOME);
    }
    
    public function identify() {
    
    // Build list of supported currencies
      $currencies = array();
      foreach ($this->currencies as $currency) {
        if ($currency['status']) {
          $currencies[] = $currency['code'];
        }
      }
      
    // Return currency from cookie
      if (isset($_COOKIE['currency_code']) && in_array($_COOKIE['currency_code'], $currencies)) return $_COOKIE['currency_code'];
      
      return $this->system->settings->get('default_currency_code');
    }
    
    public function calculate($value, $to, $from=null) {
      
      if (empty($from)) $from = $this->system->settings->get('store_currency_code');
      
      if (!isset($this->currencies[$from])) trigger_error('Currency ('. $from .') does not exist', E_USER_ERROR);
      if (!isset($this->currencies[$to])) trigger_error('Currency ('. $to .') does not exist', E_USER_ERROR);
      
      return $value / $this->currencies[$from]['value'] * $this->currencies[$to]['value'];
    }
    
    public function convert($value, $from=null, $to) {
      return $this->calculate($value, $to, $from);
    }
    
    public function format($value, $auto_decimals=true, $raw=false, $code='', $currency_value=null) {
      
      if (empty($code)) $code = $this->selected['code'];
      if ($currency_value === null) $currency_value = $this->system->currency->currencies[$code]['value'];
      
      if (!isset($this->currencies[$code])) trigger_error('Currency ('. $code .') does not exist', E_USER_ERROR);
      
      $value = $value * $currency_value;
      
      if ($auto_decimals == false || $value - floor($value) > 0) {
        $decimals = (int)$this->currencies[$code]['decimals'];
      } else {
        $decimals = 0;
      }
      
      if ($raw) {
        return number_format($value, $decimals, '.', '');
      } else {
        return $this->currencies[$code]['prefix'] . number_format($value, $decimals, $this->system->language->selected['decimal_point'], $this->system->language->selected['thousands_sep']) . $this->currencies[$code]['suffix'];
      }
    }
  }
  
?>