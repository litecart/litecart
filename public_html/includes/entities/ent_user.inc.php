<?php

  class ent_user {
    public $data;
    public $previous;

    public function __construct($user_id=null) {

      if ($user_id !== null) {
        $this->load($user_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."users;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = database::create_variable($field['Type']);
      }

      $this->data['apps'] = [];
      $this->data['widgets'] = [];

      $this->previous = $this->data;
    }

    public function load($user_id) {

      if (!preg_match('#(^[0-9]+$|^[0-9a-zA-Z_]$|@)#', $user_id)) throw new Exception('Invalid user (ID: '. $user_id .')');

      $this->reset();

      $user_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."users
        ". (preg_match('#^[0-9]+$#', $user_id) ? "where id = '". (int)$user_id ."'" : "") ."
        ". (!preg_match('#^[0-9]+$#', $user_id) ? "where lower(username) = '". database::input(strtolower($user_id)) ."'" : "") ."
        ". (preg_match('#@#', $user_id) ? "where lower(email) = '". database::input(strtolower($user_id)) ."'" : "") ."
        limit 1;"
      );

      if ($user = database::fetch($user_query)) {
        $this->data = array_replace($this->data, array_intersect_key($user, $this->data));
      } else {
        throw new Exception('Could not find user (ID: '. (int)$user_id .') in database.');
      }

      $this->data['apps'] = !empty($this->data['apps']) ? json_decode($this->data['apps'], true) : [];
      $this->data['widgets'] = !empty($this->data['widgets']) ? json_decode($this->data['widgets'], true) : [];

      $this->previous = $this->data;
    }

    public function save() {

      $user_query = database::query(
        "select id from ". DB_TABLE_PREFIX ."users
        where (
          lower(username) = '". database::input(strtolower($this->data['username'])) ."'
          ". (!empty($this->data['email']) ? "or lower(email) = '". database::input(strtolower($this->data['email'])) ."'" : "") ."
        )
        ". (!empty($this->data['id']) ? "and id != ". (int)$this->data['id'] : "") ."
        limit 1;"
      );

      if (database::num_rows($user_query)) {
        throw new Exception(language::translate('error_user_conflict', 'The user conflicts another user in the database'));
      }

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."users
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."users set
        status = '". (empty($this->data['status']) ? 0 : 1) ."',
        username = '". database::input(strtolower($this->data['username'])) ."',
        email = '". database::input(strtolower($this->data['email'])) ."',
        apps = '". database::input(json_encode($this->data['apps'], JSON_UNESCAPED_SLASHES)) ."',
        widgets = '". database::input(json_encode($this->data['widgets'], JSON_UNESCAPED_SLASHES)) ."',
        date_valid_from = ". (empty($this->data['date_valid_from']) ? "null" : "'". date('Y-m-d H:i:s', strtotime($this->data['date_valid_from'])) ."'") .",
        date_valid_to = ". (empty($this->data['date_valid_to']) ? "null" : "'". date('Y-m-d H:i:s', strtotime($this->data['date_valid_to'])) ."'") .",
        date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $htpasswd = file_get_contents(FS_DIR_ADMIN . '.htpasswd');

    // Rename .htpasswd user
      if (empty($this->previous) && $this->data['username'] != $this->previous['username']) {
        $htpasswd = preg_replace('#(?<=^|\r\n|\r|\n)(\#*'. preg_quote($this->previous['username'], '#') .'):([^\r|\n]+)(\r\n?|\n)*#', '$1:$2' . PHP_EOL, $htpasswd);
      }

    // Set .htpasswd user status
      if (!empty($this->data['status'])) {
        $htpasswd = preg_replace('#(?<=^|\r\n|\r|\n)\#*('. preg_quote($this->data['username'], '#') .'):([^\r|\n]+)(\r\n?|\n)*#', '$1:$2' . PHP_EOL, $htpasswd);
      } else {
        $htpasswd = preg_replace('#(?<=^|\r\n|\r|\n)\#*('. preg_quote($this->data['username'], '#') .'):([^\r|\n]+)(\r\n?|\n)*#', '#$1:$2' . PHP_EOL, $htpasswd);
      }

      file_put_contents(FS_DIR_ADMIN . '.htpasswd', $htpasswd);

      $this->previous = $this->data;

      cache::clear_cache('users');
    }

    public function set_password($password) {

      if (empty($this->data['id'])) {
        $this->save();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."users
        set password_hash = '". database::input($this->data['password_hash'] = password_hash($password, PASSWORD_DEFAULT)) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $htpasswd = file_get_contents(FS_DIR_ADMIN . '.htpasswd');

      if (preg_match('#(?<=^|\r\n|\r|\n)\#*('. preg_quote($this->data['username'], '#') .'):.*?#', $htpasswd)) {
        $htpasswd = preg_replace('#(?<=^|\r\n|\r|\n)(\#*'. preg_quote($this->data['username'], '#') .'):[^\r|\n]+(\r\n?|\n)*#', '$1:{SHA}'.base64_encode(sha1($password, true)) . PHP_EOL, $htpasswd);
      } else {
        $htpasswd .= $this->data['username'] .':{SHA}'. base64_encode(sha1($password, true)) . PHP_EOL;
      }

      file_put_contents(FS_DIR_ADMIN . '.htpasswd', $htpasswd);

      $this->previous['password_hash'] = $this->data['password_hash'];
    }

    public function delete() {

      $htpasswd = file_get_contents(FS_DIR_ADMIN . '.htpasswd');
      $htpasswd = preg_replace('#(?<=^|\r\n|\r|\n)\#*'. preg_quote($this->data['username'], '#') .':[^\r|\n]+(\r\n?|\n)*#', '', $htpasswd);
      file_put_contents(FS_DIR_ADMIN . '.htpasswd', $htpasswd);

      database::query(
        "delete from ". DB_TABLE_PREFIX ."users
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->reset();

      cache::clear_cache('users');
    }
  }
