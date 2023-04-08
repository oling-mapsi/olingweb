/* Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import './vendor/bootstrap-icons/font/bootstrap-icons.css';
import './vendor/hs-mega-menu/dist/hs-mega-menu.min.css';
import './vendor/swiper/swiper-bundle.min.css';
import './styles/theme.min.css';

// Importez les dépendances
import $ from 'jquery';
import Popper from 'popper.js';
import 'bootstrap';
import HSHeader from './vendor/hs-header/dist/hs-header.min.js';
import HSMegaMenu from './vendor/hs-mega-menu/dist/hs-mega-menu.min.js';
import HSGoTo from './vendor/hs-go-to/dist/hs-go-to.min.js';
import Swiper from 'swiper/bundle';
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

  // INITIALIZATION OF GO TO
  // =======================================================
  new HSGoTo('.js-go-to');

  // INITIALIZATION OF SWIPER
  // =======================================================
  var swiper = new Swiper('.js-swiper-clients', {
    slidesPerView: 2,
    breakpoints: {
      380: {
        slidesPerView: 3,
        spaceBetween: 15,
      },
      768: {
        slidesPerView: 4,
        spaceBetween: 15,
      },
      1024: {
        slidesPerView: 5,
        spaceBetween: 15,
      },
    },
  });
});
