// Password Strength
  $('form').on('input', 'input[type="password"][data-toggle="password-strength"]', function(){

    $(this).siblings('meter').remove();

    if ($(this).val() == '') return;

    let numbers = ($(this).val().match(/[0-9]/g) || []).length,
     lowercases = ($(this).val().match(/[a-z]/g) || []).length,
     uppercases = ($(this).val().match(/[A-Z]/g) || []).length,
     symbols =   ($(this).val().match(/[^\w]/g) || []).length,

     score = (numbers * 9) + (lowercases * 11.25) + (uppercases * 11.25) + (symbols * 15)
           + (numbers ? 10 : 0) + (lowercases ? 10 : 0) + (uppercases ? 10 : 0) + (symbols ? 10 : 0);

    let meter = $('<meter min="0" low="80" high="120" optimum="150" max="150" value="'+ score +'"></meter>').css({
      position: 'absolute',
      bottom: '-1em',
      width: '100%',
      height: '1em'
    });

    $(this).after(meter);
  });
