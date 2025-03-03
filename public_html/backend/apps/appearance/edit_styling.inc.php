<?php

	if (is_file($file = FS_DIR_APP . 'frontend/templates/'. settings::get('template') .'/.development')) {
		$developement = file_get_contents($file);
	} else {
		$development = false;
	}

	if ($development = 'advanced' && is_file(FS_DIR_APP . 'frontend/templates/'. settings::get('template') .'/less/variables.less')) {
		$stylesheet = FS_DIR_APP . 'frontend/templates/'. settings::get('template') .'/less/variables.less';
	} else if (is_file(FS_DIR_APP . 'frontend/templates/'. settings::get('template') .'/css/variables.css')) {
		$stylesheet = FS_DIR_APP . 'frontend/templates/'. settings::get('template') .'/css/variables.css';

	} else {
		notices::add('errors', language::translate('error_template_missing_variables_stylesheet', 'This template does not have an editable stylesheet with variables (e.g. variables.css)'));
		return;
	}

	if (!$_POST) {
		$_POST['content'] = file_get_contents($stylesheet);
	}

	if (!empty($_POST['save'])) {

		try {

			if (!file_put_contents($stylesheet, $_POST['content'])) {
				throw new Exception(language::translate('error_unable_to_write_to_file', 'Unable to write to file'));
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::link());
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo language::translate('title_edit_styling', 'Edit Styling'); ?>
		</div>
	</div>

	<div class="card-body">

		<?php if (preg_match('#\.less$#', $stylesheet)) { ?>
		<div class="notices">
			<div class="notice notice-default"><?php echo functions::draw_fonticon('icon-info'); ?> <?php echo language::translate('notice_detected_less_version_of_variables', 'We detected a LESS version present in this installation that will be used. A LESS compiler is needed to compile the CSS versions (e.g. Developer Kit add-on).'); ?></div>
		</div>
		<?php } ?>

		<?php echo functions::form_begin('file_form', 'post'); ?>

			<label class="form-group" style="max-width: 800px;">
				<div class="form-label"><?php echo language::translate('title_file', 'File'); ?></div>
				<div class="form-input" readonly><?php echo preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $stylesheet); ?></div>
			</label>

			<label class="form-group">
				<div class="form-label"><?php echo language::translate('title_content', 'Content'); ?></div>
				<?php echo functions::form_input_code('content', true); ?>
			</label>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>