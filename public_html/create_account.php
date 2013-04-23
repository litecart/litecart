<?php
  require_once('includes/app_header.inc.php');
  
  header('X-Robots-Tag: noindex');
  $system->document->snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  
  $system->breadcrumbs->add($system->language->translate('title_create_account', 'Create Account'), $system->document->link());
  
  $system->document->snippets['title'][] = $system->language->translate('title_create_account', 'Create Account');
  //$system->document->snippets['keywords'] = '';
  //$system->document->snippets['description'] = '';
  
  if (!$_POST) {
    foreach ($system->customer->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }
  
  if (!empty($system->customer->data['id'])) {
    $system->notices->add('errors', $system->language->translate('error_already_logged_in', 'You are already logged in'));
  }
  
  if (!empty($_POST['create_account'])) {
  
    if (isset($_POST['email'])) $_POST['email'] = strtolower($_POST['email']);
    
    if (!empty($_POST['email']) && $system->database->num_rows($system->database->query("select id from ". DB_TABLE_CUSTOMERS ." where email = '". $system->database->input($_POST['email']) ."' limit 1;"))) $system->notices->add('errors', $system->language->translate('error_email_already_registered', 'The e-mail address already exists in our customer database. Please login or select a different e-mail address.'));
      
    if (empty($_POST['email'])) $system->notices->add('errors', $system->language->translate('error_email_missing', 'You must enter your e-mail address.'));
    
    if (empty($_POST['password'])) $system->notices->add('errors', $system->language->translate('error_missing_password', 'You must enter a password.'));
    if (empty($_POST['confirmed_password'])) $system->notices->add('errors', $system->language->translate('error_missing_confirmed_password', 'You must confirm your password.'));
    if (isset($_POST['password']) && isset($_POST['confirmed_password']) && $_POST['password'] != $_POST['confirmed_password']) $system->notices->add('errors', $system->language->translate('error_passwords_missmatch', 'The passwords did not match.'));
    
    if (empty($_POST['firstname'])) $system->notices->add('errors', $system->language->translate('error_missing_firstname', 'You must enter a first name.'));
    if (empty($_POST['lastname'])) $system->notices->add('errors', $system->language->translate('error_missing_lastname', 'You must enter a last name.'));
    if (empty($_POST['address1'])) $system->notices->add('errors', $system->language->translate('error_missing_address1', 'You must enter an address.'));
    if (empty($_POST['city'])) $system->notices->add('errors', $system->language->translate('error_missing_city', 'You must enter a city.'));
    if (empty($_POST['postcode'])) $system->notices->add('errors', $system->language->translate('error_missing_postcode', 'You must enter a postcode.'));
    if (empty($_POST['country_code'])) $system->notices->add('errors', $system->language->translate('error_missing_country', 'You must select a country.'));
    if (empty($_POST['country_code']) && $system->functions->reference_country_num_zones($_POST['country_code'])) $system->notices->add('errors', $system->language->translate('error_missing_zone', 'You must select a zone.'));
    
    if (!$system->notices->get('errors')) {
      
      $customer = new ctrl_customer();
      
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
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $customer->data[$field] = $_POST[$field];
      }
      
      $customer->save();
      
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
      
      $system->notices->add('success', $system->language->translate('success_your_customer_account_has_been_created', 'Your customer account has been created.'));
      
    // Login user
      $system->customer->load($customer->data['id']);
      
      header('Location: '. $system->document->link(WS_DIR_HTTP_HOME));
      exit;
    }
  }
  
?>
<div class="box">
  <div class="heading"><h1><?php echo $system->language->translate('title_create_account', 'Create Account'); ?></h1></div>
  <div class="content">
    <?php echo $system->functions->form_draw_form_begin('customer_form', 'post'); ?>
      <table>
        <tr>
          <td nowrap="nowrap"><?php echo $system->language->translate('title_tax_id', 'Tax ID'); ?><br />
            <?php echo $system->functions->form_draw_input('tax_id', true); ?></td>
          <td><?php echo $system->language->translate('title_company', 'Company'); ?><br />
            <?php echo $system->functions->form_draw_input('company', true); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_firstname', 'First Name'); ?> <span class="required">*</span><br />
            <?php echo $system->functions->form_draw_input('firstname', true); ?></td>
          <td><?php echo $system->language->translate('title_lastname', 'Last Name'); ?> <span class="required">*</span><br />
            <?php echo $system->functions->form_draw_input('lastname', true); ?></td>
        </tr>
        <tr>
          <td width="50%"><?php echo $system->language->translate('title_email', 'E-mail'); ?> <span class="required">*</span><br />
            <?php echo $system->functions->form_draw_email_field('email', true); ?></td>
          <td><?php echo $system->language->translate('title_phone', 'Phone'); ?><br />
            <?php echo $system->functions->form_draw_input('phone', true); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_address1', 'Address 1'); ?> <span class="required">*</span><br />
            <?php echo $system->functions->form_draw_input('address1', true); ?></td>
          <td><?php echo $system->language->translate('title_address2', 'Address 2'); ?><br />
          <?php echo $system->functions->form_draw_input('address2', true); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_city', 'City'); ?> <span class="required">*</span><br />
            <?php echo $system->functions->form_draw_input('city', true); ?></td>
          <td><?php echo $system->language->translate('title_postcode', 'Postcode'); ?> <span class="required">*</span><br />
            <?php echo $system->functions->form_draw_input('postcode', true); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_country', 'Country'); ?> <span class="required">*</span><br />
            <?php echo $system->functions->form_draw_countries_list('country_code', true); ?></td>
          <td><?php echo $system->language->translate('title_zone', 'Zone'); ?> <span class="required">*</span><br />
            <?php echo form_draw_zones_list(isset($_POST['country_code']) ? $_POST['country_code'] : '', 'zone_code', true); ?></td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_desired_password', 'Desired Password'); ?> <span class="required">*</span><br />
          <?php echo $system->functions->form_draw_input('password', '', 'password'); ?></td>
          <td nowrap="nowrap"><?php echo $system->language->translate('title_confirm_password', 'Confirm Password'); ?> <span class="required">*</span><br />
          <?php echo $system->functions->form_draw_input('confirmed_password', '', 'password'); ?></td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="2"><?php echo $system->functions->form_draw_button('create_account', $system->language->translate('title_create_account', 'Create Account')); ?></td>
        </tr>
      </table>
    <?php echo $system->functions->form_draw_form_end(); ?>
  </div>
</div>

<script type="text/javascript">
  
  $("form[name='customer_form'] input, form[name='customer_form'] select").change(function() {
    if ($(this).val() == '') return;
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
</script>

<?php
  if ($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'] == __FILE__) {
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
  }
?>