jQuery(document).ready( function($) {

	$('#lazyest_watermark_settings_button').pointer({
  	content: waterMark.content,
		 position: {'edge':'right'},
		 close: function() {
		 	$.post( ajaxurl, {
				pointer: 'lazyest_watermark',
				action: 'dismiss-wp-pointer'
			});
		 }
	}).pointer('open');
});    