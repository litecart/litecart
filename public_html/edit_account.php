<?php
  require_once('includes/app_header.inc.php');
  
  header('X-Robots-Tag: noindex');
  $system->document->snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  
  if (empty($system->customer->data['id'])) die('You must be logged in to access this page.');
  
  $system->document->snippets['title'][] = $system->language->translate('title_edit_account', 'Edit Account');
  //$system->document->snippets['keywords'] = '';
  //$system->document->snippets['description'] = '';
  
  $system->breadcrumbs->add($system->language->translate('title_account', 'Account'), '');
  $system->breadcrumbs->add($system->language->translate('title_edit_account', 'Edit Account'), 'edit_account.php');
  
  $customer = new ctrl_customer($system->customer->data['id']);
  
  if (!$_POST) {
    foreach ($customer->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }
  
  if (!empty($_POST['save'])) {
  
    if (isset($_POST['email'])) $_POST['email'] = strtolower($_POST['email']);
    
    if ($system->database->num_rows($system->database->query("select id from ". DB_TABLE_CUSTOMERS ." where email = '". $system->database->input($_POST['email']) ."' and id != '". $customer->data['id'] ."' limit 1;"))) $system->notices->add('errors', $system->language->translate('error_email_already_registered', 'The e-mail address already exists in our customer database.'));
    
    if (empty($_POST['email'])) $system->notices->add('errors', $system->language->translate('error_email_missing', 'You must enter an e-mail address.'));
      
    if (!empty($_POST['new_password'])) {
      if (empty($_POST['confirmed_password'])) $system->notices->add('errors', $system->language->translate('error_missing_confirmed_password', 'You must confirm your password.'));
      if (isset($_POST['new_password']) && isset($_POST['confirmed_password']) && $_POST['new_password'] != $_POST['confirmed_password']) $system->notices->add('errors', $system->language->translate('error_passwords_missmatch', 'The passwords did not match.'));
    }
    
    if (empty($_POST['firstname'])) $system->notices->add('errors', $system->language->translate('error_missing_firstname', 'You must enter a first name.'));
    if (empty($_POST['lastname'])) $system->notices->add('errors', $system->language->translate('error_missing_lastname', 'You must enter a last name.'));
    if (empty($_POST['address1'])) $system->notices->add('errors', $system->language->translate('error_missing_address1', 'You must enter an address.'));
    if (empty($_POST['city'])) $system->notices->add('errors', $system->language->translate('error_missing_city', 'You must enter a city.'));
    if (empty($_POST['postcode'])) $system->notices->add('errors', $system->language->translate('error_missing_postcode', 'You must enter a postcode.'));
    if (empty($_POST['country_code'])) $system->notices->add('errors', $system->language->translate('error_missing_country', 'You must select a country.'));
    if (empty($_POST['country_code']) && $system->functions->reference_country_num_zones($_POST['country_code'])) $system->notices->add('errors', $system->language->translate('error_missing_zone', 'You must select a zone.'));
      
    if (!empty($_POST['different_shipping_address'])) {
      if (empty($_POST['shipping_address']['firstname'])) $system->notices->add('errors', $system->language->translate('error_missing_firstname', 'You must enter a first name.'));
      if (empty($_POST['shipping_address']['lastname'])) $system->notices->add('errors', $system->language->translate('error_missing_lastname', 'You must enter a last name.'));
      if (empty($_POST['shipping_address']['address1'])) $system->notices->add('errors', $system->language->translate('error_missing_address1', 'You must enter an address.'));
      if (empty($_POST['shipping_address']['city'])) $system->notices->add('errors', $system->language->translate('error_missing_city', 'You must enter a city.'));
      if (empty($_POST['shipping_address']['postcode'])) $system->notices->add('errors', $system->language->translate('error_missing_postcode', 'You must enter a postcode.'));
      if (empty($_POST['shipping_address']['country_code'])) $system->notices->add('errors', $system->language->translate('error_missing_country', 'You must select a country.'));
      if (empty($_POST['shipping_address']['country_code']) && $system->functions->reference_country_num_zones($_POST['shipping_address']['country_code'])) $system->notices->add('errors', $system->language->translate('error_missing_zone', 'You must select a zone.'));
    }
    
    if (!$system->notices->get('errors')) {
      
      if (!isset($_POST['different_shipping_address'])) $_POST['different_shipping_address'] = 0;
      
      $fields = array(
        'email',
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
        'different_shipping_address',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $customer->data[$field] = $_POST[$field];
      }
      
      $fields = array(
        'company',
        'firstname',
        'lastname',
        'address1',
        'address2',
        'postcode',
        'city',
        'country_code',
        'zone_code',
      );
      foreach ($fields as $field) {
        if (isset($_POST['shipping_address'][$field])) $customer->data['shipping_address'][$field] = $_POST['shipping_address'][$field];
      }
      
      if (empty($_POST['different_shipping_address'])) {
        $fields = array(
          'company',
          'firstname',
          'lastname',
          'address1',
          'address2',
          'postcode',
          'city',
          'country_code',
          'zone_code',
        );
        foreach ($fields as $key) {
          $customer->data['shipping_address'][$key] = $customer->data[$key];
        }
      }
      
      if (!empty($_POST['new_password'])) $customer->set_password($_POST['new_password']);
      
      $customer->save();
      $system->customer->data = $customer->data;
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved successfully.'));
      
      header('Location: '. $system->document->link());
      exit;
    }
  }
  
?>
  <div class="box">
    <div class="heading"><h1><?php echo $system->language->translate('title_customer_information', 'Customer Information'); ?></h1></div>
    <div class="content" style="padding: 0px;">
      <?php echo $system->functions->form_draw_form_begin('customer_form', 'post'); ?>
        <table style="width: 100%">
          <tr>
            <td style="width: 50%;">
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
                  <td><?php echo $system->language->translate('title_email', 'E-mail'); ?><br />
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
                    <?php echo form_draw_zones_list(isset($_POST['country_code']) ? $_POST['country_code'] : '', 'zone_code', isset($_POST['zone_code']) ? $_POST['zone_code'] : ''); ?></td>
                </tr>
                <tr>
                  <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                  <td><?php echo $system->language->translate('title_new_password', 'New Password'); ?><br />
                  <?php echo $system->functions->form_draw_input_field('new_password', '', 'password'); ?></td>
                  <td nowrap="nowrap"><?php echo $system->language->translate('title_confirm_password', 'Confirm Password'); ?><br />
                  <?php echo $system->functions->form_draw_input_field('confirmed_password', '', 'password'); ?></td>
                </tr>
              </table>
            </td>
            <td>
              <h3 style="margin-top: 0px;"><label for="different_shipping_address"><?php echo $system->functions->form_draw_checkbox('different_shipping_address', '1', (empty($_POST['different_shipping_address'])) ? '' : '1', 'style="margin: 0px;" onclick="if (this.checked == true) $(\'#shipping-address-container\').slideDown(); else $(\'#shipping-address-container\').slideUp();"'); ?> <?php echo $system->language->translate('title_different_shipping_address', 'Different Shipping Address'); ?></label></h3>
              <div id="shipping-address-container"<?php echo (empty($_POST['different_shipping_address'])) ? ' style="display: none;"' : false; ?>>
                <table>
                  <tr>
                    <td><?php echo $system->language->translate('title_company', 'Company'); ?><br />
                      <?php echo $system->functions->form_draw_input_field('shipping_address[company]', isset($_POST['shipping_address']['company']) ? $_POST['shipping_address']['company'] : ''); ?></td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr>
                    <td><?php echo $system->language->translate('title_firstname', 'First Name'); ?><br />
                      <?php echo $system->functions->form_draw_input_field('shipping_address[firstname]', isset($_POST['shipping_address']['firstname']) ? $_POST['shipping_address']['firstname'] : ''); ?></td>
                    <td><?php echo $system->language->translate('title_lastname', 'LastName'); ?><br />
                      <?php echo $system->functions->form_draw_input_field('shipping_address[lastname]', isset($_POST['shipping_address']['lastname']) ? $_POST['shipping_address']['lastname'] : ''); ?></td>
                  </tr>
                  <tr>
                    <td><?php echo $system->language->translate('title_address1', 'Address 1'); ?><br />
                      <?php echo $system->functions->form_draw_input_field('shipping_address[address1]', isset($_POST['shipping_address']['address1']) ? $_POST['shipping_address']['address1'] : ''); ?></td>
                    <td><?php echo $system->language->translate('title_address2', 'Address 2'); ?><br />
                      <?php echo $system->functions->form_draw_input_field('shipping_address[address2]', isset($_POST['shipping_address']['address2']) ? $_POST['shipping_address']['address2'] : ''); ?></td>
                  </tr>
                  <tr>
                    <td><?php echo $system->language->translate('title_city', 'City'); ?><br />
                      <?php echo $system->functions->form_draw_input_field('shipping_address[city]', isset($_POST['shipping_address']['city']) ? $_POST['shipping_address']['city'] : ''); ?></td>
                    <td><?php echo $system->language->translate('title_postcode', 'Postcode'); ?><br />
                      <?php echo $system->functions->form_draw_input_field('shipping_address[postcode]', isset($_POST['shipping_address']['postcode']) ? $_POST['shipping_address']['postcode'] : ''); ?></td>
                  </tr>
                  <tr>
                    <td><?php echo $system->language->translate('title_country', 'Country'); ?><br />
                      <?php echo $system->functions->form_draw_countries_list('shipping_address[country_code]', isset($_POST['shipping_address']['country_code']) ? $_POST['shipping_address']['country_code'] : ''); ?></td>
                    <td><?php echo $system->language->translate('title_zone', 'Zone'); ?><br />
                      <?php echo form_draw_zones_list(isset($_POST['shipping_address']['country_code']) ? $_POST['shipping_address']['country_code'] : '', 'shipping_address[zone_code]', isset($_POST['shipping_address']['zone_code']) ? (isset($_POST['shipping_address']['zone_code']) ? $_POST['shipping_address']['zone_code'] : '') : ''); ?></td>
                  </tr>
                </table>
              </div>
            </td>
          </tr>
          <tr>
          <td colspan="2" align="center"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save')); ?></td>
          </tr>
        </table>
      <?php echo $system->functions->form_draw_form_end(); ?>
    </div>
  </div>
  
  <script type="text/javascript">
    $("select[name='country_code']").change(function(){
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
          $("select[name='zone_code']").html('');
          if ($("select[name='zone_code']").attr('disabled')) $("select[name='zone_code']").removeAttr('disabled');
          if (data) {
            $.each(data, function(i, zone) {
              $("select[name='zone_code']").append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
            });
          } else {
            $("select[name='zone_code']").attr('disabled', 'disabled');
          }
        },
        complete: function() {
          $('body').css('cursor', 'auto');
        }
      });
    });
    
    $("select[name='shipping_country_code']").change(function(){
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
          $("select[name='shipping_zone_code']").html('');
          if ($("select[name='shipping_zone_code']").attr('disabled')) $("select[name='shipping_zone_code']").removeAttr('disabled');
          if (data) {
            $.each(data, function(i, zone) {
              $("select[name='shipping_zone_code']").append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
            });
          } else {
            $("select[name='shipping_zone_code']").attr('disabled', 'disabled');
          }
        },
        complete: function() {
          $('body').css('cursor', 'auto');
        }
      });
    });
  </script>
<?php
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>