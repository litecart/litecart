<?php

  if (!empty($_GET['tax_rate_id'])) {
    $tax_rate = new ent_tax_rate($_GET['tax_rate_id']);
  } else {
    $tax_rate = new ent_tax_rate();
  }

  if (empty($_POST)) {
    $_POST = $tax_rate->data;

    if (empty($tax_rate->data['id'])) {
      $_POST['address_type'] = 'shipping';
    }
  }

  document::$snippets['title'][] = !empty($tax_rate->data['id']) ? language::translate('title_edit_tax_rate', 'Edit Tax Rate') : language::translate('title_add_new_tax_rate', 'Add New Tax Rate');

  breadcrumbs::add(language::translate('title_tax_rates', 'Tax Rates'), document::link(WS_DIR_ADMIN, ['doc' => 'tax_rates'], ['app']));
  breadcrumbs::add(!empty($tax_rate->data['id']) ? language::translate('title_edit_tax_rate', 'Edit Tax Rate') : language::translate('title_add_new_tax_rate', 'Add New Tax Rate'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['name'])) throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));
      if (empty($_POST['geo_zone_id'])) throw new Exception(language::translate('error_must_select_geo_zone', 'You must select a geo zone'));
      if (empty($_POST['tax_class_id'])) throw new Exception(language::translate('error_must_select_tax_class', 'You must select a tax class'));
      if (empty($_POST['rate'])) throw new Exception(language::translate('error_must_enter_rate', 'You must enter a rate'));

      if (empty($_POST['rule_companies_with_tax_id'])) $_POST['rule_companies_with_tax_id'] = 0;
      if (empty($_POST['rule_companies_without_tax_id'])) $_POST['rule_companies_without_tax_id'] = 0;
      if (empty($_POST['rule_individuals_with_tax_id'])) $_POST['rule_individuals_with_tax_id'] = 0;
      if (empty($_POST['rule_individuals_without_tax_id'])) $_POST['rule_individuals_without_tax_id'] = 0;

      $fields = [
        'tax_class_id',
        'geo_zone_id',
        'code',
        'name',
        'description',
        'rate',
        'type',
        'address_type',
        'rule_companies_with_tax_id',
        'rule_companies_without_tax_id',
        'rule_individuals_with_tax_id',
        'rule_individuals_without_tax_id',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $tax_rate->data[$field] = $_POST[$field];
      }

      $tax_rate->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'tax_rates'], true, ['tax_rate_id']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($tax_rate->data['id'])) throw new Exception(language::translate('error_must_provide_tax_rate', 'You must provide a tax rate'));

      $tax_rate->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'tax_rates'], true, ['tax_rate_id']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo !empty($tax_rate->data['id']) ? language::translate('title_edit_tax_rate', 'Edit Tax Rate') : language::translate('title_add_new_tax_rate', 'Add New Tax Rate'); ?>
  </div>

  <div class="panel-body">
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
            <?php echo functions::form_draw_select_field('type', [['percent'], ['fixed']], true, 'style="width: 150px;"'); ?>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_address_type', 'Address Type'); ?></label>
          <div class="checkbox">
            <label><?php echo functions::form_draw_radio_button('address_type', 'shipping', true); ?> <?php echo language::translate('title_shipping_address', 'Shipping Address'); ?></label>
          </div>
          <div class="checkbox">
            <label><?php echo functions::form_draw_radio_button('address_type', 'payment', true); ?> <?php echo language::translate('title_payment_address', 'Payment Address'); ?></label>
          </div>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_conditions', 'Conditions'); ?></label>
          <div class="radio">
            <label><?php echo functions::form_draw_checkbox('rule_companies_with_tax_id', '1', true); ?> <?php echo language::translate('text_applies_to_companies_with_tax_id', 'Applies to companies with a tax ID'); ?></label>
          </div>
          <div class="radio">
            <label><?php echo functions::form_draw_checkbox('rule_companies_without_tax_id', '1', true); ?> <?php echo language::translate('rule_applies_to_companies_without_tax_id', 'Applies to companies without a tax ID'); ?></label>
          </div>
          <div class="radio">
            <label><?php echo functions::form_draw_checkbox('rule_individuals_with_tax_id', '1', true); ?> <?php echo language::translate('text_applies_to_individuals_with_tax_id', 'Applies to individuals with a tax ID'); ?></label>
          </div>
          <div class="radio">
            <label><?php echo functions::form_draw_checkbox('rule_individuals_without_tax_id', '1', true); ?> <?php echo language::translate('rule_applies_to_individuals_without_tax_id', 'Applies to individuals without a tax ID'); ?></label>
          </div>
        </div>
      </div>

      <div class="panel-action btn-group">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        <?php echo (isset($tax_rate->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>
