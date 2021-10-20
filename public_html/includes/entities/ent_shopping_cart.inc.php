<?php

	class ent_shopping_cart {
		public $data;
		public $previous;

		public function __construct($cart_id=null, $customer_id=null, $create_if_missing=false) {

			if (!empty($cart_id)) {
				$this->load($cart_id, $customer_id, $create_if_missing);
			} else {
				$this->reset();
			}
		}

		public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."shopping_carts;"
      );

      while ($field = database::fetch($fields_query)) {
        switch (true) {
          case (preg_match('#^customer_#', $field['Field'])):
            $this->data['customer'][preg_replace('#^(customer_)#', '', $field['Field'])] = null;
            break;

          case (preg_match('#^shipping_(?!option)#', $field['Field'])):
            $this->data['customer']['shipping_address'][preg_replace('#^(shipping_)#', '', $field['Field'])] = null;
            break;

          case (preg_match('#^shipping_option#', $field['Field'])):
            $this->data['shipping_option'][preg_replace('#^(shipping_option_)#', '', $field['Field'])] = null;
            break;

          default:
            $this->data[$field['Field']] = null;
            break;
        }
      }

      $this->data = array_merge($this->data, [
        'uid' => uniqid(),
        'weight_unit' => settings::get('site_weight_unit'),
        'currency_code' => currency::$selected['code'],
        'currency_value' => currency::$selected['value'],
        'language_code' => language::$selected['code'],
        'items' => [],
        'subtotal' => ['amount' => 0, 'tax' => 0],
      ]);

      $this->previous = $this->data;
		}

		public function load($cart_id, $customer_id='', $create_if_missing=false) {

			if (!preg_match('#^([0-9]+|[a-f0-9]{13})$#', $cart_id)) throw new Exception('Invalid cart (ID: '. $cart_id .')');
			if (!preg_match('#^([0-9]+|0|$)$#', $customer_id)) throw new Exception('Invalid customer ID ('. $customer_id .')');

			$this->reset();

      $sql_where = [];

      if (preg_match('#^[a-f0-9]{13}$#', $cart_id)) {
        $sql_where[] = "uid = '". database::input($cart_id) ."'";
      }

      if (preg_match('#^[0-9]+$#', $cart_id)) {
        $sql_where[] = "id = '". database::input($cart_id) ."'";
      }

      if (preg_match('#^[0-9]+$#', $customer_id)) {
        $sql_where[] = "customer_id = ". (int)$customer_id;
      }

			$shopping_cart_query = database::query(
				"select * from ". DB_TABLE_PREFIX ."shopping_carts
				where ". implode(" or ", $sql_where) ."
        order by id;"
			);

      if ($shopping_cart = database::fetch($shopping_cart_query)) {
        $this->data = array_replace($this->data, array_intersect_key($shopping_cart, $this->data));

        foreach ($shopping_cart as $field => $value) {
          switch (true) {
            case (preg_match('#^customer_#', $field)):
              $this->data['customer'][preg_replace('#^(customer_)#', '', $field)] = $value;
              break;

            case (preg_match('#^shipping_(?!option)#', $field)):
              $this->data['customer']['shipping_address'][preg_replace('#^(shipping_)#', '', $field)] = $value;
              break;

            case (preg_match('#^shipping_option#', $field)):
              $this->data['shipping_option'][preg_replace('#^(shipping_option_)#', '', $field)] = $value;
              break;
          }
        }

      } else {
        if ($create_if_missing) {
          if (preg_match('#^[a-f0-9]{13}$#', $cart_id)) {
          $this->data['uid'] = $cart_id;
          } else if (preg_match('#^[0-9]+$#', $cart_id)) {
            $this->data['id'] = $cart_id;
          }
        } else {
          throw new Exception('Could not find shopping cart in database (ID: '. (int)$cart_id .')');
        }
      }

			$items_query = database::query(
				"select sci.*, p.quantity_min, p.quantity_max, p.quantity_step, qui.name as quantity_unit_name from ". DB_TABLE_PREFIX ."shopping_carts_items sci
        left join ". DB_TABLE_PREFIX ."products p on (p.id = sci.product_id)
        left join ". DB_TABLE_PREFIX ."quantity_units_info qui on (qui.id = p.quantity_unit_id and qui.language_code = '". database::input(language::$selected['code']) ."')
				where sci.cart_id = ". (int)$this->data['id'] ."
        order by sci.id;"
			);

			while ($item = database::fetch($items_query)) {
        $this->data['items'][$item['key']] = $item;
			}

      $this->_calculate_total();

			$this->previous = $this->data;
		}

		public function save() {

      if (empty($this->data['public_key'])) {
        $this->data['public_key'] = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', mt_rand(5, 10))), 0, 32);
      }

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."shopping_carts
          (uid, date_created)
          values ('". database::input($this->data['uid']) ."', '". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );

        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."shopping_carts
        set uid = '". database::input($this->data['uid']) ."',
          customer_id = ". (int)$this->data['customer']['id'] .",
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

			foreach ($this->data['items'] as $key => $item) {

        if (empty($item['id'])) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."shopping_carts_items
            (cart_id)
            values (". (int)$this->data['id'] .");"
          );

          $this->data['items'][$key]['id'] = $item['id'] = database::insert_id();
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."shopping_carts_items
          set cart_id = ". (int)$this->data['id'] .",
            product_id = ". (int)$item['product_id'] .",
            stock_item_id = ". (int)$item['stock_item_id'] .",
            `key` = '". database::input($item['key']) ."',
            name = '". database::input($item['name']) ."',
            image = '". database::input($item['image']) ."',
            sku = '". database::input($item['sku']) ."',
            gtin = '". database::input($item['gtin']) ."',
            taric = '". database::input($item['taric']) ."',
            quantity = ". (float)$item['quantity'] .",
            quantity_unit_id = ". (int)$item['quantity_unit_id'] .",
            discount = ". (float)$item['discount'] .",
            discount_tax = ". (float)$item['discount_tax'] .",
            price = ". (float)$item['price'] .",
            tax = ". (float)$item['tax'] .",
            weight = ". (float)$item['weight'] .",
            weight_unit = '". database::input($item['weight_unit']) ."',
            length = ". (float)$item['length'] .",
            width = ". (float)$item['width'] .",
            height = ". (float)$item['height'] .",
            length_unit = '". database::input($item['length_unit']) ."'
          where id = ". (int)$item['id'] ."
          and cart_id = ". (int)$this->data['id'] ."
          limit 1;"
        );
			}

			$this->previous = $this->data;
		}

    public function add_product($product_id, $stock_item_id='', $quantity=1, $halt_on_error=false) {

      $product = reference::product($product_id);
      $quantity = round((float)$quantity, $product->quantity_unit ? (int)$product->quantity_unit['decimals'] : 0, PHP_ROUND_HALF_UP);

    // Set item key
      if (!empty($product->quantity_unit['separate'])) {
        $item_key = uniqid();
      } else {
        $item_key = crc32(json_encode([$product->id, $stock_item_id]));
      }

			$item = [
        'id' => null,
        'product_id' => (int)$product->id,
        'stock_item_id' => $stock_item_id,
        'key' => $item_key,
        'image' => $product->image,
        'name' => $product->name,
        'code' => $product->code,
        'sku' => $product->sku,
        'gtin' => $product->gtin,
        'taric' => $product->taric,
        'price' => (!empty($product->campaign) && $product->campaign['price'] > 0) ? $product->campaign['price'] : $product->price,
        'discount' => 0,
        'discount_tax' => 0,
        'extras' => 0,
        'tax' => tax::get_tax((!empty($product->campaign) && $product->campaign['price'] > 0) ? $product->campaign['price'] : $product->price, $product->tax_class_id),
        'tax_class_id' => $product->tax_class_id,
        'quantity' => round($quantity, $product->quantity_unit['decimals'], PHP_ROUND_HALF_UP),
        'quantity_unit_id' => $product->quantity_unit['id'],
        'quantity_min' => $product->quantity_min,
        'quantity_max' => $product->quantity_max,
        'quantity_step' => $product->quantity_step,
        'weight' => $product->weight,
        'weight_unit' => $product->weight_unit,
        'length' => $product->length,
        'width' => $product->width,
        'height' => $product->height,
        'length_unit' => $product->length_unit,
        'error' => '',
      ];

    // Validate
      if (!$product->id) {
        throw new Exception(language::translate('error_item_not_a_valid_product', 'The item is not a valid product'));
      }

      if (!$product->status) {
        throw new Exception(language::translate('error_product_currently_not_available_for_purchase', 'The product is currently not available for purchase'));
      }

      if (!empty($product->date_valid_from) && $product->date_valid_from > date('Y-m-d H:i:s')) {
        throw new Exception(strtr(language::translate('error_product_cannot_be_purchased_until_date', 'The product cannot be purchased until %date'), ['%date' => language::strftime(language::$selected['format_date'], strtotime($product->date_valid_from))]));
      }

      if (!empty($product->date_valid_to) && $product->date_valid_to > 1970 && $product->date_valid_to < date('Y-m-d H:i:s')) {
        throw new Exception(strtr(language::translate('error_product_can_no_longer_be_purchased', 'The product can no longer be purchased as of %date'), ['%date' => language::strftime(language::$selected['format_date'], strtotime($product->date_valid_to))]));
      }

      if ($quantity <= 0) {
        throw new Exception(language::translate('error_invalid_item_quantity', 'Invalid item quantity'));
      }

      $available_quantity_after_purchase = $product->quantity - $quantity - (isset($this->data['items'][$item_key]) ? $this->data['items'][$item_key]['quantity'] : 0);

      if ($available_quantity_after_purchase < 0 && empty($product->sold_out_status['orderable'])) {
        throw new Exception(strtr(language::translate('error_only_n_remaining_products_in_stock', 'There are only %quantity remaining products in stock.'), ['%quantity' => round((float)$product->quantity, isset($product->quantity_unit['decimals']) ? (int)$product->quantity_unit['decimals'] : 0)]));
      }

    // Stock Option
      if (!empty($stock_item_id) && array_search($stock_item_id, array_column($product->stock_options, 'stock_item_id', 'id')) === false) {
        throw new Exception(language::translate('error_invalid_stock_option', 'Invalid stock option'));
      }

      if (empty($stock_item_id) && $product->stock_options) {
        throw new Exception(language::translate('error_muset_select_stock_option', 'You must select a stock option'));
      }

      if ($product->stock_options) {

        $stock_option_key = array_search($stock_item_id, array_column($product->stock_options, 'stock_item_id', 'id'));
        $stock_option = &$product->stock_options[$stock_option_key];

        if (!empty($product->sold_out_status) && empty($product->sold_out_status['orderable'])) {
          //var_dump($stock_option->quantity, $quantity, (isset($this->data['items'][$item_key]) ? $this->data['items'][$item_key]['quantity'] : ''));exit;
          if (($stock_option['quantity'] - $quantity - (isset($this->data['items'][$item_key]) ? $this->data['items'][$item_key]['quantity'] : 0)) < 0) {
            throw new Exception(language::translate('error_not_enough_products_in_stock_for_option', 'Not enough products in stock for the selected option') .' ('. $stock_item_id .')');
          }
        }

        if (!empty($stock_option['sku'])) $item['sku'] = $stock_option['sku'];
        if (!empty($stock_option['weight']) && (float)$stock_option['weight'] != 0) $item['weight'] = (float)$stock_option['weight'];
        if (!empty($stock_option['weight_unit'])) $item['weight_unit'] = $stock_option['weight_unit'];
        if (!empty($stock_option['length']) && (float)$stock_option['length'] != 0) $item['length'] = (float)$stock_option['length'];
        if (!empty($stock_option['width']) && (float)$stock_option['width'] != 0) $item['width'] = (float)$stock_option['width'];
        if (!empty($stock_option['height']) && (float)$stock_option['height'] != 0) $item['height'] = (float)$stock_option['height'];
        if (!empty($stock_option['length_unit'])) $item['length_unit'] = $stock_option['length_unit'];
      }

    // Adjust price with extras
      $item['price'] += $item['extras'];
      $item['tax'] += tax::get_tax($item['extras'], $product->tax_class_id);

    // Round currency amount (Gets rid of hidden decimals)
      $item['price'] = currency::round($item['price'], currency::$selected['code']);
      $item['tax'] = currency::round($item['tax'], currency::$selected['code']);

    // Add item or append to existing
      if (!empty($this->data['items'][$item_key])) {
        $this->data['items'][$item_key]['quantity'] += $quantity;
      } else {
        $this->data['items'][$item_key] = $item;
      }

      $this->_calculate_total();

      return true;
    }

    public function update_item($item_key, $quantity) {

      if (!isset($this->data['items'][$item_key])) {
        notices::add('errors', 'The item does not exist in cart.');
        return;
      }

      if ($this->data['items'][$item_key]['quantity'] == $quantity) {
        return;
      }

      if ($quantity <= 0) {
        self::remove($item_key, true);
        return;
      }

    // Re-add quantity for validation
      $item = &$this->data['items'][$item_key];
      $item['quantity'] = 0;

      $this->add($item['product_id'], $quantity, true, $item_key);

      self::_calculate_total();
    }

    public function remove_item($item_key) {

      if (!isset($this->data['items'][$item_key])) return;

      database::query(
        "delete from ". DB_TABLE_PREFIX ."shopping_carts_items
        where cart_uid = '". database::input($this->data['uid']) ."'
        and id = ". (int)$this->data['items'][$item_key]['id'] ."
        limit 1;"
      );

      unset($this->data['items'][$item_key]);

      $this->_calculate_total();
    }

    private function _calculate_total() {

      $this->data['total'] = [
        'items' => 0,
        'value' => 0,
        'tax' => 0,
      ];

      foreach ($this->data['items'] as $item) {
        $num_items = $item['quantity'];

        if (!empty($item['quantity_unit_id']) && reference::quantity_unit($item['quantity_unit_id'])->separate) {
          $num_items = 1;
        }

        $this->data['total']['value'] += $item['price'] * $item['quantity'];
        $this->data['total']['tax'] += $item['tax'] * $item['quantity'];
        $this->data['total']['items'] += $num_items;
      }
    }

		public function delete() {

			if (empty($this->data['id'])) return;

			database::query(
				"delete sc, sci
        from ". DB_TABLE_PREFIX ."shopping_carts sc
        left join ". DB_TABLE_PREFIX ."shopping_carts_items sci on (sci.cart_id = sc.id)
				where sc.id = ". (int)$this->data['id'] .";"
			);

			$this->reset();
		}
	}
