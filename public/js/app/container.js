define([    
	'can/util/library',
    'can/control/route',
    'can/model',
    'can/map/define',
    'can/view/stache',
    'can/component'
    ], function() {
	return new (can.Map({
		guest: true, 
		mobile: can.compute(false), 
		duration: false,
		sidebar: can.compute(null), 
		ready: can.compute(false), 
		render: can.compute(false), 
		client: {}, 
		provider: {}, 
		user: {}, 
		page: {}, 
		token: ''
	}));
});