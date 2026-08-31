<?php

	class job_sitemap_generator extends abs_module {

		public $id = __CLASS__;
		public $name = 'Sitemap Generator';
		public $description = 'Generate Sitemap Index and Sitemaps.';
		public $author = 'LiteCart Dev Team';
		public $version = '1.0';
		public $website = 'https://www.litecart.net';
		public $priority = 0;

		public function process(string $force, string $last_run): void {

			if (!$force) {

				if (empty($this->settings['status'])) return;

				switch ($this->settings['frequency']) {

					case 'Daily':
						if (date('Ymd', strtotime($last_run)) == date('Ymd')) return;
						break;

					case 'Weekly':
						if (date('W', strtotime($last_run)) == date('W')) return;
						break;

					case 'Monthly':
						if (date('Ym', strtotime($last_run)) == date('Ym')) return;
						break;
				}
			}

			if (!is_dir($dir = 'storage://cache/sitemap/')) {
				mkdir($dir);
			}

			// Clean up previous sitemaps
			f::file_delete('storage://cache/sitemap/sitemap-*.xml');

			// Define some functions
			$last_sitemap_path = null;
			$init_sitemap = function($index=1) use (&$fh, &$last_sitemap_path) {
				$last_sitemap_path = 'storage://cache/sitemap/sitemap-'. $index .'.xml';
				$fh = fopen($last_sitemap_path, 'x');
				fwrite($fh, implode(PHP_EOL, [
					'<?xml version="1.0" encoding="UTF-8"?>',
					'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">',
				]) . PHP_EOL);
			};

			$bump_sitemap = function() use (&$fh, &$close_sitemap, &$init_sitemap, &$last_sitemap_path) {
				$index = preg_replace('#^.*/sitemap-(\d+).xml$#', '$1', $last_sitemap_path);
				$close_sitemap($fh);
				$init_sitemap(++$index);
			};

			$close_sitemap = function() use (&$fh) {
				fwrite($fh, '</urlset>');
				fclose($fh);
			};

			$draw_url_node = function($resource, $params, $changefreq, $priority, $updated_at) use (&$fh, &$count, &$bump_sitemap) {

				$hreflangs = '';
				foreach (language::$languages as $language) {
					if ($language['code'] == settings::get('store_language_code')) continue;
					$hreflangs .= '		<xhtml:link rel="alternate" hreflang="'. $language['code'] .'" href="'. document::href_ilink($resource, $params, false, [], $language['code']) .'" />' . PHP_EOL;
				}

				fwrite($fh, implode(PHP_EOL, [
					'  <url>',
					'    <loc>'. document::ilink($resource, $params, false, [], settings::get('store_language_code')) .'</loc>',
					$hreflangs,
					'    <changefreq>'. $changefreq .'</changefreq>',
					'    <priority>'. $priority .'</priority>',
					'    <lastmod>'. date('Y-m-d', strtotime($updated_at)) .'</lastmod>',
					'  </url>',
				]) . PHP_EOL);

				if (++$count == $this->settings['entries_per_sitemap']) {
					$bump_sitemap();
					$count = 0;
				}
			};

			$draw_image_node = function($image_url, $caption) use (&$fh, &$count, &$bump_sitemap) {

				fwrite($fh, implode(PHP_EOL, [
					'    <image:image>',
					'      <image:loc>'. $image_url .'</image:loc>',
					'      <image:caption>'. f::escape_html($caption) .'</image:caption>',
					'    </image:image>',
				]) . PHP_EOL);

				if (++$count == $this->settings['entries_per_sitemap']) {
					$bump_sitemap();
					$count = 0;
				}
			};

			// Generate sitemaps
			echo 'Generating Sitemaps...' . PHP_EOL;

			$count = 0;

			$init_sitemap();

			$draw_url_node('', [], 'daily', '1.0', date('Y-m-d'));

			## Categories

			$category_iterator = function($parent_id=0, &$visited=[]) use (&$category_iterator, &$draw_url_node) {
				f::catalog_categories_query($parent_id)->each(function($category) use (&$category_iterator, &$draw_url_node, &$visited) {

					if (in_array($category['id'], $visited)) {
						// Failsafe: skip already visited category to prevent recursion
						trigger_error('Recursive category reference detected ('. implode(" -> ", $visited) .')', E_USER_WARNING);
						return;
					}

					$visited[] = $category['id'];
					$draw_url_node('f:category', ['category_id' => $category['id']], 'weekly', '1.0', $category['updated_at']);
					$category_iterator($category['id'], $visited);
				});
			};

			$visited_categories = [];
			$category_iterator(0, $visited_categories);

			## Products

			database::query(
				"select id, updated_at from ". DB_PREFIX ."products
				where status
				order by id;"
			)->each(function($product) use (&$draw_url_node) {
				$draw_url_node('f:product', ['product_id' => $product['id']], 'weekly', '1.0', $product['updated_at']);
			});

			## Product Images

			database::query(
				"select id, default_image, updated_at from ". DB_PREFIX ."products
				where status
				order by id;"
			)->each(function($product) use (&$fh, &$count, &$bump_sitemap) {

				fwrite($fh, implode(PHP_EOL, array_filter([
					'  <url>',
					'    <loc>'. document::ilink('f:product', ['product_id' => $product['id']]) .'</loc>',

					implode(PHP_EOL, f::array_each(language::$languages, function($language) use ($product) {
						if ($language['url_type'] == 'none') return;
						return '    <xhtml:link rel="alternate" hreflang="'. $language['code'] .'" href="'. document::href_ilink('f:product', ['product_id' => $product['id']], false, [], $language['code']) .'" />';
					})),

					implode(PHP_EOL, database::query(
						"select filename from ". DB_PREFIX ."products_images
						where product_id = ". (int)$product['id'] ."
						order by priority;"
					)->fetch_all(function($image){
						return implode(PHP_EOL, [
							'    <image:image>',
							'      <image:loc>'. document::rlink('storage://images/' . $image['filename']) .'</image:loc>',
							'    </image:image>',
						]);
					})),

					'    <lastmod>'. date('Y-m-d', strtotime($product['updated_at'])) .'</lastmod>',
					'    <changefreq>weekly</changefreq>',
					'    <priority>0.8</priority>',
					'  </url>',
				])) . PHP_EOL);

				if (++$count == $this->settings['entries_per_sitemap']) {
					$bump_sitemap();
					$count = 0;
				}
			});

			$close_sitemap();

			// Generate indexes
			$indexes = preg_replace('#^.*/sitemap-(\d+).xml$#', '$1', $last_sitemap_path);

			$fhs = fopen('storage://cache/sitemap/index.xml', 'w');

			fwrite($fhs, implode(PHP_EOL, [
				'<?xml version="1.0" encoding="UTF-8"?>',
				'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
			]) . PHP_EOL);

			for ($i=1; $i <= $indexes; $i++) {
				fwrite($fhs, implode(PHP_EOL, [
					'  <sitemap>',
					'		<loc>'. document::ilink('f:sitemap/sitemap-'.$i.'.xml') .'</loc>',
					'	</sitemap>',
				]) . PHP_EOL);
			}

			fwrite($fhs, '</sitemapindex>' . PHP_EOL);
			fclose($fhs);

			echo 'Siteindex with '. $indexes . ' sitemaps generated' . PHP_EOL;
		}

		public function settings(): array {

			return [
				[
					'key' => 'status',
					'default_value' => '1',
					'title' => t(__CLASS__.':title_status', 'Status'),
					'description' => t(__CLASS__.':description_status', 'Enables or disables the module.'),
					'function' => 'toggle("e/d")',
				],
				[
					'key' => 'frequency',
					'default_value' => 'Daily',
					'title' => t(__CLASS__.':title_frequency', 'Frequency'),
					'description' => t(__CLASS__.':description_frequency', 'How often the job should be processed.'),
					'function' => 'radio("Daily","Weekly","Monthly")',
				],
				[
					'key' => 'entries_per_sitemap',
					'default_value' => '1000',
					'title' => t(__CLASS__.':title_entries_per_sitemap', 'Entries Per Sitemap'),
					'description' => t(__CLASS__.':description_entries_per_sitemap', 'The number of entries per sitemap file.'),
					'function' => 'number()',
				],
				[
					'key' => 'priority',
					'default_value' => '0',
					'title' => t(__CLASS__.':title_priority', 'Priority'),
					'description' => t(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
					'function' => 'number()',
				],
			];
		}
	}
