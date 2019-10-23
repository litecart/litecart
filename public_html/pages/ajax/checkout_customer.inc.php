<?php

  header('X-Robots-Tag: noindex');

  functions::draw_lightbox();

  if (empty(cart::$items)) return;

  if (file_get_contents('php://input') == '') {
    foreach (customer::$data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  if (!empty($_POST['customer_details'])) {

    if (isset($_POST['email'])) $_POST['email'] = strtolower($_POST['email']);
    if (!isset($_POST['different_shipping_address'])) $_POST['different_shipping_address'] = 0;
    if (!isset($_POST['zone_code'])) $_POST['zone_code'] = '';
    if (!isset($_POST['shipping_address']['zone_code'])) $_POST['shipping_address']['zone_code'] = '';

  // Validate
    if (!empty($_POST['save_customer_details'])) { // <-- Button is pressed
      if (!empty($_POST['create_account'])) {
        try {
          if (empty($_POST['email'])) throw new Exception(language::translate('error_missing_email', 'You must enter an email address'));

          if (!functions::validate_email($_POST['email'])) throw new Exception(language::translate('error_invalid_email', 'The email address is invalid'));

          if (!database::num_rows(database::query("select id from ". DB_TABLE_CUSTOMERS ." where email = '". database::input($_POST['email']) ."' limit 1;"))) {
            if (empty($_POST['password'])) throw new Exception(language::translate('error_missing_password', 'You must enter a password'));
            if (!isset($_POST['confirmed_password']) || $_POST['password'] != $_POST['confirmed_password']) throw new Exception(language::translate('error_passwords_missmatch', 'The passwords did not match.'));
          }

          $mod_customer = new mod_customer();
          $result = $mod_customer->validate($_POST);
          if (!empty($result['error'])) throw new Exception($result['error']);

        } catch(Exception $e) {
          notices::add('errors', $e->getMessage());
        }
      }
    }

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

              $customer = new ent_customer();
              foreach (array_keys($customer->data) as $key) {
                if (isset(customer::$data[$key])) $customer->data[$key] = customer::$data[$key];
              }

              if (empty($_POST['password'])) $_POST['password'] = functions::password_generate(6);
              $customer->set_password($_POST['password']);

              $customer->save();

              database::query(
                "update ". DB_TABLE_CUSTOMERS ."
                set last_ip = '". database::input($_SERVER['REMOTE_ADDR']) ."',
                    last_host = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
                    last_agent = '". database::input($_SERVER['HTTP_USER_AGENT']) ."'
                where id = ". (int)$customer->data['id'] ."
                limit 1;"
              );

              $aliases = array(
                '%store_name' => settings::get('store_name'),
                '%store_link' => document::ilink(''),
                '%customer_firstname' => $_POST['firstname'],
                '%customer_lastname' => $_POST['lastname'],
                '%customer_email' => $_POST['email'],
                '%customer_password' => $_POST['password']
              );

              $subject = language::translate('email_subject_customer_account_created', 'Customer Account Created');
              $message = strtr(language::translate('email_account_created', "Welcome %customer_firstname %customer_lastname to %store_name!\r\n\r\nYour account has been created. You can now make purchases in our online store and keep track of history.\r\n\r\nLogin using your email address %customer_email.\r\n\r\n%store_name\r\n\r\n%store_link"), $aliases);

              $email = new ent_email();
              $email->add_recipient($_POST['email'], $_POST['firstname'] .' '. $_POST['lastname'])
                    ->set_subject($subject)
                    ->add_body($message)
                    ->send();

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

  $box_checkout_customer = new ent_view();
  $box_checkout_customer->snippets = array(
    'account_exists' => $account_exists,
  );
  echo $box_checkout_customer->stitch('views/box_checkout_customer');
