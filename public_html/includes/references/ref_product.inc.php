<?php

  class ref_product extends abs_reference_entity {

    protected $_language_codes;
    protected $_currency_codes;
    protected $_customer_id;

    function __construct($product_id, $language_code=null, $currency_code=null, $customer_id=null) {

      if (empty($language_code)) {
        $language_code = language::$selected['code'];
      }

      if (empty($currency_code)) {
        $currency_code = currency::$selected['code'];
      }

      if (empty($customer_id)) {
        $customer_id = customer::$data['id'];
      }

      $this->_data['id'] = (int)$product_id;

      $this->_customer_id = $customer_id;

      $this->_language_codes = array_unique([
        $language_code,
        settings::get('default_language_code'),
        settings::get('store_language_code'),
      ]);

      $this->_currency_codes = array_unique([
        $currency_code,
        settings::get('store_currency_code'),
      ]);
    }

    protected function _load($field) {

      switch($field) {

        case 'also_purchased_products':

          $this->_data['also_purchased_products'] = database::query(
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
          )->fetch_all(function($product) {
            return reference::product($product['product_id'], $this->_language_codes[0]);
          });

          break;

        case 'attributes':

          $this->_data['attributes'] = database::query(
            "select pa.id, ag.code, pa.group_id, pa.value_id, pa.custom_value, agi.name as group_name, avi.name as value_name, pa.custom_value from ". DB_TABLE_PREFIX ."products_attributes pa
            left join ". DB_TABLE_PREFIX ."attribute_groups ag on (ag.id = pa.group_id)
            left join ". DB_TABLE_PREFIX ."attribute_groups_info agi on (agi.group_id = pa.group_id and agi.language_code = '". database::input($this->_language_codes[0]) ."')
            left join ". DB_TABLE_PREFIX ."attribute_values_info avi on (avi.value_id = pa.value_id and avi.language_code = '". database::input($this->_language_codes[0]) ."')
            where product_id = ". (int)$this->_data['id'] ."
            order by priority, group_name, value_name, custom_value;"
          )->fetch_all();

          break;

        case 'brand':

          $this->_data['brand'] = [];

          if (empty($this->_data['brand_id'])) return;

          $this->_data['brand'] = reference::brand($this->brand_id, $this->_language_codes[0]);

        case 'campaign':

          $this->_data['campaign'] = database::query(
            "select *, min(
              coalesce(
                ". implode(", ", array_map(function($currency_code){ return "if(`". database::input($currency_code) ."` != 0, `". database::input($currency_code) ."` * ". currency::$currencies[$currency_code]['value'] .", null)"; }, $this->_currency_codes)) ."
              )
            ) as price
            from ". DB_TABLE_PREFIX ."products_campaigns
            where product_id = ". (int)$this->_data['id'] ."
            and (start_date is null or start_date <= '". date('Y-m-d H:i:s') ."')
            and (end_date is null or end_date >= '". date('Y-m-d H:i:s') ."')
            limit 1;"
          )->fetch();

          break;

        case 'campaign':

          $this->_data['campaign'] = [];

          database::query(
            "select *, min(if(`". database::input(currency::$selected['code']) ."`, `". database::input(currency::$selected['code']) ."` * ". (float)currency::$selected['value'] .", `". database::input(settings::get('store_currency_code')) ."`)) as price
            from ". DB_TABLE_PREFIX ."products_campaigns
            where product_id = ". (int)$this->_data['id'] ."
            and (start_date is null or start_date <= '". date('Y-m-d H:i:s') ."')
            and (end_date is null or year(end_date) < '1971' or end_date >= '". date('Y-m-d H:i:s') ."');"
          )->each(function($campaign) {
            if ($campaign['price'] < $this->price) {
              if (!isset($this->_data['campaign']['price']) || $campaign['price'] < $this->_data['campaign']['price']) {
                $this->_data['campaign'] = $campaign;
              }
            }
          });

          break;

        case 'categories':

          $this->_data['categories'] = [];

          database::query(
            "select * from ". DB_TABLE_PREFIX ."products_to_categories
            where product_id = ". (int)$this->_data['id'] .";"
          )->each(function($product_to_category) {

            database::query(
              "select * from ". DB_TABLE_PREFIX ."categories_info
              where category_id = ". (int)$product_to_category['category_id'] ."
              and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
              order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
            )->each(function($info){
              foreach ($info as $key => $value) {
                if (in_array($key, ['id', 'category_id', 'language_code'])) continue;
                if (empty($this->_data['categories'][$info['category_id']])) {
                  $this->_data['categories'][$info['category_id']] = $value;
                }
              }
            });
          });

          break;

        case 'default_category':

          $this->_data['default_category'] = 0;

          if (empty($this->default_category_id)) return;

          $this->_data['default_category'] = reference::category($this->default_category_id, $this->_language_codes[0]);

          break;

        case 'delivery_status':

          $this->_data['delivery_status'] = [];

          database::query(
            "select * from ". DB_TABLE_PREFIX ."delivery_statuses_info
            where delivery_status_id = ". (int)$this->_data['delivery_status_id'] ."
            and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
            order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
          )->each(function($info){

            foreach ($info as $key => $value) {
              if (in_array($key, ['id', 'delivery_status_id', 'language_code'])) continue;
              if (empty($this->_data['delivery_status'][$key])) {
                $this->_data['delivery_status'][$key] = $value;
              }
            }

          });

          break;

        case 'final_price':

          $this->_data['final_price'] = $this->price;

          if (isset($this->campaign['price']) && $this->campaign['price'] > 0 && $this->campaign['price'] < $this->_data['final_price']) {
            $this->_data['final_price'] = $this->campaign['price'];
          }

          break;

        case 'images':

          $this->_data['images'] = database::query(
            "select * from ". DB_TABLE_PREFIX ."products_images
            where product_id = ". (int)$this->_data['id'] ."
            order by priority asc, id asc;"
          )->fetch_all('filename');

          break;

        case 'name':
        case 'short_description':
        case 'description':
        case 'technical_data':
        case 'head_title':
        case 'meta_description':
        case 'synonyms':

          database::query(
            "select * from ". DB_TABLE_PREFIX ."products_info
            where product_id = ". (int)$this->_data['id'] ."
            and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
            order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
          )->each(function($info){

            foreach ($info as $key => $value) {
              if (in_array($key, ['id', 'product_id', 'language_code'])) continue;
              if (empty($this->_data[$key])) $this->_data[$key] = $value;
            }

          });

          if ($this->autofill_technical_data) {
            $this->_data['technical_data'] = '';
            foreach ($this->attributes as $attribute) {
              $this->_data['technical_data'] = $attribute['group_name'] .': '. $attribute['value_name'] . PHP_EOL;
            }
            $this->_data['technical_data'] = rtrim($this->_data['technical_data']);
          }

          $this->_data['synonyms'] = preg_split('#\s*,\s*#', (string)$this->_data['synonyms'], -1, PREG_SPLIT_NO_EMPTY);

          break;

        case 'options':

          $this->_data['options'] = [];

          $products_options_query = database::query(
            "select * from ". DB_TABLE_PREFIX ."products_options
            where product_id = ". (int)$this->_data['id'] ."
            order by priority;"
          )->fetch(function($option){

          // Group
            database::query(
              "select * from ". DB_TABLE_PREFIX ."attribute_groups_info pcgi
              where group_id = ". (int)$option['group_id'] ."
              and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
              order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
            )->each(function($info) use ($option) {

              foreach ($info as $key => $value) {
                if (in_array($k, ['id', 'group_id', 'language_code'])) continue;
                if (empty($option[$key])) $option[$key] = $value;
              }

            });

          // Values
            $option['values'] = [];

            database::query(
              "select * from ". DB_TABLE_PREFIX ."products_options_values
              where product_id = ". (int)$this->_data['id'] ."
              and group_id = ". (int)$option['group_id'] ."
              order by priority;"
            )->each(function($value) use ($option) {

              if (!empty($value['value_id'])) {

                database::query(
                  "select * from ". DB_TABLE_PREFIX ."attribute_values_info pcvi
                  where value_id = ". (int)$value['value_id'] ."
                  and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
                  order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
                )->each(function($info){

                  foreach ($info as $key => $v) {
                    if (in_array($key, ['id', 'value_id', 'language_code'])) continue;
                    if (empty($value[$key])) $value[$key] = $v;
                  }

                });

              } else {
                $value['name'] = $value['custom_value'];
              }

            // Price Adjust
              $value['price_adjust'] = 0;

              if ((!empty($value[$this->_currency_code]) && (float)$value[$this->_currency_code] != 0) || (!empty($value[settings::get('store_currency_code')]) && (float)$value[settings::get('store_currency_code')] != 0)) {

                switch ($value['price_operator']) {

                  case '+':
                    if ((float)$value[$this->_currency_code] != 0) {
                      $value['price_adjust'] = currency::convert($value[$this->_currency_code], $this->_currency_code, settings::get('store_currency_code'));
                    } else {
                      $value['price_adjust'] = (float)$value[settings::get('store_currency_code')];
                    }
                    break;

                  case '%':
                    if ((float)$value[$this->_currency_code] != 0) {
                      $value['price_adjust'] = $this->price * currency::convert((float)$value[$this->_currency_code], $this->_currency_code, settings::get('store_currency_code')) / 100;
                    } else {
                      $value['price_adjust'] = $this->price * (float)$value[settings::get('store_currency_code')] / 100;
                    }
                    break;

                  case '*':
                    if ((float)$value[$this->_currency_code] != 0) {
                      $value['price_adjust'] = $this->price * currency::convert($value[$this->_currency_code], $this->_currency_code, settings::get('store_currency_code'));
                    } else {
                      $value['price_adjust'] = $this->price * $value[settings::get('store_currency_code')];
                    }
                    break;

                  case '=':
                    if ((float)$value[$this->_currency_code] != 0) {
                      $value['price_adjust'] = currency::convert($value[$this->_currency_code], $this->_currency_code, settings::get('store_currency_code')) - $this->price;
                    } else {
                      $value['price_adjust'] = $value[settings::get('store_currency_code')] - $this->price;
                    }
                    break;

                  default:
                    trigger_error('Unknown price operator for option', E_USER_WARNING);
                    break;
                }
              }

              if ($value['price_adjust'] && !empty($this->campaign['price'])) {
                $value['price_adjust'] = $value['price_adjust'] * $this->campaign['price'] / $this->price;
              }

              if (!empty($value['value_id'])) {
                $option['values'][$value['value_id']] = $value;
              } else {
                $option['values'][uniqid()] = $value;
              }
            });

            if ($option['sort'] == 'alphabetically') {
              uasort($option['values'], function($a, $b){
                if ($a['name'] == $b['name']) return 0;
                return ($a['name'] < $b['name']) ? -1 : 1;
              });
            }

            $this->_data['options'][$option['group_id']] = $option;
          });

          break;

        case 'options_stock':

          $this->_data['options_stock'] = [];

          database::query(
            "select * from ". DB_TABLE_PREFIX ."products_options_stock
            where product_id = ". (int)$this->_data['id'] ."
            ". (!empty($option_id) ? "and id = ". (int)$option_id ."" : '') ."
            order by priority asc;"
          )->each(function($stock_option){

            if (empty($stock_option['tax_class_id'])) {
              $stock_option['tax_class_id'] = $this->tax_class_id;
            }

            if (empty($stock_option['sku'])) {
              $stock_option['sku'] = $this->sku;
            }

            if (empty($stock_option['weight']) || (float)$stock_option['weight'] == 0) {
              $stock_option['weight'] = $this->weight;
              $stock_option['weight_class'] = $this->weight_class;
            }

            if (empty($stock_option['dim_x'])) {
              $stock_option['dim_x'] = $this->dim_x;
              $stock_option['dim_y'] = $this->dim_y;
              $stock_option['dim_z'] = $this->dim_z;
              $stock_option['dim_class'] = $this->dim_class;
            }

            $stock_option['quantity_available'] = null;

            $stock_option['reserved_quantity'] = database::query(
              "select sum(quantity) as total_reserved from ". DB_TABLE_PREFIX ."orders_items oi
              left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
              where oi.product_id = ". (int)$this->_data['id'] ."
              and oi.option_stock_combination = '". database::input($stock_option['combination']) ."'
              and o.order_status_id in (
                select id from ". DB_TABLE_PREFIX ."order_statuses
                where stock_action = 'reserve'
              );"
            )->fetch('total_reserved');

            $stock_option['quantity_available'] = $stock_option['quantity'] - $stock_option['reserved_quantity'];

            $stock_option['name'] = [];

            foreach (explode(',', $stock_option['combination']) as $combination) {
              list($group_id, $value_id) = explode('-', $combination);

              if (preg_match('#^0:"?(.*?)"?$#', $value_id, $matches)) {

                foreach (array_keys(language::$languages) as $language_code) {
                  $stock_option['name'][$language_code] = $matches[1];
                }

              } else {

                $options_values_query = database::query(
                  "select * from ". DB_TABLE_PREFIX ."products_options_values pov
                  left join ". DB_TABLE_PREFIX ."attribute_values_info avi on (avi.value_id = pov.value_id)
                  where pov.value_id = ". (int)$value_id ."
                  and avi.language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
                  order by field(avi.language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
                );

                while ($option_value_info = database::fetch($options_values_query)) {

                  foreach ($option_value_info as $key => $value) {
                    if (in_array($key, ['id', 'value_id', 'language_code'])) continue;
                    if (!is_array(empty($stock_option[$key][$option_value_info['value_id']]))) continue;
                    if (empty($stock_option[$key][$option_value_info['value_id']])) {
                      $stock_option[$key][$option_value_info['value_id']] = $value;
                    }
                  }

                }
              }
            }

            $stock_option['name'] = implode(',', $stock_option['name']);

            $this->_data['options_stock'][$stock_option['id']] = $stock_option;
          });

          break;

        case 'parents':

          $this->_data['parents'] = database::query(
            "select category_id from ". DB_TABLE_PREFIX ."products_to_categories
            where product_id = ". (int)$this->_data['id'] .";"
          )->fetch_all(function($row) {
            return reference::category($row['category_id'], $this->_language_codes[0]);
          });

          break;

        case 'price':

          $this->_data['price'] = (float)database::query(
            "select coalesce(
              ". implode(", ", array_map(function($currency){ return "if(`". database::input($currency['code']) ."` != 0, `". database::input($currency['code']) ."` * ". $currency['value'] .", null)"; }, currency::$currencies)) ."
            ) price
            from ". DB_TABLE_PREFIX ."products_prices
            where product_id = ". (int)$this->_data['id'] ."
            limit 1;"
          )->fetch('price');

          break;

        case 'quantity':
        case 'num_stock_options':

          $this->_data['quantity'] = null;
          $this->_data['num_stock_options'] = null;

          $stock_options = database::query(
            "select count(id) as num_stock_options, sum(quantity) as total_quantity
            from ". DB_TABLE_PREFIX ."products_stock_options
            where product_id = ". (int)$this->_data['id'] ."
            group by product_id;"
          )->fetch();

          $this->_data['num_stock_options'] = $stock_options['num_stock_options'];

          if ($stock_options['num_stock_options']) {
            $this->_data['quantity'] = $stock_options['total_quantity'];
          }

          break;

        case 'quantity_available':
        case 'quantity_reserved':

          $this->_data['quantity_available'] = null;

          if (!database::query(
            "select id from ". DB_TABLE_PREFIX ."products_stock_options
            where product_id = ". (int)$this->_data['id'] ."
            limit 1;"
          )->num_rows) {
            break;
          }

          $this->_data['quantity_reserved'] = database::query(
            "select sum(quantity) as total_reserved from ". DB_TABLE_PREFIX ."orders_items oi
            left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
            where oi.product_id = ". (int)$this->_data['id'] ."
            and o.order_status_id in (
              select id from ". DB_TABLE_PREFIX ."order_statuses
              where stock_action = 'reserve'
            );"
          )->fetch('total_reserved');

          $this->_data['quantity_available'] = $this->quantity - $this->_data['quantity_reserved'];

          break;

        case 'quantity_available':
        case 'quantity_reserved':

          $this->_data['quantity_reserved'] = database::query(
            "select sum(quantity) as total_reserved from ". DB_TABLE_PREFIX ."orders_items oi
            left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
            where oi.product_id = ". (int)$this->_data['id'] ."
            and o.order_status_id in (
              select id from ". DB_TABLE_PREFIX ."order_statuses
              where stock_action = 'reserve'
            );"
          )->fetch('total_reserved');

          $this->_data['quantity_available'] = $this->quantity - $this->quantity_reserved;

          break;

        case 'quantity_unit':

          $this->_data['quantity_unit'] = database::query(
            "select id, decimals, separate from ". DB_TABLE_PREFIX ."quantity_units
            where id = ". (int)$this->quantity_unit_id ."
            limit 1;"
          )->fetch();

          if (!$this->_data['quantity_unit']) return;

          database::query(
            "select * from ". DB_TABLE_PREFIX ."quantity_units_info
            where quantity_unit_id = ". (int)$this->quantity_unit_id ."
            and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
            order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
          )->each(function($info){
            foreach ($info as $key => $value) {
              if (in_array($key, ['id', 'quantity_unit_id', 'language_code'])) continue;
              if (empty($this->_data['quantity_unit'][$key])) {
                $this->_data['quantity_unit'][$key] = $value;
              }
            }
          });

          break;

        case 'supplier':

          $this->_data['supplier'] = null;

          if (!empty($this->supplier_id)) {
            $this->_data['supplier'] = reference::supplier($this->supplier_id);
          }

          break;

        case 'sold_out_status':

          $this->_data['sold_out_status'] = database::query(
            "select id, orderable from ". DB_TABLE_PREFIX ."sold_out_statuses
            where id = ". (int)$this->sold_out_status_id ."
            limit 1;"
          )->fetch();

          if (!$this->_data['sold_out_status']) return;

          database::query(
            "select * from ". DB_TABLE_PREFIX ."sold_out_statuses_info
            where sold_out_status_id = ". (int)$this->_data['sold_out_status_id'] ."
            and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
            order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
          )->each(function($info){
            foreach ($info as $key => $value) {
              if (in_array($key, ['id', 'sold_out_status_id', 'language_code'])) continue;
              if (empty($this->_data['sold_out_status'][$key])) {
                $this->_data['sold_out_status'][$key] = $value;
              }
            }
          });

          break;

        case 'tax':

          $this->_data['tax'] = tax::get_tax($this->final_price, $this->tax_class_id);

          break;

        default:

          $result = database::query(
            "select * from ". DB_TABLE_PREFIX ."products
            where id = ". (int)$this->_data['id'] ."
            limit 1;"
          );

          if ($result->num_rows) {
            $row = $result->fetch();
          } else {
            $row = array_fill_keys($result->fields(), null);
          }

          foreach ($row as $key => $value) {
            $this->_data[$key] = $value;
          }

          $this->_data['keywords'] = preg_split('#\s*,\s*#', (string)$this->_data['keywords'], -1, PREG_SPLIT_NO_EMPTY);

          break;
      }
    }
  }
