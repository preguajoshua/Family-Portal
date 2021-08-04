define(['can/model'], function() {
	return can.Model.extend({
	  	findAll: 'GET /front/calendar',
	  	findOne: 'GET /front/calendar/{id}',
		parseModels: function(json) {
			this.count = json.count;
			return json.data;
		}
	},{});
});
