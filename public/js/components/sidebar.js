define([
	'app/container',
	'can/component',
	'can/view/stache',
], function(ioc) {
	can.Component.extend({
		tag: "sidebar",
		template: can.view('/js/views/sidebar.stache'),
		init: function() {
			this.viewModel.attr('client', ioc.attr('client'));
			this.viewModel.attr('provider', ioc.attr('provider'));
		},
		viewModel: {
			client: null,
			provider: null
		}
	});
	
	// components.Sidebar = can.Control({
	// 	data: {},
	// 	init: function(element) {
	// 		element.html(can.view('views/sidebar.stache', this.options));
	// 	},
	// 	'.nav > li click': function(element) {
	// 		if(element.hasClass('active')) return;
	// 		$(window).trigger('resize');
	// 	}
	// });

	// return components.Sidebar;
});