<?php

	// Default dialect (2020-12):
	return $schema = [
		'name' => 'ping',
		'description' => 'Returns a ping response',
		'inputSchema' => [
			'type' => 'object',
			'params' => [],
			'required' => [],
		],
	];

	function mcp_ping($params) {
		return [];
	}
