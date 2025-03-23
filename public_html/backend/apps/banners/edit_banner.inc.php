<?php

	if (!empty($_GET['banner_id'])) {
		$banner = new ent_banner($_GET['banner_id']);
	} else {
		$banner = new ent_banner();
	}

	if (!$_POST) {
		$_POST = $banner->data;
	}

	document::$title[] = !empty($banner->data['id']) ? language::translate('title_edit_banner', 'Edit Banner') : language::translate('title_create_new_banner', 'Create New Banner');

	breadcrumbs::add(language::translate('title_banners', 'Banners'), document::ilink(__APP__.'/banners'));
	breadcrumbs::add(!empty($banner->data['id']) ? language::translate('title_edit_banner', 'Edit Banner') : language::translate('title_create_new_banner', 'Create New Banner'), document::ilink());

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['name'])) {
				throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));
			}

			if (empty($banner->data['id'])) {

				if (empty($_POST['html']) && empty($_FILES['image'])) {
					throw new Exception(language::translate('error_must_upload_image_or_enter_html', 'You must upload an image or enter HTML'));
				}

				if (!empty($_POST['image']) && empty($_POST['link'])) {
					throw new Exception(language::translate('error_must_enter_link', 'You must enter a target link'));
				}
			}

			if (empty($_POST['languages'])) {
				$_POST['languages'] = [];
			}

			foreach ([
				'status',
				'name',
				'languages',
				'link',
				'html',
				'keywords',
				'date_valid_from',
				'date_valid_to',
			] as $field) {
				if (isset($_POST[$field])) {
					$banner->data[$field] = $_POST[$field];
				}
			}

			if (is_uploaded_file($_FILES['image']['tmp_name'])) {
				$banner->save_image($_FILES['image']['tmp_name']);
			}

			$banner->save();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(__APP__.'/banners'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			$banner->delete();

			notices::add('success', language::translate('success_changes_saved', 'Changes were successfully saved.'));
			header('Location: '. document::ilink(__APP__.'/banners'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

?>
<style>
table th {
	min-width: 250px;
}
table th:last-child {
	min-width: auto;
}
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($banner->data['id']) ? language::translate('title_edit_banner', 'Edit Banner') : language::translate('title_create_new_banner', 'Create New Banner'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('banner_form', 'post', '', true, 'style="max-width: 640px;"'); ?>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_status', 'Status'); ?></div>
						<?php echo functions::form_toggle('status', 'e/d', true); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_name', 'Name'); ?></div>
						<?php echo functions::form_input_text('name', true); ?>
					</label>
				</div>
			</div>

			<label class="form-group">
				<div class="form-label"><?php echo language::translate('title_languages', 'Languages'); ?> <em>(<?php echo language::translate('text_leave_blank_for_all', 'Leave blank for all'); ?>)</em></div>
				<div><?php echo functions::form_select_language('languages[]', true); ?></div>
			</label>

			<label class="form-group">
				<div class="form-label"><?php echo language::translate('title_image', 'Image'); ?></div>
				<?php echo functions::form_input_file('image', 'accept="image/*"'); ?>
				<?php if (!empty($banner->data['image'])) echo '<div>' . $banner->data['image'] .'</div>'; ?>
			</label>

			<label class="form-group">
				<div class="form-label"><?php echo language::translate('title_link', 'Link'); ?></div>
				<?php echo functions::form_input_url('link', true); ?>
			</label>

			<label class="form-group">
				<div class="form-label"><?php echo language::translate('title_html', 'HTML'); ?></div>
				<div class="form-input" style="padding: 0;">
					<?php echo functions::form_input_code('html', true, 'placeholder="'. functions::escape_attr('<a href="$target_url"><img class="responsive" src="$image_url"></a>') .'" style="height: 150px;"'); ?>
					<div style="padding: 0.5em; background: #efefef;">
						<?php echo language::translate('title_aliases', 'Aliases'); ?>: <em>$uid, $key, $language_code, $image_url, $target_url</em>
					</div>
				</div>
			</label>

			<label class="form-group">
				<div class="form-label"><?php echo language::translate('title_keywords', 'Keywords'); ?></div>
				<?php echo functions::form_input_tags('keywords', true); ?>
			</label>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_date_valid_from', 'Date Valid From'); ?></div>
						<?php echo functions::form_input_datetime('date_valid_from', true); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_date_valid_to', 'Date Valid To'); ?></div>
						<?php echo functions::form_input_datetime('date_valid_to', true); ?>
					</label>
				</div>
			</div>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php if (!empty($banner->data['id'])) echo functions::form_button_predefined('delete'); ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>

<script>
	$('.data-table').on('input', ':input[name^="keys"]', function() {

		let key = $(this).val();
		let $row = $(this).closest('tr');

		$(this).attr('name', $(this).attr('name').replace(/^keys\[([^\]]+)?\]/, 'keys['+ key +']'));

		$.each($row.find(':input[name^="values["]'), function(i, $field) {
			let matches = $(field).attr('name').match(/^values\[(.*?)\]\[(.*?)\]$/);
			$field.attr('name', 'values['+ matches[1] +']['+ key +']');
		});
	});

	let new_key_index = 0;
	while ($(':input[name^="keys['+new_key_index+']"]').length) new_key_index++;

	$('.data-table .add').on('click', function(e) {
		e.preventDefault();

		let $output = $([
			'<tr>',
			'  <td><?php echo functions::form_input_text('keys[new_key_index]', 'new_key_index', 'required pattern="[0-9A-Za-z_-]+" placeholder="keyname"'); ?></td>',
			<?php foreach (language::$languages as $language) { ?>
			'  <td><?php echo functions::form_input_text('values['. $language['code'] .'][new_key_index]', true); ?></td>',
			<?php } ?>
			'  <td><a class="btn btn-default btn-sm remove" href="#" title="<?php echo functions::escape_html(language::translate('title_remove', 'Remove')); ?>"><?php echo functions::draw_fonticon('icon-times', 'style="color: #cc3333;"'); ?></a></td>',
			'</tr>'
		].join('\n')
			.replace(/new_key_index/g, 'new_' + new_key_index++)
		);

		$('.data-table tbody').append(output);
	});

	$('.data-table').on('click', '.remove', function(e) {
		e.preventDefault();
		$(this).closest('tr').remove();
	});

	$('a.tracker-wrapper-help').on('click', function(e) {
		e.preventDefault();
		alert([
			"Encapsulates the code with a div wrapper and makes it trackable.",
			"",
			"When disabled use the following parameters on an element for tracking:",
			"id=\"banner-$uid\" class=\"banner ...\""
		].join('\n'));
	});
</script>