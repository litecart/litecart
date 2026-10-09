<?php

	administrator::require_login();

	if (!empty($_POST['disconnect'])) {
		try {

			database::query(
				"update ". DB_PREFIX ."settings
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
		<h2 class="card-title"><?= t('title_disconnect', 'Disconnect') ?></h2>
	</div>

	<div class="card-body">
		<?= f::form_begin('disconnect_form', 'post') ?>

			<label class="form-group">
				<div class="form-label"><?= t('text_are_you_sure', 'Are you sure?') ?></div>
				<?= f::form_button('disconnect', t('title_disconnect', 'Disconnect'), 'submit', ['class' => 'btn btn-default']) ?>
			</label>

		<?= f::form_end() ?>
	</div>
</div>
