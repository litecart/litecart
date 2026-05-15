<?php

	return [
		'name' => 'system',
		'description' => 'System tools.',
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
