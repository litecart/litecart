<?php

  perform_action('modify', [
    FS_DIR_APP . '.htaccess' => [
      [
        'search'  => "864000",
        'replace' => "86400",
      ],
    ],
  ]);

  $copied_files = [
    FS_DIR_APP . 'data/blacklist.txt' => FS_DIR_APP . 'install/data/default/upload/data/blacklist.txt',
    FS_DIR_APP . 'data/whitelist.txt' => FS_DIR_APP . 'install/data/default/upload/data/whitelist.txt',
  ];
