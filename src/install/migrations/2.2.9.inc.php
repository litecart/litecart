<?php

	// Delete files
	$deleted_files = [
		FS_DIR_APP . 'admin/customers.app/mailchimp.png',
	];

	foreach ($deleted_files as $pattern) {
		if (!file_delete($pattern)) {
			echo '<span class="error">[Skipped]</span></p>';
		}
	}
