<?php

	document::$title[] = t('title_export_data', 'Export Data');

	breadcrumbs::add(t('title_export_data', 'Export Data'));

	$collections = include 'app://includes/collections.inc.php';
	$collections = array_filter($collections, function ($collection) {
		return !empty($collection['entity']);
	});

	$database_tables = database::query(
		"select table_name from information_schema.tables
			where table_schema = '". DB_DATABASE ."'
			and table_type = 'BASE TABLE'
			and table_name not like '%.%'
			order by table_name asc;",
	)->fetch_all('table_name');

	if (isset($_POST['export'])) {
		try {

			if (empty($_POST['format'])) {
				throw new Exception(t('error_must_select_format', 'You must select a format'));
			}

			if (empty($_POST['source'])) {
				throw new Exception(t('error_must_select_source', 'You must select a source'));
			}

			switch (true) {
				case substr($_POST['source'], 0, 10) == 'collection:':

					$collection = basename(substr($_POST['source'], 10));

					// Check if collection exists in collections
					if (!in_array($collection, array_column($collections, 'id'))) {
						throw new Exception(t('error_invalid_source', 'Invalid source'));
					}

					$i = array_search($collection, array_column($collections, 'id'));

					if ($i === false || !isset($collections[$i])) {
						throw new Exception(t('error_invalid_collection', 'Invalid collection'));
					}

					if (empty($collections[$i]['entity'])) {
						throw new Exception(t('error_collection_missing_entity', 'The selected collection is missing an entity definition and cannot be exported'));
					}

					$collection = $collections[$i];

					$class_name = 'ent_' . $collection['entity'];
					if (!class_exists($class_name)) {
						throw new Exception(t('error_entity_class_not_found', 'Entity class not found'));
					}

					$entity_obj = new $class_name();

					$data = database::query(
						"SELECT * FROM `". DB_TABLE_PREFIX . $collection['id'] . "`
						ORDER BY `id` ASC;"
					)->fetch_all();

					if (!$data) {
						$data = [array_fill_keys(array_keys($entity_obj->data), '')];
					}

					break;

				case substr($_POST['source'], 0, 9) == 'database:':
					$table = substr($_POST['source'], 9);

					if (!in_array($table, $database_tables)) {
						throw new Exception(t('error_invalid_source', 'Invalid source'));
					}

					$data = database::query(
						"SELECT * FROM `". DB_TABLE_PREFIX . $table . "`
						ORDER BY 1 ASC;"
					)->fetch_all();

					if (!$data) {
						// Get columns for the table
						$fields = database::query(
							"SHOW FIELDS FROM `". database::input($table) ."`;"
						)->fetch_all('Field');
						$data = [array_fill_keys(array_keys($fields), '')];
					}

					break;

				default:
					throw new Exception(t('error_invalid_source', 'Invalid source'));
					break;
			}

			ob_clean();

			switch ($_POST['format']) {

				case 'csv':

					if ($_POST['output'] == 'screen') {
						header('Content-Type: text/plain; charset=' . $_POST['charset']);
					} else {
						header('Content-Type: application/csv; charset=' . $_POST['charset']);
						header('Content-Disposition: attachment; filename='. $_POST['source'] .'.csv');
					}

					$eol = match($_POST['eol']) {
						'Linux' => "\r",
						'Mac' => "\n",
						'Win' => "\r\n",
						default => throw new Exception(t('error_invalid_eol_format', 'Invalid EOL format')),
					};

					echo f::csv_encode($data, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], $eol);
					exit;

				case 'json':

					if ($_POST['output'] == 'screen') {
						header('Content-Type: text/plain; charset=' . $_POST['charset']);
					} else {
						header('Content-Type: application/json; charset=' . $_POST['charset']);
						header('Content-Disposition: attachment; filename='. $_POST['source'] .'.json');
					}

					echo f::format_json($data);
					exit;

				case 'yaml':

					if (!extension_loaded('yaml')) {
						throw new Exception(t('error_yaml_extension_not_loaded', 'The YAML extension is not loaded. Please install the YAML extension to use this format.'));
					}

					if ($_POST['output'] == 'screen') {
						header('Content-Type: text/plain; charset=' . $_POST['charset']);
					} else {
						header('Content-Type: application/x-yaml; charset=' . $_POST['charset']);
						header('Content-Disposition: attachment; filename='. $_POST['source'] .'.yaml');
					}

					echo yaml_emit($data);
					exit;

				case 'xml':

					if ($_POST['output'] == 'screen') {
						header('Content-Type: text/plain; charset=' . $_POST['charset']);
					} else {
						header('Content-Type: application/xml; charset=' . $_POST['charset']);
						header('Content-Disposition: attachment; filename='. $_POST['source'] .'.xml');
					}

					$xml = new SimpleXMLElement('<customers/>');

					foreach ($data as $row) {
						$customer = $xml->addChild('customer');
						foreach ($row as $key => $value) {
							$customer->addChild($key, htmlspecialchars($value));
						}
					}

					echo $xml->asXML();
					exit;

				default:
					throw new Exception(t('error_invalid_format', 'Invalid format'));
					break;
			}

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$indentations = [
		"\t" => t('title_tabs', 'Tabs'),
		'  ' => t('title_two_spaces', 'Two Spaces'),
		'    ' => t('title_four_spaces', 'Four Spaces'),
	];

	$json_indentations = [
		'' => t('title_none', 'None'),
		"\t" => t('title_tabs', 'Tabs'),
		'  ' => t('title_two_spaces', 'Two Spaces'),
		'    ' => t('title_four_spaces', 'Four Spaces'),
	];

	$source_options = [
		[
			'label' => t('title_entity_collection', 'Entity Collection'),
			'options' => array_combine(
				f::array_each($collections, fn($collection) => 'collection:' . $collection['id']),
				array_column($collections, 'name')
			),
		],
		[
			'label' => t('title_database_table', 'Database Table'),
			'options' => array_combine(
				f::array_each($database_tables, fn($table) => 'database:' . $table),
				$database_tables
			),
		],
	];

	$format_options = [
		['csv', t('title_csv', 'CSV')],
		['json', t('title_json', 'JSON')],
		['yaml', t('title_yaml', 'YAML'), !extension_loaded('yaml') ? 'disabled title="' . f::escape_attr(t('error_missing_extension_yaml', 'The PHP extension YAML is not loaded. Please install it to use this format.')) . '"' : ''],
		['xml', t('title_xml', 'XML'), !extension_loaded('simplexml') ? 'disabled title="' . f::escape_attr(t('error_missing_extension_simplexml', 'The PHP extension SimpleXML is not loaded. Please install it to use this format.')) . '"' : ''],
	];

	$output_options = [
		'screen' => t('title_screen', 'Screen'),
		'file' => t('title_file', 'File'),
	];

?>
<style>
#formats .options {
	display: none;
	transition: all 200ms linear;
}

