<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/partials/site_privacy_consent.inc.php
	 */

	// Honour browser "Do Not Track"
	//if (isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] == 1) {
	//	return;
	//}

	$privacy_classes = [
		'necessary' => [
			'id' => 'necessary',
			'title' => language::translate('title_strictly_necessary', 'Strictly Necessary'),
			'description' => language::translate('description_privacy_necessary', 'These features are used for activities that are absolutely necessary to operate or deliver a service you request from us. Therefore, we do not need your consent.'),
			'third_parties' => [],
		],
		'functionality' =>  [
			'id' => 'functionality',
			'title' => language::translate('title_functionality', 'Functionality'),
			'description' => language::translate('description_privacy_functionality', 'These features enable basic interaction and functionality that allows you to use selected features of our Service and communicate smoothly with us.'),
			'third_parties' => [],
		],
		'personalization' =>  [
			'id' => 'personalization',
			'title' => language::translate('title_personalization', 'Personalization'),
			'description' => language::translate('description_privacy_personalization', 'These features help us improve your user personalization and enable you to interact with external content, external networks and external platforms.'),
			'third_parties' => [],
		],
		'security' =>  [
			'id' => 'security',
			'title' => language::translate('title_security', 'Security'),
			'description' => language::translate('description_privacy_security', 'These features help us keep our service secure and prevent fraud.'),
			'third_parties' => [],
		],
		'measurement' => [
			'id' => 'measurement',
			'title' => language::translate('title_measurement', 'Measurement'),
			'description' => language::translate('description_privacy_measurement ', 'These features help us measure traffic and analyze your behavior to improve our service.'),
			'third_parties' => [],
		],
		'marketing' => [
			'id' => 'marketing',
			'title' => language::translate('title_marketing', 'Marketing'),
			'description' => language::translate('description_privacy_marketing', 'These features help us deliver personalized ads or personalized marketing to you. They also help us measure the performance of ads or marketing.'),
			'third_parties' => [],
		],
	];

	$third_parties = database::query(
		"select tp.*, json_value(tp.name, '$.". database::input(language::$selected['code']) ."') as name
		from ". DB_TABLE_PREFIX ."third_parties tp
		where tp.status
		order by name asc;"
	)->fetch_all();

	foreach ($third_parties as $party) {
		foreach (preg_split('#\s*,\s*#', $party['privacy_classes'], -1, PREG_SPLIT_NO_EMPTY) as $privacy_class) {
			$privacy_classes[$privacy_class]['third_parties'][] = $party;
		}
	}

	if (isset($_POST['privacy_consent']) && $_POST['privacy_consent'] == 1) {

		try {

			if (empty($_POST['privacy_classes'])) {
				$_POST['privacy_classes'] = [];
			}

			if (empty($_POST['third_parties'])) {
				$_POST['third_parties'] = [];
			}

			if (empty($_POST['consents'])) {
				$_POST['consents'] = ['necessary' => ['all']];
			}

			foreach ($_POST['privacy_classes'] as $privacy_class) {
				if (!in_array($privacy_class, [
					'necessary',
					'functionality',
					'personalization',
					'measurement',
					'marketing',
				])) {
					throw new Exception(language::translate('error_invalid_privacy_class', 'Invalid privacy class') .' ('. $privacy_class .')');
				}
			}

			$consents = [];

			foreach ($_POST['consents'] as $key => $values) {
				$consents[] = $key .':'. implode(',', $values) ?: 'all';
			}

			$consents = implode('|', $consents);

			header('Set-Cookie: privacy_consents='. $consents .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+12 months')) .'; Path=/; SameSite=Lax', false);
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['privacy_consent']) && $_POST['privacy_consent'] == 0) {
		header('Set-Cookie: privacy_consents=necessary:all; Path='. WS_DIR_APP .'; Expires=0; Path=/; SameSite=Lax', false);
		reload();
		exit;
	}

	$consents = [];

	if (!empty($_COOKIE['privacy_consents'])) {
		foreach (preg_split('#\|#', $_COOKIE['privacy_consents'], -1, PREG_SPLIT_NO_EMPTY) as $consent) {
			list($privacy_class, $values) = preg_split('#:#', $consent, 2);
			$values = preg_split('#,#', $values, -1, PREG_SPLIT_NO_EMPTY);
			$consents[$privacy_class] = $values;
		}
	}

	$site_privacy_consent = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/site_privacy_consent.inc.php');

	$site_privacy_consent->snippets = [
		'privacy_classes' => $privacy_classes,
		'consents' => $consents,
	];

	echo $site_privacy_consent->render();
