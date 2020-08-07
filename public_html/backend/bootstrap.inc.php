<?php

// Load routes
  route::load(FS_DIR_APP . 'backend/routes/url_backend.inc.php');

  route::identify();

// Run operations before capture
  event::fire('before_capture');

// Go
  route::process();
