<?php

	document::$title[] = t('title_import', 'Import');

	breadcrumbs::add(t('title_data_studio', 'Data Studio'));
	breadcrumbs::add(t('title_import', 'Import'));

	session::$data['csv_batch'] = [];

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

	if (!$_POST) {
		$_POST['format'] = 'csv';
		$_POST['column_titles'] = '1';
	}

	if (isset($_POST['load'])) {
		try {

			if (!isset($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {

				if (empty($_POST['url'])) {
					throw new Exception(t('error_must_select_file_to_upload', 'You must select a file to upload or provide a URL'));
				}

				// Download remote file
				$data = (function($url){
					$client = new http_client();
					$response = $client->call('GET', $url);
					if ($client->last_response['status_code'] != 200) {
						throw new Exception('Could not fetch remote file: ' . $url . ' (HTTP ' . $client->last_response['status_code'] . ')');
					}
					if (!$response) {
						throw new Exception('Remote file is empty: ' . $url);
					}
					return $response;
				})($_POST['url']);

				if ($data === false) {
					throw new Exception(t('error_failed_downloading_file', 'Failed downloading remote file'));
				}

			} else {

				if (!empty($_FILES['file']['error'])) {
					throw new Exception(t('error_uploaded_file_rejected', 'An uploaded file was rejected for unknown reason'));
				}

				if (!($data = file_get_contents($_FILES['file']['tmp_name']))) {
					throw new Exception(t('error_failed_reading_file_or_it_has_no_contents', 'Failed reading file or it has no contents'));
				}
			}

			if (empty($_POST['target'])) {
				throw new Exception(t('error_must_select_target', 'You must select a target'));
			}

			switch ($_POST['format']) {

				case 'csv':

					// Remove Byte Order Mark (BOM)
					$data = preg_replace('#^\xEF\xBB\xBF#', '', $data);

					if ($first_line = preg_match('#^(.*)(\R|$)#', $data, $matches)) {
						$first_line = $matches[1];
					} else {
						throw new Exception('Failed determining first line of CSV');
					}

					// Determine delimited
					if (empty($_POST['delimiter'])) {
						foreach ([',', ';', "\t", '|', chr(124)] as $char) {
							if (strpos($first_line, $char) !== false) {
								$_POST['delimiter'] = $char;
								break;
							}
						}

						if (empty($_POST['delimiter'])) {
							throw new Exception('Failed detecting CSV delimiter');
						}
					}

					// Append column titles
					if (empty($_POST['column_titles'])) {
						if (!preg_match('#^.*$#m', $data, $matches)) {
							throw new Exception('Failed parsing CSV data');
						}

						$num_columns = count(str_getcsv($matches[0], $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar']));

						$alphabetical_titles = array_slice(str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ', 1), 0, $num_columns);

						$data = implode($_POST['delimiter'], $alphabetical_titles) . PHP_EOL . $data;
					}

					if (!($data = f::csv_decode($data, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset']))) {
						throw new Exception(t('error_failed_decoding_csv', 'Failed decoding CSV'));
					}

					break;

				case 'json':
					if (!($data = json_decode($data, true))) {
						throw new Exception(t('error_failed_decoding_json', 'Failed decoding JSON'));
					}

					// Flatten any multidimensional arrays
					for ($i = 0; $i < count($data); $i++) {
						$data[$i] = f::array_flatten($data[$i]);
					}

					break;

				case 'yaml':
					if (!($data = yaml_parse($data))) {
						throw new Exception(t('error_failed_decoding_yaml', 'Failed decoding YAML'));
					}

					// Flatten any multidimensional arrays
					for ($i = 0; $i < count($data); $i++) {
						$data[$i] = f::array_flatten($data[$i]);
					}

					break;

				case 'xml':
					// Convert XML to JSON, then to array

					if (!($data = simplexml_load_string($data))) {
						throw new Exception(t('error_failed_decoding_xml', 'Failed decoding XML'));
					}

					if (!($data = f::format_json($data))) {
						throw new Exception(t('error_failed_encoding_json', 'Failed encoding JSON'));
					}

					if (!($data = json_decode($data, true))) {
						throw new Exception(t('error_failed_decoding_json', 'Failed decoding JSON'));
					}

					// Flatten any multidimensional arrays
					for ($i = 0; $i < count($data); $i++) {
						$data[$i] = f::array_flatten($data[$i]);
					}

					break;

				default:
					throw new Exception(t('error_invalid_format', 'Invalid format') . ' (' . $_POST['format'] . ')');
					break;
			}

			// Check for column consistency
			for ($i = 0; $i < count($data); $i++) {
				if (($found = count($data[$i])) != ($expected = count($data[0]))) {
					throw new Exception(
						strtr(t('error_mismatched_column_count', 'Found {found} properties in object number {nth_object} but expected {expected} consistently with those of the first object'), [
							'{found}' => $found,
							'{expected}' => $expected,
							'{nth_object}' => $i + 1,
						]),
					);
				}

				if (array_keys($data[$i]) != array_keys($data[0])) {
					throw new Exception(
						strtr(t('error_mismatched_column_names', 'The name of the properties in object number {nth_object} are inconsistent with those of the first object'), [
							'{nth_object}' => $i + 1,
						]),
					);
				}
			}

			switch (true) {

				case substr($_POST['target'], 0, 4) == 'ent:':
					$entity = basename(substr($_POST['target'], 4));

					// Check if entity exists
					if (!in_array($entity, array_column($collections, 'entity'))) {
						throw new Exception('Unknown entity (' . f::escape_html($class_name) . ')');
					}

					$target = [
						'type' => 'entity',
						'entity' => $entity,
					];

					$class_name = 'ent_' . $entity;
					$properties = array_keys(f::array_flatten((new $class_name())->data));

					break;

				case substr($_POST['target'], 0, 3) == 'db:':
					$table_name = substr($_POST['target'], 3);

					// Check if table exists
					if (
						!in_array(
							$table_name,
							$database_tables = database::query(
								"select table_name from information_schema.tables
							where table_schema = '" .
									DB_DATABASE .
									"'
							and table_type = 'BASE TABLE'
							and table_name not like '%.%'
							order by table_name asc;",
							)->fetch_all('table_name'),
						)
					) {
						throw new Exception('Unknown database table (' . f::escape_html($table_name) . ')');
					}

					$target = [
						'type' => 'database',
						'table' => $table_name,
					];

					$properties = [];

					database::query('show fields from `' . database::input($table_name) . '`;')->each(function ($field) use (&$properties) {
						$properties[] = $field['Field'];
					});

					break;

				default:
					throw new Exception('Unknown target (' . f::escape_html($_POST['target']) . ')');
			}

			// Create batch
			session::$data['csv_batch'] = [
				'rows' => $data,
				'total_lines' => count($data),
				'insert' => !empty($_POST['insert']),
				'overwrite' => !empty($_POST['overwrite']),
				'target' => $target,
				'properties' => $properties,
				'counters' => [
					'updated' => 0,
					'inserted' => 0,
					'line' => 0,
				],
			];

			redirect(document::ilink(__APP__ . '/confirm'), 303);
			exit;
		} catch (Exception $e) {
			unset(session::$data['csv_batch']);
			notices::add('errors', $e->getMessage());
		}
	}

	$target_options = [
		[
			'label' => t('title_entity_collection', 'Entity Collection'),
			'options' => array_combine(
				f::array_each($collections, fn($collection) => 'ent:' . $collection['entity']),
				array_column($collections, 'name')
			),
		],
		[
			'label' => t('title_database_table', 'Database Table'),
			'options' => array_combine(
				f::array_each($database_tables, fn($table) => 'db:' . $table),
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
?>
<style>
#formats .options {
	display: none;
	transition: all 200ms linear;
}

#formats:has(input[name="format"][value="csv"]:checked) #csv.options {
	display: block;
}

#formats:has(input[name="format"][value="json"]:checked) #json.options {
	display: block;
}

#formats:has(input[name="format"][value="yaml"]:checked) #yaml.options {
	display: block;
}

#formats:has(input[name="format"][value="xml"]:checked) #xml.options {
	display: block;
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

		<div class="card-body">
			<?php echo f::form_begin('import_form', 'post', null, true, 'style="max-width: 600px;"'); ?>

				<label class="form-group">
					<div class="form-label"><?php echo t('title_url', 'URL'); ?></div>
					<?php echo f::form_input_url('url', true, 'placeholder="https://www.example.tld/data.csv"'); ?>
				</label>

				<div class="divider" style="margin-top: -1.5em;">
					<span><?php echo t('title_or', 'or'); ?></span>
				</div>

				<div class="row">
					<label class="form-group col-sm-8">
						<div class="form-label"><?php echo t('title_file', 'File'); ?></div>
						<?php echo f::form_input_file('file', 'accept=".csv,.json,.yaml,.yml,.xml"'); ?>
					</label>

					<label class="form-group col-sm-4">
						<div class="form-label"><?php echo t('title_character_encoding', 'Character Encoding'); ?></div>
						<?php echo f::form_select_encoding('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
					</label>
				</div>

				<label class="form-group">
					<div class="form-label"><?php echo t('title_target', 'Target'); ?></div>
					<div>
						<?php echo f::form_select_optgroup('target', $target_options, true); ?>
					</div>
				</label>

				<div id="formats">

					<div class="form-group">
						<label><?php echo t('title_format', 'Format'); ?></label>
						<?php	echo f::form_toggle('format', $format_options, $_POST['format'] ?? 'csv'); ?>
					</div>

					<fieldset id="csv" class="options">
						<legend><?php echo t('title_format_options', 'Format Options'); ?></legend>

						<div class="grid">
							<label class="form-group col-6">
								<div class="form-label"><?php echo t('title_delimiter', 'Delimiter'); ?></div>
								<?php echo f::form_select('delimiter', ['' => '(' . t('title_auto_detect', 'Auto Detect') . ')', ',' => ',', ';' => ';', "\t" => 'TAB', '|' => '|'], true); ?>
							</label>

							<label class="form-group col-6">
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
							<div><?php echo f::form_radio_button('column_titles', ['1', t('text_first_row_contains_column_titles', 'The first row contains column titles')], true); ?></div>
							<div><?php echo f::form_radio_button('column_titles', ['0', t('text_file_has_no_column_titles', 'The file has no column titles')], true); ?></div>
						</div>
					</fieldset>

					<fieldset id="json" class="options">
						<legend><?php echo t('title_format_options', 'Format Options'); ?></legend>
						<div><em><?php echo t('text_no_options_for_selected_format', 'There are no options for this format.'); ?></em></div>
					</fieldset>

					<fieldset id="yaml" class="options">
						<legend><?php echo t('title_format_options', 'Format Options'); ?></legend>
						<div><em><?php echo t('text_no_options_for_selected_format', 'There are no options for this format.'); ?></em></div>
					</fieldset>

					<fieldset id="xml" class="options">
						<legend><?php echo t('title_format_options', 'Format Options'); ?></legend>
						<div><em><?php echo t('text_no_options_for_selected_format', 'There are no options for this format.'); ?></em></div>
					</fieldset>
				</div>

				<div class="text-center">
					<?php echo f::form_button('load', t('title_next', 'Next') . ' ' . f::draw_fonticon('icon-chevron-right'), 'submit', 'class="btn btn-default btn-lg btn-block"'); ?>
				</div>

			<?php echo f::form_end(); ?>
		</div>
	</div>
</div>

<script>
	$('input[name="file"]').on('change', function() {

		if (this.files.length) {

			switch (true) {

				case (this.files[0].name.match(/\.csv$/i) !== null):
					$('input[name="format"][value="csv"]').prop('checked', true).trigger('change');
					break;

				case (this.files[0].name.match(/\.json$/i) !== null):
					$('input[name="format"][value="json"]').prop('checked', true).trigger('change');
					break;

				case (this.files[0].name.match(/\.ya?ml$/i) !== null):
					$('input[name="format"][value="yaml"]').prop('checked', true).trigger('change');
					break;

				case (this.files[0].name.match(/\.xml$/i) !== null):
					$('input[name="format"][value="xml"]').prop('checked', true).trigger('change');
					break;
			}
		}

		if (this.files.length) {
			$('input[name="charset"]').prop('disabled', true);
			$('input[name="format"]').prop('disabled', false);
		} else {
			$('input[name="charset"]').prop('disabled', false);
			$('input[name="format"]').prop('disabled', true);
		}
	});
</script>