<?php

  class stream_app {
    private $_directory;
    private $_stream;
    public $context;

    public function dir_opendir($path) {
      $path = $this->_resolve_path($path);
      $this->_directory = opendir($path);
      return true;
      }

    public function dir_readdir() {

      $file = readdir($this->_directory);

    // Skip returning . and ..
      if (is_string($file) && preg_match('#^\.{1,2}$#', $file)) {
        return $this->dir_readdir();
      }

      return $file;
    }

    public function dir_closedir() {
      $this->_directory = null;
      return true;
    }

    public function dir_rewinddir() {
      return rewinddir($this->_directory);
    }

    public function mkdir(string $path, int $mode, int $options): bool {
      trigger_error('Creating an app:// directory is prohibited', E_USER_WARNING);
      return false;
    }

    public function rename(string $path_from, string $path_to): bool {
      trigger_error('Renaming an app:// resource is prohibited', E_USER_WARNING);
      return false;
    }

    public function rmdir(string $path, int $options): bool {
      trigger_error('Removing an app:// directory is prohibited', E_USER_WARNING);
      return false;
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
      return fflush($this->_stream);
    }

    public function stream_lock(int $operation): bool {
      return flock($this->_stream, $operation);
    }

    public function stream_metadata(string $path, int $option, mixed $value): bool {

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
      $path = vmod::check($path);

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
      trigger_error('Truncating an app:// resource is prohibited', E_USER_WARNING);
      return false;
    }

    public function stream_write(string $data): int|bool {
      trigger_error('Writing to an app:// resource is prohibited', E_USER_WARNING);
      return false;
    }

    public function unlink(string $path): bool {
      trigger_error('Removing an app:// resource is prohibited', E_USER_WARNING);
      return false;
    }

    public function url_stat(string $path, int $flags): array|false {
      $path = $this->_resolve_path($path);
      return file_exists($path) ? stat($path) : false;
    }

    ####################################################################

    private function _resolve_path($path) {
      return preg_replace('#^app://#', FS_DIR_APP, str_replace('\\', '/', $path));
    }
  }
