<?php

	class ent_stock_transaction {
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
				"show fields from ". DB_TABLE_PREFIX ."stock_transactions;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			$this->data['contents'] = [];

			$this->previous = $this->data;
		}

		public function load($id) {

			if (!preg_match('#^(system|[0-9]+)$#', $id)) {
				throw new Exception('Invalid stock transaction (ID: '. functions::escape_html($id) .')');
			}

			$this->reset();

			if ($id == 'system') {

				$transaction = database::query(
					"select * from ". DB_TABLE_PREFIX ."stock_transactions
					where name like 'System Generated%'
					and date(created_at) = '". date('Y-m-d') ."'
					limit 1;"
				)->fetch();

				if ($transaction) {
					$this->load($transaction['id']);
				} else {
					$this->data['name'] = 'System Generated '. date('Y-m-d');
				}

				return;
			}

			$transaction = database::query(
				"select * from ". DB_TABLE_PREFIX ."stock_transactions
				where id = ". (int)$id ."
				limit 1;"
			)->fetch();

			if ($transaction) {
				$this->data = array_replace($this->data, array_intersect_key($transaction, $this->data));
			} else {
				throw new Error('Could not find stock transaction (ID: '. (int)$id .') in database.');
			}

			$this->data['contents'] = database::query(
				"select stc.*, si.sku, si.quantity, si.backordered, json_value(si.name, '$.". database::input(language::$selected['code']) ."') as name
				from ". DB_TABLE_PREFIX ."stock_transactions_contents stc
				left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = stc.stock_item_id)
				where stc.transaction_id = ". (int)$this->data['id'] .";"
			)->fetch_all();

			$this->previous = $this->data;
		}

		public function save() {

			// Insert/update transaction
			if (!$this->data['id']) {

				database::query(
					"insert into ". DB_TABLE_PREFIX ."stock_transactions
					(name, created_at)
					values ('". database::input($this->data['name']) ."', '". ($this->data['created_at'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."stock_transactions
				set name = '". database::input($this->data['name']) ."',
					description = '". database::input($this->data['description']) ."',
					updated_at = '". ($this->data['updated_at'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			// Revert stock changes
			foreach ($this->previous['contents'] as $previous_content) {
				database::query(
					"update ". DB_TABLE_PREFIX ."stock_items
					set quantity = quantity - ". (float)$previous_content['quantity_adjustment'] .",
						backordered = backordered - ". (!empty($previous_content['backordered']) ? (float)$previous_content['backordered'] : 0) ."
					where id = ". (int)$previous_content['stock_item_id'] ."
					limit 1;"
				);
			}

			// Delete transaction contents
			database::query(
				"delete from ". DB_TABLE_PREFIX ."stock_transactions_contents
				where transaction_id = ". (int)$this->data['id'] ."
				and id not in ('". implode("', '", database::input(array_column($this->data['contents'], 'id'))) ."');"
			);

			// Insert/update transaction contents
			foreach ($this->data['contents'] as $key => $content) {

				if (empty($content['id'])) {

					database::query(
						"insert into ". DB_TABLE_PREFIX ."stock_transactions_contents
						(transaction_id, stock_item_id)
						values (". (int)$this->data['id'] .", ". (int)$content['stock_item_id'] .");"
					);

					$this->data['contents'][$key]['id'] = $content['id'] = database::insert_id();
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."stock_transactions_contents
					set stock_item_id = ". (int)$content['stock_item_id'] .",
						quantity_adjustment = ". (float)$content['quantity_adjustment'] ."
					where id = ". (int)$content['id'] ."
					and transaction_id = ". (int)$this->data['id'] ."
					limit 1;"
				);

				// Commit stock changes
				if (!empty($content['stock_item_id'])) {
					database::query(
						"update ". DB_TABLE_PREFIX ."stock_items
						set quantity = quantity + ". (float)$content['quantity_adjustment'] .",
							backordered = backordered + ". (!empty($content['backordered']) ? (float)$content['backordered'] : 0) ."
						where id = ". (int)$content['stock_item_id'] ."
						limit 1;"
					);
				}
			}

			$this->previous = $this->data;

			cache::clear_cache('stock_option');
			cache::clear_cache('product');
		}

		public function adjust_quantity($stock_item_id, $quantity_adjustment) {

			foreach ($this->data['contents'] as $content) {
				if ($content['stock_item_id'] == $stock_item_id) {
					$content['quantity_adjustment'] += $quantity_adjustment;
					return;
				}
			}

			$this->data['contents'][] = [
				'id' => null,
				'stock_item_id' => $stock_item_id,
				'quantity_adjustment' => $quantity_adjustment,
			];

			$this->save();
		}

		public function delete() {

			if (!$this->data['id']) return;

			database::query(
				"delete st, stc
				from ". DB_TABLE_PREFIX ."stock_transactions st
				left join ". DB_TABLE_PREFIX ."stock_transactions_contents stc on (stc.transaction_id = st.id)
				where st.id = ". (int)$this->data['id'] .";"
			);

			$this->reset();

			cache::clear_cache('stock_option');
			cache::clear_cache('product');
		}
	}
