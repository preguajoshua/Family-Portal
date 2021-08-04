define(['app/container'], function(ioc) {
	return ioc.Config = {
		ContentClass: '#content',
		redirectTo: 'calendar',
		redirectLogin: 'login',
		redirectSwitch: 'switch',
	}
});