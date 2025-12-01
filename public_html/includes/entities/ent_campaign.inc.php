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

			if (!$campaign) {
				throw new Exception('Could not find campaign ('. f::escape_html($id) .') in the database.');
			}

			$this->data = array_replace($this->data, array_intersect_key($campaign, $this->data));

			$this->data['products'] = database::query(
				"select pp.id, pp.product_id, pp.campaign_id, pp.customer_group_id, pp.geo_zone_id, pp.price,
					json_value(p.name, '$.". database::input(language::$selected['code']) ."') as name,
					json_value(pp_regular.price, '$.". database::input(settings::get('store_currency_code')) ."') as regular_price,
					cg.name as customer_group_name,
					gz.name as geo_zone_name
				from ". DB_TABLE_PREFIX ."products_prices pp
				left join ". DB_TABLE_PREFIX ."products p on (p.id = pp.product_id)
				left join ". DB_TABLE_PREFIX ."products_prices pp_regular on (pp_regular.product_id = pp.product_id and pp_regular.campaign_id is null and pp_regular.customer_group_id is null)
				left join ". DB_TABLE_PREFIX ."customer_groups cg on (cg.id = pp.customer_group_id)
				left join ". DB_TABLE_PREFIX ."geo_zones gz on (gz.id = pp.geo_zone_id)
				where pp.campaign_id = ". (int)$this->data['id'] ."
				order by pp.id;"
			)->fetch_all(function($row){

				$row['price'] = $row['price'] ? json_decode($row['price'], true) : [];

				if ($row['regular_price'] && $row['price'][settings::get('store_currency_code')]) {
					$row['percentage'] = ($row['regular_price'] - $row['price'][settings::get('store_currency_code')]) / $row['regular_price'] * 100;
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
					(id, created_at)
					values (". (int)$this->data['id'] .", '". ($this->data['created_at'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."campaigns
				set name = '". database::input($this->data['name']) ."',
					valid_from = ". (!empty($this->data['valid_from']) ? "'". database::input($this->data['valid_from']) ."'" : "null") .",
					valid_to = ". (!empty($this->data['valid_to']) ? "'". database::input($this->data['valid_to']) ."'" : "null") ."
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			database::query(
				"delete from ". DB_TABLE_PREFIX ."products_prices
				where campaign_id = ". (int)$this->data['id'] ."
				and id not in ('". implode("', '", database::input(array_column($this->data['products'], 'id'))) ."');"
			);

			foreach ($this->data['products'] as $key => $campaign_product) {

				if (empty($campaign_product['id'])) {
					database::query(
						"insert into ". DB_TABLE_PREFIX ."products_prices
						(product_id, campaign_id)
						values (". (int)$campaign_product['product_id'] .", ". (int)$this->data['id'] .");"
					);

					$this->data['products'][$key]['id'] = $campaign_product['id'] = database::insert_id();
				}

				$campaign_product['price'] = array_filter($campaign_product['price']);

				database::query(
					"update ". DB_TABLE_PREFIX ."products_prices
					set product_id = ". (int)$campaign_product['product_id'] .",
						campaign_id = ". (int)$this->data['id'] .",
						customer_group_id = ". (!empty($campaign_product['customer_group_id']) ? (int)$campaign_product['customer_group_id'] : "null") .",
						geo_zone_id = ". (!empty($campaign_product['geo_zone_id']) ? (int)$campaign_product['geo_zone_id'] : "null") .",
						price = ". (!empty($campaign_product['price']) ? "'". database::input(f::format_json($campaign_product['price'])) ."'" : "{}") ."
					where id = ". (int)$campaign_product['id'] ."
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
