<div id="content" class="fourteen-forty">
  {snippet:notices}

  <?php echo functions::form_draw_form_begin('checkout_form', 'post', document::ilink('order_process'), false, 'autocomplete="off"'); ?>

  <section id="box-checkout" class="box">
    <div class="cart wrapper"></div>

    <div class="row" style="grid-gap: 2rem;">
      <div class="col-md-6">
        <div class="customer wrapper"></div>
      </div>

      <div class="col-md-6">
        <div class="shipping wrapper"></div>

        <div class="payment wrapper"></div>
      </div>
    </div>

    <div class="summary wrapper"></div>
  </section>

  <?php echo functions::form_draw_form_end(); ?>
</div>

<script>
// Queue Handler

  var updateQueue = [
    {component: 'cart',     data: null, refresh: true},
    {component: 'customer', data: null, refresh: true},
    {component: 'shipping', data: null, refresh: true},
    {component: 'payment',  data: null, refresh: true},
    {component: 'summary',  data: null, refresh: true}
  ];

  function queueUpdateTask(component, data, refresh) {

    updateQueue = jQuery.grep(updateQueue, function(tasks) {
      return (tasks.component == component) ? false : true;
    });

    updateQueue.push({
      component: component,
      data: data,
      refresh: refresh
    });

    runQueue();
  }

  var queueRunLock = false;
  function runQueue() {

    if (queueRunLock) return;
    if (!updateQueue.length) return;

    queueRunLock = true;

    task = updateQueue.shift();

    if (console) console.log('Processing ' + task.component);

    if (!$('body > .loader-wrapper').length) {
      var loader = '<div class="loader-wrapper">'
                 + '  <div class="loader" style="width: 256px; height: 256px;"></div>'
                 + '</div>';
      $('body').append(loader);
    }

    if (task.refresh) {
      $('#box-checkout .'+ task.component +'.wrapper').fadeTo('fast', 0.15);
    }

    var url = '';
    switch (task.component) {
      case 'cart':
        url = '<?php echo document::ilink('ajax/checkout_cart'); ?>';
        break;
      case 'customer':
        url = '<?php echo document::ilink('ajax/checkout_customer'); ?>';
        break;
      case 'shipping':
        url = '<?php echo document::ilink('ajax/checkout_shipping'); ?>';
        break;
      case 'payment':
        url = '<?php echo document::ilink('ajax/checkout_payment'); ?>';
        break;
      case 'summary':
        url = '<?php echo document::ilink('ajax/checkout_summary'); ?>';
        break;
      default:
        alert('Error: Invalid component ' + task.component);
        break;
    }

    if (task.data === true) {
      switch (task.component) {
        case 'customer':
          task.data = $('#box-checkout-customer :input').serialize();
          break;
        case 'shipping':
          task.data = $('#box-checkout-shipping .option.active :input').serialize();
          break;
        case 'payment':
          task.data = $('#box-checkout-payment .option.active :input').serialize();
          break;
        case 'summary':
          task.data = $('#box-checkout-summary :input').serialize();
          break;
      }
    }

    if (task.component == 'summary') {
      var comments = $(':input[name="comments"]').val();
      var terms_agreed = $(':input[name="terms_agreed"]').prop('checked');
    }

    $.ajax({
      type: task.data ? 'post' : 'get',
      url: url,
      data: task.data,
      dataType: 'html',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType('text/html;charset=<?php echo language::$selected['charset']; ?>');
      },
      error: function(jqXHR, textStatus, errorThrown) {
        $('#box-checkout .'+ task.component +'.wrapper').html('An unexpected error occurred, try reloading the page.');
      },
      success: function(html) {
        if (task.refresh) $('#box-checkout .'+ task.component +'.wrapper').html(html).fadeTo('fast', 1);
        if (task.component == 'summary') {
          $(':input[name="comments"]').val(comments);
          $(':input[name="terms_agreed"]').prop('checked', terms_agreed);
        }
      },
      complete: function(html) {
        if (!updateQueue.length) {
          $('body > .loader-wrapper').fadeOut('fast', function(){
            $(this).remove();
          });
        }
        queueRunLock = false;
        runQueue();
      }
    });
  }

  runQueue();

