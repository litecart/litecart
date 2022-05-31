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

      // Set filename
        $view = preg_replace('#\.inc\.php$#', '', $view) . '.inc.php';

      // Absolute path
        if (preg_match('#^([a-zA-Z]:)?/#', $view)) {
          $this->view = $view;

      // Relative path
        } else {
          $this->view = vmod::check(FS_DIR_TEMPLATE . $view);
          if (!is_file($this->view)) $this->view = vmod::check(FS_DIR_APP . 'frontend/templates/default/'. $view);
        }
      }

    // Register default parser for snippets {{var|modifier1|modifier2}}
      $this->register_parser('([0-9a-zA-Z_\.]+)(|[^'. preg_quote($this->wrapper[1][0], '#') .']+)?', function($matches) {

        if (!isset($this->snippets[$matches[1]])) return $matches[0];

        if ($this->snippets[$matches[1]] instanceof ent_view) {
          $this->snippets[$matches[1]] = (string)$this->snippets[$matches[1]];
        }

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
          $output = functions::escape_html($output);
        }

        return $output;
      });
    }

    public function register_parser($pattern, $callable) {
      $pattern = '#'. preg_quote($this->wrapper[0], '#') . $pattern . preg_quote($this->wrapper[1], '#') .'#s';
      $this->_parsers = [$pattern => $callable] + $this->_parsers;
    }

    public function __toString() {

    // Load view and process it in an isolated scope
      if ($this->view) {
        $this->html = (function(){
          ob_start();
          extract(func_get_arg(1));
          include vmod::check(func_get_arg(0));
          return ob_get_clean();
        })($this->view, $this->snippets);
      }

      if (empty($this->html)) return '';

      foreach ($this->snippets as $key => $snippet) {
        if (!is_string($snippet)) continue;
        $this->snippets[$key] = preg_replace_callback_array($this->_parsers, $snippet);
      }

      if (is_string($this->html)) {
        $this->html = preg_replace_callback_array($this->_parsers, $this->html);
      }

      if ($this->cleanup) {
        //$this->html = preg_replace('#'. preg_quote($this->wrapper[0], '#') .'(if|each) .*?'. preg_quote($this->wrapper[0], '#') .'\1'. preg_quote($this->wrapper[1], '#') .'#', '', $this->html);
        $this->html = preg_replace('#'. preg_quote($this->wrapper[0], '#') .'.*?'. preg_quote($this->wrapper[1], '#') .'#', '', $this->html);
      }

      return $this->html;
    }

    public function stitch($view=null, $cleanup=false) {

      //trigger_error('ent_view->stitch() is deprecated. Instead set the view file when constructing view object and use echo to output the rendered view.', E_USER_DEPRECATED);

      if ($cleanup) {
        $this->cleanup = true;
      }

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

      return $this->__toString();
    }
  }
