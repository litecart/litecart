<?php
  if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    header('Content-type: text/html; charset='. language::$selected['charset']);
    document::$layout = 'ajax';
    header('X-Robots-Tag: noindex');
  }
  
  if (empty(cart::$items)) return;
  
  if (empty($_POST)) {
    foreach (customer::$data as $key => $value) {
      $_POST[$key] = $value;
    }
  }
  
  if (!empty($_POST['set_addresses'])) {
  
    if (isset($_POST['email'])) $_POST['email'] = strtolower($_POST['email']);
    
    if (empty($_POST['email'])) notices::add('errors', language::translate('error_email_missing', 'You must enter your email address.'));
    
    if (settings::get('register_guests') && settings::get('fields_customer_password')) {
      if (isset($_POST['email']) && !database::num_rows(database::query("select id from ". DB_TABLE_CUSTOMERS ." where email = '". database::input($_POST['email']) ."' limit 1;"))) {
        if (empty(customer::$data['desired_password'])) {
          if (empty($_POST['password'])) notices::add('errors', language::translate('error_missing_password', 'You must enter a password.'));
          if (empty($_POST['confirmed_password'])) notices::add('errors', language::translate('error_missing_confirmed_password', 'You must confirm your password.'));
          if (isset($_POST['password']) && isset($_POST['confirmed_password']) && $_POST['password'] != $_POST['confirmed_password']) notices::add('errors', language::translate('error_passwords_missmatch', 'The passwords did not match.'));
        }
      }
    }
    
    if (empty($_POST['firstname'])) notices::add('errors', language::translate('error_missing_firstname', 'You must enter a first name.'));
    if (empty($_POST['lastname'])) notices::add('errors', language::translate('error_missing_lastname', 'You must enter a last name.'));
    if (empty($_POST['address1'])) notices::add('errors', language::translate('error_missing_address1', 'You must enter an address.'));
    if (empty($_POST['city'])) notices::add('errors', language::translate('error_missing_city', 'You must enter a city.'));
    if (empty($_POST['postcode'])) notices::add('errors', language::translate('error_missing_postcode', 'You must enter a postcode.'));
    if (empty($_POST['country_code'])) notices::add('errors', language::translate('error_missing_country', 'You must select a country.'));
    if (empty($_POST['zone_code']) && functions::reference_country_num_zones($_POST['country_code'])) notices::add('errors', language::translate('error_missing_zone', 'You must select a zone.'));
    
    if (!empty($_POST['different_shipping_address'])) {
      if (empty($_POST['shipping_address']['firstname'])) notices::add('errors', language::translate('error_missing_firstname', 'You must enter a first name.'));
      if (empty($_POST['shipping_address']['lastname'])) notices::add('errors', language::translate('error_missing_lastname', 'You must enter a last name.'));
      if (empty($_POST['shipping_address']['address1'])) notices::add('errors', language::translate('error_missing_address1', 'You must enter an address.'));
      if (empty($_POST['shipping_address']['city'])) notices::add('errors', language::translate('error_missing_city', 'You must enter a city.'));
      if (empty($_POST['shipping_address']['postcode'])) notices::add('errors', language::translate('error_missing_postcode', 'You must enter a postcode.'));
      if (empty($_POST['shipping_address']['country_code'])) notices::add('errors', language::translate('error_missing_country', 'You must select a country.'));
      if (empty($_POST['shipping_address']['zone_code']) && functions::reference_country_num_zones($_POST['shipping_address']['country_code'])) notices::add('errors', language::translate('error_missing_zone', 'You must select a zone.'));
    }
    
    if (isset($_POST['email']) && $_POST['email'] != customer::$data['email'] && database::num_rows(database::query("select id from ". DB_TABLE_CUSTOMERS ." where email = '". database::input($_POST['email']) ."' limit 1;"))) {
      notices::add('notices', language::translate('notice_existing_customer_account_will_be_used', 'We found an existing customer account that will be used for this order'));
    }
    
    if (!empty(notices::$data['errors'])) notices::$data['errors'] = array(array_shift(notices::$data['errors']));
    
    if (!isset($_POST['different_shipping_address'])) $_POST['different_shipping_address'] = 0;
    
    $fields = array(
      'email',
      'tax_id',
      'company',
      'firstname',
      'lastname',
      'address1',
      'address2',
      'postcode',
      'city',
      'country_code',
      'zone_code',
      'phone',
      'mobile',
      'different_shipping_address',
    );
    
    foreach ($fields as $field) {
      if (isset($_POST[$field])) customer::$data[$field] = $_POST[$field];
    }
    
    $fields = array(
      'company',
      'firstname',
      'lastname',
      'address1',
      'address2',
      'postcode',
      'city',
      'country_code',
      'zone_code',
    );
    
    if (!empty(customer::$data['different_shipping_address'])) {
      foreach ($fields as $field) {
        if (isset($_POST['shipping_address'][$field])) customer::$data['shipping_address'][$field] = $_POST['shipping_address'][$field];
      }
    } else {
      foreach ($fields as $field) {
        if (isset($_POST[$field])) customer::$data['shipping_address'][$field] = $_POST[$field];
      }
    }
    
    customer::$data['country_name'] = functions::reference_get_country_name(customer::$data['country_code']);
    customer::$data['zone_name'] = functions::reference_get_zone_name(customer::$data['country_code'], customer::$data['zone_code']);
    
    customer::$data['shipping_address']['country_name'] = functions::reference_get_country_name(customer::$data['shipping_address']['country_code']);
    customer::$data['shipping_address']['zone_name'] = functions::reference_get_zone_name(customer::$data['shipping_address']['country_code'], customer::$data['shipping_address']['zone_code']);
    
    if (empty(customer::$data['id'])) {
      
      if (empty(notices::$data['errors']) && settings::get('register_guests') && !database::num_rows(database::query("select id from ". DB_TABLE_CUSTOMERS ." where email = '". database::input($_POST['email']) ."' limit 1;"))) {
        
        $customer = new ctrl_customer();
        $customer->data = customer::$data;
        $customer->save();
        
        if (empty($_POST['password'])) $_POST['password'] = functions::password_generate(6);
        $customer->set_password($_POST['password']);
        
        $email_message = language::translate('email_subject_account_created', "Welcome %customer_firstname %customer_lastname to %store_name!\r\n\r\nYour account has been created. You can now make purchases in our online store and keep track of history.\r\n\r\nLogin using your email address %customer_email and password %customer_password.\r\n\r\n%store_name\r\n\r\n%store_link");
        
        $translations = array(
          '%store_name' => settings::get('store_name'),
          '%store_link' => document::ilink(''),
          '%customer_firstname' => $_POST['firstname'],
          '%customer_lastname' => $_POST['lastname'],
          '%customer_email' => $_POST['email'],
          '%customer_password' => $_POST['password']
        );
        
        foreach ($translations as $needle => $replace) {
          $email_message = str_replace($needle, $replace, $email_message);
        }
        
        functions::email_send(
          null,
          $_POST['email'],
          language::translate('email_subject_customer_account_created', 'Customer Account Created'),
          $email_message
        );
        
        notices::add('success', language::translate('success_account_has_been_created', 'A customer account has been created that will let you keep track of orders.'));
        
      // Login user
        customer::load($customer->data['id']);
      }
    }
    
  // Clear errors, we won't be using them in this component
    if (!empty(notices::$data['errors'])) notices::$data['errors'] = array();
    
    header('Location: '. document::ilink());
    exit;
  }
  
  $box_checkout_customer = new view();
  
  echo $box_checkout_customer->stitch('views/box_checkout_customer');
  
?>