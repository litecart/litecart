<?php

  if (!settings::get('accounts_enabled')) return;

  $box_account = new ent_view(FS_DIR_TEMPLATE . 'partials/box_account_links.inc.php');
  echo $box_account;
