<?php

  class ent_language {
    public $data;
    public $previous;

    public function __construct($language_code=null) {

      if ($language_code !== null) {
        $this->load($language_code);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."languages;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = null;
      }

      $this->previous = $this->data;
    }

    public function load($language_code) {

      if (!preg_match('#^([0-9]+|[a-z]{2,3})$#', $language_code)) throw new Exception('Invalid language code ('. $language_code .')');

      $this->reset();

      $language_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."languages
        ". (preg_match('#^[0-9]+$#', $language_code) ? "where id = '". (int)$language_code ."'" : "") ."
        ". (preg_match('#^[a-z]{2}$#', $language_code) ? "where code = '". database::input($language_code) ."'" : "") ."
        ". (preg_match('#^[a-z]{3}$#', $language_code) ? "where code2 = '". database::input($language_code) ."'" : "") ."
        limit 1;"
      );

      if ($language = database::fetch($language_query)) {
        $this->data = array_intersect_key(array_merge($this->data, $language), $this->data);
      } else {
        throw new Exception('Could not find language ('. htmlspecialchars($language_code) .') in database.');
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['status']) && $this->data['code'] == settings::get('default_language_code')) {
        throw new Exception(language::translate('error_cannot_disable_default_language', 'You must change the default language before disabling it.'));
      }

      if (empty($this->data['status']) && $this->data['code'] == settings::get('store_language_code')) {
        throw new Exception(language::translate('error_cannot_disable_store_language', 'You must change the store language before disabling it.'));
      }

      $language_query = database::query(
        "select id from ". DB_TABLE_PREFIX ."languages
        where (
          code = '". database::input($this->data['code']) ."'
          ". (!empty($this->data['code2']) ? "or code2 = '". database::input($this->data['code2']) ."'" : "") ."
        )
        ". (!empty($this->data['id']) ? "and id != ". $this->data['id'] : "") ."
        limit 1;"
      );

      if (database::num_rows($language_query)) {
        throw new Exception(language::translate('error_language_conflict', 'The language conflicts another language in the database'));
      }

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."languages
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."languages
        set
          status = ". (int)$this->data['status'] .",
          code = '". database::input($this->data['code']) ."',
          code2 = '". database::input($this->data['code2']) ."',
          name = '". database::input($this->data['name']) ."',
          charset = '". database::input($this->data['charset']) ."',
          locale = '". database::input($this->data['locale']) ."',
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
          currency_code = '". database::input($this->data['currency_code']) ."',
          priority = ". (int)$this->data['priority'] .",
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      if (!empty($this->previous['code'])) {
        if ($this->data['code'] != $this->previous['code']) {

          if ($this->previous['code'] == 'en') {
            throw new Exception('You cannot not rename the english language because it is used for the PHP framework.');

          } else {
            database::query(
              "alter table ". DB_TABLE_PREFIX ."translations
              change `text_". database::input($this->previous['code']) ."` `text_". database::input($this->data['code']) ."` text not null;"
            );

            $info_tables = [
              DB_TABLE_PREFIX . "attribute_groups_info",
              DB_TABLE_PREFIX . "attribute_values_info",
              DB_TABLE_PREFIX . "categories_info",
              DB_TABLE_PREFIX . "delivery_statuses_info",
              DB_TABLE_PREFIX . "brands_info",
              DB_TABLE_PREFIX . "order_statuses_info",
              DB_TABLE_PREFIX . "pages_info",
              DB_TABLE_PREFIX . "products_info",
              DB_TABLE_PREFIX . "quantity_units_info",
              DB_TABLE_PREFIX . "slides_info",
              DB_TABLE_PREFIX . "sold_out_statuses_info",
            ];

            foreach ($info_tables as $table) {
              database::query(
                "update ". $table ."
                set language_code = '". database::input($this->data['code']) ."'
                where language_code = '". database::input($this->previous['code']) ."';"
              );
            }
          }
        }

      } else {

        $translations_query = database::query(
          "show fields from ". DB_TABLE_PREFIX ."translations
          where `Field` = 'text_". database::input($this->data['code']) ."';"
        );

        if (!database::num_rows($translations_query)) {
          database::query(
            "alter table ". DB_TABLE_PREFIX ."translations
            add `text_". database::input($this->data['code']) ."` text not null after text_en;"
          );
        }
      }

      $this->previous = $this->data;

      cache::clear_cache('languages');
    }

    public function delete() {

      if ($this->data['code'] == 'en') {
        throw new Exception(language::translate('error_cannot_delete_framework_language', 'You cannot delete the PHP framework language. But you can disable it.'));
      }

      if ($this->data['code'] == settings::get('default_language_code')) {
        throw new Exception(language::translate('error_cannot_delete_default_language', 'You must change the default language before it can be deleted.'));
      }

      if ($this->data['code'] == settings::get('store_language_code')) {
        throw new Exception(language::translate('error_cannot_delete_store_language', 'You must change the store language before it can be deleted.'));
      }

      database::query(
        "delete from ". DB_TABLE_PREFIX ."languages
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $translations_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."translations
        where `Field` = 'text_". database::input($this->data['code']) ."';"
      );

      if (database::num_rows($translations_query) == 1) {
        database::query(
          "alter table ". DB_TABLE_PREFIX ."translations
          drop `text_". database::input($this->data['code']) ."`;"
        );
      }

      $info_tables = [
        DB_TABLE_PREFIX . "attribute_groups_info",
        DB_TABLE_PREFIX . "attribute_values_info",
        DB_TABLE_PREFIX . "categories_info",
        DB_TABLE_PREFIX . "delivery_statuses_info",
        DB_TABLE_PREFIX . "brands_info",
        DB_TABLE_PREFIX . "order_statuses_info",
        DB_TABLE_PREFIX . "pages_info",
        DB_TABLE_PREFIX . "products_info",
        DB_TABLE_PREFIX . "quantity_units_info",
        DB_TABLE_PREFIX . "slides_info",
        DB_TABLE_PREFIX . "sold_out_statuses_info",
      ];

      foreach ($info_tables as $table) {
        database::query(
          "delete from ". $table ."
          where language_code = '". database::input($this->data['code']) ."';"
        );
      }

      $this->reset();

      cache::clear_cache('languages');
    }
  }