// Cart

  $('#box-checkout .cart.wrapper').on('click', 'button[name="remove_cart_item"]', function(e){
    e.preventDefault();
    var data = $(this).closest('li').find(':input').serialize()
             + '&remove_cart_item=' + $(this).val();
    queueUpdateTask('cart', data, true);
    queueUpdateTask('customer', true, true);
    queueUpdateTask('shipping', true, true);
    queueUpdateTask('payment', true, true);
    queueUpdateTask('summary', true, true);
  });

  $('#box-checkout .cart.wrapper').on('click', 'button[name="update_cart_item"]', function(e){
    e.preventDefault();
    var data = $(this).closest('li').find(':input').serialize()
             + '&update_cart_item=' + $(this).val();
    queueUpdateTask('cart', data, true);
    queueUpdateTask('customer', true, true);
    queueUpdateTask('shipping', true, true);
    queueUpdateTask('payment', true, true);
    queueUpdateTask('summary', true, true);
  });

// Customer Form: Toggles

  $('#box-checkout .customer.wrapper').on('change', 'input[name="different_shipping_address"]', function(e){
    if (this.checked == true) {
      $('#box-checkout-customer .shipping-address fieldset').prop('disabled', false).slideDown('fast');
    } else {
      $('#box-checkout-customer .shipping-address fieldset').prop('disabled', true).slideUp('fast');
    }
  });

  $('#box-checkout .customer.wrapper').on('change', 'input[name="create_account"]', function(){
    if (this.checked == true) {
      $('#box-checkout-customer .account fieldset').prop('disabled', false).slideDown('fast');
    } else {
      $('#box-checkout-customer .account fieldset').prop('disabled', true).slideUp('fast');
    }
  });

// Customer Form: Get Address

  $('#box-checkout .customer.wrapper').on('change', '.billing-address :input', function() {
    if ($(this).val() == '') return;
    if (console) console.log('Retrieving address (Trigger: '+ $(this).attr('name') +')');
    $.getJSON(
      '<?php echo document::ilink('ajax/get_address.json'); ?>?trigger='+$(this).attr('name'),
      $('.billing-address :input').serialize(),
      function(data) {
        if (data['alert']) alert(data['alert']);
        $.each(data, function(key, value) {
          $('.billing-address :input[name="'+key+'"]').val(value);
        });
      }
    );
  });

