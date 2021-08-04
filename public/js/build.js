({
    baseUrl: '.',
    mainConfigFile: 'main.js',

    name: 'main',
    out: 'dist/app.js',
    optimize: 'uglify2',
    removeCombined: false,
    findNestedDependencies: true,
    
    include: [
	    'main',
		'controllers/calendar',
		'controllers/carelogs',
		'controllers/clients',
		'controllers/contacts',
		'controllers/home',

		'controllers/invoices',
		'controllers/invoices/details',
		'controllers/invoices/main',

		'controllers/login',
		'controllers/medications',
		'controllers/profile',
		'controllers/switch',

		'jquery',
		'pace',
		'can',
		'bootstrap',
		'bootstrap-validation',
		'moment',
		'toastr',
		'bootbox',
		'sticky',
		'fullcalendar',
		'picker',
		'picker.date',
		'picker.time',
		'jquery.print',
    ],
    //name: '../../resources/assets/bower_components/almond/almond',
})
