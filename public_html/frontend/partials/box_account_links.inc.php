<?php

  if (!settings::get('accounts_enabled')) return;

  $box_account = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_account_links.inc.php');
  echo $box_account->render();
