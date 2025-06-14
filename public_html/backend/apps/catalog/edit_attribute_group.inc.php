<?php

	if (!empty($_GET['group_id'])) {
		$attribute_group = new ent_attribute_group($_GET['group_id']);
	} else {
		$attribute_group = new ent_attribute_group();
	}

	if (!$_POST) {
		$_POST = $attribute_group->data;
	}

	document::$title[] = !empty($attribute_group->data['id']) ? language::translate('title_edit_attribute_group', 'Edit Attribute Group') : language::translate('title_create_new_attribute_group', 'Create New Attribute Group');

	breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
	breadcrumbs::add(language::translate('title_attribute_groups', 'Attribute Groups'), document::ilink(__APP__.'/attribute_groups'));
	breadcrumbs::add(!empty($attribute_group->data['id']) ? language::translate('title_edit_attribute_group', 'Edit Attribute Group') : language::translate('title_create_new_attribute_group', 'Create New Attribute Group'), document::ilink());

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['values'])) {
				$_POST['values'] = [];
			}

			foreach ($_POST['values'] as $value) {
				foreach ($value['name'] as $name) {
					if (preg_match('#(["\',\[\]<>])#', $name, $matches)) {
						throw new Exception(strtr(language::translate('error_attribute_value_contains_forbidden_character', 'An attribute value contains a forbidden character (%char)'), ['%char' => $matches[1]]));
					}
				}
			}

			foreach ([
				'code',
				'sort',
				'name',
				'values',
			] as $field) {
				if (isset($_POST[$field])) {
					$attribute_group->data[$field] = $_POST[$field];
				}
			}

			$attribute_group->save();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/attribute_groups'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($attribute_group->data['id'])) {
				throw new Exception(language::translate('error_must_provide_attribute', 'You must provide an attribute'));
			}

			$attribute_group->delete();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/attribute_groups'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$sort_options = [
		'priority' => language::translate('title_list_order', 'List Order'),
		'alphabetical' => language::translate('title_alphabetical', 'Alphabetical'),
	];
?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($attribute_group->data['id']) ? language::translate('title_edit_attribute_group', 'Edit Attribute Group') : language::translate('title_create_new_attribute_group', 'Create New Attribute Group'); ?>
		</div>
	</div>

	<?php echo functions::form_begin('attribute_form', 'post', false, false, 'style="max-width: 720px;"'); ?>

	<div class="card-body">

		<div class="grid">
			<div class="col-md-6">
				<label class="form-group">
					<div class="form-label"><?php echo language::translate('title_code', 'Code'); ?></div>
					<?php echo functions::form_input_text('code', true); ?>
				</label>
			</div>

			<div class="col-md-6">
				<label class="form-group">
					<div class="form-label"><?php echo language::translate('title_sort_values', 'Sort Values'); ?></div>
					<?php echo functions::form_select('sort', $sort_options, true); ?>
				</label>
			</div>
		</div>

		<label class="form-group">
			<div class="form-label"><?php echo language::translate('title_name', 'Name'); ?></div>
			<?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_regional_text('name['. $language_code .']', $language_code, true); ?>
		</label>

		<h2><?php echo language::translate('title_values', 'Values'); ?></h2>

		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo language::translate('title_id', 'ID'); ?></th>
					<th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
					<th><?php echo language::translate('title_in_use', 'In Use'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php if (!empty($_POST['values'])) foreach ($_POST['values'] as $key => $group_value) { ?>
				<tr draggable="true">
					<td><?php echo $group_value['id']; ?><?php echo functions::form_input_hidden('values['. $key .'][id]', $group_value['id']); ?></td>
					<td><?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_regional_text( 'values['. $key .'][name]['. $language_code .']', $language_code, true); ?></td>
					<td class="text-center"><?php echo !empty($group_value['in_use']) ? language::translate('title_yes', 'Yes') : language::translate('title_no', 'No'); ?></td>
					<td class="grabbable"><?php echo functions::draw_fonticon('icon-arrows-vertical'); ?></td>
					<td class="text-end"><?php if (empty($group_value['in_use'])) echo '<a href="#" class="remove btn btn-default btn-sm" title="'. language::translate('title_remove', 'Remove') .'">'. functions::draw_fonticon('icon-times', 'style="color: #c33;"') .'</a>'; ?></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<a class="add btn btn-default btn-sm" href="#">
							<?php echo functions::draw_fonticon('icon-plus'); ?>
						</a>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-action">
			<?php echo functions::form_button_predefined('save'); ?>
			<?php if (!empty($attribute_group->data['id'])) echo functions::form_button_predefined('delete'); ?>
			<?php echo functions::form_button_predefined('cancel'); ?>
		</div>
	</div>

	<?php echo functions::form_end(); ?>
</div>

<script>
	$('form[name="attribute_form"]').on('click', '.add', function(e) {
		e.preventDefault();

		let __index__ = 0;
		while ($(':input[name^="values[new_'+__index__+']"]').length) __index__++;

<?php
		$name_fields = '';
		foreach (array_keys(language::$languages) as $language_code) $name_fields .= functions::form_regional_text('values[__index__][name]['. $language_code .']', $language_code, '', '');
?>
		let $output = $([
			'<tr>',
			'  <td><?php echo functions::escape_js(functions::form_input_hidden('values[__index__][id]', '')); ?></td>',
			'  <td><?php echo functions::escape_js($name_fields); ?></td>',
			'  <td class="text-center"><?php echo language::translate('title_no', 'No'); ?></td>',
			'  <td class="grabbable"><?php echo functions::escape_js(functions::draw_fonticon('icon-arrows-vertical')); ?></td>',
			'  <td class="text-end"><a class="remove btn btn-default btn-sm" href="#" title="<?php echo functions::escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('icon-times', 'style="color: #c33;"')); ?></a></td>',
			'</tr>'
		].join('\n')
			.replace('__index__', 'new_' + __index__)
		);

		$(this).closest('table').find('tbody').append($output);
	});

	$('form[name="attribute_form"]').on('click', '.remove', function(e) {
		e.preventDefault();
		$(this).closest('tr').remove();
	});
</script>