<?php

	class ent_stock_item {
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
				"show fields from ". DB_TABLE_PREFIX ."stock_items;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			$this->data['name'] = array_fill_keys(array_keys(language::$languages), '');
			$this->data['quantity_reserved'] = 0;
			$this->data['quantity_adjustment'] = 0;
			$this->data['references'] = [];

			$this->previous = $this->data;
		}

		public function load($id) {

			$this->reset();

			if (preg_match('#^[0-9]+$#', $id)) {

				$stock_item = database::query(
					"select * from ". DB_TABLE_PREFIX ."stock_items
					where id = ". (int)$id ."
					limit 1;"
				)->fetch();

			} else {

				foreach ([
					'sku',
					'gtin',
					'mpn',
				] as $column) {

					$stock_item = database::query(
						"select * from ". DB_TABLE_PREFIX ."stock_items
						where lower(nullif($column, '')) = '". database::input(strtolower($id)) ."'
						limit 1;"
					)->fetch();

					if ($stock_item) break;
				}
			}

			if (!$stock_item) {
				throw new Error('Could not find stock item (ID: '. (int)$id .') in database.');
			}

			$this->data = array_replace($this->data, array_intersect_key($stock_item, $this->data));

			// Info
			foreach ([
				'name',
			] as $column) {
				$this->data[$column] = json_decode($this->data[$column], true) ?: [];
				$this->data[$column] += array_fill_keys(array_keys(language::$languages), '');
			}

			// Reserved Quantity
			$this->data['quantity_reserved'] = database::query(
				"select sum(ol.quantity * oi.quantity) as quantity_reserved
				from ". DB_TABLE_PREFIX ."orders_items oi
				left join ". DB_TABLE_PREFIX ."orders_lines ol on (ol.id = oi.line_id and ol.order_id = oi.order_id)
				left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
				where o.order_status_id in (
					select id from ". DB_TABLE_PREFIX ."order_statuses
					where stock_action = 'reserve'
				);"
			)->fetch('quantity_reserved');

			// Deposited Quantity
			$this->data['quantity_deposited'] = database::query(
				"select stock_item_id, sum(quantity_adjustment) as quantity_deposited
				from ". DB_TABLE_PREFIX ."stock_transactions_contents
				where stock_item_id = ". (int)$this->data['id'] ."
				group by stock_item_id;"
			)->fetch('quantity_deposited');

			// Withdrawn Quantity
			$this->data['quantity_withdrawn'] = database::query(
				"select oi.stock_item_id, sum(ol.quantity * oi.quantity) as quantity_withdrawn
				from ". DB_TABLE_PREFIX ."orders_items oi
				left join ". DB_TABLE_PREFIX ."orders_lines ol on (ol.id = oi.line_id and ol.order_id = oi.order_id)
				where oi.stock_item_id = ". (int)$this->data['id'] ."
				and oi.order_id in (
					select id from ". DB_TABLE_PREFIX ."orders o
					where order_status_id in (
						select id from ". DB_TABLE_PREFIX ."order_statuses os
						where stock_action = 'withdraw'
					)
				)
				group by oi.stock_item_id;"
			)->fetch('quantity_withdrawn');

			// Expected Quantity
			$this->data['quantity_expected'] = $this->data['quantity_deposited'] - $this->data['quantity_withdrawn'];

			// References
			$this->data['references'] = database::query(
				"select * from ". DB_TABLE_PREFIX ."stock_items_references
				where stock_item_id = ". (int)$this->data['id'] ."
				order by id;"
			)->fetch_all();

			$this->previous = $this->data;
		}

		public function save() {

			if (!$this->data['id']) {

				database::query(
					"insert into ". DB_TABLE_PREFIX ."stock_items
					(sku, mpn, gtin, created_at)
					values ('". database::input($this->data['sku']) ."', '". database::input($this->data['mpn']) ."', '". database::input($this->data['gtin']) ."', '". ($this->data['created_at'] = date('c')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

		 // Create sku if missing
			if (empty($this->data['sku'])) {

				$i = 1;
				$name = 'UNTITLED';
				$language_codes = array_unique(array_merge([language::$selected['code']], [settings::get('store_language_code')], array_keys(language::$languages)));

				foreach ($language_codes as $language_code) {
					if (!empty($this->data['name'][$language_code])) {
						$name = strtoupper(preg_replace('#[^A-Z0-9]#', '', $this->data['name'][$language_code]));
						break;
					}
				}

				do {
					$this->data['sku'] = implode('-', [$this->data['id'], $name, $i++]);
				} while (database::query(
					"select id from ". DB_TABLE_PREFIX ."stock_items
					where sku = '". database::input($this->data['sku']) ."'
					limit 1;"
				)->num_rows);
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."stock_items
				set name = '". database::input(json_encode($this->data['name'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ."',
					sku = '". database::input(strtoupper($this->data['sku'])) ."',
					mpn = '". database::input($this->data['mpn']) ."',
					gtin = '". database::input($this->data['gtin']) ."',
					backordered = ". (float)$this->data['backordered'] .",
					quantity_unit_id = ". (int)$this->data['quantity_unit_id'] .",
					purchase_price = '". (float)$this->data['purchase_price'] ."',
					purchase_price_currency_code = '". database::input($this->data['purchase_price_currency_code']) ."',
					weight = '". (float)$this->data['weight'] ."',
					weight_unit = '". database::input($this->data['weight_unit']) ."',
					length = ". (float)$this->data['length'] .",
					width = ". (float)$this->data['width'] .",
					height = ". (float)$this->data['height'] .",
					length_unit = '". database::input($this->data['length_unit']) ."',
					updated_at = '". ($this->data['updated_at'] = date('c')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			if ($this->previous['quantity'] != $this->data['quantity']) {
				if (!isset($this->data['quantity_adjustment']) ||  $this->data['quantity_adjustment'] == 0) {
					$this->data['quantity_adjustment'] = $this->data['quantity'] - $this->previous['quantity'];
				}
			}

			// If quantity adjustment is set
			if (isset($this->data['quantity_adjustment']) && $this->data['quantity_adjustment'] != 0) {

				$stock_transaction = new ent_stock_transaction('system');

				foreach ($stock_transaction->data['contents'] as $key => $row) {
					if ($row['stock_item_id'] == $this->data['id']) {
						$stock_transaction->data['contents'][$key]['quantity_adjustment'] += (float)$this->data['quantity_adjustment'];
						$updated_existing = true;
						break;
					}
				}

				if (empty($updated_existing)) {
					$stock_transaction->data['contents'][] = [
						'stock_item_id' => $this->data['id'],
						'quantity_adjustment' => $this->data['quantity_adjustment'],
					];
				}

				$stock_transaction->save();

				$this->data['quantity_adjustment'] = 0;
			}

			// References

			database::query(
				"delete from ". DB_TABLE_PREFIX ."stock_items_references
				where stock_item_id = ". (int)$this->data['id'] ."
				and id not in ('". implode("', '", array_column($this->data['references'], 'id')) ."');"
			);

			if (!empty($this->data['references'])) {
				foreach ($this->data['references'] as $key => $reference) {

					if (empty($reference['id'])) {

						database::query(
							"insert into ". DB_TABLE_PREFIX ."stock_items_references
							(stock_item_id, supplier_id)
							values (". (int)$this->data['id'] .", ". (int)$reference['supplier_id'] .");"
						);

						$reference['id'] = $this->data['references'][$key]['id'] = database::insert_id();
					}

					database::query(
						"update ". DB_TABLE_PREFIX ."stock_items_references
						set supplier_id = ". (int)$reference['supplier_id'] .",
							code = '". database::input($reference['code']) ."'
						where stock_item_id = ". (int)$this->data['id'] ."
						and id = ". (int)$reference['id'] ."
						limit 1;"
					);
				}
			}

			$this->previous = $this->data;

			cache::clear_cache('stock_item');
		}

		public function save_image($file) {

			if (!$file) {
				return;
			}

			if (!$this->data['id']) {
				$this->save();
			}

			if (!is_dir('storage://images/stock_items/')){
				mkdir('storage://images/stock_items/', 0777);
			}

			$image = new ent_image($file);

			// 456-12345_Fancy-title.jpg
			$filename = 'stock_items/' . $this->data['id'] .'-'. functions::format_path_friendly($this->data['name'][settings::get('store_language_code')], settings::get('store_language_code')) .'.'. $image->type;

			if (is_file('storage://images/' . $this->data['image'])) {
				unlink('storage://images/' . $this->data['image']);
			}

			functions::image_delete_cache('storage://images/' . $filename);

			if (settings::get('image_downsample_size')) {
				list($width, $height) = functions::string_split(settings::get('image_downsample_size'));
				$image->resample($width, $height, 'FIT_ONLY_BIGGER');
			}

			$image->save('storage://images/' . $filename, 90);

			database::query(
				"update ". DB_TABLE_PREFIX ."stock_items
				set image = '". database::input($filename) ."'
				where id = ". (int)$this->data['id'] .";"
			);

			$this->previous['image'] = $this->data['image'] = $filename;
		}

		public function delete_image() {

			if (!$this->data['id']) return;

			if (is_file('storage://images/' . $this->data['image'])){
				unlink('storage://images/' . $this->data['image']);
			}

			functions::image_delete_cache('storage://images/' . $this->data['image']);

			database::query(
				"update ". DB_TABLE_PREFIX ."brands
				set image = ''
				where id = ". (int)$this->data['id'] .";"
			);

			$this->previous['image'] = $this->data['image'] = '';
		}

		public function save_file($source, $filename, $mime_type) {

			if (!$source) return;

			if (!$this->data['id']) {
				$this->save();
			}

			if (!is_dir('storage://files/')){
				mkdir('storage://files/', 0777);
			}

			if (is_file($this->data['file'])) {
				unlink($this->data['file']);
			}

			$file = 'files/' . $this->data['id'] .'-'. $filename;
			copy($source, FS_DIR_STORAGE . $file);

			$this->previous['file'] = $this->data['file'] = $file;
			$this->previous['filename'] = $this->data['filename'] = $filename;
			$this->previous['mime_type'] = $this->data['mime_type'] = $mime_type;

			database::query(
				"update ". DB_TABLE_PREFIX ."stock_items
				set file = '". database::input($this->data['file']) ."',
					filename = '". database::input($this->data['filename']) ."',
					mime_type = '". database::input($this->data['mime_type']) ."'
				where id = ". (int)$this->data['id'] .";"
			);
		}

		public function delete_file() {

			if (!$this->data['id']) return;

			if (is_file(FS_DIR_STORAGE . $this->data['file'])) {
				unlink(FS_DIR_STORAGE . $this->data['file']);
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."stock_items
				set file = null
				where id = ". (int)$this->data['id'] .";"
			);

			$this->previous['file'] = $this->data['file'] = '';
		}

		public function delete() {

			database::query(
				"delete si, sir, pso
				from ". DB_TABLE_PREFIX ."stock_items si
				left join ". DB_TABLE_PREFIX ."stock_items_references sir on (sir.stock_item_id = si.id)
				left join ". DB_TABLE_PREFIX ."products_stock_options pso on (pso.stock_item_id = si.id)
				where si.id = ". (int)$this->data['id'] .";"
			);

			$this->reset();

			cache::clear_cache('stock_item');
		}
	}
