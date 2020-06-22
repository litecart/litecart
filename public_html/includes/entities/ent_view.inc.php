<?php
  class ent_view {
    public $snippets = [];
    public $html = '';

    public function stitch($view=null, $cleanup=false) {

      if (!empty($view)) {

      // Absolute path
        if (preg_match('#^([a-z]:)?/#', $view)) {
          $file = $view;

      // Relative path
        } else {
          if (!empty(route::$route['endpoint']) && route::$route['endpoint'] == 'backend') {
            $file = vmod::check(FS_DIR_APP . 'backend/template/'. $view .'.inc.php');
          } else {
            $file = vmod::check(FS_DIR_APP . 'frontend/templates/' . document::$template .'/'. $view .'.inc.php');
          }
        }

        if (!is_file($file)) {
          $file = vmod::check(FS_DIR_APP . 'frontend/templates/default/'. $view .'.inc.php');
        }

        $this->html = $this->_process_view($file, $this->snippets);
      }

      if (empty($this->html)) return null;

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

      return $this->html;
    }

  // Method to process isolated PHP logic in a view
    private function _process_view($_file) {

      if (empty($_file)) {
        trigger_error('No view file passed for processing', E_USER_WARNING);
        return $html;
      }

    // Extract snippets
      if (!empty($this->snippets)) {
        extract($this->snippets);
      }

      ob_start();
      include vmod::check($_file);
      $html = ob_get_clean();

      return $html;
    }
  }
