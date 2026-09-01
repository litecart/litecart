<?php

	class ent_webhook_request {

		public $data;
		public $previous;

		public function __construct(int|null $request_id = null) {

			if ($request_id) {
				$this->load($request_id);
			} else {
				$this->reset();
			}
		}

		public function reset(): void {

			$this->data = [];

			foreach (database::schema(DB_PREFIX .'webhook_requests') as $field) {
				$this->data[$field['Field']] = database::create_variable($field['Type']);
			}

			$this->previous = $this->data;
		}

		public function load(int $request_id): void {

			if (!preg_match('#^[0-9]+$#', $request_id)) {
				throw new Exception('Invalid webhook request (ID: '. $request_id .')');
			}

			$this->reset();

			$request = database::query(
				"select * from ". DB_PREFIX ."webhook_requests
				where id = ". (int)$request_id ."
				limit 1;"
			)->fetch();

			if (!$request) {
				throw new Exception('Could not find webhook (ID: '. (int)$request_id .') in database.');
			}

			$this->data = array_update($this->data, $request);

			$this->previous = $this->data;
		}

		public function save(): void {

			if (!$this->data['id']) {

				database::query(
					"insert into ". DB_PREFIX ."webhook_requests
					(created_at)
					values ('". date('Y-m-d H:i:s') ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_PREFIX ."webhook_requests
				set status = '". database::input($this->data['status']) ."',
					method = '". database::input($this->data['method']) ."',
					url = '". database::input($this->data['url']) ."',
					headers = '". (!empty($this->data['headers']) ? database::input($this->data['headers']) : "") ."',
					body = ". (isset($this->data['body']) ? "'". database::input($this->data['body']) ."'" : "NULL") .",
					failed_attempts = ". (int)$this->data['failed_attempts'] .",
					raw_request = ". (isset($this->data['raw_request']) ? "'". database::input($this->data['raw_request']) ."'" : "NULL") .",
					raw_response = ". (isset($this->data['raw_response']) ? "'". database::input($this->data['raw_response']) ."'" : "NULL") .",
					last_attempt = ". (!empty($this->data['last_attempt']) ? "'". database::input($this->data['last_attempt']) ."'" : "NULL") .",
					delivered_at = ". (!empty($this->data['delivered_at']) ? "'". database::input($this->data['delivered_at']) ."'" : "NULL") .",
					scheduled_at = ". (!empty($this->data['scheduled_at']) ? "'". database::input($this->data['scheduled_at']) ."'" : "NULL") .",
					updated_at = '". ($this->data['updated_at'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->previous = $this->data;
		}

		public function delete(): void {

			database::query(
				"delete from ". DB_PREFIX ."webhook_requests
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->reset();
		}
	}
