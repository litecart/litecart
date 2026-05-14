<?php

	document::add_csp('img-src', 'https://www.litecart.net/');

	if (isset($_GET['addon_id'])) {
		$_GET['addon_id'] = basename($_GET['addon_id']);
	}

	try {

		if (empty($_GET['addon_id'])) {
			throw new Exception(t('error_must_provide_addon', 'You must provide an addon'));
		}

		$addon = new ent_addon($_GET['addon_id']);

		// Get user defined settings
		if ($json = @json_decode(file_get_contents('storage://addons/.settings'), true)) {
			$addon_settings = $json;
		} else {
			$addon_settings = [];
		}

		foreach ($addon->data['settings'] as $setting) {
			if (!isset($addon_settings[$addon->data['id']][$setting['key']])) {
				$addon_settings[$addon->data['id']][$setting['key']] = $setting['default_value'];
			}
		}

		if (empty($_POST) && !empty($addon_settings[$addon->data['id']])) {
			$_POST['settings'] = $addon_settings[$addon->data['id']];
		}

	} catch (Exception $e) {
		notices::add('errors', $e->getMessage());
		return;
	}

	if (isset($_POST['save'])) {
		try {

			if (empty($settings)) {
				$_POST['settings'] = [];
			}

			$settings[$addon->data['id']] = $_POST['settings'];

			file_put_contents('storage://addons/' . '.settings', f::format_json($settings), LOCK_EX);

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/installed'), 303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	breadcrumbs::add(t('title_installed', 'Installed'), document::ilink(__APP__.'/installed'));
	breadcrumbs::add($addon->data['name']);

	$vmod = vmod::parse_xml(null, $addon->data['location'] . 'vmod.xml');

?>
<style>
pre {
	background: #f9f9f9;
	border-radius: 4px;
	overflow: auto;
	max-width: 100%;
	max-height: 400px;
}

.operation {
	border: 1px solid #f3f3f3;
	border-radius: 4px;
	padding: 1em;
	margin-bottom: 1em;
}
</style>

<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_addon', 'Add-On'); ?>: <?php echo f::escape_html($addon->data['name']); ?>
		</div>
	</div>

	<div class="card-body">

		<?php if (!empty($addon->data['description'])) { ?>
		<p class="description"><?php echo $addon->data['description']; ?></p>
		<?php } ?>

		<?php if (!empty($addon->data['marketplace_addon_id'])) { ?>
		<div class="marketplace-addon-id" style="margin-bottom: 2em;"><?php echo t('title_marketplace_addon_id', 'Marketplace Add-On ID'); ?>: <?php echo f::escape_html($addon->data['marketplace_addon_id']); ?></div>
		<?php } ?>

		<?php if (!empty($addon->data['author'])) { ?>
		<div class="author" style="margin-bottom: 2em;"><?php echo t('title_developed_by', 'Developed By'); ?>: <?php echo f::escape_html($addon->data['author']); ?></div>
		<?php } ?>

		<?php echo f::form_begin('settings_form', 'post'); ?>

		<div class="grid">
			<div class="col-md-7">
				<h2><?php echo t('title_settings', 'Settings'); ?></h2>
				<table class="table table-striped">
					<thead>
						<tr>
							<th><?php echo t('title_setting', 'Setting'); ?></th>
							<th><?php echo t('title_value', 'Value'); ?></th>
						</tr>
					</thead>

					<tbody>
						<?php foreach ($addon->data['settings'] as $setting) { ?>
						<tr>
							<td style="width: 50%">
								<strong><?php echo $setting['title']; ?></strong>
								<?php if (!empty($setting['description'])) echo '<div>'. $setting['description'] .'</div>'; ?>
							</td>
							<td style="width: 50%">
								<?php if (!empty($setting['multiple'])) { ?>
								<?php echo f::form_function('settings['.$setting['key'].'][]', $setting['function'], true); ?>
								<?php } else { ?>
								<?php echo f::form_function('settings['.$setting['key'].']', $setting['function'], true); ?>
								<?php } ?>
							</td>
						</tr>
						<?php } ?>
					</tbody>

					<?php if (empty($addon->data['settings'])) { ?>
					<tfoot>
						<tr>
							<td colspan="99">
								<em><?php echo t('text_nothing_to_configure', 'Nothing to configure'); ?></em>
							</td>
						</tr>
					</tfoot>
					<?php } ?>
				</table>
			</div>

			<div class="col-md-5">
				<h2><?php echo t('title_vmod_health', 'vMod Health'); ?></h2>

				<table class="table table-striped table-hover data-table">
					<thead>
						<tr>
							<th class="main"><?php echo t('title_file', 'File'); ?></th>
							<th><?php echo t('title_result', 'Result'); ?></th>
						</tr>
					</thead>
					<tbody>
<?php foreach ($vmod['files'] as $file) {
	foreach (explode(',', $file['name']) as $pattern) {

		$path_and_file = $file['path'] . $pattern;

		if (!empty(vmod::$aliases)) {
			$path_and_file = preg_replace(array_keys(vmod::$aliases), array_values(vmod::$aliases), $path_and_file);
		}
?>
						<tr>
							<td>
								<h3><?php echo $path_and_file; ?></h3>
<?php
	$error = null;

	try {

		if (!is_file(FS_DIR_APP . $path_and_file)) {
			throw new Exception('File does not exist');
		}

		$buffer = file_get_contents(FS_DIR_APP . $path_and_file);

		foreach ($file['operations'] as $i => $operation) {

			echo "<div>Operation #$i ";

			if (!empty($operation['ignoreif']) && preg_match($operation['ignoreif'], $buffer)) {
				continue;
			}
			$found = preg_match_all($operation['find']['pattern'], $buffer, $matches, PREG_OFFSET_CAPTURE);

			if (!$found) {
				switch ($operation['onerror']) {
					case 'ignore':
						continue 2;
					case 'abort':
					case 'warning':
					default:
						throw new Exception('Search not found', E_USER_WARNING);
						continue 2;
				}
			}

			if (!empty($operation['find']['indexes'])) {
				rsort($operation['find']['indexes']);

				foreach ($operation['find']['indexes'] as $index) {
					$index = $index - 1; // [0] is the 1st in computer language

					if ($found > $index) {
						$buffer = substr_replace($buffer, preg_replace($operation['find']['pattern'], $operation['insert'], $matches[0][$index][0]), $matches[0][$index][1], strlen($matches[0][$index][0]));
					}
				}

			} else {
				$buffer = preg_replace($operation['find']['pattern'], $operation['insert'], $buffer, -1, $count);

				if (!$count && $operation['onerror'] != 'skip') {
					throw new Exception('Failed to perform insert');
					continue;
				}
			}

			echo f::draw_fonticon('true') .'</div>';
		}

	} catch (Exception $e) {
		echo f::draw_fonticon('remove') .' Error: '. f::escape_html($e->getMessage()) .'</div>';
		$error = true;
	}
?>
							</td>
							<td>
								<?php echo empty($error) ? f::draw_fonticon('true') : f::draw_fonticon('false'); ?>
							</td>
						</tr>
<?php
		}
	}
?>
					</tbody>

					<?php if (empty($vmod['files'])) { ?>
					<tfoot>
						<tr>
							<td colspan="99">
								<em><?php echo t('text_no_core_files_to_modify', 'No core files to modify'); ?><em></td>
						</tr>
					</tfoot>
					<?php } ?>
				</table>
			</div>
		</div>

			<div class="card-action">
				<?php echo f::form_button_predefined('save'); ?>
				<?php echo $addon->data['id'] ? f::form_button_predefined('delete') : ''; ?>
				<?php echo f::form_button_predefined('cancel'); ?>
			</div>

		<?php echo f::form_end(); ?>
	</div>
</div>