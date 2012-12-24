
// On Login Form Show, Focus Username
$('.modal#login').on('shown', function () {
  $('form#loginform input#username').focus();
})

// On Enter in Username, Focus Password
$('form#loginform input#username').keypress(function(e) {
  if(e.which == 13) {
    $(this).blur();
    $('form#loginform input#password').focus();
  }
});

// On Enter in Password, Submit Form
$('form#loginform input#password').keypress(function(e) {
  if(e.which == 13) {
    $(this).blur();
    $('#login.btn').focus().click();
  }
});

// Submit Form
$('#login.btn').click(function(event){
  event.preventDefault();
  $(this).addClass('loading');
  $.ajax({
    type: "POST",
    url: "{{@rootpath}}/login",
    data: {username: $('#username').val(), password: $('#password').val()},
    success: function(html)
    {  
      window.location.reload();
    },
    statusCode: {
      404: function() {
        $('#badinfo').show();
        $('#login.btn').removeClass('loading');
      }
    }
  });
});