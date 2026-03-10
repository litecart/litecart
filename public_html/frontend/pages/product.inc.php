<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/product.inc.php
	 */

	if (empty($_GET['product_id'])) {
		include 'app://frontend/pages/error_document.inc.php';
		return;
	}

	$product = reference::product($_GET['product_id']);

	if (!$product->id) {
		http_response_code(410);
		include 'app://frontend/pages/error_document.inc.php';
		return;
	}

	if (!$product->status) {
		http_response_code(404);
		include 'app://frontend/pages/error_document.inc.php';
		return;
	}

	if ($product->valid_from && $product->valid_from > date('Y-m-d H:i:s')) {
		notices::add('errors', strtr(t('text_product_cannot_be_purchased_until_s', 'The product cannot be purchased until {date}}'), [
			'{date}' => f::datetime_format('date', $product->valid_from)
		]));
	}

	if ($product->valid_to && $product->valid_to < date('Y-m-d H:i:s')) {
		notices::add('errors', t('text_product_can_no_longer_be_purchased', 'The product can no longer be purchased'));
	}

	// Notify if product has been replaced by another product
	if (!empty($product->replaced_by)) {
		$replaced_product = reference::product($product->replaced_by);
		if (!empty($replaced_product->id)) {
			notices::add('info', strtr(t('text_product_replaced_by', 'This product has been replaced by <a href="{link}">{name}</a>.'), [
				'{link}' => document::ilink('product', ['product_id' => $replaced_product->id], true),
				'{name}' => $replaced_product->name,
			]));
		} else {
			notices::add('info', strtr(t('text_product_replaced_by_id', 'This product has been replaced by product {id}.'), [
				'{id}' => f::escape_html($product->replaced_by, ENT_QUOTES, 'UTF-8'),
			]));
		}
	}

	// Handle product notification signup
	if (!empty($_POST['notify_me'])) {
		try {

			if (empty($_POST['email'])) {
				throw new Exception(t('error_must_provide_email', 'You must provide an email address'));
			}

			if (!f::validate_email($_POST['email'])) {
				throw new Exception(t('error_invalid_email', 'The email address is invalid'));
			}

			database::query(
				"replace into ". DB_TABLE_PREFIX ."product_notification_recipients
				(product_id, email, language_code, created_at)
				values (". (int)$_GET['product_id'] .", '". database::input($_POST['email']) ."', '". database::input(language::$selected['code']) ."', '". date('Y-m-d H:i:s') ."')"
			);

			notices::add('success', t('success_notification_back_in_stock', 'We will notify you when the product is back in stock'));
			header('Location: '. $_SERVER['REQUEST_URI']);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

  if (empty(self::$data['is_bot'])) { // Needs an addon to detect bots
    database::query(
      "insert into ". DB_TABLE_PREFIX ."statistics
      (type, entity_type, entity_id, measure_group_type, measure_group_value, `count`)
      values ('product_views', 'product', ". (int)$product->id .", 'week', '". database::input(date('Y-W')) ."', 1)
      on duplicate key update
      `count` = `count` + 1;"
    );
  }

	customer::log([
		'type' => 'product_view',
		'description' => 'User viewed a product',
		'data' => [
			'product_id' => $product->id,
			'name' => $product->name,
		],
	]);

	document::$title[] = $product->head_title ?: $product->name;
	document::$description = $product->meta_description ?: strip_tags($product->short_description);

	document::$head_tags['canonical'] = '<link rel="canonical" href="'. document::href_ilink('product', ['product_id' => (int)$product->id], ['category_id']) .'">';

	if ($product->image) {
		document::$head_tags[] = '<meta property="og:image" content="'. document::href_rlink('storage://images/' . $product->image) .'">';
	}

	if (!empty($_GET['category_id'])) {

		breadcrumbs::add(t('title_categories', 'Categories'), document::ilink('categories'));

		foreach (reference::category($_GET['category_id'])->path as $category_crumb) {
			document::$title[] = $category_crumb->name;
			breadcrumbs::add($category_crumb->name, document::ilink('category', ['category_id' => $category_crumb->id]));
		}

	} else if ($product->default_category) {
		document::$title[] = $product->default_category->name;
		breadcrumbs::add(t('title_categories', 'Categories'), document::ilink('categories'));
		breadcrumbs::add($product->default_category->name, document::ilink('category', ['category_id' => $product->default_category->id]));

	} else if ($product->brand) {
		document::$title[] = $product->brand->name;
		breadcrumbs::add(t('title_brands', 'Brands'), document::ilink('brands'));
		breadcrumbs::add($product->brand->name, document::ilink('brand', ['brand_id' => $product->brand->id]));
	}

	breadcrumbs::add($product->name, document::ilink('product', ['product_id' => $product->id], ['category_id', 'brand_id']));

	// Recently viewed products
	if (isset(session::$data['recently_viewed_products'][$product->id])) {
		unset(session::$data['recently_viewed_products'][$product->id]);
	}

	if (empty(session::$data['recently_viewed_products']) || !is_array(session::$data['recently_viewed_products'])) {
		session::$data['recently_viewed_products'] = [];
	}

	session::$data['recently_viewed_products'][$product->id] = [
		'id' => $product->id,
		'name' => $product->name,
		'image' => $product->image ? 'storage://images/'.$product->image : '',
	];

	document::$schema = [
		'@context' => 'http://schema.org/',
		'@type' => 'Product',
		'productID' => $product->id,
		'sku' => $product->sku,
		'gtin14' => $product->gtin,
		'mpn' => $product->mpn,
		'name' => $product->name,
		'image' => $product->image ? 'storage://images/' . $product->image : '',
		'description' => (!empty($product->description) && (trim(strip_tags($product->description)) != '')) ? $product->description : '',
		'brand' => [],
		'offers' => [
			'@type' => 'Offer',
			'priceCurrency' => currency::$selected['code'],
			'price' => currency::format_raw(tax::get_price($product->final_price, $product->tax_class_id)),
			'priceValidUntil' => (!empty($product->campaign['end_date']) && strtotime($product->campaign['end_date']) > time()) ? $product->campaign['end_date'] : null,
			'itemCondition' => 'https://schema.org/NewCondition', // Or RefurbishedCondition, DamagedCondition, UsedCondition
			'availability' => ($product->quantity_available > 0) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
			'url' => document::link(),
		],
	];

	// Page
	if (is_ajax_request()) {
		$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/product.ajax.inc.php');
	} else {
		$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/product.inc.php');
	}

	$_page->snippets = [
		'product_id' => $product->id,
		'link' => document::ilink('product', [], true),
		'code' => $product->code,
		'sku' => $product->sku,
		'mpn' => $product->mpn,
		'gtin' => $product->gtin,
		'name' => $product->name,
		'short_description' => $product->short_description,
		'description' => $product->description,
		'technical_data' => preg_split('#\r\n?|\n#', $product->technical_data, -1, PREG_SPLIT_NO_EMPTY),
		'head_title' => $product->head_title ?: $product->name,
		'meta_description' => $product->meta_description ?: $product->short_description,
		'attributes' => $product->attributes,
		'customizations' => [],
		'stock_options' => [],
		'keywords' => $product->keywords,
		'image' => $product->images ? 'storage://images/' . $product->image : '',
		'video_url' => $product->video_url,
		'sticker' => '',
		'extra_images' => [],
		'main_category' => [],
		'category' => [],
		'brand' => [],
		'recommended_price' => tax::get_price($product->recommended_price, $product->tax_class_id),
		'regular_price' => $product->regular_price ? tax::get_price($product->regular_price, $product->tax_class_id) : null,
		'final_price' => $product->final_price ? tax::get_price($product->final_price, $product->tax_class_id) : null,
		'tax_class_id' => $product->tax_class_id,
		'including_tax' => !empty(customer::$data['display_prices_including_tax']),
		'total_tax' => $product->tax,
		'tax_rates' => [],
		'quantity_min' => ($product->quantity_min > 0) ? $product->quantity_min : 1,
		'quantity_max' => ($product->quantity_max > 0) ? $product->quantity_max : null,
		'quantity_step' => ($product->quantity_step > 0) ? $product->quantity_step : null,
		'quantity_unit' => $product->quantity_unit,
		'quantity_available' => $product->quantity_available,
		'quantity_reserved' => $product->quantity_reserved,
		'stock_status' => null,
		'delivery_status' => $product->delivery_status ?: [],
		'sold_out_status' => $product->sold_out_status ?: [],
		'orderable' => !empty($product->sold_out_status['orderable']),
		'cheapest_shipping_fee' => null,
	];

	// Extra Images
	foreach (array_slice(array_values($product->images), 1) as $image) {
		$_page->snippets['extra_images'][] = 'storage://images/' . $image;
	}

	// Watermark Images
	if (settings::get('product_image_watermark') && $product->image) {
		$_page->snippets['image'] = f::image_process($product->image, ['watermark' => true]);
		foreach ($_page->snippets['extra_images'] as $image) {
			$_page->snippets['extra_images'][$key] = f::image_process($image, ['watermark' => true]);
		}
	}

	// Sticker
	if (!empty($product->campaign['price']) && $product->price > 0 && $product->campaign['price'] > 0) {
		$percentage = round(($product->price - $product->campaign['price']) / $product->price * 100);
		$_page->snippets['sticker'] = '<div class="sticker sale">'. t('sticker_sale', 'Sale') .' -'. $percentage .'%</div>';
	} else if ($product->created_at > date('Y-m-d', strtotime('-'.settings::get('new_products_max_age')))) {
		$_page->snippets['sticker'] = '<div class="sticker new">'. t('sticker_new', 'New') .'</div>';
	}

	// Main Category
	if (!empty($category->id)) {
		$_page->snippets['main_category'] = [
			'id' => $category->main_category->id,
			'name' => $category->main_category->name,
			'image' => $category->main_category->image ? 'storage://images/' . $category->main_category->image : '',
			'link' => document::ilink('category', ['category_id' => $category->main_category->id]),
		];
	}

	// Category
	if (!empty($category->id)) {
		document::$schema['category'] = $category->name;
		$_page->snippets['category'] = [
			'id' => $category->id,
			'name' => $category->name,
			'image' => $category->image ? 'storage://images/' . $category->image : '',
			'link' => document::ilink('category', ['category_id' => $category->id]),
		];
	}

	// Brand
	if (!empty($product->brand)) {
		document::$schema['brand']['name'] = $product->brand->name;
		$_page->snippets['brand'] = [
			'id' => $product->brand->id,
			'name' => $product->brand->name,
			'image' => $product->brand->image ? 'storage://images/' . $product->brand->image : '',
			'link' => document::ilink('brand', ['brand_id' => $product->brand->id]),
		];
	}

	// Customizations
	foreach ($product->customizations as $group) {

		$values = '';

		switch ($group['function']) {

			case 'checkbox':

				foreach ($group['values'] as $value) {

					$price_adjustment_text = '';
					$price_adjustment = currency::format_raw(tax::get_price($value['price_adjustment'], $product->tax_class_id));
					$tax_adjust = currency::format_raw(tax::get_tax($value['price_adjustment'], $product->tax_class_id));

					if ($value['price_adjustment']) {

						if ($value['price_adjustment'] > 0) {
							$price_adjustment_text = ' +';
						} else if ($value['price_adjustment'] < 0) {
							$price_adjustment_text = ' -';
						}

						$price_adjustment_text .= currency::format(tax::get_price(abs($value['price_adjustment']), $product->tax_class_id));
					}

					$values .= f::form_checkbox('customizations['.$group['name'].'][]', [$value['name'], $value['name'] . $price_adjustment_text], true, 'data-group-id="'. (int)$group['group_id'] .'" data-value-id="'. (int)$value['value_id'] .'" data-price-adjust="'. (float)$price_adjustment .'" data-tax-adjust="'. (float)$tax_adjust .'"' . (!empty($group['required']) ? ' required' : ''));
				}

				break;

			case 'radio':

				foreach ($group['values'] as $value) {

					$price_adjustment_text = '';
					$price_adjustment = currency::format_raw(tax::get_price($value['price_adjustment'], $product->tax_class_id));
					$tax_adjust = currency::format_raw(tax::get_tax($value['price_adjustment'], $product->tax_class_id));

					if ($value['price_adjustment']) {

						if ($value['price_adjustment'] > 0) {
							$price_adjustment_text = ' +';
						} else if ($value['price_adjustment'] < 0) {
							$price_adjustment_text = ' -';
						}

						$price_adjustment_text .= currency::format(tax::get_price(abs($value['price_adjustment']), $product->tax_class_id));
					}

					$values .= implode(PHP_EOL, [
						'<div class="radio">',
						'  <label>'. f::form_radio_button('customizations['.$group['name'].']', $value['name'], true, 'data-group-id="'. (int)$group['group_id'] .'" data-value-id="'. (int)$value['value_id'] .'" data-price-adjust="'. (float)$price_adjustment .'" data-tax-adjust="'. (float)$tax_adjust .'"' . (!empty($group['required']) ? ' required' : '')) .' '. $value['name'] . $price_adjustment_text . '</label>',
						'</div>',
					]);
				}

				break;

			case 'select':

				$customizations = [['-- '. t('title_select', 'Select') .' --', '']];
				foreach ($group['values'] as $value) {

					$price_adjustment_text = '';
					$price_adjustment = currency::format_raw(tax::get_price($value['price_adjustment'], $product->tax_class_id));
					$tax_adjust = currency::format_raw(tax::get_tax($value['price_adjustment'], $product->tax_class_id));

					if ($value['price_adjustment']) {

						if ($value['price_adjustment'] > 0) {
							$price_adjustment_text = ' +';
						} else if ($value['price_adjustment'] < 0) {
							$price_adjustment_text = ' -';
						}

						$price_adjustment_text .= currency::format(tax::get_price(abs($value['price_adjustment']), $product->tax_class_id));
					}

					$customizations[] = [$value['name'] . $price_adjustment_text, $value['name'], 'data-value-id="'. (int)$value['value_id'] .'" data-price-adjust="'. (float)$price_adjustment .'" data-tax-adjust="'. (float)$tax_adjust .'"'];
				}

				$values .= f::form_select('customizations['.$group['name'].']', $customizations, true, 'data-group-id="'. (int)$group['group_id'] .'"'. (!empty($group['required']) ? ' required' : ''));
				break;

			case 'text':

				$values .= f::form_input_text('customizations['.$group['name'].']', true, 'data-group-id="'. (int)$group['group_id'] .'"' . (!empty($group['required']) ? ' required' : '')) . PHP_EOL;
				break;

			case 'textarea':

				$values .= f::form_textarea('customizations['.$group['name'].']', true, !empty($group['required']) ? 'data-group-id="'. (int)$group['group_id'] .'" required' : '') . PHP_EOL;
				break;
		}

		$_page->snippets['customizations'][] = [
			'id' => $group['id'],
			'group_id' => $group['group_id'],
			'name' => $group['name'],
			'required' => !empty($group['required']),
			'values' => $values,
		];
	}

	// Stock Options
	foreach ($product->stock_options as $stock_option) {
		$stock_option['image'] = $stock_option['image'] ? 'storage://images/' . $stock_option['image'] : '';
		$_page->snippets['stock_options'][] = $stock_option;
	}

	// Stock Status
	if (!empty($product->quantity_unit['name'])) {
		$_page->snippets['stock_status'] = settings::get('display_stock_count') ? language::number_format($product->quantity_available, $product->quantity_unit['decimals']) .' '. $product->quantity_unit['name'] : t('title_in_stock', 'In Stock');
	} else {
		$_page->snippets['stock_status'] = settings::get('display_stock_count') ? f::format_number($product->quantity_available) : t('title_in_stock', 'In Stock');
	}
f::format_number
	// Cheapest shipping
	if (settings::get('display_cheapest_shipping')) {

		$tmp_order = (object)[
			'data' => [
				'items' => [
					[
						'quantity' => 1,
						'product_id' => $product->id,
						'price' => $product->final_price,
						'tax' => tax::get_tax($product->final_price, $product->tax_class_id),
						'tax_class_id' => $product->tax_class_id,
						'weight' => $product->weight,
						'weight_unit' => $product->weight_unit,
						'length' => $product->length,
						'width' => $product->width,
						'height' => $product->height,
						'length_unit' => $product->length_unit,
					],
				],
				'subtotal' => $product->final_price,
				'subtotal_tax' => $product->tax,
				'customer' => customer::$data,
				'currency_code' => currency::$selected['code'],
			],
		];

		$cheapest_shipping = (new mod_shipping)->cheapest($tmp_order);

		if ($cheapest_shipping) {
			$_page->snippets['cheapest_shipping_fee'] = tax::get_price($cheapest_shipping['fee'], $cheapest_shipping['tax_class_id']);
		}
	}

	echo $_page;
