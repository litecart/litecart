<?php

	if (!empty($_GET['site_tag_id'])) {
		$site_tag = new ent_site_tag($_GET['site_tag_id']);
	} else {
		$site_tag = new ent_site_tag();
	}

	document::$title[] = !empty($site_tag->data['id']) ? t('title_edit_site_tag', 'Edit Site Tag') : t('title_create_new_site_tag', 'Create New Site Tag');

	breadcrumbs::add(t('title_webtools', 'Webtools'));
	breadcrumbs::add(t('title_site_tags', 'Site Tags'), document::ilink(__APP__.'/site_tags'));
	breadcrumbs::add(!empty($site_tag->data['id']) ? t('title_edit_site_tag', 'Edit Site Tag') : t('title_create_new_site_tag', 'Create New Site Tag'));

	if (!$_POST) {
		$_POST = $site_tag->data;
	}

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['content'])) {
				throw new Exception(t('error_must_provide_position', 'You must provide position'));
			}

			if (empty($_POST['status'])) {
				$_POST['status'] = 0;
			}

			if (empty($_POST['require_consent'])) {
				$_POST['require_consent'] = null;
			}

			foreach ([
				'status',
				'position',
				'name',
				'content',
				'require_consent',
				'priority',
			] as $field) {
				if (isset($_POST[$field])) {
					$site_tag->data[$field] = $_POST[$field];
				}
			}

			$site_tag->save();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/site_tags'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($site_tag->data['id'])) {
				throw new Exception(t('error_must_provide_site_tag', 'You must provide a site tag'));
			}

			$site_tag->delete();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/site_tags'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$privacy_classes = [
		'necessary' => t('title_necessary', 'Necessary'),
		'functionality' => t('title_functionality', 'Functionality'),
		'personalization' => t('title_personalization', 'Personalization'),
		'security' => t('title_security', 'Security'),
		'measurement' => t('title_measurement', 'Measurement'),
		'marketing' => t('title_marketing', 'Marketing'),
	];

	$position_options = [
		'head' => t('title_head', 'Head'),
		'body' => t('title_body', 'Body'),
	];

	$consent_options = database::query(
		"select * from ". DB_TABLE_PREFIX ."third_parties"
	)->fetch_all(function($third_party) use ($privacy_classes) {

		$options = [];

		foreach (preg_split('#\s*,\s*#', $third_party['privacy_classes'], -1, PREG_SPLIT_NO_EMPTY) as $class) {
			$options[$class.':'. $third_party['id']] = $privacy_classes[$class];
		}

		return [
			'label' => $third_party['name'],
			'options' => $options,
		];
	});

	array_unshift($consent_options, [
		'label' => t('title_no_consent', 'No Consent'),
		'options' => [
			'' => t('title_no_consent_required', 'No Consent Required'),
		],
	]);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($site_tag->data['id']) ? t('title_edit_site_tag', 'Edit Site Tag') : t('title_create_new_site_tag', 'Create New Site Tag'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('site_tag_form', 'post', false, false, 'autocomplete="off" style="max-width: 960px;"'); ?>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_status', 'Status'); ?></div>
						<?php echo functions::form_toggle('status', 'e/d', true); ?>
					</label>
				</div>

				<div class="col-sm-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_name', 'Name'); ?></div>
						<?php echo functions::form_input_text('name', true, 'required'); ?>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_position', 'Position'); ?></div>
						<?php echo functions::form_select('position', $position_options, true); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_priority', 'Priority'); ?></div>
						<?php echo functions::form_input_number('priority', true); ?>
					</label>
				</div>
			</div>

			<label class="form-group">
				<div class="form-label"><?php echo t('title_require_consent', 'Require Consent'); ?></div>
				<?php echo functions::form_select_optgroup('require_consent', $consent_options, true); ?>
			</label>

			<label class="form-group">
				<div class="form-label"><?php echo t('title_html_content', 'HTML Content'); ?></div>
				<?php echo functions::form_input_code('content', true, 'required style="height: 480px;"'); ?>
			</label>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php echo (!empty($site_tag->data['id'])) ? functions::form_button_predefined('delete') : ''; ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>
