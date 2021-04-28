jQuery(function ($) {
	var briqpayAdmin = {
		wrapperElement: $('#briqpay_rules_result_wrapper'),
		buttonElement: $('#briqpay_show_rules'),

		toggleRules: function() {
			briqpayAdmin.wrapperElement.slideToggle( "slow" );
		},

		init: function () {
			$('body').on('click', '#briqpay_show_rules', briqpayAdmin.toggleRules );
		}
	}
	briqpayAdmin.init();
});
