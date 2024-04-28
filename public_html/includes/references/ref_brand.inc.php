<?php

  class ref_brand extends abs_reference_entity {

    protected $_language_codes;

    function __construct($brand_id, $language_code=null) {

      if (empty($language_code)){
        $language_code = language::$selected['code'];
      }

      $this->_data['id'] = (int)$brand_id;
      $this->_language_codes = array_unique([
        $language_code,
        settings::get('default_language_code'),
        settings::get('store_language_code'),
      ]);
    }

    protected function _load($field) {

      switch($field) {

        case 'description':
        case 'short_description':
        case 'head_title':
        case 'meta_description':
        case 'h1_title':
        case 'link':

          $this->_data['info'] = [];

          $query = database::query(
            "select * from ". DB_TABLE_PREFIX ."brands_info
            where brand_id = ". (int)$this->_data['id'] ."
            and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
            order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
          );

          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) {

              if (in_array($key, ['id', 'brand_id', 'language_code'])) continue;

              if (empty($this->_data[$key])) {
                $this->_data[$key] = $value;
              }
            }
          }

          break;

        case 'products':

          $this->_data['products'] = database::query(
            "select id from ". DB_TABLE_PREFIX ."products
            where status
            and brand_id = ". (int)$this->_data['id'] ."
            and (quantity > 0 or sold_out_status_id in (
              select id from ". DB_TABLE_PREFIX ."sold_out_statuses
              where (hidden is null or hidden = 0)
            ))
            and (date_valid_from is null or date_valid_from <= '". date('Y-m-d H:i:s') ."')
            and (date_valid_to is null or date_valid_to >= '". date('Y-m-d H:i:s') ."');"
          )->fetch_all(function($row) {
            return new ref_product($row['id'], $this->_language_codes[0]);
          });

          break;

        case 'num_products':

          if (!empty($this->_data['products'])) {
            $this->_data['num_products'] = count($this->_data['products']);
            break;
          }

          $this->_data['num_products'] = (int)database::query(
            "select count(id) as num_products from ". DB_TABLE_PREFIX ."products
            where status
            and brand_id = ". (int)$this->_data['id'] ."
            and (quantity > 0 or sold_out_status_id in (
              select id from ". DB_TABLE_PREFIX ."sold_out_statuses
              where (hidden is null or hidden = 0)
            ))
            and (date_valid_from is null or date_valid_from <= '". date('Y-m-d H:i:s') ."')
            and (date_valid_to is null or date_valid_to >= '". date('Y-m-d H:i:s') ."');"
          )->fetch('num_products');

          break;

        default:

          $row = database::query(
            "select * from ". DB_TABLE_PREFIX ."brands
            where id = ". (int)$this->_data['id'] ."
            limit 1;"
          )->fetch();

          if (!$row) return;

          foreach ($row as $key => $value) {
            $this->_data[$key] = $value;
          }

          $this->_data['keywords'] = preg_split('#\s*,\s*#', $this->_data['keywords'], -1, PREG_SPLIT_NO_EMPTY);

          break;
      }
    }
  }
