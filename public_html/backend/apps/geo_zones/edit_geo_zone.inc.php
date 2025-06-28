<?php

	if (!empty($_GET['geo_zone_id'])) {
		$geo_zone = new ent_geo_zone($_GET['geo_zone_id']);
	} else {
		$geo_zone = new ent_geo_zone();
	}

	if (!$_POST) {
		$_POST = $geo_zone->data;
	}

	document::$title[] = !empty($geo_zone->data['id']) ? t('title_edit_geo_zone', 'Edit Geo Zone') : t('title_new_geo_zone', 'Create New Geo Zone');

	breadcrumbs::add(t('title_geo_zones', 'Geo Zones'), document::ilink(__APP__.'/geo_zones'));
	breadcrumbs::add(!empty($geo_zone->data['id']) ? t('title_edit_geo_zone', 'Edit Geo Zone') : t('title_new_geo_zone', 'Create New Geo Zone'), document::ilink());

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['zones'])) {
				$_POST['zones'] = [];
			}

			foreach ([
				'code',
				'name',
				'description',
				'zones',
			] as $field) {
				if (isset($_POST[$field])) {
					$geo_zone->data[$field] = $_POST[$field];
				}
			}

			$geo_zone->save();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/geo_zones'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {
			if (empty($geo_zone->data['id'])) throw new Exception(t('error_must_provide_geo_zone', 'You must provide a geo zone'));

			$geo_zone->delete();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/geo_zones'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}
