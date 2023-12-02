<?php

  class ent_view {
    public $snippets = [];
    public $html = '';

    public static $time_elapsed = 0;

    public function stitch($view=null, $cleanup=false) {

      $timestamp_start = microtime(true);

      if (!empty($view)) {

      // Absolute path
        if (preg_match('#^([A-Z]:)?/#', $view)) {
          $file = $view;

      // Relative path
        } else {
          $file = vmod::check(FS_DIR_APP . 'includes/templates/' . document::$template .'/'. $view .'.inc.php');
          if (!is_file($file)) $file = vmod::check(FS_DIR_APP . 'includes/templates/default.catalog/'. $view .'.inc.php');
        }

        $fn = function(){
          ob_start();
          extract(func_get_arg(1));
          include vmod::check(func_get_arg(0));
          return ob_get_clean();
        };

      // Process view in an isolated scope
        $this->html = $fn($file, $this->snippets);
      }

      if (empty($this->html)) return '';

      if (!empty($this->snippets)) {

        $search_replace = [];
        foreach (array_keys($this->snippets) as $key) {
          if (!is_string($this->snippets[$key])) continue;
          $search_replace['<!--snippet:'.$key.'-->'] = &$this->snippets[$key];
          $search_replace['{snippet:'.$key.'}'] = &$this->snippets[$key];
        }

        foreach (array_keys($search_replace) as $key) {
          $search_replace[$key] = str_replace(array_keys($search_replace), array_values($search_replace), $search_replace[$key]);
        }

        $this->html = str_replace(array_keys($search_replace), array_values($search_replace), $this->html, $count);
      }

    // Clean orphan snippets
      if ($cleanup) {
        $search = [
          '#\{snippet:.*?\}#',
          '#<!--snippet:.*?-->#',
        ];

        $this->html = preg_replace($search, '', $this->html);
      }

      self::$time_elapsed += microtime(true) - $timestamp_start;

      return $this->html;
    }
  }
