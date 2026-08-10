<?php

	$view = new ent_view('app://frontend/templates/' . settings::get('template') . '/layouts/email.inc.php');

	$view->snippets = [
		'content' => 'Lorem ipsum dolor sit amet.',
		'language_code' => language::$selected['code'],
		'text_direction' => language::$languages[language::$selected['code']]['direction'] ?? 'ltr',
	];

	echo $view->render();

	exit;
