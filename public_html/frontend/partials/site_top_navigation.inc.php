<?php

  $site_navigation = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/site_top_navigation.inc.php');
  echo $site_navigation->render();
