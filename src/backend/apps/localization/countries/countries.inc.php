<?php

	document::$title[] = t('title_countries', 'Countries');

	breadcrumbs::add(t('title_localization', 'Localization'));
	breadcrumbs::add(t('title_countries', 'Countries'), document::ilink());

	if (isset($_POST['enable']) || isset($_POST['disable'])) {

		try {

			if (empty($_POST['countries'])) {
				throw new Exception(t('error_must_select_countries', 'You must select countries'));
			}

			foreach ($_POST['countries'] as $country_code) {

				if (!empty($_POST['disable']) && $country_code == settings::get('default_country_code')) {
					throw new Exception(t('error_cannot_disable_default_country', 'You cannot disable the default country'));
				}

				if (!empty($_POST['disable']) && $country_code == settings::get('store_country_code')) {
					throw new Exception(t('error_cannot_disable_store_country', 'You cannot disable the store country'));
				}

				$country = new ent_country($country_code);
				$country->data['status'] = !empty($_POST['enable']) ? 1 : 0;
				$country->save();
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Table Rows
	$countries = database::query(
		"select c.*, z.num_zones from ". DB_TABLE_PREFIX ."countries c
		left join (
			select country_code, count(*) as num_zones from ". DB_TABLE_PREFIX ."zones
			group by country_code
		) z on (z.country_code = c.iso_code_2)
		order by status desc, name asc;"
	)->fetch_all();

	// Number of Rows
	$num_rows = count($countries);
?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_countries', 'Countries'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo f::form_button_link(document::ilink(__APP__.'/countries/edit_country'), t('title_create_new_country', 'Create New Country'), '', 'create'); ?>
	</div>

	<div class="card-filter">
		<div class="expandable"><?php echo f::form_input_search('query', false, ['placeholder' => t('text_search_phrase_or_keyword', 'Search phrase or keyword')]); ?></div>
	</div>

	<?php echo f::form_begin('countries_form', 'post'); ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
					<th></th>
					<th><?php echo t('title_id', 'ID'); ?></th>
					<th class="main"><?php echo t('title_name', 'Name'); ?></th>
					<th>Numeric</th>
					<th>Alpha 2</th>
					<th>Alpha-3</th>
					<th><?php echo t('title_zones', 'Zones'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($countries as $country) { ?>
				<tr class="<?php echo empty($country['status']) ? 'semi-transparent' : ''; ?>">
					<td><?php echo f::form_checkbox('countries[]', $country['iso_code_2']); ?></td>
					<td><?php echo f::draw_fonticon($country['status'] ? 'on' : 'off'); ?></td>
					<td><?php echo $country['id']; ?></td>
					<td><a class="link" href="<?php echo document::href_ilink(__APP__.'/countries/edit_country', ['country_code' => $country['iso_code_2']]); ?>"><?php echo $country['name']; ?></a></td>
					<td class="text-center"><?php echo $country['iso_code_1']; ?></td>
					<td class="text-center"><?php echo $country['iso_code_2']; ?></td>
					<td class="text-center"><?php echo $country['iso_code_3']; ?></td>
					<td class="text-center"><?php echo $country['num_zones'] ?: '-'; ?></td>
					<td><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/countries/edit_country', ['country_code' => $country['iso_code_2']]); ?>" title="<?php echo t('title_edit', 'Edit'); ?>"><?php echo f::draw_fonticon('edit'); ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?php echo t('title_countries', 'Countries'); ?>: <?php echo f::format_number($num_rows); ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions">

				<legend>
					<?php echo t('text_with_selected', 'With selected'); ?>:
				</legend>

				<div class="btn-group">
					<?php echo f::form_button('enable', t('title_enable', 'Enable'), 'submit', '', 'on'); ?>
					<?php echo f::form_button('disable', t('title_disable', 'Disable'), 'submit', '', 'off'); ?>
				</div>

			</fieldset>
		</div>

	<?php echo f::form_end(); ?>
</div>

<script>
	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');

	$('input[name="query"]').on('input', function() {
		var query = $(this).val().toLowerCase();
		$('.data-table tbody tr').each(function() {
			$(this).toggle(!query || $(this).text().toLowerCase().indexOf(query) > -1);
		});
		$('.data-table tfoot td').text('<?php echo t('title_countries', 'Countries'); ?>: ' + $('.data-table tbody tr:visible').length);
	});
</script>