<?php

  class wrap_stream_app {
    private $_directory;
    private $_stream;
    public $context;

    public function dir_opendir($path, $options) {

      $path = $this->_resolve_path($path);
      $relative_path = preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $path);

      $this->_directory = [];

      foreach (glob($path.'*') as $file) {
        $basename = basename($file) . (is_dir($file) ? '/' : '');
        $this->_directory[$basename] = $file . (is_dir($file) ? '/' : '');
      }

      foreach (glob(FS_DIR_STORAGE .'addons/*/'.$relative_path.'*', GLOB_BRACE) as $file) {

        $file = str_replace('\\', '/', $file) . (is_dir($file) ? '/' : '');
        $basename = basename($file) . (is_dir($file) ? '/' : '');

        if (preg_match('#^'. preg_quote(FS_DIR_STORAGE .'addons/', '#') .'[^/]+.cache/#', $file)) continue;
        if (preg_match('#^'. preg_quote(FS_DIR_STORAGE .'addons/', '#') .'[^/]+.disabled/#', $file)) continue;
        if (preg_match('#^'. preg_quote(FS_DIR_STORAGE .'addons/', '#') .'[^/]+/vmod\.xml$#', $file)) continue;

        $this->_directory[$basename] = $file;
      }

      uasort($this->_directory, function($a, $b){

        if (is_dir($a) == is_dir($b)) {
          return (basename($a) < basename($b)) ? -1 : 1;
        }

        return is_dir($a) ? -1 : 1;
      });

      return true;
    }

    public function dir_readdir() {
      $result = key($this->_directory);
      next($this->_directory);

      return $result;
    }

    public function dir_closedir() {
      $this->_directory = null;
      return true;
    }

    public function dir_rewinddir() {
      reset($this->_directory);
      return true;
    }

    public function mkdir(string $path, int $mode, int $options): bool {
      return mkdir($this->_resolve_path($path), $mode);
    }

    public function rename(string $path_from, string $path_to): bool {
      return rename($this->_resolve_path($path_from), $this->_resolve_path($path_to));
    }

    public function rmdir(string $path, int $options): bool {
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
      $relative_path = preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $path);

      foreach (glob(FS_DIR_STORAGE .'addons/*/'.$relative_path, GLOB_BRACE) as $file) {
        $file = str_replace('\\', '/', $file);
        if (preg_match('#^'. preg_quote(FS_DIR_STORAGE .'addons/', '#') .'[^/]+.disabled/#', $file)) continue;
        $path = $file;
      }

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
      return ftruncate($this->_stream, $new_size);
    }

    public function stream_write(string $data): int {
      return fwrite($this->_stream, $data);
    }

    public function unlink(string $path): bool {
      return unlink($this->_resolve_path($path));
    }

    public function url_stat(string $path, int $flags): array|false {

      $path = $this->_resolve_path($path);
      $relative_path = preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $path);

      foreach (glob(FS_DIR_STORAGE .'addons/*/'.$relative_path, GLOB_BRACE) as $file) {
        $file = str_replace('\\', '/', $file);
        if (preg_match('#^'. preg_quote(FS_DIR_STORAGE .'addons/', '#') .'[^/]+.disabled/#', $file)) continue;
        $path = $file;
      }

      if (!file_exists($path)) return false;

      return stat($path);
    }

    ####################################################################

    private function _resolve_path($path) {
      return preg_replace('#^app://#', FS_DIR_APP, str_replace('\\', '/', $path));
    }
  }
