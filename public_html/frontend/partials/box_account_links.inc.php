<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/partials/box_account_links.inc.php
	 */

	if (!settings::get('accounts_enabled')) return;

	$box_account = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_account_links.inc.php');

	$box_account->snippets = [
		'menu_items' => [],
	];

	$box_account->snippets['menu_items'][] = [
		'title' => language::translate('title_regional_settings', 'Regional Settings'),
		'link' => document::href_ilink('regional_settings'),
		'active' => (route::$selected['resource'] == 'f:regional_settings'),
	];

	if (!empty(customer::$data['id'])) {

		$box_account->snippets['menu_items'][] = [
			'title' => language::translate('title_edit_account', 'Edit Account'),
			'link' => document::href_ilink('account/edit'),
			'active' => (route::$selected['resource'] == 'f:edit_account'),
		];

		$box_account->snippets['menu_items'][] = [
			'title' => language::translate('title_order_history', 'Order History'),
			'link' => document::href_ilink('account/order_history'),
			'active' => (route::$selected['resource'] == 'f:order_history'),
		];

		$box_account->snippets['menu_items'][] = [
			'title' => language::translate('title_sign_out', 'Sign Out'),
			'link' => document::href_ilink('account/sign_out'),
			'active' => (route::$selected['resource'] == 'f:account/sign_out'),
		];

	} else {

		$box_account->snippets['menu_items'][] = [
			'title' => language::translate('title_sign_in', 'Sign In'),
			'link' => document::href_ilink('account/sign_in'),
			'active' => (route::$selected['resource'] == 'f:account/sign_in'),
		];

		$box_account->snippets['menu_items'][] = [
			'title' => language::translate('title_sign_up', 'Sign Up'),
			'link' => document::href_ilink('account/sign_up'),
			'active' => (route::$selected['resource'] == 'f:account/sign_up'),
		];

		$box_account->snippets['menu_items'][] = [
			'title' => language::translate('title_reset_password', 'Reset Password'),
			'link' => document::href_ilink('account/reset_password'),
			'active' => (route::$selected['resource'] == 'f:account/reset_password'),
		];
	}

	echo $box_account->render();
