define([
	'moment',
	'can/view/stache',
], function(moment) {

	can.stache.registerHelper('GetSingleClient', function(clients, options) {
		var from = options.hash.from;

		var obj;
		var t = clients.each(function(element, index) {
			if(element.Id == from) {
				obj = element;
				return true;
			}
		});

		return options.fn(obj);		
	});

});