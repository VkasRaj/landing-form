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
    alert(`Congratulation ${name} !!! You have successfully submitted your form.`);
    document.demo_form.reset();
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