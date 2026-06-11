<?php

  ### Database > Check Version ###########################################

  $database_software = database::query(
    "SELECT VERSION();"
  )->fetch(function($row) use ($requirements) {
    if (preg_match('#mariadb#i', $row['VERSION()'])) {
      return [
        'name' => 'MariaDB',
        'version' => strtok($row['VERSION()'], '-'),
        'min_version' => $requirements['databases']['mariadb']['minimumVersion'],
        'recommended_version' => $requirements['databases']['mariadb']['recommendedVersion'],
      ];
    }
    return [
      'name' => 'MySQL',
      'version' => $row['VERSION()'],
      'min_version' => $requirements['databases']['mysql']['minimumVersion'],
      'recommended_version' => $requirements['databases']['mysql']['recommendedVersion'],
    ];
  });

  echo $database_software['name'] .' '. $database_software['version'] . ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

  echo '<p>Checking database software... ';

  if (version_compare($database_software['version'], $database_software['min_version'], '<')) {
    throw new Exception($database_software['name'] .' '. $database_software['version'] . ' <span class="error">[Error] '.  $database_software['name'] .' '. $database_software['min_version'] .'+ required</span></p>');

  } else if (version_compare($database_software['version'], $database_software['recommended_version'], '<')) {
    echo $database_software['name'] .' '. $database_software['version'] .' <span class="ok">[OK]</span><br>'
      . '<span class="warning">'. $database_software['name'] .' '. $database_software['recommended_version'] .'+ recommended</span></span></p>';

  } else {
    echo $database_software['name'] .' '. $database_software['version'] . ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
  }
