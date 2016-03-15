<?php
  
  if (!empty($_GET['slide_id'])) {
    $slide = new ctrl_slide($_GET['slide_id']);
  } else {
    $slide = new ctrl_slide();
  }
  
  if (empty($_POST)) {
    foreach ($slide->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }
  
  breadcrumbs::add(!empty($slide->data['id']) ? language::translate('title_edit_slide', 'Edit Slide') : language::translate('title_add_new_slide', 'Add New Slide'));
  
  if (!empty($_POST['save'])) {
    
    if (empty($_POST['name'])) notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));
    
    if (empty(notices::$data['errors'])) {
      
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
      
      notices::add('success', language::translate('success_changes_saved', 'Changes were successfully saved.'));
      header('Location: '. document::link('', array('doc' => 'slides'), true, array('action', 'slide_id')));
      exit;
    }
  }
  
  if (!empty($_POST['delete'])) {
    
    $slide->delete();
    
    notices::add('success', language::translate('success_changes_saved', 'Changes were successfully saved.'));
    header('Location: '. document::link('', array('doc' => 'slides'), true, array('action', 'slide_id')));
    exit;
  }

?>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo !empty($slide->data['id']) ? language::translate('title_edit_slide', 'Edit Slide') : language::translate('title_add_new_slide', 'Add New Slide'); ?></h1>

<?php if (!empty($slide->data['image'])) echo '<p><img src="'. WS_DIR_IMAGES . $slide->data['image'] .'" /></p>'; ?>

<?php echo functions::form_draw_form_begin('', 'post', false, true); ?>

  <table>
    <tr>
      <td><strong><?php echo language::translate('title_status', 'Status'); ?></strong><br />
        <?php echo functions::form_draw_toggle('status', isset($_POST['status']) ? $_POST['status'] : '1', 'e/d'); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_language', 'Language'); ?></strong><br />
        <?php echo functions::form_draw_languages_list('language_code', true); ?> <em><?php echo language::translate('text_leave_blank_for_all_languages', 'Leave blank for all languages'); ?></em>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_name', 'Name'); ?></strong><br />
        <?php echo functions::form_draw_text_field('name', true); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_caption', 'Caption'); ?></strong><br />
        <?php echo functions::form_draw_textarea('caption', true, 'style="height: 320px;"'); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_link', 'Link'); ?></strong><br />
        <?php echo functions::form_draw_url_field('link', true); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_image', 'Image'); ?></strong><br />
        <?php echo functions::form_draw_file_field('image'); ?>
        <?php echo (!empty($slide->data['image'])) ? '<br />' . $slide->data['image'] : ''; ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_date_valid_from', 'Date Valid From'); ?></strong><br />
        <?php echo functions::form_draw_datetime_field('date_valid_from', true); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_date_valid_to', 'Date Valid To'); ?></strong><br />
        <?php echo functions::form_draw_datetime_field('date_valid_to', true); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_priority', 'Priority'); ?></strong><br />
        <?php echo functions::form_draw_number_field('priority', true); ?>
      </td>
    </tr>
  </table>
  
  <p><span class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($slide->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></span></p>
  
<?php echo functions::form_draw_form_end(); ?>