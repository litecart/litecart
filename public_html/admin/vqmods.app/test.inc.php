<?php
  $_GET['debug'] = true;
  $_GET['vqmod'] = basename($_GET['vqmod']);

  breadcrumbs::add(basename($_GET['vqmod']));

  try {

    if (empty($_GET['vqmod'])) throw new Exception('No vQmod provided');
    if (!is_file(FS_DIR_APP . 'vqmod/xml/' . basename($_GET['vqmod']))) throw new Exception('The vQmod does not exist');

    $modification = [];

    $dom = new \DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;

    if (!$dom->loadXml(file_get_contents(FS_DIR_APP . 'vqmod/xml/' . basename($_GET['vqmod'])))) {
      throw new Exception(libxml_get_last_error());
    }

    if ($dom->documentElement->tagName != 'modification') {
      throw new Exception("File is not a valid vqmod ($file)");
    }

    if (empty($dom->getElementsByTagName('file'))) {
      throw new \Exception("File has no defined files to modify");
    }

    foreach ($dom->getElementsByTagName('file') as $file_node) {

      foreach (explode(',', $file_node->getAttribute('name')) as $files_to_modify) {
        $files_to_modify = $file_node->getAttribute('path') . $files_to_modify;

      // Apply path aliases
        if (!empty(VQMod::$replaces)) {
          $files_to_modify = preg_replace(array_keys(VQMod::$replaces), array_values(VQMod::$replaces), $files_to_modify);
        }

      // Find all files to modify
        foreach (glob(FS_DIR_APP . $files_to_modify) as $file) {

          $short_file = preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $file);

        // Parse vQmod
          foreach ($file_node->getElementsByTagName('operation') as $i => $operation_node) {

          // On Error
            switch ($operation_node->getAttribute('error')) {
              case 'error':
                $onerror = 'error';
                break;

              case 'skip':
                $onerror = 'skip';
                break;

              case 'abort':
              default:
                $onerror = 'abort';
                break;
            }

          // Search
            $search_node = $operation_node->getElementsByTagName('search')->item(0);
            $search = $search_node->textContent;

          // Regex
            if ($search_node->getAttribute('regex') == 'true') {
              $search = trim($search);

            } else {

            // Trim
              if ($search_node->getAttribute('trim') != 'false') {
                $search = preg_replace('#^\s*#s', '', $search); // Trim beginning of CDATA
                $search = preg_replace('#\s*$#s', '$1', $search); // Trim end of CDATA
              }

            // Whitespace
              if (!in_array($search_node->getAttribute('position'), ['ibefore', 'iafter'])) {
                $search = preg_split('#(\r\n?|\n)#', $search);
                for ($i=0; $i<count($search); $i++) {
                  if ($search[$i] = trim($search[$i])) {
                    $search[$i] = '[ \\t]*' . preg_quote($search[$i], '#') . '[ \\t]*';
                  } else if ($i != count($search)-1) {
                    $search[$i] = '[ \\t]*(?:\r\n?|\n)';
                  }
                }
                $search = implode($search);
              } else {
                $search = '[ \\t]*' . preg_quote(trim($search), '#') . '[ \\t]*';
              }

            // Offset
              if ($search_node->getAttribute('offset') && in_array($search_node->getAttribute('position'), ['before', 'after', 'replace'])) {
                switch ($search_node->getAttribute('position')) {
                  case 'before':
                    $offset_before = '(?:.*?(?:\r\n?|\n)){'. (int)$search_node->getAttribute('offset') .'}';
                    $offset_after  = '';
                    break;
                  case 'after':
                  case 'replace':
                    $offset_before = '';
                    $offset_after = '(?:.*?(?:\r\n?|\n|$)){0,'. (int)$search_node->getAttribute('offset') .'}';
                    break;
                  default:
                    $offset_before = '';
                    $offset_after = '';
                    break;
                }
                $search = $offset_before . $search . $offset_after;
              }

              $search = '#'. $search .'#';
            }

          // Indexes
            if ($indexes = $search_node->getAttribute('index')) {
              $indexes = preg_split('#, ?#', $indexes);
            }

          // Ignoreif
            if ($ignoreif_node = $operation_node->getElementsByTagName('ignoreif')->item(0)) {
              $ignoreif = $ignoreif_node->textContent;

              if ($ignoreif_node->getAttribute('regex') == 'true') {
                $ignoreif = trim($ignoreif);

              } else {

                if ($ignoreif_node->getAttribute('trim') != 'false') {
                  $ignoreif = preg_replace('#^\s*#s', '', $ignoreif); // Trim beginning of CDATA
                  $ignoreif = preg_replace('#\s*$#s', '$1', $ignoreif); // Trim end of CDATA
                }

                if (preg_match('#[\r\n]#', $ignoreif)) {
                  $ignoreif = preg_split('#(\r\n?|\n)#', $ignoreif);
                  for ($i=0; $i<count($ignoreif); $i++) {
                    if ($ignoreif[$i] = trim($ignoreif[$i])) {
                      $ignoreif[$i] = '[ \\t]*' . preg_quote($ignoreif[$i], '#') . '[ \\t]*(?:\r\n?|\n)';
                    } else if ($i != count($ignoreif)-1) {
                      $ignoreif[$i] = '[ \\t]*(?:\r\n?|\n)';
                    }
                  }
                  $ignoreif = implode($ignoreif);
                } else {
                  $ignoreif = '[ \\t]*' . preg_quote(trim($ignoreif), '#') . '[ \\t]*';
                }
              }
            }

          // Add
            $add_node = $operation_node->getElementsByTagName('add')->item(0);
            $add = $add_node->textContent;

            if ($add_node->getAttribute('regex') == 'true') {
              $add = trim($add);

            } else {

              if ($add_node->getAttribute('trim') != 'false') {
                $add = preg_replace('#^\s*#s', '', $add); // Trim beginning of CDATA
                $add = preg_replace('#\s*$#s', '$1', $add); // Trim end of CDATA
              }

              switch($search_node->getAttribute('position')) {

                case 'before':
                case 'ibefore':
                  $add = addcslashes($add, '\\$').'$0';
                  break;

                case 'after':
                case 'iafter':
                  $add = '$0'. addcslashes($add, '\\$');
                  break;

                case 'top':
                  $search = '#^.*$#s';
                  $indexes = '';
                  $add = addcslashes($add, '\\$').'$0';
                  break;

                case 'bottom':
                  $search = '#^.*$#s';
                  $indexes = '';
                  $add = '$0'.addcslashes($add, '\\$');
                  break;

                case 'replace':
                case 'ireplace':
                  $add = addcslashes($add, '\\$');
                  break;

                case 'all':
                  $search = '#^.*$#s';
                  $indexes = '';
                  $add = addcslashes($add, '\\$');
                  break;

                default:
                  throw new \Exception('Unknown value ('. $search_node->getAttribute('position') .') for attribute position (replace|before|after|ireplace|ibefore|iafter)');
                  continue 2;
              }
            }

            if (!isset($modification[$short_file])) {
              $modification[$short_file] = [];
            }

            $modification[$short_file][] = [
              'onerror' => $onerror,
              'search' => [
                'pattern' => $search,
                'indexes' => $indexes,
              ],
              'ignoreif' => !empty($ignoreif) ? $ignoreif : null,
              'add' => $add,
            ];
          }
        }
      }
    }

  } catch (Exception $e) {
    notices::add('errors', $e->getMessage());
    return;
  }

