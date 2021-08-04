define(['modules/library'], function(modules) {
	var module = modules.get = function(url, data, callback) {
		return $.get(url, data, callback);
	}

	return module;
});