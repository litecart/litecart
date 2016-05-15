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

      $fields = array(
        'status',
        'username',
        'password',
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
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo !empty($user->data['username']) ? language::translate('title_edit_user', 'Edit User') : language::translate('title_create_new_user', 'Create New User'); ?></h1>

<?php echo functions::form_draw_form_begin(false, 'post'); ?>

  <table>
    <tr>
      <td colspan="2"><strong><?php echo language::translate('title_status', 'Status'); ?></strong><br />
        <label><?php echo functions::form_draw_checkbox('status', '1', (isset($_POST['status'])) ? $_POST['status'] : '1'); ?> <?php echo language::translate('title_enabled', 'Enabled'); ?></label></td>
    </tr>
    <tr>
      <td>
        <?php echo language::translate('title_username', 'Username'); ?><br />
          <?php echo functions::form_draw_text_field('username', true, 'required="required"'); ?>
      </td>
      <td>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo language::translate('title_new_password', 'New Password'); ?><br />
          <?php echo functions::form_draw_password_field('password', ''); ?>
      </td>
      <td>
        <?php echo language::translate('title_confirm_password', 'Confirm Password'); ?><br />
          <?php echo functions::form_draw_password_field('confirmed_password', ''); ?>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo language::translate('title_blocked_until', 'Blocked Until'); ?><br />
          <?php echo functions::form_draw_datetime_field('date_blocked', true); ?>
      </td>
      <td>
        <?php echo language::translate('title_expires', 'Expires'); ?><br />
          <?php echo functions::form_draw_datetime_field('date_expires', true); ?>
      </td>
    </tr>
    <?php if (!empty($user->data['id'])) { ?>
    <tr>
      <td>
        <?php echo language::translate('title_last_ip', 'Last IP'); ?><br />
          <?php echo functions::form_draw_text_field('last_ip', true, 'disabled="disabled"'); ?>
      </td>
      <td>
        <?php echo language::translate('title_last_host', 'Last Host'); ?><br />
          <?php echo functions::form_draw_text_field('last_host', true, 'disabled="disabled"'); ?>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo language::translate('title_last_login', 'Last Login'); ?><br />
          <?php echo functions::form_draw_text_field('date_login', true, 'disabled="disabled"'); ?>
      </td>
      <td>
      </td>
    </tr>
    <?php } ?>
  </table>

  <p><span class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (!empty($user->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></span></p>

<?php echo functions::form_draw_form_end(); ?>