// Customer Form: Fields

  $('#box-checkout .customer.wrapper').on('input propertyChange', 'select[name="country_code"]', function(e) {

    if ($(this).find('option:selected').data('tax-id-format')) {
      $('input[name="tax_id"]').attr('pattern', $(this).find('option:selected').data('tax-id-format'));
    } else {
      $('input[name="tax_id"]').removeAttr('pattern');
    }

    if ($(this).find('option:selected').data('postcode-format')) {
      $('input[name="postcode"]').attr('pattern', $(this).find('option:selected').data('postcode-format'));
    } else {
      $('input[name="postcode"]').removeAttr('pattern');
    }

    if ($(this).find('option:selected').data('phone-code')) {
      $('input[name="phone"]').attr('placeholder', '+' + $(this).find('option:selected').data('phone-code'));
    } else {
      $('input[name="phone"]').removeAttr('placeholder');
    }

    <?php if (settings::get('customer_field_zone')) { ?>
    $('body').css('cursor', 'wait');
    $.ajax({
      url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
      type: 'get',
      cache: true,
      async: true,
      dataType: 'json',
      success: function(data) {
        $('select[name="zone_code"]').html('');
        if (data.length) {
          $('select[name="zone_code"]').prop('disabled', false);
          $.each(data, function(i, zone) {
            $('select[name="zone_code"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
          });
        } else {
          $('select[name="zone_code"]').prop('disabled', true);
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
    <?php } ?>
  });

  $('#box-checkout .customer.wrapper').on('input propertyChange', 'select[name="shipping_address[country_code]"]', function(e) {

    if ($(this).find('option:selected').data('postcode-format')) {
      $('input[name="shipping_address[postcode]"]').attr('pattern', $(this).find('option:selected').data('postcode-format'));
    } else {
      $('input[name="shipping_address[postcode]"]').removeAttr('pattern');
    }

    if ($(this).find('option:selected').data('phone-code')) {
      $('input[name="shipping_address[phone]"]').attr('placeholder', '+' + $(this).find('option:selected').data('phone-code'));
    } else {
      $('input[name="shipping_address[phone]"]').removeAttr('placeholder');
    }

    <?php if (settings::get('customer_field_zone')) { ?>
    $('body').css('cursor', 'wait');
    $.ajax({
      url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
      type: 'get',
      cache: true,
      async: false,
      dataType: 'json',
      success: function(data) {
        $('select[name="shipping_address[zone_code]"]').html('');
        if (data.length) {
          $('select[name="shipping_address[zone_code]"]').prop('disabled', false);
          $.each(data, function(i, zone) {
            $('select[name="shipping_address[zone_code]"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
          });
        } else {
          $('select[name="shipping_address[zone_code]"]').prop('disabled', true);
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
    <?php } ?>
  });

// Customer Form: Checksum

  window.customer_form_changed = null;
  window.customer_form_checksum = null;
  $('#box-checkout .customer.wrapper').on('input change', ':input', function(e) {
    if ($('#box-checkout-customer :input').serialize() != window.customer_form_checksum) {
      if (window.customer_form_checksum == null) return;
      window.customer_form_changed = true;
      $('#box-checkout-customer button[name="save_customer_details"]').prop('disabled', false);
    } else {
      window.customer_form_changed = false;
      $('#box-checkout-customer button[name="save_customer_details"]').prop('disabled', true);
    }
  });

// Customer Form: Auto-Save

  var timerSubmitCustomer;
  $('#box-checkout .customer.wrapper').on('focusout', '#box-checkout-customer', function() {
    timerSubmitCustomer = setTimeout(
      function() {
        if (!$(this).is(':focus')) {
          if (window.customer_form_changed) {
            if (console) console.log('Autosaving customer details');
            var data = $('#box-checkout-customer :input').serialize();
            queueUpdateTask('customer', data, true);
            queueUpdateTask('cart', null, true);
            queueUpdateTask('shipping', true, true);
            queueUpdateTask('payment', true, true);
            queueUpdateTask('summary', null, true);
          }
        }
      }, 50
    );
  });

  $('body').on('focusin', '#box-checkout-customer', function() {
    clearTimeout(timerSubmitCustomer);
  });

// Customer Form: Process Data

  $('#box-checkout .customer.wrapper').on('click', 'button[name="save_customer_details"]', function(e){
    e.preventDefault();
    var data = $('#box-checkout-customer :input').serialize()
             + '&save_customer_details=true';
    queueUpdateTask('customer', data, true);
    queueUpdateTask('cart', null, true);
    queueUpdateTask('shipping', true, true);
    queueUpdateTask('payment', true, true);
    queueUpdateTask('summary', null, true);
    window.customer_form_checksum = $('#box-checkout-customer :input').serialize();
    $('#box-checkout-customer :input:first-child').trigger('change');
  });

// Shipping Form: Process Data

  $('#box-checkout .shipping.wrapper').on('click', '.option:not(.active):not(.disabled)', function(){
    $('#box-checkout-shipping .option').removeClass('active');
    $(this).find('input[name="shipping[option_id]"]').prop('checked', true).trigger('change');
    $(this).addClass('active');

    $('#box-checkout-shipping .option.active .fields :input').prop('disabled', false);
    $('#box-checkout-shipping .option:not(.active) .fields :input').prop('disabled', true);

    var data = $('#box-checkout-shipping .option.active :input').serialize();
    queueUpdateTask('shipping', data, false);
    queueUpdateTask('payment', true, true);
    queueUpdateTask('summary', null, true);
  });

// Payment Form: Process Data

  $('#box-checkout .payment.wrapper').on('click', '.option:not(.active):not(.disabled)', function(){
    $('#box-checkout-payment .option').removeClass('active');
    $(this).find('input[name="payment[option_id]"]').prop('checked', true).trigger('change');
    $(this).addClass('active');

    $('#box-checkout-payment .option.active .fields :input').prop('disabled', false);
    $('#box-checkout-payment .option:not(.active) .fields :input').prop('disabled', true);

    var data = $('#box-checkout-payment .option.active :input').serialize();
    queueUpdateTask('payment', data, false);
    queueUpdateTask('summary', null, true);
  });

// Summary Form: Process Data

  $('#box-checkout-summary-wrapper').on('click', 'button[name="confirm_order"]', function(e) {
    if (window.customer_form_changed) {
      e.preventDefault();
      alert("<?php echo language::translate('warning_your_customer_information_unsaved', 'Your customer information contains unsaved changes.')?>");
    }
  });

  $('body').on('submit', 'form[name="checkout_form"]', function(e) {
    $('#box-checkout-summary button[name="confirm_order"]').css('display', 'none').before('<div class="btn btn-block btn-default btn-lg disabled"><?php echo functions::draw_fonticon('fa-spinner'); ?> <?php echo functions::escape_js(language::translate('text_please_wait', 'Please wait')); ?>&hellip;</div>');
  });
</script>