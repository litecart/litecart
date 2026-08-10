<?php

	/*
		This file contains PHP logic that is separated from the HTML view.
		Visual changes can be made to the file found in the template folder:
		- frontend/templates/default/pages/checkout/customer.inc.php
	*/

	try {

		if (empty(session::$data['checkout_order'])) {
			throw new Exception('No order in session');
		}

		$order = new ent_order();
		$order->data = &session::$data['checkout_order'];

		if (!$_POST) {
			$_POST['customer'] = $order->data['customer'];
		}

	} catch (Exception $e) {
		redirect(document::ilink('checkout/init', ['redirect_url' => document::ilink('checkout/customer')]));
		exit;
	}

	if (isset($_POST['save'])) {

		foreach ([
			'type',
			'company',
			'firstname',
			'lastname',
			'address1',
			'address2',
			'postcode',
			'city',
			'country_code',
			'zone_code',
			'phone',
			'email',
		] as $field) {
			if (isset($_POST['customer'][$field])) {
				$order->data['customer'][$field] = $_POST['customer'][$field];
			}
		}

		$order->data['customer']['different_shipping_address'] = !empty($_POST['customer']['different_shipping_address']) ? 1 : 0;

		foreach ([
			'type',
			'company',
			'firstname',
			'lastname',
			'address1',
			'address2',
			'postcode',
			'city',
			'country_code',
			'zone_code',
			'phone',
			'email',
		] as $field) {
			if (!empty($_POST['customer']['different_shipping_address'])) {
				$order->data['customer']['shipping_address'][$field] = $_POST['customer']['shipping_address'][$field] ?? '';
			} else {
				$order->data['customer']['shipping_address'][$field] = $order->data['customer'][$field];
			}
		}

		// Save details to customer account
		if (!empty($_POST['save_details'])) {

			if (!empty(customer::$data['id'])) {
				$customer = new ent_customer(customer::$data['id']);
			} else {
				$customer = new ent_customer();
			}

			$customer->data = array_update($customer->data, $order->data['customer']);

			$customer->save();
		}

		notices::add('success', t('success_customer_details_updated', 'Customer details updated successfully'));
		redirect(document::ilink('checkout/index'));
		exit;
	}

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/checkout/customer.inc.php');
	$_page->snippets['customer'] = $order->data['customer'];

	echo $_page->render();
