<?php

	class ent_newsletter_recipient {
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
				"show fields from ". DB_TABLE_PREFIX ."newsletter_recipients;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			$this->data['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
			$this->data['hostname'] = gethostbyaddr($_SERVER['REMOTE_ADDR'] ?? '');
			$this->data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

			$this->previous = $this->data;
		}

		public function load($id) {

			if (!preg_match('#(^\d+$|@)#', $id)) {
				throw new Exception('Invalid newsletter recipient (ID: '. $id .')');
			}

			$this->reset();

			$recipient = database::query(
				"select * from ". DB_TABLE_PREFIX ."newsletter_recipients
				". (preg_match('#^\d+$#', $id) ? "where id = ". (int)$id ."" : "") ."
				". (preg_match('#@#', $id) ? "where email = '". database::input(strtolower($id)) ."'" : "") ."
				limit 1;"
			)->fetch();

			if (!$recipient) {
				throw new Exception('Could not find newsletter recipient (ID: '. (int)$id .') in database.');
			}

			$this->data = array_replace($this->data, array_intersect_key($recipient, $this->data));

			$this->previous = $this->data;
		}

		public function save() {

			if (!$this->data['id']) {
				database::query(
					"insert into ". DB_TABLE_PREFIX ."newsletter_recipients
					(email, created_at)
					values ('". database::input($this->data['email']) ."', '". ($this->data['created_at'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."newsletter_recipients
				set subscribed = ". (!empty($this->data['subscribed']) ? 1 : 0) .",
					firstname = '". database::input($this->data['firstname']) ."',
					lastname = '". database::input($this->data['lastname']) ."',
					email = '". database::input($this->data['email']) ."',
					language_code = ". (!empty($this->data['language_code']) ? "'". database::input($this->data['language_code']) ."'" : "null") .",
					country_code = ". (!empty($this->data['country_code']) ? "'". database::input($this->data['country_code']) ."'" : "null") .",
					ip_address = '". database::input($this->data['ip_address']) ."',
					hostname = '". database::input($this->data['hostname']) ."',
					user_agent = '". database::input($this->data['user_agent']) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->previous = $this->data;

			cache::clear_cache('newsletter_recipients');
		}

		public function delete() {

			database::query(
				"delete from ". DB_TABLE_PREFIX ."newsletter_recipients
				where id = ". (int)$this->data['id'] .";"
			);

			$this->reset();

			cache::clear_cache('newsletter_recipients');
		}
	}
