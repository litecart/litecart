<?php

	try {

		if (empty($_POST['options']) || !is_array($_POST['options'])) {
			throw new Exception('Missing options');
		}

		$_POST['options'] = array_filter($_POST['options']);

		$product = reference::product($_POST['product_id']);
		$selected_options = [];

		if (count($product->options) > 0) {
			foreach (array_keys($product->options) as $key) {

				if ($product->options[$key]['required'] != 0) {
					if (empty($_POST['options'][$product->options[$key]['name']])) {
						throw new Exception('Missing required option');
					}
				}

				if (!empty($_POST['options'][$product->options[$key]['name']])) {
					switch ($product->options[$key]['function']) {

						case 'checkbox':
							$valid_values = [];
							foreach ($product->options[$key]['values'] as $value) {
								$valid_values[] = $value['name'];
								if (in_array($value['name'], explode(', ', $_POST['options'][$product->options[$key]['name']]))) {
									$selected_options[] = $product->options[$key]['group_id'].'-'.$value['value_id'];
								}
							}

							foreach (explode(', ', $_POST['options'][$product->options[$key]['name']]) as $current_value) {
								if (!in_array($current_value, $valid_values)) {
									throw new Exception('Not a valid option');
								}
							}
							break;

						case 'input':
						case 'textarea':
							$values = array_values($product->options[$key]['values']);
							$value = array_shift($values);
							$selected_options[] = $product->options[$key]['group_id'].'-'.$value['value_id'];
							break;

						case 'radio':
						case 'select':

							$valid_values = [];
							foreach ($product->options[$key]['values'] as $value) {
								$valid_values[] = $value['name'];
								if ($value['name'] == $_POST['options'][$product->options[$key]['name']]) {
									$selected_options[] = $product->options[$key]['group_id'].'-'.$value['value_id'];
								}
							}

							if (!in_array($_POST['options'][$product->options[$key]['name']], $valid_values)) {
								throw new Exception('Not a valid option');
							}

							break;
					}
				}
			}
		}

		if (!empty($item['options'])) {
			foreach (array_keys($item['options']) as $key) {
				if (is_array($item['options'][$key])) {
					$item['options'][$key] = implode(', ', $item['options'][$key]);
				}
			}
		}

		if (!$product->options_stock) {
			throw new Exception('No stock options for this product');
		}

		// Match options with options stock
		foreach ($product->options_stock as $stock_option) {
			foreach (explode(',', $stock_option['combination']) as $pair) {
				if (!in_array($pair, $selected_options)) continue 2;
			}

			header('Content-Type: application/json');

			if ($stock_option['quantity'] > 0) {
				$notice = strtr(t('text_there_are_n_items_in_stock_for_option', 'There are {n} items remaining in stock for this option'), [
					'{n}' => (int)$stock_option['quantity'],
				]);
				echo functions::json_format(['status' => 'ok', 'notice' => $notice]);
				exit;

			} else if (empty($product->sold_out_status['orderable'])) {
				$notice = t('notice_option_out_of_stock', 'We are out of stock for this option');
				echo functions::json_format(['status' => 'warning', 'notice' => $notice]);
				exit;
			}

			header('Content-Type: application/json');
			$json = [
				'status' => 'ok',
				'notice' => $notice,
			];

			break;
		}

		throw new Exception('No stock defined for option');

	} catch (Exception $e) {
		$json = ['error' => $e->getMessage()];
	}

	header('Content-Type: application/json');
	echo functions::json_format($json);
	exit;
