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

	// RFC 6570 (subset) URI template matcher for resource templates
	class McpUriTemplate {
		public static function match($template, $uri) {
			$uri_parts = explode('?', $uri, 2);
			$uri_path = $uri_parts[0];
			$uri_query = [];
			if (isset($uri_parts[1])) {
				parse_str($uri_parts[1], $uri_query);
			}

			$pattern = preg_quote($template, '#');
			$pattern = preg_replace('/\\\{([^{}]+)\\\}/', '(?P<$1>[^/]+)', $pattern);
			$pattern = '#^' . $pattern . '$#';

			if (preg_match($pattern, $uri_path, $matches)) {
				$params = [];
				foreach ($matches as $key => $value) {
					if (!is_int($key)) {
						$params[$key] = $value;
					}
				}
				return array_merge($params, $uri_query);
			}
			return null;
		}
	}

	try {

		$method = $_SERVER['REQUEST_METHOD'];

		// DELETE - terminate session (Streamable HTTP transport)
		if ($method === 'DELETE') {
			http_response_code(200);
			exit;
		}

		if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {

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

		// GET - SSE stream endpoint. We run stateless (no session, no pushed notifications),
		// so acknowledge the endpoint with proper SSE headers and close immediately.
		if ($method === 'GET') {
			header('Content-Type: text/event-stream; charset=UTF-8');
			header('Cache-Control: no-cache');
			echo ": connected\n\n";
			exit;
		}

		if ($method !== 'POST') {
			throw new McpException('MCP server expects GET, POST or DELETE requests', 405, -32600);
		}

		// Validate Accept header per MCP Streamable HTTP transport
		if (!empty($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] !== '*/*') {
			$accept = $_SERVER['HTTP_ACCEPT'];
			if (strpos($accept, 'application/json') === false && strpos($accept, 'text/event-stream') === false) {
				throw new McpException('Client must accept application/json or text/event-stream', 406, -32600);
			}
		}

		$raw = file_get_contents('php://input');

		$rpc = json_decode($raw, true);

		if (!is_array($rpc) || empty($rpc['jsonrpc']) || $rpc['jsonrpc'] !== '2.0' || empty($rpc['method'])) {
			throw new McpException('Invalid Request', 400, -32600);
		}

		$rpc_id = $rpc['id'] ?? null;
		$params = isset($rpc['params']) && is_array($rpc['params']) ? $rpc['params'] : [];

		switch ($rpc['method']) {
			case 'initialize':

				$result = [
					'protocolVersion' => '2024-11-05', // Protocol 2024-11-05 for custom authentication and tool schema format
					'serverInfo' => [
						'name' => PLATFORM_NAME .' MCP Server',
						'version' => PLATFORM_VERSION,
					],
					'capabilities' => [
						'tools' => new stdClass(),
						'resources' => new stdClass(),
					],
				];

				break;

			case 'notifications/initialized':

				$result = null;
				break;

			case 'resources/list':

				$resource_schemas = [];

				foreach (functions::file_search(vmod::check(FS_DIR_APP . 'includes/mcp/mcp_*.inc.php')) as $mcp_file) {

					// Include without polluting global scope
					$resources = (function() use ($mcp_file) {
						return include $mcp_file;
					})();

					if (empty($toolset['tools'])) continue;

					foreach ($toolset['tools'] as $tool) {

						if (empty($resource['name']) || !is_array($resource['inputSchema'])) continue;

						// Skip tools the administrator isn't permitted to use
						if (!empty($allowed_tools) && !in_array($resource['name'], $allowed_tools)) continue;

						$tool_schemas[] = [
							'name' => $resource['name'],
							'description' => $resource['description'] ?? '',
							'inputSchema' => $resource['inputSchema'] ?? [
								'type' => 'object',
								'properties' => new stdClass(),
							],
						];
					}
				}

				$result = [
					'tools' => $resources_schemas,
				];

				break;

			case 'resources/call':

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
				'structuredContent' => is_array($tool_result) ? (object)$tool_result : $tool_result,
				'isError' => false,
			];

			break;

			case 'resources/list':

				$resources = [];

				foreach (functions::file_search(vmod::check(FS_DIR_APP . 'includes/mcp/mcp_*.inc.php')) as $mcp_file) {

					$toolset = (function() use ($mcp_file) {
						return include $mcp_file;
					})();

					if (!is_array($toolset) || empty($toolset['resources']) || !is_array($toolset['resources'])) continue;

					foreach ($toolset['resources'] as $resource) {

						if (empty($resource['uri']) || empty($resource['name']) || !is_callable($resource['function'] ?? null)) continue;

						if (!empty($allowed_resources) && !in_array($resource['uri'], $allowed_resources)) continue;

						$resources[] = [
							'uri' => $resource['uri'],
							'name' => $resource['name'],
							'description' => $resource['description'] ?? '',
							'mimeType' => $resource['mimeType'] ?? 'text/plain',
						];
					}
				}

				$result = [
					'resources' => $resources,
				];

				break;

			case 'resources/templates/list':

				$resource_templates = [];

				foreach (functions::file_search(vmod::check(FS_DIR_APP . 'includes/mcp/mcp_*.inc.php')) as $mcp_file) {

					$toolset = (function() use ($mcp_file) {
						return include $mcp_file;
					})();

					if (!is_array($toolset) || empty($toolset['resourceTemplates']) || !is_array($toolset['resourceTemplates'])) continue;

					foreach ($toolset['resourceTemplates'] as $template) {

						if (empty($template['uriTemplate']) || empty($template['name']) || !is_callable($template['function'] ?? null)) continue;

						if (!empty($allowed_resources) && !in_array($template['uriTemplate'], $allowed_resources)) continue;

						$resource_templates[] = [
							'uriTemplate' => $template['uriTemplate'],
							'name' => $template['name'],
							'description' => $template['description'] ?? '',
							'mimeType' => $template['mimeType'] ?? 'text/plain',
						];
					}
				}

				$result = [
					'resourceTemplates' => $resource_templates,
				];

				break;

			case 'resources/read':

				if (empty($params['uri'])) {
					throw new McpException('Missing resource URI', 400, -32602);
				}

				$uri = $params['uri'];
				$read_result = null;
				$read_mime = 'text/plain';

				foreach (functions::file_search(vmod::check(FS_DIR_APP . 'includes/mcp/*.inc.php')) as $mcp_file) {

					$toolset = (function() use ($mcp_file) {
						return include $mcp_file;
					})();

					if (!is_array($toolset)) continue;

					// Static resources (exact URI match)
					if (!empty($toolset['resources']) && is_array($toolset['resources'])) {
						foreach ($toolset['resources'] as $resource) {

							if (empty($resource['uri']) || $resource['uri'] !== $uri) continue;

							if (!empty($allowed_resources) && !in_array($resource['uri'], $allowed_resources)) {
								throw new McpException('Resource not permitted for this administrator', 403, -32001, $rpc_id);
							}

							$read_result = ($resource['function'])($params);
							$read_mime = $resource['mimeType'] ?? 'text/plain';
							break 2;
						}
					}

					// Resource templates (URI pattern match)
					if (!empty($toolset['resourceTemplates']) && is_array($toolset['resourceTemplates'])) {
						foreach ($toolset['resourceTemplates'] as $template) {

							if (empty($template['uriTemplate'])) continue;

							$template_params = McpUriTemplate::match($template['uriTemplate'], $uri);
							if ($template_params === null) continue;

							if (!empty($allowed_resources) && !in_array($template['uriTemplate'], $allowed_resources)) {
								throw new McpException('Resource not permitted for this administrator', 403, -32001, $rpc_id);
							}

							$read_result = ($template['function'])($template_params);
							$read_mime = $template['mimeType'] ?? 'text/plain';
							break 2;
						}
					}
				}

				if ($read_result === null) {
					throw new McpException('Resource not found', 404, -32601);
				}

				// Normalize callback return value into contents[] entries
				if (is_string($read_result)) {
					$contents = [[
						'uri' => $uri,
						'mimeType' => $read_mime,
						'text' => $read_result,
					]];
				} elseif (isset($read_result['contents']) && is_array($read_result['contents'])) {
					$contents = $read_result['contents'];
				} elseif (isset($read_result['text']) || isset($read_result['blob'])) {
					$contents = [[
						'uri' => $uri,
						'mimeType' => $read_mime,
						'text' => $read_result['text'] ?? null,
						'blob' => $read_result['blob'] ?? null,
					]];
				} else {
					$contents = [[
						'uri' => $uri,
						'mimeType' => 'application/json',
						'text' => json_encode($read_result, JSON_UNESCAPED_SLASHES),
					]];
				}

				$result = [
					'contents' => $contents,
				];

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
				'structuredContent' => is_array($tool_result) ? (object)$tool_result : $tool_result,
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
