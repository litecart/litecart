<?php
  
  if (isset($_GET['tax_rate_id'])) {
    $tax_rate = new ctrl_tax_rate($_GET['tax_rate_id']);
  } else {
    $tax_rate = new ctrl_tax_rate();
  }
  
  if (!$_POST) {
    foreach ($tax_rate->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  
  // Save data to database
  if (isset($_POST['save'])) {

    if (empty($_POST['name'])) $system->notices->add('errors', $system->language->translate('error_must_enter_name', 'You must enter a name'));
    if (empty($_POST['geo_zone_id'])) $system->notices->add('errors', $system->language->translate('error_must_select_geo_zone', 'You must select a geo zone'));
    if (empty($_POST['tax_class_id'])) $system->notices->add('errors', $system->language->translate('error_must_select_tax_class', 'You must select a tax class'));
    if (empty($_POST['rate'])) $system->notices->add('errors', $system->language->translate('error_must_enter_rate', 'You must enter a rate'));
    
    if (!$system->notices->get('errors')) {
    
      $fields = array(
        'tax_class_id',
        'geo_zone_id',
        'type',
        'name',
        'description',
        'rate',
        'tax_id_rule',
        'customer_type',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $tax_rate->data[$field] = $_POST[$field];
      }
      
      $tax_rate->save();
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array('doc' => 'tax_rates'), true, array('tax_rate_id')));
      exit;
    }
  }
  
  if (isset($_POST['delete'])) {

    $tax_rate->delete();
    
    $system->notices->add('success', $system->language->translate('success_post_deleted', 'Post deleted'));
    header('Location: '. $system->document->link('', array('doc' => 'tax_rates'), true, array('tax_rate_id')));
    exit();
  }

?>
  <table width="100%">
    <tr>
      <td><h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo (!empty($tax_rate->data['id'])) ? $system->language->translate('title_edit_tax_rate', 'Edit Tax Rate') : $system->language->translate('title_add_new_tax_rate', 'Add New Tax Rate'); ?></h1>
        <?php echo $system->functions->form_draw_form_begin(false, 'post', false, true); ?>
        <table>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_name', 'Name'); ?></strong><br />
              <?php echo $system->functions->form_draw_text_field('name', true); ?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_description', 'Description'); ?></strong><br />
              <?php echo $system->functions->form_draw_text_field('description', true, 'data-size="large"'); ?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_tax_class', 'Tax Class'); ?></strong><br />
              <?php echo $system->functions->form_draw_tax_classes_list('tax_class_id', true); ?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_geo_zone', 'Geo Zone'); ?></strong><br />
              <?php echo $system->functions->form_draw_geo_zones_list('geo_zone_id', true); ?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_rate', 'Rate'); ?></strong><br />
              <?php echo $system->functions->form_draw_decimal_field('rate', true); ?> <?php echo $system->functions->form_draw_select_field('type', array(array('percent'), array('fixed')), true); ?>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_rule', 'Rule'); ?>: <?php echo $system->language->translate('title_customer_type', 'Customer Type'); ?></strong><br />
              <label><?php echo $system->functions->form_draw_radio_button('customer_type', 'individuals', true); ?> <?php echo $system->language->translate('text_tax_rate_rule_individuals', 'Applies to individuals'); ?></label><br />
              <label><?php echo $system->functions->form_draw_radio_button('customer_type', 'companies', true); ?> <?php echo $system->language->translate('text_tax_rate_rule_companies', 'Applies to companies'); ?></label><br />
              <label><?php echo $system->functions->form_draw_radio_button('customer_type', 'both', empty($_POST['customer_type']) ? 'both' : true); ?> <?php echo $system->language->translate('text_tax_rate_rule_both_of_the_above', 'Applies to both of above'); ?></label>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_rule', 'Rule'); ?>: <?php echo $system->language->translate('title_tax_id', 'Tax ID'); ?></strong><br />
              <label><?php echo $system->functions->form_draw_radio_button('tax_id_rule', 'with', true); ?> <?php echo $system->language->translate('text_tax_rate_rule_customers_with_tax_id', 'Applies to customers with a tax ID'); ?></label><br />
              <label><?php echo $system->functions->form_draw_radio_button('tax_id_rule', 'without', true); ?> <?php echo $system->language->translate('text_tax_rate_rule_customers_without_tax_id', 'Applies to customers without a tax ID'); ?></label><br />
              <label><?php echo $system->functions->form_draw_radio_button('tax_id_rule', 'both', empty($_POST['customer_type']) ? 'both' : true); ?> <?php echo $system->language->translate('text_tax_rate_rule_both_of_the_above', 'Applies to both of above'); ?></label>
            </td>
          </tr>
          <tr>
            <td align="left" nowrap="nowrap"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($tax_rate->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></td>
          </tr>
        </table>
      <?php echo $system->functions->form_draw_form_end(); ?></td>
    </tr>
  </table>