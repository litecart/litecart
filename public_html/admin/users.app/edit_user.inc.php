<?php

  if (!empty($_GET['user_id'])) {
    $user = new ent_user($_GET['user_id']);
  } else {
    $user = new ent_user();
  }

  if (empty($_POST)) {
    $_POST = $user->data;
  }

  document::$snippets['title'][] = !empty($user->data['username']) ? language::translate('title_edit_user', 'Edit User') : language::translate('title_create_new_user', 'Create New User');

  breadcrumbs::add(language::translate('title_users', 'Users'), document::href_link(WS_DIR_ADMIN, ['doc' => 'users'], ['app']));
  breadcrumbs::add(!empty($user->data['username']) ? language::translate('title_edit_user', 'Edit User') : language::translate('title_create_new_user', 'Create New User'));

  if (isset($_POST['save'])) {

    try {

      if (empty($_POST['username'])) throw new Exception(language::translate('error_must_enter_username', 'You must enter a username'));
      if (empty($user->data['id']) && empty($_POST['password'])) throw new Exception(language::translate('error_must_enter_password', 'You must enter a password'));
      if (!empty($_POST['password']) && empty($_POST['confirmed_password'])) throw new Exception(language::translate('error_must_enter_confirmed_password', 'You must confirm the password'));
      if (!empty($_POST['password']) && $_POST['password'] != $_POST['confirmed_password']) throw new Exception(language::translate('error_passwords_missmatch', 'The passwords did not match'));

      if (empty($_POST['apps'])) $_POST['apps'] = [];
      if (empty($_POST['widgets'])) $_POST['widgets'] = [];

      $fields = [
        'status',
        'username',
        'email',
        'password',
        'apps',
        'widgets',
        'date_valid_from',
        'date_valid_to',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $user->data[$field] = $_POST[$field];
      }

      if (!empty($_POST['password'])) $user->set_password($_POST['password']);

      $user->data['user_security_timestamp'] = date('Y-m-d H:i:s');

      $user->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'users'], ['app']));
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
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'users'], ['app']));
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
              <?php echo functions::form_draw_text_field('username', true, 'autocomplete="off" required'); ?>
            </div>

            <div class="form-group col-sm-6">
              <label><?php echo language::translate('title_email', 'Email'); ?></label>
              <?php echo functions::form_draw_email_field('email', true, 'autocomplete="off"'); ?>
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
              <?php echo functions::form_draw_text_field('last_ip', true, 'readonly'); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_host', 'Last Host'); ?></label>
              <?php echo functions::form_draw_text_field('last_host', true, 'readonly'); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_login', 'Last Login'); ?></label>
              <?php echo functions::form_draw_text_field('date_login', true, 'readonly'); ?>
            </div>
          </div>
          <?php } ?>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label><?php echo functions::form_draw_checkbox('apps_toggle', '1', !empty($_POST['apps']) ? '1' : '0'); ?> <?php echo language::translate('title_apps', 'Apps'); ?></label>
            <div class="form-control" style="height: 400px; overflow-y: scroll;">
              <ul class="list-unstyled">
<?php
  $apps = functions::admin_get_apps();
  foreach ($apps as $app) {
    echo '  <li>' . PHP_EOL
       . '    <label>'. functions::form_draw_checkbox('apps['.$app['code'].'][status]', '1', true) .' '. $app['name'] .'</label>' . PHP_EOL;
    if (!empty($app['docs'])) {
      echo '    <ul class="">' . PHP_EOL;
      foreach ($app['docs'] as $doc => $file) {
        echo '      <li><label>'. functions::form_draw_checkbox('apps['.$app['code'].'][docs][]', $doc, true) .' '. $doc .'</label>' . PHP_EOL;
      }
      echo '    </ul>' . PHP_EOL;
    }
    echo '  </li>' . PHP_EOL;
  }
?>
              </ul>
            </div>
          </div>

          <div class="form-group">
            <label><?php echo functions::form_draw_checkbox('widgets_toggle', '1', !empty($_POST['widgets']) ? '1' : '0'); ?> <?php echo language::translate('title_widgets', 'Widgets'); ?></label>
            <div class="form-control" style="height: 150px; overflow-y: scroll;">
              <ul class="list-unstyled">
<?php
  $widgets = functions::admin_get_widgets();
  foreach ($widgets as $widget) {
    echo '  <li>' . PHP_EOL
       . '    <label>'. functions::form_draw_checkbox('widgets['.$widget['code'].']', '1', true) .' '. $widget['name'] .'</label>' . PHP_EOL
       . '  </li>' . PHP_EOL;
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
        <?php echo (!empty($user->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<script>
  $('input[name="apps_toggle"]').change(function(){
    if ($(this).is(':checked')) {
      $('input[name^="apps"][name$="[status]"]').prop('disabled', false);
      $('input[name^="apps"][name$="[docs][]"]').prop('disabled', false);
    } else {
      $('input[name^="apps"][name$="[status]"]').prop('disabled', true);
      $('input[name^="apps"][name$="[docs][]"]').prop('disabled', true);
    }
  }).trigger('change');

  $('input[name^="apps"][name$="[status]"]').change(function(){
    if ($(this).is(':checked')) {
      if (!$(this).closest('li').find('input[name^="apps"][name$="[docs][]"]:checked').length) {
        $(this).closest('li').find('input[name^="apps"][name$="[docs][]"]').prop('checked', true);
      }
    } else {
      $(this).closest('li').find('input[name^="apps"][name$="[docs][]"]').prop('checked', false);
    }
  }).trigger('change');

  $('input[name="widgets_toggle"]').change(function(){
    if ($(this).is(':checked')) {
      $('input[name^="widgets["]').prop('disabled', false);
    } else {
      $('input[name^="widgets["]').prop('disabled', true);
    }
  }).trigger('change');
</script>