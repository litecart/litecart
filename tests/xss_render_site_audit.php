<?php

	include_once __DIR__.'/../public_html/shared/app_header.inc.php';

	try {

		########################################################################
		## Verifies that customer-supplied fields are escaped at render sites.
		## This is a static-source audit, not a runtime test — catches regressions
		## where a future PR re-introduces an unescaped echo of customer data.
		########################################################################

		$base = realpath(__DIR__.'/../public_html');

		// Map of file => list of regex patterns that MUST NOT appear in the file.
		// Each pattern represents an unescaped customer-data render that PROJ-26 closes.
		$forbidden = [
			// XSS-001: Formatted addresses must be escaped before nl2br
			'backend/apps/orders/order.inc.php' => [
				'#nl2br\(f::format_address\(#' => 'XSS-001: format_address output must pass through escape_html before nl2br',
			],
			'frontend/templates/default/pages/checkout/index.inc.php' => [
				'#nl2br\(f::format_address\(#' => 'XSS-001: format_address output must pass through escape_html before nl2br',
			],
			'frontend/templates/default/pages/printable_order_copy.inc.php' => [
				'#nl2br\(f::format_address\(#' => 'XSS-001: format_address output must pass through escape_html before nl2br',
			],
			'frontend/templates/default/pages/printable_packing_slip.inc.php' => [
				'#nl2br\(f::format_address\(#' => 'XSS-001: format_address output must pass through escape_html before nl2br',
			],

			// XSS-002: Customer-list columns must be escaped
			'backend/apps/customers/customers.inc.php' => [
				'#echo \$customer\[\'company\'\] \?:#'  => 'XSS-002: customer.company must be escaped at render',
				'#<td><\?php echo \$customer\[\'email\'\];#'         => 'XSS-002: customer.email must be escaped at render',
				'#<td><\?php echo \$customer\[\'company\'\];#'       => 'XSS-002: customer.company column must be escaped',
				'#<td><\?php echo \$customer\[\'last_hostname\'\];#' => 'XSS-002: customer.last_hostname (reverse-DNS, unauth vector) must be escaped',
				'#echo \$customer\[\'group_name\'\];#'    => 'XSS-002: customer.group_name must be escaped',
			],

			// XSS-004: Email-list subjects and recipient names must be escaped
			'backend/apps/email/sent.inc.php' => [
				'#echo \$email\[\'subject\'\];#' => 'XSS-004: email.subject (unauth via contact form) must be escaped',
			],
			'backend/apps/email/failed.inc.php' => [
				'#echo \$email\[\'subject\'\];#' => 'XSS-004: email.subject must be escaped',
			],
			'backend/apps/email/scheduled.inc.php' => [
				'#echo \$email\[\'subject\'\];#' => 'XSS-004: email.subject must be escaped',
			],
			'backend/apps/email/drafts.inc.php' => [
				'#echo \$email\[\'subject\'\];#' => 'XSS-004: email.subject must be escaped',
			],

			// XSS-006: Order contact / tax fields must be escaped
			'backend/apps/orders/order.inc.php' => [
				'#nl2br\(f::format_address\(#'                          => 'XSS-001: format_address output must pass through escape_html before nl2br',
				'#echo \$order->data\[\'customer\'\]\[\'tax_id\'\];#' => 'XSS-006: order.customer.tax_id must be escaped',
			],
			'frontend/templates/default/pages/checkout/index.inc.php' => [
				'#nl2br\(f::format_address\(#' => 'XSS-001: format_address output must pass through escape_html before nl2br',
				'#echo \$order\[\'customer\'\]\[\'email\'\] \?\?#'                       => 'XSS-006: order.customer.email must be escaped',
				'#echo \$order\[\'customer\'\]\[\'phone\'\] \?\?#'                       => 'XSS-006: order.customer.phone must be escaped',
				'#echo \$order\[\'customer\'\]\[\'shipping_address\'\]\[\'email\'\] \?\?#' => 'XSS-006: order.shipping_address.email must be escaped',
				'#echo \$order\[\'customer\'\]\[\'shipping_address\'\]\[\'phone\'\] \?\?#' => 'XSS-006: order.shipping_address.phone must be escaped',
			],

			// XSS-003: search.js result-builder must use safe DOM APIs (no string-concat with untrusted data)
			'backend/template/js/components/search.js' => [
				'#\'\<h3\>\'\s*\+\s*group\.name#'                          => 'XSS-003: group.name must not be string-concatenated into HTML — use .text()',
				'#data-group="\'\s*\+\s*group\.name#'                      => 'XSS-003: group.name must not be string-concatenated into attribute — use .attr()',
				'#\'\<div class="title"\>\'\s*\+\s*result\.title#'         => 'XSS-003: result.title must not be string-concatenated — use .text()',
				'#\'\<small\>\'\s*\+\s*result\.description#'               => 'XSS-003: result.description must not be string-concatenated — use .text()',
				'#href="\'\s*\+\s*result\.link#'                           => 'XSS-003: result.link must not be string-concatenated into attribute — use .attr()',
				'#ul\[data-group="\'\s*\+\s*group\.name#'                  => 'XSS-006: selector argument must not be string-concatenated — use stored DOM reference',
				'#\.attr\(\'href\',\s*result\.link\)#'                     => 'XSS-007 (defense-in-depth): result.link must pass through safe_link() URL allowlist before being used as href',
			],
		];

		$failures = [];

		foreach ($forbidden as $relative_path => $patterns) {

			$full_path = $base . '/' . $relative_path;

			if (!is_file($full_path)) {
				$failures[] = $relative_path . ': file not found';
				continue;
			}

			$source = file_get_contents($full_path);

			foreach ($patterns as $pattern => $description) {
				if (preg_match($pattern, $source)) {
					$failures[] = $relative_path . ': ' . $description;
				}
			}
		}

		if (!empty($failures)) {
			throw new Exception(
				'Found '. count($failures) . ' unescaped customer-data renders:' . PHP_EOL . '  - '
				. implode(PHP_EOL . '  - ', $failures)
			);
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {
		// No DB writes — no rollback needed
	}
