define(['can/model'], function() {
	return can.Model.extend({
		findAll: 'GET /front/invoices',
		findOne: 'GET /front/invoices/{id}'
	});
});
