<?php
  
  if (isset($_GET['order_status_id'])) {
    $order_status = new ctrl_order_status($_GET['order_status_id']);
    
    if (!$_POST) {
      foreach ($order_status->data as $key => $value) {
        $_POST[$key] = $value;
      }
    }
  } else {
    $order_status = new ctrl_order_status();
  }
  
  if (isset($_POST['save'])) {

    if (empty($_POST['name'])) notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));
    
    if (empty($_POST['notify'])) $_POST['notify'] = 0;
    if (empty($_POST['is_sale'])) $_POST['is_sale'] = 0;
    
    if (!notices::get('errors')) {
    
      $fields = array(
        'is_sale',
        'notify',
        'priority',
        'name',
        'description',
        'email_message',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $order_status->data[$field] = $_POST[$field];
      }
      
      $order_status->save();
      
      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('', array('doc' => 'order_statuses'), true, array('order_status_id')));
      exit;
    }
  }
  
  if (isset($_POST['delete'])) {

    $order_status->delete();
    
    notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
    header('Location: '. document::link('', array('doc' => 'order_statuses'), true, array('order_status_id')));
    exit();
  }

?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo !empty($order_status->data['id']) ? language::translate('title_edit_order_status', 'Edit Order Status') : language::translate('title_create_new_order_status', 'Create New Order Status'); ?></h1>

<?php echo functions::form_draw_form_begin('order_status_form', 'post'); ?>

  <table>
    <tr>
      <td align="left" nowrap="nowrap">
        <strong><?php echo language::translate('title_name', 'Name'); ?></strong><br />
<?php
  $use_br = false;
  foreach (array_keys(language::$languages) as $language_code) {
    if ($use_br) echo '<br />';
    echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true, '');
    $use_br = true;
  }
?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo language::translate('title_description', 'Description'); ?></strong><br />
<?php
  $use_br = false;
  foreach (array_keys(language::$languages) as $language_code) {
    if ($use_br) echo '<br />';
    echo functions::form_draw_regional_textarea($language_code, 'description['. $language_code .']', true, 'data-size="large" style="height: 30px;"');
    $use_br = true;
  }
?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo language::translate('title_email_message', 'E-mail Message'); ?></strong><br />
        <p><?php echo language::translate('description_order_status_email_message', 'Compose a message that will be used as e-mail body or leave blank to display the order copy.'); ?></p>
        <p><?php echo language::translate('title_aliases', 'Aliases'); ?>: <em>%order_id, %firstname, %lastname, %billing_address, %shipping_address, %order_copy_url</em></p>
<?php
  $use_br = false;
  foreach (array_keys(language::$languages) as $language_code) {
    if ($use_br) echo '<br />';
    echo functions::form_draw_regional_textarea($language_code, 'email_message['. $language_code .']', true, 'data-size="large" style="height: 70px;"');
    $use_br = true;
  }
?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><?php echo functions::form_draw_checkbox('is_sale', '1', empty($_POST['is_sale']) ? '0' : '1'); ?> <?php echo language::translate('text_is_sale', 'Is sale');?></td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><?php echo functions::form_draw_checkbox('notify', '1', empty($_POST['notify']) ? '0' : '1'); ?> <?php echo language::translate('text_notify_customer', 'Notify customer');?></td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo language::translate('title_priority', 'Priority'); ?></strong><br />
        <?php echo functions::form_draw_number_field('priority', true); ?>
      </td>
    </tr>
  </table>
  
  <p><span class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($order_status->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></span></p>
  
<?php echo functions::form_draw_form_end(); ?>