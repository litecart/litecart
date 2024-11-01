<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/checkout/summary.inc.php
	 */

	header('X-Robots-Tag: noindex');

	document::$layout = 'checkout';

	if (settings::get('catalog_only_mode')) {
		return;
	}

	if (!empty(session::$data['checkout']['order'])) {
		$order = &session::$data['checkout']['order'];
	} else {
		return;
	}

	if (empty($order->data['items'])) {
		return;
	}

	$box_checkout_summary = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/checkout/summary.inc.php');

	$box_checkout_summary->snippets = [
		'order' => $order->data,
		'error' => $order->validate(),
		'consent' => null,
		'confirm' => !empty($payment->selected['confirm']) ? $payment->selected['confirm'] : language::translate('title_confirm_order', 'Confirm Order'),
	];

	$privacy_policy_id = settings::get('privacy_policy');
	$terms_of_purchase_id = settings::get('terms_of_purchase');

	switch(true) {

		case ($terms_of_purchase_id && $privacy_policy_id):
			$box_checkout_summary->snippets['consent'] = language::translate('consent:privacy_policy_and_terms_of_purchase', 'I have read the <a href="%privacy_policy_link" target="_blank">Privacy Policy</a> and <a href="%terms_of_purchase_link" target="_blank">Terms of Purchase</a> and I consent.');
			break;

		case ($privacy_policy_id):
			$box_checkout_summary->snippets['consent'] = language::translate('consent:privacy_policy', 'I have read the <a href="%privacy_policy_link" target="_blank">Privacy Policy</a> and I consent.');
			break;

		case ($terms_of_purchase_id):
			$box_checkout_summary->snippets['consent'] = language::translate('consent:terms_of_purchase', 'I have read the <a href="%terms_of_purchase_link" target="_blank">Terms of Purchase</a> and I consent.');
			break;
	}

	$box_checkout_summary->snippets['consent'] = strtr($box_checkout_summary->snippets['consent'], [
		'%privacy_policy_link' => document::href_ilink('information', ['page_id' => $privacy_policy_id]),
		'%terms_of_purchase_link' => document::href_ilink('information', ['page_id' => $terms_of_purchase_id]),
	]);

	echo $box_checkout_summary->render();

	// Don't process layout if this is an ajax request
	if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		exit;
	}
