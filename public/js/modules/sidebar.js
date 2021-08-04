define([
	'modules/library',
	'can/control',
	'can/view/stache',
], function(modules) {
	var module = modules.Sidebar = can.Control({
		data: {},
		init: function(element) {
			element.html(can.view('views/sidebar.stache', this.options));
		},
		'.nav > li click': function(element) {
			if(element.hasClass('active')) return;
			$(window).trigger('resize');
		}
	});

	return module;
});
