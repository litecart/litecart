<?php

  /*!
   * This file contains PHP logic that is separated from the HTML view.
   * Visual changes can be made to the file found in the template folder:
   *
   *   ~/frontend/templates/default/pages/edit_account.inc.php
   */

  header('X-Robots-Tag: noindex');
  document::$head_tags['noindex'] = '<meta name="robots" content="noindex">';

  customer::require_login();

  document::$title[] = language::translate('title_edit_account', 'Edit Account');

  if (!settings::get('accounts_enabled')) {
    echo language::translate('error_accounts_are_disabled', 'Accounts are disabled');
    return;
  }

  breadcrumbs::add(language::translate('title_account', 'Account'), '');
  breadcrumbs::add(language::translate('title_edit_account', 'Edit Account'));

  $customer = new ent_customer(customer::$data['id']);

  if (!$_POST) {
    $_POST = $customer->data;
  }

  if (isset($_POST['save_account'])) {

    try {

      if (isset($_POST['email'])) {
        $_POST['email'] = strtolower($_POST['email']);
      }

      if (database::query("select id from ". DB_TABLE_PREFIX ."customers where email = '". database::input($_POST['email']) ."' and id != ". (int)$customer->data['id'] ." limit 1;")->num_rows) {
        throw new Exception(language::translate('error_email_already_registered', 'The email address already exists in our customer database.'));
      }

      if (empty($_POST['email'])) {
        throw new Exception(language::translate('error_email_missing', 'You must enter an email address.'));
      }

      if (!password_verify($_POST['password'], customer::$data['password_hash'])) {
        throw new Exception(language::translate('error_wrong_password', 'Wrong password'));
      }

      if (!empty($_POST['new_password'])) {

        if (empty($_POST['confirmed_password'])) {
          throw new Exception(language::translate('error_missing_confirmed_password', 'You must confirm your password.'));
        }

        if (isset($_POST['new_password']) && isset($_POST['confirmed_password']) && $_POST['new_password'] != $_POST['confirmed_password']) {
          throw new Exception(language::translate('error_passwords_missmatch', 'The passwords did not match.'));
        }

        if (!functions::password_check_strength($_POST['password'])) {
          throw new Exception(language::translate('error_password_not_strong_enough', 'The password is not strong enough'));
        }
      }

      foreach ([
        'email',
      ] as $field) {
        if (isset($_POST[$field])) {
          $customer->data[$field] = $_POST[$field];
        }
      }

      if (!empty($_POST['new_password'])) {
        $customer->set_password($_POST['new_password']);
      }

      $customer->data['password_reset_token'] = '';
      $customer->data['date_expire_sessions'] = date('Y-m-d H:i:s');
      $customer->save();

      customer::load($customer->data['id']);

      session::regenerate_id();
      session::$data['customer_security_timestamp'] = strtotime($customer->data['date_expire_sessions']);

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['save_details'])) {

    try {

      if (!isset($_POST['different_shipping_address'])) {
        $_POST['different_shipping_address'] = 0;
      }

      if (!isset($_POST['newsletter'])) {
        $_POST['newsletter'] = 0;
      }

      if (empty($_POST['firstname'])) {
        throw new Exception(language::translate('error_missing_firstname', 'You must enter a first name.'));
      }

      if (empty($_POST['lastname'])) {
        throw new Exception(language::translate('error_missing_lastname', 'You must enter a last name.'));
      }

      if (empty($_POST['address1'])) {
        throw new Exception(language::translate('error_missing_address1', 'You must enter an address.'));
      }

      if (empty($_POST['city'])) {
        throw new Exception(language::translate('error_missing_city', 'You must enter a city.'));
      }

      if (empty($_POST['postcode']) && !empty($_POST['country_code']) && reference::country($_POST['country_code'])->postcode_format) {
        throw new Exception(language::translate('error_missing_postcode', 'You must enter a postcode.'));
      }

      if (empty($_POST['country_code'])) {
        throw new Exception(language::translate('error_missing_country', 'You must select a country.'));
      }

      if (empty($_POST['zone_code']) && settings::get('customer_field_zone') && reference::country($_POST['country_code'])->zones) {
        throw new Exception(language::translate('error_missing_zone', 'You must select a zone.'));
      }

      if (!empty($_POST['different_shipping_address']) && settings::get('customer_shipping_address')) {

        if (empty($_POST['shipping_address']['firstname'])) {
          throw new Exception(language::translate('error_missing_firstname', 'You must enter a first name.'));
        }

        if (empty($_POST['shipping_address']['lastname'])) {
          throw new Exception(language::translate('error_missing_lastname', 'You must enter a last name.'));
        }

        if (empty($_POST['shipping_address']['address1'])) {
          throw new Exception(language::translate('error_missing_address1', 'You must enter an address.'));
        }

        if (empty($_POST['shipping_address']['city'])) {
          throw new Exception(language::translate('error_missing_city', 'You must enter a city.'));
        }

        if (empty($_POST['shipping_address']['postcode']) &&!empty($_POST['shipping_address']['country_code'])) {
          if (reference::country($_POST['shipping_address']['country_code'])->postcode_format) {
            throw new Exception(language::translate('error_missing_postcode', 'You must enter a postcode.'));
          }
        }

        if (empty($_POST['shipping_address']['country_code'])) {
          throw new Exception(language::translate('error_missing_country', 'You must select a country.'));
        }

        if (empty($_POST['shipping_address']['zone_code']) && settings::get('customer_field_zone') && reference::country($_POST['shipping_address']['country_code'])->zones){
          throw new Exception(language::translate('error_missing_zone', 'You must select a zone.'));
        }
      }

      foreach ([
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
        'newsletter',
      ] as $field) {
        if (isset($_POST[$field])) {
          $customer->data[$field] = $_POST[$field];
        }
      }

      foreach ([
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
      ] as $field) {
        if (isset($_POST[$field])) {
          $customer->data['shipping_address'][$field] = $_POST[$field];
        }
      }

      $customer->save();
      customer::$data = $customer->data;

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $_page = new ent_view('app://frontend/templates/'. settings::get('template') .'/pages/edit_account.inc.php');
  echo $_page->render();
