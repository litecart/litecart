<?php

  /**
   * VQMod (LiteCart Edition)
   * @description Main Object used
   */
  abstract class VQMod {
    public static $_vqversion = '2.6.4';        // Current version number

    private static $_debug = false;             // Debug mode
    private static $_modFileList = array();     // Array of xml files
    private static $_mods = array();            // Array of modifications to apply
    private static $_filesModded = array();     // Array of already modified files
    private static $_doNotMod = array();        // Array of files not to apply modifications to
    private static $_cwd = '';                  // Current working directory path
    private static $_folderChecks = false;      // Flag for already checked log/cache folders exist
    private static $_cachePathFull = '';        // Full cache folder path
    private static $_lastModifiedTime = 0;      // Integer representing the last time anything was modified

    public static $vqCachePath = 'vqmod/vqcache/';             // Relative path to cache file directory
    public static $modCache = 'vqmod/mods.cache';              // Relative path to serialized mods array cache file
    public static $checkedCache = 'vqmod/checked.cache';       // Relative path to already checked files array cache file
    public static $protectedFilelist = 'vqmod/vqprotect.txt';  // Relative path to protected files array cache file
    public static $fileModding = null;                        // Reference to the current file being modified by vQmod for logging
    public static $replaces = array();                         // Array of regex replaces to perform on file paths array(search => replace)

    /**
     * VQMod::bootup()
     *
     * @param bool $path File path to use
     * @return null
     * @description Startup of VQMod
     */
    public static function bootup($path = false) {

      if (!class_exists('DOMDocument', false)) {
        trigger_error(__METHOD__.' - vQmod requires the PHP DOMDocument extension', E_USER_ERROR);
      }

      if (isset($_GET['debug'])) {
        self::$_debug = true;
      }

      //if (isset($_SERVER['HTTP_CACHE_CONTROL'])) {
      //  if (strpos(strtolower($_SERVER['HTTP_CACHE_CONTROL']), 'no-cache') !== false) self::$_debug = true;
      //  if (strpos(strtolower($_SERVER['HTTP_CACHE_CONTROL']), 'max-age=0') !== false) self::$_debug = true;
      //}

      if (!$path) $path = dirname(__DIR__);
      self::$_cwd = self::_realpath($path);

      // Create cache folder if it doesn't exist
      $cache_folder = self::path(self::$vqCachePath, true);
      if (!is_dir($cache_folder)) {
        if (!mkdir($cache_folder)) trigger_error(__METHOD__.' - Cannot create directory (' . $cache_folder . ')', E_USER_ERROR);
      }

      // Store cache folder path to save on repeat checks for path validity
      self::$_cachePathFull = self::path(self::$vqCachePath);

      self::_getMods();
      self::_loadProtected();
      self::_loadChecked();
    }

    /**
     * VQMod::modCheck()
     *
     * @param string $sourceFile path for file to be modified
     * @param string $modificationFile path for mods to be applied to file
     * @return string
     * @description Checks if a file has modifications and applies them, returning cache files or the file name
     */
    public static function modCheck($sourceFile, $modificationFile = false) {

      if (!preg_match('%^([a-z]:)?[\\\\/]%i', $sourceFile)) {
        $sourcePath = self::path($sourceFile);
      } else {
        $sourcePath = self::_realpath($sourceFile);
      }

      if ($modificationFile !== false) {
        if (!preg_match('%^([a-z]:)?[\\\\/]%i', $modificationFile)) {
          $modificationsPath = self::path($modificationFile);
        } else {
          $modificationsPath = self::_realpath($modificationFile);
        }
      } else {
        $modificationsPath = $sourcePath;
      }

      if (!$sourcePath || is_dir($sourcePath) || in_array($sourcePath, self::$_doNotMod)) {
        return $sourceFile;
      }

      $stripped_filename = preg_replace('#^' . preg_quote(self::getCwd(), '#') . '#i', '', $sourcePath);
      $cacheFile = self::$_cachePathFull . 'vq2-' . preg_replace('#[/\\\\]+#', '_', $stripped_filename);
      $file_last_modified = filemtime($sourcePath);

      if (!self::$_debug && file_exists($cacheFile) && filemtime($cacheFile) >= self::$_lastModifiedTime && filemtime($cacheFile) >= $file_last_modified) {
        return $cacheFile;
      }

      if (isset(self::$_filesModded[$sourcePath])) {
        return self::$_filesModded[$sourcePath]['cached'] ? $cacheFile : $sourceFile;
      }

      $changed = false;
      $fileHash = sha1_file($sourcePath);
      $fileData = file_get_contents($sourcePath);

      foreach (self::$_mods as $modObject) {
        foreach ($modObject->mods as $path => $mods) {
          if (self::_checkMatch($path, $modificationsPath)) {
            $modObject->applyMod($mods, $fileData, $modObject);
          }
        }
      }

      if (sha1($fileData) != $fileHash) {
        $writePath = $cacheFile;
        if (!file_exists($writePath) || is_writable($writePath)) {
          file_put_contents($writePath, $fileData, LOCK_EX);
          $changed = true;
        }
      } else {
        file_put_contents(self::path(self::$checkedCache, true), $stripped_filename . PHP_EOL, FILE_APPEND | LOCK_EX);
        self::$_doNotMod[] = $sourcePath;
      }

      self::$_filesModded[$sourcePath] = array('cached' => $changed);
      return $changed ? $writePath : $sourcePath;
    }

    /**
     * VQMod::path()
     *
     * @param string $path File path
     * @param bool $skip_real If true path is full not relative
     * @return bool, string
     * @description Returns the full true path of a file if it exists, otherwise false
     */
    public static function path($path, $skip_real=false) {

      $tmp = self::$_cwd . $path;
      $realpath = $skip_real ? $tmp : self::_realpath($tmp);

      if (!$realpath) return false;

      return $realpath;
    }

    /**
     * VQMod::getCwd()
     *
     * @return string
     * @description Returns current working directory
     */
    public static function getCwd() {
      return self::$_cwd;
    }

    /**
     * VQMod::handleXMLError()
     *
     * @description Error handler for bad XML files
     */
    public static function handleXMLError($errno, $errstr, $errfile, $errline) {
      if ($errno == E_WARNING && (substr_count($errstr, 'DOMDocument::load()') > 0)) {
        throw new DOMException(str_replace('DOMDocument::load()', '', $errstr));
      } else {
        return false;
      }
    }

    /**
     * VQMod::_getMods()
     *
     * @return null
     * @description Gets list of XML files in vqmod xml folder for processing
     */
    private static function _getMods() {

      self::$_modFileList = glob(self::path('vqmod/xml/', true) . '*.xml');

      foreach (self::$_modFileList as $file) {
        if (file_exists($file)) {
          $lastMod = filemtime($file);
          if ($lastMod > self::$_lastModifiedTime) {
            self::$_lastModifiedTime = $lastMod;
          }
        }
      }

      $xml_folder_time = filemtime(self::path('vqmod/xml'));
      if ($xml_folder_time > self::$_lastModifiedTime) {
        self::$_lastModifiedTime = $xml_folder_time;
      }

      $modCache = self::path(self::$modCache);
      if (self::$_debug || !file_exists($modCache)) {
        self::$_lastModifiedTime = time();
      } else if (file_exists($modCache) && filemtime($modCache) >= self::$_lastModifiedTime) {
        $mods = file_get_contents($modCache);
        if (!empty($mods)) self::$_mods = unserialize($mods);
        if (self::$_mods !== false) {
          return;
        }
      }

      // Clear checked cache if rebuilding
      file_put_contents(self::path(self::$checkedCache, true), '', LOCK_EX);

      if (self::$_modFileList) {
        self::_parseMods();
      }
    }

    /**
     * VQMod::_parseMods()
     *
     * @return null
     * @description Loops through xml files and attempts to load them as VQModObject's
     */
    private static function _parseMods() {

      set_error_handler(array('VQMod', 'handleXMLError'));

      $dom = new DOMDocument('1.0', 'UTF-8');
      foreach (self::$_modFileList as $modFileKey => $modFile) {
        if (file_exists($modFile)) {
          try {
            $dom->load($modFile);
            $mod = $dom->getElementsByTagName('modification')->item(0);
            $vqmver = $mod->getElementsByTagName('vqmver')->item(0);

            if ($vqmver) {
              $version_check = $vqmver->getAttribute('required');
              if (strtolower($version_check) == 'true') {
                if (version_compare(self::$_vqversion, $vqmver->nodeValue, '<')) {
                  trigger_error(__METHOD__.' - File "' . $modFile . '" requires vQmod "' . $vqmver->nodeValue . '" or above and has been skipped', E_USER_WARNING);
                  continue;
                }
              }
            }

            self::$_mods[] = new VQModObject($mod, $modFile);

          } catch (Exception $e) {
            trigger_error(__METHOD__.' - Invalid XML file (' . $modFile . '): '. $e->getMessage(), E_USER_WARNING);
          }
        } else {
          trigger_error(__METHOD__.' - File not found (' . $modFile . ')', E_USER_WARNING);
        }
      }

      restore_error_handler();

      $modCache = self::path(self::$modCache, true);
      $result = file_put_contents($modCache, serialize(self::$_mods), LOCK_EX);

      if (!$result) {
        trigger_error(__METHOD__.' - File not writable (' . $modCache . ')', E_USER_ERROR);
      }
    }

    /**
     * VQMod::_loadProtected()
     *
     * @return null
     * @description Loads protected list and adds them to _doNotMod array
     */
    private static function _loadProtected() {

      $file = self::path(self::$protectedFilelist);

      if ($file && is_file($file)) {
        $paths = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!empty($paths)) {

          foreach ($paths as $path) {
            $fullPath = self::path($path);
            if ($fullPath && !in_array($fullPath, self::$_doNotMod)) {
              self::$_doNotMod[] = $fullPath;
            }
          }
        }
      }
    }

    /**
     * VQMod::_loadChecked()
     *
     * @return null
     * @description Loads already checked files and adds them to _doNotMod array
     */
    private static function _loadChecked() {

      $file = self::path(self::$checkedCache);

      if ($file && is_file($file)) {
        $paths = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!empty($paths)) {
          foreach ($paths as $path) {
            $fullPath = self::path($path, true);
            if ($fullPath) {
              self::$_doNotMod[] = $fullPath;
            }
          }
        }
      }
    }

    /**
     * VQMod::_realpath()
     *
     * @param string $file
     * @return string
     * @description Returns real path of any path, adding directory slashes if necessary
     */
    private static function _realpath($file) {

      if (!file_exists($file)) return false;

      $path = str_replace("\\", '/', realpath($file));
      if (!$path) return false;

      if (is_dir($path)) {
        $path = rtrim($path, '/') . '/';
      }

      return $path;
    }

    /**
     * VQMod::_checkMatch()
     *
     * @param string $modFilePath Modification path from a <file> node
     * @param string $checkFilePath File path
     * @return bool
     * @description Checks a modification path against a file path
     */
    private static function _checkMatch($modFilePath, $checkFilePath) {
      $modFilePath = str_replace('\\', '/', $modFilePath);
      $checkFilePath = str_replace('\\', '/', $checkFilePath);

      if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
        $modFilePath = strtolower($modFilePath);
        $checkFilePath = strtolower($checkFilePath);
      }

      if ($modFilePath == $checkFilePath) {
        $return = true;
      } else if (strpos($modFilePath, '*') !== false) {

        $return = true;
        $modParts = explode('/', $modFilePath);
        $checkParts = explode('/', $checkFilePath);

        if (count($modParts) !== count($checkParts)) {
           $return = false;
        } else {

          $toCheck = array_diff_assoc($modParts, $checkParts);

          foreach ($toCheck as $k => $part) {
            if ($part === '*') {
              continue;
            } else if (strpos($part, '*') !== false) {
              $part = preg_replace_callback('#([^*]+)#', array('self', '_quotePath'), $part);
              $part = str_replace('*', '[^/]*', $part);
              $part = (bool) preg_match('#^' . $part . '$#', $checkParts[$k]);

              if ($part) continue;

            } else if ($part === $checkParts[$k]) {
              continue;
            }

            $return = false;
            break;
          }

        }
      } else {
        $return = false;
      }

      return $return;
    }

    /**
     * VQMod::_quotePath()
     *
     * @param string $matches callback matches
     * @return string
     * @description apply's preg_quote to string from callback
     */
    private static function _quotePath($matches) {
      return preg_quote($matches[1], '#');
    }
  }

  /**
   * VQModObject
   * @description Object for the <modification> that orchestrates each applied modification
   */
  class VQModObject {
    public $modFile;
    public $id;
    public $version;
    public $vqmver;
    public $author;
    public $mods = array();

    private $_skip = false;

    /**
     * VQModObject::__construct()
     *
     * @param DOMNode $node <modification> node
     * @param string $modFile File modification is from
     * @return null
     * @description Loads modification meta information
     */
    public function __construct(DOMNode $node, $modFile) {
      if ($node->hasChildNodes()) {
        foreach ($node->childNodes as $child) {
          $name = (string) $child->nodeName;
          if (isset($this->$name)) {
            $this->$name = (string) $child->nodeValue;
          }
        }
      }

      $this->modFile = $modFile;
      $this->_parseMods($node);
    }

    /**
     * VQModObject::skip()
     *
     * @return bool
     * @description Returns the skip status of a modification
     */
    public function skip() {
      return $this->_skip;
    }

    /**
     * VQModObject::applyMod()
     *
     * @param array $mods Array of search add nodes
     * @param string $data File contents to be altered
     * @return null
     * @description Applies all modifications to the text data
     */
    public function applyMod($mods, &$data, &$modObject) {
      if ($this->_skip) return;
      $tmp = $data;

      foreach ($mods as $mod) {
        VQMod::$fileModding = $mod['fileToMod'] . '(' . $mod['opIndex'] . ')';
        if (!empty($mod['ignoreif'])) {
          if ($mod['ignoreif']->regex == 'true') {
            if (preg_match($mod['ignoreif']->getContent(), $tmp)) {
              continue;
            }
          } else {
            if (strpos($tmp, $mod['ignoreif']->getContent()) !== false) {
              continue;
            }
          }
        }

        $indexCount = 0;

        $tmp = preg_split('#\R#', $tmp);
        $lineMax = count($tmp) - 1;

      // <add> tag attributes - Override <search> attributes if set
      foreach (array_keys((array)$mod['search']) as $key) {
        if ($key == "\x0VQNode\x0_content") { continue; }
        if ($key == "trim") { continue; }
        if (isset($mod['add']->$key) && $mod['add']->$key) {
          $mod['search']->$key = $mod['add']->$key;
        }
      }

        switch($mod['search']->position) {
          case 'top':
            $tmp[$mod['search']->offset] =  $mod['add']->getContent() . $tmp[$mod['search']->offset];
            break;

          case 'bottom':
            $offset = $lineMax - $mod['search']->offset;
            if ($offset < 0) {
              $tmp[-1] = $mod['add']->getContent();
            } else {
              $tmp[$offset] .= $mod['add']->getContent();
            }
            break;

          default:
            $changed = false;
            foreach ($tmp as $lineNum => $line) {
              if (strlen($mod['search']->getContent()) == 0) {
                if ($mod['error'] == 'log' || $mod['error'] == 'abort') {
                  trigger_error(__METHOD__.'('. basename($this->modFile) .') - Empty search content in "'. $modObject->id  .'": '. VQMod::$fileModding . $skip, E_USER_WARNING);
                }
                break;
              }

              if ($mod['search']->regex == 'true') {
                $pos = @preg_match($mod['search']->getContent(), $line);
                if ($pos === false) {
                  if ($mod['error'] == 'log' || $mod['error'] == 'abort' ) {
                    trigger_error(__METHOD__.'('. basename($this->modFile) .') - Invalid regular expression in "'. $modObject->id  .'": '. VQMod::$fileModding . $skip, E_USER_WARNING);
                  }
                  break 2;
                } else if ($pos == 0) {
                  $pos = false;
                }
              } else {
                $pos = strpos($line, $mod['search']->getContent());
              }

              if ($pos !== false) {
                $indexCount++;
                $changed = true;

                if (!$mod['search']->indexes() || ($mod['search']->indexes() && in_array($indexCount, $mod['search']->indexes()))) {

                  switch($mod['search']->position) {
                    case 'before':
                      $offset = ($lineNum - $mod['search']->offset < 0) ? -1 : $lineNum - $mod['search']->offset;
                      $tmp[$offset] = empty($tmp[$offset]) ? $mod['add']->getContent() : $mod['add']->getContent() . "\n" . $tmp[$offset];
                      break;

                    case 'after':
                      $offset = ($lineNum + $mod['search']->offset > $lineMax) ? $lineMax : $lineNum + $mod['search']->offset;
                      $tmp[$offset] = $tmp[$offset] . "\n" . $mod['add']->getContent();
                      break;

                    case 'ibefore':
                      $tmp[$lineNum] = str_replace($mod['search']->getContent(), $mod['add']->getContent() . $mod['search']->getContent(), $line);
                      break;

                    case 'iafter':
                      $tmp[$lineNum] = str_replace($mod['search']->getContent(), $mod['search']->getContent() . $mod['add']->getContent(), $line);
                      break;

                    default:
                      if (!empty($mod['search']->offset)) {
                        if ($mod['search']->offset > 0) {
                          for($i = 1; $i <= $mod['search']->offset; $i++) {
                            if (isset($tmp[$lineNum + $i])) {
                              $tmp[$lineNum + $i] = '';
                            }
                          }
                        } else if ($mod['search']->offset < 0) {
                          for($i = -1; $i >= $mod['search']->offset; $i--) {
                            if (isset($tmp[$lineNum + $i])) {
                              $tmp[$lineNum + $i] = '';
                            }
                          }
                        }
                      }

                      if ($mod['search']->regex == 'true') {
                        $tmp[$lineNum] = preg_replace($mod['search']->getContent(), $mod['add']->getContent(), $line);
                      } else {
                        $tmp[$lineNum] = str_replace($mod['search']->getContent(), $mod['add']->getContent(), $line);
                      }
                      break;
                  }
                }
              }
            }

            if (!$changed) {
              $skip = ($mod['error'] == 'skip' || $mod['error'] == 'log') ? ' [SKIPPED]' : ' [ABORTING MOD]';

              if ($mod['error'] == 'log' || $mod['error'] == 'abort') {
                trigger_error(__METHOD__.'('. basename($this->modFile) .') - Search not found in "'. $modObject->id  .'": '. VQMod::$fileModding . $skip, E_USER_WARNING);
              }

              if ($mod['error'] == 'abort') {
                $this->_skip = true;
                return;
              }

            }

            break;
        }

        ksort($tmp);
        $tmp = implode(PHP_EOL, $tmp);
      }

      VQMod::$fileModding = false;

      $data = $tmp;
    }

    /**
     * VQModObject::_parseMods()
     *
     * @param DOMNode $node <modification> node to be parsed
     * @return null
     * @description Parses modifications in preparation for the applyMod method to work
     */
    private function _parseMods(DOMNode $node) {

      foreach ($node->getElementsByTagName('file') as $file) {
        $path = $file->getAttribute('path') ? $file->getAttribute('path') : '';
        $filesToMod = explode(',', $file->getAttribute('name'));

        foreach ($filesToMod as $filename) {

          $fileToMod = $path . $filename;
          if (!empty(VQMod::$replaces)) {
            foreach (VQMod::$replaces as $search => $replace) {
              $fileToMod = preg_replace($search, $replace, $fileToMod);
            }
          }

          $error = ($file->hasAttribute('error')) ? $file->getAttribute('error') : 'log';
          $fullPath = VQMod::path($fileToMod);

          if (!$fullPath || !file_exists($fullPath)) {
            if (strpos($fileToMod, '*') !== false) {
              $fullPath = VQMod::getCwd() . $fileToMod;
            } else {
              if ($error == 'log' || $error == 'abort') {
                $skip = ($error == 'log') ? ' [SKIPPED]' : ' [ABORTING MOD]';
                trigger_error(__METHOD__.'('. basename($this->modFile) .') - Could not resolve path ('. $fileToMod .')'. $skip, E_USER_WARNING);
              }

              if ($error == 'log' || $error == 'skip') {
                continue;
              } else if ($error == 'abort') {
                return false;
              }
            }
          }

          $operations = $file->getElementsByTagName('operation');

          foreach ($operations as $opIndex => $operation) {
            VQMod::$fileModding = $fileToMod . '(' . $opIndex . ')';
            $skipOperation = false;

            $error = ($operation->hasAttribute('error')) ? $operation->getAttribute('error') : 'abort';
            $ignoreif = $operation->getElementsByTagName('ignoreif')->item(0);

            if ($ignoreif) {
              $ignoreif = new VQSearchNode($ignoreif);
            } else {
              $ignoreif = false;
            }

            $search = $operation->getElementsByTagName('search')->item(0);
            $add = $operation->getElementsByTagName('add')->item(0);

            if (!$search) {
              trigger_error(__METHOD__.'('. basename($this->modFile) .') - Operation <search> tag missing', E_USER_WARNING);
              $skipOperation = true;
            }

            if (!$add) {
              trigger_error(__METHOD__.'('. basename($this->modFile) .') - Operation <add> tag missing', E_USER_WARNING);
              $skipOperation = true;
            }

            if (!$skipOperation) {
              $this->mods[$fullPath][] = array(
                'search'     => new VQSearchNode($search),
                'add'        => new VQAddNode($add),
                'ignoreif'   => $ignoreif,
                'error'      => $error,
                'fileToMod'  => $fileToMod,
                'opIndex'    => $opIndex,
              );
            }
          }

          VQMod::$fileModding = false;
        }
      }
    }
  }

  /**
   * VQNode
   * @description Basic node object blueprint
   */
  class VQNode {
  public $regex = 'false';
    public $trim = 'false';
    private $_content = '';

    /**
     * VQNode::__construct()
     *
     * @param DOMNode $node Search/add node
     * @return null
     * @description Parses the node attributes and sets the node property
     */
    public function  __construct(DOMNode $node) {
      $this->_content = $node->nodeValue;

      if ($node->hasAttributes()) {
        foreach ($node->attributes as $attr) {
          $name = $attr->nodeName;
          if (isset($this->$name)) {
            $this->$name = $attr->nodeValue;
          }
        }
      }
    }

    /**
     * VQNode::getContent()
     *
     * @return string
     * @description Returns the content, trimmed if applicable
     */
    public function getContent() {
      $content = ($this->trim == 'true') ? trim($this->_content) : $this->_content;
      return $content;
    }
  }

  /**
   * VQSearchNode
   * @description Object for the <search> xml tags
   */
  class VQSearchNode extends VQNode {
    public $position = 'replace';
    public $offset = 0;
    public $index = 'false';
    public $regex = 'false';
    public $trim = 'true';

    /**
     * VQSearchNode::indexes()
     *
     * @return bool, array
     * @description Returns the index values to use the search on, or false if none
     */
    public function indexes() {
      if ($this->index == 'false') {
        return false;
      }
      $tmp = explode(',', $this->index);
      foreach ($tmp as $k => $v) {
        if (!is_int($v)) {
          unset($k);
        }
      }
      $tmp = array_unique($tmp);
      return empty($tmp) ? false : $tmp;
    }
  }

  /**
   * VQAddNode
   * @description Object for the <add> xml tags
   */
  class VQAddNode extends VQNode {
    public $position = false;
    public $offset = false;
    public $index = false;
    public $regex = false;
    public $trim = 'false';
  }
