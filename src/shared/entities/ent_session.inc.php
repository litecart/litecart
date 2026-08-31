<?php

	class ent_session {
		public $data;
		public $previous;

		public function __construct(string|null $id = null) {

			if ($id) {
				$this->load($id);
			} else {
				$this->reset();
			}
		}

		public function reset(): void {

			$this->data = [];

			database::query(
				"show fields from ". DB_PREFIX ."sessions;"
			)->each(function($field) {
				$this->data[$field['Field']] = database::create_variable($field['Type']);
			});

			$this->data['id'] = bin2hex(random_bytes(16));

			$this->previous = $this->data;
		}

		public function load(string $id): void {

			if (!preg_match('#^[0-9a-z]+$#i', $id)) {
				throw new Exception('Invalid session (ID: '. $id .')');
			}

			$this->reset();

			$session = database::query(
				"select * from ". DB_PREFIX ."sessions
				where id = '". database::input($id) ."'
				limit 1;"
			)->fetch(function($row){
				$row['data'] = $row['data'] ? json_decode($row['data'], true) : [];
			});

			if (!$session) {
				throw new Exception('Could not find session in database (ID: '. $id .')');
			}

			$this->data = f::array_update($this->data, $session);

			$this->previous = $this->data;
		}

		public function save(): void {

			if (!$this->data['id']) {

				$this->data['id'] = bin2hex(random_bytes(16));

				database::query(
					"insert into ". DB_PREFIX ."sessions
					(id, created_at)
					values ('". database::input($this->data['id']) ."', '". date('Y-m-d H:i:s') ."');"
				);
			}

			database::query(
				"update ". DB_PREFIX ."sessions
				set customer_id = ". (!empty($this->data['customer_id']) ? (int)$this->data['customer_id'] : "null") .",
					data = '". database::input(f::format_json($this->data['data'])) ."',
					ip_address = '". database::input($this->data['ip_address']) ."',
					hostname = '". database::input($this->data['hostname']) ."',
					user_agent = '". database::input($this->data['user_agent']) ."',
					data = '". database::input(f::format_json($this->data['data'])) ."',
					last_request = '". database::input($this->data['last_request']) ."',
					last_active = '". ($this->data['last_active'] = date('Y-m-d H:i:s')) ."',
					expires_at = '". ($this->data['expires_at'] = date('Y-m-d H:i:s', strtotime('+15 minutes'))) ."',
					updated_at = '". ($this->data['updated_at'] = date('Y-m-d H:i:s')) ."'
				where id = '". database::input($this->data['id']) ."'
				limit 1;"
			);
		}
	}
