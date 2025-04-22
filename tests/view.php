<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		########################################################################
		## If Condition
		########################################################################

		$view = new ent_view();

		$view->html = '{{foo}}';

		$view->snippets = [
			'foo' => 'Bar',
		];

		if ($view->render() !== 'Bar') {
			throw new Exception('Variable substitution failed');
		}
/*
		########################################################################
		## Else Condition
		########################################################################

		$view = new ent_view();

		$view->html = implode(PHP_EOL, [
			'{{if $var}}',
			'{{var}}',
			'{{else}}',
			'Nope',
			'{{/if}}',
		]);

		$view->snippets = [
			'foo' => 'Bar',
		];

		if ($view->render() !== 'Nope') {
			print_r($view->render());
			throw new Exception('Else condition failed');
		}

		########################################################################
		## Modifier: Money Formatting
		########################################################################

		$view->html = '{{amount|money}}';

		$view->snippets = [
			'amount' => 9.99,
		];

		if ($view->render() !== currency::format(9.99)) {
			throw new Exception('Money formatting failed');
		}
*/
		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {
		// No rollback needed in this file
	}
