<?php

	function webhook_send(string $event, array $data): void {

		try {

			database::query(
				"select * from ". DB_PREFIX ."webhooks
				where status
				and event = '". database::input($event) ."';"
			)->each(function($webhook) use ($event, $data) {

				$client = new wrap_http();

				$headers = [
					'Content-Type' => 'application/json;charset='. language::$selected['charset'],
				];

				$client->call('POST', $webhook['url'], f::format_json($data), $headers);

				database::query(
					"insert into ". DB_PREFIX ."webhook_requests
					(`type`, status, webhook_id, url, request, response, updated_at, created_at)
					values (
						'outgoing',
						'pending',
						". (int)$webhook['id'] .",
						'". database::input($event) ."',
						'". database::input($webhook['url']) ."',
						'". database::input($client->last_request['head'] . $client->last_request['body'], true) ."',
						'". database::input($client->last_response['head'] . $client->last_response['body'], true) ."',
						'". database::input(date('Y-m-d H:i:s')) ."',
						'". database::input(date('Y-m-d H:i:s')) ."'
					);"
				);

				database::query(
					"update ". DB_PREFIX ."webhooks
					set last_sent = '". database::input(date('Y-m-d H:i:s')) ."'
					where id = ". (int)$webhook['id'] ."
					limit 1;"
				);
			});

		} catch (Throwable $e) {
			trigger_error('Webhook event failed: '. $e->getMessage(), E_USER_WARNING);
		}
	}

