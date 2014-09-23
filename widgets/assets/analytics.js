$(function () {
	$.fn.analytics = function (localOptions) {
		var $this = $.fn.analytics;
		var optionsData = $.extend($this.options.data, localOptions.data || {});
		$this.options = $.extend($this.options, localOptions || {});
		$this.options.data = optionsData;
		$this.init();
	};

	$.fn.analytics.options = {
		requestUrl: null,
		debug: false,
		data: {
			id: null,
			name: document.title,
			data: document.referrer
		}
	};

	$.fn.analytics.init = function() {
		var $this = this;
		if($this.options.debug) {
			console.log("Analytics request begin");
			console.log($this.options.data);
		}
		$.ajax({
			url: $this.options.requestUrl,
			method: 'GET',
			data: $this.options.data,
			dataType: 'json',
			success: function(data) {
				if(data.success == true) {
					if($this.options.debug) {
						console.log("Analytics success request");
					}
				} else {
					console.warn("Analytics response data error");
					console.log(data);
				}
			},
			error: function(error) {
				if($this.options.debug) {
					console.warn("Analytics request error");
					console.log($this.options);
				}
			}
		});
	};
});