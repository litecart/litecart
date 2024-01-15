<?php

  function draw_fonticon($class, $parameters=null) {

    switch (true) {

    // Bootstrap Icons
      case (substr($class, 0, 3) == 'bi-'):
        document::$snippets['head_tags']['bootstrap-icons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" />';
        return '<i class="bi '. $class .'"'. (!empty($parameters) ? ' ' . $parameters : null) .'></i>';

    // Fontawesome 4
      case (substr($class, 0, 3) == 'fa-'):
        //document::$snippets['head_tags']['fontawesome'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/fontawesome/4.7.0/css/font-awesome.min.css" />'; // Uncomment if removed from lib_document
        return '<i class="fa '. $class .'"'. (!empty($parameters) ? ' ' . $parameters : null) .'></i>';

    // Foundation
      case (substr($class, 0, 3) == 'fi-'):
        document::$snippets['head_tags']['foundation-icons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/foundation-icons/latest/foundation-icons.min.css" />';
        return '<i class="'. $class .'"'. (!empty($parameters) ? ' ' . $parameters : null) .'></i>';

    // Glyphicon
      case (substr($class, 0, 10) == 'glyphicon-'):
        //document::$snippets['head_tags']['glyphicon'] = '<link rel="stylesheet" href="'/path/to/glyphicon.min.css" />'; // Not embedded in release
        return '<span class="glyphicon '. $class .'"'. (!empty($parameters) ? ' ' . $parameters : null) .'></span>';

    // Ion Icons
      case (substr($class, 0, 4) == 'ion-'):
        document::$snippets['head_tags']['ionicons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/ionicons/latest/css/ionicons.min.css" />';
        return '<i class="'. $class .'"'. (!empty($parameters) ? ' ' . $parameters : null) .'></i>';

    // Material Design Icons
      case (substr($class, 0, 4) == 'mdi-'):
        document::$snippets['head_tags']['material-design-icons'] = '<link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.5.95/css/materialdesignicons.min.css" />';
        return '<i class="mdi '. $class .'"'. (!empty($parameters) ? ' ' . $parameters : null) .'></i>';
    }

    switch ($class) {
      case 'add':         return draw_fonticon('fa-plus');
      case 'cancel':      return draw_fonticon('fa-times');
      case 'edit':        return draw_fonticon('fa-pencil');
      case 'fail':        return draw_fonticon('fa-times', 'color: #c00;"');
      case 'folder':      return draw_fonticon('fa-folder', 'style="color: #cc6;"');
      case 'folder-open': return draw_fonticon('fa-folder-open', 'style="color: #cc6;"');
      case 'remove':      return draw_fonticon('fa-times', 'style="color: #c33;"');
      case 'delete':      return draw_fonticon('fa-trash-o');
      case 'move-up':     return draw_fonticon('fa-arrow-up', 'style="color: #39c;"');
      case 'move-down':   return draw_fonticon('fa-arrow-down', 'style="color: #39c;"');
      case 'ok':          return draw_fonticon('fa-check', 'style="color: #8c4;"');
      case 'on':          return draw_fonticon('fa-circle', 'style="color: #8c4;"');
      case 'off':         return draw_fonticon('fa-circle', 'style="color: #f64;"');
      case 'semi-off':    return draw_fonticon('fa-circle', 'style="color: #ded90f;"');
      case 'save':        return draw_fonticon('fa-floppy-o');
      case 'send':        return draw_fonticon('fa-paper-plane');
      case 'warning':     return draw_fonticon('fa-exclamation-triangle', 'color: #c00;"');
      default: trigger_error('Unknown font icon ('. $class .')', E_USER_WARNING); return;
    }
  }

  function draw_listing_category($category, $view='views/listing_category') {

    $listing_category = new ent_view();

    list($width, $height) = functions::image_scale_by_width(480, settings::get('category_image_ratio'));

    $listing_category->snippets = [
      'category_id' => $category['id'],
      'name' => $category['name'],
      'link' => document::ilink('category', ['category_id' => $category['id']]),
      'image' => [
        'original' => 'images/' . $category['image'],
        'thumbnail' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $category['image'], $width, $height, settings::get('category_image_clipping')),
        'thumbnail_2x' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $category['image'], $width*2, $height*2, settings::get('category_image_clipping')),
        'ratio' => str_replace(':', '/', settings::get('category_image_ratio')),
        'viewport' => [
          'width' => $width,
          'height' => $height,
        ],
      ],
      'short_description' => $category['short_description'],
    ];

    return $listing_category->stitch($view);
  }

  function draw_listing_product($product, $inherit_params=[], $view='views/listing_product') {

    $listing_product = new ent_view();

    $sticker = '';
    if ((float)$product['campaign_price']) {
      $sticker = '<div class="sticker sale" title="'. language::translate('title_on_sale', 'On Sale') .'">'. language::translate('sticker_sale', 'Sale') .'</div>';
    } else if ($product['date_created'] > date('Y-m-d', strtotime('-'.settings::get('new_products_max_age')))) {
      $sticker = '<div class="sticker new" title="'. language::translate('title_new', 'New') .'">'. language::translate('sticker_new', 'New') .'</div>';
    }

    list($width, $height) = functions::image_scale_by_width(320, settings::get('product_image_ratio'));

    $listing_product->snippets = [
      'product_id' => $product['id'],
      'code' => $product['code'],
      'sku' => $product['sku'],
      'mpn' => $product['mpn'],
      'gtin' => $product['gtin'],
      'name' => $product['name'],
      'link' => document::ilink('product', ['product_id' => $product['id']], $inherit_params),
      'image' => [
        'original' => ltrim($product['image'] ? 'images/' . $product['image'] : '', '/'),
        'thumbnail' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $product['image'], $width, $height, settings::get('product_image_clipping'), settings::get('product_image_trim')),
        'thumbnail_2x' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $product['image'], $width*2, $height*2, settings::get('product_image_clipping'), settings::get('product_image_trim')),
        'ratio' => str_replace(':', '/', settings::get('product_image_ratio')),
        'viewport' => [
          'width' => $width,
          'height' => $height,
        ],
      ],
      'sticker' => $sticker,
      'manufacturer' => [],
      'short_description' => $product['short_description'],
      'quantity' => (float)$product['quantity'],
      'recommended_price' => tax::get_price($product['recommended_price'], $product['tax_class_id']),
      'regular_price' => tax::get_price($product['price'], $product['tax_class_id']),
      'campaign_price' => (float)$product['campaign_price'] ? tax::get_price($product['campaign_price'], $product['tax_class_id']) : null,
      'final_price' => tax::get_price($product['final_price'], $product['tax_class_id']),
      'tax' => tax::get_tax($product['price'], $product['tax_class_id']),
      'tax_class_id' => $product['tax_class_id'],
    ];

    if (!empty($product['manufacturer_id'])) {
      $listing_product->snippets['manufacturer'] = [
        'id' => $product['manufacturer_id'],
        'name' => $product['manufacturer_name'],
      ];
    }

  // Watermark Original Image
    if (settings::get('product_image_watermark')) {
      $listing_product->snippets['image']['original'] = functions::image_process(FS_DIR_APP . $listing_product->snippets['image']['original'], ['watermark' => true]);
    }

    return $listing_product->stitch($view);
  }

  function draw_lightbox($selector='', $parameters=[]) {

    $selector = str_replace("'", '"', $selector);

    document::$snippets['head_tags']['featherlight'] = '<link rel="stylesheet" href="'. document::href_rlink(FS_DIR_APP . 'ext/featherlight/featherlight.min.css') .'" />';
    document::$snippets['foot_tags']['featherlight'] = '<script src="'. document::href_rlink(FS_DIR_APP . 'ext/featherlight/featherlight.min.js') .'"></script>';
    document::$snippets['javascript']['featherlight'] = '  $.featherlight.autoBind = \'[data-toggle="lightbox"]\';' . PHP_EOL
                                                      . '  $.featherlight.defaults.loading = \'<div class="loader" style="width: 128px; height: 128px; opacity: 0.5;"></div>\';' . PHP_EOL
                                                      . '  $.featherlight.defaults.closeIcon = \'&#x2716;\';' . PHP_EOL
                                                      . '  $.featherlight.defaults.targetAttr = \'data-target\';';
    if (empty($selector)) return;

    if (preg_match('#^(https?:)?//#', $selector)) {
      $js = '  $.featherlight(\''. $selector .'\', {' . PHP_EOL;
    } else {
      $js = '  $(\''. $selector .'\').featherlight({' . PHP_EOL;
    }

    foreach ($parameters as $key => $value) {
      switch (gettype($parameters[$key])) {

        case 'NULL':
          $js .= '    '. $key .': null,' . PHP_EOL;
          break;

        case 'boolean':
          $js .= '    '. $key .': '. ($value ? 'true' : 'false') .',' . PHP_EOL;
          break;

        case 'integer':
          $js .= '    '. $key .': '. $value .',' . PHP_EOL;
          break;

        case 'string':
          if (preg_match('#^\s*function\s*\(#', $value)) {
            $js .= '    '. $key .': '. $value .',' . PHP_EOL;
          } else {
            $js .= '    '. $key .': "'. addslashes($value) .'",' . PHP_EOL;
          }
          break;

        case 'array':
          $js .= '    '. $key .': ["'. implode('", "', $value) .'"],' . PHP_EOL;
          break;
      }
    }

    $js = rtrim($js, ",\r\n") . PHP_EOL
        . '  });';

    document::$snippets['javascript']['featherlight-'.$selector] = $js;
  }

  function draw_pagination($pages) {

    $pages = ceil($pages);

    if ($pages < 2) return false;

    if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) $_GET['page'] = 1;

    if ($_GET['page'] > 1) document::$snippets['head_tags']['prev'] = '<link rel="prev" href="'. document::href_link($_SERVER['REQUEST_URI'], ['page' => $_GET['page']-1]) .'" />';
    if ($_GET['page'] < $pages) document::$snippets['head_tags']['next'] = '<link rel="next" href="'. document::href_link($_SERVER['REQUEST_URI'], ['page' => $_GET['page']+1]) .'" />';
    if ($_GET['page'] < $pages) document::$snippets['head_tags']['prerender'] = '<link rel="prerender" href="'. document::href_link($_SERVER['REQUEST_URI'], ['page' => $_GET['page']+1]) .'" />';

    $pagination = new ent_view();

    $pagination->snippets['items'][] = [
      'page' => $_GET['page']-1,
      'title' => language::translate('title_previous', 'Previous'),
      'link' => document::link($_SERVER['REQUEST_URI'], ['page' => $_GET['page']-1]),
      'disabled' => ($_GET['page'] <= 1) ? true : false,
      'active' => false,
    ];

    for ($i=1; $i<=$pages; $i++) {

      if ($i < $pages-5) {
        if ($i > 1 && $i < $_GET['page'] - 1 && $_GET['page'] > 4) {
          $rewind = round(($_GET['page'] - 1) / 2);
          $pagination->snippets['items'][] = [
            'page' => $rewind,
            'title' => ($rewind == $_GET['page']-2) ? $rewind : '...',
            'link' => document::link($_SERVER['REQUEST_URI'], ['page' => $rewind]),
            'disabled' => false,
            'active' => false,
          ];
          $i = $_GET['page'] - 1;
          if ($i > $pages-4) $i = $pages-4;
        }
      }

      if ($i > 5) {
        if ($i > $_GET['page'] + 1 && $i < $pages) {
          $forward = round(($_GET['page']+1+$pages)/2);
          $pagination->snippets['items'][] = [
            'page' => $forward,
            'title' => ($forward == $_GET['page']+2) ? $forward : '...',
            'link' => document::link($_SERVER['REQUEST_URI'], ['page' => $forward]),
            'disabled' => false,
            'active' => false,
          ];
          $i = $pages;
        }
      }

      $pagination->snippets['items'][] = [
        'page' => $i,
        'title' => $i,
        'link' => document::link($_SERVER['REQUEST_URI'], ['page' => $i]),
        'disabled' => false,
        'active' => ($i == $_GET['page']) ? true : false,
      ];
    }

    $pagination->snippets['items'][] = [
      'page' => $_GET['page']+1,
      'title' => language::translate('title_next', 'Next'),
      'link' => document::link($_SERVER['REQUEST_URI'], ['page' => $_GET['page']+1]),
      'disabled' => ($_GET['page'] >= $pages) ? true : false,
      'active' => false,
    ];

    $html = $pagination->stitch('views/pagination');

    return $html;
  }
