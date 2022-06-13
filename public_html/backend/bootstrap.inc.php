<?php

// Define template paths
  define('FS_DIR_TEMPLATE', 'app://backend/template/');
  define('WS_DIR_TEMPLATE', WS_DIR_APP . 'backend/template/');

// Fetch apps
  $apps = functions::admin_get_apps();

// Identify app and document
  if (preg_match('#'. preg_quote(BACKEND_ALIAS, '#') .'/(?<app>[^/]*)/(?<doc>.*)$#', route::$request, $matches)) {

    if (!empty($matches['app']) && in_array($matches['app'], array_column($apps, 'id'))) {
      define('__APP__', $matches['app']);

      if (!empty($matches['doc']) && in_array($matches['doc'], array_keys($apps[__APP__]['docs']))) {
        define('__DOC__', $matches['doc']);
      }
    }
  }
