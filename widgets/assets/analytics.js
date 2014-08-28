$(function () {
	var options = {
		requestUrl: null,
		debug: false,
		data: {}
	};
	$.fn.analytics = function (localOptions) {
		options = $.extend(options, localOptions || {});
		$.fn.analytics.init();
	};

	$.fn.analytics.init = function() {
		if(options.debug)
			console.log("Analytics request begin");
		var url = $.fn.analytics.prepareUrl();
		$.ajax({
			url: url,
			method: 'GET',
			dataType: 'json',
			success: function(data) {
				if(data.success == true) {
					if(options.debug) {
						console.log("Analytics success request");
					}
				} else {
					console.warn("Analytics response data error");
					console.log(data);
				}
			},
			error: function(error) {
				if(options.debug) {
					console.warn("Analytics request error");
					console.log(options);
				}
			}
		});
	};

	$.fn.analytics.prepareUrl = function() {
		
	};
});