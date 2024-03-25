<?php

  function draw_image($image, $width=null, $height=null, $clipping='fit', $parameters='') {

    if ($width && $height) {
      if (preg_match('#style="#', $parameters)) {
        $parameters = preg_replace('#style="(.*?)"#', 'style="$1 aspect-ratio: '. functions::image_aspect_ratio($width, $height) .';"', $parameters);
      } else {
        $parameters .= ' style="aspect-ratio: '. functions::image_aspect_ratio($width, $height) .';"';
      }
    }

    return '<img '. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="'. functions::escape_html($clipping) .'"' : '') .' src="'. document::href_rlink($image) .'" '. ($parameters ? ' '. $parameters : '') .'>';
  }

  function draw_thumbnail($source, $width, $height, $clipping='fit', $parameters='') {

    if (!is_file($source)) {
      $source = 'storage://images/no_image.png';
    }

    if (!$width) {
      if ($clipping == 'product') {
        list($width, $height) = functions::image_scale_by_height($height, settings::get('product_image_ratio'));
      } else if ($clipping == 'category') {
        list($width, $height) = functions::image_scale_by_height($height, settings::get('category_image_ratio'));
      } else {
        list($width, $height) = functions::image_scale_by_height($height, functions::image_ratio($source));
      }
    }

    if (!$height) {
      if ($clipping == 'product') {
        list($width, $height) = functions::image_scale_by_width($width, settings::get('product_image_ratio'));
      } else if ($clipping == 'category') {
        list($width, $height) = functions::image_scale_by_width($width, settings::get('category_image_ratio'));
      } else {
        list($width, $height) = functions::image_scale_by_width($width, functions::image_ratio($source));
      }
    }

    switch (strtolower($clipping)) {

      case '':
        $clipping = '';
        break;

      case 'fit':
        $clipping = 'fit';
        break;

      case 'crop':
        $clipping = 'crop';
        break;

      case 'product':
        $clipping = strtolower(settings::get('product_image_clipping'));
        break;

      case 'category':
        $clipping = strtolower(settings::get('category_image_clipping'));
        break;

      default:
        trigger_error('Invalid clipping mode ('. $clipping .')', E_USER_WARNING);
        break;
    }

    $thumbnail = functions::image_thumbnail($source, $width, $height);
    $thumbnail_2x = functions::image_thumbnail($source, $width*2, $height*2);


    if ($width && $height) {
      if (preg_match('#style="#', $parameters)) {
        $parameters = preg_replace('#style="(.*?)"#', 'style="$1 aspect-ratio: '. functions::image_aspect_ratio($width, $height) .';"', $parameters);
      } else {
        $parameters .= ' style="aspect-ratio: '. functions::image_aspect_ratio($width, $height) .';"';
      }
    }

    return '<img '. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="thumbnail '. functions::escape_html($clipping) .'"' : '') .' src="'. document::href_rlink($thumbnail) .'" srcset="'. document::href_rlink($thumbnail) .' 1x, '. document::href_rlink($thumbnail_2x) .' 2x"'. ($parameters ? ' '. $parameters : '') .'>';
  }

  function draw_banner($keywords, $limit=0) {

    if (!is_array($keywords)) {
      $keywords = preg_split('#\s*,\s*#', $keywords, -1, PREG_SPLIT_NO_EMPTY);
    }

    $banners = database::query(
      "select * from ". DB_TABLE_PREFIX ."banners
      where status
      and (image != '' or html != '')
      and (". implode(" or ", array_map(function($k){ return "find_in_set('". database::input($k) ."', keywords)"; }, $keywords)) .")
      order by rand()
      ". ($limit ? "limit ". (int)$limit : '') .";"
    )->fetch_all();

    if (!$banners) return;

    database::query(
      "update ". DB_TABLE_PREFIX ."banners
      set total_views = total_views + 1
      where id in ('". implode("', '", array_column($banners, 'id')) ."');"
    );

    foreach ($banners as $key => $banner) {

      if (!$banner['html']) {
        $banner['html'] = '<img src="$image_url" alt="" style="width: 100%; vertical-align: middle;">';

        if ($banner['link']) {
          $banner['html'] = implode(PHP_EOL, [
            '<a href="$target_url" target="_blank">',
            '  ' . $banner['html'],
            '</a>',
          ]);
        }
      }

      $aliases = [
        '$id' => $banner['id'],
        '$language_code' => language::$selected['code'],
        '$image_url' => $banner['image'] ? document::rlink('storage://images/' . $banner['image']) : '',
        '$target_url' => $banner['link'] ? document::href_link($banner['link']) : '',
      ];

      $output = implode(PHP_EOL, [
        '<div class="banner" data-id="'. $banner['id'] .'" data-name="'. $banner['name'] .'">',
        '  '. strtr($banner['html'], $aliases),
        '</div>',
      ]);

      $banners[$key]['output'] = $output;
    }

    // Banner Click Tracking
    document::$javascript['banner-click-tracking'] = implode(PHP_EOL, [
      '  var mouseOverAd = false;',
      '  $(\'.banner[data-id]\').hover(function(){',
      '    mouseOverAd = $(this).data("id");',
      '  }, function(){',
      '    mouseOverAd = false;',
      '  });',
      '  $(\'.banner[data-id]\').click(function(){',
      '    $.post("'. document::ilink('ajax/bct') .'", "banner_id=" + $(this).data("id"));',
      '  });',
      '  $(window).blur(function(){',
      '    if (mouseOverAd){',
      '      $.post("'. document::ilink('ajax/bct') .'", "banner_id=" + mouseOverAd);',
      '    }',
      '  });',
    ]);

    if (count($banners) == 1) {
      return $banners[0]['output'];
    }

    $carousel = new ent_view('app://frontend/templates/'. settings::get('template') .'/partials/carousel.inc.php');
    $carousel->snippets['items'] = array_column($banners, 'output');
    return $carousel->render();


    return $output;
  }

  function draw_fonticon($class, $parameters=null) {

    switch(true) {

    // Bootstrap Icons
      case (substr($class, 0, 3) == 'bi-'):
        document::$head_tags['bootstrap-icons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">';
        return '<i class="bi '. $class .'"'. (!empty($parameters) ? ' ' . $parameters : '') .'></i>';

    // Fontawesome 4
      case (substr($class, 0, 3) == 'fa-'):
        //document::$head_tags['fontawesome'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/fontawesome/4.7.0/css/font-awesome.min.css">'; // Uncomment if removed from lib_document
        return '<i class="fa '. $class .'"'. (!empty($parameters) ? ' ' . $parameters : '') .'></i>';

    // Foundation
      case (substr($class, 0, 3) == 'fi-'):
        document::$head_tags['foundation-icons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/foundation-icons/latest/foundation-icons.min.css">';
        return '<i class="'. $class .'"'. (!empty($parameters) ? ' ' . $parameters : '') .'></i>';

    // Glyphicon
      case (substr($class, 0, 10) == 'glyphicon-'):
        //document::$head_tags['foundation-icons'] = '<link rel="stylesheet" href="'/path/to/glyphicon.min.css">'; // Not embedded in release
        return '<span class="glyphicon '. $class .'"'. (!empty($parameters) ? ' ' . $parameters : '') .'></span>';

    // Ion Icons
      case (substr($class, 0, 4) == 'ion-'):
        document::$head_tags['ionicons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/ionicons/latest/css/ionicons.min.css">';
        return '<i class="'. $class .'"'. (!empty($parameters) ? ' ' . $parameters : '') .'></i>';

    // Material Design Icons
      case (substr($class, 0, 4) == 'mdi-'):
        document::$head_tags['material-design-icons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css">';
        return '<i class="mdi '. $class .'"'. (!empty($parameters) ? ' ' . $parameters : '') .'></i>';
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

    $listing_category = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/listing_category.inc.php');

    $listing_category->snippets = [
      'category_id' => $category['id'],
      'name' => $category['name'],
      'link' => document::ilink('category', ['category_id' => $category['id']]),
      'image' => $category['image'] ? 'storage://images/' . $category['image'] : '',
      'short_description' => $category['short_description'],
    ];

    return $listing_category->render();
  }

  function draw_listing_product($product, $inherit_params=[], $view='views/listing_product') {

    $listing_product = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/listing_product.inc.php');

    $sticker = '';
    if ($product['campaign_price']) {
      $sticker = '<div class="sticker sale" title="'. language::translate('title_on_sale', 'On Sale') .'">'. language::translate('sticker_sale', 'Sale') .'</div>';
    } else if ($product['date_created'] > date('Y-m-d', strtotime('-'.settings::get('new_products_max_age')))) {
      $sticker = '<div class="sticker new" title="'. language::translate('title_new', 'New') .'">'. language::translate('sticker_new', 'New') .'</div>';
    }

    list($width, $height) = functions::image_scale_by_width(320, settings::get('product_image_ratio'));

    $listing_product->snippets = [
      'product_id' => $product['id'],
      'code' => $product['code'],
      'sku' => fallback($product['sku'], ''),
      'gtin' => fallback($product['gtin'], ''),
      'mpn' => fallback($product['mpn'], ''),
      'name' => $product['name'],
      'link' => document::ilink('product', ['product_id' => $product['id']], $inherit_params),
      'image' => $product['image'] ? 'storage://images/' . $product['image'] : '',
      'sticker' => $sticker,
      'brand' => [],
      'short_description' => $product['short_description'],
      'quantity' => $product['quantity'],
      'quantity_unit_id' => $product['quantity_unit_id'],
      'quantity_available' => $product['quantity_available'],
      'recommended_price' => tax::get_price($product['recommended_price'], $product['tax_class_id']),
      'regular_price' => tax::get_price($product['price'], $product['tax_class_id']),
      'campaign_price' => $product['campaign_price'] ? tax::get_price($product['campaign_price'], $product['tax_class_id']) : null,
      'final_price' => tax::get_price($product['final_price'], $product['tax_class_id']),
      'tax' => tax::get_tax($product['price'], $product['tax_class_id']),
      'tax_class_id' => $product['tax_class_id'],
      'delivery_status_id' => $product['delivery_status_id'],
      'sold_out_status_id' => $product['sold_out_status_id'],
    ];

    if (!empty($product['brand_id'])) {
      $listing_product->snippets['brand'] = [
        'id' => $product['brand_id'],
        'name' => $product['brand_name'],
      ];
    }

  // Watermark Original Image
    if (settings::get('product_image_watermark')) {
      $listing_product->snippets['image']['original'] = functions::image_process(FS_DIR_APP . $listing_product->snippets['image']['original'], ['watermark' => true]);
    }

    return $listing_product->render();
  }

  function draw_lightbox($selector='', $parameters=[]) {

    document::load_style('app://assets/featherlight/featherlight.min.css', 'featherlight');
    document::load_script('app://assets/featherlight/featherlight.min.js', 'featherlight');
    document::$javascript['featherlight'] = implode(PHP_EOL, [
      "$.featherlight.autoBind = '[data-toggle=\"lightbox\"]';",
      "$.featherlight.defaults.loading = '<div class=\"loader\" style=\"width: 128px; height: 128px; opacity: 0.5;\"></div>';",
      "$.featherlight.defaults.closeIcon = '&#x2716;';",
      "$.featherlight.defaults.targetAttr = 'data-target';",
    ]);

    $selector = str_replace("'", '"', $selector);

    if (empty($selector)) return;

    if (preg_match('#^(https?:)?//#', $selector)) {
      $js = ['  $.featherlight(\''. $selector .'\', {'];
    } else {
      $js = ['  $(\''. $selector .'\').featherlight({'];
    }

    foreach ($parameters as $key => $value) {
      switch (gettype($parameters[$key])) {

        case 'NULL':
          $js[] = '    '. $key .': null,';
          break;

        case 'boolean':
          $js[] = '    '. $key .': '. ($value ? 'true' : 'false') .',';
          break;

        case 'integer':
          $js[] = '    '. $key .': '. $value .',';
          break;

        case 'string':
          if (preg_match('#^function\s*\(#', $value)) {
            $js[] = '    '. $key .': '. $value .',';
          } else {
            $js[] = '    '. $key .': "'. addslashes($value) .'",';
          }
          break;

        case 'array':
          $js[] = '    '. $key .': ["'. implode('", "', $value) .'"],';
          break;
      }
    }

    $js[] = '  });';

    document::$javascript['featherlight-'.$selector] = implode(PHP_EOL, $js);
  }

  function draw_pagination($pages) {

    $pages = ceil($pages);

    if ($pages < 2) return false;

    if (!isset($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
       $_GET['page'] = 1;
    }

    if ($_GET['page'] > 1) {
      document::$head_tags['prev'] = '<link rel="prev" href="'. document::href_link($_SERVER['REQUEST_URI'], ['page' => $_GET['page']-1]) .'">';
    }

    if ($_GET['page'] < $pages) {
      document::$head_tags['next'] = '<link rel="next" href="'. document::href_link($_SERVER['REQUEST_URI'], ['page' => $_GET['page']+1]) .'">';
    }

    if ($_GET['page'] < $pages) {
      document::$head_tags['prerender'] = '<link rel="prerender" href="'. document::href_link($_SERVER['REQUEST_URI'], ['page' => $_GET['page']+1]) .'">';
    }

    $pagination = new ent_view('app://frontend/templates/'. settings::get('template') .'/partials/pagination.inc.php');

    $pagination->snippets['items'][] = [
      'page' => $_GET['page']-1,
      'title' => language::translate('title_previous', 'Previous'),
      'link' => document::link($_SERVER['REQUEST_URI'], ['page' => $_GET['page']-1]),
      'disabled' => ($_GET['page'] <= 1),
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
        'active' => ($i == $_GET['page']),
      ];
    }

    $pagination->snippets['items'][] = [
      'page' => $_GET['page']+1,
      'title' => language::translate('title_next', 'Next'),
      'link' => document::link($_SERVER['REQUEST_URI'], ['page' => $_GET['page']+1]),
      'disabled' => ($_GET['page'] >= $pages) ? true : false,
      'active' => false,
    ];

    return (string)$pagination;
  }
