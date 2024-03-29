<?php

  /*!
   * This file contains PHP logic that is separated from the HTML view.
   * Visual changes can be made to the file found in the template folder:
   *
   *   ~/frontend/templates/default/partials/box_account_links.inc.php
   */

  if (!settings::get('accounts_enabled')) return;

  $box_account = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_account_links.inc.php');
  echo $box_account->render();
