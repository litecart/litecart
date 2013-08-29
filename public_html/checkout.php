<?php
  define('REQUIRE_POST_TOKEN', false);
  require_once('includes/app_header.inc.php');
  
  header('X-Robots-Tag: noindex');
  $system->document->snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';

  $system->breadcrumbs->add($system->language->translate('title_checkout', 'Checkout'), $system->document->link());
  
  $system->document->snippets['title'][] = $system->language->translate('title_checkout', 'Checkout');
  //$system->document->snippets['keywords'] = '';
  //$system->document->snippets['description'] = '';
?>

<div id="checkout-cart-wrapper">
  <?php include_once(FS_DIR_HTTP_ROOT . WS_DIR_AJAX . 'checkout_cart.html.php'); ?>
</div>

<div id="checkout-customer-wrapper">
  <?php include_once(FS_DIR_HTTP_ROOT . WS_DIR_AJAX . 'checkout_customer.html.php'); ?>
</div>

<div id="checkout-shipping-wrapper">
  <?php include_once(FS_DIR_HTTP_ROOT . WS_DIR_AJAX . 'checkout_shipping.html.php'); ?>
</div>

<div id="checkout-payment-wrapper">
  <?php include_once(FS_DIR_HTTP_ROOT . WS_DIR_AJAX . 'checkout_payment.html.php'); ?>
</div>

<div id="checkout-summary-wrapper">
  <?php include_once(FS_DIR_HTTP_ROOT . WS_DIR_AJAX . 'checkout_summary.html.php'); ?>
</div>

