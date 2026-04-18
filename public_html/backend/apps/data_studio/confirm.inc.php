<?php

document::$title[] = t('title_import_export_csv', 'Import/Export CSV');

breadcrumbs::add(t('title_database', 'Database'));
breadcrumbs::add(t('title_import_export_csv', 'Import/Export CSV'));

if (isset($_POST['import']) || isset($_GET['resume'])) {
	try {
		ini_set('memory_limit', -1);

		ob_clean();

		header('Content-Type: text/plain; charset=UTF-8');

		if (empty(session::$data['csv_batch'])) {
			throw new Exception('Missing batch to resume');
		}

		$batch = &session::$data['csv_batch'];

		if (isset($_POST['import'])) {
			if (empty($_POST['map']) || (is_array($_POST['map']) && !array_filter($_POST['map']))) {
				throw new Exception(t('error_no_columns_mapped', 'No columns mapped'));
			}

			if (empty($_POST['insert']) && empty($_POST['update'])) {
				throw new Exception(t('error_nothing_to_do', 'Nothing to do'));
			}

			if (empty($_POST['match_column'])) {
				throw new Exception(t('error_no_match_column_selected', 'No match column selected'));
			}

			session::$data['csv_batch']['time_start'] = microtime(true);
			session::$data['csv_batch']['map'] = array_filter($_POST['map']);
			session::$data['csv_batch']['truncate'] = !empty($_POST['truncate']);
			session::$data['csv_batch']['insert'] = !empty($_POST['insert']);
			session::$data['csv_batch']['update'] = !empty($_POST['update']) ? $_POST['update'] : false;
		}

		$progress = round((($batch['total_lines'] - count($batch['data'])) / $batch['total_lines']) * 100, 2, PHP_ROUND_HALF_DOWN);
		$time_elapsed = round(microtime(true) - $batch['time_start'], 2);

		if ($progress != 0) {
			$time_remaining = round(($time_elapsed / $progress) * 100, 2) - $time_elapsed;
		} else {
			$time_remaining = 99;
		}

		$memory_usage = round(memory_get_usage() / 1024 / 1024, 3);

		echo implode(PHP_EOL, [f::draw_progress_bar($progress, 15), 'Estimated Time Remaining: ' . $time_remaining . ' s', 'Memory Usage: ' . $memory_usage . ' MB', '', '']);

		$time_start = microtime(true);

		ignore_user_abort(true);

		echo implode(PHP_EOL, ['Processing batch...', '']);

		while ($row = array_shift($batch['data'])) {
			if (round(microtime(true) - $time_start) > 5) {
				array_unshift($batch['data'], $row);
				echo implode(PHP_EOL, ['', 'Resuming ' . number_format(count($batch['data']), 0, '', ' ') . ' remaining lines for processing...', '', '']);
				header('Refresh: 0; url=' . document::link(null, ['resume' => 'true']));
				exit;
			}

			if (connection_aborted()) {
				throw new Exception('Connection aborted');
			}

			$batch['counters']['line']++;

			switch ($batch['target']['type']) {
				// Process row for an entity
				case 'entity':
					$class_name = 'ent_' . $batch['target']['entity'];
					$entity = new $class_name();

					try {
						$entity->load($row[$batch['target']['identified_by']]);
						$entity_exists = true;
					} catch (Exception $e) {
						$entity_exists = false;
					}

					if (!$entity_exists && $batch['insert']) {
						foreach ($batch['map'] as $column => $to_column) {
							$entity->data($to_column, $row[$column]);
						}

						$entity->save();
					}

					if ($entity_exists && $batch['update']) {
						foreach ($batch['map'] as $column => $to_column) {
							$entity->data($to_column, $row[$column]);
						}

						$entity->save();
					}

					if ($entity_exists && $batch['update'] == 'append') {
						foreach ($batch['map'] as $column => $to_column) {
							if (empty($entity->data[$to_column])) {
								$entity->data($to_column, $row[$column]);
							}
						}

						$entity->save();
					}

				// Process row for a database table
				case 'database':

					$row_exists = database::query(
						"select `". database::input($batch['match_column']) ."`
						from `". database::input($batch['target']['table_name']) ."`
						where `". database::input($batch['match_column']) ."` = '". database::input($row[$batch['match_column']]) ."'
						limit 1;",
					)->num_rows();

					if (!$row_exists && $batch['insert']) {
						$values = [];
						foreach ($batch['map'] as $column => $to_column) {
							if (empty($to_column)) {
								continue;
							}
							$values[$to_column] = $row[$column];
						}

						database::query(
							'insert into `'. database::input($batch['target']['table_name']) ."`
							(`". implode("`, `", database::input(array_keys($values))) ."`)
							values ('". implode("', '", database::input($values)) ."');",
						);
					}

					if ($row_exists && $batch['update']) {
						$values = [];
						foreach ($batch['map'] as $column => $to_column) {
							if (empty($to_column)) {
								continue;
							}
							$values[$to_column] = $row[$column];
						}

						database::query(
							'update `'. database::input($batch['target']['table_name']) ."`
								set `". implode("` = '" . database::input($values) . "', `", array_keys($values)) ."'
								where `". database::input($batch['match_column']) ."` = '". database::input($row[$batch['match_column']]) ."'
								limit 1;",
						);
					}

					if ($row_exists && $batch['update'] == 'append') {
						$values = [];
						foreach ($batch['map'] as $column => $to_column) {
							if (empty($to_column)) {
								continue;
							}
							if (empty($row[$column])) {
								continue;
							}
							$values[$to_column] = $row[$column];
						}

						database::query(
							"update `". database::input($batch['target']['table_name']) ."`
							set `". implode("` = '" . database::input($values) . "', `", array_keys($values)) ."'
							where `". database::input($batch['match_column']) ."` = '". database::input($row[$batch['match_column']]) ."'
							limit 1;"
						);
					}

					break;
			}
		}

		unset(session::$data['csv_batch']);

		echo PHP_EOL . 'Completed!';

		notices::add('success', t('success_import_completed', 'Import completed'));

		header('Refresh: 5; url=' . document::ilink());
		exit;
	} catch (Exception $e) {
		notices::add('errors', $e->getMessage());
		echo 'Error: ' . $e->getMessage();
		header('Refresh: 5; url=' . document::ilink());
		exit;
	}
}

