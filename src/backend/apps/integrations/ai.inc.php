<?php

	administrator::require_login();

	// Built from the URL object rather than ilink() because ilink() adds the admin language prefix
	$mcp_url = (string)new type_url(WS_DIR_APP . BACKEND_ALIAS .'/mcp');
	$key_placeholder = '<YOUR_ACCESS_KEY>';

	// Generic mcpServers format understood by Claude Desktop, Claude Code, Cursor, Codex and most clients
	$config_json = f::format_json([
		'mcpServers' => [
			'litecart' => [
				'type' => 'http',
				'url' => $mcp_url,
				'headers' => [
					'Authorization' => 'Bearer '. $key_placeholder,
				],
			],
		],
	]);

	// VS Code uses the servers key in mcp.json and understands the install url handler
	$vscode_json = f::format_json([
		'name' => 'LiteCart',
		'type' => 'http',
		'url' => $mcp_url,
		'headers' => [
			'Authorization' => 'Bearer '. $key_placeholder,
		],
	]);

	$vscode_install_link = 'vscode:mcp/install?'. urlencode($vscode_json);

	// Claude opens its add custom connector modal pre-filled with the name and url
	$claude_install_link = 'https://claude.ai/customize/connectors?'. http_build_query([
		'modal' => 'add-custom-connector',
		'connectorName' => 'LiteCart',
		'connectorUrl' => $mcp_url,
	]);

	// Codex keeps mcp servers as toml tables and reads the bearer token from an environment variable
	$codex_toml = '[mcp_servers.litecart]' . PHP_EOL
		. 'url = "'. $mcp_url .'"' . PHP_EOL
		. 'bearer_token_env_var = "LITECART_ACCESS_KEY"';

	$clients = [
		[
			'id' => 'vscode',
			'name' => t('title_client_vscode', 'Visual Studio Code'),
			'action' => 'install',
			'href' => $vscode_install_link,
			'config' => $vscode_json,
			'paths' => [
				'.vscode/mcp.json',
				'%APPDATA%\Code\User\mcp.json',
				'~/Library/Application Support/Code/User/mcp.json',
				'~/.config/Code/User/mcp.json',
			],
		],
		[
			'id' => 'claude_desktop',
			'name' => t('title_client_claude_desktop', 'Claude Desktop'),
			'action' => 'install',
			'href' => $claude_install_link,
			'config' => $config_json,
			'paths' => [
				'%APPDATA%\Claude\claude_desktop_config.json',
				'~/Library/Application Support/Claude/claude_desktop_config.json',
				'~/.config/Claude/claude_desktop_config.json',
			],
		],
		[
			'id' => 'claude_code',
			'name' => t('title_client_claude_code', 'Claude Code'),
			'action' => 'copy',
			'config' => $config_json,
			'paths' => [
				'~/.claude.json',
				'.mcp.json',
			],
		],
		[
			'id' => 'cursor',
			'name' => t('title_client_cursor', 'Cursor'),
			'action' => 'copy',
			'config' => $config_json,
			'paths' => [
				'~/.cursor/mcp.json',
				'.cursor/mcp.json',
			],
		],
		[
			'id' => 'codex',
			'name' => t('title_client_codex', 'Codex'),
			'action' => 'copy',
			'config' => $codex_toml,
			'paths' => [
				'~/.codex/config.toml',
				'.codex/config.toml',
			],
		],
		[
			'id' => 'gemini_cli',
			'name' => t('title_client_gemini_cli', 'Gemini CLI'),
			'action' => 'copy',
			'config' => $config_json,
			'paths' => [
				'~/.gemini/settings.json',
				'.gemini/settings.json',
			],
		],
	];

	// Tools the current administrator may call. Tool gating now lives on each
	// access key, so this page only lists what is installed.
	$toolsets = [];

	foreach (f::file_search('app://shared/mcp/mcp_*.inc.php') as $mcp_file) {

		$toolset = (function() use ($mcp_file) {
			return include $mcp_file;
		})();

		if (!is_array($toolset) || empty($toolset['tools'])) continue;

		$group = $toolset['name'] ?? basename($mcp_file, '.inc.php');

		if (!isset($toolsets[$group])) {
			$toolsets[$group] = [
				'name' => $group,
				'description' => $toolset['description'] ?? '',
				'tools' => [],
			];
		}

		foreach ($toolset['tools'] as $tool) {

			if (empty($tool['name'])) continue;

			$toolsets[$group]['tools'][] = [
				'name' => $tool['name'],
				'description' => $tool['description'] ?? '',
				'required' => $tool['inputSchema']['required'] ?? [],
				'permitted' => true,
			];
		}
	}

	$total_tools = 0;

	foreach ($toolsets as $toolset) {
		$total_tools += count($toolset['tools']);
	}

	// The server advertises no resources capability yet
	$resources = [];

