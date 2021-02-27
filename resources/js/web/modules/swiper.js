/**
 * Dependencies
 */
import Swiper from '../vendor/swiper.js';

var SwiperUi = (function() {

  var selectors = {
    html:   'html',
    body:   'body',
    swiperHorizontal: '.js-swiper-horizontal',
    swiperVertical: '.js-swiper-vertical',
    swiperThumb: '.js-swiper-thumb'
  };

  var swiperVertical;
  var swiperHorizontal;
     
  var _initialize = function() {
    _bind();
  };

  var _bind = function() {
    swiperVertical = new Swiper(selectors.swiperVertical, {
      slidesPerView: 'auto',
      direction: 'vertical',
      speed: 400,
      autoplay: {
        delay: 3000,
      },
      navigation: {
        nextEl: '.swiper-btn-next',
        prevEl: '.swiper-btn-prev',
      },
      spaceBetween: 0,
      mousewheel: {
        invert: false,
      },
    });

    swiperHorizontal = new Swiper(selectors.swiperHorizontal, {
      slidesPerView: 'auto',
      speed: 400,
      autoplay: {
        delay: 3000,
      },
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      spaceBetween: 0,
    });
  };
  
  return {
    init:  _initialize,
  };
	
})();

// Initialize
$(function() {
  if ($('body').find('.swiper-container').length) {
    SwiperUi.init();
  }
});