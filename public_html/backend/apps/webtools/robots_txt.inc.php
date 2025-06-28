<?php

	$file = 'storage://robots.txt';

	if (!$_POST) {
		$_POST['content'] = file_get_contents($file);
	}

	if (!empty($_POST['save'])) {

		try {

			if (file_put_contents($file, $_POST['content']) === false) {
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
			<?php echo $app_icon; ?> <?php echo t('title_edit_robots_txt', 'Edit robots.txt'); ?>
		</div>
	</div>

	<div class="card-body">

		<?php echo functions::form_begin('file_form', 'post'); ?>

			<label class="form-group" style="max-width: 800px;">
				<div class="form-label"><?php echo t('title_file', 'File'); ?></div>
				<div class="form-input" readonly><?php echo preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $file); ?></div>
			</label>

			<label class="form-group">
				<div class="form-label"><?php echo t('title_content', 'Content'); ?></div>
				<?php echo functions::form_input_code('content', true); ?>
			</label>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>