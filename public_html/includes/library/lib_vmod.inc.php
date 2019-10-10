<?php

  class vmod {
    public static $enabled = true;                      // Bool wheither or not to enable this feature
    private static $_modifications = array();           // Array of modifications to apply
    private static $_files_to_modifications = array();  // Array of modifications to apply
    private static $_checked = array();                 // Array of files that have already passed check() and
    private static $_checksums = array();               // Array of checksums for time comparison
    private static $_aliases = array();                 // Array of path aliases

    public static function init() {

      if (!self::$enabled) return;

      self::$_aliases['#^(admin/)#'] = BACKEND_ALIAS . '/';
      self::$_aliases['#^(includes/controllers/ctrl_)#'] = 'includes/entities/ent_';

/*
      $last_modified = null;

    // Compare last modification date
      $folder_last_modified = filemtime();
      if ($folder_last_modified > $last_modified) {
        $last_modified = $folder_last_modified;
      }

      foreach (glob(FS_DIR_APP .'modifications/*.xml') as $file) {
        $file_last_modified = filemtime();
        if ($file_last_modified > $last_modified) {
          $last_modified = $file_last_modified;
        }
      }
*/

    // If no cache is requested by browser
      if (isset($_SERVER['HTTP_CACHE_CONTROL'])) {
        if (strpos(strtolower($_SERVER['HTTP_CACHE_CONTROL']), 'no-cache') !== false) $last_modified = time();
        if (strpos(strtolower($_SERVER['HTTP_CACHE_CONTROL']), 'max-age=0') !== false) $last_modified = time();
      }

    // Get a cached list of modifications
      $modifications_file = FS_DIR_APP . 'cache/modifications.cache';
      if (is_file($modifications_file) && filemtime($modifications_file) >= $last_modified) {
        if ($modifications = file_get_contents($modifications_file)) {
          self::$_modifications = @json_decode($modifications, true);
        }
      }

    // Load modifications from disk
      if (empty(self::$_modifications)) {
        foreach (glob(FS_DIR_APP .'modifications/*.xml') as $file) {
          self::_load_file($file);
        }
      }
    }

  // Return a modified file
    public static function check($file) {

    // Halt if there is nothing to modify
      if (!self::$enabled || empty($file) || empty(self::$_files_to_modifications)) {
        return $file;
      }

      if (is_file($file)) {
        $file = str_replace('\\', '/', realpath($file));
      }

      $short_file = preg_replace('#^('. preg_quote(FS_DIR_APP, '#') .')#', '', $file);
      $modified_file = FS_DIR_APP . 'cache/modifications/' . preg_replace('#[/\\\\]+#', '_', $short_file);

    // Returned already checked file
      if (!empty(self::$_checked[$short_file])) {
        return self::$_checked[$short_file];
      }

    // Gather some info from modifications
      $queue = array();
      $digest = array(filemtime($file));

      foreach (self::$_files_to_modifications as $pattern => $modifications) {
        if (!fnmatch($pattern, $short_file)) continue;

        foreach ($modifications as $modification) {
          $digest[] = strtotime($modification['date_modified']);
        }

        $queue[] = $modifications;
      }

      $checksum = md5(implode('', $digest));

    // Return modified file if checksum matches
      if (!empty(self::$_checksums[$short_file]) && self::$_checksums[$short_file] == $checksum) {
        return self::$_checked[$short_file] = $modified_file;
      }

    // Return original if nothing to modify
      if (empty($queue)) {
        if (is_file($modified_file)) unset($modified_file);
        return self::$_checked[$short_file] = $file;
      }

    // Modify file
      if (is_file($file)) {
        $original = $buffer = preg_replace('#(\r\n|\r|\n)#', PHP_EOL, file_get_contents($file));
      } else {
        $original = $buffer = null;
      }

      foreach ($queue as $modifications) {
        foreach ($modifications as $modification) {

          if (!$vmod = self::$_modifications[$modification['id']]) continue;
          if (!$operations = self::$_modifications[$modification['id']]['files'][$modification['index']]['operations']) continue;

          $tmp = $buffer; $i=0;
          foreach ($operations as $operation) {
            $i++;

            if (!empty($operation['ignoreif']) && preg_match($operation['ignoreif'], $tmp)) {
              continue;
            }

            $found = preg_match_all($operation['find']['pattern'], $tmp, $matches, PREG_OFFSET_CAPTURE);

            if (!$found) {
              switch ($operation['onerror']) {
                case 'abort':
                  trigger_error("Vmod \"{$vmod['title']}\" failed during operation #$i in \"{$short_file}\": Search not found" . PHP_EOL . $operation['find']['pattern'], E_USER_WARNING);
                  $modifications = $recovery;
                  continue 3;
                case 'skip':
                default:
                  continue 2;
              }
            }

            if (!empty($operation['find']['indexes'])) {
              rsort($operation['find']['indexes']);

              foreach ($operation['find']['indexes'] as $index) {
                $index = $index - 1; // [0] is the 1st in computer language

                if ($found > $index) {
                  $tmp = substr_replace($tmp, preg_replace($operation['find']['pattern'], $operation['replace']['insert'], $matches[0][$index][0]), $matches[0][$index][1], strlen($matches[0][$index][0]));
                }
              }

            } else {
              $tmp = preg_replace($operation['find']['pattern'], $operation['replace']['insert'], $tmp);
            }
          }

          $buffer = $tmp;
        }
      }

      if (!is_dir(FS_DIR_APP . 'cache/modifications/')) {
        if (!mkdir(FS_DIR_APP . 'cache/modifications/', 0777)) {
          throw new \Exception('The modifications cache directory could not be created', E_USER_ERROR);
        }
      }

      if (!is_writable(FS_DIR_APP . 'cache/modifications/')) {
        throw new \Exception('The modifications cache directory is not writable', E_USER_ERROR);
      }

    // Return original if nothing was modified
      if ($buffer == $original) {

        return self::$_checked[$short_file] = $file;
      }

    // Write modified file
      file_put_contents($modified_file, $buffer);

      self::$_checksums[$short_file] = $checksum;

      return self::$_checked[$short_file] = $modified_file;
    }

    public static function _load_file($file) {

      try {

        $xml = file_get_contents($file);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->loadXml($xml);

        switch ($dom->documentElement->tagName) {
          case 'vmod': // LiteCart Modification
            $vmod = self::_parse_vmod($xml);
            break;
          case 'modification': // vQmod
            $vmod = self::_parse_vqmod($xml);
            break;
          default:
            throw new \Exception("File ($file) is not a valid vmod or vQmod");
        }

        $vmod['id'] = basename($file);
        $vmod['date_modified'] = filemtime($file);

        self::$_modifications[$vmod['id']] = $vmod;

      // Create cross reference for file patterns
        foreach (array_keys($vmod['files']) as $key) {
          $patterns = explode(',', $vmod['files'][$key]['name']);

          foreach ($patterns as $pattern) {
            $path_file_pattern = $vmod['files'][$key]['path'].$pattern;

            self::$_files_to_modifications[$path_file_pattern][] = array(
              'id' => $vmod['id'],
              'index' => $vmod['files'][$key]['path'].$vmod['files'][$key]['name'],
              'date_modified' => $vmod['date_modified'],
            );
          }
        }

      } catch (\Exception $e) {
        trigger_error("Could not parse file ($file): " . $e->getMessage(), E_USER_ERROR);
      }
    }

    private static function _parse_vmod($xml) {

      $xml = preg_replace('#(\r\n|\r|\n)#', PHP_EOL, $xml);

      $dom = new \DOMDocument('1.0', 'UTF-8');
      $dom->preserveWhiteSpace = false;
      $dom->loadXml($xml);

      if ($dom->documentElement->tagName != 'vmod') {
        throw new \Exception('File is not a valid vmod');
      }

      if (empty($dom->getElementsByTagName('title')->item(0))) {
        throw new \Exception('File is missing the title element');
      }

      $vmod = array(
        //'id' => '',
        'title' => $dom->getElementsByTagName('title')->item(0)->textContent,
        'files' => array(),
      );

      if (empty($dom->getElementsByTagName('file'))) {
        throw new \Exception('File has no defined files to modify');
      }

      foreach ($dom->getElementsByTagName('file') as $file_node) {

        $vmod_file = array(
          'path' => $file_node->getAttribute('path'),
          'name' => $file_node->getAttribute('name'),
          'operations' => array(),
        );

        foreach ($file_node->getElementsByTagName('operation') as $operation) {

          $find_node = $operation->getElementsByTagName('find')->item(0);
          $insert_node = $operation->getElementsByTagName('insert')->item(0);

        // Find
          if (!in_array($insert_node->getAttribute('position'), array('top', 'bottom'))) {
            $find = $find_node->textContent;

            $multiline = preg_match('#^([ \t]+)?\R#', $find) ? PHP_EOL : '';

            if ($find_node->getAttribute('regex') != 'true') {
              if ($find_node->getAttribute('trim') != 'false') $find = trim($find);

              if ($multiline) {
                $find = '#'. preg_replace('#([^\s])\s+([^\s])#m', '$1\s+?$2', preg_quote($find, '#')) .'#';
              } else {
                $find = '#'. preg_quote($find, '#s') .'#';
              }
            }

          // Indexes
            if ($indexes = $find_node->getAttribute('index')) {
              $indexes = preg_split('#, ?#', $indexes);
            }
          }

        // Ignoreif
          if ($ignoreif_node = $operation->getElementsByTagName('ignoreif')->item(0)) {

            if ($ignoreif_node->getAttribute('regex') == 'true') {
              $ignoreif = $ignoreif_node->textContent;
            } else {
              $ignoreif = '#'. preg_quote($ignoreif_node->textContent, '#') .'#';
            }
          }

        // Insert
          $insert = $insert_node->textContent;

          if ($insert_node->getAttribute('regex') != 'true') {

            $multiline = preg_match('#^([ \t]+)?\R#', $insert) ? PHP_EOL : '';

            if ($insert_node->getAttribute('trim') != 'false') {
              $insert = preg_replace('#^(\s+)?\R#', '', $insert); // Trim leading whitespace
              $insert = preg_replace('#\R(\s+)?$#', '', $insert); // Trim trailing whitespace
              $insert = trim($insert, "\r\n");
            }

            switch($position = $insert_node->getAttribute('position')) {

              case 'before':
              case 'prepend':
                $insert = addcslashes($insert . $multiline, '\\$').'$0';
                break;

              case 'after':
              case 'append':
                $insert = '$0'. addcslashes($multiline . $insert, '\\$');
                break;

              case 'top':
                $find = '#^.*$#s';
                $indexes = '';
                $insert = addcslashes($insert . $multiline, '\\$').'$0';
                break;

              case 'bottom':
                $find = '#^.*$#s';
                $indexes = '';
                $insert = '$0'.addcslashes($multiline . $insert, '\\$');
                break;

              case 'replace':
                $insert = addcslashes($multiline . $insert . $multiline, '\\$');
                break;

              default:
                throw new \Exception("Unknown value \"$position\" for attribute position (replace|before|after|ireplace|iafter|ibefore)");
                continue 2;
            }

            $offset = (int)$insert_node->getAttribute('offset');
          }

        // Gather
          $vmod_file['operations'][] = array(
            'onerror' => $operation->getAttribute('onerror'),
            'find' => array(
              'pattern' => $find,
              'indexes' => $indexes,
              'ignoreif' => !empty($ignoreif) ? $ignoreif : null,
            ),
            'replace' => array(
              'insert' => $insert,
              'offset' => !empty($offset) ? $offset : 0,
            ),
          );
        }

        $vmod['files'][$vmod_file['path'].$vmod_file['name']] = $vmod_file;
      }

      return $vmod;
    }

    private static function _parse_vqmod($xml) {

      $xml = preg_replace('#(\r\n|\r|\n)#', PHP_EOL, $xml);

      $dom = new \DOMDocument('1.0', 'UTF-8');
      $dom->preserveWhiteSpace = false;
      $dom->loadXml($xml);

      if ($dom->documentElement->tagName != 'modification') {
        throw new \Exception("File is not a valid vQmod");
      }

      if (empty($dom->getElementsByTagName('id')->item(0))) {
        throw new \Exception("File is missing the id element");
      }

      $mod = array(
        'title' => $dom->getElementsByTagName('id')->item(0)->textContent,
        'files' => array(),
      );

      if (empty($dom->getElementsByTagName('file'))) {
        throw new \Exception("File has no defined files to modify");
      }

      foreach ($dom->getElementsByTagName('file') as $file_node) {

        $mod_file = array(
          'path' => $file_node->getAttribute('path'),
          'name' => $file_node->getAttribute('name'),
          'operations' => array()
        );

        foreach ($file_node->getElementsByTagName('operation') as $operation) {

        // Search
          $search_node = $operation->getElementsByTagName('search')->item(0);
          $search = $search_node->textContent;

          if ($search_node->getAttribute('regex') != 'true') {
            if ($search_node->getAttribute('trim') != 'false') $search = trim($search);
            $search = '#'. preg_quote($search, '#') .'#';
          }

        // Indexes
          if ($indexes = $search_node->getAttribute('index')) {
            $indexes = preg_split('#, ?#', $indexes);
          }

        // Ignoreif
          if ($ignoreif_node = $operation->getElementsByTagName('ignoreif')->item(0)) {

            if ($ignoreif_node->getAttribute('regex') == 'true') {
              $ignoreif = $ignoreif_node->textContent;
            } else {
              $ignoreif = '#'. preg_quote($ignoreif_node->textContent, '#') .'#';
            }
          }

        // Add
          $add_node = $operation->getElementsByTagName('add')->item(0);
          $add = $add_node->textContent;

          if ($add_node->getAttribute('regex') != 'true') {

            $multiline = preg_match('#^([ \t]+)?\R#', $add) ? PHP_EOL : '';

            if ($add_node->getAttribute('trim') != 'false') {
              $add = preg_replace('#^(\s+)?\R#', '', $add); // Trim leading whitespace
              $add = preg_replace('#\R(\s+)?$#', '', $add); // Trim trailing whitespace
              $add = trim($add, "\r\n");
            }

            switch($search_node->getAttribute('position')) {

              case 'before':
                $add = addcslashes($multiline . $add . $multiline, '\\$').'$0';
                break;

              case 'after':
                $add = '$0'. addcslashes($multiline . $add . $multiline, '\\$');
                break;

              case 'top':
                $search = '#^.*$#s';
                $add = addcslashes($add . $multiline, '\\$').'$0';
                break;

              case 'bottom':
                $search = '#^.*$#s';
                $add = '$0'.addcslashes($multiline . $add, '\\$');
                break;

              case 'replace':
                $add = addcslashes($multiline . $add . $multiline, '\\$');
                break;

              default:
                throw new \Exception('Unknown value for attribute position ('. $search_node->getAttribute('position') .')');
                continue 2;
            }

            $offset = (int)$search_node->getAttribute('offset');
          }

        // Gather
          $mod_file['operations'][] = array(
            'onerror' => $operation->getAttribute('onerror'),
            'find' => array(
              'pattern' => $search,
              'indexes' => $indexes,
              'ignoreif' => !empty($ignoreif) ? $ignoreif : null,
            ),
            'replace' => array(
              'insert' => $add,
              'offset' => !empty($offset) ? $offset : 0,
            ),
          );
        }

        $mod['files'][$mod_file['path'].$mod_file['name']] = $mod_file;
      }

      return $mod;
    }
  }
