<?php

  class ent_vmod {
    public $data;
    public $previous;

    public function __construct($filename=null) {

      if (!empty($filename)) {
        $this->load($filename);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [
        'filename' => null,
        'id' => null,
        'title' => null,
        'description' => null,
        'version' => null,
        'author' => null,
        'settings' => [],
        'aliases' => [],
        'files' => [],
        'date_updated' => null,
        'date_created' => null,
      ];

      $this->previous = $this->data;
    }

    public function load($filename) {

      if (!is_file(FS_DIR_STORAGE . 'vmods/'. $filename)) throw new Exception('Invalid vmod ('. $filename .')');

      $this->reset();

      $xml = file_get_contents(FS_DIR_STORAGE . 'vmods/'. $filename);
      $xml = preg_replace('#(\r\n?|\n)#', PHP_EOL, $xml);

      $dom = new \DOMDocument('1.0', 'UTF-8');
      $dom->preserveWhiteSpace = false;

      if (!$dom->loadXml($xml)) {
        throw new Exception(libxml_get_errors());
      }

      $this->data['filename'] = $filename;
      $this->data['id'] = preg_replace('#\.(xml|disabled)$#', '', $filename);
      $this->data['date_created'] = date('Y-m-d H:i:s', filectime(FS_DIR_STORAGE . 'vmods/' . $filename));
      $this->data['date_updated'] = date('Y-m-d H:i:s', filemtime(FS_DIR_STORAGE . 'vmods/' . $filename));

      switch ($dom->documentElement->tagName) {

        case 'vmod': // LiteCart Modification
          $vmod = $this->_load_vmod($dom);
          break;

        case 'modification': // vQmod
          $vmod = $this->_load_vqmod($dom);
          break;

        default:
          throw new \Exception("File ($file) is not a valid vmod or vQmod");
      }

      $this->previous = $this->data;
    }

    private function _load_vmod($dom) {

      $this->data['title'] = !empty($dom->getElementsByTagName('title')->item(0)) ? $dom->getElementsByTagName('title')->item(0)->textContent : '';
      $this->data['description'] = !empty($dom->getElementsByTagName('description')->item(0)) ? $dom->getElementsByTagName('description')->item(0)->textContent : '';
      $this->data['version'] = !empty($dom->getElementsByTagName('version')->item(0)) ? $dom->getElementsByTagName('version')->item(0)->textContent : '';
      $this->data['author'] = !empty($dom->getElementsByTagName('author')->item(0)) ? $dom->getElementsByTagName('author')->item(0)->textContent : '';

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
    }

    private function _load_vqmod($dom) {

      $this->data['title'] = $dom->getElementsByTagName('id')->item(0)->textContent;
      $this->data['version'] = $dom->getElementsByTagName('version')->item(0)->textContent;
      $this->data['author'] = $dom->getElementsByTagName('author')->item(0)->textContent;

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
            'ignoreif' => [],
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

            if ($search_node->getAttribute('trim') != 'false') {
              $search_node->textContent = preg_replace('#^\r?\n?#s', '', $search_node->textContent); // Trim beginning of CDATA
              $search_node->textContent = preg_replace('#\r?\n[\t ]*$#s', '', $search_node->textContent); // Trim end of CDATA
            }

            $this->data['files'][$f]['operations'][$o]['find'] = [
              'content' => $search_node->textContent,
              'regex' => $search_node->getAttribute('regex'),
              'index' => $search_node->getAttribute('index'),
              'trim' => $search_node->getAttribute('trim'),
            ];

            if ($search_node->getAttribute('position') == 'before') {
              $this->data['files'][$f]['operations'][$o]['find'] = [
                'offset-before' => $search_node->getAttribute('offset'),
                'offset-after' => '',
              ];
            } else {
              $this->data['files'][$f]['operations'][$o]['find'] = [
                'offset-before' => '',
                'offset-after' => $search_node->getAttribute('offset'),
              ];
            }
          }

          if ($add_node = $operation_node->getElementsByTagName('add')->item(0)) {

            if ($add_node->getAttribute('trim') != 'false') {
              $add_node->textContent = preg_replace('#^\r?\n?#s', '', $add_node->textContent); // Trim beginning of CDATA
              $add_node->textContent = preg_replace('#\r?\n[\t ]*$#s', '', $add_node->textContent); // Trim end of CDATA
            }

            $this->data['files'][$f]['operations'][$o]['insert'] = [
              'position' => $add_node->getAttribute('position'),
              'content' => $add_node->textContent,
              'regex' => $add_node->getAttribute('regex'),
              'trim' => $add_node->getAttribute('trim'),
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
    }

    public function save() {

      $dom = new DomDocument('1.0', 'UTF-8');
      $dom->preserveWhiteSpace = false;
      $dom->formatOutput = true;

      $vmod_node = $dom->createElement('vmod');

      $vmod_node->appendChild( $dom->createElement('title', $this->data['title']) );
      $vmod_node->appendChild( $dom->createElement('description', $this->data['description']) );
      $vmod_node->appendChild( $dom->createElement('version', $this->data['version']) );
      $vmod_node->appendChild( $dom->createElement('author', $this->data['author']) );

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

          foreach (['regex', 'trim', 'offset-before', 'offset-after', 'index'] as $attribute_name) {
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

      if (!empty($previous->data['filename'])) unlink(FS_DIR_STORAGE . 'vmods/' . $previous->data['filename']);

      $dom->save(FS_DIR_STORAGE . 'vmods/' . $this->data['filename']);

      $this->previous = $this->data;

      cache::clear_cache('vmods');
    }

    public function delete() {

      unlink(FS_DIR_STORAGE . 'vmods/' . $this->previous['filename']);

      $this->reset();

      cache::clear_cache('vmods');
    }
  }
