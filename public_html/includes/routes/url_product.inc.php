<?php

  class url_product {

    function routes() {
      return array(
        array(
          'pattern' => '#^(?:.*-c-([0-9]+)/)?.*-p-([0-9]+)$#',
          'page' => 'product',
          'params' => 'category_id=$1&product_id=$2',
          'redirect' => true,
        ),
      );
    }

  	function rewrite($parsed_link, $language_code) {

      if (empty($parsed_link['query']['product_id'])) return;

      $product = reference::product($parsed_link['query']['product_id'], $language_code);

      if (empty($product->id)) return $parsed_link;

      $parsed_link['path'] = '';

      if (!empty($parsed_link['query']['category_id'])) {

        $category_trail = functions::catalog_category_trail($parsed_link['query']['category_id'], $language_code);

        if (!empty($category_trail)) {
          foreach ($category_trail as $category_id => $category_name) $parsed_link['path'] .= functions::general_path_friendly($category_name, $language_code) .'-c-'. $category_id .'/';
        }

        unset($parsed_link['query']['category_id']);

      } else if (!empty($parsed_link['query']['manufacturer_id'])) {

        $manufacturer = new ref_manufacturer($parsed_link['query']['manufacturer_id']);

        if (!empty($manufacturer->id)) {
          $parsed_link['path'] = functions::general_path_friendly($manufacturer->name, $language_code) .'-m-'. $manufacturer->id .'/';
        }

        unset($parsed_link['query']['manufacturer_id']);

      } else if (!empty($product->default_category_id)) {

        $category_trail = functions::catalog_category_trail($product->default_category_id, $language_code);

        if (!empty($category_trail)) {
          foreach ($category_trail as $category_id => $category_name) $parsed_link['path'] .= functions::general_path_friendly($category_name, $language_code) .'-c-'. $category_id .'/';
        }
      }

      $parsed_link['path'] .= functions::general_path_friendly($product->name, $language_code) .'-p-'. $product->id;

      unset($parsed_link['query']['product_id']);

      return $parsed_link;
    }
  }
