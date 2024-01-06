<?php
  document::$layout = 'blank';

  http_response_code(503);

  $_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/maintenance_mode.inc.php');
  echo $_page->render();
