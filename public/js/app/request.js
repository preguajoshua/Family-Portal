define(['app/container','can/control'], function(ioc) {
	var request = ioc.Request = function(data) {
		this.data = data;

		this.init = function(data) {
			this.data = data;
		},
		this.getParam = function(key) {
			return typeof this.data[key] ? this.data[key] : null;
		}
	}

	return request;
});