<?php

	/*
		Model Context Protocol (MCP) Server — protocol version 2026-07-28
		- HTTP: single POST JSON-RPC 2.0 request → response (Streamable HTTP)
		- CLI stdio: persistent process, reads one JSON-RPC 2.0 message per line until stdin closes

		Modern MCP carries the protocol version per-request in the `MCP-Protocol-Version`
		HTTP header (HTTP) or in `_meta.io.modelcontextprotocol/protocolVersion` (stdio).
		There is no `initialize` handshake. Servers advertise versions and capabilities via
		`server/discover`.
	*/

	// JSON-RPC error output
	class McpException extends Exception {
		public $rpc_id;
		public $rpc_code;
		public $rpc_data;
		public function __construct($message, $code=400, $rpc_code=-32000, $rpc_id=null, $rpc_data=null) {
			parent::__construct($message, $code);
			$this->rpc_id = $rpc_id;
			$this->rpc_code = $rpc_code;
			$this->rpc_data = $rpc_data;
		}
	}

	// Model Context Protocol plumbing shared by the backend and frontend servers
	class mcp {

		private static $_sets = [];

		## Node specific methods

		// Load the collection files matching a glob and merge them into one set
		public static function load_sets(string $glob): array {

			if (isset(self::$_sets[$glob])) return self::$_sets[$glob];

			$sets = [
				'tools' => [],
				'resources' => [],
				'resourceTemplates' => [],
			];

			foreach (f::file_search($glob) as $mcp_file) {

				// Include without polluting global scope
				$set = (function() use ($mcp_file) {
					return include $mcp_file;
				})();

				if (!is_array($set)) continue;

				foreach (['tools', 'resources', 'resourceTemplates'] as $key) {
					if (!empty($set[$key]) && is_array($set[$key])) {
						$sets[$key] = array_merge($sets[$key], $set[$key]);
					}
				}
			}

			return self::$_sets[$glob] = $sets;
		}

		// Match a URI against an RFC 6570 (subset) URI template
		public static function match_uri_template(string $template, string $uri): ?array {

			$uri_parts = explode('?', $uri, 2);
			$uri_path = $uri_parts[0];
			$uri_query = [];
			if (isset($uri_parts[1])) {
				parse_str($uri_parts[1], $uri_query);
			}

			$pattern = preg_quote($template, '#');
			$pattern = preg_replace('#\\\{([^{}]+)\\\}#', '(?P<$1>[^/]+)', $pattern);
			$pattern = '#^' . $pattern . '$#';

			if (!preg_match($pattern, $uri_path, $matches)) return null;

			$params = [];
			foreach ($matches as $key => $value) {
				if (!is_int($key)) {
					$params[$key] = $value;
				}
			}

			// A query parameter wins over a placeholder of the same name
			return array_merge($params, $uri_query);
		}

		// Resources/list payload
		public static function list_resources(array $sets): array {

			$resources = [];

			foreach ($sets['resources'] as $resource) {

				if (empty($resource['uri']) || empty($resource['name']) || empty($resource['function']) || !is_callable($resource['function'])) continue;

				$resources[] = [
					'uri' => $resource['uri'],
					'name' => $resource['name'],
					'description' => $resource['description'] ?? '',
					'mimeType' => $resource['mimeType'] ?? 'text/plain',
				];
			}

			return $resources;
		}

		// Resources/templates/list payload
		public static function list_resource_templates(array $sets): array {

			$templates = [];

			foreach ($sets['resourceTemplates'] as $template) {

				if (empty($template['uriTemplate']) || empty($template['name']) || empty($template['function']) || !is_callable($template['function'])) continue;

				$templates[] = [
					'uriTemplate' => $template['uriTemplate'],
					'name' => $template['name'],
					'description' => $template['description'] ?? '',
					'mimeType' => $template['mimeType'] ?? 'text/plain',
				];
			}

			return $templates;
		}

		// Resources/read payload
		public static function read_resource(array $sets, string $uri, array $params): array {

			$handler = null;
			$handler_mime = 'text/plain';
			$handler_params = $params;

			foreach ($sets['resources'] as $resource) {

				if (empty($resource['uri']) || $resource['uri'] !== $uri) continue;

				$handler = $resource['function'];
				$handler_mime = $resource['mimeType'] ?? 'text/plain';
				break;
			}

			if (!$handler) {
				foreach ($sets['resourceTemplates'] as $template) {

					if (empty($template['uriTemplate']) || empty($template['function']) || !is_callable($template['function'])) continue;

					$template_params = self::match_uri_template($template['uriTemplate'], $uri);
					if ($template_params === null) continue;

					$handler = $template['function'];
					$handler_mime = $template['mimeType'] ?? 'text/plain';
					$handler_params = $template_params;
					break;
				}
			}

			if (!$handler) {
				throw new McpException('Resource not found', 200, -32602);
			}

			// A failing handler is a JSON-RPC level error, not a transport failure
			try {
				$read_result = $handler($handler_params);
			} catch (McpException $e) {
				throw new McpException($e->getMessage(), 200, $e->rpc_code ?: -32603, $e->rpc_id, $e->rpc_data);
			} catch (Throwable $e) {
				throw new McpException($e->getMessage(), 200, -32603);
			}

			// A content entry is only valid with a uri and a text or blob member, so a handler
			// may return a contents list, a single entry or a plain value
			$content_entry = function($content) use ($uri, $handler_mime) {

				if (is_object($content)) {
					$content = (array)$content;
				}

				if (is_array($content)) {

					if (!array_key_exists('text', $content) && !array_key_exists('blob', $content)) {
						$encoded = json_encode((object)$content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRETTY_PRINT);
						$content = [
							'text' => $encoded === false ? '' : $encoded,
							'mimeType' => 'application/json',
						];
					}

				} else {
					// A plain value becomes the text of a single content entry
					$content = ['text' => (string)$content];
				}

				if (!isset($content['uri'])) $content['uri'] = $uri;
				if (!isset($content['mimeType'])) $content['mimeType'] = $handler_mime;

				return $content;
			};

			if (is_array($read_result) && $read_result && array_keys($read_result) === range(0, count($read_result) - 1)) {
				// A ready made contents list
				$contents = array_map($content_entry, $read_result);
			} else if (is_array($read_result) && !$read_result) {
				$contents = [];
			} else {
				$contents = [ $content_entry($read_result) ];
			}

			// MCP requires a non-empty contents list
			if (empty($contents)) {
				$contents = [[
					'uri' => $uri,
					'mimeType' => $handler_mime,
					'text' => '',
				]];
			}

			return ['contents' => $contents];
		}
	}

	$indent = "\t"; // No indentation by default for compact output; can be set to e.g. '  ' for pretty-printing

	// Protocol versions this server supports, newest-first.
	// Modern era is post-handshake and stateless; clients SHOULD pick the highest
	// mutually supported version.
	$supported_versions = ['2026-07-28'];

	// Legacy era: pre-handshake, session-oriented. Accepted for backwards
	// compatibility so older clients can still connect. These versions speak the
	// same stateless request/response wire format for every RPC we implement
	// (tools/list, tools/call); the only difference is the initialize handshake.
	$legacy_versions = ['2025-11-25', '2025-06-18', '2025-03-26', '2024-11-05'];

	// Versions we will accept on an inbound request
	$accepted_versions = array_merge($supported_versions, $legacy_versions);

	// --- HTTP-only validation: method, headers, body size ---
	if (!is_cli()) {

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$output = f::format_json([
				'jsonrpc' => '2.0',
				'id' => null,
				'error' => ['code' => -32600, 'message' => 'MCP server expects HTTP POST JSON-RPC requests'],
			], $indent);
			ob_clean();
			http_response_code(405);
			header('Date: '. date('r'));
			header('Content-Type: application/json; charset=UTF-8');
			header('Content-Length: '. strlen($output));
			echo $output;
			exit;
		}

		// Streamable HTTP clients MUST advertise both application/json and text/event-stream
		$accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
		if ($accept && strpos($accept, 'application/json') === false && strpos($accept, 'text/event-stream') === false) {
			$output = f::format_json([
				'jsonrpc' => '2.0',
				'id' => null,
				'error' => [
					'code' => -32600,
					'message' => 'Client must accept application/json or text/event-stream'
				],
			], $indent);
			ob_clean();
			http_response_code(406);
			header('Content-Type: application/json; charset=UTF-8');
			header('Content-Length: '. strlen($output));
			echo $output;
			exit;
		}
	}

	// Parse raw JSON-RPC body first so we can pull `_meta.io.modelcontextprotocol/protocolVersion`
	// and compare it against the `MCP-Protocol-Version` header (HTTP) before authenticating.
	$raw_body = is_cli() ? '' : (file_get_contents('php://input', false, null, 0, 65536) ?: '');
	$rpc = null;

	if (!is_cli()) {
		$rpc = json_decode($raw_body, true);
	}

	// --- Protocol version negotiation ---
	$requested_version = null;

	if (!is_cli()) {
		// HTTP: prefer the header, but fall back to the body `_meta` field.
		$requested_version = $_SERVER['HTTP_MCP_PROTOCOL_VERSION'] ?? null;
		if (!$requested_version && is_array($rpc) && isset($rpc['params']['_meta']['io.modelcontextprotocol/protocolVersion'])) {
			$requested_version = $rpc['params']['_meta']['io.modelcontextprotocol/protocolVersion'];
		}

		// Both MUST match per spec.
		$meta_version = is_array($rpc) && isset($rpc['params']['_meta']['io.modelcontextprotocol/protocolVersion'])
			? $rpc['params']['_meta']['io.modelcontextprotocol/protocolVersion']
			: null;

		if ($requested_version && $meta_version && $requested_version !== $meta_version) {
			$output = f::format_json([
				'jsonrpc' => '2.0',
				'id' => is_array($rpc) && isset($rpc['id']) ? $rpc['id'] : null,
				'error' => [
					'code' => -32020,
					'message' => 'MCP-Protocol-Version header does not match _meta.io.modelcontextprotocol/protocolVersion',
					'data' => ['header' => $requested_version, 'meta' => $meta_version],
				],
			], $indent);
			ob_clean();
			http_response_code(400);
			header('Content-Type: application/json; charset=UTF-8');
			header('Content-Length: '. strlen($output));
			echo $output;
			exit;
		}

		if ($requested_version && !in_array($requested_version, $accepted_versions, true)) {
			$output = f::format_json([
				'jsonrpc' => '2.0',
				'id' => is_array($rpc) && isset($rpc['id']) ? $rpc['id'] : null,
				'error' => [
					'code' => -32022,
					'message' => 'Unsupported protocol version',
					'data' => [
						'supported' => $accepted_versions,
						'requested' => $requested_version,
					],
				],
			], $indent);
			ob_clean();
			http_response_code(400);
			header('MCP-Protocol-Version: '. implode(', ', $supported_versions));
			header('Content-Type: application/json; charset=UTF-8');
			header('Content-Length: '. strlen($output));
			echo $output;
			exit;
		}
	} else {
		// stdio: version comes from `_meta.io.modelcontextprotocol/protocolVersion` on each request.
		// Stdio clients typically re-send metadata every request, so we validate it inline below.
	}

	// Authenticate once for the lifetime of the process.
	// This server is an OAuth 2.1 resource server: it accepts only a Bearer access token
	// minted by our own token endpoint.
	try {

		$token = '';
		if (preg_match('#^Bearer\s+(\S+)$#i', trim(getallheaders()['Authorization'] ?? ''), $matches)) {
			$token = $matches[1];
		}

		// Throttle invalid keys per calling IP
		$num_attempts = database::query(
			"select count(*) as num_attempts from ". DB_PREFIX ."rate_limiting
			where action = 'mcp_auth_failed'
			and ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."'
			and created_at >= '". date('Y-m-d H:i:s', strtotime('-5 minutes')) ."'
			;"
		)->fetch('num_attempts');

		if ($num_attempts >= 30) {
			throw new McpException('Too many failed authentication attempts', 429, -32029);
		}

		if ($token === '') {

			database::insert('rate_limiting', [
				'action' => 'mcp_auth_failed',
				'ip_address' => $_SERVER['REMOTE_ADDR'],
				'hostname' => reverse_dns($_SERVER['REMOTE_ADDR']),
				'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
				'created_at' => date('Y-m-d H:i:s'),
			]);

			throw new McpException('Authentication required', 401, -32000);
		}

		// Resolve the access key, only the hash is ever stored
		$access_key = database::query(
			"select * from ". DB_PREFIX ."access_keys
			where key_hash = '". database::input(hash('sha256', $token)) ."'
			and status = 1
			limit 1;"
		)->fetch();

		if (!$access_key) {

			database::insert('rate_limiting', [
				'action' => 'mcp_auth_failed',
				'ip_address' => $_SERVER['REMOTE_ADDR'],
				'hostname' => reverse_dns($_SERVER['REMOTE_ADDR']),
				'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
				'created_at' => date('Y-m-d H:i:s'),
			]);

			throw new McpException('The access key is invalid or has been revoked', 401, -32000);
		}

		// Record key usage so unused keys stay visible in the manager
		$now = date('Y-m-d H:i:s');
		database::query(
			"update ". DB_PREFIX ."access_keys
			set last_used_at = '". database::input($now) ."'
			where id = ". (int)$access_key['id'] ."
			limit 1;"
		);

		database::query(
			"delete from ". DB_PREFIX ."rate_limiting
			where action = 'mcp_auth_failed'
			and ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."';"
		);

		$permissions   = !empty($access_key['permissions']) ? json_decode($access_key['permissions'], true) : [];
		$allowed_tools = array_merge(...($permissions['mcp'] ?? []));

	} catch (McpException $e) {

		$output = f::format_json([
			'jsonrpc' => '2.0',
			'id'      => $e->rpc_id,
			'error'   => [
				'code' => $e->rpc_code,
				'message' => $e->getMessage(),
				'data' => $e->rpc_data,
			],
		], $indent);

		if (is_cli()) {
			fwrite(STDOUT, $output . "\n");
		} else {
			http_response_code($e->getCode() ?: 500);
			if ($e->getCode() == 401) {
				header('WWW-Authenticate: Bearer realm="'. PLATFORM_NAME .' MCP Server", error="invalid_token"');
			} else if ($e->getCode() == 429) {
				header('Retry-After: 300');
			}
			ob_clean();
			header('Date: '. date('r'));
			header('Content-Type: application/json; charset=UTF-8');
			header('Content-Length: '. strlen($output));
			echo $output;
		}

		exit;
	}

	// Dispatch one JSON-RPC request. Returns the JSON response string, or null for notifications (no response).
	$dispatch = function(string $raw) use ($indent, $allowed_tools, $supported_versions, $legacy_versions, $accepted_versions): ?string {

		$rpc_id = null;

		try {

			$rpc = json_decode($raw, true);

			if (!is_array($rpc) || empty($rpc['jsonrpc']) || $rpc['jsonrpc'] !== '2.0' || empty($rpc['method'])) {
				throw new McpException('Invalid Request', 400, -32600);
			}

			$rpc_id = $rpc['id'] ?? null;
			$params = isset($rpc['params']) && is_array($rpc['params']) ? $rpc['params'] : [];

			// Per-request protocol version validation on stdio (HTTP validates before dispatch).
			if (is_cli()) {
				$req_ver = $params['_meta']['io.modelcontextprotocol/protocolVersion'] ?? null;
				if ($req_ver && !in_array($req_ver, $accepted_versions, true)) {
					throw new McpException('Unsupported protocol version', 400, -32022, $rpc_id, [
						'supported' => $accepted_versions,
						'requested' => $req_ver,
					]);
				}
			}

			switch ($rpc['method']) {

				// Legacy handshake, retained for clients that predate 2026-07-28.
				// The server is stateless, so no Mcp-Session-Id is issued and every
				// subsequent request is self-describing anyway.
				case 'initialize':

					// Echo back the client's requested version when we speak it,
					// otherwise offer our newest supported version.
					$client_version = $params['protocolVersion'] ?? null;
					$negotiated = in_array($client_version, $accepted_versions, true)
						? $client_version
						: $supported_versions[0];

					$result = [
						'protocolVersion' => $negotiated,
						'capabilities' => [
							'tools' => new stdClass(),
							'resources' => new stdClass(),
						],
						'serverInfo' => [
							'name' => PLATFORM_NAME .' MCP Server',
							'version' => PLATFORM_VERSION,
						],
						'instructions' => PLATFORM_NAME .' Model Context Protocol server. Tools are filtered by the access key\'s permissions.',
					];

					break;

				// Legacy notification, acknowledged with no response body.
				case 'notifications/initialized':
					return null;

				// MCP built-in: capability and version discovery
				case 'server/discover':

					$result = [
						'resultType' => 'complete',
						'supportedVersions' => $supported_versions,
						'capabilities' => [
							'tools' => new stdClass(),
							'resources' => new stdClass(),
						],
						'_meta' => [
							'io.modelcontextprotocol/serverInfo' => [
								'name' => PLATFORM_NAME .' MCP Server',
								'version' => PLATFORM_VERSION,
							],
						],
						'instructions' => PLATFORM_NAME .' Model Context Protocol server. Tools are filtered by the access key\'s permissions.',
						'ttlMs' => 300000,
						'cacheScope' => 'private',
					];

					break;

				// MCP: list available tools
				case 'tools/list':

					$tool_schemas = [];

					foreach (mcp::load_sets('app://shared/mcp/mcp_*.inc.php')['tools'] as $tool) {

						if (empty($tool['name']) || !is_array($tool['inputSchema'])) continue;

						// Skip tools the access key isn't permitted to use
						if (!empty($allowed_tools) && !in_array($tool['name'], $allowed_tools)) continue;

						$tool_schemas[] = [
							'name' => $tool['name'],
							'title' => $tool['title'] ?? $tool['name'],
							'description' => $tool['description'] ?? '',
							'inputSchema' => $tool['inputSchema'] ?? [
								'type' => 'object',
								'additionalProperties' => false,
							],
						];
					}

					$result = [
						'resultType' => 'complete',
						'tools' => $tool_schemas,
					];

					break;

				// MCP: call a tool
				case 'tools/call':

					if (empty($params['name'])) {
						throw new McpException('Missing tool name', 400, -32602);
					}

					// Tool dispatch
					foreach (mcp::load_sets('app://shared/mcp/mcp_*.inc.php')['tools'] as $tool) {

						if (empty($tool['name']) || $tool['name'] !== $params['name']) continue;

						// Per-toolset permission check
						if (!empty($allowed_tools) && !in_array($tool['name'], $allowed_tools)) {
							throw new McpException('Tool not permitted for this access key', 403, -32001, $rpc_id);
						}

						$tool_args = $params['arguments'] ?? [];
						if (!is_array($tool_args)) {
							throw new McpException('Invalid arguments: expected an object', 400, -32602, $rpc_id);
						}

						// Check input against required parameters
						if (!empty($tool['inputSchema']['required']) && is_array($tool['inputSchema']['required'])) {
							foreach ($tool['inputSchema']['required'] as $field) {
								if (!isset($tool_args[$field]) || $tool_args[$field] === '') {
									throw new McpException("Missing required parameter: $field", 400, -32602);
								}
							}
						}

						$tool_result = ($tool['function'])($tool_args);
						break;
					}

					if (!isset($tool_result)) {
						throw new McpException('Tool not found', 404, -32601);
					}

					$result = [
						'resultType' => 'complete',
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

				// MCP: list available resources
				case 'resources/list':

					$result = [
						'resultType' => 'complete',
						'resources' => mcp::list_resources(mcp::load_sets('app://shared/mcp/mcp_*.inc.php')),
					];

					break;

				// MCP: list available resource templates
				case 'resources/templates/list':

					$result = [
						'resultType' => 'complete',
						'resourceTemplates' => mcp::list_resource_templates(mcp::load_sets('app://shared/mcp/mcp_*.inc.php')),
					];

					break;

				// MCP: subscribe and unsubscribe are no-ops, the server is stateless
				case 'resources/subscribe':
				case 'resources/unsubscribe':

					$result = [
						'resultType' => 'complete',
					];

					break;

				// MCP: read a resource
				case 'resources/read':

					if (empty($params['uri'])) {
						throw new McpException('Missing resource URI', 200, -32602, $rpc_id);
					}

					$result = array_merge(['resultType' => 'complete'], mcp::read_resource(
						mcp::load_sets('app://shared/mcp/mcp_*.inc.php'),
						$params['uri'],
						$params
					));

					break;

				// Unknown method — server does not implement this RPC.
				// Streamable HTTP requires 404 Not Found with a JSON-RPC error body for unknown methods.
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
				'error'   => [
					'code' => $e->rpc_code,
					'message' => $e->getMessage(),
					'data' => $e->rpc_data,
				],
			], $indent) ?: '{"jsonrpc":"2.0","id":null,"error":{"code":-32603,"message":"Encoding error"}}';

		} catch (Throwable $e) {

			return f::format_json([
				'jsonrpc' => '2.0',
				'id'      => $rpc_id,
				'error'   => [
					'code' => -32000,
					'message' => $e->getMessage(),
				],
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
	$output = $dispatch($raw_body);

	if ($output === null) {
		// Notifications over HTTP MUST return 202 Accepted with no body.
		ob_clean();
		http_response_code(202);
		exit;
	}

	// Detect "Method not found" and surface HTTP 404 alongside the JSON-RPC error body
	// per the Streamable HTTP server-validation rule.
	$http_status = 200;
	if (preg_match('#"code":\s*-32601#', $output)) {
		$http_status = 404;
	}

	ob_clean();
	if (!headers_sent()) {
		header('Date: '. date('r'));
		header('Content-Type: application/json; charset=UTF-8');
		header('Content-Length: '. strlen($output));
	}
	http_response_code($http_status);
	echo $output;
	exit;