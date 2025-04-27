<?php

	class ent_vmod {
		public $data;
		public $previous;

		public function __construct($file=null) {

			if ($file) {
				$this->load($file);
			} else {
				$this->reset();
			}
		}

		public function reset() {

			$this->data = [
				'id' => null,
				'file' => null,
				'status' => 0,
				'name' => '',
				'description' => '',
				'version' => '',
				'author' => '',
				'settings' => [],
				'aliases' => [],
				'files' => [],
				'install' => '',
				'uninstall' => '',
				'upgrades' => [],
				'filename' => null,
				'updated_at' => null,
				'created_at' => null,
			];

			$this->previous = $this->data;
		}

		public function load($file) {

			// If absolute path is not provided, search for the file in the vmods directory
			if (!preg_match('#^(\w:)?/#', str_replace('\\', '/', $file))) {
					$file = FS_DIR_STORAGE . 'vmods/' . $file;
			}

			if (!is_file($file)) {
					throw new Exception('Invalid vMod ('. $file .')');
			}

			$this->reset();

			$xml = file_get_contents($file);
			$xml = preg_replace('#(\r\n?|\n)#', PHP_EOL, $xml);

			$dom = new \DOMDocument('1.0', 'UTF-8');
			$dom->preserveWhiteSpace = false;

			if (!$dom->loadXml($xml)) {
				throw new Exception(libxml_get_errors());
			}

			$this->data['id'] = preg_replace('#\.(xml|disabled)?$#', '', basename($file));
			$this->data['status'] = !preg_match('#\.disabled$#', $file) ? '1' : '0';
			$this->data['file'] = $file;
			$this->data['created_at'] = date('Y-m-d H:i:s', filectime($file));
			$this->data['updated_at'] = date('Y-m-d H:i:s', filemtime($file));

			$this->data['name'] = !empty($dom->getElementsByTagName('name')->item(0)) ? $dom->getElementsByTagName('name')->item(0)->textContent : '';
			$this->data['description'] = !empty($dom->getElementsByTagName('description')->item(0)) ? $dom->getElementsByTagName('description')->item(0)->textContent : '';
			$this->data['version'] = !empty($dom->getElementsByTagName('version')->item(0)) ? $dom->getElementsByTagName('version')->item(0)->textContent : '';
			$this->data['author'] = !empty($dom->getElementsByTagName('author')->item(0)) ? $dom->getElementsByTagName('author')->item(0)->textContent : '';

			if ($install_node = $dom->getElementsByTagName('install')->item(0)) {
				$install = $install_node->textContent;
				$install = preg_replace('#^(\r\n?|\n)#s', '', $install); // Trim beginning of CDATA
				$install = preg_replace('#(\r\n?|\n)[\t ]*$#s', '', $install); // Trim end of CDATA
				$this->data['install'] = $install;
			}

			if ($uninstall_node = $dom->getElementsByTagName('uninstall')->item(0)) {
				$uninstall = $uninstall_node->textContent;
				$uninstall = preg_replace('#^(\r\n?|\n)#s', '', $uninstall); // Trim beginning of CDATA
				$uninstall = preg_replace('#(\r\n?|\n)[\t ]*$#s', '', $uninstall); // Trim end of CDATA
				$this->data['uninstall'] = $uninstall;
			}

			foreach ($dom->getElementsByTagName('upgrade') as $upgrade_node) {
				$upgrade = $upgrade_node->textContent;
				$upgrade = preg_replace('#^(\r\n?|\n)#s', '', $upgrade); // Trim beginning of CDATA
				$upgrade = preg_replace('#(\r\n?|\n)[\t ]*$#s', '', $upgrade); // Trim end of CDATA
				$this->data['upgrades'][] = [
					'version' => $upgrade_node->getAttribute('version'),
					'script' => $upgrade,
				];
			}

			foreach ($dom->getElementsByTagName('alias') as $alias_node) {
				$this->data['aliases'][] = [
					'key' => $alias_node->getAttribute('key'),
					'value' => $alias_node->getAttribute('value'),
				];
			}

			foreach ($dom->getElementsByTagName('setting') as $setting_node) {
				$this->data['settings'][] = [
					'title' => $setting_node->getElementsByTagName('title')->item(0)->textContent,
					'description' => $setting_node->getElementsByTagName('description')->item(0)->textContent,
					'function' => $setting_node->getElementsByTagName('function')->item(0)->textContent,
					'key' => $setting_node->getElementsByTagName('key')->item(0)->textContent,
					'default_value' => $setting_node->getElementsByTagName('default_value')->item(0)->textContent,
				];
			}

			$f = 0;
			foreach ($dom->getElementsByTagName('file') as $file_node) {

				$this->data['files'][$f] = [
					'name' => $file_node->getAttribute('name'),
					'operations' => [],
				];

				$o = 0;
				foreach ($file_node->getElementsByTagName('operation') as $operation_node) {

					$this->data['files'][$f]['operations'][$o] = [
						'type' => $operation_node->getAttribute('type'),
						'method' => $operation_node->getAttribute('method'),
						'find' => [],
						'insert' => [],
						'onerror' => $operation_node->getAttribute('onerror'),
					];

					if ($find_node = $operation_node->getElementsByTagName('find')->item(0)) {

						if (in_array($operation_node->getAttribute('type'), ['inline', 'regex'])) {
							$find_node->textContent = trim($find_node->textContent);

						} else if (in_array($operation_node->getAttribute('type'), ['multiline', ''])) {
							$find_node->textContent = preg_replace('#^(\r\n?|\n)?#s', '', $find_node->textContent); // Trim beginning of CDATA
							$find_node->textContent = preg_replace('#(\r\n?|\n)[\t ]*$#s', '', $find_node->textContent); // Trim end of CDATA
						}

						$this->data['files'][$f]['operations'][$o]['find'] = [
							'content' => $find_node->textContent,
							'index' => $find_node->getAttribute('index'),
							'offset-before' => $find_node->getAttribute('offset-before'),
							'offset-after' => $find_node->getAttribute('offset-after'),
						];
					}

					if ($insert_node = $operation_node->getElementsByTagName('insert')->item(0)) {

						if (in_array($operation_node->getAttribute('type'), ['inline', 'regex'])) {
							$insert_node->textContent = trim($insert_node->textContent);

						} else if (in_array($operation_node->getAttribute('type'), ['multiline', ''])) {
							$insert_node->textContent = preg_replace('#^(\r\n?|\n)#s', '', $insert_node->textContent); // Trim beginning of CDATA
							$insert_node->textContent = preg_replace('#(\r\n?|\n)[\t ]*$#s', '', $insert_node->textContent); // Trim end of CDATA
						}

						$this->data['files'][$f]['operations'][$o]['insert'] = [
							'content' => $insert_node->textContent,
						];
					}

					$o++;
				}

				$f++;
			}

			$this->previous = $this->data;
		}

		public function save() {

			if (!empty($this->data['file'])) {
				$this->data['file'] = dirname($this->data['file']) .'/'. basename($this->data['id']) . ($this->data['status'] ? '.xml' : '.disabled');

			} else {

				if (is_file($file = FS_DIR_STORAGE . 'vmods/' . $this->data['id'] . ($this->data['status'] ? '.xml' : '.disabled'))) {
					throw new Exception('vMod already exists');
				}

				$this->data['file'] = $file;
			}

			$dom = new DomDocument('1.0', 'UTF-8');
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = true;

			$vmod_node = $dom->createElement('vmod');

			$vmod_node->appendChild( $dom->createElement('name', $this->data['name']) );
			$vmod_node->appendChild( $dom->createElement('version', $this->data['version']) );
			$vmod_node->appendChild( $dom->createElement('description', $this->data['description']) );
			$vmod_node->appendChild( $dom->createElement('author', $this->data['author']) );

			// Settings
			foreach ($this->data['settings'] as $setting) {
				$setting_node = $dom->createElement('setting');
				$setting_node->appendChild( $dom->createElement('title', $setting['title']) );
				$setting_node->appendChild( $dom->createElement('description', $setting['description']) );
				$setting_node->appendChild( $dom->createElement('key', $setting['key']) );
				$setting_node->appendChild( $dom->createElement('default_value', $setting['default_value']) );
				$setting_node->appendChild( $dom->createElement('function', $setting['function']) );
				$vmod_node->appendChild( $setting_node );
			}

			// Aliases
			foreach ($this->data['aliases'] as $alias) {
				$alias_node = $dom->createElement('alias');

				$attribute = $dom->createAttribute('key');
				$attribute->value = $alias['key'];
				$alias_node->appendChild( $attribute );

				$attribute = $dom->createAttribute('value');
				$attribute->value = $alias['value'];
				$alias_node->appendChild( $attribute );

				$vmod_node->appendChild( $alias_node );
			}

			// Install
			if (!empty($this->data['install'])) {
				$install_node = $dom->createElement('install');
				$install_node->appendChild( $dom->createCDATASection(PHP_EOL . rtrim($this->data['install']) . PHP_EOL . str_repeat(' ', 2)) );
				$vmod_node->appendChild( $install_node );
			}

			// Uninstall
			if (!empty($this->data['uninstall'])) {
				$uninstall_node = $dom->createElement('uninstall');
				$uninstall_node->appendChild( $dom->createCDATASection(PHP_EOL . rtrim($this->data['uninstall']) . PHP_EOL . str_repeat(' ', 2)) );
				$vmod_node->appendChild( $uninstall_node );
			}

			// Upgrade
			foreach ($this->data['upgrades'] as $upgrade) {
				$upgrade_node = $dom->createElement('upgrade');
				$attribute = $dom->createAttribute('version');
				$attribute->value = $upgrade['version'];
				$upgrade_node->appendChild( $attribute );
				$upgrade_node->appendChild( $dom->createCDATASection(PHP_EOL . rtrim($upgrade['script']) . PHP_EOL . str_repeat(' ', 4)) );
				$vmod_node->appendChild( $upgrade_node );
			}

			// Files
			foreach ($this->data['files'] as $file) {

				if (empty($file['operations'])) continue;

				$file_node = $dom->createElement('file');

				$attribute = $dom->createAttribute('name');
				$attribute->value = $file['name'];
				$file_node->appendChild($attribute);

				foreach ($file['operations'] as $operation) {
					$operation_node = $dom->createElement('operation');

					foreach (['method', 'type', 'onerror'] as $attribute_name) {
						if (!empty($operation[$attribute_name])) {
							$attribute = $dom->createAttribute($attribute_name);
							$attribute->value = $operation[$attribute_name];
							$operation_node->appendChild($attribute);
						}
					}

					// Find
					if (!in_array($operation['method'], ['top', 'bottom', 'all'])) {

						$find_node = $dom->createElement('find');

						foreach (['offset-before', 'offset-after', 'index'] as $attribute_name) {
							if (!empty($operation['find'][$attribute_name])) {
								$attribute = $dom->createAttribute($attribute_name);
								$attribute->value = $operation['find'][$attribute_name];
								$find_node->appendChild($attribute);
							}
						}

						if (in_array($operation['type'], ['inline', 'regex'])) {
							$find_node->appendChild( $dom->createCDATASection($operation['find']['content']) );
						} else {
							$find_node->appendChild( $dom->createCDATASection(PHP_EOL . $operation['find']['content'] . PHP_EOL . str_repeat(' ', 6)) );
						}

						$operation_node->appendChild($find_node);
					}

					// Insert
					$insert_node = $dom->createElement('insert');

					if (in_array($operation['type'], ['inline', 'regex'])) {
						$insert_node->appendChild( $dom->createCDATASection($operation['insert']['content']) );
					} else {
						$insert_node->appendChild( $dom->createCDATASection(PHP_EOL . $operation['insert']['content'] . PHP_EOL . str_repeat(' ', 6)) );
					}

					$operation_node->appendChild( $insert_node );

					$file_node->appendChild($operation_node);
				}

				$vmod_node->appendChild( $file_node );
			}

			$dom->appendChild( $vmod_node );

			$xml = preg_replace_callback('#^ +#m', function($m) {
				return str_repeat("\t", ceil(strlen($m[0]) / 4)); // Replace indentation with tabs
			}, $dom->saveXML());

			// Additional pretty printing
			$xml = preg_replace('#( |\t)+(\r\n?|\n)#', '$2', $xml); // Remove trailing whitespace
			$xml = preg_replace('#(\r\n?|\n)#', PHP_EOL, $xml); // Convert line endings
			$xml = preg_replace('#^( +<(alias|setting|install|uninstall|upgrade|file|operation|insert)[^>]*>)#m', PHP_EOL . '$1', $xml); // Add some empty lines
			$xml = preg_replace('#(\r\n?|\n){3,}#', PHP_EOL . PHP_EOL, $xml); // Remove exceeding line breaks

			if (!empty($this->previous['file']) && $this->data['file'] != $this->previous['file']) {
				rename($this->previous['file'], $this->data['file']);
			}

			file_put_contents('storage://vmods/' . $this->data['file'], $xml);

			$this->previous = $this->data;

			cache::clear_cache('vmods');
		}

		public function delete() {

			if (empty($this->previous['file'])) return;

			unlink($this->previous['file']);

			$this->reset();

			cache::clear_cache('vmods');
		}
	}
