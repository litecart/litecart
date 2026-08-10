<?php

	try {

		ob_clean();

		if (!is_dir('storage://cache/sitemap/')) {
			throw new Exception('Sitemap resources not found', 404);
		}

		switch (true) {

			case preg_match('#sitemap/index\.xml(\.gz)?$#', route::$request):
			case preg_match('#site(map|index)\.xml(\.gz)?$#', route::$request):

					header('Content-Type: application/xml; charset='. mb_http_output());
					include 'storage://cache/sitemap/index.xml';
					exit;

				case preg_match('#sitemap-(\d+)?\.xml#', route::$request, $matches):

					if (!file_exists('storage://cache/sitemap/sitemap-'. ($matches[1] ?: '1') .'.xml')) {
						throw new Exception('Sitemap not found', 404);
					}

					header('Content-Type: application/xml; charset='. mb_http_output());
					include 'storage://cache/sitemap/sitemap-'. ($matches[1] ?: '1') .'.xml';
					exit;

			default:
				throw new Exception('No sitemap matching request', 404);
		}

	} catch (Exception $e) {
		http_response_code($e->getCode() ?: 500);
		notices::add('errors', $e->getMessage());
		include 'app://frontend/pages/error_document.inc.php';
	}
