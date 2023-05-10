/* Welcome to your app's main JavaScript file! */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import './vendor/bootstrap-icons/font/bootstrap-icons.css';
import './vendor/hs-mega-menu/dist/hs-mega-menu.min.css';
import './vendor/hs-mega-menu/dist/hs-mega-menu.min.css';
import './vendor/hs-video-bg/dist/hs-video-bg.css';
import './vendor/swiper/swiper-bundle.css';
import './styles/theme.min.css';

// Importez les dépendances
import $ from 'jquery';
import Popper from 'popper.js';
import 'bootstrap';
import HSHeader from './vendor/hs-header/dist/hs-header.min.js';
import HSStickyBlock from './vendor/hs-sticky-block/dist/hs-sticky-block.min.js';
import HSMegaMenu from './vendor/hs-mega-menu/dist/hs-mega-menu.min.js';
import HSGoTo from './vendor/hs-go-to/dist/hs-go-to.min.js';
import Swiper from 'swiper/bundle';
import HSVideoBg from './vendor/hs-video-bg/dist/hs-video-bg.min.js';
import FsLightbox from './vendor/fslightbox/index.js';

import './js/theme.min.js';

// Assurez-vous que jQuery et Popper.js sont disponibles globalement (nécessaire pour certaines parties du thème Unify)
window.$ = window.jQuery = $;
window.Popper = Popper;

// start the Stimulus application
import './bootstrap';

$(document).ready(function () {
  // INITIALIZATION OF NAVBAR
  // =======================================================
  new HSHeader('#header').init();

  // INITIALIZATION OF MEGA MENU
  // =======================================================
  const megaMenu = new HSMegaMenu('.js-mega-menu', {
    desktop: {
      position: 'left'
    }
  });


  // INITIALIZATION OF VIDEO BG
    // =======================================================
    document.querySelectorAll('.js-video-bg').forEach(item=> {
      new HSVideoBg(item).init()
      console.log('video')
    })

  // INITIALIZATION OF GO TO
  // =======================================================
  new HSGoTo('.js-go-to');

  (function() {
     // INITIALIZATION OF SWIPER
      // =======================================================
      var sliderThumbs = new Swiper('.js-swiper-blog-modern-hero-thumbs', {
        slidesPerView: 4,
        breakpoints: {
          580: {
            slidesPerView: 3,
            spaceBetween: 30,
          },
          1024: {
            slidesPerView: 4,
            spaceBetween: 50,
          },
        },
      });

      // Blog Modern Hero
      var swiper = new Swiper('.js-swiper-blog-modern-hero',{
        effect: 'fade',
        autoplay: {
          delay: 11000,
        },
        loop: true,
        pagination: {
          el: '.js-swiper-blog-modern-hero-pagination',
          clickable: true,
        },
        thumbs: {
          swiper: sliderThumbs
        }
      });

      // Projets
      var swiper = new Swiper('.js-swiper-card-grid',{
        navigation: {
          nextEl: '.js-swiper-card-grid-button-next',
          prevEl: '.js-swiper-card-grid-button-prev',
        },
        slidesPerView: 1,
        spaceBetween: 30,
        loop: 2,
        breakpoints: {
          480: {
            slidesPerView: 2
          },
          768: {
            slidesPerView: 2
          },
          1024: {
            slidesPerView: 3
          },
        }
      });


  })()



  // INITIALIZATION OF STICKY BLOCKS
  // =======================================================
  new HSStickyBlock('.js-sticky-block', {
    targetSelector: document.getElementById('header').classList.contains('navbar-fixed') ? '#header' : null
  })
  
  // Ajouter un événement de soumission du formulaire
  $('#envoiemail').on('click', function () {
    console.log($('#envoiemail'))
      // Récupérer la valeur de l'e-mail
      var email = $('#champsemail').val();

      // Envoyer une requête AJAX
      $.ajax({
          url: '/add-email',
          type: 'POST',
          data: {
              email: email
          },
          success: function (response) {
              // Vider le champ email
             $('#champsemail').val('');
              // Afficher un message de succès
              alert(response.message);
          },
          error: function (response) {
              // Afficher un message d'erreur
              alert('Une erreur est survenue lors de l\'enregistrement de l\'adresse e-mail.');
          }
      });
  });


       // email
  // =======================================================

  $('#button-send').on('click', function () {
    $.ajax({
        url: '/send-email',
        type: 'POST',
        data: $('#contact-form').serialize(),
        success: function (response) {
            if (response.success) {
                alert(response.message);
                $('#contact-form')[0].reset();
            } else {
                alert(response.message);
            }
        },
        error: function (response) {
            alert('Une erreur est survenue lors de l\'envoi du message.');
        }
    });
});

  const lightbox = new FsLightbox();

  // Initialiser le plugin
  document.querySelectorAll('.js-fslightbox').forEach((element) => {
    element.addEventListener('click', (event) => {
      event.preventDefault();
      lightbox.open(element.dataset.src.split(','));
    });
  });
});

$(document).ready(function() {
  if(window.location.pathname !== '/' && $(this).scrollTop() <= 40) {
      $('.mymenumoov').addClass('navbar-light');
      $('.mymenumoov').removeClass('navbar-dark');
      $('.mymenulink').removeClass('link-light');
  } else {
      $('.mymenumoov').addClass('navbar-dark');
      $('.mymenumoov').removeClass('navbar-light');
      $('.mymenulink').addClass('link-light');
  }

  $(document).scroll(function() {
    if(window.location.pathname !== '/' && $(this).scrollTop() <= 40) {
      $('.mymenumoov').addClass('navbar-light');
      $('.mymenumoov').removeClass('navbar-dark');
      $('.mymenulink').removeClass('link-light');
  } else {
      $('.mymenumoov').addClass('navbar-dark');
      $('.mymenumoov').removeClass('navbar-light');
      $('.mymenulink').addClass('link-light');
  }
  });
});


$(document).ready(function() {
  // Vérifier la largeur de lécran à chaque changement de taille
  $(window).resize(function() {
    if ($(window).width() < 768) {
      $('.swiper-slide').removeClass('fullheight');
    } else {
      $('.swiper-slide').addClass('fullheight');
    }
  });
});

$(document).ready(function() {
  $('.highlight-text').each(function() {
    var textElement = $(this);
    var text = textElement.text();
    var lines = text.split('\n');
    var highlightedText = '';

    for (var i = 0; i < lines.length; i++) {
      var line = lines[i].trim();

      // Ajoutez un espace au début et à la fin de chaque ligne
      line = ' ' + line + ' ';

      // Ajoutez la classe "highlighted-line" pour le surlignage
      line = '<span class="highlight">' + line + '</span>';

      highlightedText += line;
    }

    textElement.html(highlightedText);
  });
});









