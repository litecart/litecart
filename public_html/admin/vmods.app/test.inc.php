<?php
  $_GET['debug'] = true;
  $_GET['vmod'] = basename($_GET['vmod']);

  breadcrumbs::add(basename($_GET['vmod']));

  try {

    if (empty($_GET['vmod'])) throw new Exception('No vmod provided');
    if (!is_file(FS_DIR_APP . 'vmods/' . basename($_GET['vmod']))) throw new Exception('The vmod does not exist');

    $dom = new \DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;

    if (!$dom->loadXml(file_get_contents(FS_DIR_APP . 'vmods/' . basename($_GET['vmod'])))) {
      throw new Exception(libxml_get_last_error());
    }

    switch ($dom->documentElement->tagName) {

      case 'vmod': // LiteCart Modification
        $vmod = vmod::parse_vmod($dom, $_GET['vmod']);
        break;

      case 'modification': // vQmod
        $vmod = vmod::parse_vqmod($dom);
        break;

      default:
        throw new Exception("File ($file) is not a valid vmod or vQmod");
    }

  } catch (Exception $e) {
    notices::add('errors', $e->getMessage());
    return;
  }

// Test results

  $result = [
    'name' => $vmod['name'],
    'files' => [],
  ];

  foreach (array_keys($vmod['files']) as $key) {
    $patterns = explode(',', $vmod['files'][$key]['name']);

    foreach ($patterns as $pattern) {
      $path_and_file = $vmod['files'][$key]['path'].$pattern;

    // Apply path aliases
      if (!empty(vmod::$aliases)) {
        $path_and_file = preg_replace(array_keys(vmod::$aliases), array_values(vmod::$aliases), $path_and_file);
      }

      $result['pathfiles'][$path_and_file] = [
        'pathfile' => $path_and_file,
        'files' => [],
      ];

      $files = glob(FS_DIR_APP . $path_and_file);

      if (empty($files)) {
        $result['pathfiles'][$path_and_file]['error'] = 'No files matching pattern';
        continue;
      }

      foreach ($files as $file) {

        $short_file = preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $file);

        $result['pathfiles'][$path_and_file]['files'][$short_file] = [
          'file' => $short_file,
          'operations' => [],
          'error' => '',
        ];

        try {

          if (!is_file($file)) throw new Exception('File does not exist');

          $buffer = file_get_contents($file);

          foreach ($vmod['files'][$key]['operations'] as $i => $operation) {

            try {

              $result['pathfiles'][$path_and_file]['files'][$short_file]['operations'][$i] = [
                'error' => '',
              ];

              if (!empty($operation['ignoreif']) && preg_match($operation['ignoreif'], $buffer)) {
                continue;
              }
              $found = preg_match_all($operation['find']['pattern'], $buffer, $matches, PREG_OFFSET_CAPTURE);

              if (!$found) {
                switch ($operation['onerror']) {
                  case 'ignore':
                    continue 2;
                  case 'abort':
                  case 'warning':
                  default:
                    throw new Exception('Search not found', E_USER_WARNING);
                    continue 2;
                }
              }

              if (!empty($operation['find']['indexes'])) {
                rsort($operation['find']['indexes']);

                foreach ($operation['find']['indexes'] as $index) {
                  $index = $index - 1; // [0] is the 1st in computer language

                  if ($found > $index) {
                    $buffer = substr_replace($buffer, preg_replace($operation['find']['pattern'], $operation['insert'], $matches[0][$index][0]), $matches[0][$index][1], strlen($matches[0][$index][0]));
                  }
                }

              } else {
                $buffer = preg_replace($operation['find']['pattern'], $operation['insert'], $buffer, -1, $count);

                if (!$count && $operation['onerror'] != 'skip') {
                  throw new Exception('Failed to perform insert', E_USER_ERROR);
                  continue;
                }
              }

            } catch (Exception $e) {
              $result['pathfiles'][$path_and_file]['files'][$short_file]['operations'][$i]['error'] = $e->getMessage();
              throw new Exception('Directive contains errors', $e->getCode());
            }
          }

        } catch (Exception $e) {
          $result['pathfiles'][$path_and_file]['files'][$short_file]['error'] = 'Directive contains errors';
          $result['pathfiles'][$path_and_file]['error'] = 'Directive contains errors';
        }
      }
    }
  }

?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_test_vmod', 'Test vMod'); ?>
    </div>
  </div>

  <div class="card-body">
    <h2><?php echo functions::escape_html($vmod['name']); ?></h2>
  </div>

  <table class="table table-striped table-hover data-table">
    <thead>
      <tr>
        <th class="main"><?php echo language::translate('title_file', 'File'); ?></th>
        <th><?php echo language::translate('title_result', 'Result'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($result['pathfiles'] as $pathfile) { ?>
      <tr>
        <td>
          <h3><?php echo functions::escape_html($pathfile['pathfile']); ?></h3>
          <?php foreach ($pathfile['files'] as $file) { ?>
          <div><?php echo functions::escape_html($file['file']); ?> <?php echo empty($file['error']) ? functions::draw_fonticon('fa-check', 'style="color: #7ccc00;"') : functions::draw_fonticon('fa-times', 'style="color: #c00;"'); ?></div>
          <ul>
            <?php foreach ($file['operations'] as $i => $operation) { ?>
            <li>Operation #<?php echo $i; ?> <?php echo empty($operation['error']) ? functions::draw_fonticon('fa-check', 'style="color: #7ccc00;"') : functions::draw_fonticon('fa-times', 'style="color: #c00;"') .'<br />'. $operation['error']; ?></li>
            <?php } ?>
          </ul>
          <?php } ?>
        </td>
        <td style="font-size: 3em;">
          <?php echo empty($pathfile['error']) ? functions::draw_fonticon('fa-check', 'style="color: #7ccc00;"') : functions::draw_fonticon('fa-times', 'style="color: #c00;"'); ?>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>