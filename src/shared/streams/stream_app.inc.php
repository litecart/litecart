<?php

	class stream_app {

		private static $_directories_cache = [];
		private static $_files_cache = [];
		private static $_addon_files = null;

		private $_directory = [];
		private $_stream;
		public $context;

		public function dir_opendir(string $path, int $options): bool {

			$microtime = microtime(true);

			$path = self::_resolve_path($path);
			$relative_path = preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $path);

			$this->_directory = [];

			if (!isset(self::$_directories_cache[$path])) {

				// File System
				foreach (glob($path.'*', GLOB_NOSORT) as $file) { // glob() seems faster than opendir() for this operation

					$file = str_replace('\\', '/', $file) . (is_dir($file) ? '/' : '');
					$basename = basename($file) . (is_dir($file) ? '/' : '');

					$this->_directory[$basename] = $file;
				}

				// Virtual File System
				foreach (glob(FS_DIR_STORAGE .'addons/*/'.$relative_path.'*', GLOB_NOSORT) as $file) {

					$file = str_replace('\\', '/', $file) . (is_dir($file) ? '/' : '');
					$basename = basename($file) . (is_dir($file) ? '/' : '');

					$skip_pattern = '(.cache/|.disabled/|/vmod\.xml$)';
					if (preg_match('#^'. preg_quote(FS_DIR_STORAGE .'addons/', '#') .'[^/]+'. $skip_pattern .'#', $file)) continue;

					$this->_directory[$basename] = $file;
				}

				uasort($this->_directory, function($a, $b){

					if (is_dir($a) == is_dir($b)) {
						return (basename($a) < basename($b)) ? -1 : 1;
					}

					return is_dir($a) ? -1 : 1;
				});

				if (class_exists('stats')) {
					stats::$data['streamwrappers'] = microtime(true) - $microtime;
				}

				self::$_directories_cache[$path] = $this->_directory;

			} else {
				$this->_directory = self::$_directories_cache[$path];
			}

			return true;
		}

		public function dir_readdir(): string|false {

			$result = key($this->_directory);

			if (!isset($result)) {
				return false;
			}

			next($this->_directory);

			return $result;
		}

		public function dir_closedir(): bool {
			if (is_resource($this->_directory)) {
				closedir($this->_directory);
			}
			$this->_directory = null;
			return true;
		}

		public function dir_rewinddir(): bool {
			reset($this->_directory);
			return true;
		}

		public function mkdir(string $path, int $mode, int $options): bool {

			if (!getenv('SUPER_MODE')) {
				trigger_error('Creating an app:// directory is prohibited', E_USER_WARNING);
				return false;
			}

			return mkdir(self::_resolve_path($path), $mode);
		}

		public function rename(string $path_from, string $path_to): bool {

			if (!getenv('SUPER_MODE')) {
				trigger_error('Renaming an app:// resource is prohibited', E_USER_WARNING);
				return false;
			}

			return rename(self::_resolve_path($path_from), self::_resolve_path($path_to));
		}

		public function rmdir(string $path, int $options): bool {

			if (!getenv('SUPER_MODE')) {
				trigger_error('Removing an app:// directory is prohibited', E_USER_WARNING);
				return false;
			}

			return rmdir(self::_resolve_path($path));
		}

		public function stream_cast(int $cast_as): object {
			return $this->_stream;
		}

		public function stream_close(): void {
			fclose($this->_stream);
		}

		public function stream_eof(): bool {
			return feof($this->_stream);
		}

		public function stream_flush(): bool {

			if (!getenv('SUPER_MODE')) {
				trigger_error('Flushing data to an app:// resource is prohibited', E_USER_WARNING);
				return false;
			}

			return fflush($this->_stream);
		}

		public function stream_lock(int $operation): bool {

			if (!getenv('SUPER_MODE')) {
				trigger_error('Adding a file lock for an app:// resource is prohibited', E_USER_WARNING);
				return false;
			}

			return flock($this->_stream, $operation);
		}

		public function stream_metadata(string $path, int $option, mixed $value): bool {

			if (!getenv('SUPER_MODE')) {
				trigger_error('Changing metadata for an app:// resource is prohibited', E_USER_WARNING);
				return false;
			}

			$path = self::_resolve_path($path);

			switch ($option) {

				case STREAM_META_TOUCH:
					$currentTime = \time();
					return touch($path, (is_array($value) && array_key_exists(0, $value)) ? $value[0] : $currentTime, (is_array($value) && array_key_exists(1, $value)) ? $value[1] : $currentTime);

				case STREAM_META_OWNER_NAME:
					return chown($path, (string)$value);

				case STREAM_META_OWNER:
					return chown($path, (int)$value);

				case STREAM_META_GROUP_NAME:
					return chgrp($path, (string)$value);

				case STREAM_META_GROUP:
					return chgrp($path, (int)$value);

				case STREAM_META_ACCESS:
					return chmod($path, $value);

				default:
					return false;
			}
		}

		public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool {

			$path = self::_resolve_file($path);

			if (!getenv('SUPER_MODE')) {
				$mode = 'r'; // Force read-only
			}

			$this->_stream = fopen($path, $mode, $options, $opened_path);

			return (bool)$this->_stream;
		}

		public function stream_read(int $count): string {
			return fread($this->_stream, $count);
		}

		public function stream_seek(int $offset, int $whence = SEEK_SET): bool {
			return fseek($this->_stream, $offset, $whence);
		}

		public function stream_set_option(int $option, int $arg1, int $arg2): bool {
			return false;
		}

		public function stream_stat(): array|false {
			return fstat($this->_stream);
		}

		public function stream_tell(): int {
			return ftell($this->_stream);
		}

		public function stream_truncate(int $new_size): bool {

			if (!getenv('SUPER_MODE')) {
				trigger_error('Truncating an app:// resource is prohibited', E_USER_WARNING);
				return false;
			}

			return ftruncate($this->_stream, $new_size);
		}

		public function stream_write(string $data): int|bool {

			if (!getenv('SUPER_MODE')) {
				trigger_error('Writing to an app:// resource is prohibited', E_USER_WARNING);
				return 0;
			}

			return fwrite($this->_stream, $data);
		}

		public function unlink(string $path): bool {

			if (!getenv('SUPER_MODE')) {
				trigger_error('Removing an app:// resource is prohibited', E_USER_WARNING);
				return false;
			}

			return unlink(self::_resolve_path($path));
		}

		public function url_stat(string $path, int $flags): array|false {

			$path = self::_resolve_file($path);

			if (!file_exists($path)) {
				return false;
			}

			return stat($path);
		}

		## Non-Standard StreamWrapper Methods

		// Resolve an app:// path to a real file path on disk. Including the resolved path
		// instead of the app:// path keeps the OPcache working, as it does not cache files
		// loaded through a userland stream wrapper.
		public static function resolve(string $path): string {

			if (!str_starts_with($path, 'app://')) {
				return $path;
			}

			return self::_resolve_file($path);
		}

		private static function _resolve_path(string $path): string {
			return preg_replace('#^app://#', FS_DIR_APP, str_replace('\\', '/', $path));
		}

		private static function _resolve_file(string $path): string {

			if (isset(self::$_files_cache[$path])) {
				return self::$_files_cache[$path];
			}

			$resolved = self::_resolve_path($path);
			$relative_path = preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $resolved);
			$parent_folder = dirname($resolved).'/';
			$basename = basename($resolved);

			if (isset(self::$_directories_cache[$parent_folder]) && isset(self::$_directories_cache[$parent_folder][$basename])) {
				return self::$_files_cache[$path] = self::$_directories_cache[$parent_folder][$basename];
			}

			// Add-on override
			if ($addon_file = self::_addon_file($relative_path)) {
				$resolved = $addon_file;
			}

			if (!defined('VMOD_DISABLED') || VMOD_DISABLED !== 'true') {
				$resolved = vmod::check($resolved);
			}

			return self::$_files_cache[$path] = $resolved;
		}

		// Index the files provided by add-ons by their app relative path. Doing this once
		// per request is far cheaper than looking up every file in every add-on folder.
		private static function _addon_files(): array {

			if (self::$_addon_files !== null) {
				return self::$_addon_files;
			}

			$files = [];
			$files_case_insensitive = [];

			foreach (glob(FS_DIR_STORAGE .'addons/*', GLOB_ONLYDIR) as $addon_dir) {

				if (preg_match('#\.disabled$#', basename($addon_dir))) continue;

				$iterator = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator($addon_dir, \FilesystemIterator::SKIP_DOTS),
					\RecursiveIteratorIterator::SELF_FIRST
				);

				foreach ($iterator as $file) {

					$path = str_replace('\\', '/', $file->getPathname());
					$relative = substr($path, strlen(str_replace('\\', '/', $addon_dir)) + 1);

					// Folders are indexed with a trailing slash, just like the paths they are resolved from
					if ($file->isDir()) {
						$relative = rtrim($relative, '/') . '/';
						$path = rtrim($path, '/') . '/';
					}

					if ($relative === '/') continue;

					// Add-ons are indexed in the same order as a glob() would return them,
					// so a later add-on overrides an earlier one just like before
					$files[$relative] = $path;
					$files_case_insensitive[strtolower($relative)] = $path;
				}
			}

			return self::$_addon_files = [
				'exact' => $files,
				'case_insensitive' => $files_case_insensitive,
			];
		}

		// Get the add-on provided file matching an app relative path
		private static function _addon_file(string $relative_path): string|false {

			$files = self::_addon_files();

			if (isset($files['exact'][$relative_path])) {
				return $files['exact'][$relative_path];
			}

			// Case insensitive fallback, as glob() matches case insensitively on Windows
			return $files['case_insensitive'][strtolower($relative_path)] ?? false;
		}
	}
