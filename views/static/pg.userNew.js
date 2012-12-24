
// On Form Show, Focus Username
$('.modal#newUser').on('shown', function () {
  $('form#newuserform input#name').focus();
})

// On Enter in Username, Next Field
$('form#newuserform input#name').keypress(function(e) {
  if(e.which == 13) {
    e.preventDefault();
    $(this).blur();
    $('form#newuserform input#password').focus();
  }
});

// On Enter in Password, Next Field
$('form#newuserform input#password').keypress(function(e) {
  if(e.which == 13) {
    e.preventDefault();
    $(this).blur();
    $('form#newuserform input#password2').focus();
  }
});

// On Enter in Password Confirm, Next Field
$('form#newuserform input#password2').keypress(function(e) {
  if(e.which == 13) {
    e.preventDefault();
    $(this).blur();
    $('form#newuserform select#access').focus();
  }
});

// On Enter in Password Confirm, Next Field
$('form#newuserform select#access').keypress(function(e) {
  if(e.which == 13) {
    e.preventDefault();
    $(this).blur();
    $('.modal#newUser #submit.btn').focus().click();
  }
});

// Submit Form
$('.modal#newUser #submit.btn').click(function(event){
  event.preventDefault();
  if($('form#newuserform #password').val() == $('form#newuserform #password2').val()){
    $(this).addClass('loading');
    $.ajax({
      type: "POST",
      url: "{{@rootpath}}/users/new",
      data: {name: $('form#newuserform #name').val(), password: $('form#newuserform #password').val(), access: $('form#newuserform #access').val()},
      success: function()
      {
        window.location.reload();
      },
      statusCode: {
        404: function() {
          $('form#newuserform #badinfo').show();
          $('.modal#editUser #submit.btn').removeClass('loading');
        }
      }
    });
  }else{
    $('form#newuserform #badpass').show();
  }
});