<?php
  class url_category {

    function routes() {
      return array(
        array(
          'pattern' => '#^.*-c-([0-9]+)/?$#',
          'page' => 'category',
          'params' => 'category_id=$1',
          'options' => array(
            'redirect' => true,
          ),
        ),
      );
    }

    function rewrite(ent_link $link, $language_code) {

      if (empty($link->query['category_id'])) return;

      $category_trail = functions::catalog_category_trail($link->query['category_id'], $language_code);

      if (empty($category_trail)) return;

      $new_path = '';
      foreach ($category_trail as $category_id => $category_name) {
        $new_path .= functions::general_path_friendly($category_name, $language_code) .'-c-'. $category_id .'/';
      }

      $link->path = $new_path;
      $link->unset_query('category_id');

      return $link;
    }
  }
