<?php

  if (isset($_GET['user_id'])) {
    $user = new ctrl_user($_GET['user_id']);
    
    if (empty($_POST)) {
      foreach ($user->data as $key => $value) {
        $_POST[$key] = $value;
      }
    }
    
  } else {
    $user = new ctrl_user();
  }
  
  if (isset($_POST['save'])) {
    
    if (empty($_POST['username'])) $system->notices->add('errors', $system->language->translate('error_must_enter_username', 'You must enter a username'));
    
    if (empty($user->data['username'])) {
      if (empty($_POST['password'])) $system->notices->add('errors', $system->language->translate('error_must_enter_password', 'You must enter a password'));
      if (empty($_POST['confirmed_password'])) $system->notices->add('errors', $system->language->translate('error_must_enter_confirmed_password', 'You must confirm the password'));
    }
    
    if (!empty($_POST['password']) && !empty($_POST['confirmed_password']) && $_POST['password'] != $_POST['confirmed_password']) $system->notices->add('errors', $system->language->translate('error_passwords_missmatch', 'The passwords did not match'));
    
    if (empty($system->notices->data['errors'])) {
      
      if (empty($_POST['status'])) $_POST['status'] = 0;
      
      $fields = array(
        'status',
        'username',
        'password',
        'firstname',
        'lastname',
        'email',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $user->data[$field] = $_POST[$field];
      }
      
      if (!empty($_POST['password'])) $user->set_password($_POST['password']);
      
      $user->save();
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array(), array('app')));
      exit;
    }
  }
  
  if (isset($_POST['delete'])) {
    
    if (!empty($user->data['id'])) {
    
      $user->delete();
    
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array(), array('app')));
      exit;
    }
  }
  
?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo (!empty($user->data['username'])) ? $system->language->translate('title_edit_user', 'Edit User') : $system->language->translate('title_create_new_user', 'Create New User'); ?></h1>
<?php echo $system->functions->form_draw_form_begin(false, 'post'); ?>
  <table>
    <tr>
      <td colspan="2"><strong><?php echo $system->language->translate('title_status', 'Status'); ?></strong><br />
        <label><?php echo $system->functions->form_draw_checkbox('status', '1', (isset($_POST['status'])) ? $_POST['status'] : '1'); ?> <?php echo $system->language->translate('title_enabled', 'Enabled'); ?></label></td>
    </tr>
    <tr>
      <td>
        <?php echo $system->language->translate('title_username', 'Username'); ?><br />
          <?php echo $system->functions->form_draw_text_field('username', true, 'required="required"'); ?>
      </td>
      <td>
        <?php echo $system->language->translate('title_email_address', 'E-mail Address'); ?><br />
          <?php echo $system->functions->form_draw_email_field('email', true); ?>
      </td>
    </tr>
    <tr>
      <td align="left">
        <?php echo $system->language->translate('title_first_name', 'First Name'); ?><br />
          <?php echo $system->functions->form_draw_text_field('firstname', true); ?>
      </td>
      <td>
        <?php echo $system->language->translate('title_last_name', 'Last Name'); ?><br />
          <?php echo $system->functions->form_draw_text_field('lastname', true); ?>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo $system->language->translate('title_new_password', 'New Password'); ?><br />
          <?php echo $system->functions->form_draw_password_field('password', ''); ?>
      </td>
      <td>
        <?php echo $system->language->translate('title_confirm_password', 'Confirm Password'); ?><br />
          <?php echo $system->functions->form_draw_password_field('confirmed_password', ''); ?>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo $system->language->translate('title_last_ip', 'Last IP'); ?><br />
          <?php echo $system->functions->form_draw_static_field('last_ip', true); ?>
      </td>
      <td>
        <?php echo $system->language->translate('title_last_host', 'Last Host'); ?><br />
          <?php echo $system->functions->form_draw_static_field('last', true); ?>
      </td>
    </tr>
    <tr>
      <td colspan="2"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (!empty($user->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></td>
    </tr>
  </table>
<?php echo $system->functions->form_draw_form_end(); ?>