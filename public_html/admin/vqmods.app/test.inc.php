<?php
  $_GET['debug'] = true;
  $_GET['vqmod'] = basename($_GET['vqmod']);

  try {
    if (!empty($_GET['vqmod'])) {
      $vqmods = array(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'vqmod/xml/'. basename($_GET['vqmod']));
    } else {
      $vqmods = glob(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'vqmod/xml/*.xml');
    }

    $files_to_modify = array();

    foreach ($vqmods as $vqmod) {

      $xml = simplexml_load_file($vqmod);

      foreach($xml->file as $file) {
        foreach(explode(',', $file['name']) as $filename) {
          $filename = FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . (isset($file['path']) ? $file['path'] : '') . $filename;

          if (!empty(VQMod::$replaces)) {
            foreach(VQMod::$replaces as $search => $replace) {
              $filename = preg_replace($search, $replace, $filename);
            }
          }

          foreach(glob($filename) as $file) {
            if (in_array($file, array('.', '..'))) continue;
            $files_to_modify[] = $file;
          }
        }
      }
    }

    $files_to_modify = array_unique($files_to_modify);
    sort($files_to_modify);

  } catch (Exception $e) {
    notices::add('errors', $e->getMessage());
    return;
  }

?>
<h1><?php echo $_GET['vqmod']; ?> / <?php echo $xml->id; ?></h1>

<table class="table table-striped data-table">
  <tbody>
    <?php foreach($files_to_modify as $file) { ?>
    <tr>
      <td>
        <div><?php echo 'Testing ' . preg_replace('#^('. preg_quote(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME, '#') .')#', '', $file) . PHP_EOL; ?></div>
        <div><?php vmod::check($file); ?></div>
      </td>
    </tr>
    <?php } ?>
  </tbody>
</table>
