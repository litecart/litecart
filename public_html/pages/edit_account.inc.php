<?php

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';

  customer::require_login();

  document::$snippets['title'][] = language::translate('title_edit_account', 'Edit Account');

  breadcrumbs::add(language::translate('title_account', 'Account'), '');
  breadcrumbs::add(language::translate('title_edit_account', 'Edit Account'));

  $customer = new ctrl_customer(customer::$data['id']);

  if (!$_POST) {
    foreach ($customer->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  if (!empty($_POST['save'])) {

    if (isset($_POST['email'])) $_POST['email'] = strtolower($_POST['email']);

    if (database::num_rows(database::query("select id from ". DB_TABLE_CUSTOMERS ." where email = '". database::input($_POST['email']) ."' and id != '". $customer->data['id'] ."' limit 1;"))) notices::add('errors', language::translate('error_email_already_registered', 'The email address already exists in our customer database.'));

    if (empty($_POST['email'])) notices::add('errors', language::translate('error_email_missing', 'You must enter an email address.'));

    if (!empty($_POST['new_password'])) {
      if (empty($_POST['confirmed_password'])) notices::add('errors', language::translate('error_missing_confirmed_password', 'You must confirm your password.'));
      if (isset($_POST['new_password']) && isset($_POST['confirmed_password']) && $_POST['new_password'] != $_POST['confirmed_password']) notices::add('errors', language::translate('error_passwords_missmatch', 'The passwords did not match.'));
    }

    if (empty($_POST['firstname'])) notices::add('errors', language::translate('error_missing_firstname', 'You must enter a first name.'));
    if (empty($_POST['lastname'])) notices::add('errors', language::translate('error_missing_lastname', 'You must enter a last name.'));
    if (empty($_POST['address1'])) notices::add('errors', language::translate('error_missing_address1', 'You must enter an address.'));
    if (empty($_POST['city'])) notices::add('errors', language::translate('error_missing_city', 'You must enter a city.'));
    if (empty($_POST['postcode']) && !empty($_POST['country_code']) && functions::reference_get_postcode_required($_POST['country_code'])) notices::add('errors', language::translate('error_missing_postcode', 'You must enter a postcode.'));
    if (empty($_POST['country_code'])) notices::add('errors', language::translate('error_missing_country', 'You must select a country.'));
    if (empty($_POST['zone_code']) && !empty($_POST['country_code']) && functions::reference_country_num_zones($_POST['country_code'])) notices::add('errors', language::translate('error_missing_zone', 'You must select a zone.'));

    if (!empty($_POST['different_shipping_address'])) {
      if (empty($_POST['shipping_address']['firstname'])) notices::add('errors', language::translate('error_missing_firstname', 'You must enter a first name.'));
      if (empty($_POST['shipping_address']['lastname'])) notices::add('errors', language::translate('error_missing_lastname', 'You must enter a last name.'));
      if (empty($_POST['shipping_address']['address1'])) notices::add('errors', language::translate('error_missing_address1', 'You must enter an address.'));
      if (empty($_POST['shipping_address']['city'])) notices::add('errors', language::translate('error_missing_city', 'You must enter a city.'));
      if (empty($_POST['shipping_address']['postcode']) && !empty($_POST['shipping_address']['country_code']) && functions::reference_get_postcode_required($_POST['shipping_address']['country_code'])) notices::add('errors', language::translate('error_missing_postcode', 'You must enter a postcode.'));
      if (empty($_POST['shipping_address']['country_code'])) notices::add('errors', language::translate('error_missing_country', 'You must select a country.'));
      if (empty($_POST['shipping_address']['zone_code']) && !empty($_POST['shipping_address']['country_code']) && functions::reference_country_num_zones($_POST['shipping_address']['country_code'])) notices::add('errors', language::translate('error_missing_zone', 'You must select a zone.'));
    }

    if (empty(notices::$data['errors'])) {

      if (!isset($_POST['different_shipping_address'])) $_POST['different_shipping_address'] = 0;
      if (!isset($_POST['newsletter'])) $_POST['newsletter'] = 0;

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
        'newsletter',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $customer->data[$field] = $_POST[$field];
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
      foreach ($fields as $field) {
        if (isset($_POST['shipping_address'][$field])) $customer->data['shipping_address'][$field] = $_POST['shipping_address'][$field];
      }

      if (empty($_POST['different_shipping_address'])) {
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
        foreach ($fields as $key) {
          $customer->data['shipping_address'][$key] = $customer->data[$key];
        }
      }

      if (!empty($_POST['new_password'])) $customer->set_password($_POST['new_password']);

      $customer->save();
      customer::$data = $customer->data;

      notices::add('success', language::translate('success_changes_saved', 'Changes saved successfully.'));

      header('Location: '. document::ilink());
      exit;
    }
  }

  $_page = new view();
  echo $_page->stitch('pages/edit_account');
?>