<main id="box-checkout">
  <?php echo functions::form_begin('checkout_form', 'post', '', false, 'autocomplete="off"'); ?>

    {{notices}}

    <div class="row">

      <div class="left-wrapper col-md-6">
        <div class="left">

          <div class="navigate-back text-center">
            <a class="btn btn-default" href="<?php echo document::ilink(''); ?>" >
              <?php echo functions::draw_fonticon('fa-arrow-left'); ?> <?php echo language::translate('title_back_to_store', 'Back To Store'); ?>
            </a>
          </div>

          <?php if (!empty($express_checkout)) { ?>
          <fieldset id="express-checkout">
            <legend><?php echo language::translate('title_express_checkkout', 'Express Checkout'); ?></legend>

            <div class="options">
              <?php foreach ($express_checkout as $express_option) { ?>
              <a class="option btn btn-default btn-lg" href=""><?php echo $express_option['title']; ?></a>
              <?php } ?>
            </div>
          </fieldset>

          <div class="strikethrough-divider">
            <span><?php echo language::translate('text_or', 'Or'); ?></span>
          </div>
          <?php } ?>

          <div class="customer wrapper"></div>

       </div>
      </div>

      <div class="right-wrapper col-md-6">
        <div class="right">

          <div class="shipping wrapper"></div>

          <div class="payment wrapper"></div>

          <div class="summary wrapper"></div>

        </div>
      </div>
    </div>

  <?php echo functions::form_end(); ?>
</main>

<script>
// Queue Handler

  $('#box-checkout').data('updateQueue', [
    {component: 'customer', data: null, refresh: true},
    {component: 'shipping', data: null, refresh: true},
    {component: 'payment',  data: null, refresh: true},
    {component: 'summary',  data: null, refresh: true}
  ]);

  $('#box-checkout').on('update', function(e, task) {

    let updateQueue = $(this).data('updateQueue');

    if (task && task.component) {
      updateQueue = jQuery.grep(updateQueue, function(tasks) {
        return (tasks.component == task.component) ? false : true;
      });

      updateQueue.push(task);

      $(this).data('updateQueue', updateQueue);
    }

    if ($(this).prop('updateLock')) return;
    if ($(this).data('updateQueue').length == 0) return;

    $(this).prop('updateLock', true);

    let task = updateQueue.shift();
    $(this).data('updateQueue', updateQueue);

    console.log('Updating ' + task.component);

    if (!$('body > .loader-wrapper').length) {

      let loader = [
        '<div class="loader-wrapper">'
        '  <div class="loader" style="width: 256px; height: 256px;"></div>',
        '</div>'
      ].join('\n');

      $('body').append(loader);
    }

    if (task.refresh) {
      $('#box-checkout .'+ task.component +'.wrapper').fadeTo('fast', 0.15);
    }

    let url = '';
    switch (task.component) {

      case 'customer':
        url = '<?php echo document::ilink('checkout/customer'); ?>';
        break;

      case 'shipping':
        url = '<?php echo document::ilink('checkout/shipping'); ?>';
        break;

      case 'payment':
        url = '<?php echo document::ilink('checkout/payment'); ?>';
        break;

      case 'summary':
        url = '<?php echo document::ilink('checkout/summary'); ?>';
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
          task.data = $('#box-checkout-shipping :input').serialize();
          break;
        case 'payment':
          task.data = $('#box-checkout-payment :input').serialize();
          break;
        case 'summary':
          task.data = $('#box-checkout-summary :input').serialize();
          break;
      }
    }

    if (task.component == 'summary') {
      let comments = $(':input[name="comments"]').val();
      let terms_agreed = $(':input[name="terms_agreed"]').prop('checked');
    }

    $.ajax({
      type: task.data ? 'post' : 'get',
      url: url,
      data: task.data,
      dataType: 'html',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType('text/html;charset=<?php echo mb_http_output(); ?>');
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
        if ($('#box-checkout').data('updateQueue').length == 0) {
          $('body > .loader-wrapper').fadeOut('fast', function(){
            $(this).remove();
          });
        }
        $('#box-checkout').prop('updateLock', false);
        $('#box-checkout').trigger('update');
      }
    });
  }).trigger('update');
</script>