<?php
  class ent_view {
    public $html = '';
    public $snippets = [];
    public $wrapper = ['{{', '}}'];

    public function stitch($view=null, $cleanup=false) {

      if (!empty($view)) {

        $view = preg_replace('#\.inc\.php$#', '', $view);

      // Absolute path
        if (preg_match('#^([a-zA-Z]:)?/#', $view)) {
          $file = $view . '.inc.php';

      // Relative path
        } else {
          $file = vmod::check(FS_DIR_TEMPLATE . $view .'.inc.php');
          if (!is_file($file)) $file = vmod::check(FS_DIR_APP . 'frontend/templates/default/'. $view .'.inc.php');
        }

      // Load view
        $fn = function(){
          ob_start();
          $view_file = preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', func_get_arg(0));
          extract(func_get_arg(1));
          include vmod::check(func_get_arg(0));
          return ob_get_clean();
        };

      // Process view in an isolated scope
        $this->html = $fn($file, $this->snippets);
      }

      if (empty($this->html)) return;

    // Create stack of patterns and replacements
      $stack = [];
      foreach (array_keys($this->snippets) as $key) {
        if (!is_string($this->snippets[$key])) continue;
        $pattern = '#'. preg_quote($this->wrapper[0], '#') .'\s*'. preg_quote($key, '#') .'\s*'. preg_quote($this->wrapper[1], '#') .'#';
        $replace = addcslashes($this->snippets[$key], '\\$');
        $stack[$pattern] = $replace;
      }

    // Replace any patterns in stack to prevent being left out
      foreach (array_keys($stack) as $key) {
        $stack[$key] = preg_replace(array_keys($stack), array_values($stack), $stack[$key]);
      }

    // Perform replacing
      $this->html = preg_replace(array_keys($stack), array_values($stack), $this->html);

    // Clean orphan snippets
      if ($cleanup) {
        $this->html = preg_replace('#'. preg_quote($this->wrapper[0], '#') .'.*?'. preg_quote($this->wrapper[1], '#') .'#', '', $this->html);
      }

      return $this->html;
    }
  }
