define([
	'jquery',
	'modules/library',
	'can/control',
], function($, modules) {
	var module = modules.Navbar = can.Control({
		data: {},
		init: function(element) {
			element.html(can.view('views/navbar.stache', this.options));
			console.log('NavBar Options', this.options);

			$(document).ready(function() {
				$(window).trigger('resize');
			});
			// this.options.sidebar(true);
		},
		'{sidebar} change': function(data) {

			var sidebarWidth = 260;
			var duration = 150;

			console.log('Sidebar event listener changes', data());
			console.log('Mobile Status:', this.options.mobile());
			if(data() === false) {
				// .css('box-shadow', '3px -8px 10px #000')
				$('.main-panel').css('box-shadow', '3px -63px 10px #000').animate({
					'left': 0,
					'min-width': '100%'
				}, {
					duration: duration,
					complete: function() {
						// $('#content').css('box-shadow', '');
						// $('#navbar').css('box-shadow', '');
					}
				});

				$('#navbar').animate({
					"left": 0
				}, duration);
			} else {
				console.log('FIRE SIDE BAR CHANGE ');
				$('.main-panel').animate({
					'left': sidebarWidth,
					'min-width': $(window).width()-sidebarWidth
				}, {
					duration: duration,
					complete: function() {
						$('.main-panel').css({'box-shadow': '', 'width': ''});
						// $('#content').css('box-shadow', '');
						// $('#navbar').css('box-shadow', '');
					}
				});

				$('#navbar').animate({
					"left": sidebarWidth
				}, duration);
			}
		},
		'#navbar-slide click': function(element, event) {
			event.preventDefault();
			this.options.sidebar(!this.options.sidebar());

			// if($('.wrapper').hasClass('sidebar-toggle')) {
			// 	$('#wrapper').animate({
			// 		"left" :'-260px',
			// 		"width": $(window).width()+260
			// 		}, {
			// 		complete: function() {
			// 			$('#wrapper').attr('style', null);
			// 			$('.main-panel').width('100%');
			// 			$('.wrapper').removeClass('sidebar-toggle');
			// 		}
			// 	});
			// 	$('#navbar').animate({
			// 		"left": 0
			// 	})

			// } else {

			// 	$('.main-panel').animate({
			// 		"left" :'260',
			// 		}, {
			// 		complete: function() {
			// 			$('.main-panel').attr('style', null);
			// 			$('.wrapper').addClass('sidebar-toggle');
			// 			$('#wrapper').attr('style', null);
			// 		}
			// 	});
			// 	$('#navbar').animate({
			// 		"left": 260
			// 	}, {
			// 		complete: function() {
			// 		$('#navbar').attr('style', null);
			// 	}});
			// }
		},
		'.logout click': function(element, event) {
			event.preventDefault();

			$.post('/auth/logout', function(data) {
				window.location.href = '/login';
			});
		}
	});


	return module;
});