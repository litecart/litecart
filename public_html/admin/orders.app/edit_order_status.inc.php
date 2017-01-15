<?php

  if (!empty($_GET['order_status_id'])) {
    $order_status = new ctrl_order_status($_GET['order_status_id']);
  } else {
    $order_status = new ctrl_order_status();
  }

  if (empty($_POST)) {
    foreach ($order_status->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  breadcrumbs::add(!empty($order_status->data['id']) ? language::translate('title_edit_order_status', 'Edit Order Status') : language::translate('title_create_new_order_status', 'Create New Order Status'));

  if (isset($_POST['save'])) {

    if (empty($_POST['name'])) notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));

    if (empty($_POST['notify'])) $_POST['notify'] = 0;
    if (empty($_POST['is_sale'])) $_POST['is_sale'] = 0;
    if (empty($_POST['is_archived'])) $_POST['is_archived'] = 0;

    if (empty(notices::$data['errors'])) {

      $fields = array(
        'icon',
        'color',
        'is_sale',
        'is_archived',
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
    exit;
  }

?>
<h1 style="margin-top: 0;"><?php echo $app_icon; ?> <?php echo !empty($order_status->data['id']) ? language::translate('title_edit_order_status', 'Edit Order Status') : language::translate('title_create_new_order_status', 'Create New Order Status'); ?></h1>

<?php echo functions::form_draw_form_begin('order_status_form', 'post', null, false, 'style="max-width: 640px;"'); ?>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_name', 'Name'); ?></label>
      <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', (isset($_POST['name'][$language_code]) ? $_POST['name'][$language_code] : ''), 'text', 'style="width: 360px"'); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md">
      <label><?php echo language::translate('title_description', 'Description'); ?></label>
      <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_textarea($language_code, 'description['. $language_code .']', (isset($_POST['description'][$language_code]) ? $_POST['description'][$language_code] : ''), 'style="height: 50px;"');  $use_br = true; ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_icon', 'Icon');?> <a href="http://fontawesome.io/icons/" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
      <?php echo functions::form_draw_text_field('icon', true, 'placeholder="fa-circle-thin"'); ?>
    </div>

    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_color', 'Color');?></label>
      <?php echo functions::form_draw_color_field('color', empty($_POST['color']) ? '#cccccc' : true, 'placeholder="#cccccc"'); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <div class="checkbox">
        <label><?php echo functions::form_draw_checkbox('is_sale', '1', empty($_POST['is_sale']) ? '0' : '1'); ?> <?php echo language::translate('text_is_sale', 'Is sale');?></label>
      </div>

      <div class="checkbox">
        <label><?php echo functions::form_draw_checkbox('is_archived', '1', empty($_POST['is_archived']) ? '0' : '1'); ?> <?php echo language::translate('text_is_archived', 'Is archived');?></label>
      </div>

      <div class="checkbox">
        <label><?php echo functions::form_draw_checkbox('notify', '1', empty($_POST['notify']) ? '0' : '1'); ?> <?php echo language::translate('text_notify_customer', 'Notify customer');?></label>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_email_message', 'Email Message'); ?></label>
      <p><?php echo language::translate('description_order_status_email_message', 'Compose a message that will be used as email body or leave blank to display the order copy.'); ?></p>
      <p><?php echo language::translate('title_aliases', 'Aliases'); ?>: <em>%order_id, %firstname, %lastname, %billing_address, %shipping_address, %order_copy_url</em></p>
      <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_wysiwyg_field($language_code, 'email_message['. $language_code .']', true, 'style="height: 170px;"'); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_priority', 'Priority'); ?></label>
        <?php echo functions::form_draw_number_field('priority', true); ?>
    </div>
  </div>

  <p class="btn-group">
    <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
    <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
    <?php echo (isset($order_status->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>