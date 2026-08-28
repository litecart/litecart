<?php

	function draw_banner(array|string $keywords, int $limit=0): string|null {

		if (!is_array($keywords)) {
			$keywords = preg_split('#\s*,\s*#', $keywords, -1, PREG_SPLIT_NO_EMPTY);
		}

		$sql_where_keywords = "(". implode(" or ", f::array_each($keywords, fn($keyword) =>
			"find_in_set('". database::input($keyword) ."', keywords)"
		)) .")";

		$banners = database::query(
			"select * from ". DB_TABLE_PREFIX ."banners
			where status
			and (image != '' or html != '')
			and $sql_where_keywords
			order by rand()
			". ($limit ? "limit ". (int)$limit : '') .";"
		)->fetch_all();

		if (!$banners) return null;

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
	}

	function draw_element(string $tag, array $attributes=[], string $content=''): string {

		if (is_array($attributes)) {
			$attributes = implode(' ', array_map(function($key, $value) {

				$boolean_attributes = [
					'allowfullscreen', 'async', 'autofocus', 'autoplay', 'checked', 'controls', 'default',
					'defer', 'disabled', 'formnovalidate', 'hidden', 'inert', 'ismap', 'itemscope',
					'loop', 'multiple', 'muted', 'nomodule', 'novalidate', 'open', 'playsinline',
					'readonly', 'required', 'reversed', 'selected',
				];

				if (in_array(strtolower((string)$key), $boolean_attributes, true)) {
					return $key;
				} else {
					return $key .'="'. f::escape_attr($value) .'"';
				}

			}, array_keys($attributes), $attributes));
		}

		if (in_array($tag, ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'])) {
			if ($content) {
				trigger_error('Self-closing tags should not have content', E_USER_WARNING);
			}
			return '<'. $tag . ($attributes ? ' '. $attributes : '') .'>';
		}

		if (preg_match('#^<[a-z]#i', $content, $m)) {
			return implode(PHP_EOL, [
				'<'. $tag . ($attributes ? ' '. $attributes : '') .'>',
				'  '. $content,
				'</'. $tag .'>',
			]);
		}

		return '<'. $tag . ($attributes ? ' ' . $attributes : '') .'>'. $content .'</'. $tag .'>';
	}

	function draw_fonticon(string $icon, array|string $attributes=[]): string {

		if (!$icon) {
			return '';
		}

		$attributes = is_array($attributes) ? $attributes : f::form_attributes($attributes);

		switch(true) {

			// Graphics elements
			case (preg_match('#\.(avif|gif|jpe?g|png|webp|svg)$#', $icon)):
				return draw_element('img', ['class' => 'icon', 'src' => document::rlink($icon), ...$attributes]);

			// LiteCore Fonticons
			case (preg_match('#^icon-#', $icon)):
				return draw_element('i', ['class' => 'icon '. $icon, ...$attributes]);

			// Bootstrap Icons
			case (preg_match('#^bi-#', $icon)):
				document::$head_tags['bootstrap-icons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">';
				return draw_element('i', ['class' => 'bi '. $icon, ...$attributes]);

			// Fontawesome 4
			case (preg_match('#^fa-#', $icon)):
				trigger_error('Fontawesome 4 icon `'. f::escape_html($icon) .'` is deprecated. Please use Fontawesome 5 instead.', E_USER_DEPRECATED);
				document::$head_tags['fontawesome4'] = '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/v4-shims.css">';
				document::$head_tags['fontawesome5'] = '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">';
				return draw_element('i', ['class' => 'fa '. $icon, ...$attributes]);

			// Fontawesome 7
			case (substr($icon, 0, 6) == 'fa fa-'):
			case (substr($icon, 0, 7) == 'far fa-'):
			case (substr($icon, 0, 7) == 'fab fa-'):
			case (substr($icon, 0, 7) == 'fas fa-'):
				document::$foot_tags['fontawesome7'] = '<script src="https://use.fontawesome.com/releases/v7.1.0/js/all.js" crossorigin="anonymous"></script>';
				return draw_element('i', ['class' => $icon, ...$attributes]);

			// Foundation
			case (preg_match('#^fi-#', $icon)):
				document::$head_tags['foundation-icons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/foundation-icons/latest/foundation-icons.min.css">';
				return draw_element('i', ['class' => $icon, ...$attributes]);

			// Ion Icons
			case (preg_match('#^ion-#', $icon)):
				document::$head_tags['ionicons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/ionicons/latest/css/ionicons.min.css">';
				return draw_element('i', ['class' => $icon, ...$attributes]);

			// Material Design Icons
			case (preg_match('#^mdi-#', $icon)):
				document::$head_tags['material-design-icons'] = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css">';
				return draw_element('i', ['class' => 'mdi '. $icon, ...$attributes]);
		}

		return match($icon) {
			'add'         => draw_fonticon('icon-plus'),
			'cancel'      => draw_fonticon('icon-times'),
			'create'      => draw_fonticon('icon-square-pen'),
			'company'     => draw_fonticon('icon-building', 'style="color: #888;"'),
			'delete'      => draw_fonticon('icon-trash'),
			'download'    => draw_fonticon('icon-download'),
			'edit'        => draw_fonticon('icon-pen'),
			'failed'      => draw_fonticon('icon-times', 'style="color: #c00;"'),
			'false'       => draw_fonticon('icon-times', 'style="color: #c00;"'),
			'female'      => draw_fonticon('icon-female', 'style="color: #e77be9;"'),
			'folder'      => draw_fonticon('icon-folder', 'style="color: #cc6;"'),
			'folder-open' => draw_fonticon('icon-folder-open', 'style="color: #cc6;"'),
			'group'       => draw_fonticon('icon-group', 'style="color: #888;"'),
			'remove'      => draw_fonticon('icon-times', 'style="color: #c33;"'),
			'male'        => draw_fonticon('icon-male', 'style="color: #0a94c3;"'),
			'move-up'     => draw_fonticon('icon-arrow-up', 'style="color: #39c;"'),
			'move-down'   => draw_fonticon('icon-arrow-down', 'style="color: #39c;"'),
			'ok'          => draw_fonticon('icon-check', 'style="color: #8c4;"'),
			'on'          => draw_fonticon('icon-bullet', 'style="color: #8c4;"'),
			'off'         => draw_fonticon('icon-bullet', 'style="color: #f64;"'),
			'print'       => draw_fonticon('icon-print', 'style="color: #ded90f;"'),
			'remove'      => draw_fonticon('icon-times', 'style="color: #c00;"'),
			'secure'      => draw_fonticon('icon-lock'),
			'semi-off'    => draw_fonticon('icon-bullet', 'style="color: #ded90f;"'),
			'save'        => draw_fonticon('icon-memory-card'),
			'send'        => draw_fonticon('icon-paper-plane'),
			'success'     => draw_fonticon('icon-check', 'style="color: #8c4;"'),
			'true'        => draw_fonticon('icon-check', 'style="color: #8c4;"'),
			'user'        => draw_fonticon('icon-user', 'style="color: #888;"'),
			'warning'     => draw_fonticon('icon-exclamation-triangle', 'style="color: #c00;"'),
			default =>    trigger_error('Unknown font icon ('. $icon .')', E_USER_WARNING) ? '' : '',
		};
	}

	function draw_image(string $image, int|null $width=null, int|null $height=null, string $clipping='fit', array|string $attributes=[]): string {

		$attributes = is_array($attributes) ? $attributes : f::form_attributes($attributes);

		if ($width && $height) {
			if (preg_match('#style="#', $attributes)) {
				$attributes = preg_replace('#style="(.*?)"#', 'style="$1 aspect-ratio: '. f::image_aspect_ratio($width, $height) .';"', $attributes);
			} else {
				$attributes .= ' style="aspect-ratio: '. f::image_aspect_ratio($width, $height) .';"';
			}
		}

		return draw_element('img', ['class' => $clipping, 'src' => document::rlink($image), ...$attributes]);
	}

	function draw_script(string $src): string {

		if (preg_match('#^(app|storage)://#', $src)) {
			$checksum = base64_encode(hash_file('sha256', $src, true));
			return draw_element('script', ['src' => document::href_rlink($src), 'defer' => true, 'nonce' => security::$data['nonce'], 'integrity' => 'sha256-'. $checksum,]);
		}

		return draw_element('script', ['src' => document::href_link($src), 'defer' => true, 'nonce' => security::$data['nonce']], $content);
	}

	function draw_style(string $href): string {

		if (preg_match('#^(app|storage)://#', $href)) {
			$checksum = base64_encode(hash_file('sha256', $href, true));
			return draw_element('link', ['rel' => 'stylesheet', 'href' => document::href_rlink($href), 'nonce' => security::$data['nonce'], 'integrity' => 'sha256-'. $checksum, ]);
		}

		return draw_element('link', ['rel' => 'stylesheet', 'href' => document::href_link($href), 'nonce' => security::$data['nonce']]);
	}

	function draw_thumbnail(string $image, int $width=0, int $height=0, string $clipping='fit', array|string $attributes=[]): string {

		if (!$image || !is_file($image)) {
			$image = 'storage://images/no_image.svg';
		}

		if (!$width && !$height) {
			$entity = new ent_image($image);
			$width = $entity->width;
			$height = $entity->height;
		}

		$target_ratio = match($clipping) {
			'product' => settings::get('product_image_ratio'),
			'category' => settings::get('category_image_ratio'),
			default => (new ent_image($image))->aspect_ratio
		};

		$attributes = is_array($attributes) ? $attributes : f::form_attributes($attributes);

		if (!$width) {
			[$width, $height] = f::image_scale_by_height($height, $target_ratio);
		}

		if (!$height) {
			[$width, $height] = f::image_scale_by_width($width, $target_ratio);
		}

		if (empty($aspect_ratio)) {
			$aspect_ratio = f::image_aspect_ratio($width, $height);
		}

		$clipping = match(strtolower($clipping)) {
			'' => '',
			'fit' => 'fit',
			'crop' => 'crop',
			'product' => strtolower(settings::get('product_image_clipping')),
			'category' => strtolower(settings::get('category_image_clipping')),
			default => trigger_error('Invalid clipping mode ('. $clipping .')', E_USER_WARNING),
		};

		$thumbnail = f::image_thumbnail($image, $width, $height);
		$thumbnail_2x = f::image_thumbnail($image, $width*2, $height*2);

		if ($width && $height) {
			if (!empty($attributes['style'])) {
				$attributes['style'] .= ' aspect-ratio: '. $aspect_ratio .';';
			} else {
				$attributes['style'] = 'aspect-ratio: '. $aspect_ratio .';';
			}
		}

		return draw_element('img', [
			'class' => 'thumbnail '. f::escape_attr($clipping),
			'src' => document::href_rlink($thumbnail),
			'srcset' => document::href_rlink($thumbnail) .' 1x, '. document::href_rlink($thumbnail_2x) .' 2x',
			...$attributes,
		]);
	}

	function draw_listing_category(array $category, string $view='views/listing_category'): string {

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

	function draw_listing_product(array $product, array $inherit_params=[], string $view='views/listing_product'): string {

		$listing_product = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/listing_product.inc.php');

		$sticker = '';
		if (!empty($product['campaign_scope_discount'])) {
			$sticker = '<div class="sticker sale" title="'. t('title_on_sale', 'On Sale') .'">-'. (int)$product['campaign_scope_discount'] .'%</div>';
		} else if ($product['final_price'] && $product['final_price'] < $product['regular_price']) {
			$sticker = '<div class="sticker sale" title="'. t('title_on_sale', 'On Sale') .'">'. t('sticker_sale', 'Sale') .'</div>';
		} else if ($product['created_at'] > date('Y-m-d', strtotime('-'.settings::get('new_products_max_age')))) {
			$sticker = '<div class="sticker new" title="'. t('title_new', 'New') .'">'. t('sticker_new', 'New') .'</div>';
		}

		list($width, $height) = f::image_scale_by_width(320, settings::get('product_image_ratio'));

		$is_favourite = database::query(
			"select id from ". DB_TABLE_PREFIX ."favorites
			where product_id = ". (int)$product['id'] ."
			and customer_id = ". (int)customer::$data['id'] ."
			limit 1;"
		)->num_rows();

		$listing_product->snippets = [
			'product_id' => $product['id'],
			'num_stock_options' => $product['num_stock_options'],
			'code' => $product['code'],
			'sku' => $product['sku'] ?? '',
			'gtin' => $product['gtin'] ?? '',
			'mpn' => $product['mpn'] ?? '',
			'name' => $product['name'],
			'link' => document::ilink('product', ['product_id' => $product['id']], $inherit_params),
			'image' => $product['image'] ? 'storage://images/' . $product['image'] : '',
			'sticker' => $sticker,
			'brand' => [],
			'short_description' => $product['short_description'],
			'quantity' => $product['quantity'] ?? null,
			'quantity_unit_id' => $product['quantity_unit_id'],
			'quantity_available' => $product['quantity_available'] ?? null,
			'recommended_price' => isset($product['recommended_price']) ? tax::get_price($product['recommended_price'], $product['tax_class_id']) : null,
			'regular_price' => isset($product['regular_price']) ? tax::get_price($product['regular_price'], $product['tax_class_id']) : null,
			'final_price' => isset($product['final_price']) ? tax::get_price($product['final_price'], $product['tax_class_id']) : null,
			'tax' => isset($product['regular_price']) ? tax::get_tax($product['regular_price'], $product['tax_class_id']) : null,
			'tax_class_id' => $product['tax_class_id'],
			'delivery_status_id' => $product['delivery_status_id'],
			'sold_out_status_id' => $product['sold_out_status_id'],
			'is_favourite' => $is_favourite,
			'rating' => $product['rating'] ?? null,
		];

		if (!empty($product['brand_id'])) {
			$listing_product->snippets['brand'] = [
				'id' => $product['brand_id'],
				'name' => $product['brand_name'],
			];
		}

		// Watermark Original Image
		if (settings::get('product_image_watermark')) {
			$listing_product->snippets['image']['original'] = f::image_process(FS_DIR_APP . $listing_product->snippets['image']['original'], ['watermark' => true]);
		}

		return $listing_product->render();
	}

	function draw_lightbox(string $selector='', array $attributes=[]): void {

		if (!$selector && !$attributes) return;

		if (preg_match('#^(https?:)?//#', $selector)) {
			$js = ['$.litebox(\''. $selector .'\', {'];

		} else if ($selector) {
			$js = ['$(\''. $selector .'\').litebox({'];

		} else {
			$js = ['$.litebox({'];
		}

		foreach ($attributes as $key => $value) {
			switch (gettype($attributes[$key])) {

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

		$js[] = '});';

		document::add_script($js, 'litebox-'. $selector);
	}

	function draw_pagination(int $pages): string|false {

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

		$pagination = new ent_view('app://frontend/templates/'. settings::get('template') .'/partials/pagination.inc.php', [
			'items' => [],
		]);

		$pagination->snippets['items'][] = [
			'page' => $_GET['page']-1,
			'title' => t('title_previous', 'Previous'),
			'link' => document::link($_SERVER['REQUEST_URI'], ['page' => $_GET['page']-1]),
			'rel'	=> 'prev',
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
			'title' => t('title_next', 'Next'),
			'link' => document::link($_SERVER['REQUEST_URI'], ['page' => $_GET['page']+1]),
			'rel'	=> 'next',
			'disabled' => ($_GET['page'] >= $pages) ? true : false,
			'active' => false,
		];

		return (string)$pagination;
	}

	function draw_price_tag(float|null $regular_price, float|null $final_price=null, string|null $currency_code=null, float|null $currency_value=null): string {

		if ($regular_price === null && $final_price === null) {
			return '';
		}

		if ($final_price > $regular_price) {
			list($regular_price, $final_price) = [$final_price, $regular_price];
		}

		if (!isset($currency_code)) {
			$currency_code = currency::$selected['code'];
		}

		if (!isset($currency_value)) {
			$currency_value = currency::$selected['value'];
		}

		$price_tag = ['<div class="price-tag">'];

		if ($final_price !== null && $final_price < $regular_price) {
			$price_tag[] = '	<del class="regular-price">'. currency::format($regular_price, true, $currency_code, $currency_value) .'</del> <strong class="sale-price">'. currency::format($final_price, true, $currency_code, $currency_value) .'</strong>';
		} else {
			$price_tag[] = '	<span class="regular-price">'. currency::format($regular_price, true, $currency_code, $currency_value) .'</span>';
		}

		$price_tag[] = '</div>';

		return implode(PHP_EOL, $price_tag);
	}


	// ▮▮▮▯▯▯▯▯▯▯▯▯▯▯▯ 25%
	function draw_progress_bar(float $progress, int $width=15): string {
		$percentage = floor($progress);
		return str_pad(str_repeat("\u{25AE}", floor(($width / 100) * $percentage)), $width, "\u{25AF}", STR_PAD_RIGHT) . ' '. $percentage .'%';
	}

	function draw_rating(float|null $rating, int $max_rating=5): string {

		if ($rating === null) {
			$rating = 0;
		}

		$rating = round($rating * 2) / 2;

		$output = '';

		foreach (range(1, $max_rating) as $i) {
			if ($rating >= $i) {
				$output .= draw_fonticon('icon-star', 'style="color: #f90;"');
			} else if ($rating == $i-0.5) {
				$output .= draw_fonticon('icon-star-half', 'style="color: #f90;"');
			} else {
				$output .= draw_fonticon('icon-star', 'style="color: #ccc;"');
			}
		}

		return $output;
	}
