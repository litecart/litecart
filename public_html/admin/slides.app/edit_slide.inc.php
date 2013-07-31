<?php
  
  if (isset($_GET['slide_id'])) {
    $slide = new ctrl_slide($_GET['slide_id']);
    if (!$_POST) {
      foreach ($slide->data as $key => $value) {
        $_POST[$key] = $value;
      }
    }
  } else {
    $slide = new ctrl_slide();
  }

  if (!empty($_POST['save'])) {
    
    if (empty($_POST['name'])) $system->notices->add('errors', $system->language->translate('error_must_enter_name', 'You must enter a name'));
    if (empty($_POST['language_code'])) $system->notices->add('errors', $system->language->translate('error_must_enter_language_code', 'You must enter a language'));
    
    if (empty($system->notices->data['errors'])) {
      
      if (empty($_POST['stable'])) $_POST['stable'] = 0;
      
      $fields = array(
        'status',
        'language_code',
        'name',
        'caption',
        'link',
        'priority',
        'date_valid_from',
        'date_valid_to',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $slide->data[$field] = $_POST[$field];
      }
      
      if (is_uploaded_file($_FILES['image']['tmp_name'])) $slide->save_image($_FILES['image']['tmp_name']);
      
      $slide->save();
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes were successfully saved.'));
      header('Location: '. $system->document->link('', array('doc' => 'slides'), true, array('action', 'slide_id')));
      exit;
    }
  }
  
  if (!empty($_POST['delete'])) {
    
    $slide->delete();
    
    $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes were successfully saved.'));
    header('Location: '. $system->document->link('', array('doc' => 'slides'), true, array('action', 'slide_id')));
    exit;
  }

?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo (isset($slide->data['id'])) ? $system->language->translate('title_edit_slide', 'Edit Slide') : $system->language->translate('title_add_new_slide', 'Add New Slide'); ?></h1>

<?php if (!empty($slide->data['image'])) echo '<p><img src="'. WS_DIR_IMAGES . $slide->data['image'] .'" /></p>'; ?>

<?php echo $system->functions->form_draw_form_begin('', 'post', false, true); ?>

<table>
  <tr>
    <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_status', 'Status'); ?></strong><br />
      <?php echo $system->functions->form_draw_radio_button('status', '1', isset($_POST['status']) ? $_POST['status'] : '1'); ?> <?php echo $system->language->translate('title_enabled', 'Enabled'); ?>
      <?php echo $system->functions->form_draw_radio_button('status', '0', isset($_POST['status']) ? $_POST['status'] : '1'); ?> <?php echo $system->language->translate('title_disabled', 'Disabled'); ?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_language', 'Language'); ?></strong><br />
      <?php echo $system->functions->form_draw_languages_list('language_code', true); ?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_name', 'Name'); ?></strong><br />
      <?php echo $system->functions->form_draw_text_field('name', true); ?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_caption', 'Caption'); ?></strong><br />
      <?php echo $system->functions->form_draw_text_field('caption', true); ?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_link', 'Link'); ?></strong><br />
      <?php echo $system->functions->form_draw_url_field('link', true); ?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_image', 'Image'); ?></strong><br />
      <?php echo $system->functions->form_draw_file_field('image'); ?>
      <?php echo (!empty($slide->data['image'])) ? '<br />' . $slide->data['image'] : ''; ?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_date_valid_from', 'Date Valid From'); ?></strong><br />
      <?php echo $system->functions->form_draw_datetime_field('date_valid_from', true); ?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_date_valid_to', 'Date Valid To'); ?></strong><br />
      <?php echo $system->functions->form_draw_datetime_field('date_valid_to', true); ?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_priority', 'Priority'); ?></strong><br />
      <?php echo $system->functions->form_draw_number_field('priority', true); ?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"'); ?> <?php echo (isset($slide->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"') : false; ?></td>
  </tr>
</table>
  
<?php echo $system->functions->form_draw_form_end(); ?>