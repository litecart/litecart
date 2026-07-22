<?php

	class ent_language {
		public $data;
		public $previous;

		public function __construct(string|null $language_code = null) {

			if ($language_code) {
				$this->load($language_code);
			} else {
				$this->reset();
			}
		}

		public function reset(): void {

			$this->data = [];

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."languages;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			$this->data['direction'] = 'ltr';
			$this->data['url_type'] = 'path';

			$this->previous = $this->data;
		}

		public function load(string $language_code): void {

			if (!preg_match('#^(\d+|[a-z]{2,3}|[a-z A-Z]{4,})$#', $language_code)) {
				throw new Exception('Invalid language ('. $language_code .')');
			}

			$this->reset();

			$language = database::query(
				"select * from ". DB_TABLE_PREFIX ."languages
				". (preg_match('#^\d+$#', $language_code) ? "where id = ". (int)$language_code : "") ."
				". (preg_match('#^[a-z]{2}$#', $language_code) ? "where code = '". database::input($language_code) ."'" : "") ."
				". (preg_match('#^[a-z]{3}$#', $language_code) ? "where code2 = '". database::input($language_code) ."'" : "") ."
				". (preg_match('#^[a-z A-Z]{4,}$#', $language_code) ? "where name like '". addcslashes(database::input($language_code), '%_') ."'" : "") ."
				limit 1;"
			)->fetch();

			if (!$language) {
				throw new Exception('Could not find language ('. f::escape_html($language_code) .') in database.');
			}

			$this->data = f::array_update($this->data, $language);

			$this->previous = $this->data;
		}

		public function save(): void {

			if (!$this->data['status'] && $this->data['code'] == settings::get('default_language_code')) {
				throw new Exception(t('error_cannot_disable_default_language', 'You must change the default language before disabling it.'));
			}

			if (!$this->data['status'] && $this->data['code'] == settings::get('store_language_code')) {
				throw new Exception(t('error_cannot_disable_store_language', 'You must change the store language before disabling it.'));
			}

			if (database::query(
				"select id from ". DB_TABLE_PREFIX ."languages
				where (
					code = '". database::input($this->data['code']) ."'
					". (!empty($this->data['code2']) ? "or code2 = '". database::input($this->data['code2']) ."'" : "") ."
				)
				". (!empty($this->data['id']) ? "and id != ". (int)$this->data['id'] : "") ."
				limit 1;"
			)->num_rows) {
				throw new Exception(strtr(t('error_language_conflict', 'The language code ({code} or {code2}) conflicts with another language in the database'), [
					'{code}' => $this->data['code'],
					'{code2}' => $this->data['code2'],
				]));
			}

			if (!$this->data['id']) {

			database::query(
					"insert into ". DB_TABLE_PREFIX ."languages
					(code, code2, created_at)
					values ('". database::input($this->data['code']) ."', '". database::input($this->data['code2']) ."', '". ($this->data['created_at'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."languages
				set status = ". (int)$this->data['status'] .",
					code = '". database::input($this->data['code']) ."',
					code2 = '". database::input($this->data['code2']) ."',
					name = '". database::input($this->data['name']) ."',
					direction = '". database::input($this->data['direction']) ."',
					locale = '". database::input($this->data['locale']) ."',
					locale_intl = '". database::input($this->data['locale_intl']) ."',
					mysql_collation = '". database::input($this->data['mysql_collation']) ."',
					url_type = '". database::input($this->data['url_type']) ."',
					domain_name = '". database::input($this->data['domain_name']) ."',
					raw_date = '". database::input($this->data['raw_date']) ."',
					raw_time = '". database::input($this->data['raw_time']) ."',
					raw_datetime = '". database::input($this->data['raw_datetime']) ."',
					format_date = '". database::input($this->data['format_date']) ."',
					format_time = '". database::input($this->data['format_time']) ."',
					format_datetime = '". database::input($this->data['format_datetime']) ."',
					decimal_point = '". database::input($this->data['decimal_point']) ."',
					thousands_sep = '". database::input($this->data['thousands_sep'], false, false) ."',
					auto_translate = ". (!empty($this->data['auto_translate']) ? "1" : "0") .",
					priority = ". (int)$this->data['priority'] .",
					updated_at = '". ($this->data['updated_at'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			if (!empty($this->previous['code']) && $this->data['code'] != $this->previous['code']) {

				if ($this->previous['code'] == 'en') {
					throw new Exception('You cannot not rename the english language because it is used for the PHP framework.');
				}

				$previous_code = database::identifier($this->previous['code']);
				$language_code = database::identifier($this->data['code']);

				// Rename language code in translations table
				database::query(
					"update ". DB_TABLE_PREFIX ."translations
					set `text` = if(
							json_contains_path(`text`, 'one', '$.". database::input($previous_code) ."'),
							json_set(json_remove(`text`, '$.". database::input($previous_code) ."'),
							'$.". database::input($language_code) ."', json_value(`text`, '$.". database::input($previous_code) ."')
						), `text`);"
				);

				// Rename language code in entity collections

				$collections = include 'app://includes/collections.inc.php';

				foreach ($collections as $collection) {
					if (empty($collection['translatable'])) continue;

					$table = DB_TABLE_PREFIX . $collection['id'];

					foreach ($collection['translatable'] as $column) {
						database::query(
							"update `$table`
							set `{$column}` = if(
									json_contains_path(`{$column}`, 'one', '$.". database::input($previous_code) ."'),
									json_set(json_remove(`{$column}`, '$.". database::input($previous_code) ."'),
									'$.". database::input($language_code) ."', json_value(`{$column}`, '$.". database::input($previous_code) ."')
								), `{$column}`)
							limit 1;"
						);
					}
				}

			} else {

				$language_code = database::identifier($this->data['code']);

				// Add new language to entity collections

				$collections = include 'app://includes/collections.inc.php';

				foreach ($collections as $collection) {
					if (empty($collection['translatable'])) continue;

					$table = DB_TABLE_PREFIX . $collection['id'];

					foreach ($collection['translatable'] as $column) {
						database::query(
							"update `$table`
							set `{$column}` = json_set(ifnull(`{$column}`, '{}'), '$.". database::input($language_code) ."', '')"
						);
					}
				}
			}

			$this->previous = $this->data;

			cache::clear_cache('languages');
		}

		public function delete(): void {

			if ($this->data['code'] == 'en') {
				throw new Exception(t('error_cannot_delete_framework_language', 'You cannot delete the PHP framework language. But you can disable it.'));
			}

			if ($this->data['code'] == settings::get('default_language_code')) {
				throw new Exception(t('error_cannot_delete_default_language', 'You must change the default language before it can be deleted.'));
			}

			if ($this->data['code'] == settings::get('store_language_code')) {
				throw new Exception(t('error_cannot_delete_store_language', 'You must change the store language before it can be deleted.'));
			}

			database::query(
				"delete from ". DB_TABLE_PREFIX ."languages
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			// Drop the language key from every translation row.
			$language_code = database::identifier($this->data['code']);

			database::query(
				"update ". DB_TABLE_PREFIX ."translations
				set `text` = json_remove(`text`, '$.". database::input($language_code) ."');"
			);

			$collections = include 'app://includes/collections.inc.php';

			foreach ($collections as $collection) {
				if (empty($collection['translatable'])) continue;

				$table = DB_TABLE_PREFIX . $collection['id'];

				foreach ($collection['translatable'] as $column) {
					database::query(
						"update `$table`
						set `{$column}` = json_remove(`{$column}`, '$.". database::input($language_code) ."');"
					);
				}
			}

			$this->reset();

			cache::clear_cache('languages');
		}
	}
