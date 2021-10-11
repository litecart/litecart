<?php
  document::$layout = 'blank';

  http_response_code(503);

  $_page = new ent_view('pages/maintenance_mode.inc.php');
  echo $_page;
