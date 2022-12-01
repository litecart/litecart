<?php

  class ref_product {

    private $_currency_code;
    private $_language_codes;
    private $_customer_id;
    private $_data = [];

    function __construct($product_id, $language_code=null, $currency_code=null, $customer_id=null) {

      if (empty($language_code)) $language_code = language::$selected['code'];
      if (empty($currency_code)) $currency_code = currency::$selected['code'];
      if (empty($customer_id)) $customer_id = customer::$data['id'];

      $this->_data['id'] = (int)$product_id;
      $this->_language_codes = array_unique([
        $language_code,
        settings::get('default_language_code'),
        settings::get('store_language_code'),
      ]);
      $this->_currency_code = $currency_code;
      $this->_customer_id = $customer_id;
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
              "select oi.product_id, sum(oi.quantity) as total_quantity from ". DB_TABLE_PREFIX ."orders_items oi
              left join ". DB_TABLE_PREFIX ."products p on (p.id = oi.product_id)
              where p.status
              and (oi.product_id != 0 and oi.product_id != ". (int)$this->_data['id'] .")
              and order_id in (
                select distinct order_id as id from ". DB_TABLE_PREFIX ."orders_items
                where product_id = ". (int)$this->_data['id'] ."
              )
              group by oi.product_id
              order by total_quantity desc;"
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
            order by group_name, value_name, custom_value;"
          );

          while ($attribute = database::fetch($product_attributes_query)) {
            $this->_data['attributes'][$attribute['group_id'].'-'.$attribute['value_id']] = $attribute;
          }

          break;

        case 'name':
        case 'short_description':
        case 'description':
        case 'technical_data':
        case 'head_title':
        case 'meta_description':

          $this->_data['name'] = '';
          $this->_data['short_description'] = '';
          $this->_data['description'] = '';
          $this->_data['technical_data'] = '';
          $this->_data['head_title'] = '';
          $this->_data['meta_description'] = '';

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

          break;

        case 'campaign':

          $this->_data['campaign'] = [];

          $campaigns_query = database::query(
            "select *, min(if(`". database::input(currency::$selected['code']) ."`, `". database::input(currency::$selected['code']) ."` * ". (float)currency::$selected['value'] .", `". database::input(settings::get('store_currency_code')) ."`)) as price
            from ". DB_TABLE_PREFIX ."products_campaigns
            where product_id = ". (int)$this->_data['id'] ."
            and (start_date is null or start_date <= '". date('Y-m-d H:i:s') ."')
            and (end_date is null or year(end_date) < '1971' or end_date >= '". date('Y-m-d H:i:s') ."');"
          );

          while ($campaign = database::fetch($campaigns_query)) {
            if ($campaign['price'] < $this->price) {
              if (!isset($this->_data['campaign']['price']) || $campaign['price'] < $this->_data['campaign']['price']) {
                $this->_data['campaign'] = $campaign;
              }
            }
          }

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

        case 'default_category':

          $this->_data['default_category'] = 0;

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

          $this->_data['images'] = [];

          $query = database::query(
            "select * from ". DB_TABLE_PREFIX ."products_images
            where product_id = ". (int)$this->_data['id'] ."
            order by priority asc, id asc;"
          );

          while ($row = database::fetch($query)) {
            $this->_data['images'][$row['id']] = $row['filename'];
          }

          break;

        case 'manufacturer':

          $this->_data['manufacturer'] = [];

          if (empty($this->_data['manufacturer_id'])) return;

          $this->_data['manufacturer'] = reference::manufacturer($this->manufacturer_id, $this->_language_codes[0]);

          break;

        case 'options':

          $this->_data['options'] = [];

          $products_options_query = database::query(
            "select * from ". DB_TABLE_PREFIX ."products_options
            where product_id = ". (int)$this->_data['id'] ."
            order by priority;"
          );

          while ($option = database::fetch($products_options_query)) {

          // Group
            $attribute_group_info_query = database::query(
              "select * from ". DB_TABLE_PREFIX ."attribute_groups_info pcgi
              where group_id = ". (int)$option['group_id'] ."
              and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
              order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
            );

            while ($attribute_group_info = database::fetch($attribute_group_info_query)) {
              foreach ($attribute_group_info as $k => $v) {
                if (in_array($k, ['id', 'group_id', 'language_code'])) continue;
                if (empty($option[$k])) $option[$k] = $v;
              }
            }

          // Values
            $option['values'] = [];

            $option_values_query = database::query(
              "select * from ". DB_TABLE_PREFIX ."products_options_values
              where product_id = ". (int)$this->_data['id'] ."
              and group_id = ". (int)$option['group_id'] ."
              order by priority;"
            );

            while ($value = database::fetch($option_values_query)) {

              if (!empty($value['value_id'])) {

                $attribute_values_info_query = database::query(
                  "select * from ". DB_TABLE_PREFIX ."attribute_values_info pcvi
                  where value_id = ". (int)$value['value_id'] ."
                  and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
                  order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
                );

                while ($attribute_value_info = database::fetch($attribute_values_info_query)) {
                  foreach ($attribute_value_info as $k => $v) {
                    if (in_array($k, ['id', 'value_id', 'language_code'])) continue;
                    if (empty($value[$k])) $value[$k] = $v;
                  }
                }

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

              $option['values'][] = $value;
            }

            if ($option['sort'] == 'alphabetically') {
              uasort($option['values'], function($a, $b){
                if ($a['name'] == $b['name']) return 0;
                return ($a['name'] < $b['name']) ? -1 : 1;
              });
              break;
            }

            $this->_data['options'][$option['group_id']] = $option;
          }

          break;

        case 'options_stock':

          $this->_data['options_stock'] = [];

          $query = database::query(
            "select * from ". DB_TABLE_PREFIX ."products_options_stock
            where product_id = ". (int)$this->_data['id'] ."
            ". (!empty($option_id) ? "and id = ". (int)$option_id ."" : '') ."
            order by priority asc;"
          );

          while ($row = database::fetch($query)) {

            if (empty($row['tax_class_id'])) {
              $row['tax_class_id'] = $this->tax_class_id;
            }

            if (empty($row['sku'])) {
              $row['sku'] = $this->sku;
            }

            if (empty($row['weight']) || (float)$row['weight'] == 0) {
              $row['weight'] = $this->weight;
              $row['weight_class'] = $this->weight_class;
            }

            if (empty($row['dim_x'])) {
              $row['dim_x'] = $this->dim_x;
              $row['dim_y'] = $this->dim_y;
              $row['dim_z'] = $this->dim_z;
              $row['dim_class'] = $this->dim_class;
            }

            $row['quantity_available'] = null;

            $reserved_items_query = database::query(
              "select sum(quantity) as total_reserved from ". DB_TABLE_PREFIX ."orders_items oi
              left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
              where oi.product_id = ". (int)$this->_data['id'] ."
              and oi.option_stock_combination = '". database::input($row['combination']) ."'
              and o.order_status_id in (
                select id from ". DB_TABLE_PREFIX ."order_statuses
                where stock_action = 'reserve'
              );"
            );

            $row['reserved_quantity'] = database::fetch($reserved_items_query, 'total_reserved');
            $row['quantity_available'] = $row['quantity'] - $row['reserved_quantity'];

            $row['name'] = [];

            foreach (explode(',', $row['combination']) as $combination) {
              list($group_id, $value_id) = explode('-', $combination);

              if (preg_match('#^0:"?(.*?)"?$#', $value_id, $matches)) {

                foreach (array_keys(language::$languages) as $language_code) {
                  $row['name'][$language_code] = $matches[1];
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
                    if (!is_array(empty($row[$key][$option_value_info['value_id']]))) continue;
                    if (empty($row[$key][$option_value_info['value_id']])) {
                      $row[$key][$option_value_info['value_id']] = $value;
                    }
                  }
                }
              }
            }

            $row['name'] = implode(',', $row['name']);

            $this->_data['options_stock'][$row['id']] = $row;
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

          $this->_data['price'] = 0;

          $products_prices_query = database::query(
            "select * from ". DB_TABLE_PREFIX ."products_prices
            where product_id = ". (int)$this->_data['id'] ."
            limit 1;"
          );

          if (!$product_price = database::fetch($products_prices_query)) return;

          if (!empty($product_price[$this->_currency_code]) && (float)$product_price[$this->_currency_code] != 0) {
            $this->_data['price'] = currency::convert($product_price[$this->_currency_code], $this->_currency_code, settings::get('store_currency_code'));
          } else {
            $this->_data['price'] = $product_price[settings::get('store_currency_code')];
          }

          break;

        case 'quantity_available':
        case 'quantity_reserved':

          $this->_data['quantity_available'] = null;

          $reserved_items_query = database::query(
            "select sum(quantity) as total_reserved from ". DB_TABLE_PREFIX ."orders_items oi
            left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
            where oi.product_id = ". (int)$this->_data['id'] ."
            and o.order_status_id in (
              select id from ". DB_TABLE_PREFIX ."order_statuses
              where stock_action = 'reserve'
            );"
          );

          $this->_data['quantity_reserved'] = database::fetch($reserved_items_query, 'total_reserved');
          $this->_data['quantity_available'] = $this->quantity - $this->_data['quantity_reserved'];

          break;

        case 'quantity_unit':

          $quantity_unit_query = database::query(
            "select id, decimals, separate from ". DB_TABLE_PREFIX ."quantity_units
            where id = ". (int)$this->quantity_unit_id ."
            limit 1;"
          );

          if (!$this->_data['quantity_unit'] = database::fetch($quantity_unit_query)) return;

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

        case 'supplier':

          $this->_data['supplier'] = null;

          if (!empty($this->supplier_id)) {
            $this->_data['supplier'] = reference::supplier($this->supplier_id);
          }

          break;

        case 'sold_out_status':

          $this->_data['sold_out_status'] = [];

          $query = database::query(
            "select id, orderable from ". DB_TABLE_PREFIX ."sold_out_statuses
            where id = ". (int)$this->sold_out_status_id ."
            limit 1;"
          );

          if (!$this->_data['sold_out_status'] = database::fetch($query)) return;

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

          $query = database::query(
            "select * from ". DB_TABLE_PREFIX ."products
            where id = ". (int)$this->_data['id'] ."
            limit 1;"
          );

          if (!$row = database::fetch($query)) return;

          foreach ($row as $key => $value) $this->_data[$key] = $value;

          $this->_data['keywords'] = preg_split('#\s*,\s*#', $this->_data['keywords'], -1, PREG_SPLIT_NO_EMPTY);

          break;
      }
    }

    public function adjust_stock($combination, $quantity) {
      trigger_error('catalog_stock_adjust() is deprecated. Use $ent_product->adjust_quantity()', E_USER_DEPRECATED);
      $product = new ent_product($this->_data['id']);
      return $product->adjust_quantity($quantity, $combination);
    }
  }
