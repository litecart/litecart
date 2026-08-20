<?php

	return [
		'name' => 'ping',
		'description' => 'Availability checking.',
		'tools' => [
			[
				'name' => 'ping',
				'description' => 'Returns a ping response',
				'inputSchema' => [
					'type' => 'object',
					'properties' => new stdClass(),
					'required' => [],
				],
				'function' => function($params) {
					return [];
				},
			],
		],
	];
