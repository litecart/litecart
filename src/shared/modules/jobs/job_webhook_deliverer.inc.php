<?php

	#[AllowDynamicProperties]
	class job_webhook_deliverer extends abs_module {

		public $id = __CLASS__;
		public $name = 'Webhook Deliverer';
		public $description = 'Deliver webhooks that are pending or waiting for retry attempts.';
		public $author = 'LiteCart Dev Team';
		public $version = '1.0';
		public $website = 'https://www.litecart.net';
		public $priority = 0;

		public function process(string $force, string $last_run): void {

			if (empty($this->settings['status'])) return;

			database::query(
				"delete from ". DB_PREFIX ."webhook_requests
				where created_at < '". date('Y-m-d H:i:s', strtotime('-1 month')) ."';"
			);

			$pending_requests = database::query(
				"select * from ". DB_PREFIX ."webhook_requests
				where (
					status = 'pending'
					or (status = 'pending_retry' and failed_attempts = 1 and last_attempt < '". date('Y-m-d H:i:s', strtotime('-1 hour')) ."')
					or (status = 'pending_retry' and failed_attempts = 2 and last_attempt < '". date('Y-m-d H:i:s', strtotime('-3 hours')) ."')
					or (status = 'pending_retry' and failed_attempts = 3 and last_attempt < '". date('Y-m-d H:i:s', strtotime('-6 hours')) ."')
					or (status = 'pending_retry' and failed_attempts = 4 and last_attempt < '". date('Y-m-d H:i:s', strtotime('-12 hours')) ."')
					or (status = 'pending_retry' and failed_attempts >= 5 and last_attempt < '". date('Y-m-d H:i:s', strtotime('-24 hours')) ."')
				)
				and (scheduled_at is null or scheduled_at < '". date('Y-m-d H:i:s') ."')
				order by created_at asc;"
			)->fetch_all(function(&$row) {
				$row['headers'] = $row['headers'] ? f::array_each(f::string_split($row['headers']), fn($header) => preg_split('#: ?#', $header, 2)) : [];
			});

			if (!$pending_requests) {
				echo 'No pending webhooks to deliver.' . PHP_EOL;
				return;
			}

			echo 'Found '. count($pending_requests) .' webhooks awaiting delivery' . PHP_EOL;

			foreach ($pending_requests as $request) {

				echo 'Delivering webhook request ID '. $request['id'] .' to '. $request['url'] . PHP_EOL;

				$response = (new http_client)->call($request['method'], $request['url'], $request['body'], $request['headers']);

				$request_log = implode(PHP_EOL, [
					$client->last_request['head'],
					$client->last_request['body'],
				]);

				$response_log = implode(PHP_EOL, [
					$client->last_response['head'],
					$client->last_response['body'],
				]);

				if (in_array($client->last_response['status_code'], [200, 201, 202, 204, 304])) {
					database::query(
						"update ". DB_PREFIX ."webhook_requests
						set status = 'delivered',
							last_attempt = '". date('Y-m-d H:i:s') ."',
							raw_request = '". database::input($request_log) ."',
							raw_response = '". database::input($response_log) ."',
							delivered_at = '". date('Y-m-d H:i:s') ."'
						where id = ". (int)$request['id'] .";"
					);
				} else if (substr($client->last_response['status_code'], 0, 1) == '4') {
					database::query(
						"update ". DB_PREFIX ."webhook_requests
						set status = 'failed',
							last_attempt = '". date('Y-m-d H:i:s') ."',
							raw_request = '". database::input($request_log) ."',
							raw_response = '". database::input($response_log) ."',
							delivered_at = '". date('Y-m-d H:i:s') ."',
							failed_attempts = failed_attempts + 1
						where id = ". (int)$request['id'] .";"
					);
				} else {
					database::query(
						"update ". DB_PREFIX ."webhook_requests
						set status = '". ($request['failed_attempts'] >= 10 ? 'failed' : 'pending_retry') ."',
							last_attempt = '". date('Y-m-d H:i:s') ."',
							raw_request = '". database::input($request_log) ."',
							raw_response = '". database::input($response_log) ."',
							failed_attempts = failed_attempts + 1
						where id = ". (int)$request['id'] .";"
					);
				}
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
					'key' => 'priority',
					'default_value' => '0',
					'title' => t(__CLASS__.':title_priority', 'Priority'),
					'description' => t(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
					'function' => 'number()',
				],
			];
		}
	}
