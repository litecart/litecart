<?php

  $box_cookie_notice = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/site_cookie_notice.inc.php');
  echo $box_cookie_notice->render();
