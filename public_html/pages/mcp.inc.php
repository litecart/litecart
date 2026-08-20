<?php

	/*
	  Model Context Protocol (MCP) server over HTTP JSON-RPC 2.0
		- HTTP: single POST JSON-RPC 2.0 request → response
	*/

	// Custom exception for JSON-RPC error output
	class McpException extends Exception {
		public $rpc_id;
		public $rpc_code;
		public function __construct($message, $code=400, $rpc_code=-32000, $rpc_id=null) {
			parent::__construct($message, $code);
			$this->rpc_id = $rpc_id;
			$this->rpc_code = $rpc_code;
		}
	}

	try {

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			throw new McpException('MCP server expects HTTP POST JSON-RPC requests', 405, -32600);
		}

		$raw = file_get_contents('php://input');

		$rpc = json_decode($raw, true);

		if (!is_array($rpc) || empty($rpc['jsonrpc']) || $rpc['jsonrpc'] !== '2.0' || empty($rpc['method'])) {
			throw new McpException('Invalid Request', 400, -32600);
		}

		$rpc_id = $rpc['id'] ?? null;
		$params = isset($rpc['params']) && is_array($rpc['params']) ? $rpc['params'] : [];

		if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {

			// Try LiteCart user authentication
			$user = database::query(
				"select * from ". DB_TABLE_PREFIX ."users
				where status
				and lower(username) = lower('". database::input($_SERVER['PHP_AUTH_USER']) ."')
				and (date_valid_from is null or date_valid_from < '". database::input(date('Y-m-d H:i:s')) ."')
				and (date_valid_to is null or date_valid_to > '". database::input(date('Y-m-d H:i:s')) ."')
				limit 1;"
			)->fetch();

			if (!$user) {
				throw new McpException(language::translate('error_user_not_found', 'The user is either suspended or could not be found in our database'), 401);
			}

			if ($user['date_valid_from'] && $user['date_valid_from'] > date('Y-m-d H:i:s')) {
				throw new McpException(strtr(language::translate('error_account_is_blocked_until', 'The account is blocked until %s'), ['%s' => date('Y-m-d H:i:s', strtotime($user['date_valid_from']))]), 403);
			}

			if (!password_verify($_SERVER['PHP_AUTH_PW'], $user['password_hash'])) {
				if (++$user['login_attempts'] < 3) {

					database::query(
						"update ". DB_TABLE_PREFIX ."users
						set login_attempts = login_attempts + 1
						where id = ". (int)$user['id'] ."
						limit 1;"
					);

					throw new McpException(strtr(language::translate('error_d_login_attempts_left', 'You have %d login attempts left until your account is temporary blocked'), ['%d' => 3 - $user['login_attempts']]), 403);

				} else {
					database::query(
						"update ". DB_TABLE_PREFIX ."users
						set login_attempts = 0,
						date_valid_from = '". date('Y-m-d H:i:00', strtotime('+15 minutes')) ."'
						where id = ". (int)$user['id'] ."
						limit 1;"
					);

					throw new McpException(strtr(language::translate('error_account_has_been_blocked', 'The account has been temporary blocked %d minutes'), ['%d' => 15]), 403);
				}

				throw new McpException(language::translate('error_wrong_username_password_combination', 'Wrong combination of username and password or the account does not exist.'), 403);
			}

			if ($user['login_attempts'] > 0) {
				database::query(
					"update ". DB_TABLE_PREFIX ."users
					set login_attempts = 0
					where id = ". (int)$user['id'] ."
					limit 1;"
				);
			}

		} else {
			throw new McpException('You need to authenticate to use this API', 401, -32000);
		}

		switch ($rpc['method']) {
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

			case 'tools/list':

				$tool_schemas = [];

				foreach (functions::file_search(vmod::check(FS_DIR_APP . 'includes/mcp/mcp_*.inc.php')) as $mcp_file) {

					// Include without polluting global scope
					$toolset = (function() use ($mcp_file) {
						return include $mcp_file;
					})();

					if (!is_array($toolset) || empty($toolset['tools'])) continue;

					foreach ($toolset['tools'] as $tool) {

						if (empty($tool['name']) || !is_array($tool['inputSchema'])) continue;

						// Skip tools the administrator isn't permitted to use
						if (!empty($allowed_tools) && !in_array($tool['name'], $allowed_tools)) continue;

						$tool_schemas[] = [
							'name' => $tool['name'],
							'description' => $tool['description'] ?? '',
							'inputSchema' => $tool['inputSchema'] ?? [
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

			case 'tools/call':

				if (empty($params['name'])) {
					throw new McpException('Missing tool name', 400, -32602);
				}

				foreach (functions::file_search(vmod::check(FS_DIR_APP . 'includes/mcp/*.inc.php')) as $mcp_file) {
				// Include without polluting global scope
				$toolset = (function() use ($mcp_file) {
					return include $mcp_file;
				})();

				if (!is_array($toolset) || empty($toolset['tools'])) continue;

				foreach ($toolset['tools'] as $tool) {

					if (empty($tool['name']) || $tool['name'] !== $params['name']) continue;

					// Per-toolset permission check
					if (!empty($allowed_tools) && !in_array($tool['name'], $allowed_tools)) {
						throw new McpException('Tool not permitted for this administrator', 403, -32001, $rpc_id);
					}

					// Support both 'arguments' (MCP standard) and 'input' (legacy)
					$tool_args = $params['arguments'] ?? $params['input'] ?? [];

					// Check input against required parameters
					if (!empty($tool['inputSchema']['required']) && is_array($tool['inputSchema']['required'])) {
						foreach ($tool['inputSchema']['required'] as $field) {
							if (!isset($tool_args[$field]) || $tool_args[$field] === '') {
								throw new McpException("Missing required parameter: $field", 400, -32602);
							}
						}
					}

					$tool_result = ($tool['function'])($tool_args);
					break 2;
				}
			}

			if (!isset($tool_result)) {
				throw new McpException('Tool not found', 404, -32601);
			}

			$result = [
				'content' => [
					[
						'type' => 'text',
						'text' => json_encode($tool_result, JSON_UNESCAPED_SLASHES),
					]
				],
				'structuredContent' => $tool_result,
				'isError' => false,
			];

			break;

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

	} catch (McpException $e) {

		http_response_code(($e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 200);

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
				'id' => $e->rpc_id,
				'error' => [
					'code' => -32603,
					'message' => 'Encoding error',
				],
			], JSON_UNESCAPED_SLASHES);
		}

	} catch (Exception $e) {

		http_response_code(($e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 200);

		$output = json_encode([
			'jsonrpc' => '2.0',
			'id' => isset($rpc_id) ? $rpc_id : null,
			'error' => [
				'code' => -32000,
				'message' => $e->getMessage(),
			],
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
