<?php

	try {

    if (empty($_GET['vmod_id'])) {
      throw new Exception(language::translate('error_must_provide_vmod', 'You must provide a vMod'));
    }

    if (!is_file($file = FS_DIR_STORAGE . 'vmods/' . basename($_GET['vmod_id']) . '.xml')) {
      if (!is_file($file = FS_DIR_STORAGE . 'vmods/' . basename($_GET['vmod_id']) . '.disabled')) {
        throw new Exception(language::translate('error_file_not_found', 'The file could not be found'));
      }
    }

  // Load XML

		if (!$xml = simplexml_load_file($file)) {
      throw new Exception(language::translate('error_invalid_xml', 'Invalid XML'));
    }

    if ($xml->getName() != 'vmod') {
      throw new Exception(language::translate('error_invalid_vmod', 'Invalid vMod'));
    }

		if (empty($xml->setting)) {
			throw new Exception(language::translate('error_nothing_to_configure', 'Nothing to configure'));
		}

		if (!$vmods_settings = @json_decode(file_get_contents(FS_DIR_STORAGE . 'vmods/' . '.settings'), true)) {
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

			file_put_contents(FS_DIR_STORAGE . 'vmods/' . '.settings', json_encode($vmods_settings, JSON_UNESCAPED_SLASHES), LOCK_EX);

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'vmods'], ['app']));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

  breadcrumbs::add(language::translate('title_vMods', 'vMods'), document::link(WS_DIR_ADMIN, ['doc' => 'vmods'], ['app']));
  breadcrumbs::add($xml->name);
  breadcrumbs::add(language::translate('title_configure', 'Configure'), document::link());
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
		<div class="card-title"><?php echo $app_icon; ?> <?php echo language::translate('title_configure_vmod', 'Configure vMod'); ?></div>
	</div>

	<div class="card-body">

		<h1><?php echo $xml->name; ?></h1>

		<?php echo functions::form_draw_form_begin('settings_form', 'post', false, false, 'style="max-width: 960px;"'); ?>

			<table class="table table-striped">
				<tbody>
					<?php foreach ($xml->setting as $setting) { ?>
					<tr>
						<td style="width: 50%">
							<strong><?php echo $setting->title; ?></strong>
							<?php echo !empty($setting->description) ? '<div>'. $setting->description .'</div>' : ''; ?>
						</td>
						<td style="width: 50%">
							<?php echo functions::form_draw_function($setting->function, 'settings['.$setting->key.']', true); ?>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>

      <div class="card-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1)"', 'cancel'); ?>
      </div>

		<?php echo functions::form_draw_form_end(); ?>
	</div>
</div>