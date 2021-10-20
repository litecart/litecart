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

      $message = strtr(language::translate('email_customer_feedback', "** This is an email message from %sender_name <%sender_email> **\r\n\r\n%message"), [
        '%sender_name' => $_POST['name'],
        '%sender_email' => $_POST['email'],
        '%message' => $_POST['message'],
      ]);

      $email = new ent_email();
      $email->set_sender($_POST['email'], $_POST['name'])
            ->add_recipient(settings::get('site_email'), settings::get('site_name'))
            ->set_subject($_POST['subject'])
            ->add_body($message);

      $result = $email->send();

      if (!$result) {
        throw new Exception(language::translate('error_sending_email_for_unknown_reason', 'The email could not be sent for an unknown reason'));
      }

      notices::add('success', language::translate('success_your_email_was_sent', 'Your email has successfully been sent'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $box_contact_us = new ent_view(FS_DIR_TEMPLATE . 'partials/box_contact_us.inc.php');
  echo $box_contact_us;
