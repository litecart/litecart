<?php
  $_GET['vmod'] = basename($_GET['vmod']);

  breadcrumbs::add(language::translate('title_vMods', 'vMods'), document::href_link(WS_DIR_ADMIN, ['doc' => 'vmods'], ['app']));
  breadcrumbs::add(language::translate('title_view_vMod', 'View vMod') .' '. basename($_GET['vmod']));

  document::$snippets['title'][] = language::translate('title_view_vmod', 'View vMod');

  try {
    if (empty($_GET['vmod'])) throw new Exception(language::translate('error_must_provide_vmod', 'You must provide a vMod'));

    $file = FS_DIR_STORAGE . 'vmods/' . basename($_GET['vmod']);

    if (!is_file($file)) throw new Exception(language::translate('error_file_could_not_be_found', 'The file could not be found'));

    $directives = [];

    $xml = simplexml_load_file($file);

    if ($xml->getName() != 'vmod') {
      throw new Exception(language::translate('error_not_a_valid_vmod_file', 'Not a valid vMod file'));
    }

    if (empty($xml->file)) {
      throw new Exception(language::translate('error_no_files_to_modify', 'No files to modify'));
    }

    foreach ($xml->file as $file) {
      $directive = [
        'files' => [],
      ];
    }

  } catch (Exception $e) {
    notices::add('errors', $e->getMessage());
    return;
  }

  breadcrumbs::add(basename($_GET['vmod']));
?>
<style>
pre {
  background: #f9f9f9;
  border-radius: 4px;
  overflow: auto;
  max-width: 100%;
  max-height: 400px;
}

.operation {
  border: 1px solid #f3f3f3;
  border-radius: 4px;
  padding: 1em;
  margin-bottom: 1em;
}
</style>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_view_modification', 'View Modification'); ?>
    </div>
  </div>

  <div class="card-body">

    <h1><?php echo $xml->id; ?></h1>

    <p><?php echo language::translate('description_view_vmod', 'Please note: These are the contents of the virtual modification to give you a good understanding of what it does. Do NOT make these changes yourself.'); ?></p>

    <ul class="list-unstyled">
      <?php foreach ($xml->file as $file) { ?>
      <li>
        <h2>In <strong><?php echo (!empty($file->attributes()['path']) ? $file->attributes()['path'] : '') . $file->attributes()['name']; ?></strong>:</h2>

        <?php foreach ($file->operation as $operation) { ?>
        <div class="operation">
          <div class="find">
            ** Find **

          <?php echo !empty($operation->attributes()['index']) ? ' (The following matches: '. $operation->attributes()['index'] .')' : ''; ?>

          <?php echo !empty($operation->find->attributes()['offset-before']) ? ' (Offset Before: '. $operation->find->attributes()['offset-before'] .')' : ''; ?>
          <?php echo !empty($operation->find->attributes()['offset-after']) ? ' (Offset After: '. $operation->find->attributes()['offset-after'] .')' : ''; ?>

            <pre><code><?php echo functions::escape_html($operation->find); ?></code></pre>
          </div>


          <div class="insert">
<?php
  switch($operation->insert->attributes()['position']) {
    case 'replace':
      echo '** Replace with **';
      break;
    case 'before':
    case 'ibefore':
      echo '** Before that, add **';
      break;
    case 'after':
    case 'iafter':
      echo '** After that, add **';
      break;
  }
?>
            <pre><code><?php echo functions::escape_html($operation->insert); ?></code></pre>
          </div>
        </div>
        <?php } ?>

      </li>
      <?php } ?>
    </ul>
  </div>
</div>