<?php
  if (!empty($_POST['send'])) {

    if (settings::get('captcha_enabled')) {
      $captcha = functions::captcha_get('contact_us');
      if (empty($captcha) || $captcha != $_POST['captcha']) notices::add('errors', language::translate('error_invalid_captcha', 'Invalid CAPTCHA given'));
    }
    if (empty($_POST['name'])) notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));
    if (empty($_POST['subject'])) notices::add('errors', language::translate('error_must_enter_subject', 'You must enter a subject'));
    if (empty($_POST['email'])) notices::add('errors', language::translate('error_must_enter_email', 'You must enter a valid email address'));
    if (empty($_POST['message'])) notices::add('errors', language::translate('error_must_enter_message', 'You must enter a message'));

    if (empty(notices::$data['errors'])) {

      $result = functions::email_send(
        '"'. $_POST['name'] .'" <'. $_POST['email'] .'>',
        settings::get('store_email'),
        $_POST['subject'],
        $_POST['message']
      );

      if ($result) {
        notices::add('success', language::translate('success_your_email_was_sent', 'Your email has successfully been sent'));
        header('Location: '. document::ilink());
        exit;
      } else {
        notices::add('errors', language::translate('error_sending_email_for_unknown_reason', 'The email could not be sent for an unknown reason'));
      }
    }
  }

  $box_contact_us = new view();
  echo $box_contact_us->stitch('views/box_contact_us');
