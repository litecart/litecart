<?php

  if (!empty($_GET['customer_id'])) {
    $customer = new ctrl_customer($_GET['customer_id']);
  } else {
    $customer = new ctrl_customer();
  }

  if (empty($_POST)) {
      foreach ($customer->data as $key => $value) {
        $_POST[$key] = $value;
      }
    }

  breadcrumbs::add(!empty($customer->data['id']) ? language::translate('title_edit_customer', 'Edit Customer') : language::translate('title_add_new_customer', 'Add New Customer'));

  if (isset($_POST['save'])) {

    if (empty(notices::$data['errors'])) {

      if (empty($_POST['newsletter'])) $_POST['newsletter'] = 0;

      $fields = array(
        'code',
        'status',
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

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('', array('app' => $_GET['app'], 'doc' => 'customers')));
      exit;
    }
  }

  if (isset($_POST['delete'])) {

    $customer->delete();

    notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
    header('Location: '. document::link('', array('app' => $_GET['app'], 'doc' => 'customers')));
    exit;
  }

  if (!empty($customer->data['id'])) {
    $order_statuses = array();
    $orders_status_query = database::query(
      "select id from ". DB_TABLE_ORDER_STATUSES ." where is_sale;"
    );
    while ($order_status = database::fetch($orders_status_query)) {
      $order_statuses[] = (int)$order_status['id'];
    }

    $orders_query = database::query(
      "select count(o.id) as total_count, sum(oi.total_sales) as total_sales
      from ". DB_TABLE_ORDERS ." o
      left join (
        select order_id, sum(price * quantity) as total_sales from ". DB_TABLE_ORDERS_ITEMS ."
        group by order_id
      ) oi on (oi.order_id = o.id)
      where o.order_status_id in ('". implode("', '", $order_statuses) ."')
      and (o.customer_id = '". (int)$customer->data['id'] ."' or o.customer_email = '". database::input($customer->data['email']) ."');"
    );
    $orders = database::fetch($orders_query);
  }

?>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo !empty($customer->data['id']) ? language::translate('title_edit_customer', 'Edit Customer') : language::translate('title_add_new_customer', 'Add New Customer'); ?></h1>

<?php if (!empty($customer->data['id'])) { ?>
  <table class="dataTable">
    <tr>
      <td><?php echo language::translate('title_orders', 'Orders'); ?><br />
        <?php echo !empty($orders['total_count']) ? (int)$orders['total_count'] : '0'; ?>
      </td>
      <td><?php echo language::translate('title_total_sales', 'Total Sales'); ?><br />
        <?php echo currency::format(!empty($orders['total_sales']) ? $orders['total_sales'] : 0, false, false, settings::get('store_currency_code')); ?>
      </td>
    </tr>
  </table>
<?php } ?>

<?php echo functions::form_draw_form_begin('customer_form', 'post'); ?>

  <table>
    <tr>
      <td width="50%"><?php echo language::translate('title_status', 'Status'); ?><br />
        <?php echo functions::form_draw_toggle('status', isset($_POST['status']) ? $_POST['status'] : '1', 'e/d'); ?>
      </td>
      <td></td>
    </tr>
    <tr>
      <td><?php echo language::translate('title_code', 'Code'); ?><br />
        <?php echo functions::form_draw_text_field('code', true); ?></td>
      <td><?php echo language::translate('title_email_address', 'Email Address'); ?><br />
        <?php echo functions::form_draw_email_field('email', true); ?></td>
    </tr>
    <tr>
      <td><?php echo language::translate('title_tax_id', 'Tax ID'); ?><br />
        <?php echo functions::form_draw_text_field('tax_id', true); ?></td>
      <td><?php echo language::translate('title_company', 'Company'); ?><br />
        <?php echo functions::form_draw_text_field('company', true); ?></td>
    </tr>
    <tr>
      <td><?php echo language::translate('title_firstname', 'First Name'); ?><br />
        <?php echo functions::form_draw_text_field('firstname', true); ?></td>
      <td><?php echo language::translate('title_lastname', 'Last Name'); ?><br />
        <?php echo functions::form_draw_text_field('lastname', true); ?></td>
    </tr>
    <tr>
      <td><?php echo language::translate('title_address1', 'Address 1'); ?><br />
        <?php echo functions::form_draw_text_field('address1', true); ?></td>
      <td><?php echo language::translate('title_address2', 'Address 2'); ?><br />
      <?php echo functions::form_draw_text_field('address2', true); ?></td>
    </tr>
    <tr>
      <td><?php echo language::translate('title_city', 'City'); ?><br />
        <?php echo functions::form_draw_text_field('city', true); ?></td>
      <td><?php echo language::translate('title_postcode', 'Postcode'); ?><br />
        <?php echo functions::form_draw_text_field('postcode', true); ?></td>
    </tr>
    <tr>
      <td><?php echo language::translate('title_country', 'Country'); ?><br />
        <?php echo functions::form_draw_countries_list('country_code', true); ?></td>
      <td><?php echo language::translate('title_zone', 'Zone'); ?><br />
        <?php echo functions::form_draw_zones_list(isset($_POST['country_code']) ? $_POST['country_code'] : '', 'zone_code', true); ?></td>
    </tr>
    <tr>
      <td><?php echo language::translate('title_phone', 'Phone'); ?><br />
      <?php echo functions::form_draw_phone_field('phone', true); ?></td>
      <td><?php echo language::translate('title_mobile_phone', 'Mobile Phone'); ?><br />
      <?php echo functions::form_draw_phone_field('mobile', true); ?></td>
    </tr>
    <tr>
      <td><?php echo language::translate('title_newsletter', 'Newsletter'); ?><br />
        <label><?php echo functions::form_draw_checkbox('newsletter', '1', true); ?> <?php echo language::translate('title_subscribe', 'Subscribe'); ?></label></td>
      <td><?php echo !empty($customer->data['id']) ? language::translate('title_new_password', 'New Password') : language::translate('title_password', 'Password'); ?><br />
        <?php echo functions::form_draw_text_field('new_password', '', 'password'); ?></td>
    </tr>
  </table>

  <script>
    $("select[name='country[code]']").change(function(){
      $('body').css('cursor', 'wait');
      $.ajax({
        url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
        type: 'get',
        cache: true,
        async: true,
        dataType: 'json',
        error: function(jqXHR, textStatus, errorThrown) {
          alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
        },
        success: function(data) {
          $('select[name="zone_code"]').html('');
          if ($('select[name="zone_code"]').attr('disabled')) $('select[name="zone_code"]').removeAttr('disabled');
          if (data) {
            $.each(data, function(i, zone) {
              $('select[name="zone_code"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
            });
          } else {
            $('select[name="zone_code"]').attr('disabled', 'disabled');
          }
        },
        complete: function() {
          $('body').css('cursor', 'auto');
        }
      });
    });
  </script>

  <p><span class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($customer->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></span></p>

<?php echo functions::form_draw_form_end(); ?>
