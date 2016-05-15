<?php

  if (!empty($_GET['tax_class_id'])) {
    $tax_class = new ctrl_tax_class($_GET['tax_class_id']);
  } else {
    $tax_class = new ctrl_tax_class();
  }

  if (empty($_POST)) {
    foreach ($tax_class->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  breadcrumbs::add(!empty($tax_class->data['id']) ? language::translate('title_edit_tax_class', 'Edit Tax Class') : language::translate('title_add_new_tax_class', 'Add New Tax Class'));

  if (isset($_POST['save'])) {

    if (empty($_POST['name'])) notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));

    if (empty(notices::$data['errors'])) {

      $fields = array(
        'code',
        'name',
        'description',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $tax_class->data[$field] = $_POST[$field];
      }

      $tax_class->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('', array('doc' => 'tax_classes'), true, array('tax_class_id')));
      exit;
    }
  }

  if (isset($_POST['delete'])) {

    $tax_class->delete();

    notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
    header('Location: '. document::link('', array('doc' => 'tax_classes'), true, array('tax_class_id')));
    exit;
  }

?>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo !empty($tax_class->data['id']) ? language::translate('title_edit_tax_class', 'Edit Tax Class') : language::translate('title_add_new_tax_class', 'Add New Tax Class'); ?></h1>

<?php echo functions::form_draw_form_begin(false, 'post', false, true); ?>

  <table>
    <tr>
      <td><strong><?php echo language::translate('title_code', 'Code'); ?></strong><br />
        <?php echo functions::form_draw_text_field('code', true, 'data-size="small"'); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_name', 'Name'); ?></strong><br />
        <?php echo functions::form_draw_text_field('name', true); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_description', 'Description'); ?></strong><br />
        <?php echo functions::form_draw_text_field('description', true, 'data-size="large"'); ?>
      </td>
    </tr>
  </table>

  <p><span class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($tax_class->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></span></p>

<?php echo functions::form_draw_form_end(); ?>