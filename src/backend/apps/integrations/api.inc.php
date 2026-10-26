<?php

	administrator::require_login();

	// Built from the URL object rather than ilink() because ilink() adds the admin language prefix
$api_base = (string)new type_url(WS_DIR_APP .'api');
	$key_placeholder = '<YOUR_ACCESS_KEY>';

	$curl_example = implode(PHP_EOL, [
		'curl -X GET '. $api_base .'/orders \\',
		'  -H "Authorization: Bearer '. $key_placeholder .'" \\',
		'  -H "Accept: application/json"',
	]);

	$endpoints = [
		['method' => 'GET', 'path' => '/orders', 'description' => t('text_api_orders_list', 'List all orders, newest first.')],
		['method' => 'GET', 'path' => '/orders/{id}', 'description' => t('text_api_order_single', 'Return a single order by id.')],
	];

?>
<div class="card">

	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_integrations', 'Integrations') ?>
		</div>
	</div>

	<div class="card-body">

		<h4><?= t('title_api_access', 'API Access') ?></h4>

		<p class="text-muted">
			<?= t('text_api_access_intro', 'The REST API authenticates with the same access keys used for MCP. There is no separate key type, so revoking a key here revokes API and MCP access together.') ?>
			<a href="<?= document::href_ilink(__APP__ .'/access_keys') ?>"><?= t('title_manage_access_keys', 'Manage access keys') ?></a>
		</p>

		<div class="notice notice-warning">
			<?= f::draw_fonticon('icon-exclamation-triangle', 'aria-hidden="true"') ?>
			<span><?= t('text_api_not_enabled', 'The REST API is not enabled on this installation yet. The documentation below describes the intended interface; no endpoint will respond until the API is switched on.') ?></span>
		</div>

		<br>

		<h4><?= t('title_api_authentication', 'Authentication') ?></h4>

		<p class="text-muted">
			<?= t('text_api_authentication', 'Send the key in a standard bearer Authorization header. Keys are stored hashed, are shown once at creation, and do not expire until you disable or delete them.') ?>
		</p>

		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label><?= t('title_api_base_url', 'Base URL') ?></label>
					<pre id="api_base_url" class="no-padding"><code><?= f::escape_html($api_base) ?></code></pre>
					<button type="button" class="btn btn-default btn-sm" data-copy-target="api_base_url">
						<?= f::draw_fonticon('icon-copy') ?> <?= t('title_copy', 'Copy') ?>
					</button>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label><?= t('title_api_header', 'Authorization header') ?></label>
					<pre id="api_auth_header" class="no-padding"><code><?= f::escape_html('Authorization: Bearer '. $key_placeholder) ?></code></pre>
					<button type="button" class="btn btn-default btn-sm" data-copy-target="api_auth_header">
						<?= f::draw_fonticon('icon-copy') ?> <?= t('title_copy', 'Copy') ?>
					</button>
				</div>
			</div>
		</div>

		<br>

		<h4><?= t('title_api_example', 'Example request') ?></h4>

		<pre id="api_curl_example" class="no-padding"><code><?= f::escape_html($curl_example) ?></code></pre>

		<button type="button" class="btn btn-default btn-sm" data-copy-target="api_curl_example">
			<?= f::draw_fonticon('icon-copy') ?> <?= t('title_copy', 'Copy') ?>
		</button>

		<br>
		<br>

		<h4><?= t('title_api_endpoints', 'Endpoints') ?></h4>

		<table class="table table-striped">
			<thead>
				<tr>
					<th><?= t('title_method', 'Method') ?></th>
					<th><?= t('title_path', 'Path') ?></th>
					<th><?= t('title_description', 'Description') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($endpoints as $endpoint) { ?>
				<tr>
					<td><code><?= f::escape_html($endpoint['method']) ?></code></td>
					<td><code><?= f::escape_html($endpoint['path']) ?></code></td>
					<td><?= f::escape_html($endpoint['description']) ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>

		<p class="text-muted">
			<?= t('text_api_spec_pending', 'This endpoint list is provisional and will be replaced once the API specification is finalised.') ?>
		</p>

	</div>
</div>