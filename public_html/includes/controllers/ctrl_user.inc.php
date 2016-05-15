<?php

  class ctrl_user {
    public $data = array();

    public function __construct($user_id=null) {
      if ($user_id !== null) {
        $this->load((int)$user_id);
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
        $this->data[$field['Field']] = '';
      }
    }

    public function load($user_id) {

      $this->reset();

      $user_query = database::query(
        "select * from ". DB_TABLE_USERS ."
        where id = '". (int)$user_id ."'
        limit 1;"
      );
      $this->data = database::fetch($user_query);

      if (empty($this->data)) trigger_error('Could not find user (ID: '. (int)$user_id .') in database.', E_USER_ERROR);

      foreach(file(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd') as $row) {
        $row = explode(':', trim($row));
        if ($this->data['username'] == $row[0]) {
          $user->data['htpasswd'] = true;
          break;
        }
      }
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_USERS ."
          (date_created)
          values ('". database::input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      } else {
        $user_query = database::query(
          "select * from ". DB_TABLE_USERS ."
          where id = '". (int)$this->data['id'] ."'
          limit 1;"
        );
        $old_user = database::fetch($user_query);
      }

      $htpasswd = file_get_contents(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd');

    // Rename .htpasswd user
      if (!empty($old_user) && $old_user['username'] != $this->data['username']) {
        $htpasswd = preg_replace('/^(?:(#)+)?('. preg_quote($old_user['username'], '/') .')?:(.*)$/m', '${1}'.$this->data['username'].':${3}', $htpasswd);
      }

    // Set .htpasswd user status
      if (!empty($this->data['status'])) {
        $htpasswd = preg_replace('/^(?:#+)?('. preg_quote($this->data['username'], '/') .'):(.*)$/m', '${1}:${2}', $htpasswd);
      } else {
        $htpasswd = preg_replace('/^(?:#+)?('. preg_quote($this->data['username'], '/') .'):(.*)$/m', '#${1}:${2}', $htpasswd);
      }

      file_put_contents(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd', $htpasswd);

      database::query(
        "update ". DB_TABLE_USERS ."
        set
          status = '". (empty($this->data['status']) ? 0 : 1) ."',
          username = '". database::input($this->data['username']) ."',
          date_blocked = '". database::input($this->data['date_blocked']) ."',
          date_expires = '". database::input($this->data['date_expires']) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      cache::clear_cache('users');
    }

    public function set_password($password) {

      $this->save();

      $password_hash = functions::password_checksum($this->data['id'], $password, PASSWORD_SALT);

      database::query(
        "update ". DB_TABLE_USERS ."
        set
          password = '". $password_hash ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      $this->data['password'] = $password_hash;

      $htpasswd = file_get_contents(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd');

      if (preg_match('/^(?:#+)?('. preg_quote($this->data['username'], '/') .'):(.*)$/m', $htpasswd)) {
        $htpasswd = preg_replace('/^(?:(#)+)?('. preg_quote($this->data['username'], '/') .'):.*(?:(\r|\n)+)?$/m', '${1}${2}:{SHA}'.base64_encode(sha1($password, true)) . PHP_EOL, $htpasswd);
      } else {
        $htpasswd .= $this->data['username'] .':{SHA}'. base64_encode(sha1($password, true)) . PHP_EOL;
      }

      file_put_contents(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd', $htpasswd);
    }

    public function delete() {

      $htpasswd = file_get_contents(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd');
      $htpasswd = preg_replace('/^(?:#+)?'. preg_quote($this->data['username'], '/') .':.*(?:\r?\n?)+/m', '', $htpasswd);
      file_put_contents(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd', $htpasswd);

      database::query(
        "delete from ". DB_TABLE_USERS ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      $this->data['id'] = null;

      cache::clear_cache('users');
    }
  }

?>