if (isset($_POST['abort'])) {
	unset(session::$data['csv_batch']);
	redirect(document::ilink(__APP__ . '/import'), 303);
	exit;
}

if (empty($_POST)) {
	foreach (array_keys(session::$data['csv_batch']['rows'][0]) as $column) {
		if (in_array($column, array_keys(session::$data['csv_batch']['properties']))) {
			$_POST['map'][$column] = $column;
		}
	}
}

$columns = array_merge(
	[
		'' => '-- ' . t('title_skip', 'Skip') . ' --',
	],
	array_combine(array_keys(session::$data['csv_batch']['properties']), session::$data['csv_batch']['properties']),
);
?>
<style>
table td {
	white-space: normal;
}
</style>
<div>
		<nav class="tabs">
		<span class="tab-item active">
			<?php echo t('title_import', 'Import'); ?>
		</span>
		<a class="tab-item" href="<?php echo document::href_ilink(__APP__ . '/export'); ?>">
			<?php echo t('title_export', 'Export'); ?>
		</a>
	</nav>

	<div class="card">
		<div class="card-header">
			<div class="card-title">
				<?php echo $app_icon; ?> <?php echo t('title_import_data', 'Import Data'); ?>
			</div>
		</div>

		<?php echo f::form_begin('import_form', 'post'); ?>

			<div class="card-body">

				<div class="row">

					<div class="col-md-8">

						<table class="table table-striped data-table">
							<thead>
								<tr>
									<th style="width: 33.33%;"><?php echo t('title_property', 'Property'); ?></th>
									<th style="width: 33.33%;" class="text-center">
										<div class="input-group">
											<button name="previous" class="btn btn-default btn-sm" type="button" ><?php echo f::draw_fonticon('icon-chevron-left'); ?></button>
											<span class="input-group-text" style="flex-grow: 1;">
												<span class="current"><?php echo 1; ?></span> /	<span class="total"><?php echo count(session::$data['csv_batch']['rows']); ?></span>
											</span>
											<button name="next" class="btn btn-default btn-sm" type="button"><?php echo f::draw_fonticon('icon-chevron-right'); ?></button>
										</div>
									</th>
									<th style="width: 33.33%;"><?php echo t('title_map_to_property', 'Map To Property'); ?></th>
								</tr>
							</thead>

							<tbody>
								<?php foreach (array_keys(session::$data['csv_batch']['rows'][0]) as $column) { ?>
								<tr>
									<th class="text-start"><?php echo $column; ?></th>
									<td class="text-start"><?php echo session::$data['csv_batch']['rows'][0][$column]; ?></td>
									<td><?php echo f::form_select('map[' . $column . ']', $columns, true); ?></td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>

					<div class="col-md-4" style="position: relative;">
						<div style="position: sticky; top: 0;">
							<h2><?php //echo t('title_connect_columns', 'Connect Columns');	?>Lorem ipsum</h2>

							<div class="form-group">
								<div>
									<label><?php echo f::form_checkbox('truncate', '1', true); ?> <?php echo t('text_truncate_table_before_inserting_data', 'Truncate table before inserting data'); ?> <?php echo f::draw_fonticon('icon-exclamation-triangle', 'style="color: red;"'); ?></label>
								</div>
							</div>

							<div class="form-group">
								<div>
									<label><?php echo f::form_checkbox('insert', '1', true); ?> <?php echo t('text_insert_new_rows', 'Insert new rows'); ?></label>
								</div>
							</div>

							<div class="form-group">
								<label>Identify existing rows by matching values for the following column:</label>
								<?php echo f::form_select('match_column', $columns, true); ?>
							</div>

							<div class="form-group">
								<div>
									<label><?php echo f::form_radio_button('update', ['', t('text_skip_existing_rows', 'Skip existing rows')], true); ?></label>
									<label><?php echo f::form_radio_button('update', ['overwrite', t('text_overwrite_existing_rows', 'Overwrite existing rows')], true); ?></label>
									<label><?php echo f::form_radio_button('update', ['append', t('text_append_missing_data_to_existing_rows', 'Append missing data to existing rows')], true); ?></label>
								</div>
							</div>

							<div class="form-group">
								<?php echo f::form_button('import', t('title_import_now', 'Import Now'), 'submit', 'class="btn btn-success"'); ?>
								<?php echo f::form_button('abort', t('title_abort', 'Abort'), 'submit', 'class="btn btn-danger"'); ?>
							</div>
						</div>
					</div>
				</div>
			</div>

		<?php echo f::form_end(); ?>
	</div>
</div>

<script>
	var data = <?php echo f::format_json(
 	array_map(function ($node) {
 		return array_values($node);
 	}, array_slice(session::$data['csv_batch']['rows'], 0, 100)),
 ); ?>;

	$('button[name="previous"]').on('click', function(e){
		//e.preventDefault();
		var current = parseInt($('span.current').text());
		if (current > 1) {
			$('span.current').text(current - 1);
			$('tbody tr').each(function(i){
				$(this).find('td:nth-child(2)').text(data[current - 2][i]);
			});
		}
	});

	$('button[name="next"]').on('click', function(e){
		//e.preventDefault();
		var current = parseInt($('span.current').text());
		if (current < data.length) {
			$('span.current').text(current + 1);
			$('tbody tr').each(function(i){
				$(this).find('td:nth-child(2)').text(data[current][i]);
			});
		}
	});

	$('input[name="truncate"]').on('change', function(e){
		if ($(this).prop('checked') == true) {
			if (!confirm("<?php echo t('text_are_you_sure', 'Are you sure?'); ?>")){
				$(this).prop('checked', false);
			}
		}
	});
</script>