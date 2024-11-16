<?php

	class ent_campaign {
		public $data;
		public $previous;

		public function __construct($id=null) {

			if ($id) {
				$this->load($id);
			} else {
				$this->reset();
			}
		}

		public function reset() {

			$this->data = [];

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."campaigns;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			$this->data['products']	= [];

			$this->previous = $this->data;
		}

		public function load($id) {

			if (preg_match('#[^0-9]#', $id)) {
				throw new Exception('Invalid campaign id ('. $id .')');
			}

			$this->reset();

			$campaign = database::query(
				"select * from ". DB_TABLE_PREFIX ."campaigns
				". (preg_match('#^[0-9]+$#', $id) ? "where id = ". (int)$id ."" : "") ."
				limit 1;"
			)->fetch();

			if ($campaign) {
				$this->data = array_replace($this->data, array_intersect_key($campaign, $this->data));
			} else {
				throw new Exception('Could not find campaign ('. functions::escape_html($id) .') in the database.');
			}

			$this->data['products'] = database::query(
				"select cp.*, pi.name, pp.`". database::input(settings::get('store_currency_code')) ."` as regular_price
				from ". DB_TABLE_PREFIX ."campaigns_products cp
				left join ". DB_TABLE_PREFIX ."products_prices pp on (pp.product_id = cp.product_id)
				left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = cp.product_id and pi.language_code = '". database::input(language::$selected['code']) ."')
				where cp.campaign_id = ". (int)$this->data['id'] ."
				order by cp.id;"
			)->fetch_all(function($row){

				if ($row['regular_price'] && $row[settings::get('store_currency_code')]) {
					$row['percentage'] = ($row['regular_price'] - $row[settings::get('store_currency_code')]) / $row['regular_price'] * 100;
				} else {
					$row['percentage'] = 0;
				}

				return $row;
			});

			$this->previous = $this->data;
		}

		public function save() {

			if (!$this->data['id']) {

				database::query(
					"insert into ". DB_TABLE_PREFIX ."campaigns
					(id, date_created)
					values (". (int)$this->data['id'] .", '". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."campaigns
				set name = '". database::input($this->data['name']) ."',
					date_valid_from = ". (!empty($this->data['date_valid_from']) ? "'". database::input($this->data['date_valid_from']) ."'" : "null") .",
					date_valid_to = ". (!empty($this->data['date_valid_to']) ? "'". database::input($this->data['date_valid_to']) ."'" : "null") ."
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			database::query(
				"delete from ". DB_TABLE_PREFIX ."campaigns_products
				where campaign_id = ". (int)$this->data['id'] ."
				and product_id not in ('". implode("', '", database::input(array_column($this->data['products'], 'product_id'))) ."');"
			);

			foreach ($this->data['products'] as $key => $product) {

				if (empty($product['product_id'])) {
					database::query(
						"insert into ". DB_TABLE_PREFIX ."campaigns_products
						(campaign_id, product_id)
						values (". (int)$this->data['id'] .", ". (int)$product['product_id'] .");"
					);

					$this->data['products'][$key]['id'] = $product['id'] = database::insert_id();
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."campaigns_products
					set product_id = ". (int)$product['product_id'] .",
					  ". implode(",".PHP_EOL, array_map(function($currency) use ($product) {
							return $currency['code'] ." = ". (!empty($product[$currency['code']]) ? (float)$product[$currency['code']] : 0);
						}, currency::$currencies)) ."
					where id = ". (int)$this->data['id'] ."
					limit 1;"
				);
			}

			$this->previous = $this->data;

			cache::clear_cache('campaign');
		}

		public function delete() {

			database::query(
				"delete from ". DB_TABLE_PREFIX ."campaigns
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->reset();

			cache::clear_cache('campaign');
		}
	}
