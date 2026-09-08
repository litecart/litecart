<?php

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex">';
  document::$snippets['title'][] = language::translate('title_reset_password', 'Reset Password');

  breadcrumbs::add(language::translate('title_reset_password', 'Reset Password'));

  if (!empty($_POST['reset_password'])) {

    $has_code = !empty($_REQUEST['reset_token']);
    $rate_limit_action = $has_code ? 'password_reset_failed' : 'password_reset_requested';

    try {

    // Rate limit checking (defence in depth on top of the cache-based counter further down)
      if (database::query(
        "select count(*) as num_attempts from ". DB_TABLE_PREFIX ."rate_limiting
        where action = '". $rate_limit_action ."'
        and (
          ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."'
          ". (!empty($_REQUEST['email']) ? "or (scope_type = 'email' and scope_key = '". database::input($_REQUEST['email']) ."')" : '') ."
        )
        and date_created >= '". date('Y-m-d H:i:s', strtotime('-5 minutes')) ."'"
      )->fetch('num_attempts') > 3) {
        throw new Exception(language::translate('error_too_many_attempts', 'Too many failed attempts. Please try again later.'));
      }

      if (empty($_REQUEST['email']) || !filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception(language::translate('error_must_provide_email_address', 'You must provide an email address'));
      }

      $customer = database::query(
        "select * from ". DB_TABLE_PREFIX ."customers
        where email = '". database::input($_REQUEST['email']) ."'
        limit 1;"
      )->fetch();

    // Generic responses to prevent email/account enumeration
      $generic_request_msg = language::translate('success_reset_password_email_sent', 'An email with instructions has been sent to your email address.');
      $generic_error_msg = language::translate('error_invalid_reset_token', 'Invalid or expired reset token');

      if (!empty($_REQUEST['reset_token'])) {

      // Rate limit token verification attempts per IP+email to thwart brute-force
        $rate_token = cache::token('reset_password_attempts', [$_REQUEST['email'], isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown'], 'session', 900);
        $cached_attempts = cache::get($rate_token);
        $attempts = (int)(isset($cached_attempts) ? $cached_attempts : 0);
        if ($attempts >= 5) {
          throw new Exception($generic_error_msg);
        }

        if (empty($customer) || empty($customer['password_reset_token'])) {
          cache::set($rate_token, $attempts + 1);
          throw new Exception($generic_error_msg);
        }

        $reset_token = json_decode($customer['password_reset_token'], true);
        if (!is_array($reset_token) || empty($reset_token['token']) || empty($reset_token['expires'])) {
          throw new Exception($generic_error_msg);
        }

      // Constant-time comparison to mitigate timing attacks
        if (!hash_equals((string)$reset_token['token'], (string)$_REQUEST['reset_token'])) {
          cache::set($rate_token, $attempts + 1);
          throw new Exception($generic_error_msg);
        }

        $expires_ts = strtotime($reset_token['expires']);
        if (!$expires_ts || $expires_ts < time()) {
          throw new Exception($generic_error_msg);
        }

        if (empty($customer['status'])) {
          throw new Exception($generic_error_msg);
        }

        if (settings::get('captcha_enabled')) {
          $captcha = functions::captcha_get('reset_password');
          if (empty($captcha) || !hash_equals((string)$captcha, (string)isset($_POST['captcha']) ? $_POST['captcha'] : '')) {
            throw new Exception(language::translate('error_invalid_captcha', 'Invalid CAPTCHA given'));
          }
        }

        if (empty($_POST['new_password'])) {
          throw new Exception(language::translate('error_missing_password', 'You must enter a password.'));
        }

        if (empty($_POST['confirmed_password'])) {
          throw new Exception(language::translate('error_missing_confirmed_password', 'You must confirm your password.'));
        }

        if ($_POST['new_password'] != $_POST['confirmed_password']) {
          throw new Exception(language::translate('error_passwords_did_not_match', 'Passwords did not match'));
        }

        if (!functions::password_check_strength($_POST['new_password'], 6)) {
          throw new Exception(language::translate('error_password_not_strong_enough', 'The password is not strong enough'));
        }

      } else {

        if (empty($customer) || empty($customer['status'])) {
        // Don't leak existence — succeed silently
          notices::add('success', $generic_request_msg);
          header('Location: '. document::ilink('reset_password', ['email' => $_REQUEST['email'], 'reset_token' => '']));
          exit;
        }

        if (settings::get('captcha_enabled')) {
          $captcha = functions::captcha_get('reset_password');
          if (empty($captcha) || !hash_equals((string)$captcha, (string)isset($_POST['captcha']) ? $_POST['captcha'] : '')) {
            throw new Exception(language::translate('error_invalid_captcha', 'Invalid CAPTCHA given'));
          }
        }
      }

    // Process

      if (empty($_REQUEST['reset_token'])) {

      // Cryptographically random 32-byte token (URL-safe base64) — 256 bits of entropy
        $reset_token = [
          'token' => rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '='),
          'expires' => date('Y-m-d H:i:s', strtotime('+15 minutes')),
        ];

        database::query(
          "update ". DB_TABLE_PREFIX ."customers
          set password_reset_token = '". database::input(json_encode($reset_token), JSON_UNESCAPED_SLASHES) ."'
          where id = ". (int)$customer['id'] ."
          limit 1;"
        );

        $aliases = [
          '%email' => $customer['email'],
          '%store_name' => settings::get('store_name'),
          '%token' => $reset_token['token'],
          '%link' => document::ilink('reset_password', ['email' => $customer['email'], 'reset_token' => $reset_token['token']]),
        ];

        $subject = language::translate('title_reset_password', 'Reset Password');
        $message = strtr(language::translate('email_body_reset_password', "You recently requested to reset your password for %store_name. If you did not request a password reset, please ignore this email. Visit the link below to reset your password:\r\n\r\n%link\r\n\r\nReset Token: %token"), $aliases);

        (new ent_email)
          ->add_recipient($customer['email'], $customer['firstname'] .' '. $customer['lastname'])
          ->set_subject($subject)
          ->add_body($message)
          ->send();

        database::query(
          "insert into ". DB_TABLE_PREFIX ."rate_limiting
          (action, scope_type, scope_key, ip_address, hostname, user_agent, date_created)
          values ('password_reset_requested', 'email', '". database::input($customer['email']) ."', '". database::input($_SERVER['REMOTE_ADDR']) ."', '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."', '". database::input($_SERVER['HTTP_USER_AGENT']) ."', '". date('Y-m-d H:i:s') ."')"
        );

        notices::add('success', $generic_request_msg);
        header('Location: '. document::ilink('reset_password', ['email' => $_REQUEST['email'], 'reset_token' => '']));
        exit;

      } else {

      // Clear rate limit + reset token on successful use
        cache::delete($rate_token);

        database::query(
          "delete from ". DB_TABLE_PREFIX ."rate_limiting
          where action = 'password_reset_failed'
          and (
            ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."'
            or (scope_type = 'email' and scope_key = '". database::input($_REQUEST['email']) ."')
          )"
        );

        $customer = new ent_customer($customer['id']);
        $customer->set_password($_POST['new_password']);
        $customer->data['password_reset_token'] = '';

        notices::add('success', language::translate('success_new_password_set', 'Your new password has been set. You may now sign in.'));
        header('Location: '. document::ilink('login', ['email' => $customer->data['email']]));
        exit;

      }

    } catch (Exception $e) {

    // Record failed attempts for rate-limiting
      database::query(
        "insert into ". DB_TABLE_PREFIX ."rate_limiting
        (action, scope_type, scope_key, ip_address, hostname, user_agent, date_created)
        values ('". $rate_limit_action ."', '". (!empty($_REQUEST['email']) ? 'email' : '') ."', '". (!empty($_REQUEST['email']) ? database::input($_REQUEST['email']) : '') ."', '". database::input($_SERVER['REMOTE_ADDR']) ."', '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."', '". database::input($_SERVER['HTTP_USER_AGENT']) ."', '". date('Y-m-d H:i:s') ."')"
      );

      notices::add('errors', $e->getMessage());
    }
  }


  $_page = new ent_view();
  echo $_page->stitch('pages/reset_password');
