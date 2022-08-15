<?php

  class ent_vmod {
    public $data;
    public $previous;

    public function __construct($folder_name=null) {

      if (!empty($folder_name)) {
        $folder_name = rtrim($folder_name, '/');
        $this->load($folder_name);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [
        'id' => null,
        'status' => null,
        'folder' => null,
        'location' => null,
        'title' => null,
        'description' => null,
        'version' => null,
        'author' => null,
        'settings' => [],
        'aliases' => [],
        'files' => [],
        'installed' => false,
        'date_updated' => null,
        'date_created' => null,
      ];

      $this->previous = $this->data;
    }

    public function load($folder_name) {

      $this->reset();

      $this->data['folder_name'] = $folder_name;
      if (is_dir('storage://addons/'. $folder_name .'/')) {
        $this->data['folder'] = $folder_name .'/';
      } else if (is_dir('storage://addons/'. $folder_name .'.disabled/')) {
        $this->data['folder'] = $folder_name .'.disabled/';
      } else {
        throw new Exception('Invalid vMod ('. $folder_name .')');
      }

      $this->data['status'] = !preg_match('#\.disabled/$#', $this->data['folder']);
      $this->data['location'] = 'storage://addons/'. $this->data['folder'];

      if (!is_file($this->data['location'] .'vmod.xml')) {
        throw new Exception('Could not find '. $this->data['location'] .'vmod.xml');
      }

      $xml = file_get_contents($this->data['location'] .'vmod.xml');
      $dom = new \DOMDocument('1.0', 'UTF-8');
      $dom->preserveWhiteSpace = false;

      if (!$dom->loadXml($xml)) {
        throw new Exception(libxml_get_errors());
      }

      $this->data['id'] = fallback($dom->getElementsByTagName('title')->item(0)->textContent, functions::format_path_friendly($folder_name));
      $this->data['title'] = fallback($dom->getElementsByTagName('title')->item(0)->textContent, '');
      $this->data['version'] = fallback($dom->getElementsByTagName('version')->item(0)->textContent, date('Y-m-d', filemtime($this->data['location'] .'vmod.xml')));
      $this->data['description'] = fallback($dom->getElementsByTagName('description')->item(0)->textContent, '');
      $this->data['author'] = fallback($dom->getElementsByTagName('author')->item(0)->textContent, '');
      $this->data['date_created'] = date('Y-m-d H:i:s', filectime('storage://addons/'. $this->data['folder'] .'/vmod.xml'));
      $this->data['date_updated'] = date('Y-m-d H:i:s', filemtime('storage://addons/'. $this->data['folder'] .'/vmod.xml'));

      }

      foreach ($dom->getElementsByTagName('alias') as $alias_node) {
        $this->data['aliases'][$alias_node->getAttribute('key')] = $alias_node->getAttribute('value');
      }

      $f = 0;
      foreach ($dom->getElementsByTagName('file') as $file_node) {

        $this->data['files'][$f] = [
          'path' => $file_node->getAttribute('path'),
          'name' => $file_node->getAttribute('name'),
          'operations' => [],
        ];

        $o = 0;
        foreach ($file_node->getElementsByTagName('operation') as $operation_node) {

          $this->data['files'][$f]['operations'][$o] = [
            'find' => [],
            'insert' => [],
            'ignoreif' => $operation_node->getAttribute('onerror'),
          ];

          if ($find_node = $operation_node->getElementsByTagName('find')->item(0)) {

            if ($find_node->getAttribute('trim') != 'false') {
              $find_node->textContent = preg_replace('#^\r?\n?#s', '', $find_node->textContent); // Trim beginning of CDATA
              $find_node->textContent = preg_replace('#\r?\n[\t ]*$#s', '', $find_node->textContent); // Trim end of CDATA
            }

            $this->data['files'][$f]['operations'][$o]['find'] = [
              'content' => $find_node->textContent,
              'regex' => $find_node->getAttribute('regex'),
              'offset-before' => $find_node->getAttribute('offset-before'),
              'offset-after' => $find_node->getAttribute('offset-after'),
              'index' => $find_node->getAttribute('index'),
              'trim' => $find_node->getAttribute('trim'),
            ];
          }

          if ($insert_node = $operation_node->getElementsByTagName('insert')->item(0)) {

            if ($insert_node->getAttribute('trim') != 'false') {
              $insert_node->textContent = preg_replace('#^\r?\n?#s', '', $insert_node->textContent); // Trim beginning of CDATA
              $insert_node->textContent = preg_replace('#\r?\n[\t ]*$#s', '', $insert_node->textContent); // Trim end of CDATA
            }

            $this->data['files'][$f]['operations'][$o]['insert'] = [
              'position' => $insert_node->getAttribute('position'),
              'content' => $insert_node->textContent,
              'regex' => $insert_node->getAttribute('regex'),
              'trim' => $insert_node->getAttribute('trim'),
            ];
          }

          if ($ignoreif_node = $operation_node->getElementsByTagName('ignoreif')->item(0)) {

            if ($ignoreif_node->getAttribute('trim') != 'false') {
              $ignoreif_node->textContent = preg_replace('#^\r?\n?#s', '', $ignoreif_node->textContent); // Trim beginning of CDATA
              $ignoreif_node->textContent = preg_replace('#\r?\n[\t ]*$#s', '', $ignoreif_node->textContent); // Trim end of CDATA
            }

            $this->data['files'][$f]['operations'][$o]['ignoreif'] = [
              'content' => $ignoreif_node->textContent,
              'trim' => $ignoreif_node->getAttribute('trim'),
              'regex' => $ignoreif_node->getAttribute('regex'),
            ];
          }

          $o++;
        }

        $f++;
      }

      $installed_addons = preg_split('#[\r\n]+#', file_get_contents('storage://addons/.installed'), -1, PREG_SPLIT_NO_EMPTY);
      $this->data['installed'] = in_array($this->data['id'], $installed_addons) ? true : false;

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {
        throw new Exception('vMod ID cannot be empty');
      }

      $this->data['folder'] = $this->data['id'] . (empty($this->data['status']) ? '.disabled' : '') . '/';

      if (empty($this->previous['folder'])) {
        mkdir('storage://addons/'. $this->data['folder']);
      } else if ($this->data['folder'] != $this->previous['folder']) {
        rename('storage://addons/'.$this->previous['folder'], 'storage://addons/'.$this->data['folder']);
      }

      $this->data['location'] = 'storage://addons/'.$this->data['folder'];

      $xml = $this->_build_xml();
      file_put_contents($this->data['location'] .'/vmod.xml', $xml);

      $this->previous = $this->data;

      cache::clear_cache('vmods');
    }

    private function _build_xml() {

      $dom = new DomDocument('1.0', 'UTF-8');
      $dom->preserveWhiteSpace = false;
      $dom->formatOutput = true;

      $vmod_node = $dom->createElement('vmod');

      $vmod_node->appendChild( $dom->createElement('id', $this->data['id']) );
      $vmod_node->appendChild( $dom->createElement('title', $this->data['title']) );
      $vmod_node->appendChild( $dom->createElement('version', $this->data['version']) );
      $vmod_node->appendChild( $dom->createElement('description', $this->data['description']) );
      $vmod_node->appendChild( $dom->createElement('author', $this->data['author']) );

      $marketplace_node = $dom->createElement('marketplace');
      $marketplace_node->appendChild( $dom->createElement('addon_id', fallback($this->data['marketplace']['addon_id'], '')) );
      $marketplace_node->appendChild( $dom->createElement('date_expires', fallback($this->data['marketplace']['date_expires'], '')) );

      $vmod_node->appendChild($marketplace_node);

      foreach ($this->data['files'] as $file) {
        $file_node = $dom->createElement('file');

        foreach (['path', 'name'] as $attribute_name) {
          if (!empty($file[$attribute_name])) {
            $attribute = $dom->createAttribute($attribute_name);
            $attribute->value = $file[$attribute_name];
            $file_node->appendChild($attribute);
          }
        }

        foreach ($file['operations'] as $operation) {
          $operation_node = $dom->createElement('operation');

          foreach (['onerror'] as $attribute_name) {
            if (!empty($operation[$attribute_name])) {
              $attribute = $dom->createAttribute($attribute_name);
              $attribute->value = $operation[$attribute_name];
              $operation_node->appendChild($attribute);
            }
          }

        // Find
          $find_node = $dom->createElement('find');

          foreach (['regex', 'trim'] as $attribute_name) {
            if (!empty($operation['find'][$attribute_name])) {
              $attribute = $dom->createAttribute($attribute_name);
              $attribute->value = !empty($operation['find'][$attribute_name]) ? 'true' : 'false';
              $find_node->appendChild($attribute);
            }
          }

          foreach (['offset-before', 'offset-after', 'index'] as $attribute_name) {
            if (!empty($operation['find'][$attribute_name])) {
              $attribute = $dom->createAttribute($attribute_name);
              $attribute->value = $operation['find'][$attribute_name];
              $find_node->appendChild($attribute);
            }
          }

          if ($operation['insert']['regex'] == 'true') {
            $find_node->appendChild( $dom->createCDATASection($operation['find']['content']) );
          } else {
            $find_node->appendChild( $dom->createCDATASection(PHP_EOL . $operation['find']['content'] . PHP_EOL . str_repeat(' ', 6)) );
          }

          $operation_node->appendChild( $find_node );

        // Insert
          $insert_node = $dom->createElement('insert');

          foreach (['regex', 'trim', 'position'] as $attribute_name) {
            if (!empty($operation['insert'][$attribute_name])) {
              $attribute = $dom->createAttribute($attribute_name);
              $attribute->value = $operation['insert'][$attribute_name];
              $insert_node->appendChild($attribute);
            }
          }

          if ($operation['insert']['regex'] == 'true') {
            $insert_node->appendChild( $dom->createCDATASection(@$operation['insert']['content']) );
          } else {
            $insert_node->appendChild( $dom->createCDATASection(PHP_EOL . $operation['insert']['content'] . PHP_EOL . str_repeat(' ', 6)) );
          }

          $operation_node->appendChild( $insert_node );

        // Ignore If
          if (!empty($operation['ignoreif']['content'])) {

            $ignoreif_node = $dom->createElement('ignoreif');

            foreach (['regex', 'trim'] as $attribute_name) {
              if (!empty($operation['ignoreif'][$attribute_name])) {
                $attribute = $dom->createAttribute($attribute_name);
                $attribute->value = $operation['ignoreif'][$attribute_name];
                $ignoreif_node->appendChild($attribute);
              }
            }

            if (@$operation['ignoreif']['regex'] == 'true') {
              $ignoreif_node->appendChild( $dom->createCDATASection($operation['ignoreif']['content']) );
            } else {
              $ignoreif_node->appendChild( $dom->createCDATASection(PHP_EOL . $operation['ignoreif']['content'] . PHP_EOL . str_repeat(' ', 6)) );
            }

            $operation_node->appendChild( $ignoreif_node );
          }

          $file_node->appendChild($operation_node);
        }

        $vmod_node->appendChild( $file_node );
      }

      $dom->appendChild( $vmod_node );

      return $dom->saveXML();
    }

    public function check() {

      $errors = [];

      $tmp_file = functions::file_create_tempfile();

      file_put_contents($tmp_file, $this->_build_xml());

      $vmod = vmod::parse($tmp_file);

      foreach (array_keys($vmod['files']) as $key) {
        $patterns = explode(',', $vmod['files'][$key]['name']);

        foreach ($patterns as $pattern) {
          $path_and_file = $vmod['files'][$key]['path'].$pattern;

        // Apply path aliases
          if (!empty(vmod::$aliases)) {
            $path_and_file = preg_replace(array_keys(vmod::$aliases), array_values(vmod::$aliases), $path_and_file);
          }

          if (!is_file(FS_DIR_APP . $path_and_file) && (empty($vmod['files'][$key]['onerror']) || strtolower($vmod['files'][$key]['onerror']) != 'skip')) {
            $errors[] = 'File does not exist: ' . $path_and_file;
            continue 2;
          }

          $buffer = file_get_contents(FS_DIR_APP . $path_and_file);

          foreach ($vmod['files'][$key]['operations'] as $i => $operation) {

            if (!empty($operation['ignoreif']) && preg_match($operation['ignoreif'], $buffer)) {
              continue;
            }

            $found = preg_match_all($operation['find']['pattern'], $buffer, $matches, PREG_OFFSET_CAPTURE);

            if (!$found) {
              switch ($operation['onerror']) {
                case 'ignore':
                  continue 2;
                case 'abort':
                case 'warning':
                default:
                  $errors[] = "Search not found in operation $i ($path_and_file)";
                  continue 2;
              }
            }

            if (!empty($operation['find']['indexes'])) {
              rsort($operation['find']['indexes']);

              foreach ($operation['find']['indexes'] as $index) {
                $index = $index - 1; // [0] is the 1st in computer language

                if ($found > $index) {
                  $buffer = substr_replace($buffer, preg_replace($operation['find']['pattern'], $operation['insert'], $matches[0][$index][0]), $matches[0][$index][1], strlen($matches[0][$index][0]));
                }
              }

            } else {
              $buffer = preg_replace($operation['find']['pattern'], $operation['insert'], $buffer, -1, $count);

              if (!$count && $operation['onerror'] != 'skip') {
                $errors = "Failed to perform insert for operation $i ($path_and_file)";
                continue;
              }
            }
          }
        }
      }

      return $errors;
    }

    public function delete() {

      if (empty($this->previous['folder'])) return;

      functions::file_delete('storage://addons/' . $this->previous['folder']);

      $this->reset();

      cache::clear_cache('vmods');
    }
  }
