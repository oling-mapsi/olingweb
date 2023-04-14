/* Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

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
    })

  // INITIALIZATION OF GO TO
  // =======================================================
  new HSGoTo('.js-go-to');

  // INITIALIZATION OF SWIPER
  // =======================================================
  var swiper = new Swiper('.js-swiper-card-grid',{
    navigation: {
      nextEl: '.js-swiper-card-grid-button-next',
      prevEl: '.js-swiper-card-grid-button-prev',
    },
    slidesPerView: 1,
    spaceBetween: 30,
    loop: 1,
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
    },
    on: {
      'imagesReady': function (swiper) {
        const preloader = swiper.el.querySelector('.js-swiper-preloader')
        preloader.parentNode.removeChild(preloader)
      }
    }
  });

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


  $('#button-send').on('click', function () {
   

    $.ajax({
        url: '/send-email',
        type: 'POST',
        data: $(this).serialize(),
        success: function (response) {
            alert(response.message);
            $('#contact-form')[0].reset();
        },
        error: function (response) {
            alert('Une erreur est survenue lors de l\'envoi du message.');
        }
    });
});

});
