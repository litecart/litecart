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

	// Custom exception for JSON-RPC error output
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

	$indent = "\t"; // No indentation by default for compact output; can be set to e.g. '  ' for pretty-printing

	// Protocol versions this server supports (modern era, post-handshake).
	// Listed newest-first; clients SHOULD pick the highest mutually supported.
	$supported_versions = ['2026-07-28'];

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

		if ($requested_version && !in_array($requested_version, $supported_versions, true)) {
			$output = f::format_json([
				'jsonrpc' => '2.0',
				'id' => is_array($rpc) && isset($rpc['id']) ? $rpc['id'] : null,
				'error' => [
					'code' => -32022,
					'message' => 'Unsupported protocol version',
					'data' => [
						'supported' => $supported_versions,
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
	// minted by our own token endpoint. The administrator is re-resolved and re-checked on
	// every request so a suspended account cannot keep using a still-valid token.
	try {

		$token = '';
		if (preg_match('#^Bearer\s+(\S+)$#i', trim(getallheaders()['Authorization'] ?? ''), $matches)) {
			$token = $matches[1];
		}

		// Throttle invalid keys per calling IP
		if (security::rate_limit_reached('mcp_auth_failed', 30)) {
			throw new McpException('Too many failed authentication attempts', 429, -32029);
		}

		if ($token === '') {
			security::rate_limit_add('mcp_auth_failed');
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
			security::rate_limit_add('mcp_auth_failed');
			throw new McpException('The access key is invalid or has been revoked', 401, -32000);
		}

		// Re-check the owning administrator as the account may have been suspended
		$now = date('Y-m-d H:i:s');

		$administrator = database::query(
			"select * from ". DB_PREFIX ."administrators
			where id = ". (int)$access_key['administrator_id'] ."
			and status
			and (valid_from is null or valid_from < '". database::input($now) ."')
			and (valid_to is null or valid_to > '". database::input($now) ."')
			and (blocked_until is null or blocked_until < '". database::input($now) ."')
			limit 1;"
		)->fetch();

		if (!$administrator) {
			security::rate_limit_add('mcp_auth_failed');
			throw new McpException(t('error_administrator_not_found', 'The administrator is either suspended or could not be found in our database'), 401);
		}

		// Record key usage so unused keys stay visible in the manager
		database::query(
			"update ". DB_PREFIX ."access_keys
			set last_used_at = '". database::input($now) ."'
			where id = ". (int)$access_key['id'] ."
			limit 1;"
		);

		security::rate_limit_clear('mcp_auth_failed');

		$permissions   = !empty($administrator['permissions']) ? json_decode($administrator['permissions'], true) : [];
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
	$dispatch = function(string $raw) use ($indent, $allowed_tools, $supported_versions): ?string {

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
				if ($req_ver && !in_array($req_ver, $supported_versions, true)) {
					throw new McpException('Unsupported protocol version', 400, -32022, $rpc_id, [
						'supported' => $supported_versions,
						'requested' => $req_ver,
					]);
				}
			}

			switch ($rpc['method']) {

				// MCP built-in: capability and version discovery
				case 'server/discover':

					$result = [
						'resultType' => 'complete',
						'supportedVersions' => $supported_versions,
						'capabilities' => [
							'tools' => new stdClass(),
						],
						'_meta' => [
							'io.modelcontextprotocol/serverInfo' => [
								'name' => PLATFORM_NAME .' MCP Server',
								'version' => PLATFORM_VERSION,
							],
						],
						'instructions' => PLATFORM_NAME .' Model Context Protocol server. Tools are filtered by administrator permissions.',
						'ttlMs' => 300000,
						'cacheScope' => 'private',
					];

					break;

				// MCP: list available tools
				case 'tools/list':

					$tool_schemas = [];

					foreach (f::file_search('app://shared/mcp/mcp_*.inc.php') as $mcp_file) {

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
								'title' => $tool['title'] ?? $tool['name'],
								'description' => $tool['description'] ?? '',
								'inputSchema' => $tool['inputSchema'] ?? [
									'type' => 'object',
									'additionalProperties' => false,
								],
							];
						}
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
					foreach (f::file_search('app://shared/mcp/mcp_*.inc.php') as $mcp_file) {

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

							$tool_args = $params['arguments'] ?? [];

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

		} catch (Exception $e) {

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