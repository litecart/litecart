<?php

	class pm_credit_account extends abs_module {

		public $name = 'Credit Account';
		public $description = '';
		public $author = 'LiteCart Dev Team';
		public $version = '1.0';
		public $website = 'https://www.litecart.net';
		public $priority = 0;

		public function __construct() {
			$this->name = t(__CLASS__.':title_credit_account', 'Credit Account');
			parent::__construct();
		}

		public function options(array $items, float $subtotal, float $tax, string $currency_code, array $customer): ?array {

			if (empty($this->settings['status'])) {
				return null;
			}

			// Credit is only granted to a signed in customer with a credit limit
			if (empty($customer['id'])) {
				return null;
			}

			$credit = $this->credit((int)$customer['id']);

			if ($credit['limit'] <= 0) {
				return null;
			}

			$available = $credit['limit'] - $credit['usage'];

			$error = ($available < ($subtotal + $tax)) ? t(__CLASS__.':error_not_enough_credit_available', 'Not enough credit available') : '';

			$summary = strtr(t(__CLASS__.':description_credit', 'Credit limit {limit}, used {used}, available {available}'), [
				'{limit}' => currency::format($credit['limit']),
				'{used}' => currency::format($credit['usage']),
				'{available}' => currency::format(max(0, $available)),
			]);

			return [
				[
					'id' => 'account',
					'icon' => $this->settings['icon'],
					'name' => $this->name,
					'description' => '',
					'fields' => '<small>'. f::escape_html($summary) .'</small>'
						. ($error ? '<br><small>'. f::escape_html($error) .'</small>' : ''),
					'fee' => $this->settings['fee'],
					'tax_class_id' => $this->settings['tax_class_id'],
					'error' => $error,
					'confirm' => t(__CLASS__.':title_confirm_order', 'Confirm Order'),
				],
			];
		}

		public function pre_check(ent_order $order): string {

			$customer_id = (int)($order->data['customer']['id'] ?? 0);

			if (!$customer_id) {
				return t(__CLASS__.':error_must_be_logged_in_to_use_option', 'You must be logged in to use this method');
			}

			$credit = $this->credit($customer_id, (int)$order->data['id']);

			if ($credit['limit'] <= 0) {
				return t(__CLASS__.':error_no_credit_limit', 'No credit limit is set for this customer');
			}

			if (($credit['limit'] - $credit['usage']) < (float)$order->data['total']) {
				return t(__CLASS__.':error_not_enough_credit_available', 'Not enough credit available');
			}

			return '';
		}

		public function transfer(ent_order $order, string $success_url, string $cancel_url): array {
			return [
				'action' => '', // Target URL
				'method' => '', // GET, POST
				'fields' => [], // Form data
			];
		}

		public function verify(ent_order $order): array {
			return [
				'order_status_id' => $this->settings['order_status_id'],
				'payment_transaction_id' => '### CREDIT ACCOUNT - Pending ###',
				'errors' => '',
			];
		}

		// Credit line for the customer, computed by the customer entity
		private function credit(int $customer_id, int|null $exclude_order_id=null): array {
			try {

				$customer = new ent_customer($customer_id);

				if ($exclude_order_id) {
					$usage = $customer->credit_usage($exclude_order_id);
				} else {
					$usage = (float)$customer->data['credit_usage'];
				}

				return [
					'limit' => (float)$customer->data['credit_limit'],
					'usage' => $usage,
				];

			} catch (Exception $e) {
				return ['limit' => 0, 'usage' => 0];
			}
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
					'key' => 'priority',
					'default_value' => '0',
					'title' => t('title_priority', 'Priority'),
					'description' => t('modules:description_priority', 'Process this module in the given priority order.'),
					'function' => 'number()',
				],
			];
		}
	}
