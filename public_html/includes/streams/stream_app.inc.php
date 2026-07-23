<?php

	class stream_app {

		private static $_cache = [];
		private $_directory = [];
		private $_stream;
		public $context;

		public function dir_opendir(string $path, int $options): bool {

			$microtime = microtime(true);

			$path = $this->_resolve_path($path);
			$relative_path = preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $path);

			$this->_directory = [];

			if (!isset(self::$_cache[$path])) {

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

				self::$_cache[$path] = $this->_directory;

			} else {
				$this->_directory = self::$_cache[$path];
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

			return mkdir($this->_resolve_path($path), $mode);
		}

		public function rename(string $path_from, string $path_to): bool {

			if (!getenv('SUPER_MODE')) {
				trigger_error('Renaming an app:// resource is prohibited', E_USER_WARNING);
				return false;
			}

			return rename($this->_resolve_path($path_from), $this->_resolve_path($path_to));
		}

		public function rmdir(string $path, int $options): bool {

			if (!getenv('SUPER_MODE')) {
				trigger_error('Removing an app:// directory is prohibited', E_USER_WARNING);
				return false;
			}

			return rmdir($this->_resolve_path($path));
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

			$path = $this->_resolve_path($path);

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

			$path = $this->_resolve_path($path);
			
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

			return unlink($this->_resolve_path($path));
		}

		public function url_stat(string $path, int $flags): array|false {

			$path = $this->_resolve_file($path);

			if (!file_exists($path)) {
				return false;
			}

			return stat($path);
		}

		## Non-Standard StreamWrapper Methods

		private function _resolve_path(string $path): string {
			return preg_replace('#^app://#', FS_DIR_APP, str_replace('\\', '/', $path));
		}

		private function _resolve_file(string $path): string {

			$path = $this->_resolve_path($path);
			$relative_path = preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $path);
			$parent_folder = dirname($path).'/';
			$basename = basename($path);

			if (isset(self::$_cache[$parent_folder])) {
				if (isset(self::$_cache[$parent_folder][$basename])) {
					return self::$_cache[$parent_folder][$basename];
				}
			}

			foreach (glob(FS_DIR_STORAGE .'addons/*/'.$relative_path) as $file) {
				$file = str_replace('\\', '/', $file);
				if (preg_match('#^'. preg_quote(FS_DIR_STORAGE .'addons/', '#') .'[^/]+.disabled/#', $file)) continue;
				$path = $file;
			}

			if (!defined('VMOD_DISABLED') || VMOD_DISABLED !== 'true') {
				$path = vmod::check($path);
			}

			return $path;
		}
	}
