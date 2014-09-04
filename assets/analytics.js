$(function() {
	$('#graphForm').submit(function() {
		return true;
		var form = $(this);
		var data = form.serialize();
		$.ajax({
			method: 'GET',
			url: form.attr('action'),
			data: data,
			dataType: 'json',
			success: function(data) {
				$.each(data, function(graphId, series) {
					if(typeof charts[graphId] != 'undefined') {
						$.each(series, function(id, data) {
							charts[graphId].series[id].setData(data, false, false, false);
						});
						charts[graphId].redraw();
					}
				});
			}
		});
		return false;
	});
});