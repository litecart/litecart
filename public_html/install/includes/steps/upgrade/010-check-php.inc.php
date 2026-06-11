<?php

  ### PHP > Check Version ################################################

  echo '<p>Checking PHP version... ';

  if (version_compare(PHP_VERSION, $requirements['scripting']['php']['minimumVersion'], '<')) {
    throw new Exception(PHP_VERSION .' <span class="error">[Error] PHP '. $requirements['scripting']['php']['minimumVersion'].'+ minimum requirement</span></p>' . PHP_EOL . PHP_EOL);

  } else if (version_compare(PHP_VERSION, '7.2', '<=')) {
    echo PHP_VERSION .' <span class="warning">[Warning] PHP '. PHP_VERSION .' has reached <a href="https://www.php.net/supported-versions.php" target="_blank">end of life</a>.</span></p>' . PHP_EOL . PHP_EOL;

  } else if (version_compare(PHP_VERSION, $requirements['scripting']['php']['recommendedVersion'], '<=')) {
    echo PHP_VERSION .' <span class="warning">[Warning] PHP '. PHP_VERSION .' is below the recommended version '. $requirements['scripting']['php']['recommendedVersion'] .'+</p>' . PHP_EOL . PHP_EOL;

  } else {
    echo PHP_VERSION .' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
  }

  ### PHP > Check PHP Extensisons ########################################

  echo '<p>Checking for PHP extensions... ';

  $missing_extensions = [];

  foreach ($requirements['scripting']['php']['requiredExtensions'] as $extension) {
    if ((is_array($extension) && !in_array(true, array_map(function($ext) {
      return extension_loaded($ext);
    }, $extension)) && !extension_loaded($extension))) {
      $missing_extensions[] = $extension;
    }
  }

  $missing_extensions = array_map(function($extension) {
    return is_array($extension) ? implode(' or ', $extension) : $extension;
  }, $missing_extensions);

  if ($missing_extensions) {
    echo '<span class="warning">[Warning] Some important PHP extensions are missing ('. implode(', ', $missing_extensions) .'). It is recommended that you enable them in php.ini.</span></p>' . PHP_EOL . PHP_EOL;
  } else {
    echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
  }
