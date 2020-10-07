<?php
  class ent_view {
    public $snippets = [];
    public $html = '';

    public function stitch($view=null, $cleanup=false) {

      if (!empty($view)) {

      // Absolute path
        if (preg_match('#^([a-zA-Z]:)?/#', $view)) {
          $file = $view;

      // Relative path
        } else {
          if (!empty(route::$route['endpoint']) && route::$route['endpoint'] == 'backend') {
            $file = vmod::check(FS_DIR_APP . 'backend/template/'. $view .'.inc.php');
          } else {
            $file = vmod::check(FS_DIR_APP . 'frontend/templates/' . document::$template .'/'. $view .'.inc.php');
            if (!is_file($file)) $file = vmod::check(FS_DIR_APP . 'frontend/templates/default/'. $view .'.inc.php');
          }
        }

      // Process view in an isolated scope
        $this->html = (function(){
          ob_start();
          extract(func_get_arg(1));
          include vmod::check(func_get_arg(0));
          return ob_get_clean();
        })($file, $this->snippets);
      }

      if (empty($this->html)) return;

      if (!empty($this->snippets)) {

        $search_replace = [];
        foreach (array_keys($this->snippets) as $key) {
          if (!is_string($this->snippets[$key])) continue;
          $search_replace['{snippet:'.$key.'}'] = &$this->snippets[$key];
        }

        foreach (array_keys($search_replace) as $key) {
          $search_replace[$key] = str_replace(array_keys($search_replace), array_values($search_replace), $search_replace[$key]);
        }

        $this->html = str_replace(array_keys($search_replace), array_values($search_replace), $this->html, $count);
      }

    // Clean orphan snippets
      if ($cleanup) {
        $this->html = preg_replace('#\{snippet:.*?\}#', '', $this->html);
      }

      return $this->html;
    }
  }
