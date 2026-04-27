<?php

	class ent_webhook {
		public $data;
		public $previous;

		public function __construct($webhook_id=null) {

			if ($webhook_id) {
				$this->load((int)$webhook_id);
			} else {
				$this->reset();
			}
		}

		public function reset() {

			$this->data = [];

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."webhooks;"
			)->each(function($field) {
				$this->data[$field['Field']] = database::create_variable($field['Type']);
			});

			$this->previous = $this->data;
		}

		public function load($webhook_id) {

			if (!preg_match('#^[0-9]+$#', $webhook_id)) {
				throw new Exception('Invalid webhook (ID: '. $webhook_id .')');
			}

			$this->reset();

			$webhook = database::query(
				"select * from ". DB_TABLE_PREFIX ."webhooks
				where id = ". (int)$webhook_id ."
				limit 1;"
			)->fetch();

			if (!$webhook) {
				throw new Exception('Could not find webhook (ID: '. (int)$webhook_id .') in database.');
			}

			$this->data = f::array_update($this->data, $webhook);

			$this->previous = $this->data;
		}

		public function save() {

			if (empty($this->data['id'])) {

				database::query(
					"insert into ". DB_TABLE_PREFIX ."webhooks
					(created_at)
					values ('". date('Y-m-d H:i:s') ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."webhooks
				set status = ". (int)$this->data['status'] .",
					event = '". database::input($this->data['event']) ."',
					url = '". database::input($this->data['url']) ."',
					updated_at = '". ($this->data['updated_at'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->previous = $this->data;

			cache::clear_cache('webhooks');
		}

		public function delete() {

			database::query(
				"delete from ". DB_TABLE_PREFIX ."webhooks
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->reset();

			cache::clear_cache('webhooks');
		}
	}
