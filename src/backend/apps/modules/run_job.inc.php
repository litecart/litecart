<?php

	administrator::require_login();

	document::$title[] = t('title_run_job', 'Run Job');

	breadcrumbs::add(t('title_modules', 'Modules'), document::ilink(__APP__.'/modules'));
	breadcrumbs::add(t('title_job_modules', 'Job Modules'), document::ilink(__APP__.'/jobs'));

	@set_time_limit(300);

	$module_id = isset($_GET['module_id']) && is_string($_GET['module_id']) ? $_GET['module_id'] : '';

	if ($module_id) {
		breadcrumbs::add(t('title_run_job', 'Run Job') .' '. f::escape_html($module_id));
		$log = (new mod_jobs)->process($module_id, true);
	} else {
		breadcrumbs::add(t('title_run_all_jobs', 'Run All Jobs'));
		$log = (new mod_jobs)->process(null, true);
	}

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_run_job', 'Run Job') ?>
		</div>
	</div>

	<div class="card-body">
		<div class="form-code">
			<pre><?= f::escape_html($log) ?></pre>
		</div>
	</div>
</div>
