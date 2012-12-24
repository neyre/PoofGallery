
// On Enter in Password, Next Field
$('form#edituserform input#password').keypress(function(e) {
  if(e.which == 13) {
    e.preventDefault();
    $(this).blur();
    $('form#edituserform input#password2').focus();
  }
});

// On Enter in Password Confirm, Next Field
$('form#edituserform input#password2').keypress(function(e) {
  if(e.which == 13) {
    e.preventDefault();
    $(this).blur();
    $('form#edituserform select#access').focus();
  }
});

// On Enter in Password Confirm, Next Field
$('form#edituserform select#access').keypress(function(e) {
  if(e.which == 13) {
    e.preventDefault();
    $(this).blur();
    $('.modal#editUser #submit.btn').focus().click();
  }
});

// Submit Form
$('.modal#editUser #submit.btn').click(function(event){
  event.preventDefault();
  if($('form#edituserform #password').val() == $('form#edituserform #password2').val()){
    $(this).addClass('loading');
    $.ajax({
      type: "POST",
      url: "{{@rootpath}}/users/update",
      data: {name: $('form#edituserform #name').val(), password: $('form#edituserform #password').val(), access: $('form#edituserform #access').val()},
      success: function()
      {  
        window.location.reload();
      },
      statusCode: {
        404: function() {
          $('form#edituserform #badinfo').show();
          $('.modal#editUser #submit.btn').removeClass('loading');
        }
      }
    });
  }else{
    $('form#edituserform #badpass').show();
  }
});

// Load Edit Field
$('.edit.user').click(function(event){
  $('form#edituserform #name').val($(this).attr('username'));
  $('form#edituserform #access').val($(this).attr('access'));
  $('.modal#editUser').modal();
});

$('.modal#editUser #delete.btn').click(function(event){
  if(confirm("Are you sure you want to delete this user?")){
    $(this).addClass('loading');
    $.ajax({
      type: "POST",
      url: "{{@rootpath}}/users/delete",
      data: {name: $('form#edituserform #name').val()},
      success: function()
      {  
        window.location.reload();
      },
      statusCode: {
        404: function() {
          $('form#edituserform #badinfo').show();
          $('.modal#editUser #delete.btn').removeClass('loading');
        }
      }
    });
  }
});