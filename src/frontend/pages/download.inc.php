<?php

	document::$layout = 'blank';

	header('X-Robots-Tag: noindex');

	try {

		if (empty($_GET['order_id']) || empty($_GET['public_key'])) {
			throw new Exception('Missing order_id or public_key');
		}

		$order = new ent_order($_GET['order_id']);

		if (empty($order->data['id']) || $_GET['public_key'] != $order->data['public_key']) {
			throw new Exception('Not found or invalid public_key');
		}

		if (empty($_GET['order_stock_item_id'])) {
			throw new Exception('Missing order_stock_item_id');
		}

		$stock_item = database::query(
			"select osi.id, osi.product_id, p.file, p.filename, p.mime_type
			from ". DB_PREFIX ."orders_stock_items osi
			left join ". DB_PREFIX ."stock_items si on (si.id = osi.product_id)
			where osi.order_id = ". (int)$order['id'] ."
			and osi.id = ". (int)$_GET['order_stock_item_id'] ."
			and osi.file
			limit 1;"
		)->fetch();

		if (!$stock_item) {
			throw new Exception('Could not find a download for the given order_stock_item_id', 400);
		}

		$file = 'storage://files/' . $stock_item['file'];

		if (!is_file($file)) {
			trigger_error('Missing download for product ' . $stock_item['id'], E_USER_WARNING);
			throw new Exception('The downloadable file does not exist on the server', 404);
		}

		database::query(
			"update ". DB_PREFIX ."orders_stock_items
			set downloads = downloads + 1
			where id = ". (int)$stock_item['id'] ."
			limit 1;"
		);

		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Type: ' . $stock_item['mime_type']);
		header('Content-Disposition: attachment; filename="' . $stock_item['filename'] .'"');
		header('Content-Length: ' . filesize($file));

		$fh = fopen($file, 'r');
		while ($buffer = fread($fh, 1024)) echo $buffer;
		fclose($fh);

		exit;

	} catch (Exception $e) {
		http_response_code(404);
		include 'app://frontend/pages/error_document.inc.php';
		return;
	}
