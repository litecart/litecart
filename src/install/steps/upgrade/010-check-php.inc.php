<?php

  ### PHP > Check Version ################################################

  try {

    echo '<p>Checking PHP version... ';

    if (version_compare(PHP_VERSION, $requirements['scripting']['php']['minimumVersion'], '<')) {
      throw new Exception('PHP '. $requirements['scripting']['php']['minimumVersion'].'+ minimum requirement');

    } else if (
      (PHP_VERSION_ID < 80200) ||
      (PHP_VERSION_ID >= 80200 && PHP_VERSION_ID < 80235) ||
      (PHP_VERSION_ID >= 80300 && PHP_VERSION_ID < 80336) ||
      (PHP_VERSION_ID >= 80400 && PHP_VERSION_ID < 80427) ||
      (PHP_VERSION_ID >= 80500 && PHP_VERSION_ID < 80512)
    ) {
      echo '<span class="warning">[Warning]</span>' . PHP_EOL .' Your PHP version '. PHP_VERSION .' could be vulnerable to CVE-2026-109618 and CVE-2026-109619. Please upgrade to at minimum PHP 8.5.12, 8.4.27, 8.3.36 or 8.2.35.</p>' . PHP_EOL . PHP_EOL;

    } else if (PHP_VERSION_ID <= 70200) {
      echo PHP_VERSION .' <span class="warning">[Warning] PHP '. PHP_VERSION .' has reached <a href="https://www.php.net/supported-versions.php" target="_blank">end of life</a>.</span></p>' . PHP_EOL . PHP_EOL;

    } else if (version_compare(PHP_VERSION, $requirements['scripting']['php']['recommendedVersion'], '<=')) {
      echo PHP_VERSION .' <span class="warning">[Warning] PHP '. PHP_VERSION .' is below the recommended version '. $requirements['scripting']['php']['recommendedVersion'] .'+</p>' . PHP_EOL . PHP_EOL;

    } else {
      echo PHP_VERSION .' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
    }

  } catch (Exception $e) {
    echo PHP_VERSION .' '. implode(PHP_EOL, [
      '<span class="error">[Error]</span>',
      '<div class="error-message">'. $t->getMessage() .'</div></p>',
      '',
      '',
    ]);
  }

  ### PHP > Check PHP Extensisons ########################################

  try {

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

  } catch (Exception $e) {
    echo implode(PHP_EOL, [
      '<span class="error">[Error]</span>',
      '<div class="error-message">'. $t->getMessage() .'</div></p>',
      '',
      '',
    ]);
  }
