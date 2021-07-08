<?php
  class ent_view {
    public $view;
    public $html;
    public $snippets = [];
    public $wrapper = ['{{', '}}'];
    public $cleanup = false;
    private $_parsers = [];

    public function __construct($view='') {

      if ($view) {
        $view = preg_replace('#^(.*?)(\.inc\.php)?$#', '$1.inc.php', $view);

      // Absolute path
        if (preg_match('#^([a-zA-Z]:)?/#', $view)) {
          $this->view = $view;

      // Relative path
        } else {
          $this->view = vmod::check(FS_DIR_TEMPLATE . $view);
          if (!is_file($this->view)) $this->view = vmod::check(FS_DIR_APP . 'frontend/templates/default/'. $view);
        }
      }

    // Parser for Variables {{var|modifier1|modifier2}}
      $this->register_parser('#([0-9a-zA-Z_]+)(|[^'. preg_quote($this->wrapper[1][0], '#') .']+)?#', function($matches) {

        if (!isset($this->snippets[$matches[1]])) return $matches[0];
        if (empty($matches[2])) return $this->snippets[$matches[1]];

        $output = $this->snippets[$matches[1]];
        $modifiers = !empty($matches[2]) ? preg_split('#\|#', $matches[2], -1, PREG_SPLIT_NO_EMPTY) : [];

        if (in_array('title', $modifiers)) {
          $output = ucwords(strtolower($output));
        }

        if (in_array('uppercase', $modifiers)) {
          $output = strtoupper($output);
        }

        if (in_array('lowercase', $modifiers)) {
          $output = strtolower($output);
        }

        if (in_array('calculate', $modifiers)) {
          $output = currency::calculate($output);
        }

        if (in_array('money', $modifiers)) {
          $output = currency::format($output);
        }

        if (in_array('escape', $modifiers)) {
          $output = htmlspecialchars($output);
        }

        return $output;
      });

    // Parser for Translations {{translate "title_key" "Text"}}
      $this->register_parser('#translate "([^\"]+)"(?:, "([^\"]+)")?#', function($matches) {
        return language::translate($matches[1], isset($matches[2]) ? $matches[2] : '');
      });

    // Parser for Settings {{setting "key"}}
      $this->register_parser('#setting "([^\"]+)"#', function($matches) {
        return settings::get($matches[1]);
      });

    // Parser for Includes {{include "path/to/file.tpl"}}
      //$this->register_parser('#>(.*?)#', function($matches) {
      $this->register_parser('#include "(.*?)"#', function($matches) {
        if (file_exists($file = FS_DIR_TEMPLATE . $matches[1] .'.tpl')) {
          return file_get_contents($file);
        }
      });

    // Parser for Each {{each $array as $var}} {{/each}}
      $this->register_parser('#each \$(.*?) as \$(.*?)'. preg_quote($this->wrapper[1], '#') .'(.*?)'. preg_quote($this->wrapper[0], '#') .'/each#', function($matches) {

        if (!empty($this->snippets[$matches[1]]) || !is_array($this->snippets[$matches[1]])) return '';

        $view = new ent_view();
        $view->html = $matches[3];
        $view->snippets = $this->snippets;
        $view->snippets[$matches[2]] = $this->snippets[$matches[1]];

        return $view->stitch();
      });

    // Register parser: Conditions {if $array} {/if}
      $this->register_parser('#if \$(.*?)'. preg_quote($this->wrapper[1], '#') .'(.*?)'. preg_quote($this->wrapper[0], '#') .'/if#', function($matches) {
        if (!empty($this->snippets[$matches[1]]) && (float)$this->snippets[$matches[1]] != 0) return '';
        return $this->snippets[$matches[1]];
      });
    }

    public function register_parser($pattern, $callable) {
      $pattern = '#'. preg_quote($this->wrapper[0], '#') . mb_substr($pattern, 1, -1) . preg_quote($this->wrapper[1], '#') .'#';
      $this->_parsers = [$pattern => $callable] + $this->_parsers;
    }

    public function __toString() {

    // Load view - and process it in an isolated scope
      if ($this->view) {
        $load = function(){
          ob_start();
          extract(func_get_arg(1));
          include vmod::check(func_get_arg(0));
          return ob_get_clean();
        };

        $this->html = $load($this->view, $this->snippets);
      }

      if (empty($this->html)) return '';

    // Replace any logic in snippets
      foreach (array_keys($this->snippets) as $key) {
        if (!is_string($this->snippets[$key])) continue;
        $this->snippets[$key] = preg_replace_callback_array($this->_parsers, $this->snippets[$key], -1, $count);
      }

      $this->html = preg_replace_callback_array($this->_parsers, $this->html, -1, $count);

      if ($this->cleanup) {
        $this->html = preg_replace('#'. preg_quote($this->wrapper[0], '#') .'(if|each) .*?'. preg_quote($this->wrapper[0], '#') .'\1'. preg_quote($this->wrapper[1], '#') .'#', '', $this->html);
        $this->html = preg_replace('#'. preg_quote($this->wrapper[0], '#') .'.*?'. preg_quote($this->wrapper[1], '#') .'#', '', $this->html);
      }

      return $this->html;
    }

    public function stitch($view=null, $cleanup=false) {

      //trigger_error('ent_view->stitch() is deprecated. Instead set the view when constructing view and use echo to output.', E_USER_DEPRECATED);

      if ($view) {
        $view = preg_replace('#^(.*?)(\.inc\.php)?$#', '$1.inc.php', $view);

      // Absolute path
        if (preg_match('#^([a-zA-Z]:)?/#', $view)) {
          $this->view = $view;

      // Relative path
        } else {
          $this->view = vmod::check(FS_DIR_TEMPLATE . $view);
          if (!is_file($this->view)) $this->view = vmod::check(FS_DIR_APP . 'frontend/templates/default/'. $view);
        }
      }

      if ($cleanup) $this->cleanup = true;

      return $this->__toString();
    }
  }
