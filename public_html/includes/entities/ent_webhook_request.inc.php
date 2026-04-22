<?php

  class ent_webhook_request {

    public $data;
    public $previous;

    public function __construct($request_id=null) {

      if ($request_id) {
        $this->load($request_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      database::query(
        "show fields from ". DB_TABLE_PREFIX ."webhook_requests;"
      )->each(function($field) {
        $this->data[$field['Field']] = database::create_variable($field['Type']);
      });

      $this->previous = $this->data;
    }

    public function load($request_id) {

      if (!preg_match('#^[0-9]+$#', $request_id)) {
        throw new Exception('Invalid webhook request (ID: '. $request_id .')');
      }

      $this->reset();

      $request = database::query(
        "select * from ". DB_TABLE_PREFIX ."webhook_requests
        where id = ". (int)$request_id ."
        limit 1;"
      )->fetch();

      if (!$request) {
        throw new Exception('Could not find webhook (ID: '. (int)$request_id .') in database.');
      }

      $this->data = array_update($this->data, $request);

      $this->previous = $this->data;
    }

    public function save() {

      if (!$this->data['id']) {

				database::query(
          "insert into ". DB_TABLE_PREFIX ."webhook_requests
          (date_created)
          values ('". date('Y-m-d H:i:s') ."');"
        );

        $this->data['id'] = database::insert_id();
      }

			database::query(
				"update ". DB_TABLE_PREFIX ."webhook_requests
				set status = '". database::input($this->data['status']) ."',
					method = '". database::input($this->data['method']) ."',
					url = '". database::input($this->data['url']) ."',
					headers = '". (!empty($this->data['headers']) ? database::input($this->data['headers']) : "") ."',
					body = ". (isset($this->data['body']) ? "'". database::input($this->data['body']) ."'" : "NULL") .",
					failed_attempts = ". (int)$this->data['failed_attempts'] .",
					raw_request = ". (isset($this->data['raw_request']) ? "'". database::input($this->data['raw_request']) ."'" : "NULL") .",
					raw_response = ". (isset($this->data['raw_response']) ? "'". database::input($this->data['raw_response']) ."'" : "NULL") .",
					last_attempt = ". (!empty($this->data['last_attempt']) ? "'". database::input($this->data['last_attempt']) ."'" : "NULL") .",
					date_delivered = ". (!empty($this->data['date_delivered']) ? "'". database::input($this->data['date_delivered']) ."'" : "NULL") .",
					date_scheduled = ". (!empty($this->data['date_scheduled']) ? "'". database::input($this->data['date_scheduled']) ."'" : "NULL") .",
					date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

      $this->previous = $this->data;
    }

    public function delete() {

      database::query(
        "delete from ". DB_TABLE_PREFIX ."webhook_requests
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->reset();
    }
  }
