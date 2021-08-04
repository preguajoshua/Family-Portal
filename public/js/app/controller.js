define([
	'app/container',
	'can/view/stache',
	'can/control'
], function(ioc) {
	return ioc.Controller = can.Control.extend({
		render: function(view, data) {
			this.element.parent().find('> div').each(can.proxy(function(index, div) {
				if(!this.element.is(div)) {
					$(div).css('display', 'none').empty().remove();
				}
			}, this));
			this.element.html(can.view(view, data));

			if(!ioc.ready()) {
				ioc.ready(true);
			}

			if(!$('body').hasClass('loaded')) {
				ioc.app.hideLoader();
			}
		}
	});
});