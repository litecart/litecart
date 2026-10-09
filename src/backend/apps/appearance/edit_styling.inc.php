<?php

	administrator::require_login();

	if (is_file($file = FS_DIR_APP . 'frontend/templates/'. settings::get('template') .'/.development')) {
		$developement = file_get_contents($file);
	} else {
		$development = false;
	}

	if ($development = 'advanced' && is_file(FS_DIR_APP . 'frontend/templates/'. settings::get('template') .'/scss/variables.scss')) {
		$stylesheet = FS_DIR_APP . 'frontend/templates/'. settings::get('template') .'/scss/variables.scss';
	} else if (is_file(FS_DIR_APP . 'frontend/templates/'. settings::get('template') .'/css/variables.css')) {
		$stylesheet = FS_DIR_APP . 'frontend/templates/'. settings::get('template') .'/css/variables.css';

	} else {
		notices::add('errors', t('error_template_missing_variables_stylesheet', 'This template does not have an editable stylesheet with variables (e.g. variables.css)'));
		return;
	}

	if (!$_POST) {
		$_POST['content'] = file_get_contents($stylesheet);
	}

	if (!empty($_POST['save'])) {

		try {

			if (file_put_contents($stylesheet, $_POST['content']) === false) {
				throw new Exception(t('error_unable_to_write_to_file', 'Unable to write to file'));
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_edit_styling', 'Edit Styling') ?>
		</div>
	</div>

	<div class="card-body">

		<?php if (preg_match('#\.scss$#', $stylesheet)) { ?>
		<div class="notices">
			<div class="notice notice-default"><?= f::draw_fonticon('icon-info') ?> <?= t('notice_detected_scss_version_of_variables', 'We detected a SCSS version present in this installation that will be used. A SCSS compiler is needed to compile the CSS versions (e.g. Developer Kit add-on).') ?></div>
		</div>
		<?php } ?>

		<?= f::form_begin('file_form', 'post') ?>

			<label class="form-group" style="max-width: 800px;">
				<div class="form-label"><?= t('title_file', 'File') ?></div>
				<div class="form-input" readonly><?= preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $stylesheet) ?></div>
			</label>

			<label class="form-group">
				<div class="form-label"><?= t('title_content', 'Content') ?></div>
				<?= f::form_input_code('content', true, ['style' => 'height: 600px;']) ?>
			</label>

			<div class="card-action">
				<?= f::form_button_predefined('save') ?>
				<?= f::form_button_predefined('cancel') ?>
			</div>

		<?= f::form_end() ?>
	</div>
</div>