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
			$pattern = preg_replace('#\\\{([^{}]+)\\\}#', '(?P<$1>[^/]+)', $pattern);
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
			static $cache = null;

			if ($cache !== null) {
				return $cache;
			}

			$sets = [
				'tools' => [],
				'resources' => [],
				'resourceTemplates' => []
			];

			foreach (functions::file_search(vmod::check(FS_DIR_APP . 'includes/mcp/mcp_*.inc.php')) as $mcp_file) {

				$set = (function() use ($mcp_file) {
					return include $mcp_file;
				})();

				if (!is_array($set)) continue;

				if (!empty($set['tools']) && is_array($set['tools'])) {
					$sets['tools'] = array_merge($sets['tools'], $set['tools']);
				}

				if (!empty($set['resources']) && is_array($set['resources'])) {
					$sets['resources'] = array_merge($sets['resources'], $set['resources']);
				}

				if (!empty($set['resourceTemplates']) && is_array($set['resourceTemplates'])) {
					$sets['resourceTemplates'] = array_merge($sets['resourceTemplates'], $set['resourceTemplates']);
				}
			}
			return $cache = $sets;
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

			$auth_username = $_SERVER['PHP_AUTH_USER'];
			$auth_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

			// Brute force protection using rate limiting
			$auth_scope = "ip_address = '". database::input($auth_ip) ."' or (scope_type = 'username' and scope_key = '". database::input(strtolower($auth_username)) ."')";

			if (database::query(
				"select count(*) as num_attempts
				from ". DB_TABLE_PREFIX ."rate_limiting
				where action = 'mcp_auth_failed'
				and (". $auth_scope .")
				and date_created >= '". date('Y-m-d H:i:s', strtotime('-5 minutes')) ."'"
			)->fetch('num_attempts') > 3) {
				throw new McpException(language::translate('error_too_many_attempts', 'Too many failed attempts. Please try again later.'), 429);
			}

			$record_failed_attempt = function() use ($auth_ip, $auth_username) {
				database::query(
					"insert into ". DB_TABLE_PREFIX ."rate_limiting
					(action, scope_type, scope_key, ip_address, hostname, user_agent, date_created)
					values ('mcp_auth_failed', 'username', '". database::input(strtolower($auth_username)) ."', '". database::input($auth_ip) ."', '". database::input($auth_ip !== '' ? gethostbyaddr($auth_ip) : 'unknown') ."', '". database::input(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '') ."', '". date('Y-m-d H:i:s') ."')"
				);
			};

			$user = database::query(
				"select * from ". DB_TABLE_PREFIX ."users
				where status
				and lower(username) = lower('". database::input($auth_username) ."')
				and (date_valid_from is null or date_valid_from < '". database::input(date('Y-m-d H:i:s')) ."')
				and (date_valid_to is null or date_valid_to > '". database::input(date('Y-m-d H:i:s')) ."')
				limit 1;"
			)->fetch();

			// An unknown username and a wrong password answer with the same message and status,
			// so the endpoint cannot be used to probe for valid accounts.
			if (!$user) {
				$record_failed_attempt();
				throw new McpException(language::translate('error_wrong_username_password_combination', 'Wrong combination of username and password or the account does not exist.'), 401);
			}

			if (!password_verify($_SERVER['PHP_AUTH_PW'], $user['password_hash'])) {
				$record_failed_attempt();
				throw new McpException(language::translate('error_wrong_username_password_combination', 'Wrong combination of username and password or the account does not exist.'), 401);
			}

			// Authenticated, so the recorded attempts no longer apply.
			database::query(
				"delete from ". DB_TABLE_PREFIX ."rate_limiting
				where action = 'mcp_auth_failed'
				and (". $auth_scope .")"
			);

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
					throw new McpException('Missing resource URI', 200, -32602, $rpc_id);
				}

				$uri = $params['uri'];
				$read_result = null;
				$read_mime = 'text/plain';
				$sets = mcp_load_sets();

				// Resolve a matching resource or template (exact URI or URI template).
				$handler = null;
				$handler_uri = null;
				$handler_params = $params;
				$handler_mime = 'text/plain';

				foreach ($sets['resources'] as $resource) {
					if (empty($resource['uri']) || $resource['uri'] !== $uri) continue;
					$handler = $resource['function'];
					$handler_uri = $resource['uri'];
					$handler_mime = isset($resource['mimeType']) ? $resource['mimeType'] : 'text/plain';
					break;
				}

				if (!$handler) {
					foreach ($sets['resourceTemplates'] as $template) {
						if (empty($template['uriTemplate'])) continue;
						$template_params = McpUriTemplate::match($template['uriTemplate'], $uri);
						if ($template_params === null) continue;
						$handler = $template['function'];
						$handler_uri = $template['uriTemplate'];
						$handler_params = $template_params;
						$handler_mime = isset($template['mimeType']) ? $template['mimeType'] : 'text/plain';
						break;
					}
				}

				if (!$handler) {
					throw new McpException('Resource not found', 200, -32602, $rpc_id);
				}

				// A failing resource handler is a JSON-RPC level error, not a transport failure. Keep the
				// status at 200, because a Streamable HTTP client rejects a non-2xx response and
				// discards the body, which would hide the message it needs to see.
				try {
					$read_result = $handler($handler_params);
				} catch (McpException $e) {
					throw new McpException($e->getMessage(), 200, $e->rpc_code ?: -32603, $rpc_id);
				} catch (Throwable $e) {
					throw new McpException($e->getMessage(), 200, -32603, $rpc_id);
				}
				$read_mime = $handler_mime;

				// A content entry is only valid with a uri and a text or blob member. A handler may
				// return a ready made contents list, a single entry, or a plain value, so normalize all
				// three here and encode anything without text or blob as JSON.
				$content_entry = function($content) use ($uri, $read_mime) {
					if (is_array($content) || is_object($content)) {
						$content = (array)$content;
						if (!array_key_exists('text', $content) && !array_key_exists('blob', $content)) {
							$encoded = json_encode((object)$content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRETTY_PRINT);
							$content = [
								'text' => $encoded === false ? '' : $encoded,
								'mimeType' => 'application/json',
							];
						}
					} else {
						// A plain value, a string included, becomes the text of a single content entry
						$content = ['text' => (string)$content];
					}
					if (!isset($content['uri'])) $content['uri'] = $uri;
					if (!isset($content['mimeType'])) $content['mimeType'] = $read_mime;
					return $content;
				};

				switch (gettype($read_result)) {

					case 'array':
						// Pre-built contents list: [['uri'=>..., 'mimeType'=>..., 'text'=>...], ...]
						if (array_keys($read_result) === range(0, count($read_result) - 1)) {
							$contents = array_map($content_entry, $read_result);
						} else {
							// Single content entry, or a plain associative array encoded as JSON
							$contents = [ $content_entry($read_result) ];
						}
						break;

					case 'object':
						$contents = [ $content_entry($read_result) ];
						break;

					case 'NULL':
						$contents = [[
							'uri' => $uri,
							'mimeType' => $read_mime,
							'text' => '',
						]];
						break;

					default:
						$contents = [[
							'uri' => $uri,
							'mimeType' => $read_mime,
							'text' => is_string($read_result) ? $read_result : (string)$read_result,
						]];
				}

				// MCP requires a non-empty contents list.
				if (empty($contents)) {
					$contents = [[
						'uri' => $uri,
						'mimeType' => $read_mime,
						'text' => '',
					]];
				}

				$result = [
					'contents' => $contents,
				];

				break;

			case 'tools/list':

				$tools = [];
				foreach (mcp_load_sets()['tools'] as $tool) {

					if (empty($tool['name']) || !is_array($tool['inputSchema'])) continue;

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
					throw new McpException('Missing tool name', 200, -32602, $rpc_id);
				}

				$tool_result = null;
				$tool = null;
				foreach (mcp_load_sets()['tools'] as $candidate) {
					if (empty($candidate['name']) || $candidate['name'] !== $params['name']) continue;
					$tool = $candidate;
					break;
				}

				if (!$tool) {
					throw new McpException('Tool not found', 200, -32602, $rpc_id);
				}

				// Support both 'arguments' (MCP standard) and 'input' (legacy)
				$tool_args = isset($params['arguments']) ? $params['arguments'] : (isset($params['input']) ? $params['input'] : []);
				if (!is_array($tool_args)) {
					throw new McpException('Invalid arguments: expected an object', 200, -32602, $rpc_id);
				}

				// Validate against required parameters (also enforce type when schema provides it)
				if (!empty($tool['inputSchema']['required']) && is_array($tool['inputSchema']['required'])) {
					$missing = [];
					foreach ($tool['inputSchema']['required'] as $field) {
						if (!array_key_exists($field, $tool_args) || $tool_args[$field] === null || $tool_args[$field] === '') {
							$missing[] = $field;
						}
					}
					if (!empty($missing)) {
						$result = [
							'content' => [
								[
									'type' => 'text',
									'text' => 'Missing required parameter(s): '. implode(', ', $missing),
								]
							],
							'isError' => true,
						];
						break;
					}
				}

				try {
					$tool_result = ($tool['function'])($tool_args);
				} catch (Throwable $e) {
					$result = [
						'content' => [
							[
								'type' => 'text',
								'text' => $e->getMessage(),
							]
						],
						'isError' => true,
					];
					break;
				}

				// Shape the tool result into MCP's tools/call response:
				//   object       -> emit 'content' (text) + 'structuredContent'
				//   list(array)  -> only treat as 'content' if every entry looks like a content block (has a 'type' key)
				//   assoc array  -> emit as JSON object via 'content' (text) + 'structuredContent'
				//   other        -> wrap in a single 'text' content block
				$looks_like_content_blocks = is_array($tool_result)
					&& array_keys($tool_result) === range(0, count($tool_result) - 1)
					&& !empty($tool_result)
					&& array_reduce($tool_result, function($carry, $block) {
						return $carry && is_array($block) && isset($block['type']);
					}, true);

				switch (true) {

					case $looks_like_content_blocks:
						$result = [
							'content' => $tool_result,
							'isError' => false,
						];
						break;

					case is_object($tool_result):
						$result = [
							'content' => [[
								'type' => 'text',
								'text' => json_encode($tool_result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
							]],
							'structuredContent' => $tool_result,
							'isError' => false,
						];
						break;

					case is_array($tool_result):
						// Associative array returned as a JSON object
						$result = [
							'content' => [[
								'type' => 'text',
								'text' => json_encode($tool_result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
							]],
							'structuredContent' => (object)$tool_result,
							'isError' => false,
						];
						break;

					default:
						$result = [
							'content' => [
								[
									'type' => 'text',
									'text' => is_string($tool_result) ? $tool_result : (string)$tool_result,
								]
							],
							'isError' => false,
						];
				}

				break;

			default:
				throw new McpException('Method not found', 200, -32601, $rpc_id);
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
		], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

		if ($output === false) {
			throw new McpException('Encoding error', 200, -32603, $rpc_id);
		}

	} catch (McpException $e) {

		$http_code = ($e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 200;
		http_response_code($http_code);

		if ($http_code === 401) {
			header('WWW-Authenticate: Basic realm="MCP"');
		}

		$output = json_encode([
			'jsonrpc' => '2.0',
			'id' => $e->rpc_id ?? $rpc_id ?? null,
			'error' => [
				'code' => $e->rpc_code,
				'message' => $e->getMessage(),
			],
		], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

		if ($output === false) {
			$output = json_encode([
				'jsonrpc' => '2.0',
				'id' => $e->rpc_id ?? $rpc_id ?? null,
				'error' => [
					'code' => -32603,
					'message' => 'Encoding error',
				],
			], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
		}

	} catch (Throwable $e) {

		http_response_code(($e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 200);

		$output = json_encode([
			'jsonrpc' => '2.0',
			'id' => isset($rpc_id) ? $rpc_id : null,
			'error' => [
				'code' => -32000,
				'message' => $e->getMessage(),
			],
		], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

		if ($output === false) {
			$output = json_encode([
				'jsonrpc' => '2.0',
				'id' => $rpc_id ?? null,
				'error' => [
					'code' => -32603,
					'message' => 'Encoding error',
				],
			], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
		}
	}

	ob_clean();
	header('Date: '. date('r'));
	header('Content-Type: application/json; charset=UTF-8');
	header('Content-Length: '. strlen($output));
	echo $output;
	exit;
