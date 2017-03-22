<?php

  class ctrl_language {
    public $data;

    public function __construct($language_code=null) {

      if ($language_code !== null) {
        $this->load($language_code);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = array();

      $fields_query = database::query(
        "show fields from ". DB_TABLE_LANGUAGES .";"
      );
      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = null;
      }
    }

    public function load($language_code) {

      $this->reset();

      if (!preg_match('#[a-z]{2}#', $language_code)) trigger_error('Invalid language code ('. $language_code .')', E_USER_ERROR);

      $language_query = database::query(
        "select * from ". DB_TABLE_LANGUAGES ."
        where code='". database::input($language_code) ."'
        limit 1;"
      );

      if ($language = database::fetch($language_query)) {
        $this->data = array_intersect_key(array_merge($this->data, $language), $this->data);
      } else {
        trigger_error('Could not find language (Code: '. htmlspecialchars($language_code) .') in database.', E_USER_ERROR);
      }
    }

    public function save() {

      if (empty($this->data['status']) && $this->data['code'] == settings::get('default_language_code')) {
        trigger_error('You cannot disable the default language.', E_USER_ERROR);
      }

      if (!empty($this->data['id'])) {
        $previous_language_query = database::query(
          "select * from ". DB_TABLE_LANGUAGES ."
          where id = '". (int)$this->data['id'] ."'
          limit 1;"
        );
        $previous_language = database::fetch($previous_language_query);

        if ($this->data['code'] != $previous_language['code']) {
          if ($previous_language['code'] == 'en') {
            trigger_error('You cannot not rename the english language because it is used for the PHP framework.', E_USER_ERROR);

          } else {
            database::query(
              "alter table ". DB_TABLE_TRANSLATIONS ."
              change `text_". database::input($previous_language['code']) ."` `text_". database::input($this->data['code']) ."` text not null;"
            );

            $info_tables = array(
              DB_TABLE_CATEGORIES_INFO,
              DB_TABLE_DELIVERY_STATUSES_INFO,
              DB_TABLE_MANUFACTURERS_INFO,
              DB_TABLE_OPTION_GROUPS_INFO,
              DB_TABLE_OPTION_VALUES_INFO,
              DB_TABLE_ORDER_STATUSES_INFO,
              DB_TABLE_PAGES_INFO,
              DB_TABLE_PRODUCT_GROUPS_INFO,
              DB_TABLE_PRODUCT_GROUPS_VALUES_INFO,
              DB_TABLE_PRODUCTS_INFO,
              DB_TABLE_QUANTITY_UNITS_INFO,
              DB_TABLE_SLIDES_INFO,
              DB_TABLE_SOLD_OUT_STATUSES_INFO,
            );

            foreach ($info_tables as $table) {
              database::query(
                "update ". $table ."
                set language_code = '". $this->data['code'] ."'
                where language_code = '". $previous_language['code'] ."';"
              );
            }
          }
        }
      }

      if (empty($this->data['id'])) {
        $languages_query = database::query(
          "select id from ". DB_TABLE_LANGUAGES ."
          where code = '". database::input($this->data['code']) ."'
          limit 1;"
        );

        if (database::num_rows($languages_query)) {
          trigger_error('Language already exists', E_USER_ERROR);
        }

        database::query(
          "insert into ". DB_TABLE_LANGUAGES ."
          (date_created)
          values ('". database::input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      $translations_query = database::query(
        "show fields from ". DB_TABLE_TRANSLATIONS ."
        where `Field` = 'text_". database::input($this->data['code']) ."';"
      );
      if (database::num_rows($translations_query) == 0) {
        database::query(
          "alter table ". DB_TABLE_TRANSLATIONS ."
          add `text_". database::input($this->data['code']) ."` text not null after text_en;"
        );
      }

      database::query(
        "update ". DB_TABLE_LANGUAGES ."
        set
          status = '". (int)$this->data['status'] ."',
          code = '". database::input($this->data['code']) ."',
          code2 = '". database::input($this->data['code2']) ."',
          name = '". database::input($this->data['name']) ."',
          charset = '". database::input($this->data['charset']) ."',
          locale = '". database::input($this->data['locale']) ."',
          raw_date = '". database::input($this->data['raw_date']) ."',
          raw_time = '". database::input($this->data['raw_time']) ."',
          raw_datetime = '". database::input($this->data['raw_datetime']) ."',
          format_date = '". database::input($this->data['format_date']) ."',
          format_time = '". database::input($this->data['format_time']) ."',
          format_datetime = '". database::input($this->data['format_datetime']) ."',
          decimal_point = '". database::input($this->data['decimal_point']) ."',
          thousands_sep = '". database::input($this->data['thousands_sep']) ."',
          currency_code = '". database::input($this->data['currency_code']) ."',
          priority = '". (int)$this->data['priority'] ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      cache::clear_cache('languages');
    }

    public function delete() {

      if ($this->data['code'] == 'en') {
        trigger_error('English is the PHP framework language and must not be deleted, but it can be disabled.', E_USER_ERROR);
        return;
      }

      if ($this->data['code'] == settings::get('default_language_code')) {
        trigger_error('Cannot delete the store default language', E_USER_ERROR);
        return;
      }

      database::query(
        "delete from ". DB_TABLE_LANGUAGES ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      $translations_query = database::query(
        "show fields from ". DB_TABLE_TRANSLATIONS ."
        where `Field` = 'text_". database::input($this->data['code']) ."';"
      );
      if (database::num_rows($translations_query) == 1) {
        database::query(
          "alter table ". DB_TABLE_TRANSLATIONS ."
          drop `text_". database::input($this->data['code']) ."`;"
        );
      }

      $info_tables = array(
        DB_TABLE_CATEGORIES_INFO,
        DB_TABLE_DELIVERY_STATUSES_INFO,
        DB_TABLE_MANUFACTURERS_INFO,
        DB_TABLE_OPTION_GROUPS_INFO,
        DB_TABLE_OPTION_VALUES_INFO,
        DB_TABLE_ORDER_STATUSES_INFO,
        DB_TABLE_PAGES_INFO,
        DB_TABLE_PRODUCT_GROUPS_INFO,
        DB_TABLE_PRODUCT_GROUPS_VALUES_INFO,
        DB_TABLE_PRODUCTS_INFO,
        DB_TABLE_QUANTITY_UNITS_INFO,
        DB_TABLE_SLIDES_INFO,
        DB_TABLE_SOLD_OUT_STATUSES_INFO,
      );
      foreach ($info_tables as $table) {
        database::query(
          "delete from ". $table ."
          where language_code = '". $this->data['code'] ."';"
        );
      }

      cache::clear_cache('languages');

      $this->data['id'] = null;
    }
  }
