<?php

	document::$title[] = t('title_connect', 'Connect');

	breadcrumbs::add(t('title_marketplace', 'Marketplace'), document::ilink(__APP__ . '/marketplace'));
	breadcrumbs::add(t('title_connect', 'Connect'), document::ilink());

	if (!empty($_POST['connect'])) {
		redirect(
			document::link('https://www.litecart.net/account/grant_access', [
				'store_name' => settings::get('store_name'),
				'store_url' => document::ilink('f:'),
				'redirect_url' => document::link(),
			]),
			303,
		);

		exit;
	}

	if (!empty($_GET['access_token'])) {
		try {

			settings::set('marketplace_access_token', $_GET['access_token']);

			$result = marketplace_client::whoami();

			if (empty($result['user']['username'])) {
				throw new Exception(t('error_failed_retrieving_username_from_remote_machine', 'Failed retrieving username from remote machine'));
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."settings
				set `value` = '".	database::input($_GET['access_token']) ."'
				where `key` = 'marketplace_access_token'
				limit 1;"
			);

			cache::clear_cache('marketplace');

			notices::add('success',	strtr(t('text_marketplace_user_successfully_connected', 'Marketplace user account "{username}" successfully connected'), [
				'{username}' => $result['user']['username'],
			]));

			redirect(document::ilink(__APP__ . '/marketplace'), 303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
			return;
		}
	}

?>
<div class="card card-app">
	<div class="card-body">
		<?php echo f::form_begin('connect_form', 'post'); ?>
			<?php echo f::form_input_hidden('access_token', true); ?>
			<?php echo f::form_button('connect', t('title_connect', 'Connect')); ?>
		<?php echo f::form_end(); ?>
	</div>
</div>