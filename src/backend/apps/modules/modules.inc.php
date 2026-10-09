<?php

	administrator::require_login();

	extract(match(__DOC__) {
		'customer' => [
			'title' => t('title_customer_modules', 'Customer Modules'),
			'files' => f::file_search('app://shared/modules/customer/*.inc.php'),
			'mod_class' => new mod_customer(),
			'type' => 'customer',
			'edit_doc' => 'edit_customer',
		],
		'jobs' => [
			'title' => t('title_job_modules', 'Job Modules'),
			'files' => f::file_search('app://shared/modules/jobs/*.inc.php'),
			'mod_class' => new mod_jobs(),
			'type' => 'job',
			'edit_doc' => 'edit_job',
		],
		'order' => [
			'title' => t('title_order_modules', 'Order Modules'),
			'files' => f::file_search('app://shared/modules/order/*.inc.php'),
			'mod_class' => new mod_order(),
			'type' => 'order',
			'edit_doc' => 'edit_order',
		],
		'payment' => [
			'title' => t('title_payment_modules', 'Payment Modules'),
			'files' => f::file_search('app://shared/modules/payment/*.inc.php'),
			'mod_class' => new mod_payment(),
			'type' => 'payment',
			'edit_doc' => 'edit_payment',
		],
		'shipping' => [
			'title' => t('title_shipping_modules', 'Shipping Modules'),
			'files' => f::file_search('app://shared/modules/shipping/*.inc.php'),
			'mod_class' => new mod_shipping(),
			'type' => 'shipping',
			'edit_doc' => 'edit_shipping',
		],
		'translation' => [
			'title' => t('title_translation', 'Translation'),
			'files' => glob(FS_DIR_APP . 'shared/modules/translation/tm_*.inc.php'),
			'mod_class' => new mod_translation(),
			'type' => 'translation',
			'edit_doc' => 'edit_translation',
		],
		default => throw new Error('Unknown module type ('. __DOC__ .')'),
	});

	document::$title[] = $title;

	breadcrumbs::add(t('title_modules', 'Modules'));
	breadcrumbs::add($title, document::ilink());

	if (isset($_POST['enable']) || isset($_POST['disable'])) {

		try {

			if (empty($_POST['modules'])) {
				throw new Exception(t('error_must_select_modules', 'You must select modules'));
			}

			foreach ($_POST['modules'] as $module_id) {
				$module = new ent_module($module_id);
				$module->data['settings']['status'] = !empty($_POST['enable']) ? 1 : 0;
				$module->save();
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Installed Modules
	$installed_modules = database::query(
		"select module_id from ". DB_PREFIX ."modules
		where type = '". database::input($type) ."';"
	)->fetch_all('module_id');

	// Table Rows
	$modules = [];

	foreach ($files as $file) {
		$module_id = substr(basename($file), 0, -8);

		$installed = in_array($module_id, $installed_modules);
		$module = ($installed && isset($mod_class->modules[$module_id])) ? $mod_class->modules[$module_id] : new $module_id;

		$modules[] = [
			'id' => $module_id,
			'status' => $module->status,
			'name' => $module->name,
			'version' => $module->version,
			'priority' => $module->priority,
			'author' => $module->author,
			'website' => $module->website,
			'installed' => $installed,
		];
	}

	// Sort: installed before uninstalled, then enabled before disabled
	usort($modules, function($a, $b) {
		if ($a['installed'] !== $b['installed']) {
			return $b['installed'] <=> $a['installed'];
		}
		return $b['status'] <=> $a['status'];
	});

	// Number of Rows
	$num_rows = count($modules);
?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= $title ?>
		</div>
	</div>

	<?php if ($type == 'job') { ?>
	<div class="card-action">
		<button id="cron-example" class="btn btn-default" type="button" style="margin-inline-end: 1em;">
			<?= f::draw_fonticon('icon-info') ?> <?= t('title_cron_job', 'Cron Job') ?>
		</button>
	</div>
	<?php } ?>

	<?= f::form_begin('modules_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th></th>
					<th><?= t('title_id', 'ID') ?></th>
					<th class="main"><?= t('title_name', 'Name') ?></th>
					<th></th>
					<th><?= t('title_developer', 'Developer') ?></th>
					<th class="text-center"><?= t('title_priority', 'Priority') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($modules as $module) { ?>
				<?php if (!empty($module['installed'])) { ?>
				<tr class="<?= empty($module['status']) ? 'semi-transparent' : '' ?>">
					<td><?= f::form_checkbox('modules[]', $module['id']) ?></td>
					<td><?= f::draw_fonticon($module['status'] ? 'on' : 'off') ?></td>
					<td><?= $module['id'] ?></td>
					<td>
						<a class="link" href="<?= document::href_ilink(__APP__.'/edit_'.$type, ['module_id' => $module['id']]) ?>">
							<?= $module['name'] ?> / <?= $module['version'] ?>
						</a>
					</td>
					<?php if (__DOC__ == 'jobs' && !empty($module['status'])) { ?>
					<td class="text-center">
						<a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/run_job', ['module_id' => $module['id']]) ?>" target="_blank">
							<strong><?= t('title_run_now', 'Run Now') ?></strong>
						</a>
					</td>
					<?php } else { ?>
					<td class="text-center"></td>
					<?php } ?>
					<td><?= !empty($module['website']) ? '<a href="'. f::escape_attr($module['website']) .'" target="_blank">'. $module['author'] .'</a>' : $module['author'] ?></td>
					<td class="text-center"><?= $module['priority'] ?></td>
					<td class="text-end"><a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/'.$edit_doc, ['module_id' => $module['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>"><?= f::draw_fonticon('edit') ?></a></td>
				</tr>
				<?php } else { ?>
				<tr class="semi-transparent">
					<td></td>
					<td></td>
					<td><?= $module['id'] ?></td>
					<td>
						<a class="link" href="<?= document::href_ilink(__APP__.'/edit_'.$type, ['module_id' => $module['id']]) ?>">
							<?= $module['name'] ?> / <?= $module['version'] ?>
						</a>
					</td>
					<td class="text-center"></td>
					<td><?= !empty($module['website']) ? '<a href="'. f::escape_attr($module['website']) .'" target="_blank">'. $module['author'] .'</a>' : $module['author'] ?></td>
					<td class="text-center">-</td>
					<td class="text-end">
						<a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_'.$type, ['module_id' => $module['id']]) ?>">
							<?= f::draw_fonticon('add') ?> <?= t('title_install', 'Install') ?>
						</a>
					</td>
				</tr>
				<?php } ?>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_modules', 'Modules') ?>: <?= f::format_number($num_rows) ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions" disabled>

				<legend>
					<?= t('text_with_selected', 'With selected') ?>:
				</legend>

				<div class="btn-group">
					<?= f::form_button_predefined('enable') ?>
					<?= f::form_button_predefined('disable') ?>
				</div>

			</fieldset>
		</div>

	<?= f::form_end() ?>
</div>

<script>
	$('#cron-example').on('click', function() {
		prompt("<?= t('title_cron_job_configuration', 'Cron Job Configuration') ?>", "*/5 * * * * php <?= f::escape_js(FS_DIR_APP) ?>index.php push_jobs &>/dev/null");
	});

	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>