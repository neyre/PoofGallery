
// On Form Show, Focus Name
$('.modal#newAlbum').on('shown', function () {
  $('form#newalbumform input#name').focus();
})

// On Enter in Name, Submit Form
$('form#newalbumform input#name').keypress(function(e) {
  if(e.which == 13) {
    e.preventDefault();
    $(this).blur();
    $('#submit.btn').focus().click();
  }
});

// Submit Form
$('.modal#newAlbum #submit.btn').click(function(event){
  event.preventDefault();
  $(this).addClass('loading');
  $.ajax({
    type: "POST",
    url: "{{@rootpath}}/album/new",
    data: {name: $('form#newalbumform #name').val(), parent: $('form#newalbumform #parent').val()},
    success: function()
    {
      window.location.reload();
    },
    statusCode: {
      404: function() {
        $('#badinfo').show();
        $('#submit.btn').removeClass('loading');
      }
    }
  });
});