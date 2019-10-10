<?php

  if (!empty($_GET['user_id'])) {
    $user = new ent_user($_GET['user_id']);
  } else {
    $user = new ent_user();
  }

  if (empty($_POST)) {
    foreach ($user->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  breadcrumbs::add(!empty($user->data['username']) ? language::translate('title_edit_user', 'Edit User') : language::translate('title_create_new_user', 'Create New User'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['status'])) $_POST['status'] = 0;
      if (empty($_POST['permissions'])) $_POST['permissions'] = array();

      if (empty($_POST['username'])) throw new Exception(language::translate('error_must_enter_username', 'You must enter a username'));
      if (empty($user->data['id']) && empty($_POST['password'])) throw new Exception(language::translate('error_must_enter_password', 'You must enter a password'));
      if (!empty($_POST['password']) && empty($_POST['confirmed_password'])) throw new Exception(language::translate('error_must_enter_confirmed_password', 'You must confirm the password'));
      if (!empty($_POST['password']) && $_POST['password'] != $_POST['confirmed_password']) throw new Exception(language::translate('error_passwords_missmatch', 'The passwords did not match'));

      $fields = array(
        'status',
        'username',
        'email',
        'password',
        'permissions',
        'date_valid_from',
        'date_valid_to',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $user->data[$field] = $_POST[$field];
      }

      if (!empty($_POST['password'])) $user->set_password($_POST['password']);

      $user->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, array('doc' => 'users'), array('app')));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($user->data['id'])) throw new Exception(language::translate('error_must_provide_user', 'You must provide a user'));

      $user->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, array('doc' => 'users'), array('app')));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo !empty($user->data['username']) ? language::translate('title_edit_user', 'Edit User') : language::translate('title_create_new_user', 'Create New User'); ?>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('user_form', 'post', false, false, 'autocomplete="off" style="max-width: 960px;"'); ?>

      <div class="row">

        <div class="col-md-8">
          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_status', 'Status'); ?></label>
              <?php echo functions::form_draw_toggle('status', (isset($_POST['status'])) ? $_POST['status'] : '1', 'e/d'); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-sm-6">
              <label><?php echo language::translate('title_username', 'Username'); ?></label>
              <?php echo functions::form_draw_text_field('username', true, 'required="required"'); ?>
            </div>

            <div class="form-group col-sm-6">
              <label><?php echo language::translate('title_email', 'Email'); ?></label>
              <?php echo functions::form_draw_email_field('email', true); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_new_password', 'New Password'); ?></label>
              <?php echo functions::form_draw_password_field('password', '', 'autocomplete="off"'); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_confirm_password', 'Confirm Password'); ?></label>
              <?php echo functions::form_draw_password_field('confirmed_password', '', 'autocomplete="off"'); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_valid_from', 'Valid From'); ?></label>
              <?php echo functions::form_draw_datetime_field('date_valid_from', true); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_valid_to', 'Valid To'); ?></label>
              <?php echo functions::form_draw_datetime_field('date_valid_to', true); ?>
            </div>
          </div>

          <?php if (!empty($user->data['id'])) { ?>
          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_ip', 'Last IP'); ?></label>
              <?php echo functions::form_draw_text_field('last_ip', true, 'readonly="readonly"'); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_host', 'Last Host'); ?></label>
              <?php echo functions::form_draw_text_field('last_host', true, 'readonly="readonly"'); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_login', 'Last Login'); ?></label>
              <?php echo functions::form_draw_text_field('date_login', true, 'readonly="readonly"'); ?>
            </div>
          </div>
          <?php } ?>
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

      <div class="panel-action btn-group">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        <?php echo (!empty($user->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<script>
  $('input[name="permissions_toggle"]').change(function(){
    if ($(this).is(':checked')) {
      $('input[name^="permissions"][name$="[status]"]').removeAttr('disabled');
      $('input[name^="permissions"][name$="[docs][]"]').removeAttr('disabled');
    } else {
      $('input[name^="permissions"][name$="[status]"]').attr('disabled', 'disabled');
      $('input[name^="permissions"][name$="[docs][]"]').attr('disabled', 'disabled');
    }
  }).trigger('change');

  $('input[name^="permissions"][name$="[status]"]').change(function(){
    if ($(this).is(':checked')) {
      if (!$(this).closest('li').find('input[name^="permissions"][name$="[docs][]"]:checked').length) {
        $(this).closest('li').find('input[name^="permissions"][name$="[docs][]"]').prop('checked', true);
      }
    } else {
      $(this).closest('li').find('input[name^="permissions"][name$="[docs][]"]').prop('checked', false);
    }
  }).trigger('change');
</script>