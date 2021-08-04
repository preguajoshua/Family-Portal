define([], function() {

	return {
		cache: {},
		count: 0,
		max: 20,
		has: function(url) {
			return typeof this.cache[url] !== 'undefined';
		},		
		// Deprecated
		exists: function(url) {
			return typeof this.cache[url] !== 'undefined';
		},
		set: function(url, data) {
			if(this.has(url)) return;

			if(this.count >= this.max) {
				for(var i in this.cache) {
					if(i >= 10) {
						delete this.cache[i];
						this.count--;
					}
				}
			}
			this.count++;
			this.cache[url] = data;
		},
		get: function(url) {
			return typeof this.cache[url] !== 'undefined' ? this.cache[url] : false;
		},
		forget: function() {
			delete this.cache[url];
		},
		flush: function() {
			console.log('Flushing cache...');
			this.cache = {};
			this.count = 0;
		}
	};

});