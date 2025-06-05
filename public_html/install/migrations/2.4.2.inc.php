<?php

	// Delete old files
	$deleted_files = [
		FS_DIR_APP . 'ext/featherlight/featherlight.min.js.map',
		FS_DIR_APP . 'ext/featherlight/featherlight.min.css.map',
		FS_DIR_APP . 'ext/trumbowwyg/trumbowyg.min.css.map',
	];

	foreach ($deleted_files as $pattern) {
		if (!file_delete($pattern)) {
			echo '<span class="error">[Skipped]</span></p>';
		}
	}
