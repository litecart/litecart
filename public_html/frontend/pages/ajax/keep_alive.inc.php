<?php

	class_exists('session', true);

	header('X-Robots-Tag: noindex');
	header('Content-Type: text/plain; charset='. mb_http_output());

	$replies = [
		'Oh it\'s you again!',
		'I see you are still here.',
		'Do you come here a lot?',
		'I see you are still around.',
		'It\'s great to have you here.',
	];

	echo $replies[array_rand($replies)];
	exit;
