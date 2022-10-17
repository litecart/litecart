<?php

  class ent_vmod {
    public $data;
    public $previous;

    public function __construct($filename=null) {

      if (!empty($filename)) {
        $this->load(basename($filename));
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [
        'id' => null,
        'status' => null,
        'name' => null,
        'description' => null,
        'version' => null,
        'author' => null,
        'settings' => [],
        'aliases' => [],
        'files' => [],
				'install' => '',
				'uninstall' => '',
				'upgrades' => [],
        'filename' => null,
        'date_updated' => null,
        'date_created' => null,
      ];

      $this->previous = $this->data;
    }

    public function load($filename) {

      if (!is_file(FS_DIR_STORAGE . 'vmods/'. $filename)) {
        throw new Exception('Invalid vMod ('. $filename .')');
      }

      $this->reset();

      $xml = file_get_contents(FS_DIR_STORAGE . 'vmods/'. $filename);
      $xml = preg_replace('#(\r\n?|\n)#', PHP_EOL, $xml);

      $dom = new \DOMDocument('1.0', 'UTF-8');
      $dom->preserveWhiteSpace = false;

      if (!$dom->loadXml($xml)) {
        throw new Exception(libxml_get_errors());
      }

      $this->data['id'] = preg_replace('#\.(xml|disabled)?$#', '', $filename);
      $this->data['status'] = !preg_match('#\.disabled$#', $filename) ? '1' : '0';
      $this->data['filename'] = $filename;
      $this->data['date_created'] = date('Y-m-d H:i:s', filectime(FS_DIR_STORAGE . 'vmods/' . $filename));
      $this->data['date_updated'] = date('Y-m-d H:i:s', filemtime(FS_DIR_STORAGE . 'vmods/' . $filename));

      switch ($dom->documentElement->tagName) {

        case 'vmod': // LiteCart Modification
          $this->_load_vmod($dom);
          break;

        case 'modification': // vQmod
          $this->_load_vqmod($dom);
          break;

        default:
          throw new \Exception("File ($file) is not a valid vmod or vQmod");
      }

      $this->previous = $this->data;
    }

    private function _load_vmod($dom) {

      $this->data['name'] = !empty($dom->getElementsByTagName('name')) ? $dom->getElementsByTagName('name')->item(0)->textContent : '';
      $this->data['description'] = !empty($dom->getElementsByTagName('description')) ? $dom->getElementsByTagName('description')->item(0)->textContent : '';
      $this->data['version'] = !empty($dom->getElementsByTagName('version')) ? $dom->getElementsByTagName('version')->item(0)->textContent : '';
      $this->data['author'] = !empty($dom->getElementsByTagName('author')) ? $dom->getElementsByTagName('author')->item(0)->textContent : '';

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
    }

    private function _load_vqmod($dom) {

      $this->data['name'] = $dom->getElementsByTagName('id')->item(0)->textContent;
      $this->data['version'] = $dom->getElementsByTagName('version')->item(0)->textContent;
      $this->data['author'] = $dom->getElementsByTagName('author')->item(0)->textContent;

      $f = 0;
      foreach ($dom->getElementsByTagName('file') as $file_node) {

        $patterns = [];
        foreach (explode(',', $file_node->getAttribute('name')) as $pattern) {
          $patterns[] = $file_node->getAttribute('path') . $pattern;
        }

        $this->data['files'][$f] = [
          'name' => implode(',', $patterns),
          'operations' => [],
        ];

        $o = 0;
        foreach ($file_node->getElementsByTagName('operation') as $operation_node) {

          $this->data['files'][$f]['operations'][$o] = [
            'method' => '',
            'find' => [],
            'insert' => [],
            'onerror' => '',
          ];

          switch ($file_node->getAttribute('name')) {
            case 'error':
              $onerror = 'warning';
              break;

            case 'skip':
              $onerror = 'ignore';
              break;

            case 'abort':
            default:
              $onerror = 'cancel';
              break;
          }

          if ($search_node = $operation_node->getElementsByTagName('search')->item(0)) {

            if ($search_node->getAttribute('trim') == '' || filter_var($search_node->getAttribute('trim'), FILTER_VALIDATE_BOOLEAN)) {
              $search_node->textContent = preg_replace('#^(\r\n?|\n)#s', '', $search_node->textContent); // Trim beginning of CDATA
              $search_node->textContent = preg_replace('#(\r\n?|\n)[\t ]*$#s', '', $search_node->textContent); // Trim end of CDATA
            }

            if ($search_node->getAttribute('regex') == 'true') {
              $this->data['files'][$f]['operations'][$o]['type'] = 'regex';
            } else if (in_array($search_node->getAttribute('position'), ['ibefore', 'iafter'])) {
              $this->data['files'][$f]['operations'][$o]['type'] = 'inline';
            } else {
              $this->data['files'][$f]['operations'][$o]['type'] = 'multiline';
            }

            $this->data['files'][$f]['operations'][$o]['method'] = strtr($search_node->getAttribute('position'), [
              'ibefore' => 'before',
              'iafter' => 'after',
            ]);

            $this->data['files'][$f]['operations'][$o]['find'] = [
              'content' => $search_node->textContent,
              'index' => $search_node->getAttribute('index'),
              'offset-before' => ($search_node->getAttribute('position') == 'before') ? (int)$search_node->getAttribute('offset') : 0,
              'offset-after' => ($search_node->getAttribute('position') == 'after') ? (int)$search_node->getAttribute('offset') : 0,
            ];
          }

          if ($add_node = $operation_node->getElementsByTagName('add')->item(0)) {

            if ($add_node->getAttribute('trim') == '' || filter_var($add_node->getAttribute('trim'), FILTER_VALIDATE_BOOLEAN)) {
              $add_node->textContent = preg_replace('#^(\r\n?|\n)#s', '', $add_node->textContent); // Trim beginning of CDATA
              $add_node->textContent = preg_replace('#(\r\n?|\n)[\t ]*$#s', '', $add_node->textContent); // Trim end of CDATA
            }

            $this->data['files'][$f]['operations'][$o]['insert'] = [
              'content' => $add_node->textContent,
            ];
          }

          $o++;
        }

        $f++;
      }
    }

    public function save() {

      $this->data['filename'] = basename($this->data['id']) . (!empty($this->data['status']) ? '.xml' : '.disabled');

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
          if (!in_array($operation['method'], ['top', 'bottom'])) {

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

      $xml = $dom->saveXML();

    // Pretty print
      $xml = preg_replace('#^( +<(alias|setting|install|uninstall|upgrade|file|operation|insert)[^>]*>)#m', PHP_EOL . '$1', $xml);
      $xml = preg_replace('#^(\n|\r\n?){2,}#m', PHP_EOL, $xml);

      if (!empty($this->previous['filename'])) {
         rename(FS_DIR_STORAGE . 'vmods/' . $this->previous['filename'], FS_DIR_STORAGE . 'vmods/' . $this->data['filename']);
      }

      file_put_contents(FS_DIR_STORAGE . 'vmods/' . $this->data['filename'], $xml);

      $this->previous = $this->data;

      cache::clear_cache('vmods');
    }

    public function delete($cleanup=false) {

      if (empty($this->previous['filename'])) return;
      if (!empty($this->data['uninstall'])) {
        $tmp_file = stream_get_meta_data(tmpfile())['uri'];
        file_put_contents($tmp_file, "<?php\r\n" . $this->data['uninstall']);

        (function() {
          include func_get_arg(0);
        })($tmp_file);
      }

      unlink(FS_DIR_STORAGE . 'vmods/' . $this->previous['filename']);

      $this->reset();

      cache::clear_cache('vmods');
    }
  }
