<?php
  if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-type: text/html; charset='. language::$selected['charset']);
    document::$layout = 'ajax';
    header('X-Robots-Tag: noindex');
  }

  functions::draw_lightbox();

  if (empty(cart::$items)) return;

  if (file_get_contents('php://input') == '') {
    foreach (customer::$data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  if (file_get_contents('php://input') != '') {

    if (isset($_POST['email'])) $_POST['email'] = strtolower($_POST['email']);
    if (!isset($_POST['different_shipping_address'])) $_POST['different_shipping_address'] = 0;
    if (!isset($_POST['zone_code'])) $_POST['zone_code'] = '';
    if (!isset($_POST['shipping_address']['zone_code'])) $_POST['shipping_address']['zone_code'] = '';

  // Validate
    if (!empty($_POST['save_customer_details'])) { // <-- Button is pressed
      if (!empty($_POST['create_account'])) {
        if (isset($_POST['email']) && !database::num_rows(database::query("select id from ". DB_TABLE_CUSTOMERS ." where email = '". database::input($_POST['email']) ."' limit 1;"))) {
        if (empty($_POST['password']))
          notices::add('errors', language::translate('error_missing_password', 'You must enter a password.'));
        } else {
          if (!isset($_POST['confirmed_password']) || $_POST['password'] != $_POST['confirmed_password']) notices::add('errors', language::translate('error_passwords_missmatch', 'The passwords did not match.'));
        }
      }
    }

  // Keep first error only
    if (!empty(notices::$data['errors'])) notices::$data['errors'] = array(array_shift(notices::$data['errors']));

  // Billing address
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
      'different_shipping_address',
    );

    foreach ($fields as $field) {
      if (isset($_POST[$field])) customer::$data[$field] = $_POST[$field];
    }

  // Shipping address
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
      'phone',
    );

    foreach ($fields as $field) {
      if (!empty(customer::$data['different_shipping_address'])) {
        if (isset($_POST['shipping_address'][$field])) {
          customer::$data['shipping_address'][$field] = $_POST['shipping_address'][$field];
        } else {
          customer::$data['shipping_address'][$field] = null;
        }
      } else {
        if (isset($_POST[$field])) {
          customer::$data['shipping_address'][$field] = $_POST[$field];
        } else {
          customer::$data['shipping_address'][$field] = null;
        }
      }
    }

    if (empty(notices::$data['errors'])) {

    // Create customer account
      if (empty(customer::$data['id']) && !empty(customer::$data['email'])) {
        if (settings::get('register_guests') || !empty($_POST['create_account'])) {
          if (empty($_POST['password']) || ((isset($_POST['password']) && isset($_POST['confirmed_password'])) && $_POST['password'] == $_POST['confirmed_password'])) {

            if (!database::num_rows(database::query("select id from ". DB_TABLE_CUSTOMERS ." where email = '". database::input($_POST['email']) ."' limit 1;"))) {

              $customer = new ctrl_customer();
              foreach (array_keys($customer->data) as $key) {
                if (isset(customer::$data[$key])) $customer->data[$key] = customer::$data[$key];
              }
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

              customer::load($customer->data['id']);
            }
          }
        }
      }
    }
  }

  $account_exists = false;
  if (empty(customer::$data['id']) && !empty(customer::$data['email']) && database::num_rows(database::query("select id from ". DB_TABLE_CUSTOMERS ." where email = '". database::input(customer::$data['email']) ."' limit 1;"))) {
    $account_exists = true;
  }

  $box_checkout_customer = new view();
  $box_checkout_customer->snippets['account_exists'] = $account_exists;

  echo $box_checkout_customer->stitch('views/box_checkout_customer');
