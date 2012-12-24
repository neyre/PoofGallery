
// On Form Show, Focus Password
$('.modal#changePass').on('shown', function () {
  $('form#changepassform input#password').focus();
})

// On Enter in Password, Next Field
$('form#changepassform input#password').keypress(function(e) {
  if(e.which == 13) {
    e.preventDefault();
    $(this).blur();
    $('form#changepassform input#password2').focus();
  }
});

// On Enter in Password Confirm, Submit
$('form#changepassform input#password2').keypress(function(e) {
  if(e.which == 13) {
    e.preventDefault();
    $(this).blur();
    $('.modal#changePass #submit.btn').focus().click();
  }
});

// Submit Form
$('.modal#changePass #submit.btn').click(function(event){
  event.preventDefault();
  if($('form#changepassform #password').val() == $('form#changepassform #password2').val()){
    $(this).addClass('loading');
    $.ajax({
      type: "POST",
      url: "{{@rootpath}}/users/password",
      data: {password: $('form#changepassform #password').val()},
      success: function()
      {  
        $('.modal#changePass').modal('hide');
        $('.modal#changePass #submit.btn').removeClass('loading');
      },
      statusCode: {
        404: function() {
          $('form#changepassform #badinfo').show();
          $('.modal#changePass #submit.btn').removeClass('loading');
        }
      }
    });
  }else{
    $('form#changepassform #badpass').show();
  }
});