<?php

  class vmod {
    public static $enabled = true;                 // Bool whether or not to enable this feature
    private static $aliases = [];                  // Array of path aliases ['pattern' => 'replace']
    private static $_checked = [];                 // Array of files that have already passed check() and
    private static $_checksums = [];               // Array of checksums for time comparison
    private static $_files_to_modifications = [];  // Array of references to modifications
    private static $_modifications = [];           // Array of modifications to apply
    private static $_installed = [];               // Array of installed modifications
    private static $_settings = [];                // Array of modification settings
    public static $time_elapsed = 0;               // Integer of time elapsed during operations

    public static function init() {

      if (!self::$enabled) return;

      $timestamp = microtime(true);

    // Backwards Compatibility
      self::$aliases['#^admin/#'] = BACKEND_ALIAS . '/';
      self::$aliases['#^includes/controllers/ctrl_#'] = 'includes/entities/ent_'; // <2.2.0

      $last_modified = null;

    // Get last modification date for folder
      $folder_last_modified = filemtime(FS_DIR_APP .'vmods/');
      if ($folder_last_modified > $last_modified) {
        $last_modified = $folder_last_modified;
      }

    // Get last modification date modifications
      foreach (glob(FS_DIR_APP .'vmods/*.xml') as $file) {
        if (filemtime($file) > $last_modified) {
          $last_modified = filemtime($file);
        }
      }

    // If no cache is requested by browser
      //if (isset($_SERVER['HTTP_CACHE_CONTROL']) && preg_match('#no-cache#i', $_SERVER['HTTP_CACHE_CONTROL'])) {
      //  $last_modified = time();
      //}

    // Load installed
      $installed_file = FS_DIR_APP . 'vmods/.installed';
      if (is_file($installed_file)) {
        foreach (file($installed_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $vmod_id) {
          self::$_installed[] = $vmod_id;
        }
      }

    // Get modifications from cache
      $cache_file = FS_DIR_APP . 'vmods/.cache/.modifications';
      if (is_file($cache_file) && filemtime($cache_file) > $last_modified) {
        if ($cache = file_get_contents($cache_file)) {
          if ($cache = json_decode($cache, true)) {
            self::$_modifications = $cache['modifications'];
            self::$_files_to_modifications = $cache['index'];
          }
        }
      }

    // Create a list of checked files
      $checked_file = FS_DIR_APP . 'vmods/.cache/.checked';
      if (is_file($checked_file) && filemtime($checked_file) > $last_modified) {
        foreach (file($checked_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
          list($relative_path, $modified_relative_path, $checksum) = explode(';', $line);
          if (is_file(FS_DIR_APP . $relative_path) && is_file(FS_DIR_APP . $modified_relative_path) && filemtime(FS_DIR_APP . $modified_relative_path) > filemtime(FS_DIR_APP . $relative_path)) {
            self::$_checked[$relative_path] = FS_DIR_APP . $modified_relative_path;
            self::$_checksums[$relative_path] = $checksum;
          }
        }
      } else {
        file_put_contents($checked_file, '', LOCK_EX);
      }

    // Load modifications from disk
      if (empty(self::$_modifications)) {
        foreach (glob(FS_DIR_APP . 'vmods/*.xml') as $file) {
          self::load($file);
        }

      // Store modifications to cache
        $serialized = json_encode([
          'modifications' => self::$_modifications,
          'index' => self::$_files_to_modifications,
        //), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        ], JSON_UNESCAPED_SLASHES);

        file_put_contents($cache_file, $serialized, LOCK_EX);
      }

    // Load settings
      if (!is_file(FS_DIR_APP . 'vmods/.settings')) file_put_contents(FS_DIR_APP . 'vmods/.settings', '{}');
      if (!self::$_settings = json_decode(file_get_contents(FS_DIR_APP . 'vmods/.settings'), true)) {
        self::$_settings = [];
      }

      self::$time_elapsed += microtime(true) - $timestamp;
    }

  // Return a modified file
    public static function check($file) {

    // Halt if there is nothing to modify
      if (!self::$enabled || empty($file) || empty(self::$_files_to_modifications)) {
        return $file;
      }

      $timestamp = microtime(true);

      if (!is_file($file)) {
        // check here if there is a modification creating the file
        self::$time_elapsed += microtime(true) - $timestamp;
        return $file;
      } else {
        $file = str_replace('\\', '/', realpath($file));
      }

      $relative_path = preg_replace('#^('. preg_quote(FS_DIR_APP, '#') .')#', '', $file);
      $modified_file = FS_DIR_APP . 'vmods/.cache/' . preg_replace('#[/\\\\]+#', '-', $relative_path);
      $modified_relative_path = 'vmods/.cache/' . preg_replace('#[/\\\\]+#', '-', $relative_path);

    // Returned an already checked file
      if (!empty(self::$_checked[$relative_path]) && is_file(self::$_checked[$relative_path])) {
        self::$time_elapsed += microtime(true) - $timestamp;
        return self::$_checked[$relative_path];
      }

    // Add modifications to queue and calculate checksum
      $queue = [];
      $digest = [filemtime($file)];

      foreach (self::$_files_to_modifications as $pattern => $modifications) {
        if (!fnmatch($pattern, $relative_path)) continue;

        foreach ($modifications as $modification) {
          $digest[] = strtotime($modification['date_modified']);
        }

        $queue[] = $modifications;
      }

      $checksum = md5(implode($digest));

    // Return original file if nothing to modify
      if (empty($queue)) {
        if (is_file($modified_file)) unlink($modified_file);
        self::$time_elapsed += microtime(true) - $timestamp;
        return self::$_checked[$relative_path] = $file;
      }

    // Return modified file if checksum matches
      if (!empty(self::$_checksums[$relative_path]) && !empty(self::$_checked[$relative_path]) && file_exists(FS_DIR_APP . self::$_checked[$relative_path]) && self::$_checksums[$relative_path] == $checksum) {
        self::$time_elapsed += microtime(true) - $timestamp;
        return self::$_checked[$relative_path] = $modified_file;
      }

    // Modify file
      if (is_file($file)) {
        $original = $buffer = file_get_contents($file);
      } else {
        $original = $buffer = null;
      }

      foreach ($queue as $modifications) {
        foreach ($modifications as $modification) {

          if (!$vmod = self::$_modifications[$modification['id']]) continue;
          if (!$operations = self::$_modifications[$modification['id']]['files'][$modification['key']]['operations']) continue;

          $tmp = $buffer;
          foreach ($operations as $i => $operation) {

            if (!empty($operation['ignoreif']) && preg_match($operation['ignoreif'], $tmp)) {
              continue;
            }

            $found = preg_match_all($operation['find']['pattern'], $tmp, $matches, PREG_OFFSET_CAPTURE);

            if (!$found) {
              switch ($operation['onerror']) {
                case 'abort':
                  trigger_error("Modification \"$vmod[title]\" failed during operation #$i in $relative_path: Search not found [ABORTED]", E_USER_WARNING);
                  continue 3;
                case 'ignore':
                  continue 2;
                case 'warning':
                default:
                  trigger_error("Modification \"$vmod[title]\" failed during operation #$i in $relative_path: Search not found", E_USER_WARNING);
                  continue 2;
              }
            }

            if (!empty($operation['find']['indexes'])) {
              rsort($operation['find']['indexes']);

              foreach ($operation['find']['indexes'] as $index) {
                $index = $index - 1; // [0] is the 1st in computer language

                if ($found > $index) {
                  $tmp = substr_replace($tmp, preg_replace($operation['find']['pattern'], $operation['insert'], $matches[0][$index][0]), $matches[0][$index][1], strlen($matches[0][$index][0]));
                }
              }

            } else {
              $tmp = preg_replace($operation['find']['pattern'], $operation['insert'], $tmp, -1, $count);

              if (!$count && $operation['onerror'] != 'skip') {
                trigger_error("Vmod failed to perform insert", E_USER_ERROR);
                continue 2;
              }
            }
          }

          $buffer = $tmp;
        }
      }

    // Create cache folder for modified files if missing
      if (!is_dir(FS_DIR_APP . 'vmods/.cache/')) {
        if (!mkdir(FS_DIR_APP . 'vmods/.cache/', 0777)) {
          throw new \Exception('The modifications cache directory could not be created', E_USER_ERROR);
        }
      }

      if (!is_writable(FS_DIR_APP . 'vmods/.cache/')) {
        throw new \Exception('The modifications cache directory is not writable', E_USER_ERROR);
      }

    // Return original if nothing was modified
      if ($buffer == $original) {
        self::$time_elapsed += microtime(true) - $timestamp;
        return self::$_checked[$relative_path] = $file;
      }

    // Write modified file
      file_put_contents($modified_file, $buffer, LOCK_EX);

      self::$_checked[$relative_path] = $modified_file;
      self::$_checksums[$relative_path] = $checksum;
      file_put_contents(FS_DIR_APP . 'vmods/.cache/.checked', $relative_path .';'. $modified_relative_path .';'. $checksum . PHP_EOL, FILE_APPEND | LOCK_EX);

      self::$time_elapsed += microtime(true) - $timestamp;

      return $modified_file;
    }

    public static function load($file) {

      try {

        $xml = file_get_contents($file);
        $xml = preg_replace('#(\r\n?|\n)#', PHP_EOL, $xml);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;

        if (!$dom->loadXml($xml)) {
          throw new \Exception(libxml_get_last_error());
        }

        switch ($dom->documentElement->tagName) {

          case 'vmod': // vMod
            $vmod = self::parse_vmod($dom, $file);
            break;

          case 'modification': // vQmod
            $vmod = self::parse_vqmod($dom);
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
            $path_and_file = $vmod['files'][$key]['path'].$pattern;

          // Apply path aliases
            if (!empty(self::$aliases)) {
              $path_and_file = preg_replace(array_keys(self::$aliases), array_values(self::$aliases), $path_and_file);
            }

            self::$_files_to_modifications[$path_and_file][] = [
              'id' => $vmod['id'],
              'key' => $key,
              'date_modified' => $vmod['date_modified'],
            ];
          }
        }

      // Run install for previously not installed modifications
        if (!in_array($vmod['id'], self::$_installed)) {

        // Exceute install in an isolated scope
          if (!empty($vmod['install'])) {
            (function(){
              eval(func_get_args()[0]);
            })($vmod['install']);
          }

          file_put_contents(FS_DIR_APP . 'vmods/.installed', $vmod['id'] . PHP_EOL, FILE_APPEND | LOCK_EX);
          self::$_installed[] = $vmod['id'];
        }

      } catch (\Exception $e) {
        trigger_error("Could not parse file ($file): " . $e->getMessage(), E_USER_WARNING);
      }
    }

    public static function parse_vmod($dom, $file) {

      if ($dom->documentElement->tagName != 'vmod') {
        throw new \Exception('File is not a valid vmod');
      }

      if (empty($dom->getElementsByTagName('title')->item(0))) {
        throw new \Exception('File is missing the title element');
      }

      $vmod = [
        'type' => 'vmod',
        'id' => pathinfo($file, PATHINFO_FILENAME),
        'title' => $dom->getElementsByTagName('title')->item(0)->textContent,
        'files' => [],
        'install' => null,
        'settings' => [],
      ];

      if ($dom->getElementsByTagName('install')->length > 0) {
        $vmod['install'] = $dom->getElementsByTagName('install')->item(0)->textContent;
      }

      $aliases = [];
      foreach ($dom->getElementsByTagName('alias') as $alias_node) {
        $aliases[$alias_node->getAttribute('key')] = $alias_node->getAttribute('value');
      }

      if (empty($dom->getElementsByTagName('file'))) {
        throw new \Exception('File has no defined files to modify');
      }

      foreach ($dom->getElementsByTagName('file') as $file_node) {

        $vmod_file = [
          'path' => $file_node->getAttribute('path'),
          'name' => $file_node->getAttribute('name'),
          'operations' => [],
        ];

        foreach ($file_node->getElementsByTagName('operation') as $operation_node) {

        // On Error
          $onerror = $operation_node->getAttribute('onerror');

        // Find
          $find_node = $operation_node->getElementsByTagName('find')->item(0);
          $find = strtr($find_node->textContent, $aliases);

          if ($find_node->getAttribute('regex') == 'true') {
            $find = trim($find);

          } else {

          // Trim
            if ($find_node->getAttribute('trim') != 'false') {
              $find = preg_replace('#^[ \\t]*(\r\n?|\n)?#s', '', $find); // Trim beginning of CDATA
              $find = preg_replace('#(\r\n?|\n)?[ \\t]*$#s', '$1', $find); // Trim end of CDATA
            }

          // Whitespace
            if (preg_match('#[\r\n]#', $find)) {
              $find = preg_split('#(\r\n?|\n)#', $find);
              for ($i=0; $i<count($find); $i++) {
                if ($find[$i] = trim($find[$i])) {
                  $find[$i] = '(?:[ \\t]+)?' . preg_quote($find[$i], '#') . '(?:[ \\t]+)?(?:\r\n?|\n)';
                } else if ($i != count($find)-1) {
                  $find[$i] = '(?:[ \\t]+)?(?:\r\n?|\n)';
                }
              }
              $find = implode($find);
            } else {
              $find = '(?:[ \\t]+)?' . preg_quote(trim($find), '#') . '(?:[ \\t]+)?';
            }

          // Offset
            $offset_before = '(?:.*?(?:\r\n?|\n)){'. (int)$find_node->getAttribute('offset-before') .'}';
            $offset_after = '(?:.*?(?:\r\n?|\n|$)){0,'. (int)$find_node->getAttribute('offset-after') .'}';

          // Glue
            $find = '#'. $offset_before . $find . $offset_after .'#';
          }

        // Indexes
          if ($indexes = $find_node->getAttribute('index')) {
            $indexes = preg_split('#, ?#', $indexes);
          }

        // Ignoreif
          if ($ignoreif_node = $operation_node->getElementsByTagName('ignoreif')->item(0)) {
            $ignoreif = strtr($ignoreif_node->textContent, $aliases);

            if ($ignoreif_node->getAttribute('regex') == 'true') {
              $ignoreif = trim($ignoreif);

            } else {

              if ($ignoreif_node->getAttribute('trim') != 'false') {
                $ignoreif = preg_replace('#^[ \\t]*(\r\n?|\n)?#s', '', $ignoreif); // Trim beginning of CDATA
                $ignoreif = preg_replace('#(\r\n?|\n)?[ \\t]*$#s', '$1', $ignoreif); // Trim end of CDATA
              }

              if (preg_match('#[\r\n]#', $ignoreif)) {
                $ignoreif = preg_split('#(\r\n?|\n)#', $ignoreif);
                for ($i=0; $i<count($ignoreif); $i++) {
                  if ($ignoreif[$i] = trim($ignoreif[$i])) {
                    $ignoreif[$i] = '(?:[ \\t]+)?' . preg_quote($ignoreif[$i], '#') . '(?:[ \\t]+)?(?:\r\n?|\n)';
                  } else if ($i != count($ignoreif)-1) {
                    $ignoreif[$i] = '(?:[ \\t]+)?(?:\r\n?|\n)';
                  }
                }
                $ignoreif = implode($ignoreif);
              } else {
                $ignoreif = '(?:[ \\t]+)?' . preg_quote(trim($ignoreif), '#') . '(?:[ \\t]+)?';
              }
            }
          }

        // Insert
          $insert_node = $operation_node->getElementsByTagName('insert')->item(0);
          $insert = strtr($insert_node->textContent, $aliases);

          if (!empty(self::$_settings[$vmod['id']])) {
            foreach (self::$_settings[$vmod['id']] as $key => $value) {
              $insert = str_replace('{setting:'. $key .'}', $value, $insert);
            }
          }

          if ($insert_node->getAttribute('regex') == 'true') {
            $insert = trim($insert);

          } else {

            if ($insert_node->getAttribute('trim') != 'false') {
              $insert = preg_replace('#^[ \\t]*(\r\n?|\n)?#s', '', $insert); // Trim beginning of CDATA
              $insert = preg_replace('#(\r\n?|\n)?[ \\t]*$#s', '$1', $insert); // Trim end of CDATA
            }

            switch($position = $insert_node->getAttribute('position')) {

              case 'before':
              case 'prepend':
                $insert = addcslashes($insert, '\\$').'$0';
                break;

              case 'after':
              case 'append':
                $insert = '$0'. addcslashes($insert, '\\$');
                break;

              case 'top':
                $find = '#^.*$#s';
                $indexes = '';
                $insert = addcslashes($insert, '\\$').'$0';
                break;

              case 'bottom':
                $find = '#^.*$#s';
                $indexes = '';
                $insert = '$0'.addcslashes($insert, '\\$');
                break;

              case 'replace':
                $insert = addcslashes($insert, '\\$');
                break;

              default:
                throw new \Exception("Unknown value \"$position\" for attribute position (replace|before|after|all)");
                continue 2;
            }
          }

        // Gather
          $vmod_file['operations'][] = [
            'onerror' => $onerror,
            'find' => [
              'pattern' => $find,
              'indexes' => $indexes,
            ],
            'ignoreif' => !empty($ignoreif) ? $ignoreif : false,
            'insert' => $insert,
          ];
        }

        $vmod['files'][$vmod_file['path'].$vmod_file['name']] = $vmod_file;
      }

      return $vmod;
    }

    public static function parse_vqmod($dom) {

      if ($dom->documentElement->tagName != 'modification') {
        throw new \Exception("File is not a valid vQmod");
      }

      if (empty($dom->getElementsByTagName('id')->item(0))) {
        throw new \Exception("File is missing the id element");
      }

      $mod = [
        'type' => 'vqmod',
        'title' => $dom->getElementsByTagName('id')->item(0)->textContent,
        'files' => [],
      ];

      if (empty($dom->getElementsByTagName('file'))) {
        throw new \Exception("File has no defined files to modify");
      }

      foreach ($dom->getElementsByTagName('file') as $file_node) {

        $mod_file = [
          'path' => $file_node->getAttribute('path'),
          'name' => $file_node->getAttribute('name'),
          'operations' => []
        ];

        foreach ($file_node->getElementsByTagName('operation') as $operation_node) {

        // On Error
          switch ($operation_node->getAttribute('error')) {
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

        // Search
          $search_node = $operation_node->getElementsByTagName('search')->item(0);
          $search = $search_node->textContent;

        // Regex
          if ($search_node->getAttribute('regex') == 'true') {
            $search = trim($search);

          } else {

          // Trim
            if ($search_node->getAttribute('trim') != 'false') {
              $search = preg_replace('#^[ \\t]*(\r\n?|\n)?#s', '', $search); // Trim beginning of CDATA
              $search = preg_replace('#(\r\n?|\n)?[ \\t]*$#s', '$1', $search); // Trim end of CDATA
            }

          // Whitespace
            if (!in_array($search_node->getAttribute('position'), ['ibefore', 'iafter'])) {
              $search = preg_split('#(\r\n?|\n)#', $search);
              for ($i=0; $i<count($search); $i++) {
                if ($search[$i] = trim($search[$i])) {
                  $search[$i] = '(?:[ \\t]+)?' . preg_quote($search[$i], '#') . '(?:[ \\t]+)?(?:\r\n?|\n)';
                } else if ($i != count($search)-1) {
                  $search[$i] = '(?:[ \\t]+)?(?:\r\n?|\n)';
                }
              }
              $search = implode($search);
            } else {
              $search = '(?:[ \\t]+)?' . preg_quote(trim($search), '#') . '(?:[ \\t]+)?';
            }

          // Offset
            if ($search_node->getAttribute('offset') && in_array($search_node->getAttribute('position'), ['before', 'after', 'replace'])) {
              switch ($search_node->getAttribute('position')) {
                case 'before':
                  $offset_before = '(?:.*?(?:\r\n?|\n)){'. (int)$search_node->getAttribute('offset') .'}';
                  $offset_after  = '';
                  break;
                case 'after':
                case 'replace':
                  $offset_before = '';
                  $offset_after = '(?:.*?(?:\r\n?|\n|$)){0,'. (int)$search_node->getAttribute('offset') .'}';
                  break;
                default:
                  $offset_before = '';
                  $offset_after = '';
                  break;
              }
              $search = $offset_before . $search . $offset_after;
            }

            $search = '#'. $search .'#';
          }

        // Indexes
          if ($indexes = $search_node->getAttribute('index')) {
            $indexes = preg_split('#, ?#', $indexes);
          }

        // Ignoreif
          if ($ignoreif_node = $operation_node->getElementsByTagName('ignoreif')->item(0)) {
            $ignoreif = $ignoreif_node->textContent;

            if ($ignoreif_node->getAttribute('regex') == 'true') {
              $ignoreif = trim($ignoreif);

            } else {

              if ($ignoreif_node->getAttribute('trim') != 'false') {
                $ignoreif = preg_replace('#^[ \\t]*(\r\n?|\n)?#s', '', $ignoreif); // Trim beginning of CDATA
                $ignoreif = preg_replace('#(\r\n?|\n)?[ \\t]*$#s', '$1', $ignoreif); // Trim end of CDATA
              }

              if (preg_match('#[\r\n]#', $ignoreif)) {
                $ignoreif = preg_split('#(\r\n?|\n)#', $ignoreif);
                for ($i=0; $i<count($ignoreif); $i++) {
                  if ($ignoreif[$i] = trim($ignoreif[$i])) {
                    $ignoreif[$i] = '(?:[ \\t]+)?' . preg_quote($ignoreif[$i], '#') . '(?:[ \\t]+)?(?:\r\n?|\n)';
                  } else if ($i != count($ignoreif)-1) {
                    $ignoreif[$i] = '(?:[ \\t]+)?(?:\r\n?|\n)';
                  }
                }
                $ignoreif = implode($ignoreif);
              } else {
                $ignoreif = '(?:[ \\t]+)?' . preg_quote(trim($ignoreif), '#') . '(?:[ \\t]+)?';
              }
            }
          }

        // Add
          $add_node = $operation_node->getElementsByTagName('add')->item(0);
          $add = $add_node->textContent;

          if ($add_node->getAttribute('regex') == 'true') {
            $add = trim($add);

          } else {

            if ($add_node->getAttribute('trim') != 'false') {
              $add = preg_replace('#^[ \\t]*(\r\n?|\n)?#s', '', $add); // Trim beginning of CDATA
              $add = preg_replace('#(\r\n?|\n)?[ \\t]*$#s', '$1', $add); // Trim end of CDATA
            }

            switch($search_node->getAttribute('position')) {

              case 'before':
              case 'ibefore':
                $add = addcslashes($add, '\\$').'$0';
                break;

              case 'after':
              case 'iafter':
                $add = '$0'. addcslashes($add, '\\$');
                break;

              case 'top':
                $search = '#^.*$#s';
                $indexes = '';
                $add = addcslashes($add, '\\$').'$0';
                break;

              case 'bottom':
                $search = '#^.*$#s';
                $indexes = '';
                $add = '$0'.addcslashes($add, '\\$');
                break;

              case 'replace':
              case 'ireplace':
                $add = addcslashes($add, '\\$');
                break;

              case 'all':
                $search = '#^.*$#s';
                $indexes = '';
                $add = addcslashes($add, '\\$');
                break;

              default:
                throw new \Exception('Unknown value ('. $search_node->getAttribute('position') .') for attribute position (replace|before|after|ireplace|ibefore|iafter)');
                continue 2;
            }
          }

        // Gather
          $mod_file['operations'][] = [
            'onerror' => $onerror,
            'find' => [
              'pattern' => $search,
              'indexes' => $indexes,
            ],
            'ignoreif' => !empty($ignoreif) ? $ignoreif : false,
            'insert' => $add,
          ];
        }

        $mod['files'][$mod_file['path'].$mod_file['name']] = $mod_file;
      }

      return $mod;
    }
  }
