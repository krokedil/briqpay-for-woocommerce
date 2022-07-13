jQuery(function ($) {
	var briqpayAdmin = {
		wrapperElement: $('#briqpay_rules_result_wrapper'),
		buttonElement: $('#briqpay_show_rules'),

		toggleRules: function() {
			briqpayAdmin.wrapperElement.slideToggle( "slow" );
		},

		 getUrlParameter : function getUrlParameter(sParam) {
			var sPageURL = decodeURIComponent(window.location.search.substring(1)),
				sURLVariables = sPageURL.split('&'),
				sParameterName,
				i;

			for (i = 0; i < sURLVariables.length; i++) {
				sParameterName = sURLVariables[i].split('=');

				if (sParameterName[0] === sParam) {
					return sParameterName[1] === undefined ? true : sParameterName[1];
				}
			}
		},

		init: function () {
			$('body').on('click', '#briqpay_show_rules', briqpayAdmin.toggleRules );
			$('body').on('click', '.sync-btn-briqpay', briqpayAdmin.syncOrderBtn );

		},

		syncOrderBtn:function(e) {
			e.preventDefault();
			$('.sync-btn-briqpay').addClass( 'disabled' );
			$.ajax({
				type: 'POST',
				data: {
					id: briqpayAdmin.getUrlParameter('post'),
					nonce: briqpayParamsMeta.update_order_orm_url_nonce,
				},
				dataType: 'json',
				url: briqpayParamsMeta.update_order_orm,
				success: function (data) {
					console.log(data);
					if(data.success) {
						window.location.reload();
					} else {
						$('.sync-btn-briqpay').removeClass( 'disabled' );
						$('.briqpay_sync_wrapper').append( '<div><i>' + data.data + '</i></div>' );
					}
				},
				error: function (data) {
					console.log(data);
					console.log(data.statusText);
				},
				complete: function (data) {

				}
			});
		},
	}
	briqpayAdmin.init();
});
