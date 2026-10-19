<?php

  $staged_files = preg_split('#(\r\n?|\n)#', shell_exec('git diff --cached --name-only --diff-filter=ACM 2>&1'), -1, PREG_SPLIT_NO_EMPTY);

  if (!count($staged_files)) exit(0);

	echo implode(PHP_EOL, [
		'-----------------------------------',
		'-- Lint Checker Pre-Commit Hook --',
		'-----------------------------------',
		''
	]);

	// Function to create a temporary file keeping the extension so tools detect the file type
	$temp_file = function(string $extension): string {
		return sys_get_temp_dir(). DIRECTORY_SEPARATOR . uniqid('lint_', true) .'.'. $extension;
	};

	// Function to run a command and return null on success or the combined output on failure
	$run = function(string $command): ?string {
		$output = [];
		exec($command .' 2>&1', $output, $result_code);
		return $result_code === 0 ? null : trim(implode(PHP_EOL, $output));
	};

	// Linters keyed by file extension

	$linters = [

		'php' => function(string $content) use ($temp_file, $run) {
			$tmp_file = $temp_file('php');
			file_put_contents($tmp_file, $content);
			$error = $run('php -l '. escapeshellarg($tmp_file));
			unlink($tmp_file);
			return $error;
		},

		'json' => function(string $content) {
			json_decode($content, true);
			return json_last_error() === JSON_ERROR_NONE ? null : 'JSON Error: '. json_last_error_msg();
		},

		'sql' => function(string $content) use ($temp_file, $run) {
			$tmp_file = $temp_file('sql');
			file_put_contents($tmp_file, $content);
			$error = $run('sqlfluff lint --dialect mysql '. escapeshellarg($tmp_file));
			unlink($tmp_file);
			return $error;
		},

		'js' => function(string $content) use ($temp_file, $run) {
			$tmp_file = $temp_file('js');
			file_put_contents($tmp_file, $content);
			$error = $run('node --check '. escapeshellarg($tmp_file));
			unlink($tmp_file);
			return $error;
		},

		'less' => function(string $content) use ($temp_file, $run) {
			$tmp_file = $temp_file('less');
			file_put_contents($tmp_file, $content);
			$error = $run('npx lessc --lint '. escapeshellarg($tmp_file));
			unlink($tmp_file);
			return $error;
		},

		'css' => function(string $content) use ($temp_file, $run) {
			$tmp_file = $temp_file('css');
			file_put_contents($tmp_file, $content);
			$error = $run('npx lessc --lint '. escapeshellarg($tmp_file));
			unlink($tmp_file);
			return $error;
		},
	];

	// Unset the linters whose command is unavailable, so those files are skipped instead of failing
	foreach ([
		'sql' => ['sqlfluff --version', 'Install it with: pip install sqlfluff'],
		'js' => ['node --version', 'Install Node.js.'],
		'less' => ['npx lessc --version', 'Install it with: npm install less --save-dev'],
		'css' => ['npx lessc --version', 'Install it with: npm install less --save-dev'],
	] as $extension => list($command, $hint)) {

		exec($command .' 2>&1', $output, $result_code);

		if ($result_code !== 0) {
			echo 'WARNING: '. $command .' could not be found, skipping '. $extension .' linting.'. PHP_EOL;
			echo '  '. $hint . PHP_EOL;
			unset($linters[$extension]);
		}
	}

	foreach ($staged_files as $file) {

		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!isset($linters[$extension])) continue;

    $content = shell_exec('git cat-file blob :'. escapeshellarg($file));
    if (!$content) continue;

    echo "$file... ";

		$exit_code = $linters[$extension]($content);

    echo ($exit_code ? '[error]' : '[ok]') . PHP_EOL;
	}
