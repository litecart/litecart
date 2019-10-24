<?php

  class url_product {

    function routes() {
      return array(
        array(
          'pattern' => '#^(?:.*-c-([0-9]+)/)?.*-p-([0-9]+)$#',
          'page' => 'product',
          'params' => 'category_id=$1&product_id=$2',
          'options' => array(
            'redirect' => true,
          ),
        ),
      );
    }

    function rewrite(ent_link $link, $language_code) {

      if (empty($link->query['product_id'])) return;

      $product = reference::product($link->query['product_id'], $language_code);
      if (empty($product->id)) return $link;

      $new_path = '';

      if (!empty($link->query['category_id'])) {

        $category_trail = functions::catalog_category_trail($link->query['category_id'], $language_code);

        if (!empty($category_trail)) {
          foreach ($category_trail as $category_id => $category_name) $new_path .= functions::general_path_friendly($category_name, $language_code) .'-c-'. $category_id .'/';
        }

        $link->path = $new_path;
        $link->unset_query('category_id');

      } else if (!empty($link->query['manufacturer_id'])) {

        $manufacturer = reference::manufacturer($link->query['manufacturer_id'], $language_code);

        if (!empty($manufacturer->id)) {
          $new_path .= functions::general_path_friendly($manufacturer->name, $language_code) .'-m-'. $manufacturer->id .'/';
        }

        $link->path = $new_path;
        $link->unset_query('manufacturer_id');

      } else if (!empty($product->default_category_id)) {

        $category_trail = functions::catalog_category_trail($product->default_category_id, $language_code);

        if (!empty($category_trail)) {
          foreach ($category_trail as $category_id => $category_name) $new_path .= functions::general_path_friendly($category_name, $language_code) .'-c-'. $category_id .'/';
        }
      }

      $new_path .= functions::general_path_friendly($product->name, $language_code) .'-p-'. $product->id;

      $link->path = $new_path;
      $link->unset_query('product_id');

      return $link;
    }
  }
