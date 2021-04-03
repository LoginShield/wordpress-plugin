(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );

function authorise_business_account(e) {

	jQuery(e).addClass('disabled_btn');
	jQuery('.Loderimg').show();
	var error = 0;
	jQuery("#login-shield-form .input_fields").each(function () {
		if (jQuery(this).val() == '') {
			jQuery(this).addClass('empty_fields');
			error++;
		}
	});
	if (error > 0) {
		jQuery(e).removeClass('disabled_btn');
		jQuery('.Loderimg').hide();
		jQuery('.response_msg').html('*There is some error. Please check all the fields and try again.');
		jQuery('.response_msg').addClass('error_msg');
		jQuery('.response_msg').removeClass('success_msg');
		setTimeout(function () {
			jQuery('.response_msg').html('Response Message');
			jQuery('.response_msg').removeClass('error_msg success_msg');
		}, 2000);
		return false;
	}
	console.log('ajax-start');
	jQuery.ajax({
		url: loginshieldSettingAjax.ajax_url,
		type: 'post',
		data: {
			action 	: 'loginshield_enterprise_settings',
			formdata: jQuery("#login-shield-form").serialize()
		},
		success: function(res) {
			jQuery(e).removeClass('disabled_btn');
			jQuery('.Loderimg').hide();
			var obj = JSON.parse(res);
			if(obj.status==0){
				jQuery('.response_msg').html('*There some error. Please check all the fields and try again.');
				jQuery('.response_msg').addClass('error_msg');
				jQuery('.response_msg').removeClass('success_msg');
				setTimeout(function() {
					jQuery('.response_msg').html('Response Message');
					jQuery('.response_msg').removeClass('error_msg success_msg');
				}, 1500);

			}else{
				jQuery('.response_msg').html('*Setting saved successfully.');
				jQuery('.response_msg').addClass('success_msg');
				jQuery('.response_msg').removeClass('error_msg');
				setTimeout(function() {
					jQuery('.response_msg').html('Response Message');
					jQuery('.response_msg').removeClass('error_msg success_msg');
				}, 1500);
			}
		},
		error: function (err) {
			console.log('err : ', err);
        }
	});
}
