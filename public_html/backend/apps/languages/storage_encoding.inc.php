<?php

	document::$title[] = t('title_storage_encoding', 'Storage Encoding');

	breadcrumbs::add(t('title_languages', 'Languages'), document::ilink(__APP__.'/languages'));
	breadcrumbs::add(t('title_storage_encoding', 'Storage Encoding'), document::ilink());

	// Get all tables in database
	$tables = database::query(
		"SELECT * FROM information_schema.TABLES
		WHERE TABLE_SCHEMA = '". DB_DATABASE ."'
		ORDER BY TABLE_NAME;"
	)->fetch_all();

	// Filter out platform tables defined with DB_TABLE_PREFIX
	$platform_tables = array_filter(array_column($tables, 'TABLE_NAME'), function($table){
		return preg_match('#^'.preg_quote(DB_TABLE_PREFIX, '#').'#', $table);
	});

	if (!$_POST) {
		$_POST['tables'] = $platform_tables;
	}

	if (isset($_POST['convert'])) {

		try {

			if (empty($_POST['tables'])) {
				throw new Exception(t('error_must_select_tables', 'You must select tables'));
			}

			if (empty($_POST['collation']) && empty($_POST['engine'])) {
				throw new Exception(t('error_must_select_action_to_perform', 'You must select an action to perform'));
			}

			if (!preg_match('#^[a-z0-9_]+$#', $_POST['collation'])) {
				throw new Exception(t('error_invalid_collation', 'Invalid collation'));
			}

			$table_names = array_column($tables, 'TABLE_NAME');

			foreach ($_POST['tables'] as $table) {
				if (!in_array($table, $table_names)) {
					throw new Exception(strtr(t('error_unknown_table_x', 'Unknown table (%table)'), ['%table' => $table]));
				}
			}

			// Start transaction in case we need to rollback
			database::query(
				"start transaction;"
			);

			// Collect foreign keys, then drop them
			$foreign_keys = [];

			foreach ($_POST['tables'] as $table) {
				$foreign_keys_query = database::query(
					"select
						`TABLE_NAME`,
						`CONSTRAINT_NAME`,
						`COLUMN_NAME`,
						`REFERENCED_TABLE_NAME`,
						`REFERENCED_COLUMN_NAME`
					from information_schema.KEY_COLUMN_USAGE
					where TABLE_SCHEMA = '". DB_DATABASE ."'
					and TABLE_NAME = '". database::input($table) ."'
					and REFERENCED_TABLE_NAME is not null;"
				);

				while ($foreign_key = database::fetch($foreign_keys_query)) {
					$foreign_keys[] = $foreign_key;

					database::query(
						"alter table `". DB_DATABASE ."`.`". database::input($table) ."`
						drop foreign key `". $foreign_key['CONSTRAINT_NAME'] ."`;"
					);
				}
			}


			if (!empty($_POST['set_database_default'])) {
				database::query(
					"alter database `". DB_DATABASE ."`
					default character set ". database::input(preg_replace('#^([^_]+).*$#', '$1', $_POST['collation'])) ."
					collate ". database::input($_POST['collation']) .";"
				);
			}

			if (!empty($_POST['collation'])) {
				foreach ($_POST['tables'] as $table) {
					database::query(
						"alter table `". DB_DATABASE ."`.`". $table ."`
						convert to character set ". database::input(preg_replace('#^([^_]+).*$#', '$1', $_POST['collation'])) ."
						collate ". database::input($_POST['collation']) .";"
					);
				}
			}

			if (!empty($_POST['engine'])) {
				foreach ($_POST['tables'] as $table) {
					database::query(
						"alter table `". DB_DATABASE ."`.`". database::input($table) ."`
						engine=". database::input($_POST['engine']) .";"
					);
				}
			}

			// Restore foreign keys
			foreach ($foreign_keys as $foreign_key) {
				database::query(
					"alter table `". DB_DATABASE ."`.`". database::input($foreign_key['TABLE_NAME']) ."`
					add constraint `". database::input($foreign_key['CONSTRAINT_NAME']) ."`
					foreign key (`". database::input($foreign_key['COLUMN_NAME']) ."`)
					references `". DB_DATABASE ."`.`". database::input($foreign_key['REFERENCED_TABLE_NAME']) ."` (`". database::input($foreign_key['REFERENCED_COLUMN_NAME']) ."`);"
				);
			}

			// Commit the transaction
			database::query(
				"commit;"
			);

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {

			// Rollback the transaction
			database::query(
				"rollback;"
			);

			notices::add('errors', $e->getMessage());
		}
	}

	// Number of Rows
	$num_rows = count($tables);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_storage_encoding', 'Storage Encoding'); ?>
		</div>
	</div>

	<?php echo functions::form_begin('mysql_collation_form', 'post'); ?>
		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo functions::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
					<th class="main"><?php echo t('title_table', 'Table'); ?></th>
					<th><?php echo t('title_collation', 'Collation'); ?></th>
					<th><?php echo t('title_engine', 'Engine'); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($tables as $table) { ?>
				<tr>
					<td><?php echo functions::form_checkbox('tables[]', $table['TABLE_NAME'], true); ?></td>
					<td><?php echo $table['TABLE_NAME']; ?></td>
					<td><?php echo $table['TABLE_COLLATION']; ?></td>
					<td><?php echo $table['ENGINE']; ?></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?php echo t('title_tables', 'Tables'); ?>: <?php echo language::number_format($num_rows); ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<div style="width: 640px;">
				<label class="form-group">
					<div class="form-label"><?php echo t('title_collation', 'Collation'); ?></div>
					<?php echo functions::form_select_mysql_collation('collation', true); ?>
				</label>

				<div class="form-group">
					<?php echo functions::form_checkbox('set_database_default', ['1', t('text_also_set_as_database_default', 'Also set as database default (when new tables are created)')], true); ?>
				</div>

				<label class="form-group">
					<div class="form-label"><?php echo t('title_engine', 'Engine'); ?></div>
					<?php echo functions::form_select_mysql_engine('engine', true); ?>
				</label>
			</div>

			<p><?php echo t('description_set_mysql_collation', 'This will recursively convert the charset and collation for all selected database tables and belonging columns.'); ?></p>

			<div class="btn-group">
				<?php echo functions::form_button('convert', t('title_convert', 'Convert'), 'submit'); ?>
			</div>
		</div>

	<?php echo functions::form_end(); ?>
</div>