?>
<div class="card">

	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_integrations', 'Integrations') ?>
		</div>
	</div>

	<div class="card-body">

	<div class="row">

		<div class="col-md-7">

			<h4><?= t('title_quick_install', 'Quick Install') ?></h4>

		<p class="text-muted">
			<?= t('text_ai_quick_install', 'Let your AI agents access your platform. Connect your favorite client to LiteCart MCP Server. See common configuration examples below.') ?>
		</p>

		<div class="accordion">
			<?php foreach ($clients as $client) { ?>
			<div class="accordion-item">
				<div class="accordion-header">
					<span class="accordion-title"><?= $client['name'] ?></span>
					<i class="accordion-icon icon icon-chevron-down" aria-hidden="true"></i>
				</div>
				<div class="accordion-content">
					<div class="accordion-inner">
						<div class="p-3">
							<?php if (!empty($client['paths'])) { ?>
							<div class="text-muted" style="font-size: .85em; margin-bottom: .5em;">
								<div><?= t('title_common_config_locations', 'Common Config Locations') ?>:</div>
								<?php foreach ($client['paths'] as $path) { ?>
								<div><code><?= f::escape_html($path) ?></code></div>
								<?php } ?>
							</div>
							<?php } ?>
							<pre id="mcp_config_<?= $client['id'] ?>" class="no-padding"><code><?= f::escape_html($client['config']) ?></code></pre>
							<div class="flex mt-2" style="align-items: center; justify-content: flex-end; gap: .5em;">
								<?php if ($client['action'] === 'install') { ?>
								<a class="btn btn-primary btn-sm" href="<?= f::escape_attr($client['href']) ?>">
									<?= f::draw_fonticon('icon-external-link') ?> <?= t('title_install', 'Install') ?>
								</a>
								<?php } ?>
								<button type="button" class="btn btn-default btn-sm" data-copy-target="mcp_config_<?= $client['id'] ?>">
									<?= f::draw_fonticon('icon-copy') ?> <?= t('title_copy_config', 'Copy config') ?>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>

		<br>

		<h4><?= t('title_other_clients', 'Other clients') ?></h4>

		<p class="text-muted">
			<?= t('text_ai_other_clients', 'Any MCP client that accepts a Streamable HTTP server with custom headers can be connected using the same configuration.') ?>
		</p>

		</div>

		<div class="col-md-5">

			<div class="flex" style="align-items: center; justify-content: space-between;">
				<h4 style="margin: 0;"><?= t('title_installed_mcp_tools', 'Installed MCP Tools') ?></h4>
				<span class="badge badge-default"><?= number_format($total_tools) ?> <?= t('title_tools', 'tools') ?></span>
			</div>

			<p class="text-muted">
				<?= t('text_ai_tool_scope_per_key', 'Tool access is configured per access key. Scope each key on the {link} page.', ['{link}' => '<a href="'. f::escape_attr(document::href_ilink(__APP__ .'/access_keys')) .'">'. t('title_access_keys', 'Access Keys') .'</a>']) ?>
			</p>

			<div style="max-height: 34rem; overflow-y: auto;">

				<div class="accordion">

					<?php foreach ($toolsets as $toolset) { ?>
					<div class="accordion-item">
						<div class="accordion-header">
							<span class="accordion-title"><?= f::escape_html($toolset['name']) ?></span>
							<span class="badge badge-default"><?= number_format(count($toolset['tools'])) ?></span>
							<i class="accordion-icon icon icon-chevron-down" aria-hidden="true"></i>
						</div>
						<div class="accordion-content">
							<div class="accordion-inner">
								<div class="p-3">
									<p class="text-muted"><?= f::escape_html($toolset['description']) ?></p>
									<?php foreach ($toolset['tools'] as $tool) { ?>
									<div style="border-left: 2px solid var(--default-border-color); padding-left: .75em; margin-bottom: .5em;">
										<code><?= f::escape_html($tool['name']) ?></code>
										<div style="font-size: .9em;"><?= f::escape_html($tool['description']) ?></div>
										<?php if (!empty($tool['required'])) { ?>
										<div style="font-size: .85em;">
											<?= t('title_required', 'Required') ?>:
											<?php foreach ($tool['required'] as $field) { ?><code><?= f::escape_html($field) ?></code><?php } ?>
										</div>
										<?php } ?>
									</div>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>

				<h4><?= t('title_installed_mcp_resources', 'Installed MCP Resources') ?></h4>

				<div class="accordion">
					<div class="accordion-item">
						<div class="accordion-header">
							<span class="accordion-title"><?= t('title_mcp_resources', 'MCP Resources') ?></span>
							<i class="accordion-icon icon icon-chevron-down" aria-hidden="true"></i>
						</div>
						<div class="accordion-content">
							<div class="accordion-inner">
								<div class="p-3">
									<?php if (empty($resources)) { ?>
									<p class="text-muted" style="margin-bottom: 0;">
										<?= t('text_mcp_resources_unsupported', 'This server does not advertise a resources capability yet, so no resources are exposed to clients.') ?>
									</p>
									<?php } else { ?>
									<?php foreach ($resources as $resource) { ?>
									<div style="border-left: 2px solid var(--default-border-color); padding-left: .75em; margin-bottom: .5em;">
										<code><?= f::escape_html($resource['name']) ?></code>
										<div style="font-size: .9em;"><?= f::escape_html($resource['description']) ?></div>
									</div>
									<?php } ?>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				</div>

				</div>

			</div>

		</div>

	</div>

	</div>
</div>

<script>
	// The async clipboard needs a secure context, execCommand covers plain http
	function copyText(text) {

		if (navigator.clipboard && window.isSecureContext) {
			return navigator.clipboard.writeText(text);
		}

		return new Promise((resolve, reject) => {
			const field = $('<textarea>').val(text)
				.css({position: 'fixed', top: '-9999px', left: 0, opacity: 0}).appendTo('body');
			field[0].select();
			const copied = document.execCommand('copy');
			field.remove();
			copied ? resolve() : reject();
		});
	}

	function addNotice(text) {

		// The server only renders the container when a notice is queued, so build it when missing
		let $container = $('.notices');
		if (!$container.length) {
			$container = $('<div class="notices notices-floating" role="status" aria-live="polite"></div>').prependTo('#content');
		}

		$container.append(
			$('<div class="notice notice-default"></div>')
				.append('<a href="#" class="close" data-dismiss="notice" aria-label="<?= f::escape_attr(t('title_close', 'Close')) ?>">&times;</a>')
				.append(' <?= f::draw_fonticon('icon-info', 'aria-hidden="true"') ?> ')
				.append($('<span></span>').text(text))
		);
	}

	$('[data-copy-target]').on('click', function() {

		const $button = $(this);
		const $target = $('#' + $button.data('copy-target'));

		if (!$target.length) return;

		copyText($target.text()).then(() => {
			const label = $button.html();
			$button.html('<i class="icon icon-check"></i> <?= f::escape_js(t('title_copied', 'Copied')) ?>');
			setTimeout(() => $button.html(label), 2000);
		}).catch((error) => {
			console.warn('Copy to clipboard failed', error);
			addNotice('<?= f::escape_js(t('text_copy_failed', 'The configuration could not be copied, copy it from the code block instead.')) ?>');
		});
	});
</script>