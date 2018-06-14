<?php
  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {
      if (empty($_POST['vqmods'])) throw new Exception(language::translate('error_must_select_vqmods', 'You must select vQmods'));

      foreach ($_POST['vqmods'] as $vqmod) {

        if (!empty($_POST['enable'])) {
          if (!is_file(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmod/xml/'. pathinfo($vqmod, PATHINFO_FILENAME) .'.disabled')) continue;
          rename(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmod/xml/'. pathinfo($vqmod, PATHINFO_FILENAME) .'.disabled', FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmod/xml/'. pathinfo($vqmod, PATHINFO_FILENAME) .'.xml');
        } else {
          if (!is_file(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmod/xml/'. pathinfo($vqmod, PATHINFO_FILENAME) .'.xml')) continue;
          rename(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmod/xml/'. pathinfo($vqmod, PATHINFO_FILENAME) .'.xml', FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmod/xml/'. pathinfo($vqmod, PATHINFO_FILENAME) .'.disabled');
        }
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved successfully'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($_POST['vqmods'])) throw new Exception(language::translate('error_must_select_vqmods', 'You must select vQmods'));

      foreach ($_POST['vqmods'] as $vqmod) {
        unlink(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmod/xml/'. pathinfo($vqmod, PATHINFO_BASENAME));
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved successfully'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['upload'])) {

    try {
      if (!isset($_FILES['vqmod']['tmp_name']) || !is_uploaded_file($_FILES['vqmod']['tmp_name'])) {
        throw new Exception(language::translate('error_must_select_file_to_upload', 'You must select a file to upload'));
      }

      $dom = new DOMDocument('1.0', 'UTF-8');

      $xml = file_get_contents($_FILES['vqmod']['tmp_name']); // DOMDocument::load() does not support Windows paths so we use DOMDocument::loadXML()

      if (!@$dom->loadXML($xml)) {
        throw new Exception(language::translate('error_invalid_xml_file', 'Invalid XML file'));
      }

      if (!$dom->getElementsByTagName('modification')) {
        throw new Exception(language::translate('error_xml_file_is_not_valid_vqmod', 'XML file is not a valid vQmod file'));
      }

      $filename = FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmod/xml/'. pathinfo($_FILES['vqmod']['name'], PATHINFO_FILENAME) .'.xml';

      if (is_file($filename)) {
        unlink($filename);
      }

      move_uploaded_file($_FILES['vqmod']['tmp_name'], $filename);

      notices::add('success', language::translate('success_changes_saved', 'Changes saved successfully'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

?>
<h1><?php echo $app_icon; ?> <?php echo language::translate('title_vqmods', 'vQmods'); ?></h1>

<?php echo functions::form_draw_form_begin('vqmods_form', 'post'); ?>

  <table class="table table-striped table-hover data-table">
    <thead>
      <tr>
        <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
        <th></th>
        <th><?php echo language::translate('title_file', 'File'); ?></th>
        <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
        <th class="text-center"><?php echo language::translate('title_version', 'Version'); ?></th>
        <th><?php echo language::translate('title_author', 'Author'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
<?php
  if ($vqmods = glob(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'vqmod/xml/*.{xml,disabled}', GLOB_BRACE)) {
    foreach ($vqmods as $vqmod) {
      $filename = pathinfo($vqmod, PATHINFO_BASENAME);
      $xml = simplexml_load_file($vqmod);
      $enabled = preg_match('#\.xml$#', $vqmod) ? true : false;
?>
    <tr class="<?php echo empty($enabled) ? 'semi-transparent' : null; ?>">
      <td><?php echo functions::form_draw_checkbox('vqmods['. $filename .']', $filename); ?></td>
      <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. ($enabled ? '#88cc44' : '#ff6644') .';"'); ?></td>
      <td><?php echo $filename; ?></td>
      <td><?php echo $xml->id; ?></td>
      <td><?php echo $xml->version; ?></td>
      <td><?php echo $xml->author; ?></td>
      <td><a href="<?php echo document::href_link(null, array('doc' => 'download', 'vqmod' => $filename), true); ?>" title="<?php echo language::translate('title_download', 'Download'); ?>"><?php echo functions::draw_fonticon('fa-download fa-lg'); ?></a></td>
    </tr>
<?php
    }
  }
?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="7"><?php echo language::translate('title_vqmods', 'vQmods'); ?>: <?php echo count($vqmods); ?></td>
      </tr>
    </tfoot>
  </table>

  <?php if (count($vqmods)) { ?>
  <p>
    <span class="btn-group">
      <?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
      <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
    </span>
    <?php echo functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="'. htmlspecialchars('if(!confirm("'. language::translate('text_are_you_sure', 'Are you sure?') .'")) return false;') .'"', 'delete'); ?>
  </p>
  <?php } ?>

<?php echo functions::form_draw_form_end(); ?>

<?php echo functions::form_draw_form_begin('vqmod_form', 'post', '', true); ?>
  <div class="form-group" style="max-width: 320px;">
    <label><?php echo language::translate('title_upload_new_vqmod', 'Upload a New vQmod'); ?> (*.xml)</label>
    <div class="input-group">
      <?php echo functions::form_draw_file_field('vqmod', 'accept="application/xml"'); ?>
      <span class="input-group-btn"><?php echo functions::form_draw_button('upload', language::translate('title_upload', 'Upload'), 'submit'); ?></span>
    </div>
  </div>
<?php echo functions::form_draw_form_end(); ?>