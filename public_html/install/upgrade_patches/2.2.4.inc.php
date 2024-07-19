<?php

	perform_action('modify', [
		FS_DIR_APP . '.htaccess' => [
			[
				'search'  => "    SetEnv HTTP_MOD_REWRITE On",
				'replace' => "    SetEnv MOD_REWRITE On",
			],
		],
	]);
