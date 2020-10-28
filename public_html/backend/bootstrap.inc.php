<?php

  route::identify();

// Run operations before capture
  event::fire('before_capture');

// Go
  route::process();
