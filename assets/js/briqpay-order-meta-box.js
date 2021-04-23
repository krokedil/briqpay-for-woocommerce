const init = function() {
	const wrapper = document.getElementById('briqpay_rules_result_wrapper');
	const showRules = document.getElementById('briqpay_show_rules');
	if ( showRules ) {
		showRules.addEventListener('click', (showRules) => {
			wrapper.classList.add('briqpay_show_rules');
			wrapper.classList.remove('briqpay_hide_rules');
		});
	}
	const closeRules = document.getElementById('briqpay_close_rules');
	if ( closeRules ) {
		closeRules.addEventListener('click', (closeRules) => {
			wrapper.classList.remove('briqpay_show_rules');
			wrapper.classList.add('briqpay_hide_rules');
		});
	}
}

document.addEventListener('DOMContentLoaded', init, false);