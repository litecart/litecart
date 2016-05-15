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
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_vqmods', 'vQmods'); ?></h1>

<?php echo functions::form_draw_form_begin('vqmods_form', 'post'); ?>

  <table width="100%" align="center" class="dataTable">
    <tr class="header">
      <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle'); ?></th>
      <th></th>
      <th width="100%"><?php echo language::translate('title_name', 'Name'); ?></th>
      <th><?php echo language::translate('title_code', 'Code'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_version', 'Version'); ?></th>
      <th><?php echo language::translate('title_author', 'Author'); ?></th>
      <th>&nbsp;</th>
    </tr>
<?php

  $vqmods = glob(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'vqmod/xml/*.{xml,disabled}', GLOB_BRACE);

  if (!empty($vqmods)) {

    foreach ($vqmods as $vqmod) {

      $xml = simplexml_load_file($vqmod);
      $enabled = preg_match('/\.xml$/', $vqmod) ? true : false;
?>
    <tr class="row<?php echo !$enabled ? ' semi-transparent' : null; ?>">
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
    <tr class="footer">
      <td colspan="7"><?php echo language::translate('title_vqmods', 'vQmods'); ?>: <?php echo count($vqmods); ?></td>
    </tr>
  </table>

  <script>
    $(".dataTable .checkbox-toggle").click(function() {
      $(this).closest("form").find(":checkbox").each(function() {
        $(this).attr('checked', !$(this).attr('checked'));
      });
      $(".dataTable .checkbox-toggle").attr("checked", true);
    });

    $('.dataTable tr').click(function(event) {
      if ($(event.target).is('input:checkbox')) return;
      if ($(event.target).is('a, a *')) return;
      if ($(event.target).is('th')) return;
      $(this).find('input:checkbox').trigger('click');
    });
  </script>

  <p><span class="button-set"><?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?> <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?></span> <?php echo functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="'. htmlspecialchars('if(!confirm("'. language::translate('text_are_you_sure', 'Are you sure?') .'")) return false;') .'"', 'delete'); ?></p>

  <?php echo functions::form_draw_form_end(); ?>

  <fieldset style="display: inline; border: 1px solid #ccc;">
    <legend><?php echo language::translate('title_upload_new_vqmod', 'Upload a New vQmod'); ?> (*.xml)</legend>
    <?php echo functions::form_draw_form_begin('vqmod_form', 'post', '', true) . functions::form_draw_file_field('vqmod', 'accept="application/xml"') .' '. functions::form_draw_button('upload', language::translate('title_upload', 'Upload'), 'submit') . functions::form_draw_form_end(); ?>
  </fieldset>