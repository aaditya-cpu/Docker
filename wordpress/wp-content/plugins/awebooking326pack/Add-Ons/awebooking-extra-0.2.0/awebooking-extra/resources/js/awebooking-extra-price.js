const AweBooking = window.TheAweBooking;

AweBooking.Vue.component('extra-price', require('./App.vue'));

jQuery(function($) {
	new AweBooking.Vue({
		el: '#awebooking-extra-price'
	});
});
