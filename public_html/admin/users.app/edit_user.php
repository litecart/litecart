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
  
  // Save data
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
        var_dump($password);
        $contents .= "$username:$password" . PHP_EOL;
      }
      
      if (!$user_matched) $contents .= $_POST['username'] .':{SHA}'. base64_encode(sha1($_POST['password'], true)) . PHP_EOL;
      
      file_put_contents(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htpasswd', $contents);
    
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array(), array('app')));
      exit;
    }
  }
  
  if (empty($_POST['user']) && !empty($_GET['user'])) $_POST['user'] = $_GET['user'];
?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" border="0" align="absmiddle" style="margin-right: 10px;" /><?php echo (!empty($user->data['username'])) ? $system->language->translate('title_edit_user', 'Edit User') : $system->language->translate('title_create_new_user', 'Create New User'); ?></h1>
<?php echo $system->functions->form_draw_form_begin(false, 'post'); ?>
  <table border="0" cellpadding="5" cellspacing="0">
    <tr>
      <td align="left" valign="top" nowrap="nowrap">
        <strong><?php echo $system->language->translate('title_username', 'Username'); ?></strong><br />
          <?php echo $system->functions->form_draw_input_field('username', (isset($_POST['username']) ? $_POST['username'] : ''), 'text', 'style="width: 175px;"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" valign="top" nowrap="nowrap">
        <strong><?php echo $system->language->translate('title_password', 'Password'); ?></strong><br />
          <?php echo $system->functions->form_draw_input_field('password', (isset($_POST['password']) ? $_POST['password'] : ''), 'password', 'style="width: 175px;"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" valign="top" nowrap="nowrap">
        <strong><?php echo $system->language->translate('title_confirm_password', 'Confirm Password'); ?></strong><br />
          <?php echo $system->functions->form_draw_input_field('confirmed_password', (isset($_POST['confirmed_password']) ? $_POST['confirmed_password'] : ''), 'password', 'style="width: 175px;"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" valign="top" nowrap="nowrap"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"'); ?> <?php echo (isset($manufacturer['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"') : false; ?></td>
    </tr>
  </table>
<?php echo $system->functions->form_draw_form_end(); ?>