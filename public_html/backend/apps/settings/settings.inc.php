<?php

	document::$title[] = t('title_settings', 'Settings');

	breadcrumbs::add(t('title_settings', 'Settings'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (isset($_POST['save'])) {

		try {

			foreach (array_keys($_POST['settings']) as $key) {

				$setting = database::query(
					"select * from ". DB_TABLE_PREFIX ."settings
					where `key` = '". database::input($key) ."'
					limit 1;"
				)->fetch();

				if (!$setting) {
					throw new Exception(t('error_setting_key_does_not_exist', 'The settings key does not exist'));
				}

				if (!empty($setting['required']) && empty($_POST['settings'][$key])) {
					throw new Exception(t('error_cannot_set_empty_value_for_setting', 'You cannot set an empty value for this setting'));
				}

				switch ($setting['datatype']) {

					case 'boolean':
					case 'bool':
						$value = !empty($_POST['settings'][$key]) ? '1' : '0';
						break;

					case 'csv':
						$value = implode(',', array_map(function($value){
							return preg_match('#", \R#', $value) ? '"' . str_replace('"', '""', $value) . '"' : $value;
						}, (array)($_POST['settings'][$key] ?? [])));
						break;

					case 'array':
						$value = json_encode((array)($_POST['settings'][$key] ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
						break;

					case 'json':
						$value = (string)($_POST['settings'][$key] ?? '');
						break;

					case 'number':
					case 'integer':
						$value = (int)($_POST['settings'][$key] ?? 0);
						break;

					case 'decimal':
					case 'float':
					case 'double':
						$value = (float)($_POST['settings'][$key] ?? 0);
						break;

					case 'string':
					default:
						$value = (string)($_POST['settings'][$key] ?? '');
						break;
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."settings
					set `value` = '". database::input($value) ."',
						updated_at = '". date('Y-m-d H:i:s') ."'
					where `key` = '". database::input($key) ."'
					limit 1;"
				);

				// Specific operations
				switch ($key) {
					case 'store_timezone':
						$file = 'storage://config.inc.php';
						$contents = file_get_contents($file);
						$contents = preg_replace('#ini_set\(\'date.timezone\'\, [^\)]+\);#', 'ini_set(\'date.timezone\', \''. addcslashes($value)  .'\');', $contents);
						file_put_contents($file, $contents);
						break;
				}
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(null, [], true, ['action']), 303);
			exit;

		} catch (Exception $e) {
			notices::add('success', $e->getMessage());
		}
	}

	$settings_group = database::query(
		"select * from ". DB_TABLE_PREFIX ."settings_groups
		where `key` = '". database::input(__DOC__) ."'
		order by priority, `key`
		limit 1;"
	)->fetch(function(&$group){

		// Decode JSON translations for title and description
		$group['name'] = !empty($group['name']) ? json_decode($group['name'], true) : [];
		$group['name'] = $group['name'][language::$selected['code']] ?? $group['name']['en'] ?? '';

		$group['description'] = !empty($group['description']) ? json_decode($group['description'], true) : [];
		$group['description'] = $group['description'][language::$selected['code']] ?? $group['description']['en'] ?? '';

	});

	if (!$settings_group) {
		notices::add('errors', 'Invalid setting group ('. __DOC__ .')');
		return;
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$settings = database::prepare(
		"select * from ". DB_TABLE_PREFIX ."settings
		where `group_key` = '". database::input($settings_group['key']) ."'
		order by priority, `key` asc;"
	)->fetch_page(function(&$setting){

		// Decode JSON translations for title and description
		$setting['title'] = !empty($setting['title']) ? json_decode($setting['title'], true) : [];
		$setting['title'] = $setting['title'][language::$selected['code']] ?? $setting['title']['en'] ?? '';

		$setting['description'] = !empty($setting['description']) ? json_decode($setting['description'], true) : [];
		$setting['description'] = $setting['description'][language::$selected['code']] ?? $setting['description']['en'] ?? '';

		// Set Display Value
		switch (true) {

			case (preg_match('#^password#', $setting['function'])):
				$setting['display_value'] = '****************';
				break;

			case (preg_match('#^order_status$#', $setting['function'])):
				$setting['display_value'] = $setting['value'] ? reference::order_status($setting['value'])->name : '';
				break;

			case (preg_match('#^page$#', $setting['function'])):
				$setting['display_value'] = $setting['value'] ? reference::page($setting['value'])->title : '';
				break;

			case (preg_match('#^regional_#', $setting['function'])):
				$setting['value'] = !empty($setting['value']) ? json_decode($setting['value'], true) : [];
				$setting['display_value'] = isset($setting['value'][language::$selected['code']]) ? $setting['value'][language::$selected['code']] : '';
				break;

			case (preg_match('#^toggle$#', $setting['function'])):
				if (in_array($setting['value'], ['1', 'active', 'enabled', 'on', 'true', 'yes'])) {
					$setting['display_value'] = t('title_true', 'True');
				} else if (in_array(($setting['value']), ['', '0', 'inactive', 'disabled', 'off', 'false', 'no'])) {
					$setting['display_value'] = t('title_false', 'False');
				}
				break;

			default:

				switch ($setting['datatype']) {

					case 'array':
					case 'json':
						$setting['display_value'] = json_encode($setting['value'], true);
						break;

					default:
						$setting['display_value'] = $setting['value'];
						break;
				}

				break;
		}

		// Set HTTP POST Value
		switch ($setting['datatype']) {

			case 'array':
				$_POST['settings'][$setting['key']] = (array)$setting['value'];
				break;

			case 'boolean':
			case 'bool':
				$_POST['settings'][$setting['key']] = !empty($setting['value']) ? '1' : '0';
				break;

			case 'csv':
				$_POST['settings'][$setting['key']] = str_getcsv($setting['value']);
				break;

			case 'decimal':
			case 'float':
			case 'double':
				$_POST['settings'][$setting['key']] = (float)$setting['value'];
				break;

			case 'json':
				$_POST['settings'][$setting['key']] = $setting['value'] ? json_decode($setting['value'], true) : [];
				break;

			case 'number':
			case 'integer':
				$_POST['settings'][$setting['key']] = (int)$setting['value'];
				break;

			case 'string':
				$_POST['settings'][$setting['key']] = (string)$setting['value'];
				break;

			default:
				$_POST['settings'][$setting['key']] = (string)$setting['value'];
				break;
		}
	}, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_settings', 'Settings'); ?> &ndash; <?php echo f::escape_html($settings_group['name']); ?>
		</div>
	</div>

	<?php echo f::form_begin('settings_form', 'post'); ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th style="width: 35%;"><?php echo t('title_key', 'Key'); ?></th>
					<th><?php echo t('title_value', 'Value'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($settings as $setting) { ?>
				<?php if (isset($_GET['action']) && $_GET['action'] == 'edit' && $_GET['key'] == $setting['key']) { ?>
				<tr>
					<td>
						<strong><?php echo $setting['title']; ?></strong><br>
						<?php echo $setting['description']; ?>
					</td>
					<td><?php echo f::form_function('settings['.$setting['key'].']', $setting['function'], true); ?></td>
					<td class="text-end">
						<?php echo f::form_button_predefined('save'); ?>
						<?php echo f::form_button_predefined('cancel'); ?>
					</td>
				</tr>
				<?php } else { ?>
				<tr>
					<td class="text-start"><a class="link" href="<?php echo document::href_ilink(null, ['action' => 'edit', 'key' => $setting['key']]); ?>" title="<?php echo t('title_edit', 'Edit'); ?>"><?php echo $setting['title']; ?></a></td>
					<td style="white-space: normal;">
						<div style="max-height: 200px; overflow-y: auto;" title="<?php echo f::escape_html($setting['description']); ?>">
							<?php echo nl2br($setting['display_value'], false); ?>
						</div>
					</td>
					<td class="text-end">
						<a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(null, ['action' => 'edit', 'key' => $setting['key']]); ?>" title="<?php echo t('title_edit', 'Edit'); ?>">
							<?php echo f::draw_fonticon('edit'); ?>
						</a>
					</td>
				</tr>
				<?php } ?>
				<?php } ?>
			</tbody>
		</table>

	<?php echo f::form_end(); ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?php echo f::draw_pagination($num_pages); ?>
	</div>
	<?php } ?>
</div>

<script>
	$(':input[name="settings[store_zone_code]"]:disabled').prop('disabled', false);
	$(':input[name="settings[default_zone_code]"]:disabled').prop('disabled', false);
</script>