?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($geo_zone->data['id']) ? t('title_edit_geo_zone', 'Edit Geo Zone') : t('title_new_geo_zone', 'Create New Geo Zone'); ?>
		</div>
	</div>

	<?php echo functions::form_begin('form_geo_zone', 'post'); ?>
		<div class="card-body">

			<div class="grid" style="max-width: 720px;">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_code', 'Code'); ?></div>
						<?php echo functions::form_input_text('code', true); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_name', 'Name'); ?></div>
						<?php echo functions::form_input_text('name', true); ?>
					</label>
				</div>

				<div class="col-md-12">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_description', 'Description'); ?></div>
						<?php echo functions::form_input_text('description', true); ?>
					</label>
				</div>
			</div>

			<h2><?php echo t('title_zones', 'Zones'); ?></h2>
		</div>

		<table id="zones" class="table data-table">
			<thead>
				<tr>
					<th><?php echo t('title_id', 'ID'); ?></th>
					<th><?php echo t('title_country', 'Country'); ?></th>
					<th><?php echo t('title_zone', 'Zone'); ?></th>
					<th><?php echo t('title_city', 'City'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php if (!empty($_POST['zones'])) foreach (array_keys($_POST['zones']) as $key) { ?>
				<tr>
					<td><?php echo functions::form_input_hidden('zones['. $key .'][id]', true); ?><?php echo $_POST['zones'][$key]['id']; ?></td>
					<td><?php echo functions::form_input_hidden('zones['. $key .'][country_code]', true); ?> <?php echo reference::country($_POST['zones'][$key]['country_code'])->name; ?></td>
					<td><?php echo functions::form_input_hidden('zones['. $key .'][zone_code]', true); ?> <?php echo !empty($_POST['zones'][$key]['zone_code']) ? reference::country($_POST['zones'][$key]['country_code'])->zones[$_POST['zones'][$key]['zone_code']]['name'] : '-- '.t('title_all_zones', 'All Zones') .' --'; ?></td>
					<td><?php echo functions::form_input_hidden('zones['. $key .'][city]', true); ?> <?php echo fallback($_POST['zones'][$key]['city'], '-- '.t('title_all_cities'), 'All Cities') .' --'; ?></td>
					<td class="text-end">
						<a class="remove btn btn-default btn-sm" href="#" title="<?php echo t('title_remove', 'Remove'); ?>">
							<?php echo functions::draw_fonticon('icon-times', 'style="color: #cc3333;"'); ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td><?php echo functions::form_input_hidden('new_zone[id]', ''); ?></td>
					<td><?php echo functions::form_select_country('new_zone[country_code]', ''); ?></td>
					<td><?php echo functions::form_select_zone('', 'new_zone[zone_code]', '', '', 'all'); ?></td>
					<td><?php echo functions::form_input_text('new_zone[city]', '', 'placeholder="-- '. t('text_all_cities', 'All cities') .' --"'); ?></td>
					<td><?php echo functions::form_button('add', ['', t('title_add', 'Add')], 'button'); ?></td>
				</tr>
			</tfoot>
		</table>

		<div class="card-action">
			<?php echo functions::form_button_predefined('save'); ?>
			<?php if (!empty($geo_zone->data['id'])) echo functions::form_button_predefined('delete'); ?>
			<?php echo functions::form_button_predefined('cancel'); ?>
		</div>

	<?php echo functions::form_end(); ?>
</div>

<script>
	$('select[name$="new_zone[zone_code]"][disabled]').each(function() {
		$(this).html('<option value="">-- <?php echo functions::escape_js(t('title_all_zones', 'All Zones')); ?> --</option>');
	});

	$('select[name="new_zone[country_code]"]').on('change', function() {
		let zone_field = $(this).closest('tr').find('select[name="new_zone[zone_code]"]');

		$.ajax({
			url: '<?php echo document::ilink('countries/zones.json'); ?>?country_code=' + $(this).val(),
			type: 'get',
			cache: true,
			async: true,
			dataType: 'json',
			success: function(data) {
				$(zone_field).html('');
				if (data) {
					$(zone_field).append('<option value="">-- <?php echo functions::escape_js(t('title_all_zones', 'All Zones')); ?> --</option>');
					$.each(data, function(i, zone) {
						$(zone_field).append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
					});
					$(zone_field).prop('disabled', false);
				} else {
					$(zone_field).append('<option value="">-- <?php echo functions::escape_js(t('title_all_zones', 'All Zones')); ?> --</option>');
					$(zone_field).prop('disabled', true);
				}
			}
		});
	});


	$('#zones').on('click', 'button[name="add"]', function(e) {
		e.preventDefault();

		if ($('select[name="new_zone[country_code]"]').val() == '') {
			alert('<?php echo functions::escape_js(t('error_must_select_country', 'You must select a country')); ?>');
			return;
		}

		let found = false;
		$.each($('form[name="form_geo_zone"] tbody tr'), function(i, current_row) {
			if (
				$(current_row).find(':input[name$="[country_code]"]').val() == $(':input[name="new_zone[country_code]"]').val()
				&& $(current_row).find(':input[name$="[zone_code]"]').val() == $(':input[name="new_zone[zone_code]"]').val()
				&& $(current_row).find(':input[name$="[city]"]').val() == $(':input[name="new_zone[city]"]').val()
			) {
				found = true;
				return;
			}
		});

		if (found) {
			alert('<?php echo functions::escape_js(t('error_zone_already_exists', 'This zone already exists in the list'), true); ?>');
			return;
		}

		let __index__ = 0;
		while ($(':input[name^="zones[new_'+__index__+']"]').length) __index__++;

		let zone_name = $('select[name="new_zone[zone_code]"] option:selected').text();
		let city_name = $('input[name="new_zone[city]"]').val();

		if (zone_name == '') {
			zone_name = '-- <?php echo t('title_all_zones', 'All Zones'); ?> --';
		}

		if (city_name == '') {
			city_name = '-- <?php echo t('title_all_cities', 'All Cities'); ?> --';
		}

		let $output = $([
			'<tr>',
			'  <td><?php echo functions::escape_js(functions::form_input_hidden('zones[__index__][id]', '')); ?></td>',
			'  <td><?php echo functions::escape_js(functions::form_input_hidden('zones[__index__][country_code]', '')); ?>' + $('select[name="new_zone[country_code]"] option:selected').text() + '</td>',
			'  <td><?php echo functions::escape_js(functions::form_input_hidden('zones[__index__][zone_code]', '')); ?>' + zone_name + '</td>',
			'  <td><?php echo functions::escape_js(functions::form_input_hidden('zones[__index__][city]', '')); ?>' + city_name + '</td>',
			'  <td class="text-end">',
			'		<a class="remove btn btn-default btn-sm" href="#" title="<?php echo functions::escape_js(t('title_remove', 'Remove'), true); ?>">',
			'			<?php echo functions::escape_js(functions::draw_fonticon('icon-times', 'style="color: #cc3333;"')); ?>',
			'		</a>',
			'	</td>',
			'</tr>'
		].join('\n')
			.replace('__index__', 'new_' + __index__)
		);

		$(':input[name$="[country_code]"]', $output).val($(':input[name="new_zone[country_code]"]').val());
		$(':input[name$="[zone_code]"]', $output).val($(':input[name="new_zone[zone_code]"]').val());
		$(':input[name$="[city]"]', $output).val($(':input[name="new_zone[city]"]').val());

		$('#zones tbody').append($output);

		if ($(':input[name="new_zone[city]"]').val() == '') {
			$(':input[name="new_zone[zone_code]"]').val('');
		}

		$(':input[name="new_zone[city]"]').val('');
	});

	$('#zones').on('click', '.remove', function(e) {
		e.preventDefault();
		$(this).closest('tr').remove();
	});
</script>