<?php

  if (!settings::get('accounts_enabled')) return;

  if (!empty(customer::$data['id'])) {

    $box_account = new ent_view(FS_DIR_TEMPLATE . 'partials/box_account_links.inc.php');

    $box_account->snippets = [
      'name' => customer::$data['firstname'] .' '. customer::$data['lastname'],
      'email' => customer::$data['email'],
    ];

    echo $box_account;

  }
