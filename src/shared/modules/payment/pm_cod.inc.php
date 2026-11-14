<?php

	class pm_cod extends abs_module {

		public $name = 'Cash on Delivery';
		public $description = '';
		public $author = 'LiteCart Dev Team';
		public $version = '1.0';
		public $website = 'https://www.litecart.net';
		public $priority = 0;

		public function __construct() {
			$this->name = t(__CLASS__.':title_cash_on_delivery', 'Cash on Delivery');
			parent::__construct();
		}

		public function options(array $items, float $subtotal, float $tax, string $currency_code, array $customer): ?array {

			if (empty($this->settings['status'])) return null;

			if (!empty($this->settings['geo_zones'])) {
				if (!reference::country($customer['shipping_address']['country_code'])->in_geo_zone($this->settings['geo_zones'], $customer['shipping_address'])) return null;
			}

			return [
				[
					'id' => 'cod',
					'icon' => $this->settings['icon'],
					'name' => $this->name,
					'description' => '',
					'fields' => '',
					'fee' => $this->settings['fee'],
					'tax_class_id' => $this->settings['tax_class_id'],
					'confirm' => t(__CLASS__.':title_confirm_order', 'Confirm Order'),
				],
			];
		}

		public function transfer(ent_order $order, string $success_url, string $cancel_url): array {
			return [
				'action' => '', // Target URL
				'method' => '', // GET, POST
				'fields' => [], // Form data
			];
		}

		public function verify(ent_order $order): array {

			// Require an explicit confirmation intent from the checkout form
			$confirmation = $_POST['confirm_purchase'] ?? '';
			if ($confirmation === '') $confirmation = session::$data['checkout']['confirmed'] ?? '';

			if (!is_string($confirmation) || $confirmation === '') {
				return [
					'error' => t('error_payment_confirmation_required', 'The order confirmation is missing or invalid. Please place the order again.'),
				];
			}

			// Require a valid CSRF token when CSRF protection is enabled
			if (settings::get('csrf_protection')) {

				$submitted_token = $_POST['csrf_token'] ?? '';
				if ($submitted_token === '') $submitted_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
				if ($submitted_token === '') $submitted_token = session::$data['checkout']['confirm_token'] ?? '';

				if (!security::is_valid_csrf_token($submitted_token)) {
					return [
						'error' => t('error_csrf_token_mismatch', 'CSRF token mismatch. Please go back and try again.'),
					];
				}
			}

			return [
				'order_status_id' => $this->settings['order_status_id'],
				'payment_transaction_id' => '',
				'errors' => '',
			];
		}

		public function settings(): array {
			return [
				[
					'key' => 'status',
					'default_value' => '1',
					'title' => t(__CLASS__.':title_status', 'Status'),
					'description' => t(__CLASS__.':description_status', 'Enables or disables the module.'),
					'function' => 'toggle("e/d")',
				],
				[
					'key' => 'icon',
					'default_value' => '',
					'title' => t(__CLASS__.':title_icon', 'Icon'),
					'description' => t(__CLASS__.':description_icon', 'Path to an image to be displayed.'),
					'function' => 'text()',
				],
				[
					'key' => 'fee',
					'default_value' => '0',
					'title' => t(__CLASS__.':title_payment_fee', 'Payment Fee'),
					'description' => t(__CLASS__.':description_payment_fee', 'Adds a payment fee to the order.'),
					'function' => 'decimal()',
				],
				[
					'key' => 'tax_class_id',
					'default_value' => '',
					'title' => t(__CLASS__.':title_tax_class', 'Tax Class'),
					'description' => t(__CLASS__.':description_tax_class', 'The tax class for the fee.'),
					'function' => 'tax_class()',
				],
				[
					'key' => 'order_status_id',
					'default_value' => '0',
					'title' => t('title_order_status', 'Order Status'),
					'description' => t('modules:description_order_status', 'Give orders made with this payment method the following order status.'),
					'function' => 'order_status()',
				],
				[
					'key' => 'geo_zones[]',
					'default_value' => '',
					'title' => t('title_geo_zone_limitation', 'Geo Zone Limitation'),
					'description' => t('modules:description_geo_zone', 'Limit this module to the selected geo zone. Otherwise, leave it blank.'),
					'function' => 'geo_zone()',
				],
				[
					'key' => 'priority',
					'default_value' => '0',
					'title' => t('title_priority', 'Priority'),
					'description' => t('modules:description_priority', 'Process this module in the given priority order.'),
					'function' => 'number()',
				],
			];
		}
	}
