<?php

	class ent_customer {
		public $data;
		public $previous;

		public function __construct(int|string|null $id = null) {

			if ($id) {
				$this->load($id);
			} else {
				$this->reset();
			}
		}

		public function reset(): void {

			$this->data = [];

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."customers;"
			)->each(function($field){
				if (preg_match('#^shipping_(.*)$#', $field['Field'], $matches)) {
					$this->data['shipping_address'][$matches[1]] = database::create_variable($field);
				} else {
					$this->data[$field['Field']] = database::create_variable($field);
				}
			});

			$this->data['status'] = 1;
			$this->data['group_id'] = settings::get('default_customer_group_id');
			$this->data['newsletter'] = 0;

			$this->previous = $this->data;
		}

		public function load(int|string $id): void {

			if (!preg_match('#(^\d+$|@)#', $id)) {
				throw new Exception('Invalid customer (ID: '. $id .')');
			}

			$this->reset();

			$customer = database::query(
				"select * from ". DB_TABLE_PREFIX ."customers
				". (preg_match('#^\d+$#', $id) ? "where id = ". (int)$id : "") ."
				". (preg_match('#@#', $id) ? "where email = '". database::input(strtolower($id)) ."'" : "") ."
				limit 1;"
			)->fetch(function($customer){

				foreach ($customer as $key => $value) {
					if (preg_match('#^shipping_(.*)$#', $key, $matches)) {
						unset($customer['shipping_'.$matches[1]]);
						$customer['shipping_address'][$matches[1]] = $value;
					}
				}

				if (!$this->data['different_shipping_address']) {

					foreach ($this->data['shipping_address'] as $key => $value) {
						$customer['shipping_address'][$key] = '';
					}

					$customer['shipping_address']['country_code'] = $customer['country_code'];
					$customer['shipping_address']['zone_code'] = $customer['zone_code'];
				}
			});

			if (!$customer) {
				throw new Exception('Could not find customer (ID: '. (int)$id .') in database.');
			}

			$this->data = array_replace($this->data, array_intersect_key($customer, $this->data));

			$this->data['newsletter'] = database::query(
				"select id from ". DB_TABLE_PREFIX ."newsletter_recipients
				where email = '". database::input($this->data['email']) ."'
				limit 1;"
			)->num_rows ? 1 : 0;

			$this->previous = $this->data;
		}

		public function save(): void {

			if (!$this->data['id']) {
				database::query(
					"insert into ". DB_TABLE_PREFIX ."customers
					(email, created_at)
					values ('". database::input($this->data['email']) ."', '". ($this->data['created_at'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();

				database::query(
					"update ". DB_TABLE_PREFIX ."orders
					set customer_id = ". (int)$this->data['id'] ."
					where customer_email = '". database::input(strtolower($this->data['email'])) ."'
					and customer_id = 0;"
				);
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."customers
				set code = '". database::input($this->data['code']) ."',
					status = '". (!empty($this->data['status']) ? '1' : '0') ."',
					group_id = ". (int)$this->data['group_id'] .",
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
					different_shipping_address = '". (!empty($this->data['different_shipping_address']) ? '1' : '0') ."',
					shipping_company = '". database::input($this->data['shipping_address']['company']) ."',
					shipping_firstname = '". database::input($this->data['shipping_address']['firstname']) ."',
					shipping_lastname = '". database::input($this->data['shipping_address']['lastname']) ."',
					shipping_address1 = '". database::input($this->data['shipping_address']['address1']) ."',
					shipping_address2 = '". database::input($this->data['shipping_address']['address2']) ."',
					shipping_postcode = '". database::input($this->data['shipping_address']['postcode']) ."',
					shipping_city = '". database::input($this->data['shipping_address']['city']) ."',
					shipping_country_code = '". database::input($this->data['shipping_address']['country_code']) ."',
					shipping_zone_code = '". database::input($this->data['shipping_address']['zone_code']) ."',
					shipping_phone = '". database::input($this->data['shipping_address']['phone']) ."',
					language_code = '". database::input($this->data['language_code']) ."',
					notes = '". database::input($this->data['notes']) ."',
					blocked_until = ". (!empty($this->data['blocked_until']) ? "'". database::input($this->data['blocked_until']) ."'" : "null") .",
					sessions_expiry = ". (!empty($this->data['sessions_expiry']) ? "'". database::input($this->data['sessions_expiry']) ."'" : "null") .",
					updated_at = '". ($this->data['updated_at'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			if (!empty($this->previous['email']) && $this->previous['email'] != $this->data['email']) {
				database::query(
					"update ". DB_TABLE_PREFIX ."newsletter_recipients
					set email = '". database::input(strtolower($this->data['email'])) ."',
						firstname = '". database::input($this->data['firstname']) ."',
						lastname = '". database::input($this->data['lastname']) ."'
					where email = '". database::input(strtolower($this->previous['email'])) ."';"
				);
			}

			if (!empty($this->data['newsletter'])) {
				database::query(
					"insert ignore into ". DB_TABLE_PREFIX ."newsletter_recipients
					(email, firstname, lastname, ip_address, hostname, user_agent, created_at)
					values ('". database::input(strtolower($this->data['email'])) ."', '". database::input($this->data['firstname']) ."', '". database::input($this->data['lastname']) ."', '". database::input($_SERVER['REMOTE_ADDR']) ."', '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."', '". database::input($_SERVER['HTTP_USER_AGENT']) ."', '". date('Y-m-d H:i:s') ."');"
				);
			} else if (!empty($this->previous['id'])) {
				database::query(
					"delete from ". DB_TABLE_PREFIX ."newsletter_recipients
					where email = '". database::input(strtolower($this->data['email'])) ."';"
				);
			}

			$customer_modules = new mod_customer();

			if (!empty($this->previous['id'])) {
				$customer_modules->update($this->data, $this->previous);
			} else {
				$customer_modules->update($this->data);
			}

			if (empty($this->previous['id'])) {
				f::webhook_send('customer:created', $this->data);
			} else {
				f::webhook_send('customer:updated', $this->data);
			}

			if ($this->previous['newsletter'] != $this->data['newsletter']) {
				if (!empty($this->data['newsletter'])) {
					f::webhook_send('newsletter:subscribed', $this->data);
				} else {
					f::webhook_send('newsletter:unsubscribed', $this->data);
				}
			}

			$this->previous = $this->data;

			cache::clear_cache('customers');
		}

		public function set_password(string $password): void {

			if (!$this->data['id']) {
				$this->save();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."customers
				set password_hash = '". database::input($this->data['password_hash'] = password_hash($password, PASSWORD_DEFAULT)) ."',
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			// Re-sync the full snapshot so a later save() does not roll back values that were changed
			// between set_password() and save() (e.g. sessions_expiry during the reset flow).
			$this->previous = $this->data;
		}

		public function send_email(string $type, array $aliases = []): void {

			if (empty($this->data['email'])) {
				throw new Exception(t('error_cannot_send_email_to_customer_without_email', 'Cannot send email to customer without an email address'));
			}

			$aliases = [
				'{store_name}' => settings::get('store_name'),
				'{store_link}' => document::ilink(''),
				'{firstname}' => $this->data['firstname'],
				'{lastname}' => $this->data['lastname'],
				'{email}' => $this->data['email'],
				...$aliases,
			];

			switch ($type) {

				case 'account_created':

					$subject = t('email_subject_customer_account_created', 'Customer Account Created');

					$message = strtr(t('email_account_created', implode("\r\n", [
						'Welcome {firstname} {lastname} to {store_name}!',
						'',
						'Your account has been created. You can now make purchases in our online store and keep track of history.',
						'',
						'Sign in using your email address {email}.',
						'{store_name}',
						'{store_link}',
					]), $aliases));

					break;

				case 'reset_password':

					$subject = t('email_subject_reset_password', 'Reset Password');

					$message = strtr(implode("\r\n", [
						t('email_body_reset_password_intro', "You recently requested to reset your password for {store_name}."),
						t('email_body_reset_password_ignore', "If you did not request a password reset, please ignore this email."),
						t('email_body_reset_password_instruction', "Visit the link below to reset your password:"),
						"",
						"{link}",
						"",
						t('email_body_reset_password_verification_code', "Verification Code: {token}")
					]), $aliases);

					break;

				case 'verification_code':

					$subject = t('email_subject_verification_code', 'Verification Code');

					$message = strtr(implode("\r\n", [
						t('email_body_verification_code_verification_code', "Verification Code: {code}")
					]), $aliases);

					break;

				default:
					throw new Exception(t('error_invalid_email_type', 'Invalid email type'));
			}

			(new ent_email())
				->add_recipient($this->data['email'], $this->data['firstname'] .' '. $this->data['lastname'])
				->set_subject($subject)
				->add_body($message)
				->send();
		}

		public function delete(): void {

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

			f::webhook_send('customer:deleted', $this->previous);

			$this->reset();

			cache::clear_cache('customers');
		}
	}
