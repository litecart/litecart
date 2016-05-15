<?php

  class ref_product {

    private $_id;
    private $_currency_code;
    private $_data = array();

    function __construct($product_id, $currency_code=null) {

      $this->_id = (int)$product_id;

      $this->_currency_code = !empty($currency_code) ? $currency_code : currency::$selected['code'];
    }

    public function &__get($name) {

      if (array_key_exists($name, $this->_data)) {
        return $this->_data[$name];
      }

      $this->_data[$name] = null;
      $this->load($name);

      return $this->_data[$name];
    }

    public function &__isset($name) {
      return $this->__get($name);
    }

    public function __set($name, $value) {
      trigger_error('Setting data ('. $name .') is prohibited', E_USER_ERROR);
    }

    private function load($field='') {

      switch($field) {

        case 'name':
        case 'short_description':
        case 'description':
        case 'head_title':
        case 'meta_description':
        case 'attributes':

          $query = database::query(
            "select * from ". DB_TABLE_PRODUCTS_INFO ."
            where product_id = '". (int)$this->_id ."'
            and language_code in ('". implode("', '", array_keys(language::$languages)) ."');"
          );

          $keys = array();
          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) {
              if (in_array($key, array('id', 'product_id', 'language_code'))) continue;
              $keys[] = $key;
              $this->_data[$key][$row['language_code']] = $value;
            }
          }

        // Fix missing translations
            foreach (array_keys(language::$languages) as $language_code) {
            if (empty($this->_data['name'][$language_code])) {
              if (!empty($this->_data['name'][settings::get('default_language_code')])) {
                $this->_data['name'][$language_code] = $this->_data['name'][settings::get('default_language_code')];
              } else {
                $this->_data['name'][$language_code] = '[untitled]';
              }
            }
            foreach ($keys as $key) {
              if (empty($this->_data[$key][$language_code])) {
                if (!empty($this->_data[$key][settings::get('default_language_code')])) {
                  $this->_data[$key][$language_code] = $this->_data[$key][settings::get('default_language_code')];
                } else {
                  $this->_data[$key][$language_code] = '';
                }
              }
            }
          }

          break;

        case 'campaign':

          $this->_data['campaign'] = array();

          $products_campaigns_query = database::query(
            "select * from ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
            where product_id = '". (int)$this->_id ."'
            and (start_date = '0000-00-00 00:00:00' or start_date <= '". date('Y-m-d H:i:s') ."')
            and (end_date = '0000-00-00 00:00:00' or end_date >= '". date('Y-m-d H:i:s') ."')
            order by end_date asc
            limit 1;"
          );
          $products_campaigns = database::fetch($products_campaigns_query);

          if ($products_campaigns[$this->_currency_code] > 0) {
            $this->_data['campaign']['price'] = currency::convert($products_campaigns[$this->_currency_code], $this->_currency_code, settings::get('store_currency_code'));
          } else {
            $this->_data['campaign']['price'] = $products_campaigns[settings::get('store_currency_code')];
          }

          break;

        case 'categories':

          $this->_data['categories'] = array();

          if (count($this->category_ids)) {
            foreach ($this->category_ids as $category_id) {
              $query = database::query(
                "select name, language_code from ". DB_TABLE_CATEGORIES_INFO ."
                where category_id = '". (int)$category_id ."';"
              );

              while ($row = database::fetch($query)) {
                $this->_data['categories'][$category_id][$row['language_code']] = $row['name'];
              }

            // Fix missing translations
              foreach (array('name') as $key) {
                foreach (array_keys(language::$languages) as $language_code) {
                  if (empty($this->_data['categories'][$category_id][$language_code])) {
                    if (!empty($this->_data['categories'][$category_id][settings::get('default_language_code')])) {
                      $this->_data['categories'][$category_id][$language_code] = $this->_data['categories'][$category_id][settings::get('default_language_code')];
                    } else {
                      $this->_data['categories'][$category_id][$language_code] = '[untitled]';
                }
              }
            }
          }
            }
          }

          break;

        case 'delivery_status':

          $this->_data['delivery_status'] = array();

          $query = database::query(
            "select name, language_code from ". DB_TABLE_DELIVERY_STATUSES_INFO ."
            where delivery_status_id = '". (int)$this->_data['delivery_status_id'] ."';"
          );

          while ($row = database::fetch($query)) {
            $this->_data['delivery_status']['name'][$row['language_code']] = $row['name'];
          }

          if (empty($this->_data['delivery_status']['name'])) return;

        // Fix missing translations
          foreach (array('name') as $key) {
            foreach (array_keys(language::$languages) as $language_code) {
              if (empty($this->_data['delivery_status'][$key][$language_code])) {
                if (!empty($this->_data['delivery_status'][$key][settings::get('default_language_code')])) {
                  $this->_data['delivery_status'][$key][$language_code] = $this->_data['delivery_status'][$key][settings::get('default_language_code')];
                } else {
                  $this->_data['delivery_status'][$key][$language_code] = '[untitled]';
            }
          }
            }
          }

          break;

        case 'images':

          $this->_data['images'] = array();

          $query = database::query(
            "select * from ". DB_TABLE_PRODUCTS_IMAGES."
            where product_id = '". (int)$this->_id ."'
            order by priority asc, id asc;"
          );
          while ($row = database::fetch($query)) {
            $this->_data['images'][$row['id']] = $row['filename'];
          }

          break;

        case 'manufacturer':

          $this->_data['manufacturer'] = array();

          $query = database::query(
            "select id, name, image from ". DB_TABLE_MANUFACTURERS ."
            where id = '". (int)$this->manufacturer_id ."'
            limit 1;"
          );
          $this->_data['manufacturer'] = database::fetch($query);

          break;

        case 'options':

          $this->_data['options'] = array();

          $products_options_query = database::query(
            "select * from ". DB_TABLE_PRODUCTS_OPTIONS ."
            where product_id = '". (int)$this->_id ."'
            order by priority;"
          );

          while ($product_option = database::fetch($products_options_query)) {

          // Groups
            if (isset($this->_data['options'][$product_option['group_id']]['function']) == false) {
              $option_group_query = database::query(
                "select * from ". DB_TABLE_OPTION_GROUPS ."
                where id = '". (int)$product_option['group_id'] ."'
                limit 1;"
              );
              $option_group = database::fetch($option_group_query);
              foreach (array('id', 'function', 'required') as $key) {
                $this->_data['options'][$product_option['group_id']][$key] = $option_group[$key];
              }
            }

            if (isset($this->_data['options'][$product_option['group_id']]['name']) == false) {
              $option_group_info_query = database::query(
                "select name, description, language_code from ". DB_TABLE_OPTION_GROUPS_INFO ." pcgi
                where group_id = '". (int)$product_option['group_id'] ."';"
              );
              while ($option_group_info = database::fetch($option_group_info_query)) {
                foreach (array('name', 'description') as $key) {
                  $this->_data['options'][$product_option['group_id']][$key][$option_group_info['language_code']] = $option_group_info[$key];
                }
              }

            // Fix missing translations
              foreach (array_keys(language::$languages) as $language_code) {
                if (empty($this->_data['options'][$product_option['group_id']]['name'][$language_code])) {
                  if (!empty($this->_data['options'][$product_option['group_id']]['name'][settings::get('default_language_code')])) {
                    $this->_data['options'][$product_option['group_id']]['name'][$language_code] = $this->_data['options'][$product_option['group_id']]['name'][settings::get('default_language_code')];
                  } else {
                    $this->_data['options'][$product_option['group_id']]['name'][$language_code] = '[untitled]';
                  }
                }
                if (empty($this->_data['options'][$product_option['group_id']]['description'][$language_code])) {
                  if (!empty($this->_data['options'][$product_option['group_id']]['description'][settings::get('default_language_code')])) {
                    $this->_data['options'][$product_option['group_id']]['description'][$language_code] = $this->_data['options'][$product_option['group_id']]['description'][settings::get('default_language_code')];
                  } else {
                    $this->_data['options'][$product_option['group_id']]['description'][$language_code] = '';
                  }
                }
              }
            }

          // Values
            if (isset($this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']]['value']) == false) {
              $option_value_query = database::query(
                "select * from ". DB_TABLE_OPTION_VALUES ."
                where id = '". (int)$product_option['value_id'] ."'
                limit 1;"
              );
              $option_value = database::fetch($option_value_query);
              foreach (array('id', 'value') as $key) {
                $this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']][$key] = $option_value[$key];
              }
            }

            if (isset($this->_data['options'][$product_option['group_id']]['values']['name']) == false) {
              $option_values_info_query = database::query(
                "select name, language_code from ". DB_TABLE_OPTION_VALUES_INFO ." pcvi
                where value_id = '". (int)$product_option['value_id'] ."';"
              );
              while ($option_value_info = database::fetch($option_values_info_query)) {
                $this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']]['name'][$option_value_info['language_code']] = $option_value_info['name'];
              }

            // Fix missing translations
              foreach (array('name') as $key) {
                foreach (array_keys(language::$languages) as $language_code) {
                  if (empty($this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']][$key][$language_code])) {
                    if (!empty($this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']][$key][settings::get('default_language_code')])) {
                      $this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']][$key][$language_code] = $this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']][$key][settings::get('default_language_code')];
                    } else {
                      $this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']][$key][$language_code] = '[untitled]';
                    }
                  }
                }
              }
            }

            $product_option['price_adjust'] = 0;

            if ((isset($product_option[$this->_currency_code]) && $product_option[$this->_currency_code] != 0) || (isset($product_option[settings::get('store_currency_code')]) && $product_option[settings::get('store_currency_code')] != 0)) {

              switch ($product_option['price_operator']) {
                case '+':
                  if ($product_option[$this->_currency_code] != 0) {
                    $product_option['price_adjust'] = currency::convert($product_option[$this->_currency_code], $this->_currency_code, settings::get('store_currency_code'));
                  } else {
                    $product_option['price_adjust'] = $product_option[settings::get('store_currency_code')];
                  }
                  break;
                case '%':
                  if ($product_option[$this->_currency_code] != 0) {
                    $product_option['price_adjust'] = $this->price * ((float)$product_option[$this->_currency_code] / 100);
                  } else {
                    $product_option['price_adjust'] = $this->price * $product_option[settings::get('store_currency_code')] / 100;
                  }
                  break;
                case '*':
                  if ($product_option[$this->_currency_code] != 0) {
                    $product_option['price_adjust'] = $this->price * $product_option[$this->_currency_code];
                  } else {
                    $product_option['price_adjust'] = $this->price * $product_option[settings::get('store_currency_code')];
                  }
                  break;
                default:
                  trigger_error('Unknown price operator for option', E_USER_WARNING);
                  break;
              }
            }

            $this->_data['options'][$product_option['group_id']]['values'][$product_option['value_id']]['price_adjust'] = $product_option['price_adjust'];
          }

          break;

        case 'options_stock':

          $this->_data['options_stock'] = array();

          $query = database::query(
            "select * from ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ."
            where product_id = '". (int)$this->_id ."'
            ". (!empty($option_id) ? "and id = '". (int)$option_id ."'" : '') ."
            order by priority asc;"
          );

          while ($row = database::fetch($query)) {

            if (empty($row['tax_class_id'])) {
              $row['tax_class_id'] = $this->tax_class_id;
            }

            if (empty($row['sku'])) {
              $row['sku'] = $this->sku;
            }

            if (empty($row['weight']) || $row['weight'] == 0) {
              $row['weight'] = $this->weight;
              $row['weight_class'] = $this->weight_class;
            }

            if (empty($row['dim_x'])) {
              $row['dim_x'] = $this->dim_x;
              $row['dim_y'] = $this->dim_y;
              $row['dim_z'] = $this->dim_z;
              $row['dim_class'] = $this->dim_class;
            }

            $row['name'] = array();

            foreach (explode(',', $row['combination']) as $combination) {
              list($group_id, $value_id) = explode('-', $combination);

              $options_values_query = database::query(
                "select distinct ovi.value_id, ovi.name, ovi.language_code from ". DB_TABLE_OPTION_VALUES_INFO ." ovi
                where ovi.value_id = '". (int)$value_id ."'
                and language_code in ('". implode("', '", array_keys(language::$languages)) ."');"
              );

              while($option_value = database::fetch($options_values_query)) {

                if (isset($row['name'][$option_value['language_code']])) {
                  $row['name'][$option_value['language_code']] .= ', ';
                } else {
                  $row['name'][$option_value['language_code']] = '';
                }
                $row['name'][$option_value['language_code']] .= $option_value['name'];
              }
            }

          // Fix missing translations
            foreach (array('name') as $key) {
              foreach (array_keys(language::$languages) as $language_code) {
                if (empty($row[$key][$language_code])) {
                  if (!empty($row[$key][settings::get('default_language_code')])) {
                    $row[$key][$language_code] = $row[$key][settings::get('default_language_code')];
                  } else {
                    $row[$key][$language_code] = '[untitled]';
                  }
                }
              }
            }

            $this->_data['options_stock'][$row['id']] = $row;
          }

          break;

        case 'price':

          $this->_data['price'] = 0;

          $products_prices_query = database::query(
            "select * from ". DB_TABLE_PRODUCTS_PRICES ."
            where product_id = '". (int)$this->_id ."'
            limit 1;"
          );
          $product_price = database::fetch($products_prices_query);

          if ($product_price[$this->_currency_code] != 0) {
            $this->_data['price'] = currency::convert($product_price[$this->_currency_code], $this->_currency_code, settings::get('store_currency_code'));
          } else {
            $this->_data['price'] = $product_price[settings::get('store_currency_code')];
          }

          break;

        case 'product_group_ids':
        case 'product_groups':

          $this->_data['product_groups'] = array();

          if (count($this->product_group_ids)) {
            foreach ($this->product_group_ids as $pair) {

              list($group_id, $value_id) = explode('-', $pair);

              $query = database::query(
                "select name, language_code from ". DB_TABLE_PRODUCT_GROUPS_INFO ."
                where product_group_id = '". (int)$group_id ."';"
              );
              while ($group = database::fetch($query)) {
                $this->_data['product_groups'][$group_id]['name'][$group['language_code']] = $group['name'];
              }

            // Fix missing translations
              foreach (array('name') as $key) {
                foreach (array_keys(language::$languages) as $language_code) {
                  if (empty($this->_data['product_groups'][$group_id]['name'][$language_code])) {
                    $this->_data['product_groups'][$group_id]['name'][$language_code] = $this->_data['product_groups'][$group_id]['name'][settings::get('default_language_code')];
                  } else {
                    $this->_data['product_groups'][$group_id]['name'][$language_code] = '[untitled]';
                  }
                }
              }

              $query = database::query(
                "select name, language_code from ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
                where product_group_value_id = '". (int)$value_id ."';"
              );
              while ($value = database::fetch($query)) {
                $this->_data['product_groups'][$group_id]['values'][$value_id][$value['language_code']] = $value['name'];
              }

            // Fix missing translations
              foreach (array('name') as $key) {
                foreach (array_keys(language::$languages) as $language_code) {
                  if (empty($this->_data['product_groups'][$group_id]['values'][$value_id][$language_code])) {
                    if (!empty($this->_data['product_groups'][$group_id]['values'][$value_id][settings::get('default_language_code')])) {
                      $this->_data['product_groups'][$group_id]['values'][$value_id][$language_code] = $this->_data['product_groups'][$group_id]['values'][$value_id][settings::get('default_language_code')];
                    } else {
                      $this->_data['product_groups'][$group_id]['values'][$value_id][$language_code] = '[untitled]';
                    }
                  }
                }
              }
            }
          }

          break;

        case 'quantity_unit':

          $this->_data['quantity_unit'] = array(
            'id' => null,
            'decimals' => 0,
            'separate' => false,
            'name' => array(),
          );

          $quantity_unit_query = database::query(
            "select id, decimals, separate from ". DB_TABLE_QUANTITY_UNITS ."
            where id = ". (int)$this->quantity_unit_id ."
            limit 1;"
          );

          if (database::num_rows($quantity_unit_query)) {

            $this->_data['quantity_unit'] = database::fetch($quantity_unit_query);

            $query = database::query(
              "select name, language_code from ". DB_TABLE_QUANTITY_UNITS_INFO ."
              where quantity_unit_id = '". (int)$this->quantity_unit_id ."';"
            );
            while ($info = database::fetch($query)) {
              $this->_data['quantity_unit']['name'][$info['language_code']] = $info['name'];
            }
          }

          foreach (array_keys(language::$languages) as $language_code) {
            if (isset($this->_data['quantity_unit']['name'][$language_code])) continue;
            $this->_data['quantity_unit']['name'][$language_code] = isset($this->_data['quantity_unit']['name'][settings::get('store_language_code')]) ? $this->_data['quantity_unit']['name'][settings::get('store_language_code')] : '';
          }

          break;

        case 'sold_out_status':

          $this->_data['sold_out_status'] = array();

          $query = database::query(
            "select id, orderable from ". DB_TABLE_SOLD_OUT_STATUSES ."
            where id = '". (int)$this->sold_out_status_id ."'
            limit 1;"
          );
          $this->_data['sold_out_status'] = database::fetch($query);

          if (empty($this->_data['sold_out_status'])) return;

          $query = database::query(
            "select name, language_code from ". DB_TABLE_SOLD_OUT_STATUSES_INFO ."
            where sold_out_status_id = '". (int)$this->_data['sold_out_status_id'] ."';"
          );

          while ($row = database::fetch($query)) {
            $this->_data['sold_out_status']['name'][$row['language_code']] = $row['name'];
          }

        // Fix missing translations
          foreach (array('name') as $key) {
            foreach (array_keys(language::$languages) as $language_code) {
              if (empty($this->_data['sold_out_status'][$key][$language_code])) {
                if (!empty($this->_data['sold_out_status'][$key][settings::get('default_language_code')])) {
                  $this->_data['sold_out_status'][$key][$language_code] = $this->_data['sold_out_status'][$key][settings::get('default_language_code')];
                } else {
                  $this->_data['sold_out_status'][$key][$language_code] = '[untitled]';
                }
              }
            }
          }

          break;

        default:

          $query = database::query(
            "select p.*, group_concat(pc.category_id) as categories
            from ". DB_TABLE_PRODUCTS ." p
            left join ". DB_TABLE_PRODUCTS_TO_CATEGORIES . " pc on (pc.product_id = ". (int)$this->_id .")
            where id = '". (int)$this->_id ."'
            group by p.id
            limit 1;"
          );
          $row = database::fetch($query);

          if (database::num_rows($query) == 0) return;

          if (!empty($row['categories'])) {
            $row['category_ids'] = explode(',', $row['categories']);
          } else {
            $row['category_ids'] = array();
          }
          unset($row['categories']);

          if (!empty($row['product_groups'])) {
            $row['product_group_ids'] = explode(',', $row['product_groups']);
          } else {
            $row['product_group_ids'] = array();
          }
          unset($row['product_groups']);

          foreach ($row as $key => $value) $this->_data[$key] = $value;

          break;
      }
    }
  }

?>