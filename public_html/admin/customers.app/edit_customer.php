<?php
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . 'customer.inc.php');
  
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
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $customer->data[$field] = $_POST[$field];
      }
      
      $customer->save();
      
      if (!empty($_POST['new_password'])) $customer->set_password($_POST['new_password']);
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array('app' => $_GET['app'], 'doc' => 'customers.php')));
      exit;
    }
  }
  
  if (isset($_POST['delete'])) {

    $customer->delete();
    
    $system->notices->add('success', $system->language->translate('success_post_deleted', 'Post deleted'));
    header('Location: '. $system->document->link('', array('app' => $_GET['app'], 'doc' => 'customers.php')));
    exit;
  }
 
?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle;" style="margin-right: 10px;" /><?php echo !empty($customer->data['id']) ? $system->language->translate('title_edit_customer', 'Edit Customer Profile') : $system->language->translate('title_add_new_customer_profile', 'Add New Customer Profile'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('customer_form', 'post'); ?>

<table>
  <tr>
    <td><?php echo $system->language->translate('title_company', 'Company'); ?><br />
      <?php echo $system->functions->form_draw_input_field('company', isset($_POST['company']) ? $_POST['company'] : ''); ?></td>
    <td nowrap="nowrap"><?php echo $system->language->translate('title_tax_id', 'Tax ID'); ?><br />
      <?php echo $system->functions->form_draw_input_field('tax_id', isset($_POST['tax_id']) ? $_POST['tax_id'] : ''); ?></td>
  </tr>
  <tr>
    <td><?php echo $system->language->translate('title_firstname', 'First Name'); ?><br />
      <?php echo $system->functions->form_draw_input_field('firstname', isset($_POST['firstname']) ? $_POST['firstname'] : ''); ?></td>
    <td><?php echo $system->language->translate('title_lastname', 'Last Name'); ?><br />
      <?php echo $system->functions->form_draw_input_field('lastname', isset($_POST['lastname']) ? $_POST['lastname'] : ''); ?></td>
  </tr>
  <tr>
    <td width="50%"><?php echo $system->language->translate('title_email', 'E-mail'); ?><br />
      <?php echo $system->functions->form_draw_input_field('email', isset($_POST['email']) ? $_POST['email'] : ''); ?></td>
    <td><?php echo $system->language->translate('title_phone', 'Phone'); ?><br />
    <?php echo $system->functions->form_draw_input_field('phone', isset($_POST['phone']) ? $_POST['phone'] : ''); ?></td>
  </tr>
  <tr>
    <td><?php echo $system->language->translate('title_address1', 'Address 1'); ?><br />
      <?php echo $system->functions->form_draw_input_field('address1', isset($_POST['address1']) ? $_POST['address1'] : ''); ?></td>
    <td><?php echo $system->language->translate('title_address2', 'Address 2'); ?><br />
    <?php echo $system->functions->form_draw_input_field('address2', isset($_POST['address2']) ? $_POST['address2'] : ''); ?></td>
  </tr>
  <tr>
    <td><?php echo $system->language->translate('title_city', 'City'); ?><br />
      <?php echo $system->functions->form_draw_input_field('city', isset($_POST['city']) ? $_POST['city'] : ''); ?></td>
    <td><?php echo $system->language->translate('title_postcode', 'Postcode'); ?><br />
      <?php echo $system->functions->form_draw_input_field('postcode', isset($_POST['postcode']) ? $_POST['postcode'] : ''); ?></td>
  </tr>
  <tr>
    <td><?php echo $system->language->translate('title_country', 'Country'); ?><br />
      <?php echo $system->functions->form_draw_countries_list('country_code', isset($_POST['country_code']) ? $_POST['country_code'] : ''); ?></td>
    <td><?php echo $system->language->translate('title_zone', 'Zone'); ?><br />
      <?php echo $system->functions->form_draw_zones_list(isset($_POST['country_code']) ? $_POST['country_code'] : '', 'zone_code', isset($_POST['zone_code']) ? $_POST['zone_code'] : ''); ?></td>
  </tr>
  <tr>
    <td><?php echo !empty($customer->data['id']) ? $system->language->translate('title_new_password', 'New Password') : $system->language->translate('title_password', 'Password'); ?><br />
      <?php echo $system->functions->form_draw_input_field('new_password', '', 'password'); ?></td>
    <td>&nbsp;</td>
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

<p><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"'); ?> <?php echo (isset($customer->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"') : false; ?></p>

<?php echo $system->functions->form_draw_form_end(); ?>