<?php if ($system->settings->get('checkout_ajax_enabled')) { ?>
<script type="text/javascript">

  function refreshCart() {
    $('#checkout-cart-wrapper').fadeTo('slow', 0.25);
    $.ajax({
      url: '<?php echo $system->document->link(WS_DIR_AJAX .'checkout_cart.html.php'); ?>',
      data: false,
      type: 'get',
      cache: false,
      context: $('#checkout-cart-wrapper'),
      async: true,
      dataType: 'html',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType("text/html;charset=<?php echo $system->language->selected['charset']; ?>");
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
    $.ajax({
      url: '<?php echo $system->document->link(WS_DIR_AJAX .'checkout_customer.html.php'); ?>',
      data: false,
      type: 'get',
      cache: false,
      context: $('#checkout-customer-wrapper'),
      async: true,
      dataType: 'html',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType("text/html;charset=<?php echo $system->language->selected['charset']; ?>");
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
    $.ajax({
      url: '<?php echo $system->document->link(WS_DIR_AJAX .'checkout_shipping.html.php'); ?>',
      data: false,
      type: 'get',
      cache: false,
      context: $('#checkout-shipping-wrapper'),
      async: true,
      dataType: 'html',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType("text/html;charset=<?php echo $system->language->selected['charset']; ?>");
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
    $.ajax({
      url: '<?php echo $system->document->link(WS_DIR_AJAX .'checkout_payment.html.php'); ?>',
      data: false,
      type: 'get',
      cache: false,
      context: $('#checkout-payment-wrapper'),
      async: true,
      dataType: 'html',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType("text/html;charset=<?php echo $system->language->selected['charset']; ?>");
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
    var comments = $('textarea[name=comments]').val();
    $.ajax({
      url: '<?php echo $system->document->link(WS_DIR_AJAX .'checkout_summary.html.php'); ?>',
      data: false,
      type: 'get',
      cache: false,
      context: $('#checkout-summary-wrapper'),
      async: true,
      dataType: 'html',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType("text/html;charset=<?php echo $system->language->selected['charset']; ?>");
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
  
  $(document).ready(function() {
    
    $("body").on("click", "form button[type=submit]", function(e) {
      $(this).closest("form").append('<input type="hidden" name="'+ $(this).attr("name") +'" value="'+ $(this).text() +'" />');
    });
    
    $("body").on('submit', 'form[name=cart_form]', function(e) {
      e.preventDefault();
      $('body').css('cursor', 'wait');
      $('#checkout-cart-wrapper').fadeTo('slow', 0.25);
      $.ajax({
        url: '<?php echo $system->document->link(WS_DIR_AJAX .'checkout_cart.html.php'); ?>',
        data: $(this).serialize(),
        type: 'post',
        cache: false,
        async: true,
        dataType: 'html',
        beforeSend: function(jqXHR) {
          jqXHR.overrideMimeType("text/html;charset=<?php echo $system->language->selected['charset']; ?>");
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $('#checkout-cart-wrapper').html(textStatus + ': ' + errorThrown).fadeTo('slow', 1);
        },
        success: function(data) {
          $('#checkout-cart-wrapper').html(data).fadeTo('slow', 1);
          if (jQuery.isFunction(updateCart)) updateCart();
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
    
    var customer_checksum;
    var stateCustomerChanged = false;
    $("body").on('change', 'form[name=customer_form] *', function(e) {
      if (customer_checksum != $(this).closest('form').serialize()) {
        stateCustomerChanged = true;
      }
      customer_checksum = $(this).closest('form').serialize();
    });
    
    var timerSubmitCustomer;
    $("body").on('focusout', 'form[name=customer_form]', function() {
      timerSubmitCustomer = setTimeout(
        function() {
          if (!$('form[name=customer_form]').is(':focus')) {
            if (stateCustomerChanged) {
              $('form[name=customer_form]').append('<input type="hidden" name="set_addresses" value="true" />');
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
        alert("<?php echo $system->language->translate('warning_your_customer_information_unsaved', 'Your customer information contains unsaved changes.')?>");
      }
    });
    
    $("body").on('submit', 'form[name=customer_form]', function(e) {
      e.preventDefault();
      clearTimeout(timerSubmitCustomer);
      $('*').css('cursor', 'wait');
      $.ajax({
        url: '<?php echo $system->document->link(WS_DIR_AJAX .'checkout_customer.html.php'); ?>',
        data: $(this).serialize(),
        type: 'post',
        cache: false,
        context: $('#checkout-customer-wrapper'),
        async: true,
        dataType: 'html',
        beforeSend: function(jqXHR) {
          jqXHR.overrideMimeType("text/html;charset=<?php echo $system->language->selected['charset']; ?>");
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $('#checkout-customer-wrapper').html(textStatus + ': ' + errorThrown);
        },
        success: function(data) {
          stateCustomerChanged = false;
          $('#checkout-customer-wrapper').html(data);
          if (jQuery.isFunction(updateCart)) updateCart();
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
        url: '<?php echo $system->document->link(WS_DIR_AJAX .'checkout_shipping.html.php'); ?>',
        data: $(this).serialize(),
        type: 'post',
        cache: false,
        async: true,
        dataType: 'html',
        beforeSend: function(jqXHR) {
          jqXHR.overrideMimeType("text/html;charset=<?php echo $system->language->selected['charset']; ?>");
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
        url: '<?php echo $system->document->link(WS_DIR_AJAX .'checkout_payment.html.php'); ?>',
        data: $(this).serialize(),
        type: 'post',
        cache: false,
        context: $('#checkout-payment-wrapper'),
        async: true,
        dataType: 'html',
        beforeSend: function(jqXHR) {
          jqXHR.overrideMimeType("text/html;charset=<?php echo $system->language->selected['charset']; ?>");
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
        url: '<?php echo $system->document->link(WS_DIR_AJAX .'checkout_comments.html.php'); ?>',
        data: $(this).serialize(),
        type: 'post',
        cache: false,
        context: $('#checkout-comments-wrapper'),
        async: true,
        dataType: 'html',
        beforeSend: function(jqXHR) {
          jqXHR.overrideMimeType("text/html;charset=<?php echo $system->language->selected['charset']; ?>");
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
  });
  <?php } ?>
</script>
<?php
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>