var w,h,f,i_w,i_h,w_h,w_w;

// Load Image if in hash
$(function(){
  if(window.location.hash.length == 41)
    showPicture(window.location.hash.substr(1,40));
});

// If thumbnail clicked, open lightbox
$('.row.photo a.thumbnail').click(function(event){
  event.preventDefault();
  showPicture($(this).attr('id'));
});

function showPicture(id){
  $('.modal-gallery').modal();
  $('.modal-gallery img').attr('src','{{@fullmediapath}}/'+id+'-screen.jpg');
  $('.modal-gallery a.dl').attr('href','{{@fullmediapath}}/'+id+'.jpg');

  window.location.hash = id;

  // Show Controls as Appropriate
  $('.modal-gallery .overlay .carousel-control').hide();
  if($('#'+id).parent().next().hasClass('ptile'))
    $('.modal-gallery .overlay .right').show();
  if($('#'+id).parent().prev().hasClass('ptile'))
    $('.modal-gallery .overlay .left').show();
}

// Close Modal if Click Outside
$('.modal-gallery').click(function(){
  $('.modal-gallery').modal('hide');
}).children().click(function(){
  return false;
});
$('.modal-gallery .dl').click(function(){
  window.open($(this).attr('href'));
});

// Prev Image in Modal
$('.modal-gallery .overlay .left').click(function(event){
  event.preventDefault();
  id = window.location.hash.substr(1,40);
  showPicture($('#'+id).parent().prev().children('a.thumbnail').attr('id'));
});

// Next Image in Modal
$('.modal-gallery .overlay .right').click(function(event){
  event.preventDefault();
  id = window.location.hash.substr(1,40);
  showPicture($('#'+id).parent().next().children('a.thumbnail').attr('id'));
});

// Show / Hide Modal Actions Upon Hover
$('.modal-gallery').hover(function(){
  $('.modal-gallery .overlay').fadeIn(120);
},function(){
  $('.modal-gallery .overlay').fadeOut(120);
});

// Close Modal Upon X Click
$('a.action.cl').click(function(event){
  event.preventDefault();
  $('.modal-gallery').modal('hide');
});

// Clear Hash When Modal Closed
$('.modal-gallery').on('hidden',function(){
  window.location.hash = '0';
});

// Keyboard Shortcuts: Right and Left Arrows
$(document).bind('keyup', function (e){
  if(e.which==37){
    if($('.modal-gallery .overlay .left').is(':visible'))
      $('.modal-gallery .overlay .left').trigger('click');
  }else if(e.which==39){
    if($('.modal-gallery .overlay .right').is(':visible'))
      $('.modal-gallery .overlay .right').trigger('click');
  }
  return false;
});
