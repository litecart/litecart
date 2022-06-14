<?php

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {
      if (empty($_POST['vmods'])) throw new Exception(language::translate('error_must_select_vmods', 'You must select vMods'));

      foreach ($_POST['vmods'] as $vmod) {

        if (!empty($_POST['enable'])) {
          if (!is_file('storage://vmods/' . pathinfo($vmod, PATHINFO_FILENAME) .'.disabled')) continue;
          rename('storage://vmods/' . pathinfo($vmod, PATHINFO_FILENAME) .'.disabled', 'storage://vmods/' . pathinfo($vmod, PATHINFO_FILENAME) .'.xml');
        } else {
          if (!is_file('storage://vmods/' . pathinfo($vmod, PATHINFO_FILENAME) .'.xml')) continue;
          rename('storage://vmods/' . pathinfo($vmod, PATHINFO_FILENAME) .'.xml', 'storage://vmods/' . pathinfo($vmod, PATHINFO_FILENAME) .'.disabled');
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
        unlink('storage://vmods/' . pathinfo($vmod, PATHINFO_BASENAME));
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

      $filename = 'storage://vmods/' . pathinfo($_FILES['vmod']['name'], PATHINFO_FILENAME) .'.xml';

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

//var_dump(functions::file_search('storage://addons/*/vmod.xml'));exit;
  foreach (functions::file_search('storage://addons/*/vmod.xml') as $file) {
    $xml = simplexml_load_file($file);
    $vmods[] = [
      'id' => preg_replace('#^storage://addons/([^/]+)(\.disabled)?/.*$#', '$1', $file),
      'status' => preg_match('#/addons/([^/]+)(\.disabled)/#', $file) ? false : true,
      'location' => $file,
      'type' => ($xml->getName() == 'vmod') ? 'vMod' : 'VQmod',
      'title' => $xml->title,
      'version' => $xml->version,
      'author' => $xml->author,
      'configurable' => !empty($xml->setting) ? true : false,
    ];
  }

// Number of Rows
  $num_rows = count($vmods);
?>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_vmods', 'vMods'); ?>
    </div>
  </div>

  <div class="card-action">
    <?php echo functions::form_draw_link_button(document::ilink(__APP__.'/edit_vmod'), language::translate('title_create_new_vmod', 'Create New vMod'), '', 'add'); ?>
  </div>

  <?php echo functions::form_draw_form_begin('vmod_form', 'post', '', true); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th></th>
          <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
          <th><?php echo language::translate('title_version', 'Version'); ?></th>
          <th><?php echo language::translate('title_location', 'Location'); ?></th>
          <th><?php echo language::translate('title_author', 'Author'); ?></th>
          <th><?php echo language::translate('title_type', 'Type'); ?></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($vmods as $vmod) { ?>
        <tr class="<?php echo $vmod['status'] ? null : 'semi-transparent'; ?>">
          <td><?php echo functions::form_draw_checkbox('vmods[]', $vmod['id']); ?></td>
          <td><?php echo functions::draw_fonticon($vmod['status'] ? 'on' : 'off'); ?></td>
          <td><a href="<?php echo document::href_ilink(__APP__.'/view', ['vmod' => $vmod['id']]); ?>"><?php echo $vmod['title']; ?></a></td>
          <td><?php echo $vmod['version']; ?></td>
          <td><?php echo $vmod['location']; ?></td>
          <td><?php echo $vmod['author']; ?></td>
          <td class="text-center"><?php echo $vmod['type']; ?></td>
          <td><a href="<?php echo document::href_ilink(__APP__.'/test', ['vmod' => $vmod['id']]); ?>"><strong><?php echo language::translate('title_test_now', 'Test Now'); ?></strong></a></td>
          <td><?php if ($vmod['configurable']) { ?><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/configure', ['vmod' => $vmod['filename']]); ?>" title="<?php echo language::translate('title_configure', 'Configure'); ?>"><?php echo functions::draw_fonticon('fa-cog'); ?></a><?php } ?></td>
          <td><?php if ($vmod['type'] == 'vMod') { ?><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/view', ['vmod' => $vmod['id']]); ?>" title="<?php echo language::translate('title_view', 'View'); ?>"><?php echo functions::draw_fonticon('fa-search'); ?></a><?php } ?></td>
          <td>
            <a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/download', ['vmod' => $vmod['id']]); ?>" title="<?php echo language::translate('title_download', 'Download'); ?>"><?php echo functions::draw_fonticon('fa-download'); ?></a>
            <a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_vmod', ['vmod' => $vmod['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a>
          </td>
        </tr>
        <?php } ?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="11"><?php echo language::translate('title_vmods', 'vMods'); ?>: <?php echo language::number_format($num_rows); ?></td>
        </tr>
      </tfoot>
    </table>

    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <fieldset id="actions">
            <legend><?php echo language::translate('text_with_selected', 'With selected'); ?>:</legend>

            <ul class="list-inline">
              <li>
                <div class="btn-group">
                  <?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
                  <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
                </div>
              </li>
              <li>
                <?php echo functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'class="btn btn-danger" onclick="'. functions::escape_html('if(!confirm("'. language::translate('text_are_you_sure', 'Are you sure?') .'")) return false;') .'"', 'delete'); ?>
              </li>
            </ul>
          </fieldset>
      </div>

      <div class="col-md-6">
        <fieldset>
          <legend><?php echo language::translate('title_upload_new_vmod', 'Upload a New vMod'); ?>:</legend>

          <div class="input-group">
            <?php echo functions::form_draw_file_field('vmod', 'accept="application/xml"'); ?>
            <?php echo functions::form_draw_button('upload', language::translate('title_upload', 'Upload'), 'submit'); ?>
          </div>
        </fieldset>
      </div>
    </div>

  <?php echo functions::form_draw_form_end(); ?>
</div>

<script>
  $('.data-table :checkbox').change(function() {
    $('#actions').prop('disabled', !$('.data-table :checked').length);
  }).first().trigger('change');
</script>