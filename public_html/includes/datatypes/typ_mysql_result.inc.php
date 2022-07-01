<?php

  class typ_mysql_result {
    private $_query;
    private $_result;

    public function __construct(string $query, mysqli_result $result) {
      $this->_query = $query;
      $this->_result = $result;
    }

    public function __call($method, $arguments) {
      return call_user_func_array([$this->_result, $name], $arguments);
    }

    public function __get($name) {
      return $this->_result->$name;
    }

    public function __set($name, $value) {
      // Do nothing
    }

    public function export(&$object) {
      return $object = $this;
    }

    public function fields() {
      return array_column($this->_result->fetch_fields(), 'name');
    }

    public function fetch($column='') {

      if (class_exists('stats', false)) {
        stats::start_watch('database_execution');
      }

      $row = mysqli_fetch_assoc($this->_result);

      if ($column) {
        if (isset($row[$column])) {
          return $row[$column];
        } else {
          return false;
        }
      }

      if (class_exists('stats', false)) {
        stats::stop_watch('database_execution');
      }

      return $row;
    }

    public function fetch_all($column=null, $index_column=null) {

      if (class_exists('stats', false)) {
        stats::start_watch('database_execution');
      }

      if ($column || $index_column) {

        $rows = [];
        while ($row = mysqli_fetch_assoc($this->_result)) {
          if ($index_column) {
            $rows[$row[$index_column]] = $column ? $row[$column] : $row;
          } else {
            $rows[] = $column ? $row[$column] : $row;
          }
        }

      } else {
        $rows = mysqli_fetch_all($this->_result, MYSQLI_ASSOC);
      }

      if (class_exists('stats', false)) {
        stats::stop_watch('database_execution');
      }

      return $rows;
    }

    public function fetch_page($page, $items_per_page=null, &$num_rows=null, &$num_pages=null) {

      if (class_exists('stats', false)) {
        stats::start_watch('database_execution');
      }

      if ($page < 1) $page = 1;
      if (!$items_per_page) $items_per_page = settings::get('data_table_rows_per_page');

      mysqli_data_seek($this->_result, ((int)$page -1) * $items_per_page);

      $num_rows = mysqli_num_rows($this->_result);
      $num_pages = ceil($num_rows / $items_per_page);

      $rows = [];

      $i = 0;
      while ($row = mysqli_fetch_assoc($this->_result)) {
        $rows[] = $row;
        if (++$i == $items_per_page) break;
      }

      if (class_exists('stats', false)) {
        stats::stop_watch('database_execution');
      }

      return $rows;
    }

    public function seek($offset) {
      return mysqli_data_seek($this->_result, $offset);
    }

    public function num_rows() {
      return mysqli_num_rows($this->_result);
    }

    public function free() {
      return mysqli_free_result($this->_result);
    }

    public function __destruct() {
      $this->_result->free();
    }
  }
