<?php
  class url_category {

    function routes() {
      return [
        [
          'pattern' => '#^.*-c-([0-9]+)/?$#',
          'page' => 'category',
          'params' => 'category_id=$1',
          'options' => [
            'redirect' => true,
          ],
        ],
      ];
    }

    function rewrite(ent_link $link, $language_code) {

      if (empty($link->query['category_id'])) return;

      $category = reference::category($link->query['category_id'], $language_code);

      $new_path = '';
      foreach ($category->path as $parent_id => $parent) {
        $new_path .= functions::format_path_friendly($parent->name, $language_code) .'-c-'. $parent_id .'/';
      }

      $link->path = $new_path;
      $link->unset_query('category_id');

      return $link;
    }
  }
