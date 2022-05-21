<?php

  class ent_newsletter_recipient {
    public $data;
    public $previous;

    public function __construct($recipient_id=null) {

      if (!empty($recipient_id)) {
        $this->load($recipient_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."newsletter_recipients;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = database::create_variable($field['Type']);
      }

      $this->previous = $this->data;
    }

    public function load($recipient_id) {

      if (!preg_match('#(^[0-9]+$|@)#', $recipient_id)) throw new Exception('Invalid newsletter recipient (ID: '. $recipient_id .')');

      $this->reset();

      $recipient = database::fetch(database::query(
        "select * from ". DB_TABLE_PREFIX ."newsletter_recipients
        ". (preg_match('#^[0-9]+$#', $recipient_id) ? "where id = '". (int)$recipient_id ."'" : "") ."
        ". (preg_match('#@#', $recipient_id) ? "where lower(email) = '". database::input(strtolower($recipient_id)) ."'" : "") ."
        limit 1;"
      ));

      if ($recipient) {
        $this->data = array_replace($this->data, array_intersect_key($recipient, $this->data));
      } else {
        throw new Exception('Could not find newsletter recipient (ID: '. (int)$recipient_id .') in database.');
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."newsletter_recipients
          (email, date_created)
          values ('". database::input($this->data['email']) ."', '". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );

        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."newsletter_recipients
        set email = '". database::input($this->data['email']) ."',
          client_ip = '". database::input($this->data['client_ip']) ."'
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
