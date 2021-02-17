var Utils = (function() {
	
	// selectors
	var selectors = {
    html:      'html',
    body:      'body',
    btnToggle: '[data-toggle]',
    btnMember: '.js-btn-member-list',
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
  };

  var _toggle = function(el) {
    var data = el.data(attr.toggle).split(":");
    if (data.length == 2) {
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
    el.toggleClass(classes.active);
    el.find('span').toggleClass(classes.active);
    el.next('div').toggle();
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

