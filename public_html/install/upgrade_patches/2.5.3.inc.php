<?php

	// Delete some files
	perform_action('delete', [
		FS_DIR_APP . 'ext/jquery/jquery-3.6.2.min.js',
		FS_DIR_APP . 'ext/jquery/jquery-3.6.3.min.js',
		FS_DIR_APP . 'includes/library/lib_compression.inc.php',
	], 'skip');
