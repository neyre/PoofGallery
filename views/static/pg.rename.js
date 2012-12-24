
// On Form Show, Focus Name
$('.modal#rename').on('shown', function () {
  $('form#editalbumform input#name').focus();
});

// Submit Form
$('.modal#rename #submitsave.btn').click(function(event){
  event.preventDefault();
  $(this).addClass('loading');
  $.ajax({
    type: "POST",
    url: "{{@rootpath}}/album/rename",
    data: {name: $('form#editalbumform #name').val(), id: $('form#editalbumform #album').val()},
    success: function()
    {
      $('.btn-save').focus().click();
    },
    statusCode: {
      404: function() {
        $('.modal#rename #badinfo').show();
        $('.modal#rename #submit.btn').removeClass('loading');
      }
    }
  });
})
$('.modal#rename #submit.btn').click(function(event){
  event.preventDefault();
  $(this).addClass('loading');
  $.ajax({
    type: "POST",
    url: "{{@rootpath}}/album/rename",
    data: {name: $('form#editalbumform #name').val(), id: $('form#editalbumform #album').val()},
    success: function()
    {
      window.location.reload();
    },
    statusCode: {
      404: function() {
        $('.modal#rename #badinfo').show();
        $('.modal#rename #submit.btn').removeClass('loading');
      }
    }
  });
});