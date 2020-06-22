<?php
  document::$layout = 'blank';

  http_response_code(503);

  $_page = new ent_view();
  echo $_page->stitch('pages/maintenance_mode');
