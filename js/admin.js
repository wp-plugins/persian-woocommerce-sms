;(function($) {
	
	// Gateway select change event
	$('.hide_class').hide();
	$('#persianwoosms_gateway\\[sms_gateway\\]').on( 'change', function() {
		var self = $(this),
			value = self.val();
		$('.hide_class').hide();
		$('.'+value+'_wrapper').fadeIn();
	});

	// Trigger when a change occurs in gateway select box 
	$('#persianwoosms_gateway\\[sms_gateway\\]').trigger('change');

	// handle send sms from order page in admin panale
	var w = $('.persianwoosms_send_sms').width(),
		h = $('.persianwoosms_send_sms').height(),
		block = $('#persianwoosms_send_sms_overlay_block').css({
					'width' : w+'px',
					'height' : h+'px',
				});


	$( 'input#persianwoosms_send_sms_button' ).on( 'click', function(e) {
		e.preventDefault();
		var self = $(this),
			textareaValue = $('#persianwoosms_sms_to_buyer').val(),
			smsNonce = $('#persianwoosms_send_sms_nonce').val(),
			orderId = $('input[name=order_id][type=hidden]').val(),
			data = {
				action : 'persianwoosms_send_sms_to_buyer',
				textareavalue: textareaValue,
				sms_nonce: smsNonce,
				order_id: orderId
			};

		if( !textareaValue ) {
			return;
		}
		self.attr( 'disabled', true );
		block.show();
		$.post( persianwoosms.ajaxurl, data , function( res ) {
			if ( res.success ) {
				$('div.persianwoosms_send_sms_result').html( res.data.message ).show();
				$('#persianwoosms_sms_to_buyer').val('');
				block.hide();
				self.attr( 'disabled', false );
			} else {
				$('div.persianwoosms_send_sms_result').html( res.data.message ).show();	
				block.hide();
				self.attr( 'disabled', false );
			}
		});
	});


})(jQuery);