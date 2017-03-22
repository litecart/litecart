<?php

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  document::$snippets['title'][] = language::translate('title_reset_password', 'Reset Password');

  breadcrumbs::add(language::translate('title_reset_password', 'Reset Password'));

  if (!empty($_POST['reset_password'])) {

    try {

      if (empty($_REQUEST['email'])) throw new Exception(language::translate('error_must_provide_email_address', 'You must provide an email address'));

      $customer_query = database::query(
        "select * from ". DB_TABLE_CUSTOMERS ."
        where email = '". database::input($_REQUEST['email']) ."'
        limit 1;"
      );
      $customer = database::fetch($customer_query);

      if (empty($customer)) throw new Exception(language::translate('error_email_not_in_database', 'The email address does not exist in our database.'));

      if (empty($customer['status'])) throw new Exception(language::translate('error_account_inactive', 'Your account is inactive, contact customer support'));

      if (!empty($_REQUEST['reset_token'])) {

        if (!$reset_token = json_decode($customer['password_reset_token'], true)) throw new Exception(language::translate('error_invalid_reset_token', 'Invalid reset token'));

        if ($_REQUEST['reset_token'] != $reset_token['token']) throw new Exception(language::translate('error_incorrect_reset_token', 'Incorrect reset token'));

        if (strtotime($reset_token['expires']) < time()) throw new Exception(language::translate('error_reset_token_expired', 'The reset token has expired'));

        if (empty($_POST['new_password'])) throw new Exception(language::translate('error_missing_password', 'You must enter a password.'));

        if (empty($_POST['confirmed_password'])) throw new Exception(language::translate('error_missing_confirmed_password', 'You must confirm your password.'));

        if ($_POST['new_password'] != $_POST['confirmed_password']) {
          throw new Exception(language::translate('error_passwords_did_not_match', 'Passwords not not match'));
        }
      }

    // Process

      if (empty($_REQUEST['reset_token'])) {

        $reset_token = array(
          'token' => functions::password_generate(8),
          'expires' => date('Y-m-d H:i:s', strtotime('+30 minutes')),
        );

        database::query(
          "update ". DB_TABLE_CUSTOMERS ."
          set password_reset_token = '". database::input(json_encode($reset_token)) ."'
          where id = '". (int)$customer['id'] ."'
          limit 1;"
        );

        $message = language::translate('email_body_reset_password', "You recently requested to reset your password for %store_name. If you did not request a password reset, please ignore this email. Visit the link below to reset your password:\r\n\r\n%link\r\n\r\nReset Token: %token");
        $message = strtr($message, array(
          '%email' => $customer['email'],
          '%store_name' => settings::get('store_name'),
          '%token' => $reset_token['token'],
          '%link' => document::ilink('reset_password', array('email' => $customer['email'], 'reset_token' => $reset_token['token'])),
        ));

        functions::email_send(
          null,
          $customer['email'],
          language::translate('title_reset_password', 'Reset Password'),
          $message
        );

        notices::add('success', language::translate('success_reset_password_email_sent', 'An email with instructions has been sent to your email address.'));
        header('Location: '. document::ilink('reset_password', array('email' => $_REQUEST['email'], 'reset_token' => '')));
        exit;

      } else {

        database::query(
          "update ". DB_TABLE_CUSTOMERS ."
          set password_reset_token = ''
          where id = '". (int)$customer['id'] ."'
          limit 1;"
        );

        $customer = new ctrl_customer($customer['id']);
        $customer->set_password($_POST['new_password']);

        notices::add('success', language::translate('success_new_password_set', 'Your new password has been set. You may now sign in.'));
        header('Location: '. document::ilink('login', array('email' => $customer->data['email'])));
        exit;

      }

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }


  $_page = new view();
  echo $_page->stitch('pages/reset_password');
