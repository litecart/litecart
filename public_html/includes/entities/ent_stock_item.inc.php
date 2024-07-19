<?php

	class ent_stock_item {
		public $data;
		public $previous;

		public function __construct($stock_item_id=null) {

			if (!empty($stock_item_id)) {
				$this->load((int)$stock_item_id);
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

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."stock_items_info;"
			)->each(function($field){
				if (in_array($field['Field'], ['id', 'stock_item_id', 'language_code'])) return;
				$this->data[$field['Field']] = array_fill_keys(array_keys(language::$languages), database::create_variable($field));
			});

			$this->data['quantity_reserved'] = 0;
			$this->data['quantity_adjustment'] = 0;
			$this->data['references'] = [];

			$this->previous = $this->data;
		}

		public function load($stock_item_id) {

			if (!preg_match('#^[0-9]+$#', $stock_item_id)) {
				throw new Exception('Invalid stock transaction (ID: '. functions::escape_html($stock_item_id) .')');
			}

			$this->reset();

			$stock_item = database::query(
				"select * from ". DB_TABLE_PREFIX ."stock_items
				where id = ". (int)$stock_item_id ."
				limit 1;"
			)->fetch();

			if ($stock_item) {
				$this->data = array_replace($this->data, array_intersect_key($stock_item, $this->data));
			} else {
				trigger_error('Could not find stock item (ID: '. (int)$stock_item_id .') in database.', E_USER_ERROR);
			}

			// Info
			database::query(
				"select * from ". DB_TABLE_PREFIX ."stock_items_info
				 where stock_item_id = ". (int)$this->data['id'] .";"
			)->each(function($info){
				foreach ($info as $key => $value) {
					if (in_array($key, ['id', 'stock_item_id', 'language_code'])) continue;
					$this->data[$key][$info['language_code']] = $value;
				}
			});

			// Reserved Quantity
			$this->data['quantity_reserved'] = database::query(
				"select sum(oi.quantity) as total_reserved from ". DB_TABLE_PREFIX ."orders_items oi
				left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
				where o.order_status_id in (
					select id from ". DB_TABLE_PREFIX ."order_statuses
					where stock_action = 'reserve'
				)
				and oi.stock_item_id = ". (int)$this->data['id'] .";"
			)->fetch('total_reserved');

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
					(sku, mpn, gtin, date_created)
					values ('". database::input($this->data['sku']) ."', '". database::input($this->data['mpn']) ."', '". database::input($this->data['gtin']) ."', '". ($this->data['date_created'] = date('c')) ."');"
				);
				$this->data['id'] = database::insert_id();
			}

		 // Create sku if missing
			if (empty($this->data['sku'])) {
				$i = 1;
				while (true) {
					$this->data['sku'] = $this->data['id'] .'-'. ($this->data['name'][settings::get('store_language_code')] ? strtoupper(substr($this->data['name'][settings::get('store_language_code')], 0, 4)) : 'UNKN') .'-'. $i++;
					if (!database::query(
						"select id from ". DB_TABLE_PREFIX ."stock_items
						where sku = '". database::input($this->data['sku']) ."'
						limit 1;"
					)->num_rows) {
						break;
					}
				}
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."stock_items
				set sku = '". database::input(strtoupper($this->data['sku'])) ."',
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
					date_updated = '". ($this->data['date_updated'] = date('c')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			foreach (array_keys(language::$languages) as $language_code) {
				$stock_items_info_query = database::query(
					"select * from ". DB_TABLE_PREFIX ."stock_items_info
					where stock_item_id = ". (int)$this->data['id'] ."
					and language_code = '". database::input($language_code) ."'
					limit 1;"
				);

				if (!$stock_item_info = database::fetch($stock_items_info_query)) {
					database::query(
						"insert into ". DB_TABLE_PREFIX ."stock_items_info
						(stock_item_id, language_code)
						values (". (int)$this->data['id'] .", '". $language_code ."');"
					);
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."stock_items_info
					set name = '". database::input($this->data['name'][$language_code]) ."'
					where stock_item_id = ". (int)$this->data['id'] ."
					and language_code = '". database::input($language_code) ."'
					limit 1;"
				);
			}

			// If quantity adjustment is set
			if (!empty($this->data['quantity_adjustment']) && $this->data['quantity_adjustment'] != 0) {

				$stock_transaction = new ent_stock_transaction('system');

				foreach ($stock_transaction->data['contents'] as $key => $row) {
					if ($row['stock_item_id'] == $this->data['id']) {
						$stock_transaction->data['contents'][$key]['quantity_adjustment'] += $this->data['quantity_adjustment'];
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

			if (empty($file)) return;

			if (!$this->data['id']) {
				$this->save();
			}

			if (!is_dir('storage://images/stock_items/')){
				mkdir('storage://images/stock_items/', 0777);
			}

			$image = new ent_image($file);

			// 456-12345_Fancy-title.jpg
			$filename = 'stock_items/' . $this->data['id'] .'-'. functions::format_path_friendly($this->data['name'], settings::get('store_language_code')) .'.'. $image->type;

			if (is_file('storage://images/' . $this->data['image'])) {
				unlink('storage://images/' . $this->data['image']);
			}

			functions::image_delete_cache('storage://images/' . $filename);

			if (settings::get('image_downsample_size')) {
				list($width, $height) = preg_split('#\s*,\s*#', settings::get('image_downsample_size'), -1, PREG_SPLIT_NO_EMPTY);
				$image->resample($width, $height, 'FIT_ONLY_BIGGER');
			}

			$image->write('storage://images/' . $filename, 90);

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

			if (empty($source)) return;

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
				set file = ''
				where id = ". (int)$this->data['id'] .";"
			);

			$this->previous['file'] = $this->data['file'] = '';
		}

		public function delete() {

			database::query(
				"delete si, sii, sir, pso
				from ". DB_TABLE_PREFIX ."stock_items si
				left join ". DB_TABLE_PREFIX ."stock_items_info sii on (sii.stock_item_id = si.id)
				left join ". DB_TABLE_PREFIX ."stock_items_references sir on (sir.stock_item_id = si.id)
				left join ". DB_TABLE_PREFIX ."products_stock_options pso on (pso.stock_item_id = si.id)
				where si.id = ". (int)$this->data['id'] .";"
			);

			$this->reset();

			cache::clear_cache('stock_item');
		}
	}
