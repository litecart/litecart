<?php
  if (!empty($_POST['send'])) {

    try {
      if (settings::get('captcha_enabled')) {
        $captcha = functions::captcha_get('contact_us');
        if (empty($captcha) || $captcha != $_POST['captcha']) throw new Exception(language::translate('error_invalid_captcha', 'Invalid CAPTCHA given'));
      }

      if (empty($_POST['name'])) throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));
      if (empty($_POST['subject'])) throw new Exception(language::translate('error_must_enter_subject', 'You must enter a subject'));
      if (empty($_POST['email'])) throw new Exception(language::translate('error_must_enter_email', 'You must enter a valid email address'));
      if (empty($_POST['message'])) throw new Exception(language::translate('error_must_enter_message', 'You must enter a message'));

      $email = new email();
      $email->set_sender($_POST['email'], $_POST['name'])
            ->add_recipient(settings::get('store_email'), settings::get('store_name'))
            ->set_subject($_POST['subject'])
            ->add_body($_POST['message']);

      $result = $email->send();

      if ($result) {
        notices::add('success', language::translate('success_your_email_was_sent', 'Your email has successfully been sent'));
        header('Location: '. document::ilink());
        exit;
      } else {
        throw new Exception(language::translate('error_sending_email_for_unknown_reason', 'The email could not be sent for an unknown reason'));
      }

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $box_contact_us = new view();
  echo $box_contact_us->stitch('views/box_contact_us');
