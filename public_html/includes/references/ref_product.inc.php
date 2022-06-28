<?php

  class ref_product {

    private $_language_codes;
    private $_currency_codes;
    private $_customer_id;
    private $_data = [];

    function __construct($product_id, $language_code=null, $currency_code=null, $customer_id=null) {

      if (empty($language_code)) $language_code = language::$selected['code'];
      if (empty($currency_code)) $currency_code = currency::$selected['code'];
      if (empty($customer_id)) $customer_id = customer::$data['id'];

      $this->_data['id'] = (int)$product_id;
      $this->_customer_id = $customer_id;

      $this->_language_codes = array_unique([
        $language_code,
        settings::get('default_language_code'),
        settings::get('site_language_code'),
      ]);

      $this->_currency_codes = array_unique([
        $currency_code,
        settings::get('site_currency_code'),
      ]);
    }

    public function &__get($name) {

      if (array_key_exists($name, $this->_data)) {
        return $this->_data[$name];
      }

      $this->_data[$name] = null;
      $this->_load($name);

      return $this->_data[$name];
    }

    public function &__isset($name) {
      return $this->__get($name);
    }

    public function __set($name, $value) {
      trigger_error('Setting data is prohibited ('.$name.')', E_USER_ERROR);
    }

    private function _load($field) {

      switch($field) {

        case 'also_purchased_products':

          $this->_data['also_purchased_products'] = [];

            $query = database::query(
              "select oi.product_id, sum(oi.quantity) as num_purchases from ". DB_TABLE_PREFIX ."orders_items oi
              left join ". DB_TABLE_PREFIX ."products p on (p.id = oi.product_id)
              where p.status
              and (oi.product_id != 0 and oi.product_id != ". (int)$this->_data['id'] .")
              and order_id in (
                select distinct order_id as id from ". DB_TABLE_PREFIX ."orders_items
                where product_id = ". (int)$this->_data['id'] ."
              )
              group by oi.product_id
              order by num_purchases desc;"
            );

            while ($row = database::fetch($query)) {
              $this->_data['also_purchased_products'][$row['product_id']] = reference::product($row['product_id'], $this->_language_codes[0]);
            }

          break;

        case 'attributes':

          $this->_data['attributes'] = [];

          $product_attributes_query = database::query(
            "select pa.*, ag.code, agi.name as group_name, avi.name as value_name, pa.custom_value from ". DB_TABLE_PREFIX ."products_attributes pa
            left join ". DB_TABLE_PREFIX ."attribute_groups ag on (ag.id = pa.group_id)
            left join ". DB_TABLE_PREFIX ."attribute_groups_info agi on (agi.group_id = pa.group_id and agi.language_code = '". database::input($this->_language_codes[0]) ."')
            left join ". DB_TABLE_PREFIX ."attribute_values_info avi on (avi.value_id = pa.value_id and avi.language_code = '". database::input($this->_language_codes[0]) ."')
            where product_id = ". (int)$this->_data['id'] ."
            order by priority, group_name, value_name, custom_value;"
          );

          while ($attribute = database::fetch($product_attributes_query)) {
            $this->_data['attributes'][$attribute['group_id'].'-'.$attribute['value_id']] = $attribute;
          }

          break;

        case 'brand':

          $this->_data['brand'] = [];

          if (empty($this->_data['brand_id'])) return;

          $this->_data['brand'] = reference::brand($this->brand_id, $this->_language_codes[0]);

          break;

        case 'categories':

          $this->_data['categories'] = [];

          $products_to_categories_query = database::query(
            "select * from ". DB_TABLE_PREFIX ."products_to_categories
            where product_id = ". (int)$this->_data['id'] .";"
          );

          while ($product_to_category = database::fetch($products_to_categories_query)) {
            $categories_info_query = database::query(
              "select * from ". DB_TABLE_PREFIX ."categories_info
              where category_id = ". (int)$product_to_category['category_id'] ."
              and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
              order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
            );

            while ($row = database::fetch($categories_info_query)) {
              foreach ($row as $key => $value) {
                if (in_array($key, ['id', 'category_id', 'language_code'])) continue;
                if (empty($this->_data['categories'][$product_to_category['category_id']])) $this->_data['categories'][$product_to_category['category_id']] = $value;
              }
            }
          }

          break;

        case 'campaign':

          $this->_data['campaign'] = database::fetch(database::query(
            "select *, min(
              coalesce(
                ". implode(", ", array_map(function($currency_code){ return "if(`". database::input($currency_code) ."` != 0, `". database::input($currency_code) ."` * ". currency::$currencies[$currency_code]['value'] .", null)"; }, $this->_currency_codes)) ."
              )
            ) as price
            from ". DB_TABLE_PREFIX ."products_campaigns
            where product_id = ". (int)$this->_data['id'] ."
            and (start_date is null or start_date <= '". date('Y-m-d H:i:s') ."')
            and (end_date is null or year(end_date) < '1971' or end_date >= '". date('Y-m-d H:i:s') ."')
            limit 1;"
          ));

          break;

        case 'default_category':

          $this->_data['default_category'] = false;

          if (empty($this->default_category_id)) return;

          $this->_data['default_category'] = reference::category($this->default_category_id, $this->_language_codes[0]);

          break;

        case 'delivery_status':

          $this->_data['delivery_status'] = [];

          $query = database::query(
            "select * from ". DB_TABLE_PREFIX ."delivery_statuses_info
            where delivery_status_id = ". (int)$this->_data['delivery_status_id'] ."
            and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
            order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
          );

          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) {
              if (in_array($key, ['id', 'delivery_status_id', 'language_code'])) continue;
              if (empty($this->_data['delivery_status'][$key])) $this->_data['delivery_status'][$key] = $value;
            }
          }

          break;

        case 'final_price':

          $this->_data['final_price'] = (isset($this->campaign['price']) && $this->campaign['price'] > 0) ? $this->campaign['price'] : $this->price;

          break;

        case 'images':

          $this->_data['images'] = database::fetch_all(database::query(
            "select * from ". DB_TABLE_PREFIX ."products_images
            where product_id = ". (int)$this->_data['id'] ."
            order by priority asc, id asc;"
          ), 'filename');

          break;

        case 'name':
        case 'short_description':
        case 'description':
        case 'technical_data':
        case 'head_title':
        case 'meta_description':

          $query = database::query(
            "select * from ". DB_TABLE_PREFIX ."products_info
            where product_id = ". (int)$this->_data['id'] ."
            and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
            order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
          );

          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) {
              if (in_array($key, ['id', 'product_id', 'language_code'])) continue;
              if (empty($this->_data[$key])) $this->_data[$key] = $value;
            }
          }

          if ($this->autofill_technical_data) {
            $this->_data['technical_data'] = '';
            foreach ($this->attributes as $attribute) {
              $this->_data['technical_data'] = $attribute['group_name'] .': '. $attribute['value_name'] . PHP_EOL;
            }
            $this->_data['technical_data'] = rtrim($this->_data['technical_data']);
          }

          break;

        case 'parents':

          $this->_data['parents'] = [];

          $query = database::query(
            "select category_id from ". DB_TABLE_PREFIX ."products_to_categories
            where product_id = ". (int)$this->_data['id'] .";"
          );

          while ($row = database::fetch($query)) {
            $this->_data['parents'][$row['category_id']] = reference::category($row['category_id'], $this->_language_codes[0]);
          }

          break;

        case 'price':

          $this->_data['price'] = (float)database::fetch(database::query(
            "select coalesce(
              ". implode(", ", array_map(function($currency){ return "if(`". database::input($currency['code']) ."` != 0, `". database::input($currency['code']) ."` * ". $currency['value'] .", null)"; }, currency::$currencies)) ."
            ) price
            from ". DB_TABLE_PREFIX ."products_prices
            where product_id = ". (int)$this->_data['id'] ."
            limit 1;"
          ), 'price');

          break;

        case 'quantity':

          $this->_data['quantity'] = (float)database::fetch(database::query(
            "select sum(si.quantity) as sum_quanity from ". DB_TABLE_PREFIX ."products_to_stock_items p2si
            where p2si.product_id = ". (int)$this->_data['id'] .";"
          ), 'sum_quantity');

          break;

        case 'quantity_unit':

          $this->_data['quantity_unit'] = database::fetch(database::query(
            "select id, decimals, separate from ". DB_TABLE_PREFIX ."quantity_units
            where id = ". (int)$this->quantity_unit_id ."
            limit 1;"
          ));

          if (!$this->_data['quantity_unit']) return;

          $query = database::query(
            "select * from ". DB_TABLE_PREFIX ."quantity_units_info
            where quantity_unit_id = ". (int)$this->quantity_unit_id ."
            and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
            order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
          );

          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) {
              if (in_array($key, ['id', 'quantity_unit_id', 'language_code'])) continue;
              if (empty($this->_data['quantity_unit'][$key])) $this->_data['quantity_unit'][$key] = $value;
            }
          }

          break;

        case 'stock_options':

          $this->_data['stock_options'] = [];

          $query = database::query(
            "select p2si.*, sii.name, si.sku, si.gtin, si.mpn, si.weight, si.weight_unit, si.length, si.width, si.height, si.length_unit, si.quantity, oi.reserved, coalesce(si.quantity - oi.reserved, si.quantity) as available, si.image
            from ". DB_TABLE_PREFIX ."products_to_stock_items p2si
            left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = p2si.stock_item_id)
            left join ". DB_TABLE_PREFIX ."stock_items_info sii on (sii.stock_item_id = p2si.stock_item_id and sii.language_code = '". database::input(language::$selected['code']) ."')
            left join (
              select stock_item_id, sum(quantity) as reserved from ". DB_TABLE_PREFIX ."orders_items
              where order_id in (
                select id from ". DB_TABLE_PREFIX ."orders
                where order_status_id in (
                  select id from ". DB_TABLE_PREFIX ."order_statuses
                  where stock_action = 'reserve'
                )
              )
              group by stock_item_id
            ) oi on (oi.stock_item_id = p2si.stock_item_id)
            where product_id = ". (int)$this->_data['id'] ."
            ". (!empty($option_id) ? "and id = ". (int)$option_id ."" : '') ."
            order by p2si.priority asc;"
          );

          while ($row = database::fetch($query)) {
            $this->_data['stock_options'][$row['id']] = $row;
          }

          break;

        case 'supplier':

          $this->_data['supplier'] = null;

          if (!empty($this->supplier_id)) {
            $this->_data['supplier'] = reference::supplier($this->supplier_id);
          }

          break;

        case 'sold_out_status':

          $this->_data['sold_out_status'] = database::fetch(database::query(
            "select id, orderable from ". DB_TABLE_PREFIX ."sold_out_statuses
            where id = ". (int)$this->sold_out_status_id ."
            limit 1;"
          ));

          if (!$this->_data['sold_out_status']) return;

          $query = database::query(
            "select * from ". DB_TABLE_PREFIX ."sold_out_statuses_info
            where sold_out_status_id = ". (int)$this->_data['sold_out_status_id'] ."
            and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
            order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
          );

          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) {
              if (in_array($key, ['id', 'sold_out_status_id', 'language_code'])) continue;
              if (empty($this->_data['sold_out_status'][$key])) $this->_data['sold_out_status'][$key] = $value;
            }
          }

          break;

        case 'tax':

          $this->_data['tax'] = tax::get_tax($this->final_price, $this->tax_class_id);

          break;

        default:

          $row = database::fetch(database::query(
            "select * from ". DB_TABLE_PREFIX ."products
            where id = ". (int)$this->_data['id'] ."
            limit 1;"
          ));

          foreach ($row as $key => $value) {
            $this->_data[$key] = $value;
          }

          $this->_data['keywords'] = preg_split('#\s*,\s*#', $this->_data['keywords'], -1, PREG_SPLIT_NO_EMPTY);

          break;
      }
    }
  }
