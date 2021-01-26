/**
 * Dependencies
 */
import Swiper from '../vendor/swiper.js';

var SwiperUi = (function() {

  var selectors = {
    html:   'html',
    body:   'body',
    swiper: '.js-swiper',
    swiperThumb: '.js-swiper-thumb'
  };

  var mySwiper;
     
  var _initialize = function() {
    _bind();
  };

  var _bind = function() {
    // Initialize swiper
    mySwiper = new Swiper(selectors.swiper, {
      slidesPerView: 'auto',
      centeredSlides: true,
      speed: 400,
      autoplay: {
        delay: 3000,
      },
      pagination: {
        el: '.swiper-pagination',
      },
      spaceBetween: 15,
    });

    // Listener for thumbnails
    $(selectors.body).on('click', selectors.swiperThumb, function(){
      mySwiper.slideTo($(this).data('idx'));
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