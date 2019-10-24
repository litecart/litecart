<?php

  class url_manufacturer {

    function routes() {
      return array(
        array(
          'pattern' => '#^.*-m-([0-9]+)/?$#',
          'page' => 'manufacturer',
          'params' => 'manufacturer_id=$1',
          'options' => array(
            'redirect' => true,
          ),
        ),
      );
    }

    function rewrite(ent_link $link, $language_code) {

      if (empty($link->query['manufacturer_id'])) return;

      $manufacturer = reference::manufacturer($link->query['manufacturer_id'], $language_code);
      if (empty($manufacturer->id)) return $link;

      $link->path = functions::general_path_friendly($manufacturer->name, $language_code) .'-m-'. $manufacturer->id .'/';
      $link->unset_query('manufacturer_id');

      return $link;
    }
  }
