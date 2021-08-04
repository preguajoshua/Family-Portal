define([
	'app'
], function(app) {
	return app.Controller({
		test: [],
		init: function() {
			console.log('calendar controller');

			window.location.href = '/calendar';
		},	
	});
});