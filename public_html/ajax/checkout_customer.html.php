<?php
  if ($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'] == __FILE__) {
    require_once('../includes/app_header.inc.php');
    header('Content-type: text/html; charset='. $system->language->selected['charset']);
    $system->document->layout = 'default';
    $system->document->viewport = 'ajax';
  }
  
  if ($system->cart->data['total']['items'] == 0) return;
  
  if (!$_POST) {
    foreach ($system->customer->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }
  
  if (!empty($_POST['set_addresses']) || !empty($_POST['set_default_addresses'])) {
  
    if (isset($_POST['email'])) $_POST['email'] = strtolower($_POST['email']);
    
    if (empty($system->customer->data['id']) && $system->settings->get('register_guests') == 'true') {
      
      if (!empty($_POST['email']) && $system->database->num_rows($system->database->query("select id from ". DB_TABLE_CUSTOMERS ." where email = '". $system->database->input($_POST['email']) ."' limit 1;"))) $system->notices->add('errors', $system->language->translate('error_email_already_registered', 'The e-mail address already exists in our customer database. Please login or select a different e-mail address.'));
      
      if (empty($_POST['email'])) $system->notices->add('errors', $system->language->translate('error_email_missing', 'You must enter your e-mail address.'));
      
      if ($system->settings->get('fields_customer_password') == 'true') {
        if (empty($system->customer->data['desired_password'])) {
          if (empty($_POST['password'])) $system->notices->add('errors', $system->language->translate('error_missing_password', 'You must enter a password.'));
          if (empty($_POST['confirmed_password'])) $system->notices->add('errors', $system->language->translate('error_missing_confirmed_password', 'You must confirm your password.'));
          if (isset($_POST['password']) && isset($_POST['confirmed_password']) && $_POST['password'] != $_POST['confirmed_password']) $system->notices->add('errors', $system->language->translate('error_passwords_missmatch', 'The passwords did not match.'));
        }
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
        if (isset($_POST[$field])) $system->customer->data[$field] = $_POST[$field];
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
      
      if (!empty($system->customer->data['different_shipping_address'])) {
        foreach ($fields as $field) {
          if (isset($_POST['shipping_address'][$field])) $system->customer->data['shipping_address'][$field] = $_POST['shipping_address'][$field];
        }
      } else {
        foreach ($fields as $field) {
          if (isset($_POST[$field])) $system->customer->data['shipping_address'][$field] = $_POST[$field];
        }
      }
      
      $system->customer->data['country_name'] = $system->functions->reference_get_country_name($system->customer->data['country_code']);
      $system->customer->data['zone_name'] = $system->functions->reference_get_zone_name($system->customer->data['country_code'], $system->customer->data['zone_code']);
      
      $system->customer->data['shipping_address']['country_name'] = $system->functions->reference_get_country_name($system->customer->data['shipping_address']['country_code']);
      $system->customer->data['shipping_address']['zone_name'] = $system->functions->reference_get_zone_name($system->customer->data['shipping_address']['country_code'], $system->customer->data['shipping_address']['zone_code']);
      
      if (empty($system->customer->data['id'])) {
        if ($system->settings->get('register_guests') == 'true') {
          
          $customer = new ctrl_customer();
          $customer->data = $system->customer->data;
          $customer->save();
          
          if (empty($_POST['password'])) $_POST['password'] = $system->functions->password_generate(6);
          $customer->set_password($_POST['password']);
          
          $email_message = $system->language->translate('email_subject_account_created', "Welcome %customer_firstname %customer_lastname to %store_name!\r\n\r\nYour account has been created. You can now make purchases in our online store and keep track of history.\r\n\r\nLogin using your e-mail address %customer_email and password %customer_password.\r\n\r\n%store_name\r\n\r\n%store_link");
          
          $translations = array(
            '%store_name' => $system->settings->get('store_name'),
            '%store_link' => $system->document->link(WS_DIR_HTTP_HOME),
            '%customer_firstname' => $_POST['firstname'],
            '%customer_lastname' => $_POST['lastname'],
            '%customer_email' => $_POST['email'],
            '%customer_password' => $_POST['password']
          );
          
          foreach ($translations as $needle => $replace) {
            $email_message = str_replace($needle, $replace, $email_message);
          }
          
          $system->functions->email_send(
            $system->settings->get('store_email'),
            $_POST['email'],
            $system->language->translate('email_subject_customer_account_created', 'Customer Account Created'),
            $email_message
          );
          
          $system->notices->add('success', $system->language->translate('success_account_has_been_created', 'A customer account has been created that will let you keep track of orders.'));
          
        // Login user
          $system->customer->load($customer->data['id']);
        }
        
      } else if (!empty($_POST['set_default_addresses'])) {
        
          $customer = new ctrl_customer();
          $customer->data = $system->customer->data;
          $customer->save();
      }
      
      header('Location: '. $system->document->link());
      exit;
    }
  }
  
?>
  <div class="box" id="box-checkout-account">
    <div class="heading"><h2><?php echo $system->language->translate('title_customer_information', 'Customer Information'); ?></h2></div>
    <div class="content" style="padding: 0px;">
      <?php echo $system->functions->form_draw_form_begin('customer_form', 'post'); ?>
        <table style="width: 100%;">
          <tr>
            <td width="50%" align="left">
              
              <!--<?php if (empty($system->customer->data['id'])) { ?><h3 style="margin: 0;"><?php echo $system->language->translate('title_new_customer', 'New Customer'); ?></h3><?php } ?>-->
              <table>
                <tr>
                  <td nowrap="nowrap"><?php echo $system->language->translate('title_tax_id', 'Tax ID'); ?><br />
                    <?php echo $system->functions->form_draw_input('tax_id', true, 'text', ''); ?></td>
                  <td><?php echo $system->language->translate('title_company', 'Company'); ?><br />
                    <?php echo $system->functions->form_draw_input('company', true, 'text', ''); ?></td>
                </tr>
                <tr>
                  <td><?php echo $system->language->translate('title_firstname', 'First Name'); ?> <span class="required">*</span><br />
                    <?php echo $system->functions->form_draw_input('firstname', true, 'text', ''); ?></td>
                  <td><?php echo $system->language->translate('title_lastname', 'Last Name'); ?> <span class="required">*</span><br />
                    <?php echo $system->functions->form_draw_input('lastname', true, 'text', ''); ?></td>
                </tr>
                <?php if (empty($system->customer->data['id'])) { ?>
                <tr>
                  <td width="50%"><?php echo $system->language->translate('title_email', 'E-mail'); ?> <span class="required">*</span><br />
                    <?php echo $system->functions->form_draw_email_field('email', true, ''); ?></td>
                  <td><?php echo $system->language->translate('title_phone', 'Phone'); ?><br />
                  <?php echo $system->functions->form_draw_input('phone', true, 'text', ''); ?></td>                </tr>
                <?php if ($system->settings->get('fields_customer_password') == 'true') { ?>
                <tr>
                  <td><?php echo $system->language->translate('title_password', 'Password'); ?> <span class="required">*</span><br />
                  <?php echo $system->functions->form_draw_input('password', '', 'password', ''); ?></td>
                  <td nowrap="nowrap"><?php echo $system->language->translate('title_confirm_password', 'Confirm Password'); ?> <span class="required">*</span><br />
                  <?php echo $system->functions->form_draw_input('confirmed_password', '', 'password', ''); ?></td>
                </tr>
                <?php } ?>
                <?php } ?>
                <tr>
                  <td><?php echo $system->language->translate('title_address1', 'Address 1'); ?> <span class="required">*</span><br />
                    <?php echo $system->functions->form_draw_input('address1', true, 'text', ''); ?></td>
                  <td><?php echo $system->language->translate('title_address2', 'Address 2'); ?><br />
                  <?php echo $system->functions->form_draw_input('address2', true, 'text', ''); ?></td>
                </tr>
                <tr>
                  <td><?php echo $system->language->translate('title_postcode', 'Postcode'); ?> <span class="required">*</span><br />
                    <?php echo $system->functions->form_draw_input('postcode', true, 'text', 'style="width: 50px;"'); ?></td>
                  <td><?php echo $system->language->translate('title_city', 'City'); ?> <span class="required">*</span><br />
                    <?php echo $system->functions->form_draw_input('city', true, 'text', ''); ?></td>
                </tr>
                <tr>
                  <td><?php echo $system->language->translate('title_country', 'Country'); ?> <span class="required">*</span><br />
                    <?php echo $system->functions->form_draw_countries_list('country_code', true, ''); ?></td>
                  <td><?php echo $system->language->translate('title_zone', 'Zone'); ?> <span class="required">*</span><br />
                    <?php echo form_draw_zones_list(isset($_POST['country_code']) ? $_POST['country_code'] : '', 'zone_code', true, ''); ?></td>
                </tr>
              </table>
            </td>
            <td align="left">
              <h3 style="margin-top: 0px;"><?php echo $system->functions->form_draw_checkbox('different_shipping_address', '1', empty($_POST['different_shipping_address']) ? '' : '1', 'style="margin: 0px;" onclick="if (this.checked == true) $(\'#shipping-address-container\').slideDown(); else $(\'#shipping-address-container\').slideUp();"'); ?> <?php echo $system->language->translate('title_different_shipping_address', 'Different Shipping Address'); ?></h3>
              <div id="shipping-address-container"<?php echo (empty($_POST['different_shipping_address'])) ? ' style="display: none;"' : false; ?>>
                <table>
                  <tr>
                    <td><?php echo $system->language->translate('title_company', 'Company'); ?><br />
                      <?php echo $system->functions->form_draw_input('shipping_address[company]', true, 'text', ''); ?></td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr>
                    <td><?php echo $system->language->translate('title_firstname', 'First Name'); ?> <span class="required">*</span><br />
                      <?php echo $system->functions->form_draw_input('shipping_address[firstname]', true, 'text', ''); ?></td>
                    <td><?php echo $system->language->translate('title_lastname', 'LastName'); ?> <span class="required">*</span><br />
                      <?php echo $system->functions->form_draw_input('shipping_address[lastname]', true, 'text', ''); ?></td>
                  </tr>
                  <tr>
                    <td><?php echo $system->language->translate('title_address1', 'Address 1'); ?> <span class="required">*</span><br />
                      <?php echo $system->functions->form_draw_input('shipping_address[address1]', true, 'text', ''); ?></td>
                    <td><?php echo $system->language->translate('title_address2', 'Address 2'); ?><br />
                      <?php echo $system->functions->form_draw_input('shipping_address[address2]', true, 'text', ''); ?></td>
                  </tr>
                  <tr>
                    <td><?php echo $system->language->translate('title_postcode', 'Postcode'); ?> <span class="required">*</span><br />
                      <?php echo $system->functions->form_draw_input('shipping_address[postcode]', true, 'text', 'style="width: 50px;"'); ?></td>
                    <td><?php echo $system->language->translate('title_city', 'City'); ?> <span class="required">*</span><br />
                      <?php echo $system->functions->form_draw_input('shipping_address[city]', true, 'text', ''); ?></td>
                  </tr>
                  <tr>
                    <td><?php echo $system->language->translate('title_country', 'Country'); ?> <span class="required">*</span><br />
                      <?php echo $system->functions->form_draw_countries_list('shipping_address[country_code]', true, ''); ?></td>
                    <td><?php echo $system->language->translate('title_zone', 'Zone'); ?> <span class="required">*</span><br />
                      <?php echo form_draw_zones_list(isset($_POST['shipping_address[country_code]']) ? $_POST['shipping_address']['country_code'] : '', 'shipping_address[zone_code]', true, ''); ?></td>
                  </tr>
                </table>
              </div>
            </td>
          </tr>
          <tr>
          <td colspan="2" align="center"><?php echo $system->functions->form_draw_button('set_addresses', $system->language->translate('title_save', 'Save')); ?> <?php echo (!empty($system->customer->data['id'])) ? $system->functions->form_draw_button('set_default_addresses', $system->language->translate('title_set_default', 'Set as Default')) : false; ?></td>
          </tr>
        </table>
      <?php echo $system->functions->form_draw_form_end(); ?>
    </div>
  </div>
  
  <script type="text/javascript">
    
    $("#box-checkout-account input, #box-checkout-account select").change(function() {
      $('body').css('cursor', 'wait');
      $.ajax({
        url: '<?php echo $system->document->link(WS_DIR_AJAX .'get_address.json.php'); ?>?trigger='+$(this).attr('name'),
        type: 'post',
        data: $(this).closest('form').serialize(),
        cache: false,
        async: true,
        dataType: 'json',
        error: function(jqXHR, textStatus, errorThrown) {
          //alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
          alert(errorThrown.message);
        },
        success: function(data) {
          if (data['alert']) {
            alert(data['alert']);
            return;
          }
          $.each(data, function(key, value) {
            console.log(key +" "+ value);
            if ($("input[name='"+key+"']").length && $("input[name='"+key+"']").val() == '') $("input[name='"+key+"']").val(data[key]);
          });
        },
        complete: function() {
          $('body').css('cursor', 'auto');
        }
      });
    });
    
    $("select[name='country_code']").change(function(){
      $('body').css('cursor', 'wait');
      $.ajax({
        url: '<?php echo $system->document->link(WS_DIR_AJAX .'zones.json.php'); ?>?country_code=' + $(this).val(),
        type: 'get',
        cache: true,
        async: true,
        dataType: 'json',
        error: function(jqXHR, textStatus, errorThrown) {
          //alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
          alert(errorThrown.message);
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
    
    $("select[name='shipping_address[country_code]']").change(function(){
      $('body').css('cursor', 'wait');
      $.ajax({
        url: '<?php echo $system->document->link(WS_DIR_AJAX .'zones.json.php'); ?>?country_code=' + $(this).val(),
        type: 'get',
        cache: true,
        async: false,
        dataType: 'json',
        error: function(jqXHR, textStatus, errorThrown) {
          //alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
          alert(errorThrown.message);
        },
        success: function(data) {
          $("select[name='shipping_address[zone_code]']").html('');
          if ($("select[name='shipping_address[zone_code]']").attr('disabled')) $("select[name='shipping_address[zone_code]']").removeAttr('disabled');
          if (data) {
            $.each(data, function(i, zone) {
              $("select[name='shipping_address[zone_code]']").append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
            });
          } else {
            $("select[name='shipping_address[zone_code]']").attr('disabled', 'disabled');
          }
        },
        complete: function() {
          $('body').css('cursor', 'auto');
        }
      });
    });
  </script>
<?php
  
  if ($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'] == __FILE__) {
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
  }
?>