<?php

  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . 'supplier.inc.php');
  
  if (isset($_GET['supplier_id'])) {
    $supplier = new ctrl_supplier($_GET['supplier_id']);
  } else {
    $supplier = new ctrl_supplier();
  }
  
  if (!$_POST && isset($supplier)) {
    foreach ($supplier->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  // Save data to database
  if (isset($_POST['save'])) {

    if ($_POST['name'] == '') $system->notices->add('errors', $system->language->translate('error_name_missing', 'You must enter a name.'));
    
    if (!$system->notices->get('errors')) {
    
      if (!isset($_POST['status'])) $_POST['status'] = '0';
    
      $fields = array(
        'name',
        'description',
        'email',
        'phone',
        'link',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $supplier->data[$field] = $_POST[$field];
      }
      
      $supplier->save();
      
      if (is_uploaded_file($_FILES['image']['tmp_name'])) {
        $supplier->save_image($_FILES['image']['tmp_name']);
      }
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array('doc' => 'suppliers.php'), array('app')));
      exit;
    }
  }

  // Delete from database
  if (isset($_POST['delete']) && $supplier) {
    
    $supplier->delete();
    
    $system->notices->add('success', $system->language->translate('success_post_deleted', 'Post deleted'));
    header('Location: '. $system->document->link('', array('doc' => 'suppliers.php'), array('app')));
    exit();
  }

?>
  <table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td><h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" border="0" align="absmiddle" style="margin-right: 10px;" /><?php echo (empty($supplier->data['id'])) ? $system->language->translate('title_add_new_supplier', 'Add New Supplier') : $system->language->translate('title_edit_supplier', 'Edit Supplier'); ?></h1>
        <?php echo $system->functions->form_draw_form_begin(false, 'post', false, true); ?>
        <table border="0" cellpadding="5" cellspacing="0">
          <tr>
            <td align="left" valign="top" nowrap="nowrap">
              <strong><?php echo $system->language->translate('title_name', 'Name'); ?></strong><br />
                <?php echo $system->functions->form_draw_input_field('name', (isset($_POST['name']) ? $_POST['name'] : ''), 'text', 'style="width: 175px;"'); ?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap">
              <strong><?php echo $system->language->translate('title_description', 'description'); ?></strong><br />
                <?php echo $system->functions->form_draw_textarea('description', (isset($_POST['description']) ? $_POST['description'] : ''), 'style="width: 360px;"'); ?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap">
              <strong><?php echo $system->language->translate('title_email', 'email'); ?></strong><br />
                <?php echo $system->functions->form_draw_input_field('email', (isset($_POST['email']) ? $_POST['email'] : ''), 'text', 'style="width: 175px;"'); ?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap">
              <strong><?php echo $system->language->translate('title_phone', 'Phone'); ?></strong><br />
                <?php echo $system->functions->form_draw_input_field('phone', (isset($_POST['phone']) ? $_POST['phone'] : ''), 'text', 'style="width: 175px;"'); ?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap">
              <strong><?php echo $system->language->translate('title_link', 'Link'); ?></strong><br />
                <?php echo $system->functions->form_draw_input_field('link', (isset($_POST['link']) ? $_POST['link'] : ''), 'text', 'style="width: 175px;"'); ?>
            </td>
          </tr>
          <tr>
            <td align="left" valign="top" nowrap="nowrap"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"'); ?> <?php echo (isset($supplier->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"') : false; ?></td>
          </tr>
        </table>
      <?php echo $system->functions->form_draw_form_end(); ?></td>
    </tr>
  </table>