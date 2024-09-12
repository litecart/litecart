<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/order.inc.php
	 */

	header('X-Robots-Tag: noindex');

	try {

		if ((empty($_GET['order_id']) && empty($_GET['order_no'])) || empty($_GET['public_key'])) {
			throw new Exception('Missing order or key. Sign in to your account if you got the link wrong.', 400);
		}

		if (!empty($_GET['order_id'])) {

			$order = database::query(
				"select id from ". DB_TABLE_PREFIX ."orders
				where id = ". (int)$_GET['order_id'] ."
				limit 1;"
			)->fetch();

			if (!$order) {
				throw new Exception('Invalid order_id', 404);
			}

		} else if (!empty($_GET['order_no'])) {

			$order = database::query(
				"select id from ". DB_TABLE_PREFIX ."orders
				where no = '". database::input($_GET['order_no']) ."'
				limit 1;"
			)->fetch();

			if (!$order) {
				throw new Exception('Invalid order_no', 404);
			}
		}

		$order = new ent_order($order['id']);

		if (empty($order->data['id']) || $_GET['public_key'] != $order->data['public_key']) {
			throw new Exception('Invalid key', 401);
		}

		document::$layout = 'blank';
		document::$title[] = language::translate('title_order', 'Order') .' '. $order->data['no'];

		breadcrumbs::add(language::translate('title_account', 'Account'), document::ilink('account'));
		breadcrumbs::add(language::translate('title_order_history', 'Order History'), document::ilink('order_history'));
		breadcrumbs::add(language::translate('title_order', 'Order') .' '. $order->data['no'], document::ilink('order', ['order_id' => $order->data['id'], 'public_key' => $order->data['public_key']]));

		$session_language = language::$selected['code'];
		language::set($order->data['language_code']);

		$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/order.inc.php');
		$_page->snippets = [
			'text_direction' => !empty(language::$languages[$order->data['language_code']]['direction']) ? language::$languages[$order->data['language_code']]['direction'] : 'ltr',
			'order' => $order->data,
			'comments' => [],
		];

		foreach ($order->data['comments'] as $comment) {
			if (!empty($comment['hidden'])) continue;

			switch($comment['author']) {
				case 'customer':
					$comment['type'] = 'local';
					break;
				case 'staff':
					$comment['type'] = 'remote';
					break;
				default:
					$comment['type'] = 'event';
					break;
			}

			$_page->snippets['comments'][] = $comment;
		}

		echo $_page->render();

		language::set($session_language);

	} catch (Exception $e) {

		http_response_code($code);
		include 'app://frontend/pages/error_document.inc.php';
		return;
	}
