<?php
  
  if (isset($_GET['customer_id'])) {
    $customer = new ctrl_customer($_GET['customer_id']);
    
    if (empty($_POST)) {
      foreach ($customer->data as $key => $value) {
        $_POST[$key] = $value;
      }
    }
    
  } else {
    $customer = new ctrl_customer();
  }
  
  if (isset($_POST['save'])) {

    if (!$system->notices->get('errors')) {
      
      if (!isset($_POST['newsletter'])) $_POST['newsletter'] = 0;
      
      $fields = array(
        'email',
        'password',
        'tax_id',
        'company',
        'firstname',
        'lastname',
        'address1',
        'address2',
        'postcode',
        'city',
        'country_code',
        'zone_code',
        'phone',
        'mobile',
        'newsletter',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $customer->data[$field] = $_POST[$field];
      }
      
      $customer->save();
      
      if (!empty($_POST['new_password'])) $customer->set_password($_POST['new_password']);
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array('app' => $_GET['app'], 'doc' => 'customers')));
      exit;
    }
  }
  
  if (isset($_POST['delete'])) {

    $customer->delete();
    
    $system->notices->add('success', $system->language->translate('success_post_deleted', 'Post deleted'));
    header('Location: '. $system->document->link('', array('app' => $_GET['app'], 'doc' => 'customers')));
    exit;
  }
 
?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo !empty($customer->data['id']) ? $system->language->translate('title_edit_customer', 'Edit Customer Profile') : $system->language->translate('title_add_new_customer_profile', 'Add New Customer Profile'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('customer_form', 'post'); ?>

<table>
  <tr>
    <td nowrap="nowrap"><?php echo $system->language->translate('title_tax_id', 'Tax ID'); ?><br />
      <?php echo $system->functions->form_draw_input('tax_id', true); ?></td>
    <td><?php echo $system->language->translate('title_company', 'Company'); ?><br />
      <?php echo $system->functions->form_draw_input('company', true); ?></td>
  </tr>
  <tr>
    <td><?php echo $system->language->translate('title_firstname', 'First Name'); ?><br />
      <?php echo $system->functions->form_draw_input('firstname', true); ?></td>
    <td><?php echo $system->language->translate('title_lastname', 'Last Name'); ?><br />
      <?php echo $system->functions->form_draw_input('lastname', true); ?></td>
  </tr>
  <tr>
    <td><?php echo $system->language->translate('title_address1', 'Address 1'); ?><br />
      <?php echo $system->functions->form_draw_input('address1', true); ?></td>
    <td><?php echo $system->language->translate('title_address2', 'Address 2'); ?><br />
    <?php echo $system->functions->form_draw_input('address2', true); ?></td>
  </tr>
  <tr>
    <td><?php echo $system->language->translate('title_city', 'City'); ?><br />
      <?php echo $system->functions->form_draw_input('city', true); ?></td>
    <td><?php echo $system->language->translate('title_postcode', 'Postcode'); ?><br />
      <?php echo $system->functions->form_draw_input('postcode', true); ?></td>
  </tr>
  <tr>
    <td><?php echo $system->language->translate('title_country', 'Country'); ?><br />
      <?php echo $system->functions->form_draw_countries_list('country_code', true); ?></td>
    <td><?php echo $system->language->translate('title_zone', 'Zone'); ?><br />
      <?php echo $system->functions->form_draw_zones_list(isset($_POST['country_code']) ? $_POST['country_code'] : '', 'zone_code', true); ?></td>
  </tr>
  <tr>
    <td width="50%"><?php echo $system->language->translate('title_email_address', 'E-mail Address'); ?><br />
      <?php echo $system->functions->form_draw_email_field('email', true); ?></td>
    <td><?php echo $system->language->translate('title_phone', 'Phone'); ?><br />
    <?php echo $system->functions->form_draw_input('phone', true); ?></td>
  </tr>
  <tr>
    <td><?php echo $system->language->translate('title_newsletter', 'Newsletter'); ?><br />
      <?php echo $system->functions->form_draw_checkbox('newsletter', '1', true); ?> <?php echo $system->language->translate('title_subscribe', 'Subscribe'); ?></td>
    <td><?php echo !empty($customer->data['id']) ? $system->language->translate('title_new_password', 'New Password') : $system->language->translate('title_password', 'Password'); ?><br />
      <?php echo $system->functions->form_draw_input('new_password', '', 'password'); ?></td>
  </tr>
</table>

<script type="text/javascript">
  $("select[name='country[code]']").change(function(){
    $('body').css('cursor', 'wait');
    $.ajax({
      url: '<?php echo WS_DIR_AJAX .'zones.json.php'; ?>?country_code=' + $(this).val(),
      type: 'get',
      cache: true,
      async: true,
      dataType: 'json',
      error: function(jqXHR, textStatus, errorThrown) {
        alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
      },
      success: function(data) {
        $('select[name=\'zone[code]\']').html('');
        if ($('select[name=\'zone[code]\']').attr('disabled')) $('select[name=\'zone[code]\']').removeAttr('disabled');
        if (data) {
          $.each(data, function(i, zone) {
            $('select[name=\'zone[code]\']').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
          });
        } else {
          $('select[name=\'zone[code]\']').attr('disabled', 'disabled');
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
  });
</script>

<p><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($customer->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></p>

<?php echo $system->functions->form_draw_form_end(); ?>