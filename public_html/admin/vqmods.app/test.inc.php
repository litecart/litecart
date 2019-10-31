<?php
  $_GET['debug'] = true;
  $_GET['vqmod'] = basename($_GET['vqmod']);

  breadcrumbs::add(basename($_GET['vqmod']));

  foreach (glob(FS_DIR_APP . 'vqmod/vqcache/*.php') as $file) {
    unlink($file);
  }

  try {
    if (!empty($_GET['vqmod'])) {
      $vqmods = array(FS_DIR_APP . 'vqmod/xml/'. basename($_GET['vqmod']));
    } else {
      $vqmods = glob(FS_DIR_APP . 'vqmod/xml/*.xml');
    }

    $files_to_modify = array();

    foreach ($vqmods as $vqmod) {

      $xml = simplexml_load_file($vqmod);

      foreach ($xml->file as $file) {
        foreach (explode(',', $file['name']) as $filename) {
          $filename = (isset($file['path']) ? $file['path'] : '') . $filename;

          if (!empty(VQMod::$replaces)) {
            foreach (VQMod::$replaces as $search => $replace) {
              $filename = preg_replace($search, $replace, $filename);
            }
          }

          $filename = FS_DIR_APP . $filename;

          foreach (glob($filename) as $file) {
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
<h1><?php echo $app_icon; ?> <?php echo language::translate('title_test_vqmod', 'Test vQmod'); ?></h1>
<h2><?php echo $xml->id; ?> (<?php echo $_GET['vqmod']; ?>)</h2>

<table class="table table-striped table-hover data-table">
  <thead>
    <tr>
      <th class="main"><?php echo language::translate('title_file', 'File'); ?></th>
      <th><?php echo language::translate('title_result', 'Result'); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($files_to_modify as $file) { ?>
    <tr>
      <td>
        <div><?php echo 'Testing ' . preg_replace('#^('. preg_quote(FS_DIR_APP, '#') .')#', '', $file) . PHP_EOL; ?></div>
        <div><?php ob_start(); vmod::check($file); $buffer = ob_get_clean(); ?></div>
      </td>
      <td class="text-center">
        <?php echo empty($buffer) ? functions::draw_fonticon('fa-check', 'style="color: #7ccc00;"') : functions::draw_fonticon('fa-times', 'style="color: #c00;"'); ?>
      </td>
    </tr>
    <?php } ?>
  </tbody>
</table>
