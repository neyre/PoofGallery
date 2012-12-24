$(function(){
  $('.row#albums').sortable();
  $('.row.columns8').sortable();

});

$('.btn-reset').click(function(event){
  event.preventDefault();
  window.location.reload();
});

$('.btn-star').click(function(event){
  event.preventDefault();
  $(this).toggleClass('btn-warning');
  $(this).parents('li').toggleClass('star');
});

$('.btn-thumb').click(function(event){
  event.preventDefault();
  $('.thumb').removeClass('thumb');
  $('.btn-thumb').removeClass('btn-success');
  $(this).addClass('btn-success');
  $(this).parents('li').toggleClass('thumb');
});

// SELECT PHOTOS
$('li.thumbnail img').click(function(event){
  $(this).parents('li').toggleClass('btn-primary');
  if($('li.thumbnail.btn-primary').length){
    $('.btn.selected-actions.disabled').removeClass('disabled');
  }else{
    $('.btn.selected-actions').addClass('disabled');
  }
});

// Keyboard Shortcuts: A Selects All, D deselects All
// Disable When a Modal is In
$(document).bind('keypress', function (e){
  if(!$('.modal').hasClass('in')){
    if(e.which==97){
      $('li.thumbnail:not(.btn-primary) img').trigger('click');
    }else if(e.which==100){
      $('li.thumbnail.btn-primary img').trigger('click');
    }
  }
  return true;
});

// Deselect All
$('.btn-group .btn-deselect-all').click(function(event){
  $('li.thumbnail.btn-primary img').trigger('click');
});

// PREVENT USING SELECTED ACTIONS IF NOTHING SELECTED
$('.btn.selected-actions').click(function(event){
  event.preventDefault();
  if($(this).hasClass('disabled')){
    alert('Select some photos before performing actions on them.');
  }
});

// Select Move Target in Modal
$('.btn-move-select').click(function(event){
  event.preventDefault();
  $('.btn.move-target').removeClass('move-target').removeClass('btn-primary');
  $(this).addClass('btn-primary').addClass('move-target');
  $('.modal#move .btn#submit').removeClass('disabled');
});

// Move Photos
$('.modal#move .btn#submit').click(function(event){
  event.preventDefault();
  if(!$(this).hasClass('disabled')){
    selected = [];
    $('li.thumbnail.btn-primary').each(function(){selected.push($(this).attr('id'));});
    target = $('.btn.move-target').attr('id');
    if(target.length && selected.length){
      $(this).addClass('loading');
      $.ajax({
        type: "POST",
        url: "{{@rootpath}}/move",
        data: {target: target, selected: selected},
        success: function()
        {
          window.location.reload();
        }
      });
    }
  }
});

// Star Selected Photos
$('.btn-group .btn-star-selected').click(function(event){
  $('li.thumbnail.btn-primary:not(.star) .btn-star').trigger('click');
});

// Unstar Selected Photos
$('.btn-group .btn-unstar-selected').click(function(event){
  $('li.thumbnail.btn-primary.star .btn-star').trigger('click');
});

// Publish Selected Photos
$('.btn-group .btn-publish-selected').click(function(event){
  $('li.thumbnail.btn-primary:not(.pub) .btn-pub').trigger('click');
});

// Unpublish Selected Photos
$('.btn-group .btn-unpublish-selected').click(function(event){
  $('li.thumbnail.btn-primary.pub .btn-pub').trigger('click');
});

// Delete Photos & Albums
$('.btn-group .btn-delete').click(function(event){
  event.preventDefault();
  selected = [];
  $('li.thumbnail.btn-primary').each(function(){selected.push($(this).attr('id'));});
  if(selected.length && confirm('Are you sure you want to delete these items?')){
    $.ajax({
      type: "POST",
      url: "{{@rootpath}}/delete",
      data: {selected: selected},
      success: function()
      {
        window.location.reload();
      }
    });
  }
});

// Upon Publish Press, Toggle Whether or not it is in the published area
$('.btn-pub').click(function(event){
  event.preventDefault();
  var tile = $(this).parents('div.ptile');

  // If Published, Unpublish
  if(tile.children('li').hasClass('pub')){
    tile.children('li').toggleClass('pub').find('.btn-thumb').addClass('hide');

    // If no area to move to, create one
    if($('.row[user="'+tile.attr('user')+'"]').length == 0)
      $('.row.columns8.ui-sortable').last().after('<div class="row"><div class="span12"><h4>Unpublished Photos for User: '+tile.attr('user')+'</h4></div></div><div class="row columns8 ui-sortable" user="'+tile.attr('user')+'"></div>');
    
    // Move to appropriate Area
    tile.prependTo('.row[user="'+tile.attr('user')+'"]');

    // If it's a thumbnail, make another photo the thumbnail
    if(tile.children('li').hasClass('thumb')){
      tile.children('li').removeClass('thumb').find('.btn-thumb').removeClass('btn-success');
      $('.row#photos li.photo').first().addClass('thumb').find('.btn-thumb').addClass('btn-success');
    }

  // If Unpublished, Publish (add class and move element up)
  }else{
    tile.children('li').toggleClass('pub').find('.btn-thumb').removeClass('hide');
    tile.appendTo('.row#photos');
  }

  // Toggle Icon Arrow Direction
  $(this).children('i').toggleClass('icon-arrow-up').toggleClass('icon-arrow-down');
});

// Save Organization
$('.btn-save').click(function(event){
  event.preventDefault();

  // Get Affected Photos
  var items = new Array();
  var order = 1;
  $('.thumbnail').each(function(index,element){
    var data = {};
    data['id'] = $(element).attr('id');
    data['star'] = $(element).hasClass('star');
    data['pub'] = $(element).hasClass('pub');
    data['order'] = order;
    items[index] = data;
    order++;
  });

  // Get Album Cover
  var cover = $('.thumbnail.thumb').attr('id');
  var album = $('.pagetitle').attr('id');

  // Send to Server
  $(this).addClass('loading');
  $.ajax({
    type: "POST",
    url: "#",
    data: {items: items, albumcover: cover, album: album},
    success: function()
    {
      window.location.reload();
    },
    error: function(xhr, ajaxOptions, thrownError){
      alert(xhr);
      alert(thrownError);
    }
  });
});