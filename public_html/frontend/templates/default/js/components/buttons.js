// Toggle Buttons (data-toggle="buttons")

  $('body').on('click', '[data-toggle="buttons"] :checkbox', function(){
    if ($(this).is(':checked')) {
      $(this).closest('.btn').addClass('active');
    } else {
      $(this).closest('.btn').removeClass('active');
    }
  });

  $('body').on('click', '[data-toggle="buttons"] :radio', function(){
    $(this).closest('.btn').addClass('active').siblings().removeClass('active');
  });
