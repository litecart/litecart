<?php

	class ent_customer {
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
				"show fields from ". DB_TABLE_PREFIX ."customers;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			$this->data['status'] = 1;
			$this->data['newsletter'] = '';

			$this->previous = $this->data;
		}

		public function load($id) {

			if (!preg_match('#(^[0-9]+$|@)#', $id)) {
				throw new Exception('Invalid customer (ID: '. $id .')');
			}

			$this->reset();

			$customer = database::query(
				"select * from ". DB_TABLE_PREFIX ."customers
				". (preg_match('#^[0-9]+$#', $id) ? "where id = ". (int)$id ."" : "") ."
				". (preg_match('#@#', $id) ? "where lower(email) = '". database::input(strtolower($id)) ."'" : "") ."
				limit 1;"
			)->fetch();

			if ($customer) {
				$this->data = array_replace($this->data, array_intersect_key($customer, $this->data));
			} else {
				throw new Exception('Could not find customer (ID: '. (int)$id .') in database.');
			}

			$this->data['newsletter'] = database::query(
				"select id from ". DB_TABLE_PREFIX ."newsletter_recipients
				where email = '". database::input($this->data['email']) ."'
				limit 1;"
			)->num_rows ? 1 : 0;

			$this->previous = $this->data;
		}

		public function save() {

			if (!$this->data['id']) {
				database::query(
					"insert into ". DB_TABLE_PREFIX ."customers
					(email, date_created)
					values ('". database::input($this->data['email']) ."', '". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();

				database::query(
					"update ". DB_TABLE_PREFIX ."orders
					set customer_id = ". (int)$this->data['id'] ."
					where lower(billing_email) = '". database::input(strtolower($this->data['email'])) ."'
					and customer_id = 0;"
				);
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."customers
				set code = '". database::input($this->data['code']) ."',
					status = '". (!empty($this->data['status']) ? '1' : '0') ."',
					email = '". database::input(strtolower($this->data['email'])) ."',
					tax_id = '". database::input($this->data['tax_id']) ."',
					company = '". database::input($this->data['company']) ."',
					firstname = '". database::input($this->data['firstname']) ."',
					lastname = '". database::input($this->data['lastname']) ."',
					address1 = '". database::input($this->data['address1']) ."',
					address2 = '". database::input($this->data['address2']) ."',
					postcode = '". database::input($this->data['postcode']) ."',
					city = '". database::input($this->data['city']) ."',
					country_code = '". database::input($this->data['country_code']) ."',
					zone_code = '". database::input($this->data['zone_code']) ."',
					phone = '". database::input($this->data['phone']) ."',
					default_billing_address_id = ". (int)$this->data['default_billing_address_id'] .",
					default_shipping_address_id = ". (int)$this->data['default_shipping_address_id'] .",
					notes = '". database::input($this->data['notes']) ."',
					password_reset_token = '". database::input($this->data['password_reset_token']) ."',
					date_blocked_until = ". (!empty($this->data['date_blocked_until']) ? "'". database::input($this->data['date_blocked_until']) ."'" : "null") .",
					date_expire_sessions = ". (!empty($this->data['date_expire_sessions']) ? "'". database::input($this->data['date_expire_sessions']) ."'" : "null") .",
					date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			if (!empty($this->previous['email']) && $this->previous['email'] != $this->data['email']) {
				database::query(
					"update ". DB_TABLE_PREFIX ."newsletter_recipients
					set email = '". database::input(strtolower($this->data['email'])) ."',
						firstname = '". database::input($this->data['firstname']) ."',
						lastname = '". database::input($this->data['lastname']) ."'
					where lower(email) = '". database::input(strtolower($this->previous['email'])) ."';"
				);
			}

			if (!empty($this->data['newsletter'])) {
				database::query(
					"insert ignore into ". DB_TABLE_PREFIX ."newsletter_recipients
					(email, firstname, lastname, ip_address, hostname, user_agent, date_created)
					values ('". database::input(strtolower($this->data['email'])) ."', '". database::input($this->data['firstname']) ."', '". database::input($this->data['lastname']) ."', '". database::input($_SERVER['REMOTE_ADDR']) ."', '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."', '". database::input($_SERVER['HTTP_USER_AGENT']) ."', '". date('Y-m-d H:i:s') ."');"
				);
			} else if (!empty($this->previous['id'])) {
				database::query(
					"delete from ". DB_TABLE_PREFIX ."newsletter_recipients
					where lower(email) = '". database::input(strtolower($this->data['email'])) ."';"
				);
			}

			$customer_modules = new mod_customer();

			if (!empty($this->previous['id'])) {
				$customer_modules->update($this->data, $this->previous);
			} else {
				$customer_modules->update($this->data);
			}

			$this->previous = $this->data;

			cache::clear_cache('customers');
		}

		public function set_password($password) {

			if (!$this->data['id']) {
				$this->save();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."customers
				set password_hash = '". database::input($this->data['password_hash'] = password_hash($password, PASSWORD_DEFAULT)) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->previous['password_hash'] = $this->data['password_hash'];
		}

		public function delete() {

			database::query(
				"update ". DB_TABLE_PREFIX ."orders
				set customer_id = 0
				where customer_id = ". (int)$this->data['id'] .";"
			);

			database::query(
				"delete c, nr
				from ". DB_TABLE_PREFIX ."customers c
				left join ". DB_TABLE_PREFIX ."newsletter_recipients nr on (nr.email = c.email)
				where c.id = ". (int)$this->data['id'] .";"
			);

			$customer_modules = new mod_customer();
			$customer_modules->delete($this->previous);

			$this->reset();

			cache::clear_cache('customers');
		}
	}
