<?php

	/*
		Model Context Protocol (MCP) Server
		- HTTP: single POST JSON-RPC 2.0 request → response
		- CLI stdio: persistent process, reads one JSON-RPC 2.0 message per line until stdin closes
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

	$indent = ''; // No indentation by default for compact output; can be set to e.g. '  ' for pretty-printing

	// Only allow POST requests (HTTP only — CLI uses stdio)
	if (!is_cli() && $_SERVER['REQUEST_METHOD'] !== 'POST') {

		$output = f::format_json([
			'jsonrpc' => '2.0',
			'id' => null,
			'error' => [
				'code' => -32600,
				'message' => 'MCP server expects HTTP POST JSON-RPC requests'
			],
		], $indent);

		ob_clean();
		http_response_code(405);
		header('Date: '. date('r'));
		header('Content-Type: application/json; charset=UTF-8');
		header('Content-Length: '. strlen($output));
		echo $output;
		exit;
	}

	// Authenticate once for the lifetime of the process
	try {

		if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
			throw new McpException('Authentication required', 401, -32000);
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
			throw new McpException(t('error_administrator_not_found', 'The administrator is either suspended or could not be found in our database'), 401);
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

				throw new McpException(strtr(t('error_d_login_attempts_left', 'You have %d login attempts left until your account is temporary blocked'), [
					'%d' => 3 - $administrator['login_attempts']
				]), 403);

			} else {

				database::query(
					"update ". DB_TABLE_PREFIX ."administrators
					set login_attempts = 0,
					blocked_until = '". date('Y-m-d H:i:00', strtotime('+15 minutes')) ."'
					where id = ". (int)$administrator['id'] ."
					limit 1;",
				);

				throw new McpException(strtr(t('error_account_has_been_blocked', 'The account has been temporary blocked %d minutes'), ['%d' => 15]), 403);
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

		$permissions   = !empty($administrator['permissions']) ? json_decode($administrator['permissions'], true) : [];
		$allowed_tools = array_merge(...($permissions['mcp'] ?? []));

	} catch (McpException $e) {

		$output = f::format_json([
			'jsonrpc' => '2.0',
			'id'      => $e->rpc_id,
			'error'   => ['code' => $e->rpc_code, 'message' => $e->getMessage()],
		], $indent);

		if (is_cli()) {
			fwrite(STDOUT, $output . "\n");
		} else {
			http_response_code($e->getCode() ?: 500);
			if ($e->getCode() == 401) header('WWW-Authenticate: Basic realm="'. PLATFORM_NAME .' MCP Server"');
			ob_clean();
			header('Date: '. date('r'));
			header('Content-Type: application/json; charset=UTF-8');
			header('Content-Length: '. strlen($output));
			echo $output;
		}

		exit;
	}

	// Dispatch one JSON-RPC request. Returns the JSON response string, or null for notifications (no response).
	$dispatch = function(string $raw) use ($indent, $allowed_tools): ?string {

		$rpc_id = null;

		try {

			$rpc = json_decode($raw, true);

			if (!is_array($rpc) || empty($rpc['jsonrpc']) || $rpc['jsonrpc'] !== '2.0' || empty($rpc['method'])) {
				throw new McpException('Invalid Request', 400, -32600);
			}

			$rpc_id = $rpc['id'] ?? null;
			$params = isset($rpc['params']) && is_array($rpc['params']) ? $rpc['params'] : [];

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

				// MCP notification: no response required
				case 'notifications/initialized':
					return null;

			// MCP: list available tools
			case 'tools/list':

				$tool_schemas = [];

				foreach (f::file_search('app://backend/mcp/mcp_*.inc.php') as $mcp_file) {

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

			// MCP: call a tool
			case 'tools/call':

				if (empty($params['name'])) {
					throw new McpException('Missing tool name', 400, -32602);
				}

				// Tool dispatch
				foreach (f::file_search('app://backend/mcp/mcp_*.inc.php') as $mcp_file) {

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
							'text' => f::format_json($tool_result),
						]
					],
					'structuredContent' => $tool_result,
					'isError' => false,
				];

				break;

				// Unknown method
				default:
					throw new McpException('Method not found', 404, -32601);
			}

			$output = f::format_json([
				'jsonrpc' => '2.0',
				'id' => $rpc_id,
				'result' => $result
			], $indent);

			if ($output === false) {
				throw new McpException('Encoding error', 500, -32603);
			}

			return $output;

		} catch (McpException $e) {

			return f::format_json([
				'jsonrpc' => '2.0',
				'id'      => $e->rpc_id ?? $rpc_id,
				'error'   => ['code' => $e->rpc_code, 'message' => $e->getMessage()],
			], $indent) ?: '{"jsonrpc":"2.0","id":null,"error":{"code":-32603,"message":"Encoding error"}}';

		} catch (Exception $e) {

			return f::format_json([
				'jsonrpc' => '2.0',
				'id'      => $rpc_id,
				'error'   => ['code' => -32000, 'message' => $e->getMessage()],
			], $indent) ?: '{"jsonrpc":"2.0","id":null,"error":{"code":-32603,"message":"Encoding error"}}';
		}
	};

	// CLI stdio: persistent loop — one JSON-RPC message per line until stdin closes
	if (is_cli()) {

		set_time_limit(0); // No time limit for CLI mode

		while (($line = fgets(STDIN)) !== false) {
			$line = trim($line);
			if ($line === '') continue;

			$output = $dispatch($line);

			if ($output !== null) {
				ob_clean(); // Discard any buffered output from tool execution
				fwrite(STDOUT, $output . "\n");
				fflush(STDOUT);
			}
		}

		ob_end_clean(); // Discard anything left in the buffer at shutdown
		exit;
	}

	// HTTP: single request → response
	$output = $dispatch(file_get_contents('php://input', false, null, 0, 65536));

	if ($output === null) {
		ob_clean();
		http_response_code(204);
		exit;
	}

	ob_clean();
	header('Date: '. date('r'));
	header('Content-Type: application/json; charset=UTF-8');
	header('Content-Length: '. strlen($output));
	echo $output;
	exit;
