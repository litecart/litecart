<?php
  document::$snippets['title'][] = language::translate('contact:head_title', 'Contact');
  document::$snippets['description'] = language::translate('contact:meta_description', '');

  if (!empty($_GET['page_id'])) {
    breadcrumbs::add(language::translate('title_contact', 'Contact'), document::ilink('contact'));
  } else {
    breadcrumbs::add(language::translate('title_contact', 'Contact'));
  }

  if (!$_POST) {
    $_POST = [
      'firstname' => customer::$data['firstname'],
      'lastname' => customer::$data['lastname'],
      'email' => customer::$data['email'],
    ];
  }

  if (!empty($_POST['send'])) {

    try {
      if (settings::get('captcha_enabled')) {
        $captcha = functions::captcha_get('contact_us');
        if (empty($captcha) || $captcha != $_POST['captcha']) throw new Exception(language::translate('error_invalid_captcha', 'Invalid CAPTCHA given'));
      }

      if (empty($_POST['firstname'])) throw new Exception(language::translate('error_missing_firstname', 'You must provide a firstname'));
      if (empty($_POST['lastname'])) throw new Exception(language::translate('error_missing_lastname', 'You must provide a lastname'));
      if (empty($_POST['subject'])) throw new Exception(language::translate('error_missing_subject', 'You must provide a subject'));
      if (empty($_POST['email'])) throw new Exception(language::translate('error_missing_email', 'You must provide a valid email address'));
      if (empty($_POST['message'])) throw new Exception(language::translate('error_missing_message', 'You must provide a message'));

    // Collect scraps
      if (empty(customer::$data['id'])) {
        customer::$data = array_replace(customer::$data, array_intersect_key(array_filter(array_diff_key($_POST, array_flip(['id']))), customer::$data));
      }

      $message = strtr(language::translate('email_customer_feedback', "** This is an email message from %sender_name <%sender_email> **\r\n\r\n%message"), [
        '%sender_name' => $_POST['firstname'] .' '. $_POST['lastname'],
        '%sender_email' => $_POST['email'],
        '%message' => $_POST['message'],
      ]);

      $email = new ent_email();
      $email->set_sender($_POST['email'], $_POST['firstname'] .' '. $_POST['lastname'])
            ->add_recipient(settings::get('store_email'), settings::get('store_name'))
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

  $_page = new ent_view(FS_DIR_TEMPLATE . 'pages/contact.inc.php');

  echo $_page;
