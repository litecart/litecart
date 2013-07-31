<?php

  $user = new StdClass();
  $user->data = array(
    'username' => '',
  );
  
  if (!empty($_GET['user'])) {
    foreach(file(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd') as $row) {
      list($username, $password) = explode(':', trim($row));
      if ($_GET['user'] == $username) $user->data['username'] = $username;
    }
    if (empty($_POST)) {
      foreach ($user->data as $key => $value) $_POST[$key] = $value;
    }
  }
  
  if (isset($_POST['save'])) {
    
    if (empty($_POST['username'])) $system->notices->add('errors', $system->language->translate('error_must_enter_username', 'You must enter a username'));
    
    if (empty($user->data['username'])) {
      if (empty($_POST['password'])) $system->notices->add('errors', $system->language->translate('error_must_enter_password', 'You must enter a password'));
      if (empty($_POST['confirmed_password'])) $system->notices->add('errors', $system->language->translate('error_must_enter_confirmed_password', 'You must confirm the password'));
    }
    
    if (!empty($_POST['password']) && !empty($_POST['confirmed_password']) && $_POST['password'] != $_POST['confirmed_password']) $system->notices->add('errors', $system->language->translate('error_passwords_missmatch', 'The passwords did not match'));
    
    if (!is_writable(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd')) $system->notices->add('errors', sprintf($system->language->translate('error_file_s_not_writable', 'The file %s is not writable'), FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd'));
    
    if (empty($system->notices->data['errors'])) {
    
      $contents = '';
      
      $user_matched = false;
      foreach(file(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd') as $row) {
        list($username, $password) = explode(':', trim($row));
        
        if ($user->data['username'] == $username) {
          $username = $_POST['username'];
          
          if (!empty($_POST['password'])) {
            $password = '{SHA}'.base64_encode(sha1($_POST['password'], true));
          }
          
          $user_matched = true;
        }
        
        $contents .= "$username:$password" . PHP_EOL;
      }
      
      if (!$user_matched) $contents .= $_POST['username'] .':{SHA}'. base64_encode(sha1($_POST['password'], true)) . PHP_EOL;
      
      file_put_contents(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd', $contents);
    
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array(), array('app')));
      exit;
    }
  }
  
  if (isset($_POST['delete'])) {
    
    if (empty($_POST['username'])) $system->notices->add('errors', $system->language->translate('error_must_enter_username', 'You must enter a username'));
    
    if (!is_writable(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd')) $system->notices->add('errors', sprintf($system->language->translate('error_file_s_not_writable', 'The file %s is not writable'), FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd'));
    
    if (empty($system->notices->data['errors'])) {
    
      $contents = '';
      
      foreach(file(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd') as $row) {
        list($username, $password) = explode(':', trim($row));
        
        if ($user->data['username'] != $username) {
          $contents .= "$username:$password" . PHP_EOL;
        }
      }
      
      file_put_contents(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd', $contents);
    
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array(), array('app')));
      exit;
    }
  }
  
  if (empty($_POST['user']) && !empty($_GET['user'])) $_POST['user'] = $_GET['user'];
?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo (!empty($user->data['username'])) ? $system->language->translate('title_edit_user', 'Edit User') : $system->language->translate('title_create_new_user', 'Create New User'); ?></h1>
<?php echo $system->functions->form_draw_form_begin(false, 'post'); ?>
  <table>
    <tr>
      <td align="left" nowrap="nowrap">
        <strong><?php echo $system->language->translate('title_username', 'Username'); ?></strong><br />
          <?php echo $system->functions->form_draw_text_field('username', true); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap">
        <strong><?php echo $system->language->translate('title_new_password', 'New Password'); ?></strong><br />
          <?php echo $system->functions->form_draw_password_field('password', ''); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap">
        <strong><?php echo $system->language->translate('title_confirm_password', 'Confirm Password'); ?></strong><br />
          <?php echo $system->functions->form_draw_password_field('confirmed_password', ''); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (!empty($_GET['user'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></td>
    </tr>
  </table>
<?php echo $system->functions->form_draw_form_end(); ?>