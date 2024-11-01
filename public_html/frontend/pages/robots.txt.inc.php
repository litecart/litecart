<?php

	$output = [];

	// General
	$output['general'] = [
		'User-agent: *',
		'Allow: /',
		'Disallow: /storage/cache/*',
		//'Crawl-Delay: 10',
	];

	// Sitemap
	$output['sitemap'] = 'Sitemap: '. document::ilink('sitemap.xml');

	// Output
	ob_clean();
	header('Content-Type: text/plain;charset='. mb_http_output());
	
	foreach ($output as $block) {
		if (is_array($block)) {
			echo implode(PHP_EOL, $block);
		} else {
			echo $block;
		}
		echo PHP_EOL . PHP_EOL;
	}

	exit; // As we don't need app_footer to process this with a template
