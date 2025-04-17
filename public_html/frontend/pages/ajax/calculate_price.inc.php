<?php

	try {

		if (empty($_REQUEST['product_id']) || !preg_match('#^[0-9]+$#', $_REQUEST['quantity'])) {
			throw new Exception('Missing or invalid product_id', 400);
		}

		if (empty($_REQUEST['quantity']) || !is_numeric($_REQUEST['quantity'])) {
			throw new Exception('Missing or invalid quantity', 400);
		}

		$product = reference::product($_REQUEST['product_id']);

		if ($product->stock_options && empty($_REQUEST['stock_option_id'])) {
			throw new Exception('Missing stock_option_id', 400);
		}

		$stock_option_id = isset($_REQUEST['stock_option_id']) ? $_REQUEST['stock_option_id'] : null;
		$userdata = isset($_REQUEST['userdata']) ? $_REQUEST['userdata'] : null;

		$unit_price = $product->calculate_price($_REQUEST['quantity'], $stock_option_id, $userdata);
		$tax = tax::calculate($unit_price, $product->tax_class_id);

		$result = [
			'product_id' => $product->id,
			'stock_option_id' => $stock_option_id,
			'quantity' => $_REQUEST['quantity'],
			'quantity_unit' => [
				'id' => $product->quantity_unit_id,
				'name' => $product->quantity_unit_id ? reference::quantity_unit($product->quantity_unit_id)->name : '',
			],
			'unit_price' => [
				'formatted' => currency::format($unit_price),
				'value' => currency::format_raw($unit_price),
			],
			'tax' => [
				'formatted' => currency::format($tax),
				'value' => currency::format_raw($tax),
			],
		];

	} catch (Exception $e) {
		http_response_code($e->getCode() ?: 500);
		$result = ['error' => $e->getMessage()];
	}

	ob_clean();
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	exit;
