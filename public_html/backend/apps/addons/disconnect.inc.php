<?php

	if (!empty($_POST['disconnect'])) {
		try {

			database::query(
				"update ". DB_TABLE_PREFIX ."settings
				set `value` = ''
				where `key` = 'marketplace_access_token'
				limit 1;"
			);

			cache::clear_cache('marketplace');

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__ . '/marketplace'), 303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
			return;
		}
	}

?>
<div class="card card-default">
	<div class="card-header">
		<h2 class="card-title"><?php echo t('title_disconnect', 'Disconnect'); ?></h2>
	</div>

	<div class="card-body">
		<?php echo f::form_begin('disconnect_form', 'post'); ?>

			<label class="form-group">
				<div class="form-label"><?php echo t('text_are_you_sure', 'Are you sure?'); ?></div>
				<?php echo f::form_button('disconnect', t('title_disconnect', 'Disconnect'), 'submit', ['class' => 'btn btn-default']); ?>
			</label>

		<?php echo f::form_end(); ?>
	</div>
</div>
