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

	// Load all MCP resource/tool sets from disk (with vmod overrides applied).
	if (!function_exists('mcp_load_sets')) {

		function mcp_load_sets() {
			$sets = [
				'tools' => [],
				'resources' => [],
				'resourceTemplates' => []
			];

			foreach (functions::file_search(vmod::check(FS_DIR_APP . 'includes/mcp/mcp_*.inc.php')) as $mcp_file) {

				$set = (function() use ($mcp_file) {
					return include $mcp_file;
				})();

				if (!is_array($$set)) continue;

				if (!empty($$set['tools']) && is_array($$set['tools'])) {
					$sets['tools'] = array_merge($sets['tools'], $$set['tools']);
				}

				if (!empty($$set['resources']) && is_array($$set['resources'])) {
					$sets['resources'] = array_merge($sets['resources'], $$set['resources']);
				}

				if (!empty($$set['resourceTemplates']) && is_array($$set['resourceTemplates'])) {
					$sets['resourceTemplates'] = array_merge($sets['resourceTemplates'], $$set['resourceTemplates']);
				}
			}
			return $sets;
		}
	}

	// Normalize a resource callback return value into MCP contents[] entries.
	if (!function_exists('mcp_normalize_contents')) {
		function mcp_normalize_contents($result, $uri, $default_mime = 'text/plain') {

			if (is_string($result)) {
				return [[
					'uri' => $uri,
					'mimeType' => $default_mime,
					'text' => $result
				]];
			}

			if (is_array($result) && isset($result['contents']) && is_array($result['contents'])) {
				return $result['contents'];
			}

			if (is_array($result) && (isset($result['text']) || isset($result['blob']))) {
				$entry = [
					'uri' => $uri,
					'mimeType' => $default_mime
				];

				if (array_key_exists('text', $result)) {
					$entry['text'] = $result['text'];
				}

				if (array_key_exists('blob', $result)) {
					$entry['blob'] = $result['blob'];
				}

				return [ $entry ];
			}

			return [[
				'uri' => $uri,
				'mimeType' => 'application/json',
				'text' => json_encode($result, JSON_UNESCAPED_SLASHES),
			]];
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

		// Validate Accept header per MCP Streamable HTTP transport.
		// Tolerate comma-separated media-type lists (e.g. "application/json, text/event-stream").
		if (!empty($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] !== '*/*') {
			$accept_types = array_map('trim', explode(',', $_SERVER['HTTP_ACCEPT']));
			$has_json = false; $has_sse = false;
			foreach ($accept_types as $type) {
				if (strpos($type, 'application/json') === 0) $has_json = true;
				if (strpos($type, 'text/event-stream') === 0) $has_sse = true;
			}
			if (!$has_json && !$has_sse) {
				throw new McpException('Client must accept application/json or text/event-stream', 406, -32600);
			}
		}

		$raw = file_get_contents('php://input');

		$rpc = json_decode($raw, true);

		if (!is_array($rpc) || empty($rpc['jsonrpc']) || $rpc['jsonrpc'] !== '2.0' || empty($rpc['method'])) {
			throw new McpException('Invalid Request', 400, -32600);
		}

		$rpc_id = isset($rpc['id']) ? $rpc['id'] : null;
		$params = isset($rpc['params']) && is_array($rpc['params']) ? $rpc['params'] : [];
		$is_notification = !array_key_exists('id', $rpc);

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
						'logging' => new stdClass(),
					],
					'instructions' => 'LiteCart MCP server. Authenticate with HTTP Basic credentials of an admin user.',
				];

				break;

			case 'ping':

				$result = new stdClass();
				break;

			case 'notifications/initialized':
			case 'notifications/cancelled':
			case 'notifications/progress':

				// Notifications have no id and require no response.
				ob_clean();
				http_response_code(204);
				exit;

			case 'resources/list':

				$resources = [];
				foreach (mcp_load_sets()['resources'] as $resource) {

					if (empty($resource['uri']) || empty($resource['name']) || empty($resource['function']) || !is_callable($resource['function'])) continue;
					if (!empty($allowed_resources) && !in_array($resource['uri'], $allowed_resources)) continue;

					$resources[] = [
						'uri' => $resource['uri'],
						'name' => $resource['name'],
						'description' => isset($resource['description']) ? $resource['description'] : '',
						'mimeType' => isset($resource['mimeType']) ? $resource['mimeType'] : 'text/plain',
					];
				}

				$result = [ 'resources' => $resources ];
				break;

			case 'resources/templates/list':

				$resource_templates = [];
				foreach (mcp_load_sets()['resourceTemplates'] as $template) {

					if (empty($template['uriTemplate']) || empty($template['name']) || empty($template['function']) || !is_callable($template['function'])) continue;
					if (!empty($allowed_resources) && !in_array($template['uriTemplate'], $allowed_resources)) continue;

					$resource_templates[] = [
						'uriTemplate' => $template['uriTemplate'],
						'name' => $template['name'],
						'description' => isset($template['description']) ? $template['description'] : '',
						'mimeType' => isset($template['mimeType']) ? $template['mimeType'] : 'text/plain',
					];
				}

				$result = [ 'resourceTemplates' => $resource_templates ];
				break;

			case 'resources/subscribe':
			case 'resources/unsubscribe':

				// Stateless server: subscriptions are no-ops; clients may call these safely.
				$result = new stdClass();
				break;

			case 'resources/read':

				if (empty($params['uri'])) {
					throw new McpException('Missing resource URI', 400, -32602);
				}

				$uri = $params['uri'];
				$read_result = null;
				$read_mime = 'text/plain';
				$sets = mcp_load_sets();

				// Static resources (exact URI match)
				foreach ($sets['resources'] as $resource) {

					if (empty($resource['uri']) || $resource['uri'] !== $uri) continue;

					if (!empty($allowed_resources) && !in_array($resource['uri'], $allowed_resources)) {
						throw new McpException('Resource not permitted for this administrator', 403, -32001, $rpc_id);
					}

					$read_result = ($resource['function'])($params);
					$read_mime = isset($resource['mimeType']) ? $resource['mimeType'] : 'text/plain';
					break;
				}

				// Resource templates (URI pattern match)
				if ($read_result === null) {
					foreach ($sets['resourceTemplates'] as $template) {

						if (empty($template['uriTemplate'])) continue;

						$template_params = McpUriTemplate::match($template['uriTemplate'], $uri);
						if ($template_params === null) continue;

						if (!empty($allowed_resources) && !in_array($template['uriTemplate'], $allowed_resources)) {
							throw new McpException('Resource not permitted for this administrator', 403, -32001, $rpc_id);
						}

						$read_result = ($template['function'])($template_params);
						$read_mime = isset($template['mimeType']) ? $template['mimeType'] : 'text/plain';
						break;
					}
				}

				if ($read_result === null) {
					throw new McpException('Resource not found', 404, -32601);
				}

				$result = [
					'contents' => mcp_normalize_contents($read_result, $uri, $read_mime),
				];

				break;

			case 'tools/list':

				$tools = [];
				foreach (mcp_load_sets()['tools'] as $tool) {

					if (empty($tool['name']) || !is_array($tool['inputSchema'])) continue;
					if (!empty($allowed_tools) && !in_array($tool['name'], $allowed_tools)) continue;

					$tools[] = [
						'name' => $tool['name'],
						'description' => isset($tool['description']) ? $tool['description'] : '',
						'inputSchema' => isset($tool['inputSchema']) ? $tool['inputSchema'] : [
							'type' => 'object',
							'properties' => new stdClass(),
						],
					];
				}

				$result = [
					'tools' => $tools
				];

				break;

			case 'tools/call':

				if (empty($params['name'])) {
					throw new McpException('Missing tool name', 400, -32602);
				}

				$tool_result = null;
				foreach (mcp_load_sets()['tools'] as $tool) {

					if (empty($tool['name']) || $tool['name'] !== $params['name']) continue;

					// Per-tool permission check
					if (!empty($allowed_tools) && !in_array($tool['name'], $allowed_tools)) {
						throw new McpException('Tool not permitted for this administrator', 403, -32001, $rpc_id);
					}

					// Support both 'arguments' (MCP standard) and 'input' (legacy)
					$tool_args = isset($params['arguments']) ? $params['arguments'] : (isset($params['input']) ? $params['input'] : []);

					// Validate against required parameters (also enforce type when schema provides it)
					if (!empty($tool['inputSchema']['required']) && is_array($tool['inputSchema']['required'])) {
						foreach ($tool['inputSchema']['required'] as $field) {
							if (!isset($tool_args[$field]) || $tool_args[$field] === '' || $tool_args[$field] === null) {
								throw new McpException("Missing required parameter: $field", 400, -32602);
							}
						}
					}

					try {
						$tool_result = ($tool['function'])($tool_args);
					} catch (Exception $e) {
						throw new McpException($e->getMessage(), 500, -32601);
					}

					break;
				}

				if ($tool_result === null) {
					throw new McpException('Tool not found', 404, -32601);
				}

				$result = [
					'content' => [
						[
							'type' => 'text',
							'text' => is_string($tool_result) ? $tool_result : json_encode($tool_result, JSON_UNESCAPED_SLASHES),
						]
					],
					'structuredContent' => is_array($tool_result) ? (object)$tool_result : $tool_result,
					'isError' => false,
				];

				break;

			default:
				throw new McpException('Method not found', 404, -32601);
		}

		// Notifications (no id) must not produce a JSON-RPC response body.
		if ($is_notification) {
			ob_clean();
			http_response_code(204);
			exit;
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
