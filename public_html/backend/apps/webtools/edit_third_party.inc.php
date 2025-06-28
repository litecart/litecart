<?php

	if (!empty($_GET['third_party_id'])) {
		$third_party = new ent_third_party($_GET['third_party_id']);
	} else {
		$third_party = new ent_third_party();
	}

	if (!$_POST) {
		$_POST = $third_party->data;
	}

	breadcrumbs::add(!empty($third_party->data['id']) ? t('title_edit_third_party', 'Edit Third Party') : t('title_create_new_third_party', 'Create New Third Party'));

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['privacy_classes'])) {
				throw new Exception(t('error_must_provide_cookie_type', 'You must provide a cookie type'));
			}

			if (empty($_POST['name'])) {
				throw new Exception(t('error_must_provide_name', 'You must provide a name'));
			}

			foreach ([
				'status',
				'privacy_classes',
				'name',
				'description',
				'collected_data',
				'purposes',
				'country_code',
				'homepage',
				'cookie_policy_url',
				'privacy_policy_url',
				'opt_out_url',
				'do_not_sell_url',
			] as $field) {
				if (isset($_POST[$field])) {
					$third_party->data[$field] = $_POST[$field];
				}
			}

			$third_party->save();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/third_parties'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($third_party->data['id'])) {
				throw new Exception(t('error_must_provide_third_party', 'You must provide a third party'));
			}

			$third_party->delete();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::link(__APP__.'/third_parties'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$privacy_classes_options = [
		'necessary' => t('title_necessary', 'Necessary'),
		'functionality' => t('title_functionality', 'Functionality'),
		'personalization' => t('title_personalization', 'Personalization'),
		'security' => t('title_security', 'Security'),
		'measurement' => t('title_measurement', 'Measurement'),
		'marketing' => t('title_marketing', 'Marketing'),
	];
?>
<style>
.data-collected label {
	display: block;
}
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($third_party->data['id']) ? t('title_edit_third_party', 'Edit Third Party') : t('title_create_new_third_party', 'Create New Third Party'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('third_party_form', 'post', false, false, 'autocomplete="off" style="max-width: 720px;"'); ?>

			<div class="grid">
				<div class="col-md-6">
					<div class="form-group">
						<div class="form-label"><?php echo t('title_status', 'Status'); ?></div>
						<?php echo functions::form_toggle('status', 'e/d', true); ?>
					</div>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_name', 'Name'); ?></div>
						<?php echo functions::form_input_text('name', true, 'required'); ?>
					</label>
				</div>
			</div>

			<label class="form-group">
				<div class="form-label"><?php echo t('title_privacy_classes', 'Privacy Classes'); ?></div>
				<?php echo functions::form_select('privacy_classes[]', $privacy_classes_options, true); ?>
			</label>

			<nav class="tabs">
				<?php foreach (language::$languages as $language) { ?>
				<a class="tab-item<?php if ($language['code'] == language::$selected['code']) echo ' active'; ?>" data-toggle="tab" href="#<?php echo $language['code']; ?>"><?php echo $language['name']; ?></a>
				<?php } ?>
			</nav>

			<div class="tab-content">

				<?php foreach (array_keys(language::$languages) as $language_code) { ?>
				<div id="<?php echo $language_code; ?>" class="tab-pane<?php if ($language_code == language::$selected['code']) echo ' active'; ?>">

					<div class="form-group">
						<div class="form-label"><?php echo t('title_description', 'Description'); ?></div>
						<?php echo functions::form_regional_wysiwyg('description['. $language_code .']', $language_code, true); ?>
					</div>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_collected_data', 'Collected Data'); ?></div>
						<?php echo functions::form_regional_textarea('collected_data['. $language_code .']', $language_code, true); ?>
					</label>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_purposes', 'Purposes'); ?></div>
						<?php echo functions::form_regional_textarea('purposes['. $language_code .']', $language_code, true); ?>
					</label>

				</div>
				<?php } ?>
			</div>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_country_of_juristiction', 'Country of Juristiction'); ?></div>
						<?php echo functions::form_select_country('country_code', true); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_homepage', 'Homepage'); ?></div>
						<?php echo functions::form_input_url('homepage', true, 'placeholder="https://..."'); ?>
					</label>
				</div>
			</div>

			<label class="form-group">
				<div class="form-label"><?php echo t('title_third_policy', 'Cookie Policy'); ?></div>
				<?php echo functions::form_input_url('cookie_policy_url', true, 'placeholder="https://..."'); ?>
			</label>

			<label class="form-group">
				<div class="form-label"><?php echo t('title_privacy_policy', 'Privacy Policy'); ?></div>
				<?php echo functions::form_input_url('privacy_policy_url', true, 'placeholder="https://..."'); ?>
			</label>

			<label class="form-group">
				<div class="form-label"><?php echo t('title_opt_out', 'Opt Out'); ?></div>
				<?php echo functions::form_input_url('opt_out_url', true, 'placeholder="https://..."'); ?>
			</label>

			<label class="form-group">
				<div class="form-label"><?php echo t('title_do_not_sell', 'Do Not Sell'); ?></div>
				<?php echo functions::form_input_url('do_not_sell_url', true, 'placeholder="https://..."'); ?>
			</label>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php echo (!empty($third_party->data['id'])) ? functions::form_button_predefined('delete') : ''; ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>

		<?php if (!empty($third_party->data['id'])) { ?>
		<div class="form-code" style="min-height: unset;">
		<?php echo functions::escape_html(implode(PHP_EOL, [
			'<script type="application/x-privacy-script" data-privacy-class="..." data-third-party-id="'. $third_party->data['id'] .'">',
			'  ...',
			'</script>',
		])); ?>
		</div>

		<div class="form-code" style="min-height: unset;">
		<?php echo functions::escape_html(implode(PHP_EOL, [
			'<script type="application/x-privacy-content" data-privacy-class="functional|personalization|security|measurement|marketing" data-third-party-id="'. $third_party->data['id'] .'">',
			'<![CDATA[',
			'  <iframe>...</iframe>',
			']]>',
			'</script>',
		])); ?>
		</div>

		<div class="form-code" style="min-height: unset;">
		<?php echo functions::escape_html(implode(PHP_EOL, [
			'<div class="require-consent" data-privacy-class="functional|personalization|security|measurement|marketing" data-third-party-id="'. $third_party->data['id'] .'" data-content="&lt;iframe src=&quot;...&quot;&gt;&lt;/iframe&gt;"></div>',
		])); ?>
		</div>
		<?php } ?>
	</div>
</div>

<script>
	$(':input[name^="purpose"]').on('input', function(e){
		var language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
		$('.nav-tabs a[href="#'+language_code+'"]').css('opacity', $(this).val() ? 1 : .5);
	}).trigger('input');
</script>