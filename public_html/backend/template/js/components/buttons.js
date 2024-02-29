// Toggle Buttons (data-toggle="buttons")

$('body').on('click', '[data-toggle="buttons"] .btn', function(){
  if ($(this).hasClass('active') && $(this).find(':input').prop('checked')) return;
  $(this).addClass('active').find(':input').prop('checked', true).trigger('change');
  $(this).removeClass('active').siblings().find(':input').prop('checked', true).trigger('change');
});

$('body').on('click', '[data-toggle="buttons"] :input', function(){
  if ($(this).prop('checked') && $(this).closest('.btn').hasClass('active')) return;
  $(this).closest('.btn').trigger('click');
});

$('body').on('change', '[data-toggle="buttons"] :input', function(){
  $('input[name="'+ $(this).attr('name') +'"]').each(function(){
    $(this).closest('btn').toggleClass('active', $(this).prop('checked'));
  });
});

$('[data-toggle="buttons"] :input:checked').trigger('change'); // Init
