<?php

	document::$layout = 'blank';

	document::$head_tags[] = '<meta name="viewport" content="width=device-width, initial-scale=1">';

  if (empty(session::$data['security_verification']['type']) || session::$data['security_verification']['type'] != '2fa') {
		notices::add('errors', 'Invalid verification status');
		return;
	}

	$send_verification_code = function(){
		session::$data['security_verification'] = [
			'type' => '2fa',
			'code' => functions::password_generate(6, 0, 0, 6, 0),
			'expires' => strtotime('+15 minutes'),
			'attempts' => 0,
		];

		$email = new ent_email();
		$email->add_recipient(administrator::$data['email'])
					->set_subject(language::translate('title_verification_code', 'Verification Code'))
					->add_body(strtr(language::translate('email_verification_code', 'Verification code: %code'), ['%code' => session::$data['security_verification']['code']]))
					->send();

		notices::add('notices', language::translate('notice_verification_code_sent_via_email', 'A verification code was sent via email'));
	};

	if (isset($_POST['verify'])) {
		try {

			if (empty($_POST['code'])) {
				throw new Exception(language::translate('error_must_enter_authentication_code', 'You must enter verification code'));
			}

			if ($_POST['code'] != session::$data['security_verification']['code']) {
				throw new Exception(language::translate('error_invalid_verification_code', 'Invalid verification code'));
			}

			if (time() > session::$data['security_verification']['expires']) {
				throw new Exception(language::translate('error_verification_code_expired', 'The verification code has expired'));
			}

			$known_ips = preg_split('#\s*,\s*#', administrator::$data['known_ips'], -1, PREG_SPLIT_NO_EMPTY);

			array_unshift($known_ips, $_SERVER['REMOTE_ADDR']);
			$known_ips = array_unique($known_ips);

			if (count($known_ips) > 5) {
				array_pop($known_ips);
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."administrators
				set known_ips = '". database::input(implode(',', $known_ips)) ."'
				where id = ". (int)administrator::$data['id'] ."
				limit 1;"
			);

			unset(session::$data['security_verification']);

			if (!empty($_POST['redirect_url'])) {
				$redirect_url = new ent_link($_POST['redirect_url']);
				$redirect_url->host = '';
			} else {
				$redirect_url = document::ilink('b:');
			}

			notices::add('success', str_replace(['%username'], [administrator::$data['username']], language::translate('success_now_logged_in_as', 'You are now logged in as %username')));
			header('Location: '. $redirect_url);
			exit;

		} catch (Exception $e) {

			notices::add('errors', $e->getMessage());

			if (++session::$data['security_verification']['attempts'] >= 5 || time() > session::$data['security_verification']['expires']) {
				$send_verification_code();
			}
		}
	}

	if (isset($_POST['resend'])) {
		try {
			$send_verification_code();
		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$_page = new ent_view('app://backend/template/pages/verify_identity.inc.php');
	echo $_page;
