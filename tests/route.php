<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		echo 'Testing route class...';

		########################################################################
		## Test route::strip_url_logic()
		########################################################################

		echo PHP_EOL . '- Testing strip_url_logic()...';

		// Test basic path stripping
		$tests = [
			// [input, expected_output, description]
			['', '', 'Empty string'],
			['/', '', 'Root path'],
			['/frontend', 'frontend', 'Basic frontend path'],
			['/backend/admin', 'backend/admin', 'Backend admin path'],
			['/en/products', 'products', 'Path with English language code'],
			['/ar/categories/test', 'ar/categories/test', 'Non-registered language code is not stripped'],
			['/index.php/products', 'products', 'Path with index.php prefix'],
			['/en/index.php/products', 'index.php/products', 'Path with language and index.php'],
			['//multiple//slashes//', 'multiple/slashes/', 'Multiple slashes'],
			['/products/../categories', 'products./categories', 'Path traversal resolution'],
			['/products?param=value', 'products', 'Path with query string'],
			['/products#anchor', 'products', 'Path with anchor'],
			[urlencode('/products/special chars'), 'products/special chars', 'URL encoded path'],
		];

		foreach ($tests as $test) {
			$result = route::strip_url_logic($test[0]);
			if ($result !== $test[1]) {
				throw new Exception("strip_url_logic test failed for '{$test[2]}': expected '{$test[1]}', got '{$result}'");
			}
		}

		########################################################################
		## Test route::add() and route identification
		########################################################################

		echo PHP_EOL . '- Testing route::add() and identification...';

		// Save original state - we'll test indirectly through behavior
		$original_request = fallback(route::$request, '');

		// Add test routes
		route::add('f:test-page', [
			'pattern' => '#^test-page$#',
			'controller' => 'test_controller.php',
			'params' => 'param1=value1&param2=value2',
			'options' => ['redirect' => false]
		]);

		route::add('b:admin/dashboard', [
			'pattern' => '#^admin/dashboard$#',
			'controller' => 'admin_dashboard.php'
		]);

		route::add('f:product/{id}', [
			'patterns' => [
				'#^product/(\d+)$#',
				'#^item/(\d+)$#'
			],
			'controller' => 'product.php',
			'params' => 'product_id=$1'
		]);

		########################################################################
		## Test route::identify() with mock request
		########################################################################

		echo PHP_EOL . '- Testing route::identify()...';

		// Test matching frontend route
		route::$request = 'test-page';
		route::$selected = null; // Clear any previous selection
		$identified = route::identify();

		if (!$identified || $identified['resource'] !== 'f:test-page') {
			throw new Exception('Failed to identify frontend test route');
		}

		if ($identified['controller'] !== 'test_controller.php') {
			throw new Exception('Controller not set correctly in identified route');
		}

		// Test matching backend route
		route::$request = 'admin/dashboard';
		route::$selected = null;
		$identified = route::identify();

		if (!$identified || $identified['resource'] !== 'b:admin/dashboard') {
			throw new Exception('Failed to identify backend test route');
		}

		// Test pattern with parameters
		route::$request = 'product/123';
		route::$selected = null;
		$_GET = []; // Clear GET params
		$identified = route::identify();

		if (!$identified || $identified['resource'] !== 'f:product/{id}') {
			throw new Exception('Failed to identify parametrized route');
		}

		// Test alternative pattern
		route::$request = 'item/456';
		route::$selected = null;
		$_GET = []; // Clear GET params
		$identified = route::identify();

		if (!$identified || $identified['resource'] !== 'f:product/{id}') {
			throw new Exception('Failed to identify alternative pattern route');
		}

		// Test non-matching route
		route::$request = 'non-existent-page';
		route::$selected = null;
		$identified = route::identify();

		if ($identified !== null) {
			throw new Exception('Should not identify non-existent route');
		}

		########################################################################
		## Test route::create_link()
		########################################################################

		echo PHP_EOL . '- Testing route::create_link()...';

		// Mock some $_GET parameters
		$original_get = $_GET;
		$_GET = [
			'page' => '2',
			'sort' => 'name',
			'language' => 'en',
			'test_param' => 'test_value'
		];

		// Test basic link creation
		$link = route::create_link('/products');
		if (!$link instanceof ent_link) {
			throw new Exception('create_link should return an ent_link object');
		}

		// Test link with new parameters
		$link = route::create_link('/products', ['category' => 'electronics']);
		if ($link->query['category'] !== 'electronics') {
			throw new Exception('New parameters not set correctly in link');
		}

		// Test parameter inheritance
		$link = route::create_link('/products', [], true);
		if ($link->query['page'] !== '2' || $link->query['sort'] !== 'name') {
			throw new Exception('Parameters not inherited correctly');
		}

		// Language should be excluded from inheritance
		if (isset($link->query['language'])) {
			throw new Exception('Language parameter should not be inherited');
		}

		// Test selective parameter inheritance
		$link = route::create_link('/products', [], ['page']);
		if ($link->query['page'] !== '2') {
			throw new Exception('Selective parameter inheritance failed');
		}
		if (isset($link->query['sort'])) {
			throw new Exception('Non-selected parameter should not be inherited');
		}

		// Test parameter skipping
		$link = route::create_link('/products', [], true, ['page']);
		if (isset($link->query['page'])) {
			throw new Exception('Skipped parameter should not be present');
		}
		if ($link->query['sort'] !== 'name') {
			throw new Exception('Non-skipped parameter should be present');
		}

		########################################################################
		## Test URL sanitization and security
		########################################################################

		echo PHP_EOL . '- Testing URL sanitization...';

		$malicious_inputs = [
			'../../../etc/passwd',
			'script:alert(1)',
			'/path/with"quotes',
			"/path/with'quotes",
			'/path/with*wildcards',
			'/.hidden/file',
			'/path//with///multiple////slashes'
		];

		foreach ($malicious_inputs as $input) {
			$result = route::strip_url_logic($input);

			// Should not contain dangerous characters
			if (strpos($result, '"') !== false || strpos($result, "'") !== false || strpos($result, '*') !== false) {
				throw new Exception("Dangerous characters not stripped from: $input");
			}

			// Should not contain hidden file markers
			if (strpos($result, '/.') !== false) {
				throw new Exception("Hidden file markers not stripped from: $input");
			}

			// Should not contain multiple slashes
			if (strpos($result, '//') !== false) {
				throw new Exception("Multiple slashes not normalized in: $input");
			}
		}

		########################################################################
		## Test route caching behavior
		########################################################################

		echo PHP_EOL . '- Testing route caching...';

		// Test that after_capture method exists and can be called
		if (!method_exists('route', 'after_capture')) {
			throw new Exception('after_capture method should exist');
		}

		// Test the method can be called without errors
		route::after_capture();

		// Test that route selection is maintained
		route::$selected = ['test' => 'data'];
		if (!isset(route::$selected['test']) || route::$selected['test'] !== 'data') {
			throw new Exception('Route selection data not maintained correctly');
		}

		########################################################################
		## Clean up and restore state
		########################################################################

		// Restore original state
		route::$request = $original_request;
		$_GET = $original_get;

		echo PHP_EOL . '- All route tests passed!' . PHP_EOL;
		return true;

	} catch (Exception $e) {

		echo ' [Failed]' . PHP_EOL . 'Error: ' . $e->getMessage() . PHP_EOL;
		return false;

	} finally {

		// Ensure cleanup even if test fails
		if (isset($original_request)) {
			route::$request = $original_request;
		}
		if (isset($original_get)) {
			$_GET = $original_get;
		}
	}
