(function() {
  if (!window.console) {
    window.console = {};
  }
  // union of Chrome, FF, IE, and Safari console methods
  var m = [
    'log', 'info', 'warn', 'error', 'debug'
  ];
  // define undefined methods as noops to prevent errors
  for (var i = 0; i < m.length; i++) {
    if (!window.console[m[i]]) {
      window.console[m[i]] = function() {};
    }
  }

})();

var redirect = function(url) {
	$(window).trigger('goto-page', url);
}

// Shortcut alias
require.config({
	urlArgs: 'v='+(new Date()).getTime(),
	baseUrl: '/js/',
	paths: {
		'jquery': 'libs/jquery',
		'requirejs': 'libs/require',
		'pace': 'libs/pace',
		'can': 'libs/can/can',
		'bootstrap': 'libs/bootstrap',
		'bootstrap-validation': 'libs/bootstrapValidator',
		'moment': 'libs/moment',
		'toastr': 'libs/toastr',
		'bootbox': 'libs/bootbox',
		'sticky': 'libs/sticky',
		'fullcalendar': 'libs/fullcalendar',
		'picker': 'libs/picker',
		'picker.date': 'libs/picker.date',
		'picker.time': 'libs/picker.time',
		'jquery.print': 'libs/jquery.print',
	},
    config: {
        moment: {
            noGlobal: true
        }
    },
	// configure dependencies for libraries
	shim: {
		underscore: {
			exports: '_'
		},
		underscorestring: ['underscore'],
		spin: {exports: 'Spinner'},
        ladda: {
            depends: 'spin',
            exports: 'Ladda'
        },
		browser: ['jquery'],
		'bootbox': ['bootstrap'],
		'jquery.print': ['jquery'],
		'gridly': ['mapbox'],
		moment: {
			deps: ['jquery'],
			exports: 'moment'
		},
		'sticky': ['jquery'],
		supercan: ['can'],
		canattributes: ['can'],
		'bootstrap': ['jquery'],
		'picker.date': ['picker'],
		'picker.time': ['picker'],
		bootstraptoggle: ['bootstrap'],
		gridster: {
            deps: ['jquery']
        },
		handlebars: {
			exports: 'Handlebars'
		},
	
		fullcalendar: ['jquery']
	}
});

var delay = (function(){
  var timer = 0;
  return function(callback, ms){
    clearTimeout (timer);
    timer = setTimeout(callback, ms);
  };
})();

define([
	'jquery',
	'app/container',
	'app',
], function($, ioc, app) {
    var modules = [],
    	$document = $(document),
    	$window = $(window);
    	
    	$(document).ready(function() {
	    	var resizeTimer = null;
			// $window.resize(function() {
			// 	console.log('window resize fired!')
			// 	var width = $(window).width();
			// 	var sidebar = ioc.sidebar();
			// 	var timeout = ioc.ready() ? 1000 : 0; 
			// 	if(width <= 800 && ioc.mobile() !== true) {
					// clearTimeout(resizeTimer);
			// 		resizeTimer = setTimeout(function() {
			// 			ioc.sidebar(false);
			// 			ioc.mobile(true);
			// 			$('#wrapper').addClass('mobile');
			// 			$('.main-panel').css('min-width', Math.max(width, 360));
			// 			$('#navbar').css('min-width', Math.max(width, 360));
			//     	}, timeout);
			// 	} else if(width > 800) {
			// 		clearTimeout(resizeTimer);
			// 		resizeTimer = setTimeout(function() {
			// 			ioc.sidebar(true);
			// 			ioc.mobile(false);
			// 			$('#wrapper').removeClass('mobile');
			// 			$('.main-panel').css('min-width', Math.max(width-275, 360));
			// 			$('#navbar').css('min-width', Math.max(width-275, 360));
			//     	}, timeout);
			// 	} else {

			// 	}
			// 	clearTimeout(resizeTimer);
			// 	resizeTimer = setTimeout(function() {
			// 		if (screen.width <= 320) {
			// 			document.getElementById("viewport").setAttribute("content", "width=device-width, initial-scale=.8");
			// 		}
			// 	});
			// });

			$document.on('mouseup', function(e) {
			    if(!$(e.target).closest('.popover').length) {
			        $('.popover').each(function(){
			            $(this.previousSibling).trigger('click');
			        });
			    }
			});


    	})
});