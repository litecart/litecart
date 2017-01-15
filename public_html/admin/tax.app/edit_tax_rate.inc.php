<?php

  if (!empty($_GET['tax_rate_id'])) {
    $tax_rate = new ctrl_tax_rate($_GET['tax_rate_id']);
  } else {
    $tax_rate = new ctrl_tax_rate();
  }

  if (empty($_POST)) {
    foreach ($tax_rate->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  if (isset($_POST['save'])) {

    if (empty($_POST['name'])) notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));
    if (empty($_POST['geo_zone_id'])) notices::add('errors', language::translate('error_must_select_geo_zone', 'You must select a geo zone'));
    if (empty($_POST['tax_class_id'])) notices::add('errors', language::translate('error_must_select_tax_class', 'You must select a tax class'));
    if (empty($_POST['rate'])) notices::add('errors', language::translate('error_must_enter_rate', 'You must enter a rate'));

    if (empty(notices::$data['errors'])) {

      $fields = array(
        'tax_class_id',
        'geo_zone_id',
        'code',
        'name',
        'description',
        'rate',
        'type',
        'address_type',
        'customer_type',
        'tax_id_rule',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $tax_rate->data[$field] = $_POST[$field];
      }

      $tax_rate->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('', array('doc' => 'tax_rates'), true, array('tax_rate_id')));
      exit;
    }
  }

  if (isset($_POST['delete'])) {

    $tax_rate->delete();

    notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
    header('Location: '. document::link('', array('doc' => 'tax_rates'), true, array('tax_rate_id')));
    exit;
  }

?>
<h1><?php echo $app_icon; ?> <?php echo !empty($tax_rate->data['id']) ? language::translate('title_edit_tax_rate', 'Edit Tax Rate') : language::translate('title_add_new_tax_rate', 'Add New Tax Rate'); ?></h1>

<?php echo functions::form_draw_form_begin('tax_rate_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_code', 'Code'); ?></label>
      <?php echo functions::form_draw_text_field('code', true); ?>
    </div>

    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_name', 'Name'); ?></label>
      <?php echo functions::form_draw_text_field('name', true); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_description', 'Description'); ?></label>
      <?php echo functions::form_draw_text_field('description', true); ?>
    </div>

    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_tax_class', 'Tax Class'); ?></label>
      <?php echo functions::form_draw_tax_classes_list('tax_class_id', true); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_geo_zone', 'Geo Zone'); ?></label>
      <?php echo functions::form_draw_geo_zones_list('geo_zone_id', true); ?>
    </div>

    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_rate', 'Rate'); ?></label>
      <div class="input-group">
        <?php echo functions::form_draw_decimal_field('rate', true, 4); ?>
        <span class="input-group-btn"><?php echo functions::form_draw_select_field('type', array(array('percent'), array('fixed')), true, false, 'style="width: 150px;"'); ?></span>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_address_type', 'Address Type'); ?></label>
      <div class="checkbox">
        <label><?php echo functions::form_draw_radio_button('address_type', 'payment', true); ?> <?php echo language::translate('title_payment_address', 'Payment Address'); ?></label>
      </div>
      <div class="checkbox">
        <label><?php echo functions::form_draw_radio_button('address_type', 'shipping', true); ?> <?php echo language::translate('title_shipping_address', 'Shipping Address'); ?></label>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_rule', 'Rule'); ?>: <?php echo language::translate('title_customer_type', 'Customer Type'); ?></label>
      <div class="radio">
        <label><?php echo functions::form_draw_radio_button('customer_type', 'individuals', true); ?> <?php echo language::translate('text_tax_rate_rule_individuals', 'Applies to individuals'); ?></label>
      </div>
      <div class="radio">
        <label><?php echo functions::form_draw_radio_button('customer_type', 'companies', true); ?> <?php echo language::translate('text_tax_rate_rule_companies', 'Applies to companies'); ?></label></label>
      </div>
      <div class="radio">
        <label><?php echo functions::form_draw_radio_button('customer_type', 'both', empty($_POST['customer_type']) ? 'both' : true); ?> <?php echo language::translate('text_tax_rate_rule_both_of_the_above', 'Applies to both of above'); ?></label>
      </div>
    </div>

    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_rule', 'Rule'); ?>: <?php echo language::translate('title_tax_id', 'Tax ID'); ?></label>
      <div class="radio">
        <label><?php echo functions::form_draw_radio_button('tax_id_rule', 'with', true); ?> <?php echo language::translate('text_tax_rate_rule_customers_with_tax_id', 'Applies to customers with a tax ID'); ?></label></label>
      </div>
      <div class="radio">
        <label><?php echo functions::form_draw_radio_button('tax_id_rule', 'without', true); ?> <?php echo language::translate('text_tax_rate_rule_customers_without_tax_id', 'Applies to customers without a tax ID'); ?></label></label>
      </div>
      <div class="radio">
        <label><?php echo functions::form_draw_radio_button('tax_id_rule', 'both', empty($_POST['customer_type']) ? 'both' : true); ?> <?php echo language::translate('text_tax_rate_rule_both_of_the_above', 'Applies to both of above'); ?></label>
      </div>
    </div>
  </div>

  <p class="btn-group">
    <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
    <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
    <?php echo (isset($tax_rate->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>