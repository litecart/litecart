<?php

  class ctrl_user {
    public $data;

    public function __construct($user_id=null) {

      if ($user_id !== null) {
        $this->load($user_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = array();

      $fields_query = database::query(
        "show fields from ". DB_TABLE_USERS .";"
      );
      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = null;
      }

      $this->data['permissions'] = array();
    }

    public function load($user_id) {

      if (!preg_match('#^[0-9]+$#', $user_id)) throw new Exception('Invalid user id (ID: '. $user_id .')');

      $this->reset();

      $user_query = database::query(
        "select * from ". DB_TABLE_USERS ."
        where id = ". (int)$user_id ."
        limit 1;"
      );

      if ($user = database::fetch($user_query)) {
        $this->data = array_replace($this->data, array_intersect_key($user, $this->data));
      } else {
        throw new Exception('Could not find user (ID: '. (int)$user_id .') in database.');
      }

      $this->data['permissions'] = @json_decode($this->data['permissions'], true);
    }

    public function save() {

      $old_user = new ctrl_user($this->data['id']);

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_USERS ."
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_USERS ." set
        status = '". (empty($this->data['status']) ? 0 : 1) ."',
        username = '". database::input($this->data['username']) ."',
        email = '". database::input($this->data['email']) ."',
        permissions = '". database::input(json_encode($this->data['permissions'], JSON_UNESCAPED_SLASHES)) ."',
        date_valid_from = '". database::input($this->data['date_valid_from']) ."',
        date_valid_to = '". database::input($this->data['date_valid_to']) ."',
        date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $htpasswd = file_get_contents(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd');

    // Rename .htpasswd user
      if (!empty($old_user->data['id']) && $old_user->data['username'] != $this->data['username']) {
        $htpasswd = preg_replace('#^(?:(\#)+)?('. preg_quote($old_user->data['username'], '#') .')?:(.*)$#m', '${1}'.$this->data['username'].':${3}', $htpasswd);
      }

    // Set .htpasswd user status
      if (!empty($this->data['status'])) {
        $htpasswd = preg_replace('#^(?:\#+)?('. preg_quote($this->data['username'], '#') .'):(.*)$#m', '${1}:${2}', $htpasswd);
      } else {
        $htpasswd = preg_replace('#^(?:\#+)?('. preg_quote($this->data['username'], '#') .'):(.*)$#m', '#${1}:${2}', $htpasswd);
      }

      file_put_contents(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd', $htpasswd);

      cache::clear_cache('users');
    }

    public function set_password($password) {

      if (empty($this->data['id'])) $this->save();

      $password_hash = functions::password_checksum($this->data['id'], $password, PASSWORD_SALT);

      database::query(
        "update ". DB_TABLE_USERS ."
        set
          password = '". $password_hash ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->data['password'] = $password_hash;

      $htpasswd = file_get_contents(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd');

      if (preg_match('#^(?:\#+)?('. preg_quote($this->data['username'], '#') .'):(.*)$#m', $htpasswd)) {
        $htpasswd = preg_replace('#^(?:(\#)+)?('. preg_quote($this->data['username'], '#') .'):.*(?:(\r|\n)+)?$#m', '${1}${2}:{SHA}'.base64_encode(sha1($password, true)) . PHP_EOL, $htpasswd);
      } else {
        $htpasswd .= $this->data['username'] .':{SHA}'. base64_encode(sha1($password, true)) . PHP_EOL;
      }

      file_put_contents(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd', $htpasswd);

      $this->save();
    }

    public function delete() {

      $htpasswd = file_get_contents(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd');
      $htpasswd = preg_replace('#^(?:\#+)?'. preg_quote($this->data['username'], '#') .':.*(?:\r?\n?)+#m', '', $htpasswd);
      file_put_contents(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd', $htpasswd);

      database::query(
        "delete from ". DB_TABLE_USERS ."
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->data['id'] = null;

      cache::clear_cache('users');
    }
  }