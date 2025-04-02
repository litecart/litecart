<?php

	function draw_banner($keywords, $limit=0) {

		if (!is_array($keywords)) {
			$keywords = preg_split('#\s*,\s*#', $keywords, -1, PREG_SPLIT_NO_EMPTY);
		}

		$sql_where_keywords = "(". implode(" or ", array_map(function($k){
			return "find_in_set('". database::input($k) ."', keywords)";
		}, $keywords)) .")";

		$banners = database::query(
			"select * from ". DB_TABLE_PREFIX ."banners
			where status
			and (image != '' or html != '')
			and $sql_where_keywords
			order by rand()
			". ($limit ? "limit ". (int)$limit : '') .";"
		)->fetch_all();

		if (!$banners) return;

		database::query(
			"update ". DB_TABLE_PREFIX ."banners
			set total_views = total_views + 1
			where id in ('". implode("', '", database::input(array_column($banners, 'id'))) ."');"
		);

		foreach ($banners as $key => $banner) {

			if (!$banner['html']) {
				$banner['html'] = '<img src="$image_url" alt="" style="width: 100%; vertical-align: middle;">';

				if ($banner['link']) {
					$banner['html'] = implode(PHP_EOL, [
						'<a href="$target_url">',
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

		if (count($banners) == 1) {
			return $banners[0]['output'];
		}

		$carousel = new ent_view('app://frontend/templates/'. settings::get('template') .'/partials/carousel.inc.php');
		$carousel->snippets['items'] = array_column($banners, 'output');
		return $carousel->render();

		return $output;
	}

	function draw_element($name, $parameters=[], $content='') {

		$parameters = implode(' ', array_map(function($key, $value) {

			if ($value == '') {
				return $key;
			} else {
				return $key .'="'. functions::escape_attr($value) .'"';
			}

		}, array_keys($parameters, $parameters)));

		return '<'. $name . ($parameters ? ' ' . $parameters : '') .'>'. $content .'</'. $name .'>';
	}

	function draw_fonticon($icon, $parameters='') {

		switch(true) {

			// LiteCore Fonticons
			case (preg_match('#^icon-#', $icon)):
				return '<i class="'. $icon .'"'. ($parameters ? ' ' . $parameters : '') .'></i>';

			// Bootstrap Icons
			case (preg_match('#^bi-#', $icon)):
				document::$head_tags['bootstrap-icons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">';
				return '<i class="bi '. $icon .'"'. ($parameters ? ' ' . $parameters : '') .'></i>';

			// Fontawesome 4
			case (preg_match('#^fa-#', $icon)):
				trigger_error('Fontawesome 4 icon `'. functions::escape_html($icon) .'` is deprecated. Please use Fontawesome 5 instead.', E_USER_DEPRECATED);
				document::$head_tags['fontawesome4'] = '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/v4-shims.css">';
				document::$head_tags['fontawesome5'] = '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">';
				return '<i class="fa '. $icon .'"'. ($parameters ? ' ' . $parameters : '') .'></i>';

			// Fontawesome 5
			case (preg_match('#^far fa-#', $icon)):
			case (preg_match('#^fab fa-#', $icon)):
			case (preg_match('#^fas fa-#', $icon)):
				document::$head_tags['fontawesome5'] = '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">';
				return '<i class="'. $icon .'"'. ($parameters ? ' ' . $parameters : '') .'></i>';

			// Foundation
			case (preg_match('#^fi-#', $icon)):
				document::$head_tags['foundation-icons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/foundation-icons/latest/foundation-icons.min.css">';
				return '<i class="'. $icon .'"'. ($parameters ? ' ' . $parameters : '') .'></i>';

			// Ion Icons
			case (preg_match('#^ion-#', $icon)):
				document::$head_tags['ionicons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/ionicons/latest/css/ionicons.min.css">';
				return '<i class="'. $icon .'"'. ($parameters ? ' ' . $parameters : '') .'></i>';

			// Material Design Icons
			case (preg_match('#^mdi-#', $icon)):
				document::$head_tags['material-design-icons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css">';
				return '<i class="mdi '. $icon .'"'. ($parameters ? ' ' . $parameters : '') .'></i>';
		}

		switch ($icon) {
			case 'add':         return draw_fonticon('icon-square-pen');
			case 'cancel':      return draw_fonticon('icon-times');
			case 'company':     return draw_fonticon('icon-building', 'style="color: #888;"');
			case 'delete':      return draw_fonticon('icon-trash');
			case 'download':    return draw_fonticon('icon-download');
			case 'edit':        return draw_fonticon('icon-pen');
			case 'failed':      return draw_fonticon('icon-times', 'style="color: #c00;"');
			case 'false':       return draw_fonticon('icon-times', 'style="color: #c00;"');
			case 'female':      return draw_fonticon('icon-female', 'style="color: #e77be9;"');
			case 'folder':      return draw_fonticon('icon-folder', 'style="color: #cc6;"');
			case 'folder-open': return draw_fonticon('icon-folder-open', 'style="color: #cc6;"');
			case 'group':       return draw_fonticon('icon-group', 'style="color: #888;"');
			case 'remove':      return draw_fonticon('icon-times', 'style="color: #c33;"');
			case 'male':        return draw_fonticon('icon-male', 'style="color: #0a94c3;"');
			case 'move-up':     return draw_fonticon('icon-arrow-up', 'style="color: #39c;"');
			case 'move-down':   return draw_fonticon('icon-arrow-down', 'style="color: #39c;"');
			case 'ok':          return draw_fonticon('icon-check', 'style="color: #8c4;"');
			case 'on':          return draw_fonticon('icon-bullet', 'style="color: #8c4;"');
			case 'off':         return draw_fonticon('icon-bullet', 'style="color: #f64;"');
			case 'print':       return draw_fonticon('icon-print', 'style="color: #ded90f;"');
			case 'remove':      return draw_fonticon('icon-times', 'style="color: #c00;"');
			case 'secure':      return draw_fonticon('icon-lock');
			case 'semi-off':    return draw_fonticon('icon-bullet', 'style="color: #ded90f;"');
			case 'save':        return draw_fonticon('icon-memory-card');
			case 'send':        return draw_fonticon('icon-paper-plane');
			case 'success':     return draw_fonticon('icon-check', 'style="color: #8c4;"');
			case 'true':        return draw_fonticon('icon-check', 'style="color: #8c4;"');
			case 'user':        return draw_fonticon('icon-user', 'style="color: #888;"');
			case 'warning':     return draw_fonticon('icon-exclamation-triangle', 'style="color: #c00;"');
			default: trigger_error('Unknown font icon ('. $icon .')', E_USER_WARNING); return;
		}
	}

	function draw_image($image, $width=null, $height=null, $clipping='fit', $parameters='') {

		if ($width && $height) {
			if (preg_match('#style="#', $parameters)) {
				$parameters = preg_replace('#style="(.*?)"#', 'style="$1 aspect-ratio: '. functions::image_aspect_ratio($width, $height) .';"', $parameters);
			} else {
				$parameters .= ' style="aspect-ratio: '. functions::image_aspect_ratio($width, $height) .';"';
			}
		}

		return '<img '. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="'. functions::escape_attr($clipping) .'"' : '') .' src="'. document::href_rlink($image) .'" '. ($parameters ? ' '. $parameters : '') .'>';
	}

	function draw_script($src) {

		if (preg_match('#^(app|storage)://#', $src)) {
			$tag = '<script defer integrity="sha256-'. base64_encode(hash_file('sha256', $src, true)) .'" src="'. document::href_rlink($src) .'"></script>';
		} else {
			$tag = '<script src="'. document::href_link($src) .'">'. $content .'</script>';
		}

		return $tag;
	}

	function draw_style($href) {

		if (preg_match('#^(app|storage)://#', $href)) {
			$tag = '<link rel="stylesheet" integrity="sha256-'. base64_encode(hash_file('sha256', $href, true)) .'" href="'. document::href_rlink($href) .'">';
		} else {
			$tag = '<link rel="stylesheet" href="'. document::href_link($href) .'">';
		}

		return $tag;
	}

	function draw_thumbnail($image, $width=0, $height=0, $clipping='fit', $parameters='') {

		if (!is_file($image)) {
			$image = 'storage://images/no_image.svg';
		}

		if (!$width && !$height) {
			$entity = new ent_image($image);
			$width = $entity->width;
			$height = $entity->height;
		}

		if (!$width) {
			if ($clipping == 'product') {
				list($width, $height) = functions::image_scale_by_height($height, settings::get('product_image_ratio'));
			} else if ($clipping == 'category') {
				list($width, $height) = functions::image_scale_by_height($height, settings::get('category_image_ratio'));
			} else {
				$aspect_ratio = (new ent_image($image))->aspect_ratio;
				list($width, $height) = functions::image_scale_by_height($height, $aspect_ratio);
			}
		}

		if (!$height) {
			if ($clipping == 'product') {
				list($width, $height) = functions::image_scale_by_width($width, settings::get('product_image_ratio'));
			} else if ($clipping == 'category') {
				list($width, $height) = functions::image_scale_by_width($width, settings::get('category_image_ratio'));
			} else {
				$aspect_ratio = (new ent_image($image))->aspect_ratio;
				list($width, $height) = functions::image_scale_by_width($width, $aspect_ratio);
			}
		}

		if (empty($aspect_ratio)) {
			$aspect_ratio = functions::image_aspect_ratio($width, $height);
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

		$thumbnail = functions::image_thumbnail($image, $width, $height);
		$thumbnail_2x = functions::image_thumbnail($image, $width*2, $height*2);

		if ($width && $height) {
			if (preg_match('#style="#', $parameters)) {
				$parameters = preg_replace('#style="(.*?)"#', 'style="$1 aspect-ratio: '. $aspect_ratio .';"', $parameters);
			} else {
				$parameters .= ' style="aspect-ratio: '. $aspect_ratio .';"';
			}
		}

		return '<img '. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="thumbnail '. functions::escape_attr($clipping) .'"' : '') .' src="'. document::href_rlink($thumbnail) .'" srcset="'. document::href_rlink($thumbnail) .' 1x, '. document::href_rlink($thumbnail_2x) .' 2x"'. ($parameters ? ' '. $parameters : '') .'>';
	}

	function draw_price_tag($regular_price, $final_price=null, $recommended_price=null, $currency_code=null) {

		$price_tag = [];

		if (!isset($regular_price)) {
			if (isset($final_price)) {
				$regular_price = $final_price;
			} else {
				return;
			}
		}

		if (!isset($currency_code)) {
			$currency_code = currency::$selected['code'];
		}

		$price_tag[] = '<div class="price-tag">';

		if ($recommended_price > 0 && $recommended_price < $regular_price) {
			$price_tag[] = '<del class="recommended-price">'. currency::format($recommended_price) .'</del> <strong class="price">'. currency::format($regular_price) .'</strong>';
		} else if ($regular_price > $final_price) {
			$price_tag[] = '  <del class="regular-price">'. currency::format($regular_price) .'</del> <strong class="sale-price">'. currency::format($final_price) .'</strong>';
		} else {
			$price_tag[] = '<span class="price">'. currency::format($final_price) .'</span>';
		}

		$price_tag[] = '</div>';

		return implode(PHP_EOL, $price_tag);
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
			'num_stock_options' => $product['num_stock_options'],
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
			'quantity' => isset($product['quantity']) ? $product['quantity'] : null,
			'quantity_unit_id' => $product['quantity_unit_id'],
			'quantity_available' => isset($product['quantity_available']) ? $product['quantity_available'] : null,
			'recommended_price' => isset($product['recommended_price']) ? tax::get_price($product['recommended_price'], $product['tax_class_id']) : null,
			'regular_price' => isset($product['price']) ? tax::get_price($product['price'], $product['tax_class_id']) : null,
			'campaign_price' => isset($product['campaign_price']) ? tax::get_price($product['campaign_price'], $product['tax_class_id']) : null,
			'final_price' => isset($product['final_price']) ? tax::get_price($product['final_price'], $product['tax_class_id']) : null,
			'tax' => isset($product['price']) ? tax::get_tax($product['price'], $product['tax_class_id']) : null,
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

		if (!$selector && !$parameters) return;

		if (preg_match('#^(https?:)?//#', $selector)) {
			$js = ['$.litebox(\''. $selector .'\', {'];

		} else if ($selector) {
			$js = ['$(\''. $selector .'\').litebox({'];

		} else {
			$js = ['$.litebox({'];
		}

		foreach ($parameters as $key => $value) {
			switch (gettype($parameters[$key])) {

				case 'NULL':
					$js[] = '  '. $key .': null,';
					break;

				case 'boolean':
					$js[] = '  '. $key .': '. ($value ? 'true' : 'false') .',';
					break;

				case 'integer':
					$js[] = '  '. $key .': '. $value .',';
					break;

				case 'string':
					if (preg_match('#^\s*function\s*\(#', $value)) {
						$js[] = '  '. $key .': '. $value .',';
					} else {
						$js[] = '  '. $key .': "'. addslashes($value) .'",';
					}
					break;

				case 'array':
					$js[] = '  '. $key .': ["'. implode('", "', $value) .'"],';
					break;
			}
		}

		$js[] = '})';

		document::add_script($js, 'litebox-'. $selector);
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

	// ▮▮▮▯▯▯▯▯▯▯▯▯ 25%
	function draw_progress_bar($progress, $width=15) {
		$percentage = floor($progress);
		return str_pad(str_repeat('▮', floor(($width / 100) * $percentage)), $width, '▯', STR_PAD_RIGHT) . ' '. $percentage .'%';
	}
