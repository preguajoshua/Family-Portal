define([
	'app/container',
	'fullcalendar',
	'can/view/stache',
	'can/control/route',
	'can/route/pushstate',
], function(ioc) {
	return ioc.Controller({
		init: function() {
			ioc.app.flushSession();

			var self = this;

			var div = $('<div>');
			$('#wrapper').html(div);

			require(['controllers/login/form'], function(form) {
				new form(div);
			});
		}
	});
});