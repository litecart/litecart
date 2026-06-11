<?php

	### Download Latest Updates #########################################

	if (!empty($_REQUEST['skip_updates'])) {

		echo '<p>Checking for updates... ';

		require_once FS_DIR_APP . 'includes/clients/http_client.inc.php';
		$client = new http_client();

		$update_file = function($file) use ($client) {

			$response = $client->call('GET', 'https://raw.githubusercontent.com/litecart/litecart/'. PLATFORM_VERSION .'/public_html/'. $file);

			if ($client->last_response['status_code'] != 200) return false;

			if (!is_dir(dirname(FS_DIR_APP . $file))) {
				mkdir(dirname(FS_DIR_APP . $file), 0777, true);
			}

			file_put_contents(FS_DIR_APP . $file, $response);

			return true;
		};

		$calculate_md5 = function($file) {
			if (!is_file(FS_DIR_APP . $file)) return;
			$contents = preg_replace('#(\r\n?|\n)#', "\n", file_get_contents(FS_DIR_APP . $file));
			return md5($contents);
		};

		if ($update_file('install/checksums.md5')) {

			$files_updated = 0;
			foreach (file(FS_DIR_APP . 'install/checksums.md5', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
				list($checksum, $file) = explode("\t", $line);
				if ($calculate_md5($file) != $checksum) {
					if ($update_file($file)) $files_updated++;
				}
			}

			if (!empty($files_updated)) {
				echo 'Updated '. $files_updated .' file(s) <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
			}
		}
	}
