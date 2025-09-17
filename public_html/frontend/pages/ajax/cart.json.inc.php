<?php

	try {

		$result = [
			'items' => [],
			'num_items' => cart::$total['num_items'],
			'total_value' => !empty(customer::$data['display_prices_including_tax']) ? cart::$total['subtotal'] + cart::$total['subtotal_tax'] : cart::$total['subtotal'],
			'formatted_total_value' => !empty(customer::$data['display_prices_including_tax']) ? currency::format(cart::$total['subtotal'] + cart::$total['subtotal_tax']) : currency::format(cart::$total['subtotal']),
			'text_total' => t('title_total', 'Total'),
		];

		foreach (cart::$items as $key => $item) {
			$result['items'][] = [
				'key' => $key,
				'product_id' => $item['product_id'],
				'stock_option_id' => $item['stock_option_id'],
				'userdata' => $item['userdata'],
				'link' => document::ilink('product', ['product_id' => $item['product_id']]),
				'image' => [
					'original' => document::href_rlink('storage://images/'. ($item['image'] ?  $item['image'] : 'no_image.svg')),
					'thumbnail' => document::href_rlink(functions::image_thumbnail('storage://images/'. ($item['image'] ?  $item['image'] : 'no_image.svg'), 64, 0, 'product')),
					'thumbnail_2x' => document::href_rlink(functions::image_thumbnail('storage://images/'. ($item['image'] ?  $item['image'] : 'no_image.svg'), 128, 0, 'product')),
				],
				'name' => $item['name'],
				'code' => $item['code'],
				'sku' => $item['sku'],
				'gtin' => $item['gtin'],
				'taric' => $item['taric'],
				'price' => !empty(customer::$data['display_prices_including_tax']) ? $item['price'] + $item['tax']: $item['price'],
				'formatted_price' => currency::format(!empty(customer::$data['display_prices_including_tax']) ? $item['price'] + $item['tax'] : $item['price']),
				'tax' => $item['tax'],
				'tax_class_id' => $item['tax_class_id'],
				'quantity' => $item['quantity'],
				'quantity_unit' => [
					'id' => $item['quantity_unit_id'],
					'name' => $item['quantity_unit_id'] ? reference::quantity_unit($item['quantity_unit_id'])->name : '',
				],
			];
		}

		if (!empty(notices::$data['warnings'])) {
			$warnings = array_values(notices::$data['warnings']);
			$result['alert'] = array_shift($warnings);
		}

		if (!empty(notices::$data['errors'])) {
			$errors = array_values(notices::$data['errors']);
			$result['alert'] = array_shift($errors);
		}

		notices::reset();

	} catch (Exception $e) {
		http_response_code($e->getCode() ?: 500);
		$result = ['error' => $e->getMessage()];
	}

	ob_clean();
	header('Content-type: application/json; charset='. mb_http_output());
	echo functions::json_format($result);
	exit;
