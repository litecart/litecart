<?php
  
  if (empty($_GET['discount_code_id'])) {
    $discount_code = new ctrl_discount_code();
  } else {
    $discount_code = new ctrl_discount_code($_GET['discount_code_id']);
  }
  
  if (empty($_POST)) {
    foreach ($discount_code->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }
  
  // Save data to database
  if (isset($_POST['save'])) {
    
    if (!$system->notices->get('errors')) {
      
      if (empty($_POST['status'])) $_POST['status'] = 0;
      if (empty($_POST['customers'])) $_POST['customers'] = 0;
      if (empty($_POST['categories'])) $_POST['categories'] = 0;
      if (empty($_POST['manufacturers'])) $_POST['manufacturers'] = 0;
      if (empty($_POST['products'])) $_POST['products'] = 0;
      
      $fields = array(
        'status',              
        'code',
        'description',
        'discount',
        'min_subtotal_amount',
        'customers',
        'categories',
        'manufacturers',
        'products',
        'date_valid_from',
        'date_valid_to',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $discount_code->data[$field] = $_POST[$field];
      }
      
      $discount_code->save();
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array('app' => $_GET['app'], 'doc' => 'discount_codes')));
      exit;
    }
  }
  
  // Delete from database
  if (isset($_POST['delete']) && $discount_code) {
    $discount_code->delete();
    $system->notices->add('success', $system->language->translate('success_post_deleted', 'Post deleted'));
    header('Location: '. $system->document->link('', array('app' => $_GET['app'], 'doc' => 'discount_codes')));
    exit();
  }
  
?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo !empty($discount_code->data['id']) ? $system->language->translate('title_edit_discount_code', 'Edit Discount Code') : $system->language->translate('title_create_new_discount_code', 'Create New Discount Code'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('form_discount_code', 'post'); ?>
  
  <table>
    <tr>
      <td><strong><?php echo $system->language->translate('title_status', 'Status'); ?></strong><br />
        <label><?php echo $system->functions->form_draw_checkbox('status', '1'); ?> <?php echo $system->language->translate('title_enabled', 'Enabled'); ?></label>
      </td>
      <td><strong><?php echo $system->language->translate('title_code', 'Code'); ?></strong><br />
        <?php echo $system->functions->form_draw_input_field('code'); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo $system->language->translate('title_description', 'Description'); ?></strong><br />
        <?php echo $system->functions->form_draw_input_field('description'); ?>
      </td>
      <td></td>
    </tr>
    <tr>
      <td><strong><?php echo $system->language->translate('title_discount', 'Discount'); ?></strong><br />
        <?php echo $system->functions->form_draw_input_field('discount'); ?>
      </td>
      <td><strong><?php echo $system->language->translate('title_minimum_subtotal_amount', 'Minimum Subtotal Amount'); ?></strong><br />
        <?php echo $system->functions->form_draw_input_field('min_subtotal_amount'); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo $system->language->translate('title_date_valid_from', 'Date Valid From'); ?></strong><br />
        <?php echo $system->functions->form_draw_date_field('date_valid_from'); ?>
      </td>
      <td><strong><?php echo $system->language->translate('title_date_valid_to', 'Date Valid To'); ?></strong><br />
        <?php echo $system->functions->form_draw_date_field('date_valid_to'); ?>
      </td>
    </tr>
  </table>
  
  <h2><?php echo $system->language->translate('title_apply_to', 'Apply To'); ?></h2>
  <table>
    <tr>
      <td>
        <label><strong><?php echo $system->functions->form_draw_checkbox('customers_toggle', '1', !empty($_POST['customers']) ? 1 : 0); ?> <?php echo $system->language->translate('title_customers', 'Customers'); ?><strong></label><br />
        <?php echo $system->functions->form_draw_customers_list('customers[]', true, true, 'style="width: 175px; height: 175px;"'); ?>
        <script>
          $("input[name='customers_toggle']").change(function() {
            if (!$("input[name='customers_toggle']").is(':checked')) {
              $("select[name='customers[]']").attr('disabled', 'disabled').slideUp('fast');
            } else {
              $("select[name='customers[]']").removeAttr('disabled').slideDown('fast');
            }
          });
          $("input[name='customers_toggle']").trigger('change');
        </script>
        </td>
      </tr>
      <tr>
        <td>
          <label><strong><?php echo $system->functions->form_draw_radio_button('type', 'all', (empty($_POST['categories']) && empty($_POST['manufacturers']) && empty($_POST['products'])) ? 'all' : 0); ?> <?php echo $system->language->translate('text_all_products', 'All products'); ?></strong></label><br />
        </td>
      </tr>
      <tr>
        <td>
          <label><strong><?php echo $system->functions->form_draw_radio_button('type', 'categories', !empty($_POST['categories']) ? 'categories' : 0); ?> <?php echo $system->language->translate('title_categories', 'Categories'); ?></strong></label><br />
          <?php echo $system->functions->form_draw_categories_list('categories[]', true, true, 'style="width: 250px; height: 175px;"'); ?>
        </td>
      </tr>
      <tr>
        <td>
          <label><strong><?php echo $system->functions->form_draw_radio_button('type', 'manufacturers', !empty($_POST['manufacturers']) ? 'manufacturers' : 0); ?> <?php echo $system->language->translate('title_manufacturers', 'Manufacturers'); ?></strong></label><br />
          <?php echo $system->functions->form_draw_manufacturers_list('manufacturers[]', true, true, 'style="width: 175px; height: 175px;"'); ?>
        </td>
      </tr>
      <tr>
        <td>
          <label><strong><?php echo $system->functions->form_draw_radio_button('type', 'products', !empty($_POST['products']) ? 'products' : 0); ?> <?php echo $system->language->translate('title_products', 'Products'); ?></strong></label><br />
          <?php echo $system->functions->form_draw_products_list('products[]', true, true, 'style="width: 175px; height: 175px;"'); ?>
        </td>
      </tr>
    </table>
    <script>
      $("input[name='type']").change(function() {
        $("input[name='type']").each(function() {
          if (!$(this).is(':checked')) {
            $("select[name='"+ $(this).val() +"[]']").attr('disabled', 'disabled').slideUp('fast');
          } else {
            $("select[name='"+ $(this).val() +"[]']").removeAttr('disabled').slideDown('fast');
          }
        });
      });
      $("input[name='type']").trigger('change');
    </script>
  
  <p align="right"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($discount_code->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></p>
  
<?php echo $system->functions->form_draw_form_end(); ?>