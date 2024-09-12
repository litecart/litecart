<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/regional_settings.inc.php
	 */

	header('X-Robots-Tag: noindex');

	document::$title[] = language::translate('regional_settings:head_title', 'Regional Settings');

	breadcrumbs::add(language::translate('title_regional_settings', 'Regional Settings'), document::ilink('regional_settings'));

	if (isset($_POST['save'])) {

		try {

			if (!empty($_GET['redirect_url']) && (empty($_POST['language_code']) || $_POST['language_code'] == language::$selected['code'])) {
				$redirect_url = new ent_link($_GET['redirect_url']);
				$redirect_url->host = '';
			} else {
				$redirect_url = document::ilink('', [], null, [], !empty($_POST['language_code']) ? $_POST['language_code'] : '');
			}

			if (!empty($_POST['language_code'])) {
				language::set($_POST['language_code']);
			}

			if (!empty($_POST['currency_code'])) {
				currency::set($_POST['currency_code']);
			}

			if (!empty($_POST['country_code'])) {
				customer::$data['country_code'] = $_POST['country_code'];
				customer::$data['zone_code'] = fallback($_POST['zone_code']);
				if (!empty($_COOKIE['cookies_accepted']) || !settings::get('cookie_policy')) {
					header('Set-Cookie: country_code='. customer::$data['country_code'] .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+3 months')) .'; SameSite=Lax', false);
					header('Set-Cookie: zone_code='. customer::$data['zone_code'] .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+3 months')) .'; SameSite=Lax', false);
				}
			}

			if (isset($_POST['postcode'])) {
				customer::$data['postcode'] = $_POST['postcode'];
				customer::$data['shipping_address']['postcode'] = $_POST['postcode'];
			}

			if (isset($_POST['display_prices_including_tax'])) {
				customer::$data['display_prices_including_tax'] = (int)$_POST['display_prices_including_tax'];
				if (!empty($_COOKIE['cookies_accepted']) || !settings::get('cookie_policy')) {
					header('Set-Cookie: display_prices_including_tax='. (int)customer::$data['display_prices_including_tax'] .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+3 months')) .'; SameSite=Lax', false);
				}
			}

			if (!empty($_GET['redirect_url'])) {
				$redirect_url = new ent_link($_GET['redirect_url']);
				$redirect_url->host = '';
			} else {
				$redirect_url = document::ilink('', [], null, [], fallback($_POST['language_code']));
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. $redirect_url);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/regional_settings.inc.php');

	$_page->snippets = [
		'currencies' => [],
		'languages' => [],
	];

	foreach (currency::$currencies as $currency) {
		if (!empty(administrator::$data['id']) || $currency['status'] == 1) {
			$_page->snippets['currencies'][] = $currency;
		}
	}

	foreach (language::$languages as $language) {
		if (!empty(administrator::$data['id']) || $language['status'] == 1) {
			$_page->snippets['languages'][] = $language;
		}
	}

	if (!in_array(currency::$selected, $_page->snippets['currencies'])) {
		$_page->snippets['currencies'][] = currency::$selected;
	}

	if (!in_array(language::$selected, $_page->snippets['languages'])) {
		$_page->snippets['languages'][] = language::$selected;
	}

	echo $_page->render();
