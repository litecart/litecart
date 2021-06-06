<?php

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {
      if (empty($_POST['vmods'])) throw new Exception(language::translate('error_must_select_vmods', 'You must select vMods'));

      foreach ($_POST['vmods'] as $vmod) {

        if (!empty($_POST['enable'])) {
          if (!is_file(FS_DIR_STORAGE . 'vmods/' . pathinfo($vmod, PATHINFO_FILENAME) .'.disabled')) continue;
          rename(FS_DIR_STORAGE . 'vmods/' . pathinfo($vmod, PATHINFO_FILENAME) .'.disabled', FS_DIR_STORAGE . 'vmods/' . pathinfo($vmod, PATHINFO_FILENAME) .'.xml');
        } else {
          if (!is_file(FS_DIR_STORAGE . 'vmods/' . pathinfo($vmod, PATHINFO_FILENAME) .'.xml')) continue;
          rename(FS_DIR_STORAGE . 'vmods/' . pathinfo($vmod, PATHINFO_FILENAME) .'.xml', FS_DIR_STORAGE . 'vmods/' . pathinfo($vmod, PATHINFO_FILENAME) .'.disabled');
        }
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($_POST['vmods'])) throw new Exception(language::translate('error_must_select_vmods', 'You must select vMods'));

      foreach ($_POST['vmods'] as $vmod) {
        unlink(FS_DIR_STORAGE . 'vmods/' . pathinfo($vmod, PATHINFO_BASENAME));
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['upload'])) {

    try {
      if (!isset($_FILES['vmod']['tmp_name']) || !is_uploaded_file($_FILES['vmod']['tmp_name'])) {
        throw new Exception(language::translate('error_must_select_file_to_upload', 'You must select a file to upload'));
      }

      $dom = new DOMDocument('1.0', 'UTF-8');

      $xml = file_get_contents($_FILES['vmod']['tmp_name']); // DOMDocument::load() does not support Windows paths so we use DOMDocument::loadXML()

      if (!@$dom->loadXML($xml)) {
        throw new Exception(language::translate('error_invalid_xml_file', 'Invalid XML file'));
      }

      if (!$dom->getElementsByTagName('modification')) {
        throw new Exception(language::translate('error_xml_file_is_not_valid_vmod', 'XML file is not a valid vMod file'));
      }

      $filename = FS_DIR_STORAGE . 'vmods/' . pathinfo($_FILES['vmod']['name'], PATHINFO_FILENAME) .'.xml';

      if (is_file($filename)) {
        unlink($filename);
      }

      move_uploaded_file($_FILES['vmod']['tmp_name'], $filename);

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows
  $vmods = [];

  foreach (glob(FS_DIR_STORAGE . 'vmods/*.{xml,disabled}', GLOB_BRACE) as $file) {
    $xml = simplexml_load_file($file);
    $vmods[] = [
      'filename' => pathinfo($file, PATHINFO_BASENAME),
      'enabled' => preg_match('#\.xml$#', $file) ? true : false,
      'title' => $xml->title,
      'version' => $xml->version,
      'author' => $xml->author,
    ];
  }

// Number of Rows
  $num_rows = count($vmods);
?>

<div class="card card-app">
  <div class="card-heading">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_vmods', 'vMods'); ?>
    </div>
  </div>

  <div class="card-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::ilink('vmods/edit_vmod'), language::translate('title_create_new_vmod', 'Create New vMod'), '', 'add'); ?></li>
    </ul>
  </div>

  <div class="card-body">
    <?php echo functions::form_draw_form_begin('vmods_form', 'post'); ?>

      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
            <th></th>
            <th><?php echo language::translate('title_filename', 'Filename'); ?></th>
            <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
            <th><?php echo language::translate('title_version', 'Version'); ?></th>
            <th><?php echo language::translate('title_author', 'Author'); ?></th>
            <th></th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($vmods as $vmod) { ?>
          <tr class="<?php echo $vmod['enabled'] ? null : 'semi-transparent'; ?>">
            <td><?php echo functions::form_draw_checkbox('vmods['. $vmod['filename'] .']', $vmod['filename']); ?></td>
            <td><?php echo functions::draw_fonticon($vmod['enabled'] ? 'on' : 'off'); ?></td>
            <td><a href="<?php echo document::href_ilink('vmods/view', ['vmod' => $vmod['filename']]); ?>"><?php echo $vmod['filename']; ?></a></td>
            <td><?php echo $vmod['title']; ?></td>
            <td><?php echo $vmod['version']; ?></td>
            <td><?php echo $vmod['author']; ?></td>
            <td><a href="<?php echo document::href_ilink('vmods/test', ['vmod' => $vmod['filename']]); ?>"><strong><?php echo language::translate('title_test_now', 'Test Now'); ?></strong></a></td>
            <td><a href="<?php echo document::href_ilink('vmods/view', ['vmod' => $vmod['filename']]); ?>" title="<?php echo language::translate('title_view', 'View'); ?>"><?php echo functions::draw_fonticon('fa-search'); ?></a></td>
            <td><a href="<?php echo document::href_ilink('vmods/download', ['vmod' => $vmod['filename']]); ?>" title="<?php echo language::translate('title_download', 'Download'); ?>"><?php echo functions::draw_fonticon('fa-download'); ?></a></td>
            <td><a href="<?php echo document::href_ilink('vmods/configure', ['vmod' => $vmod['filename']]); ?>" title="<?php echo language::translate('title_configure', 'Configure'); ?>"><?php echo functions::draw_fonticon('fa-cog'); ?></a></td>
            <td><a href="<?php echo document::href_ilink('vmods/edit_vmod', ['vmod' => $vmod['filename']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
          </tr>
          <?php } ?>
        </tbody>

        <tfoot>
          <tr>
            <td colspan="11"><?php echo language::translate('title_vmods', 'vMods'); ?>: <?php echo $num_rows; ?></td>
          </tr>
        </tfoot>
      </table>

      <?php if ($vmods) { ?>
      <ul class="list-inline">
        <li>
          <div class="btn-group">
            <?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
            <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
          </div>
        </li>
        <li>
          <?php echo functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="'. htmlspecialchars('if(!confirm("'. language::translate('text_are_you_sure', 'Are you sure?') .'")) return false;') .'"', 'delete'); ?>
        </li>
      </p>
      <?php } ?>

    <?php echo functions::form_draw_form_end(); ?>

    <?php echo functions::form_draw_form_begin('vmod_form', 'post', '', true); ?>
      <div class="form-group" style="max-width: 320px;">
        <label><?php echo language::translate('title_upload_new_vmod', 'Upload a New vMod'); ?> (*.xml)</label>
        <div class="input-group">
          <?php echo functions::form_draw_file_field('vmod', 'accept="application/xml"'); ?>
          <?php echo functions::form_draw_button('upload', language::translate('title_upload', 'Upload'), 'submit'); ?>
        </div>
      </div>
    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>
