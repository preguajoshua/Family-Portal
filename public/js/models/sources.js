define(['can/model'], function() {
	return can.Model.extend({
		create: "POST /front/billing",
		destroy: 'DELETE /front/billing/{id}'
	}, {
	});
});
