
jQuery(document).ready(function($) {
	var selected;
	$("#current_slide_list, #new_slide_list").sortable({
		connectWith: ".slidelists",
		cursorAt: { top: 10, left: 100 },
		distance: 30,
		start: function(event, ui) {
			$(ui.helper).width(200).height();
		}
	});
	$("#current_slide_list").bind("sortupdate", function(event, ui) {
		var order = $(this).sortable("toArray");
		for(var i = 0; i < order.length; i++) order[i] = order[i].slice("slide_".length);
			$.post(ajaxurl, {
			"action": "change_slide_order",
			"order":  order.join(",")
		}, function(data) {
			console.log(data);
		});
	});
	$("#new_slide_list").bind( "sortreceive", function(event, ui) {
		ui.item.removeClass("ui-sortable-selected");
	});
	$("#current_slide_list > div, #new_slide_list > div").click(function() {
		if($(this).parent()[0] == $("#new_slide_list")[0]) return;
		if(selected) selected.removeClass("ui-sortable-selected");
		$(this).addClass("ui-sortable-selected");
		selected = $(this);
	});
});

