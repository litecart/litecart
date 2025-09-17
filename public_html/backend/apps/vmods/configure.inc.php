<?php

	$_GET['vmod'] = basename($_GET['vmod']);

	try {

		if (empty($_GET['vmod'])) {
			throw new Exception(t('error_must_provide_vmod', 'You must provide a vMod'));
		}

		$file = 'storage://vmods/' . basename($_GET['vmod']);

		if (!is_file($file)) {
			throw new Exception(t('error_file_could_not_be_found', 'The file could not be found'));
		}

	// Load XML

		if (!$xml = simplexml_load_file($file)) {
			throw new Exception(t('error_invalid_xml', 'Invalid XML'));
		}

		if ($xml->getName() != 'vmod') {
			throw new Exception(t('error_invalid_vmod', 'Invalid vMod'));
		}

		if (empty($xml->setting)) {
			throw new Exception(t('error_nothing_to_configure', 'Nothing to configure'));
		}

		if (!$vmods_settings = @json_decode(file_get_contents('storage://vmods/.settings'), true)) {
			$vmods_settings = [];
		}

		$id = pathinfo($file, PATHINFO_FILENAME);

	// Build Settings

		$settings = [];
		foreach ($xml->setting as $setting) {
			$settings[(string)$setting->key] = (string)$setting->default_value;
			if (isset($vmods_settings[$id][(string)$setting->key])) {
				$settings[(string)$setting->key] = $vmods_settings[$id][(string)$setting->key];
			}
		}

		if (!$_POST) {
			$_POST['settings'] = $settings;
		}

	} catch (Exception $e) {
		notices::add('errors', $e->getMessage());
		return;
	}

	if (isset($_POST['save'])) {

		try {

			$vmods_settings[$id] = $_POST['settings'];

			file_put_contents('storage://vmods/' . '.settings', functions::json_format($vmods_settings), LOCK_EX);

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/vmods'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	breadcrumbs::add(t('title_vmods', 'vMods'), document::ilink(__APP__.'/vmods'));
	breadcrumbs::add(basename($_GET['vmod']), document::ilink());
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

<div class="card">
	<div class="card-header">
		<div class="card-title"><?php echo $app_icon; ?> <?php echo t('title_configure_vmod', 'Configure vMod'); ?></div>
	</div>

	<div class="card-body">

		<h1><?php echo $xml->name; ?></h1>

		<?php echo functions::form_begin('settings_form', 'post', false, false, 'style="max-width: 960px;"'); ?>

			<table class="table">
				<tbody>
					<?php foreach ($xml->setting as $setting) { ?>
					<tr>
						<td style="width: 50%">
							<strong><?php echo $setting->title; ?></strong>
							<?php if (!empty($setting->description)) echo '<div>'. $setting->description .'</div>'; ?>
						</td>
						<td style="width: 50%">
							<?php echo functions::form_function($setting->function, 'settings['.$setting->key.']', true); ?>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php echo functions::form_button('cancel', t('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1)"', 'cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>