<?php
  if (!empty($_POST['enable']) || !empty($_POST['disable'])) {

    if (!empty($_POST['vqmods'])) {
      foreach ($_POST['vqmods'] as $key => $value) {
        if (!empty($_POST['enable'])) {
          rename(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmod/xml/'. pathinfo($value, PATHINFO_BASENAME), FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmod/xml/'. pathinfo($value, PATHINFO_FILENAME) .'.xml');
        } else {
          rename(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmod/xml/'. pathinfo($value, PATHINFO_BASENAME), FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmod/xml/'. pathinfo($value, PATHINFO_FILENAME) .'.disabled');
        }
      }
    }

    header('Location: '. document::ilink());
    exit;
  }

  if (!empty($_POST['delete'])) {

    if (!empty($_POST['vqmods'])) {
      foreach ($_POST['vqmods'] as $key => $value) {
        unlink(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmod/xml/'. pathinfo($value, PATHINFO_BASENAME));
      }
    }

    header('Location: '. document::ilink());
    exit;
  }

  if (!empty($_POST['upload'])) {
    if (empty($_FILES['vqmod']) || !in_array($_FILES['vqmod']['type'], array('text/xml', 'application/xml'))) notices::add('errors', language::translate('error_must_provide_vqmod', 'You must provide a valid vQmod file'));

    if (empty(notices::$data['errors'])) {
      move_uploaded_file($_FILES['vqmod']['tmp_name'], FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmod/xml/'. $_FILES['vqmod']['name']);
      header('Location: '. document::ilink());
      exit;
    }
  }
?>
<h1><?php echo $app_icon; ?> <?php echo language::translate('title_vqmods', 'vQmods'); ?></h1>

<?php echo functions::form_draw_form_begin('vqmods_form', 'post'); ?>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
        <th></th>
        <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
        <th><?php echo language::translate('title_code', 'Code'); ?></th>
        <th class="text-center"><?php echo language::translate('title_version', 'Version'); ?></th>
        <th><?php echo language::translate('title_author', 'Author'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
<?php

  $vqmods = glob(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'vqmod/xml/*.{xml,disabled}', GLOB_BRACE);

  if (!empty($vqmods)) {

    foreach ($vqmods as $vqmod) {

      $xml = simplexml_load_file($vqmod);
      $enabled = preg_match('/\.xml$/', $vqmod) ? true : false;
?>
    <tr class="<?php echo empty($enabled) ? 'semi-transparent' : null; ?>">
      <td><?php echo functions::form_draw_checkbox('vqmods['. htmlspecialchars($vqmod) .']', $vqmod); ?></td>
      <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. ($enabled ? '#99cc66' : '#ff6666') .';"'); ?></td>
      <td><?php echo (string)$xml->id; ?></td>
      <td><?php echo pathinfo($vqmod, PATHINFO_FILENAME); ?></td>
      <td><?php echo (string)$xml->version; ?></td>
      <td><?php echo (string)$xml->author; ?></td>
      <td><a href="<?php echo document::href_link(null, array('doc' => 'download', 'vqmod' => basename($vqmod)), true); ?>" title="<?php echo language::translate('title_download', 'Download'); ?>"><?php echo functions::draw_fonticon('fa-download fa-lg'); ?></a></td>
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
  <div class="row" style="margin-top: 2em;">
    <div class="form-group col-md-3">
      <label><?php echo language::translate('title_upload_new_vqmod', 'Upload a New vQmod'); ?> (*.xml)</label>
      <?php echo functions::form_draw_file_field('vqmod', 'accept="application/xml"'); ?>
    </div>
  </div>

  <?php echo functions::form_draw_button('upload', language::translate('title_upload', 'Upload'), 'submit'); ?>

<?php echo functions::form_draw_form_end(); ?>