<?php

  $_GET['vqmod'] = basename($_GET['vqmod']);

  breadcrumbs::add(language::translate('title_vqmods', 'vQmods'), document::href_link(WS_DIR_ADMIN, ['doc' => 'vqmods'], ['app']));
  breadcrumbs::add(language::translate('title_view_vqmod', 'View vQmod') .' '. basename($_GET['vqmod']));

  document::$snippets['title'][] = language::translate('title_view_vqmod', 'View vQmod');

  try {
    if (empty($_GET['vqmod'])) throw new Exception(language::translate('error_must_provide_vqmod', 'You must provide a vQmod'));

    $file = FS_DIR_APP . 'vqmod/xml/' . basename($_GET['vqmod']);

    if (!is_file($file)) throw new Exception(language::translate('error_file_could_not_be_found', 'The file could not be found'));

    $directives = [];

    $xml = simplexml_load_file($file);

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
  }
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

<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_view_modification', 'View Modification'); ?>
  </div>

  <div class="panel-body">

    <h1><?php echo $xml->id; ?></h1>

    <p><?php echo language::translate('description_view_vqmod', 'Please note: These are the contents of the virtual modification to give you a good understanding of what it does. Do NOT make these changes yourself.'); ?></p>

    <ul class="list-unstyled">
      <?php foreach ($xml->file as $file) { ?>
      <li>
        <h2>In <strong><?php echo (!empty($file->attributes()['path']) ? $file->attributes()['path'] : '') . $file->attributes()['name']; ?></strong>:</h2>

        <?php foreach ($file->operation as $operation) { ?>
        <div class="operation">
          <div class="find">
            ** Find **

          <?php echo !empty($operation->attributes()['index']) ? ' (The following matches: '. $operation->attributes()['index'] .')' : ''; ?>

          <?php echo !empty($operation->search->attributes()['offset']) ? ' (Offset: '. $operation->search->attributes()['offset'] .')' : ''; ?>

            <pre><code><?php echo functions::escape_html($operation->search); ?></code></pre>
          </div>


          <div class="insert">
<?php
  switch($operation->search->attributes()['position']) {
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
            <pre><code><?php echo functions::escape_html($operation->add); ?></code></pre>
          </div>
        </div>
        <?php } ?>

      </li>
      <?php } ?>
    </ul>
  </div>
</div>