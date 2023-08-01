<?php

  if (!settings::get('accounts_enabled')) return;

  $box_account = new ent_view();
  echo $box_account->render(FS_DIR_TEMPLATE . 'partials/box_account_links.inc.php');
