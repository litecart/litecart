<?php

	// -----------------------------------------------------------------------------
	// Model Context Protocol (MCP) server over HTTP JSON-RPC 2.0
	// Provides CRUD tools that mirror LiteCart collections/entities.
	// -----------------------------------------------------------------------------

	// Custom exception for JSON-RPC error output
	class McpException extends Exception {
		public $rpc_id;
		public $rpc_code;
		public function __construct($message, $code = 400, $rpc_code = -32000, $rpc_id = null) {
			parent::__construct($message, $code);
			$this->rpc_id = $rpc_id;
			$this->rpc_code = $rpc_code;
		}
	}

	try {

		// Only allow POST requests
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			throw new McpException('MCP server expects HTTP POST JSON-RPC requests', 405, -32600);
		}

		// Parse JSON-RPC request (max 64KB)
		$raw = file_get_contents('php://input', false, null, 0, 65536);
		$rpc = json_decode($raw, true);

		if (!is_array($rpc) || empty($rpc['jsonrpc']) || $rpc['jsonrpc'] !== '2.0' || empty($rpc['method'])) {
			throw new McpException('Invalid Request', 400, -32600);
		}

		$rpc_id = $rpc['id'] ?? null;
		$params = isset($rpc['params']) && is_array($rpc['params']) ? $rpc['params'] : [];

		// HTTP Basic Authentication (mandatory)
		if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
			throw new McpException('Authentication required', 401, -32000, $rpc_id);
		}


			$administrator = database::query(
				"select * from ". DB_TABLE_PREFIX ."administrators
				where status
				and lower(username) = lower('". database::input($_SERVER['PHP_AUTH_USER']) ."')
				and (valid_from is null or valid_from < '". database::input(date('Y-m-d H:i:s')) ."')
				and (valid_to is null or valid_to > '". database::input(date('Y-m-d H:i:s')) ."')
				and (blocked_until is null or blocked_until < '". database::input(date('Y-m-d H:i:s')) ."')
				limit 1;",
			)->fetch();

			if (!$administrator) {
				throw new McpException(language::translate('error_administrator_not_found', 'The administrator is either suspended or could not be found in our database'), 401);
			}

			// Password check and login attempts
			if (!password_verify($_SERVER['PHP_AUTH_PW'], $administrator['password_hash'])) {
				if (++$administrator['login_attempts'] < 3) {

					database::query(
							"update ". DB_TABLE_PREFIX ."administrators
							set login_attempts = login_attempts + 1
							where id = ". (int)$administrator['id'] ."
							limit 1;"
					);

					throw new McpException(strtr(language::translate('error_d_login_attempts_left', 'You have %d login attempts left until your account is temporary blocked'), ['%d' => 3 - $administrator['login_attempts']]), 403);

				} else {

					database::query(
						"update ". DB_TABLE_PREFIX ."administrators
						set login_attempts = 0,
						blocked_until = '". date('Y-m-d H:i:00', strtotime('+15 minutes')) ."'
						where id = ". (int)$administrator['id'] ."
						limit 1;",
					);

					throw new McpException(strtr(language::translate('error_account_has_been_blocked', 'The account has been temporary blocked %d minutes'), ['%d' => 15]), 403);
				}

			}

			// Reset login attempts after successful login
			if (!empty($administrator['login_attempts'])) {
				database::query(
					"update ". DB_TABLE_PREFIX ."administrators
					set login_attempts = 0
					where id = ". (int)$administrator['id'] ."
					limit 1;",
				);
			}

		// MCP method dispatch
		switch ($rpc['method']) {

			// MCP built-in: initialize
			case 'initialize':

				$result = [
					'protocolVersion' => '2024-11-05', // Protocol 2024-11-05 for custom authentication and tool schema format
					'serverInfo' => [
						'name' => PLATFORM_NAME .' MCP Server',
						'version' => PLATFORM_VERSION,
					],
					'capabilities' => [
						'tools' => true,
					],
				];

				break;

			case 'notifications/initialized':

				$result = null;
				break;

			// MCP: list available tools
			case 'tools/list':

				$tool_schemas = [];

				foreach (functions::file_search('app://backend/mcp/mcp_*.inc.php') as $mcp_file) {

					// Include without polluting global scope
					$tool_schema = (function() use ($mcp_file) {
						return include $mcp_file;
					})();

					if (!empty($tool_schema['name']) && is_array($tool_schema['inputSchema'])) {
						$tool_schemas[] = [
							'name' => $tool_schema['name'],
							'description' => $tool_schema['description'] ?? '',
							'inputSchema' => $tool_schema['inputSchema'] ?? [
								'type' => 'object',
								'properties' => new stdClass(),
							],
						];
					}
				}

				$result = [
					'tools' => $tool_schemas,
				];

				break;

			// MCP: call a tool
			case 'tools/call':

				if (empty($params['name'])) {
					throw new McpException('Missing tool name', 400, -32602);
				}

				// Tool dispatch
				foreach (functions::file_search('app://backend/mcp/mcp_*.inc.php') as $mcp_file) {

					// Include without polluting global scope
					$tool_schema = (function() use ($mcp_file) {
						return include $mcp_file;
					})();

					if (is_array($tool_schema) && !empty($tool_schema['name']) && $tool_schema['name'] === $params['name']) {
						$tool_function = 'mcp_' . str_replace(['/', '-'], '_', $tool_schema['name']);

						if (function_exists($tool_function)) {

							// Support both 'arguments' (MCP standard) and 'input' (legacy)
							$tool_args = $params['arguments'] ?? $params['input'] ?? [];

							// Check input against required parameters
							if (!empty($tool_schema['inputSchema']['required']) && is_array($tool_schema['inputSchema']['required'])) {
								foreach ($tool_schema['inputSchema']['required'] as $field) {
									if (!isset($tool_args[$field]) || $tool_args[$field] === '') {
										throw new McpException("Missing required parameter: $field", 400, -32602);
									}
								}
							}

							$tool_result = $tool_function($tool_args);
							break;
						}
					}
				}

				if (!isset($tool_result)) {
					throw new McpException('Tool not found', 404, -32601);
				}

				$result = [
					'content' => [[
						'type' => 'text',
						'text' => json_encode($tool_result, JSON_UNESCAPED_SLASHES),
					]],
					'structuredContent' => $tool_result,
					'isError' => false,
				];

				break;

			// Unknown method
			default:
				throw new McpException('Method not found', 404, -32601);
		}

		$output = json_encode([
			'jsonrpc' => '2.0',
			'id' => $rpc_id,
			'result' => $result,
		], JSON_UNESCAPED_SLASHES);

		if ($output === false) {
			throw new McpException('Encoding error', 500, -32603);
		}

	// Error handling: output JSON-RPC error response
	} catch (McpException $e) {

		http_response_code($e->getCode() ?: 500);

		if ($e->getCode() == 401) {
			header('WWW-Authenticate: Basic realm="' . PLATFORM_NAME . ' MCP Server"');
		}

		$output = json_encode([
			'jsonrpc' => '2.0',
			'id' => $e->rpc_id,
			'error' => [
				'code' => $e->rpc_code,
				'message' => $e->getMessage(),
			],
		], JSON_UNESCAPED_SLASHES);

		if ($output === false) {
			$output = json_encode([
				'jsonrpc' => '2.0',
				'error' => [
					'code' => -32603,
					'message' => 'Encoding error',
				],
				'id' => $e->rpc_id,
			], JSON_UNESCAPED_SLASHES);
		}

	} catch (Exception $e) {

		http_response_code($e->getCode() ?: 500);

		$output = json_encode([
			'jsonrpc' => '2.0',
			'error' => [
				'code' => -32000,
				'message' => $e->getMessage(),
			],
			'id' => isset($rpc_id) ? $rpc_id : null,
		], JSON_UNESCAPED_SLASHES);

		if ($output === false) {
			$output = json_encode([
				'jsonrpc' => '2.0',
				'id' => isset($rpc_id) ? $rpc_id : null,
				'error' => [
					'code' => -32603,
					'message' => 'Encoding error',
				],
			], JSON_UNESCAPED_SLASHES);
		}
	}

	ob_clean();
	header('Date: '. date('r'));
	header('Content-Type: application/json; charset=UTF-8');
	header('Content-Length: '. strlen($output));
	echo $output;
	exit;
