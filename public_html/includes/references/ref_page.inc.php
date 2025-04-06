<?php

	class ref_page extends abs_reference_entity {

		protected $_language_codes;

		function __construct($page_id, $language_code=null) {

			if (empty($language_code)) {
				$language_code = language::$selected['code'];
			}

			$this->_data['id'] = (int)$page_id;
			$this->_language_codes = array_unique([
				$language_code,
				settings::get('default_language_code'),
				settings::get('store_language_code'),
			]);
		}

		protected function _load($field) {

			switch($field) {

				case 'parent':

					if (!empty($this->parent_id)) {
						$this->_data['parent'] = reference::page($this->parent_id, $this->_language_codes[0]);
					}

					break;

				case 'path':

					$this->_data['path'] = [$this->id => $this];

					$current = $this;
					while ($current->parent_id) {

						$this->_data['path'][$current->parent_id] = $current->parent;
						$current = $current->parent;
					}

					$this->_data['path'] = array_reverse($this->_data['path'], true);

					break;

				case 'siblings':

					$this->_data['siblings'] = [];

					if (empty($this->parent_id)) return;

					$query = database::query(
						"select id from ". DB_TABLE_PREFIX ."pages
						where status
						and parent_id = ". (int)$this->parent_id ."
						and id != ". (int)$this->_data['id'] .";"
					);

					while ($row = database::fetch($query)) {
						$this->_data['siblings'][$row['id']] = reference::page($row['id'], $this->_language_codes[0]);
					}

					break;

				case 'descendants':

					$this->_data['descendants'] = [];

					$iterator = function($parent_id) use (&$iterator) {

						$descendants = [];

						$pages_query = database::query(
							"select id from ". DB_TABLE_PREFIX ."pages
							where parent_id = ". (int)$parent_id .";"
						);

						while ($page = database::fetch($pages_query)) {
							$descendants[$page['id']] = reference::page($page['id'], $this->_language_codes[0]);
							$descendants += $iterator($page['id']);
						}

						return $descendants;
					};

					$this->_data['descendants'] = $iterator($this->_data['id']);

					break;

				case 'children':

					$this->_data['subpages'] = [];

						database::query(
							"select id, parent_id from ". DB_TABLE_PREFIX ."pages
							where parent_id = ". (int)$this->_data['id'] .";"
						)->each(function($page) {
							$this->_data['subpages'][$page['id']] = reference::page($page['id'], $this->_language_codes[0]);
						});

					break;

				default:

					$page = database::query(
						"select * from ". DB_TABLE_PREFIX ."pages
						where id = ". (int)$this->_data['id'] ."
						limit 1;"
					)->fetch(function($page) {
						foreach ($page as $key => $value) {
							if (in_array($key, ['title', 'content', 'head_title', 'meta_description'])) {
								foreach ($this->_language_codes as $language_code) {
									if (!empty($page[$key])) {
										$this->_data[$key] = $page[$key];
										break;
									}
								}
							}
						}
					});

					if (!$page) return;

					foreach ($page as $key => $value) {
						$this->_data[$key] = $value;
					}

					break;
			}
		}
	}
