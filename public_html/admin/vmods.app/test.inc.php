<?php
  $_GET['debug'] = true;
  $_GET['vmod'] = basename($_GET['vmod']);

  breadcrumbs::add(basename($_GET['vmod']));

  try {

    if (empty($_GET['vmod'])) throw new Exception('No vmod provided');
    if (!is_file(FS_DIR_APP . 'vmods/' . basename($_GET['vmod']))) throw new Exception('The vmod does not exist');

    $file = FS_DIR_APP . 'vmods/' . basename($_GET['vmod']);

    $dom = new \DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;

    if (!$dom->loadXml(file_get_contents($file))) {
      throw new Exception(libxml_get_last_error());
    }

    switch ($dom->documentElement->tagName) {

      case 'vmod': // LiteCart Modification
        $vmod = vmod::parse_vmod($dom, $file);
        break;

      case 'modification': // vQmod
        $vmod = vmod::parse_vqmod($dom, $file);
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

    $glob_pattern = $vmod['files'][$key]['name'];

  // Apply path aliases
    if (!empty(vmod::$aliases)) {
      $glob_pattern = preg_replace(array_keys(vmod::$aliases), array_values(vmod::$aliases), $glob_pattern);
    }

    $result['pathfiles'][$glob_pattern] = [
      'pathfile' => $glob_pattern,
      'files' => [],
    ];

    $files = glob(FS_DIR_APP . $glob_pattern, GLOB_BRACE);

    foreach ($files as $file) {

      $short_file = preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $file);

      $result['pathfiles'][$glob_pattern]['files'][$short_file] = [
        'file' => $short_file,
        'operations' => [],
        'error' => '',
      ];

      try {

        if (!is_file($file)) throw new Exception('File does not exist');

        $buffer = file_get_contents($file);

        foreach ($vmod['files'][$key]['operations'] as $i => $operation) {

          try {

            $result['pathfiles'][$glob_pattern]['files'][$short_file]['operations'][$i] = [
              'error' => '',
            ];

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
            $result['pathfiles'][$glob_pattern]['files'][$short_file]['operations'][$i]['error'] = $e->getMessage();
            throw new Exception('Directive contains errors', $e->getCode());
          }
        }

      } catch (Exception $e) {
        $result['pathfiles'][$glob_pattern]['files'][$short_file]['error'] = 'Directive contains errors';
        $result['pathfiles'][$glob_pattern]['error'] = 'Directive contains errors';
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