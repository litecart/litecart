<?php

  /*!
   * This file contains PHP logic that is separated from the HTML view.
   * Visual changes can be made to the file found in the template folder:
   *
   *   ~/frontend/templates/default/pages/maintenance_mode.inc.php
   */

  document::$layout = 'blank';

  http_response_code(503);

  $_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/maintenance_mode.inc.php');
  echo $_page->render();
