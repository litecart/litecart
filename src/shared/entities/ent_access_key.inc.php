<?php

	class ent_access_key {
		public $data;
		public $previous;

		public function __construct(int|null $access_key_id = null) {

			if ($access_key_id) {
				$this->load((int)$access_key_id);
			} else {
				$this->reset();
			}
		}

		public function reset(): void {

			$this->data = [];

			foreach (database::schema('access_keys') as $field) {
				$this->data[$field['Field']] = database::create_variable($field['Type']);
			}

			$this->data['permissions'] = [];

			$this->previous = $this->data;
		}

		public function load(int $access_key_id): void {

			if (!preg_match('#^[0-9]+$#', $access_key_id)) {
				throw new Exception('Invalid access key (ID: '. $access_key_id .')');
			}

			$this->reset();

			$access_key = database::query(
				"select * from ". DB_PREFIX ."access_keys
				where id = ". (int)$access_key_id ."
				limit 1;"
			)->fetch();

			if (!$access_key) {
				throw new Exception('Could not find access key (ID: '. $access_key_id .') in database.');
			}

			$this->data = f::array_update($this->data, $access_key);

			$this->data['permissions'] = $this->data['permissions'] ? json_decode($this->data['permissions'], true) : [];
			if (!is_array($this->data['permissions'])) {
				$this->data['permissions'] = [];
			}

			$this->previous = $this->data;
		}

		public function save(): void {

			$this->data['permissions'] = f::array_filter_recursive($this->data['permissions'] ?? []);

			if (empty($this->data['id'])) {

				// Insert with all values at once, a partial insert would commit an empty key_hash and violate the unique key
				database::insert('access_keys', [
					'label' => $this->data['label'],
					'key_hash' => $this->data['key_hash'],
					'key_prefix' => $this->data['key_prefix'],
					'permissions' => f::format_json($this->data['permissions'] ?: []),
					'status' => (int)$this->data['status'],
					'created_at' => date('Y-m-d H:i:s'),
				]);

				$this->data['id'] = database::insert_id();

			} else {

				database::query(
					"update ". DB_PREFIX ."access_keys
					set label = '". database::input($this->data['label']) ."',
						key_hash = '". database::input($this->data['key_hash']) ."',
						key_prefix = '". database::input($this->data['key_prefix']) ."',
						permissions = '". database::input(f::format_json($this->data['permissions'] ?: [])) ."',
						status = ". (int)$this->data['status'] ."
					where id = ". (int)$this->data['id'] ."
					limit 1;"
				);
			}

			$this->previous = $this->data;

			cache::clear_cache('access_keys');
		}

		public function delete(): void {

			database::query(
				"delete from ". DB_PREFIX ."access_keys
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->reset();

			cache::clear_cache('access_keys');
		}

		// Generates a new access key and stores only its hash, the plaintext is returned once
		public static function generate(string $label): string {

			$key = 'lc_'. rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

			$access_key = new ent_access_key();
			$access_key->data['label'] = $label;
			$access_key->data['key_hash'] = hash('sha256', $key);
			$access_key->data['key_prefix'] = substr($key, 0, 12);
			$access_key->data['status'] = 1;
			$access_key->save();

			return $key;
		}

		// Rotates the key in place, the previous plaintext is invalidated immediately
		public function regenerate_key(): string {

			$key = 'lc_'. rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

			$this->data['key_hash'] = hash('sha256', $key);
			$this->data['key_prefix'] = substr($key, 0, 12);
			$this->save();

			return $key;
		}
	}