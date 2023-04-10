<?php

  if (!empty($_GET['user_id'])) {
    $user = new ent_user($_GET['user_id']);
  } else {
    $user = new ent_user();
  }

  if (!$_POST) {
    $_POST = $user->data;
  }

  document::$snippets['title'][] = !empty($user->data['username']) ? language::translate('title_edit_user', 'Edit User') : language::translate('title_create_new_user', 'Create New User');

  breadcrumbs::add(language::translate('title_users', 'Users'), document::href_ilink(__APP__.'/users'));
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
      header('Location: '. document::ilink(__APP__.'/users'));
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
      header('Location: '. document::ilink(__APP__.'/users'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<style>
#app-permissions li,
#widget-permissions li {
  padding: .25em 0;
}
</style>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($user->data['username']) ? language::translate('title_edit_user', 'Edit User') : language::translate('title_create_new_user', 'Create New User'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_begin('user_form', 'post', false, false, 'autocomplete="off" style="max-width: 960px;"'); ?>

      <div class="row">

        <div class="col-md-8">
          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_status', 'Status'); ?></label>
              <?php echo functions::form_toggle('status', 'e/d', (isset($_POST['status'])) ? $_POST['status'] : '1'); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-sm-6">
              <label><?php echo language::translate('title_username', 'Username'); ?></label>
              <?php echo functions::form_text_field('username', true, 'autocomplete="off" required'); ?>
            </div>

            <div class="form-group col-sm-6">
              <label><?php echo language::translate('title_email', 'Email'); ?></label>
              <?php echo functions::form_email_field('email', true, 'autocomplete="off"'); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_new_password', 'New Password'); ?></label>
              <?php echo functions::form_password_field('password', '', 'autocomplete="new-password"'); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_confirm_password', 'Confirm Password'); ?></label>
              <?php echo functions::form_password_field('confirmed_password', '', 'autocomplete="new-password"'); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_valid_from', 'Valid From'); ?></label>
              <?php echo functions::form_datetime_field('date_valid_from', true); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_valid_to', 'Valid To'); ?></label>
              <?php echo functions::form_datetime_field('date_valid_to', true); ?>
            </div>
          </div>

          <?php if (!empty($user->data['id'])) { ?>
          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_ip_address', 'Last IP Address'); ?></label>
              <?php echo functions::form_text_field('last_ip_address', true, 'readonly'); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_hostname', 'Last Hostname'); ?></label>
              <?php echo functions::form_text_field('last_hostname', true, 'readonly'); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_login', 'Last Login'); ?></label>
              <?php echo functions::form_text_field('date_login', true, 'readonly'); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_active', 'Last Active'); ?></label>
              <?php echo functions::form_text_field('date_active', true, 'readonly'); ?>
          </div>
          </div>
          <?php } ?>
        </div>

        <div class="col-md-4">
          <div id="app-permissions" class="form-group">
            <?php echo functions::form_checkbox('apps_toggle', ['1', language::translate('title_apps', 'Apps')]); ?>
            <div class="form-input" style="height: 400px; overflow-y: scroll;">
              <ul class="list-unstyled">
<?php
  $apps = functions::admin_get_apps();
  foreach ($apps as $app) {
    echo '  <li data-app="'. functions::escape_html($app['id']) .'">' . PHP_EOL
       . '  '. functions::form_checkbox('apps['.$app['id'].'][status]', ['1', $app['name']], true) . PHP_EOL;
    if (!empty($app['docs'])) {
      echo '  <ul class="list-unstyled">' . PHP_EOL;
      foreach ($app['docs'] as $doc => $file) {
        echo '    <li data-doc="'. functions::escape_html($doc) .'"><label>'. functions::form_checkbox('apps['.$app['id'].'][docs][]', $doc, true) .' '. $doc .'</label>' . PHP_EOL;
      }
      echo '  </ul>' . PHP_EOL;
    }
    echo '</li>' . PHP_EOL;
  }
?>
              </ul>
            </div>
          </div>

          <div id="widget-permissions" class="form-group">
            <?php echo functions::form_checkbox('widgets_toggle', ['1', language::translate('title_widgets', 'Widgets')]); ?>
            <div class="form-input" style="height: 150px; overflow-y: scroll;">
              <ul class="list-unstyled">
<?php
  $widgets = functions::admin_get_widgets();
  foreach ($widgets as $widget) {
    echo '<li>' . PHP_EOL
       . '  '. functions::form_checkbox('widgets['.$widget['id'].']', ['1', $widget['name']], true) . PHP_EOL
       . '</li>' . PHP_EOL;
  }
?>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="card-action">
        <?php echo functions::form_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
        <?php echo !empty($user->data['id']) ? functions::form_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
        <?php echo functions::form_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_end(); ?>
  </div>
</div>

<script>
  $('input[name="apps_toggle"]').change(function(){
    $('input[name^="apps"][name$="[status]"]').prop('disabled', !$(this).is(':checked'));
    $('input[name^="apps"][name$="[docs][]"]').prop('disabled', !$(this).is(':checked'));
  }).trigger('change');

  $('input[name^="apps"][name$="[status]"]').change(function(){
    if ($(this).prop('checked')) {
      if (!$(this).closest('[data-app]').find('ul :input:checked').length) {
        $(this).closest('[data-app]').find('ul :input').prop('checked', true);
      }
    } else {
      $(this).closest('[data-app]').find('ul :input').prop('checked', false);
    }
  });

  $('input[name^="apps"][name$="[docs][]"]').change(function() {
    if ($(this).is(':checked')) {
      $(this).closest('ul').closest('[data-app]').children().not('ul').find(':input').prop('checked', true);
    }
  });

  $('input[name="widgets_toggle"]').change(function(){
    $('input[name^="widgets["]').prop('disabled', !$(this).is(':checked'));
  }).trigger('change');
</script>