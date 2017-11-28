var url;
function request_invite(data_api)
{
  $('#progress_bar').removeClass('hide');
  $('#demo_name').attr('disabled','true');
  $('#demo_email').attr('disabled','true');
  $('#demo_mob').attr('disabled','true');
  $('#demo_submit_btn').hide();
  open_processing_ur_request_swal('Hold on. An invite is being created, especially for you..');

var a=$.ajax({
      dataType : "json",
      type:'POST',
      data: data_api,
      url:'./demo_user.php',
      success: function(data){
        if (data.status) {
          var resp_text="";
          if (data.resp_both) {
           resp_text = "We have sent the Demo Invitation to you via SMS & Email.<br>";
          }
          else if(data.resp_sms){
            resp_text="We have sent the Demo Invitation to you via SMS<br>";

          }
          else if(data.resp_email){
           resp_text="We have sent the Demo Invitation to you via Email.<br>"; 
          }
          else{
            resp_text="Thank you for providing the details.<br>"
          }
          url = data.url;
          $('#progress_bar').addClass('hide');
          var ecard_text="";
          if(data.only_ecard)
              ecard_text ="<br><small>Note: This is a simple video/digital e-invite. We now offer an advanced e-invite with more features.</small>"
          swal({
              html : resp_text+'Redirecting to your invitation in 15 seconds..'+ecard_text,
              // showConfirmButton : false,
              confirmButtonText: 'Show Invite',
              animation: false,
              confirmButtonColor: '#26a69a',
              showCancelButton : false,
              timer : 15000
          }).then(function(){
              window.location.href=url;
          },function(dismiss){
              window.location.href=url;
          });
        } else {
          swal({
            title:"Oops...",
            text: "It seems there was an error. Please try again after some time.",
            type: "warning",
          });
          console.log(data.msg);
          $('#progress_bar').addClass('hide');
        }
      },
      error: function(xhr, ajaxOpt, err) {
        swal({
            title:"Oops...",
            text:"Something went wrong while fetching data from the server!",
            type: "error",
        });
          $('#progress_bar').addClass('hide');
          return;
      }
    });
  return a;
}

function send_demo_invite()
{
  if(id!=-1)
    var data={action_id:1, id: id,nick_name:$('#demo_name').val().trim(),mobile:$('#demo_mob').val().trim(),email:$('#demo_email').val().trim()};
  else
    var data={action_id:1, nick_name:$('#demo_name').val().trim(),mobile:$('#demo_mob').val().trim(),email:$('#demo_email').val().trim()};
  var a=request_invite(data);
  $.when(a).done(function(){
    return true;
  }).fail(function(){
    return;
  });
}

function validate_name(name) {
  var name_regex = /^[A-Za-z0-9\s\.]{1,40}$/;
  if(!(name_regex.test(name)))
      return false;
  return true;
}
function validate_mobile(a) {
  var mob_regex=/^[7-9]{1}[0-9]{9}$/;
  if(!(mob_regex.test(a)))
      return false;
  return true;
}
function validate_email(a) {
  var email_regex=/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  if(!(email_regex.test(a)))
      return false;
  return true;
}

$(".button-collapse").sideNav();
$('.button-collapse').sideNav({
  menuWidth: 300, // Default is 300
  edge: 'left', // Choose the horizontal origin
  closeOnClick: true, // Closes side-nav on <a> clicks, useful for Angular/Meteor
  draggable: true, // Choose whether you can drag to open on touch screens,
})
window.onscroll = () => {
  if ($(window).scrollTop() > 50) {
      $('nav').removeClass('bg-none');
      $('.down-arrow').fadeOut(200);
  } else {
      $('nav').addClass('bg-none');
      $('.down-arrow').fadeIn(200);
  }
}
function wScroll() {
  $('html, body').animate({
      scrollTop : $('#demo_user_form').offset().top-30
  });
  $('#demo_name').focus();
}

function demo_validate(e) {
  e.preventDefault();
  var name = $('#demo_name').val().trim();
  var email = $('#demo_email').val().trim();
  var mob = $('#demo_mob').val().trim();

  if (!validate_name(name) || !validate_email(email) || !validate_mobile(mob)) {
    if (!validate_name(name)) {
      $('#demo_name').keyup().focus();
    } else if (!validate_email(email)) {
      $('#demo_email').keyup().focus();
    } else if (!validate_mobile(mob)) {
      $('#demo_mob').keyup().focus();
    }
    return false;
  } else {
      send_demo_invite();
    return true;
  }
}

function onValidInput(target, fname, err) {
  var val = target.value.trim();
  if (val == "") {
      $('label[for="'+target.id+'"]').attr('data-error','Required');
      $(target).removeClass('valid').addClass('invalid');
      return false;
  }
  else if (!fname(val)) {
      $('label[for="'+target.id+'"]').attr('data-error',err);
      $(target).removeClass('valid').addClass('invalid');
      return false;
  }
  else {
      $(target).removeClass('invalid').addClass('valid');
      return true;
  }
}
function open_processing_ur_request_swal(text=''){
  if(!text.trim())
  {
      swal({
          html : '<div class="preloader-wrapper big active"> <div class="spinner-layer spinner-blue"> <div class="circle-clipper left"> <div class="circle"></div></div><div class="gap-patch"> <div class="circle"></div></div><div class="circle-clipper right"> <div class="circle"></div></div></div><div class="spinner-layer spinner-red"> <div class="circle-clipper left"> <div class="circle"></div></div><div class="gap-patch"> <div class="circle"></div></div><div class="circle-clipper right"> <div class="circle"></div></div></div><div class="spinner-layer spinner-yellow"> <div class="circle-clipper left"> <div class="circle"></div></div><div class="gap-patch"> <div class="circle"></div></div><div class="circle-clipper right"> <div class="circle"></div></div></div><div class="spinner-layer spinner-green"> <div class="circle-clipper left"> <div class="circle"></div></div><div class="gap-patch"> <div class="circle"></div></div><div class="circle-clipper right"> <div class="circle"></div></div></div></div><p>Processing your request...</p>',
          allowOutsideClick :false,
          allowEscapeKey : false,
          showConfirmButton : false
      });
  }
  else {
      swal({
          html : '<div class="preloader-wrapper big active"> <div class="spinner-layer spinner-blue"> <div class="circle-clipper left"> <div class="circle"></div></div><div class="gap-patch"> <div class="circle"></div></div><div class="circle-clipper right"> <div class="circle"></div></div></div><div class="spinner-layer spinner-red"> <div class="circle-clipper left"> <div class="circle"></div></div><div class="gap-patch"> <div class="circle"></div></div><div class="circle-clipper right"> <div class="circle"></div></div></div><div class="spinner-layer spinner-yellow"> <div class="circle-clipper left"> <div class="circle"></div></div><div class="gap-patch"> <div class="circle"></div></div><div class="circle-clipper right"> <div class="circle"></div></div></div><div class="spinner-layer spinner-green"> <div class="circle-clipper left"> <div class="circle"></div></div><div class="gap-patch"> <div class="circle"></div></div><div class="circle-clipper right"> <div class="circle"></div></div></div></div><p>'+text+'</p>',
          allowOutsideClick :false,
          allowEscapeKey : false,
          showConfirmButton : false
      });
  }
}
