<?php

// Delete some files
  perform_action('delete', [
    FS_DIR_APP . 'includes/boxes/box_region.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/views/box_region.inc.php',
  ]);

  $newletter_recipients_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."newsletter_recipients
    where client_ip != '' and hostname = '';"
  );

  while ($recipient = database::fetch($newletter_recipients_query)) {
    database::query(
      "update ". DB_TABLE_PREFIX ."newsletter_recipients
      set hostname = '". database::input(gethostbyaddr($recipient['client_ip'])) ."'
      where id = ". (int)$recipient['id'] ."
      limit 1;"
    );
  }
