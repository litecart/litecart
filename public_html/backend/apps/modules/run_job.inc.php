<?php

	document::$title[] = t('title_run_job', 'Run Job');

	breadcrumbs::add(t('title_modules', 'Modules'), document::ilink(__APP__.'/modules'));
	breadcrumbs::add(t('title_job_modules', 'Job Modules'), document::ilink(__APP__.'/jobs'));

	if (empty($_GET['module_id'])) {
		breadcrumbs::add(t('title_run_job', 'Run Job') .' '. f::escape_html($_GET['module_id']));
	} else {
		breadcrumbs::add(t('title_run_all_jobs', 'Run All Jobs'));
	}

	@set_time_limit(300);

	$jobs = new mod_jobs();

	if (!empty($_GET['module_id'])) {
		$log = $jobs->process($_GET['module_id'], true);
	} else {
		$log = $jobs->process(null, true);
	}

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_run_job', 'Run Job'); ?>
		</div>
	</div>

	<div class="card-body">
		<div class="form-code">
			<pre><?php echo f::escape_html($log); ?></pre>
		</div>
	</div>
</div>

