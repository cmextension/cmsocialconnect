function socialConnect(network) {
	jQuery('#cmsc_task').val('connect');
	field = jQuery('#cmsc_network');
	field.val(network);
	field.closest("form").submit();
}

function socialDisconnect(network) {
	jQuery('#cmsc_task').val('disconnect');
	field = jQuery('#cmsc_network');
	field.val(network);
	field.closest("form").submit();
}