#formats:has(input[name="format"][value="csv"]:checked) #csv.options,
#formats:has(input[name="format"][value="json"]:checked) #json.options,
#formats:has(input[name="format"][value="yaml"]:checked) #yaml.options,
#formats:has(input[name="format"][value="xml"]:checked) #xml.options {
	display: block;
}
</style>

<div>

	<nav class="tabs">
		<a class="tab-item" href="<?php echo document::href_ilink(__APP__ . '/import'); ?>">
			<?php echo t('title_import', 'Import'); ?>
		</a>
		<span class="tab-item active">
			<?php echo t('title_export', 'Export'); ?>
		</span>
	</nav>

	<div class="card">
		<div class="card-header">
			<div class="card-title">
				<?php echo $app_icon; ?> <?php echo t('title_export_data', 'Export Data'); ?>
			</div>
		</div>

		<div class="card-body">

			<?php echo f::form_begin('export_form', 'post', null, false, ['style' => 'max-width: 600px;']); ?>

				<label class="form-group">
					<div class="form-label"><?php echo t('title_source', 'Source'); ?></div>
					<?php echo f::form_select_optgroup('source', $source_options, true); ?>
				</label>

				<div id="formats">

					<div class="form-group">
						<label><?php echo t('title_format', 'Format'); ?></label>
						<?php	echo f::form_toggle('format', $format_options, $_POST['format'] ?? 'csv'); ?>
					</div>

					<fieldset id="csv" class="options">
						<legend><?php echo t('title_format_options', 'Format Options'); ?></legend>

						<div class="grid">
							<label class="form-group col-sm-6">
								<div class="form-label"><?php echo t('title_delimiter', 'Delimiter'); ?></div>
								<?php echo f::form_select('delimiter', [',' => ', (' . t('text_default', 'default') . ')', ';' => ';', "\t" => 'TAB', '|' => '|'], true); ?>
							</label>

							<label class="form-group col-sm-6">
								<div class="form-label"><?php echo t('title_enclosure', 'Enclosure'); ?></div>
								<?php echo f::form_select('enclosure', ['"' => '" (' . t('text_default', 'default') . ')'], true); ?>
							</label>
						</div>

						<div class="grid">
							<label class="form-group col-sm-6">
								<div class="form-label"><?php echo t('title_escape_character', 'Escape Character'); ?></div>
								<?php echo f::form_select('escapechar', ['"' => '" (' . t('text_default', 'default') . ')', '\\' => '\\'], true); ?>
							</label>
						</div>

						<div class="form-group">
							<label></label>
							<div><?php echo f::form_radio_button('column_titles', ['1', t('text_first_row_contains_column_titles', 'The first row contains column titles')], true); ?></div>
							<div><?php echo f::form_radio_button('column_titles', ['0', t('text_file_has_no_column_titles', 'The file has no column titles')], true); ?></div>
						</div>

					</fieldset>

					<fieldset id="json" class="options">
						<legend><?php echo t('title_json_options', 'JSON Options'); ?></legend>

						<div class="grid">
							<label class="form-group col-sm-6">
								<div class="form-label"><?php echo t('title_indentation', 'Indentation'); ?></div>
								<?php echo f::form_select('indentation', $json_indentations, true); ?>
							</label>
						</div>
					</fieldset>

					<fieldset id="yaml" class="options">
						<legend><?php echo t('title_format_options', 'Format Options'); ?></legend>

						<div class="grid">
							<label class="form-group col-sm-6">
								<div class="form-label"><?php echo t('title_indentation', 'Indentation'); ?></div>
								<?php echo f::form_select('indentation', $indentations, true); ?>
							</label>
						</div>
					</fieldset>

					<fieldset id="xml" class="options">
						<legend><?php echo t('title_format_options', 'Format Options'); ?></legend>

						<div class="grid">
							<label class="form-group col-sm-6">
								<div class="form-label"><?php echo t('title_indentation', 'Indentation'); ?></div>
								<?php echo f::form_select('indentation', $indentations, true); ?>
							</label>
						</div>
					</fieldset>
				</div>

				<div class="grid">
					<label class="form-group col-sm-6">
						<div class="form-label"><?php echo t('title_end_of_line_character', 'End of Line Character'); ?></div>
						<?php echo f::form_select('eol', ['Linux' => 'Linux (LF)', 'Mac' => 'Mac (CR)', 'Win' => 'Windows (CRLF)'], true); ?>
					</label>

					<label class="form-group col-sm-6">
						<div class="form-label"><?php echo t('title_character_encoding', 'Character Encoding'); ?></div>
						<?php echo f::form_select_encoding('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
					</label>
				</div>

				<label class="form-group">
					<div class="form-label"><?php echo t('title_output', 'Output'); ?></div>
					<div>
						<?php echo f::form_select('output', $output_options, true); ?>
					</div>
				</label>

				<div class="text-center">
					<?php echo f::form_button('export', t('title_export', 'Export') . ' ' . f::draw_fonticon('icon-arrow-right'), 'submit', ['class' => 'btn btn-default btn-lg']); ?>
				</div>

			<?php echo f::form_end(); ?>

		</div>
	</div>
</div>

<script>
	<?php if (extension_loaded('yaml')) { ?>
	$('input[name="format"][value="yaml"]').closest('.form-group').removeAttr('disabled');
	<?php } else { ?>
	$('input[name="format"][value="yaml"]').on('click', function(e) {
		e.preventDefault();
		alert('<?php echo t('error_yaml_extension_not_loaded', 'The YAML extension is not loaded. Please install the YAML extension to use this format.'); ?>');
		return false;
	});
	<?php } ?>
</script>