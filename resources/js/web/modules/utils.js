var Utils = (function() {
	
	// selectors
	var selectors = {
    html:      'html',
    body:      'body',
    btnToggle: '[data-toggle]',
    btnMember: '.js-btn-member-list',
    btnMemberSub: '.js-btn-member-sublist',
    btnTeam: '.js-btn-team',
    btnScroll: '.js-btn-scroll',
  };
  
  var attr = {
    toggle: 'toggle',
    show: 'show',
    hide: 'hide'
  };

  var classes = {
    active: 'is-active'
  };

  // Init
  var _initialize = function() {
    _bind();
  };

  // Bind events
  var _bind = function() {
    $(selectors.body).on('click', selectors.btnToggle, function(){
      _toggle($(this));
    });

    $(selectors.body).on('click', selectors.btnMember, function(){
      _toggleList($(this));
    });

    $(selectors.body).on('click', selectors.btnTeam, function(){
      _toggleTeam($(this));
    });

    $(selectors.body).on('click', selectors.btnMemberSub, function(){
      _toggleSubList($(this));
    });

    $(selectors.body).on('click', selectors.btnScroll, function(){
      $.scrollTo('100%', 400);
    });

    if ($(selectors.body).find(selectors.btnScroll)) {
      setTimeout(function(){
        $.scrollTo('100%', 400);
      }, 4000);
    }
  };

  var _toggle = function(el) {
    var data = el.data(attr.toggle).split(":");
    if (data.length == 2) {
      el.toggleClass(classes.active);
      el[data[0]](data[1]).toggle(); // i.e. el.next('div').toggle();
    }
  };

  var _show = function(el) {
    var data = el.data(attr.show).split(":");
    if (data.length == 2) {
      el[data[0]](data[1]).show(); // i.e. el.next('div').show();
    }
  };

  var _hide = function(el) {
    var data = el.data(attr.hide).split(":");
    if (data.length == 2) {
      el[data[0]](data[1]).hide(); // i.e. el.next('div').hide();
    }
  };

  var _toggleList = function(el) {

    // toggle
    if (el.hasClass(classes.active)) {
      el.removeClass(classes.active);
      el.find('span').removeClass(classes.active);
      el.next('div').hide();
    }
    else {
      // hide all
      el.parents('article').find('.member__list > div').hide();
      el.parents('article').find('.member__list > a').removeClass(classes.active);
      el.parents('article').find('.member__list > a span').removeClass(classes.active);

      el.addClass(classes.active);
      el.find('span').addClass(classes.active);
      el.next('div').show();
    }

  };

  var _toggleTeam = function(el) {

    // toggle
    if (el.hasClass(classes.active)) {
      el.removeClass(classes.active);
      el.next('.contact-member-list-items').hide();
    }
    else {
      // hide all
      el.parents('.contact__members').find('.contact-member-list-items').hide();
      el.parents('.contact__members').find('a').removeClass(classes.active);

      el.addClass(classes.active);
      el.next('.contact-member-list-items').show();
    }

  };

  var _toggleSubList = function(el) {

    // toggle
    if (el.hasClass(classes.active)) {
      el.removeClass(classes.active);
      el.find('span').removeClass(classes.active);
      el.next('div').hide();
    }
    else {
      // hide all
      el.parents('ul').find('li > div').hide();
      el.parents('ul').find('li > a').removeClass(classes.active);
      el.parents('ul').find('li > a span').removeClass(classes.active);

      el.addClass(classes.active);
      el.find('span').addClass(classes.active);
      el.next('div').show();
    }

  };


  /* --------------------------------------------------------------
    * RETURN PUBLIC METHODS
    * ------------------------------------------------------------ */

  return {
    init:  _initialize,
  };
	
})();

// Initialize
$(function() {
  Utils.init();
});

