<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/account/order_history.inc.php
	 */

	header('X-Robots-Tag: noindex');

	document::$title[] = language::translate('order_history:head_title', 'Order History');

	customer::require_login();

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	breadcrumbs::add(language::translate('title_account', 'Account'));
	breadcrumbs::add(language::translate('title_order_history', 'Order History'), document::ilink('account/order_history'));

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/account/order_history.inc.php');

	$_page->snippets['orders'] = [];

	// Table Rows, Total Number of Rows, Total Number of Pages
	$orders = database::query(
		"select o.*, os.name as order_status_name
		from ". DB_TABLE_PREFIX ."orders o
		left join (
			select os.id, os.hidden, osi.name
			from ". DB_TABLE_PREFIX ."order_statuses os
			left join ". DB_TABLE_PREFIX ."order_statuses_info osi on (osi.order_status_id = os.id and osi.language_code = '". language::$selected['code'] ."')
		) os on (os.id = o.order_status_id)
		where order_status_id is not null
		and o.customer_id = ". (int)customer::$data['id'] ."
		and os.hidden != 0
		order by o.date_created desc;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

	foreach ($orders as $key => $order) {

		$num_downloads = database::query(
			"select oi.id
			from ". DB_TABLE_PREFIX ."orders_items oi
			left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = oi.stock_item_id)
			where oi.order_id = ". (int)$order['id'] ."
			and si.file;"
		)->num_rows;

		$_page->snippets['orders'][] = [
			'id' => $order['id'],
			'link' => document::ilink('order', ['order_id' => $order['id'], 'public_key' => $order['public_key']]),
			'printable_link' => document::ilink('printable_order_copy', ['order_id' => $order['id'], 'public_key' => $order['public_key']]),
			'order_status' => $order['order_status_name'],
			'num_downloads' => $num_downloads,
			'date_created' => language::strftime('datetime', $order['date_created']),
			'total' => currency::format($order['total'], false, $order['currency_code'], $order['currency_value']),
		];
	}

	$_page->snippets['pagination'] = functions::draw_pagination($num_pages);

	echo $_page->render();