?>
<style>
table td {
  line-height: 200% !important;
}
.operations {
  margin-inline-start: 2em;
}
</style>

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_test_vqmod', 'Test vQmod'); ?></h1>
<h2><?php echo functions::escape_html($dom->getElementsByTagName('id')->item(0)->textContent); ?></h2>

<table class="table table-striped table-hover data-table">
  <thead>
    <tr>
      <th class="main"><?php echo language::translate('title_file', 'File'); ?></th>
      <th><?php echo language::translate('title_result', 'Result'); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($modification as $file => $operations) { ?>
    <tr>
      <td>
        <div><strong><?php echo $file; ?></strong></div>
        <div class="operations">
<?php
  $error = null;

  if (!is_file(FS_DIR_APP . $file)) {
    throw new Exception("File does not exist ($file)");
  }

  $buffer = file_get_contents(FS_DIR_APP . $file);

  foreach ($operations as $i => $operation) {

    try {

      $tmp = $buffer;

      if (!empty($operation['ignoreif']) && preg_match($operation['ignoreif'], $tmp)) {
        throw new Exception(functions::draw_fonticon('fa-exclamation-circle', 'style="color: #f48f3b;"') . ' [Ignored]');
        continue;
      }

      $found = preg_match_all($operation['search']['pattern'], $tmp, $matches, PREG_OFFSET_CAPTURE);

      if (!$found) {
        switch ($operation['onerror']) {
          case 'abort':
            $error = true;
            throw new Exception(functions::draw_fonticon('fa-times', 'style="color: #c00;"') . ' Search not found');
            continue 3;

          case 'skip':
            $result = '[Skipped]';
            throw new Exception(functions::draw_fonticon('fa-exclamation-circle', 'style="color: #f48f3b;"') . ' [Skipped]');
            continue 2;

          case 'error':
          default:
            $error = true;
            throw new Exception(functions::draw_fonticon('fa-times', 'style="color: #c00;"') . ' Search not found [Aborted]');
            continue 2;
        }
      }

      if (!empty($operation['search']['indexes'])) {
        rsort($operation['search']['indexes']);

        foreach ($operation['search']['indexes'] as $index) {
          $index = $index - 1; // [0] is the 1st in computer language

          if ($found > $index) {
            $tmp = substr_replace($tmp, preg_replace($operation['search']['pattern'], $operation['add'], $matches[0][$index][0]), $matches[0][$index][1], strlen($matches[0][$index][0]));
          }
        }

      } else {
        $tmp = preg_replace($operation['search']['pattern'], $operation['add'], $tmp, -1, $count);

        if (!$count && $operation['onerror'] != 'skip') {
          $error = true;
          throw new Exception(functions::draw_fonticon('fa-times', 'style="color: #c00;"') . ' Failed to add code');
          continue 2;
        }
      }

      $buffer = $tmp;

      throw new Exception(functions::draw_fonticon('fa-check', 'style="color: #7ccc00;"'));

    } catch (Exception $e) {
      echo '<div>'. functions::draw_fonticon('fa-caret-right') .' Operation #'. ($i+1) .' '. $e->getMessage() .'</div>';
    }
  }
?>
        </div>
      </td>
      <td style="font-size: 2em;">
        <?php echo empty($error) ? functions::draw_fonticon('fa-check', 'style="color: #7ccc00;"') : functions::draw_fonticon('fa-times', 'style="color: #c00;"'); ?>
      </td>
    </tr>
    <?php } ?>
  </tbody>
</table>
