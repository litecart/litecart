<?php

	function webhook_send(string $event, array $data): void {

		try {

			database::query(
				"select * from ". DB_PREFIX ."webhooks
				where status
				and event = '". database::input($event) ."';"
			)->each(function($webhook) use ($event, $data) {

				$headers = [
					'Content-Type' => 'application/json;charset='. mb_http_output(),
				];

				$payload = f::format_json($data);

				$client = new http_client();
				$client->call('POST', $webhook['url'], $payload, $headers);

				$delivered = in_array((int)($client->last_response['status_code'] ?? 0), [200, 201, 202, 204, 304]);

				database::insert('webhook_requests', [
					'type' => 'outgoing',
					'status' => $delivered ? 'delivered' : 'failed',
					'webhook_id' => (int)$webhook['id'],
					'method' => 'POST',
					'url' => $webhook['url'],
					'headers' => f::format_json($headers),
					'body' => $payload,
					'raw_request' => $client->last_request['headers'] . $client->last_request['body'],
					'raw_response' => ($client->last_response['headers'] ?? '') . ($client->last_response['body'] ?? ''),
					'last_attempt' => date('Y-m-d H:i:s'),
					'delivered_at' => $delivered ? date('Y-m-d H:i:s') : null,
					'updated_at' => date('Y-m-d H:i:s'),
					'created_at' => date('Y-m-d H:i:s'),
				], 'default', ['headers', 'body', 'raw_request', 'raw_response']);

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
