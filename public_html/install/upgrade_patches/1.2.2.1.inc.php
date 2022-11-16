<?php

  perform_action('modify', [
    FS_DIR_APP . '.htaccess' => [
      [
        'search'  => "864000",
        'replace' => "86400",
      ],
    ],
  ]);
