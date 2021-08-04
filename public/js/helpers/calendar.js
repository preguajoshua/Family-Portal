define([
	'moment',
	'can/view/stache',
], function(moment) {

	can.stache.registerHelper('displayEventsList', function(events, options) {
		var keys = {},
			data = [];

		var today = moment();
		var tomorrow = moment(today).add(1, 'day');
		var todayDate = today.format('YYYY-MM-DD');
		var tomorrowDate = tomorrow.format('YYYY-MM-DD');

		events.each(function(evt) {
			if(!evt.attr('Id')) {
				return;
			}

			var idx = evt.attr('startDateObject').format('YYYY-MM-DD');
			var key = typeof keys[idx] !== 'undefined'

			if(typeof keys[idx] === 'undefined') {
				keys[idx] = data.length;

				var label = idx;
				if(idx == todayDate) {
					label = 'Today '+today.format('M/D/YY');
				} else if(idx == tomorrowDate) {
					label = 'Tomorrow '+today.format('M/D/YY');
				} else {
					label = evt.attr('startDateObject').format('dddd M/D/YY');
				}

				data.push({
					label: label,
					timestamp: evt.attr('startDateObject').unix(),
					items: []
				});
			}
			
			data[keys[idx]].items.push(evt);
		});
		if(data.length <= 0) {
			return options.inverse(this);
		}

		return options.fn(data.sort(function(obj1, obj2) {
			// Ascending: first age less than the previous
			return obj1.timestamp - obj2.timestamp;
		}));
	});

});