<?php

  if (!empty($_GET['pages_id'])) {
    $pages = new ctrl_page($_GET['pages_id']);
  } else {
    $pages = new ctrl_page();
  }

  if (empty($_POST)) {
    foreach ($pages->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  breadcrumbs::add(!empty($pages->data['id']) ? language::translate('title_edit_page', 'Edit Page') : language::translate('title_create_new_pages', 'Create New Page'));

  if (isset($_POST['save'])) {

    if (empty($_POST['title'])) notices::add('errors', language::translate('error_missing_title', 'You must enter a title.'));
    if (empty($_POST['dock'])) notices::add('errors', language::translate('error_missing_dock', 'You must select a dock.'));

    if (empty($_POST['status'])) $_POST['status'] = 0;

    if (empty(notices::$data['errors'])) {

      $fields = array(
        'status',
        'title',
        'content',
        'dock',
        'priority',
        'head_title',
        'meta_description',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $pages->data[$field] = $_POST[$field];
      }

      $pages->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('', array('doc' => 'pages'), true, array('pages_id')));
      exit;
    }
  }

  if (isset($_POST['delete'])) {

    $pages->delete();

    notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
    header('Location: '. document::link('', array('doc' => 'pages'), true, array('page_id')));
    exit;
  }

?>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo !empty($pages->data['id']) ? language::translate('title_edit_page', 'Edit Page') : language::translate('title_create_new_pages', 'Create New Page'); ?></h1>

<?php echo functions::form_draw_form_begin('pages_form', 'post'); ?>

  <table>
    <tr>
      <td><strong><?php echo language::translate('title_status', 'Status'); ?></strong><br />
        <label><?php echo functions::form_draw_checkbox('status', '1', (isset($_POST['status'])) ? $_POST['status'] : '1'); ?> <?php echo language::translate('title_enabled', 'Enabled'); ?></label></td>
    </tr>
    <tr>
      <td>
        <strong><?php echo language::translate('title_title', 'Title'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_input_field($language_code, 'title['. $language_code .']', true, '');
  $use_br = true;
}
?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_content', 'Content'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_wysiwyg_field($language_code, 'content['. $language_code .']', true, 'style="width: 720px; height: 400px;"');
  $use_br = true;
}
?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_head_title', 'Head Title'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_input_field($language_code, 'head_title['. $language_code .']', true, 'data-size="large"');
  $use_br = true;
}
?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_meta_description', 'Meta Description'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_input_field($language_code, 'meta_description['. $language_code .']', true, 'data-size="large"');
  $use_br = true;
}
?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_dock', 'Dock'); ?></strong><br />
        <label><?php echo functions::form_draw_checkbox('dock[]', 'menu', (isset($_POST['dock']) && in_array('menu', $_POST['dock'])) ? 'menu' : '0'); ?> <?php echo language::translate('text_dock_in_dock_menu', 'Dock in site menu'); ?></label><br />
        <label><?php echo functions::form_draw_checkbox('dock[]', 'customer_service', (isset($_POST['dock']) && in_array('customer_service', $_POST['dock'])) ? 'customer_service' : ''); ?> <?php echo language::translate('text_dock_in_customer_service', 'Dock in customer service'); ?></label><br />
        <label><?php echo functions::form_draw_checkbox('dock[]', 'information', (isset($_POST['dock']) && in_array('information', $_POST['dock'])) ? 'information' : '0'); ?> <?php echo language::translate('text_dock_in_information', 'Dock in information'); ?></label>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_priority', 'Priority'); ?></strong><br />
        <?php echo functions::form_draw_number_field('priority', true); ?>
      </td>
    </tr>
  </table>

  <p><span class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($pages->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></span></p>

<?php echo functions::form_draw_form_end(); ?>