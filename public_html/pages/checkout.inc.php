<?php
  define('REQUIRE_POST_TOKEN', false);
  require_once('includes/app_header.inc.php');
  
  if (settings::get('catalog_only_mode')) return;
  
  document::$layout = 'checkout';
  
  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';

  breadcrumbs::add(language::translate('title_checkout', 'Checkout'), document::link());
  
  document::$snippets['title'][] = language::translate('title_checkout', 'Checkout');
  //document::$snippets['keywords'] = '';
  //document::$snippets['description'] = '';
?>

<div id="checkout-cart-wrapper">
  <?php include_once vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_AJAX . 'checkout_cart.html.php'); ?>
</div>

<div id="checkout-customer-wrapper">
  <?php include_once vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_AJAX . 'checkout_customer.html.php'); ?>
</div>

<div id="checkout-shipping-wrapper">
  <?php include_once vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_AJAX . 'checkout_shipping.html.php'); ?>
</div>

<div id="checkout-payment-wrapper">
  <?php include_once vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_AJAX . 'checkout_payment.html.php'); ?>
</div>

<div id="checkout-summary-wrapper">
  <?php include_once vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_AJAX . 'checkout_summary.html.php'); ?>
</div>

<script>
  function refreshCart() {
    if (console) console.log("Refreshing cart");
    $('#checkout-cart-wrapper').fadeTo('slow', 0.25);
    $.ajax({
      url: '<?php echo document::link(WS_DIR_AJAX .'checkout_cart.html.php'); ?>',
      data: false,
      type: 'get',
      cache: false,
      context: $('#checkout-cart-wrapper'),
      async: true,
      dataType: 'html',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType("text/html;charset=<?php echo language::$selected['charset']; ?>");
      },
      error: function(jqXHR, textStatus, errorThrown) {
        $('#checkout-cart-wrapper').html(textStatus + ': ' + errorThrown).fadeTo('slow', 1);
      },
      success: function(data) {
        $('#checkout-cart-wrapper').html(data).fadeTo('slow', 1);
      },
    });
  }
  
  function refreshCustomer() {
    if (console) console.log("Refreshing customer");
    $.ajax({
      url: '<?php echo document::link(WS_DIR_AJAX .'checkout_customer.html.php'); ?>',
      data: false,
      type: 'get',
      cache: false,
      context: $('#checkout-customer-wrapper'),
      async: true,
      dataType: 'html',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType("text/html;charset=<?php echo language::$selected['charset']; ?>");
      },
      error: function(jqXHR, textStatus, errorThrown) {
        $('#checkout-customer-wrapper').html(textStatus + ': ' + errorThrown);
      },
      success: function(data) {
        $('#checkout-customer-wrapper').html(data);
      },
    });
  }

  function refreshShipping() {
    if (console) console.log("Refreshing shipping");
    $.ajax({
      url: '<?php echo document::link(WS_DIR_AJAX .'checkout_shipping.html.php'); ?>',
      data: false,
      type: 'get',
      cache: false,
      context: $('#checkout-shipping-wrapper'),
      async: true,
      dataType: 'html',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType("text/html;charset=<?php echo language::$selected['charset']; ?>");
      },
      error: function(jqXHR, textStatus, errorThrown) {
        $('#checkout-shipping-wrapper').html(textStatus + ': ' + errorThrown);
      },
      success: function(data) {
        $('#checkout-shipping-wrapper').html(data);
      },
    });
  }

  function refreshPayment() {
    if (console) console.log("Refreshing payment");
    $.ajax({
      url: '<?php echo document::link(WS_DIR_AJAX .'checkout_payment.html.php'); ?>',
      data: false,
      type: 'get',
      cache: false,
      context: $('#checkout-payment-wrapper'),
      async: true,
      dataType: 'html',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType("text/html;charset=<?php echo language::$selected['charset']; ?>");
      },
      error: function(jqXHR, textStatus, errorThrown) {
        $('#checkout-payment-wrapper').html(textStatus + ': ' + errorThrown);
      },
      success: function(data) {
        $('#checkout-payment-wrapper').html(data);
      },
    });
  }
  
  function refreshSummary() {
    if (console) console.log("Refreshing summary");
    var comments = $('textarea[name=comments]').val();
    $.ajax({
      url: '<?php echo document::link(WS_DIR_AJAX .'checkout_summary.html.php'); ?>',
      data: false,
      type: 'get',
      cache: false,
      context: $('#checkout-summary-wrapper'),
      async: true,
      dataType: 'html',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType("text/html;charset=<?php echo language::$selected['charset']; ?>");
      },
      error: function(jqXHR, textStatus, errorThrown) {
        $('#checkout-summary-wrapper').html(textStatus + ': ' + errorThrown);
      },
      success: function(data) {
        $('#checkout-summary-wrapper').html(data);
        $('textarea[name=comments]').val(comments);
      },
    });
  }
    
  $("body").on("click", "form button[type=submit]", function(e) {
    $(this).closest("form").append('<input type="hidden" name="'+ $(this).attr("name") +'" value="'+ $(this).text() +'" />');
  });
  
  $("body").on('submit', 'form[name=cart_form]', function(e) {
    e.preventDefault();
    $('body').css('cursor', 'wait');
    $('#checkout-cart-wrapper').fadeTo('slow', 0.25);
    $.ajax({
      url: '<?php echo document::link(WS_DIR_AJAX .'checkout_cart.html.php'); ?>',
      data: $(this).serialize(),
      type: 'post',
      cache: false,
      async: true,
      dataType: 'html',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType("text/html;charset=<?php echo language::$selected['charset']; ?>");
      },
      error: function(jqXHR, textStatus, errorThrown) {
        $('#checkout-cart-wrapper').html(textStatus + ': ' + errorThrown).fadeTo('slow', 1);
      },
      success: function(data) {
        $('#checkout-cart-wrapper').html(data).fadeTo('slow', 1);
        if (jQuery.isFunction(window.updateCart)) updateCart();
        refreshCustomer();
        refreshShipping();
        refreshPayment();
        refreshSummary();
      },
      complete: function() {
        $('body').css('cursor', '');
      }
    });
  });
  
  var customer_form_checksum;
  var customer_saved_checksum = $('form[name=customer_form]').serialize();
  var stateCustomerChanged = false;
  $("body").on('change keyup', 'form[name=customer_form] *', function(e) {
    customer_form_checksum = $('form[name=customer_form]').serialize();
    if (customer_form_checksum != customer_saved_checksum) {
      stateCustomerChanged = true;
      $("#box-checkout-account button[name='set_addresses']").removeAttr('disabled');
    } else {
      stateCustomerChanged = false;
      $("#box-checkout-account button[name='set_addresses']").attr('disabled', 'disabled');
    }
  });
  
  var timerSubmitCustomer;
  $("body").on('focusout', 'form[name=customer_form]', function() {
    timerSubmitCustomer = setTimeout(
      function() {
        if (!$('form[name=customer_form]').is(':focus')) {
          if (stateCustomerChanged) {
            $('form[name=customer_form]').trigger('submit');
          }
        }
      }, 50
    );
  });
  $("body").on('focusin', 'form[name=customer_form]', function() {
    clearTimeout(timerSubmitCustomer);
  });
  
  $("body").on('submit', 'form[name="order_form"]', function(e) {
    if (stateCustomerChanged) {
      e.preventDefault();
      alert("<?php echo language::translate('warning_your_customer_information_unsaved', 'Your customer information contains unsaved changes.')?>");
    }
  });
  
  $("body").on('submit', 'form[name=customer_form]', function(e) {
    e.preventDefault();
    clearTimeout(timerSubmitCustomer);
    $('*').css('cursor', 'wait');
    $.ajax({
      url: '<?php echo document::link(WS_DIR_AJAX .'checkout_customer.html.php'); ?>',
      data: $(this).serialize()+'&set_addresses=true',
      type: 'post',
      cache: false,
      context: $('#checkout-customer-wrapper'),
      async: true,
      dataType: 'html',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType("text/html;charset=<?php echo language::$selected['charset']; ?>");
      },
      error: function(jqXHR, textStatus, errorThrown) {
        $('#checkout-customer-wrapper').html(textStatus + ': ' + errorThrown);
      },
      success: function(data) {
        $("#box-checkout-account button[name='set_addresses']").attr('disabled', 'disabled');
        $('#checkout-customer-wrapper').html(data);
        stateCustomerChanged = false;
        customer_saved_checksum = $('form[name=customer_form]').serialize();
        if (jQuery.isFunction(window.updateCart)) updateCart();
        refreshCart();
        refreshShipping();
        refreshPayment();
        refreshSummary();
      },
      complete: function() {
        $('*').css('cursor', '');
        $('html, body').animate({
          scrollTop: $('#checkout-customer-wrapper').offset().top
        }, 800);
      }
    });
  });
  
  $("body").on('submit', 'form[name=shipping_form]', function(e) {
    e.preventDefault();
    $('*').css('cursor', 'wait');
    $('#checkout-shipping-wrapper').fadeTo('slow', 0.25);
    $.ajax({
      url: '<?php echo document::link(WS_DIR_AJAX .'checkout_shipping.html.php'); ?>',
      data: $(this).serialize(),
      type: 'post',
      cache: false,
      async: true,
      dataType: 'html',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType("text/html;charset=<?php echo language::$selected['charset']; ?>");
      },
      error: function(jqXHR, textStatus, errorThrown) {
        $('#checkout-shipping-wrapper').html(textStatus + ': ' + errorThrown).fadeTo('slow', 1);
      },
      success: function(data) {
        $('#checkout-shipping-wrapper').html(data).fadeTo('slow', 1);
        refreshPayment();
        refreshSummary();
        $('html, body').animate({
          scrollTop: $('#checkout-shipping-wrapper').offset().top
        }, 800);
      },
      complete: function() {
        $('*').css('cursor', '');
      }
    });
  });
  
  $("body").on('submit', 'form[name=payment_form]', function(e) {
    e.preventDefault();
    $('*').css('cursor', 'wait');
    $('#checkout-payment-wrapper').fadeTo('slow', 0.25);
    $.ajax({
      url: '<?php echo document::link(WS_DIR_AJAX .'checkout_payment.html.php'); ?>',
      data: $(this).serialize(),
      type: 'post',
      cache: false,
      context: $('#checkout-payment-wrapper'),
      async: true,
      dataType: 'html',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType("text/html;charset=<?php echo language::$selected['charset']; ?>");
      },
      error: function(jqXHR, textStatus, errorThrown) {
        $('#checkout-payment-wrapper').html(textStatus + ': ' + errorThrown).fadeTo('slow', 1);
      },
      success: function(data) {
        $('#checkout-payment-wrapper').html(data).fadeTo('slow', 1);
        refreshSummary();
        $('html, body').animate({
          scrollTop: $('#checkout-payment-wrapper').offset().top
        }, 800);
      },
      complete: function() {
        $('*').css('cursor', '');
      }
    });
  });
  
  $("body").on('blur', 'form[name=comments_form]', function(e) {
    e.preventDefault();
    $('*').css('cursor', 'wait');
    $('#checkout-comments-wrapper').fadeTo('slow', 0.25);
    $.ajax({
      url: '<?php echo document::link(WS_DIR_AJAX .'checkout_comments.html.php'); ?>',
      data: $(this).serialize(),
      type: 'post',
      cache: false,
      context: $('#checkout-comments-wrapper'),
      async: true,
      dataType: 'html',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType("text/html;charset=<?php echo language::$selected['charset']; ?>");
      },
      error: function(jqXHR, textStatus, errorThrown) {
        $('#checkout-comments-wrapper').html(textStatus + ': ' + errorThrown).fadeTo('slow', 1);
      },
      success: function(data) {
        $('#checkout-comments-wrapper').html(data).fadeTo('slow', 1);
      },
      complete: function() {
        $('*').css('cursor', '');
      }
    });
  });
</script>
<?php
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>