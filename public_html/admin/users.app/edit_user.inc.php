<?php

  if (!empty($_GET['user_id'])) {
    $user = new ctrl_user($_GET['user_id']);
  } else {
    $user = new ctrl_user();
  }

  if (empty($_POST)) {
    foreach ($user->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  breadcrumbs::add(!empty($user->data['username']) ? language::translate('title_edit_user', 'Edit User') : language::translate('title_create_new_user', 'Create New User'));

  if (isset($_POST['save'])) {

    if (empty($_POST['username'])) notices::add('errors', language::translate('error_must_enter_username', 'You must enter a username'));

    if (empty($user->data['username'])) {
      if (empty($_POST['password'])) notices::add('errors', language::translate('error_must_enter_password', 'You must enter a password'));
      if (empty($_POST['confirmed_password'])) notices::add('errors', language::translate('error_must_enter_confirmed_password', 'You must confirm the password'));
    }

    if (!empty($_POST['password']) && !empty($_POST['confirmed_password']) && $_POST['password'] != $_POST['confirmed_password']) notices::add('errors', language::translate('error_passwords_missmatch', 'The passwords did not match'));

    if (empty(notices::$data['errors'])) {

      if (empty($_POST['status'])) $_POST['status'] = 0;
      if (empty($_POST['permissions'])) $_POST['permissions'] = array();

      $fields = array(
        'status',
        'username',
        'password',
        'permissions',
        'date_blocked',
        'date_expires',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $user->data[$field] = $_POST[$field];
      }

      if (!empty($_POST['password'])) $user->set_password($_POST['password']);

      $user->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('', array('doc' => 'users'), array('app')));
      exit;
    }
  }

  if (isset($_POST['delete'])) {

    if (!empty($user->data['id'])) {

      $user->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('', array('doc' => 'users'), array('app')));
      exit;
    }
  }

?>
<h1><?php echo $app_icon; ?> <?php echo !empty($user->data['username']) ? language::translate('title_edit_user', 'Edit User') : language::translate('title_create_new_user', 'Create New User'); ?></h1>

<?php echo functions::form_draw_form_begin('user_form', 'post', false, false, 'style="max-width: 960px;"'); ?>

  <div class="row">

    <div class="col-md-8">
      <div class="row">
        <div class="form-group col-sm-6">
          <label><?php echo language::translate('title_status', 'Status'); ?></label>
          <?php echo functions::form_draw_toggle('status', (isset($_POST['status'])) ? $_POST['status'] : '1', 'e/d'); ?>
        </div>

        <div class="form-group col-sm-6">
          <label><?php echo language::translate('title_username', 'Username'); ?></label>
          <?php echo functions::form_draw_text_field('username', true, 'required="required"'); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-sm-6">
          <label><?php echo language::translate('title_new_password', 'New Password'); ?></label>
          <?php echo functions::form_draw_password_field('password', '', 'autocomplete="off"'); ?>
        </div>

        <div class="form-group col-sm-6">
          <label><?php echo language::translate('title_confirm_password', 'Confirm Password'); ?></label>
          <?php echo functions::form_draw_password_field('confirmed_password', '', 'autocomplete="off"'); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-sm-6">
          <label><?php echo language::translate('title_blocked_until', 'Blocked Until'); ?></label>
          <?php echo functions::form_draw_datetime_field('date_blocked', true); ?>
        </div>

        <div class="form-group col-sm-6">
          <label><?php echo language::translate('title_expires', 'Expires'); ?></label>
          <?php echo functions::form_draw_datetime_field('date_expires', true); ?>
        </div>
      </div>

      <div class="row">
        <?php if (!empty($user->data['id'])) { ?>
        <div class="form-group col-sm-6">
          <label><?php echo language::translate('title_last_ip', 'Last IP'); ?></label>
          <?php echo functions::form_draw_text_field('last_ip', true, 'disabled="disabled"'); ?>
        </div>

        <div class="form-group col-sm-6">
          <label><?php echo language::translate('title_last_host', 'Last Host'); ?></label>
          <?php echo functions::form_draw_text_field('last_host', true, 'disabled="disabled"'); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-sm-6">
          <label><?php echo language::translate('title_last_login', 'Last Login'); ?></label>
          <?php echo functions::form_draw_text_field('date_login', true, 'disabled="disabled"'); ?>
        </div>
        <?php } ?>
      </div>
    </div>

    <div class="col-md-4">
      <div class="form-group">
        <label><?php echo functions::form_draw_checkbox('permissions_toggle', '1', !empty($_POST['permissions']) ? '1' : '0'); ?> <?php echo language::translate('title_permissions', 'Permissions'); ?></label>
        <div class="form-control" style="height: 400px; overflow-y: scroll;">
          <ul class="list-unstyled">
<?php
  $apps = functions::admin_get_apps();
  foreach ($apps as $app) {
    echo '  <li>' . PHP_EOL
       //. '    ' . functions::draw_fonticon('fa-check-square-o checkbox-toggle') . PHP_EOL
       . '    <label>'. functions::form_draw_checkbox('permissions['.$app['code'].'][status]', '1', true) .' '. $app['name'] .'</label>' . PHP_EOL;
    if (!empty($app['docs'])) {
      echo '    <ul class="">' . PHP_EOL;
      foreach ($app['docs'] as $doc => $file) {
        echo '      <li><label>'. functions::form_draw_checkbox('permissions['.$app['code'].'][docs][]', $doc, true) .' '. $doc .'</label>' . PHP_EOL;
      }
      echo '    </ul>' . PHP_EOL;
    }
    echo '  </li>' . PHP_EOL;
  }
?>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <p class="btn-group">
    <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
    <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
    <?php echo (!empty($user->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>

<script>
  $('input[name="permissions_toggle"]').change(function(){
    if ($(this).is(':checked')) {
      $(this).closest('.form-group').find('input[name^="permissions"][name$="[status]"]').removeAttr('disabled');
    } else {
      $(this).closest('.form-group').find('input[name^="permissions"][name$="[status]"]').attr('disabled', 'disabled').prop('checked', false).trigger('change');
    }
  }).trigger('change');

  $('input[name^="permissions"][name$="[status]"]').change(function(){
    if ($(this).is(':checked')) {
      $(this).closest('li').find('input[name^="permissions"][name$="[docs][]"]').removeAttr('disabled').prop('checked', true);
    } else {
      $(this).closest('li').find('input[name^="permissions"][name$="[docs][]"]').attr('disabled', 'disabled').prop('checked', false);
    }
  }).trigger('change');
</script>