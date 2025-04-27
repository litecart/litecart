<?php

	class ent_order_status {
		public $data;
		public $previous;

		public function __construct($id=null) {

			if ($id) {
				$this->load($id);
			} else {
				$this->reset();
			}
		}

		public function reset() {

			$this->data = [];

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."order_statuses;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			foreach ([
				'name',
				'description',
				'email_subject',
				'email_message',
			] as $column) {
				$this->data[$column] = array_fill_keys(array_keys(language::$languages), '');
			}

			$this->data['num_orders'] = 0;

			$this->previous = $this->data;
		}

		public function load($id) {

			if (!preg_match('#^[0-9]+$#', $id)) {
				throw new Exception('Invalid order status (ID: '. $id .')');
			}

			$this->reset();

			$order_status = database::query(
				"select * from ". DB_TABLE_PREFIX ."order_statuses
				where id = ". (int)$id ."
				limit 1;"
			)->fetch();

			if ($order_status) {
				$this->data = array_replace($this->data, array_intersect_key($order_status, $this->data));
			} else {
				throw new Exception('Could not find order_status (ID: '. (int)$id .') in database.');
			}

			foreach ([
				'name',
				'description',
				'email_subject',
				'email_message',
			] as $column) {
				$this->data[$column] = json_decode($this->data[$column], true) ?: [];
			}

			$this->data['num_orders'] = database::query(
				"select count(*) as num_orders
				from ". DB_TABLE_PREFIX ."orders
				where order_status_id = ". (int)$this->data['id'] .";"
			)->fetch('num_orders');

			$this->previous = $this->data;
		}

		public function save() {

			if ($this->data['num_orders'] && $this->data['stock_action'] != $this->previous['stock_action']) {
				throw new Exception(language::translate('error_cannot_change_stock_action_while_used_by_orders', 'You cannot change stock action while there are orders using this status'));
			}

			if (!$this->data['id']) {

				database::query(
					"insert into ". DB_TABLE_PREFIX ."order_statuses
					(created_at)
					values ('". ($this->data['created_at'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."order_statuses
				set hidden = '". (empty($this->data['hidden']) ? '0' : '1') ."',
					state = '". database::input($this->data['state']) ."',
					icon = '". database::input($this->data['icon']) ."',
					color = '". database::input($this->data['color']) ."',
					name = '". database::input(json_encode($this->data['name'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ."',
					description = '". database::input(json_encode($this->data['description'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ."',
					email_subject = '". database::input(json_encode($this->data['email_subject'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ."',
					email_message = '". database::input(json_encode($this->data['email_message'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ."',
					is_sale = '". (empty($this->data['is_sale']) ? '0' : '1') ."',
					is_archived = '". (empty($this->data['is_archived']) ? '0' : '1') ."',
					is_trackable = '". (empty($this->data['is_trackable']) ? '0' : '1') ."',
					stock_action = '". database::input($this->data['stock_action']) ."',
					notify = '". (empty($this->data['notify']) ? '0' : '1') ."',
					priority = ". (int)$this->data['priority'] .",
					updated_at = '". ($this->data['updated_at'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->previous = $this->data;

			cache::clear_cache('order_statuses');
		}

		public function delete() {

			if ($this->data['num_orders']) {
				throw new Exception(language::translate('error_cannot_delete_order_status_while_used', 'Cannot delete the order status while it is in use by orders'));
			}

			database::query(
				"delete os
				from ". DB_TABLE_PREFIX ."order_statuses os
				where os.id = ". (int)$this->data['id'] .";"
			);

			$this->reset();

			cache::clear_cache('order_statuses');
		}
	}
