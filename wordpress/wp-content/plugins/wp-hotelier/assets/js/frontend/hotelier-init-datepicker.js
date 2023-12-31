jQuery(function ($) {
	'use strict';
	/* global fecha, datepicker_params, jQuery, HotelDatepicker */
	/* eslint-disable no-unused-vars */

	// datepicker_params is required to continue, ensure the object exists
	if (typeof datepicker_params === 'undefined') {
		return false;
	}

	function get_data() {
		var date_select_inputs = $('.datepicker-input-select');

		if (date_select_inputs.length > 0) {
			var checkin_val = '';
			var checkout_val = '';

			// Use checkin dates retrivied via AJAX
			// Useful for cached pages
			$.ajax({
				url: datepicker_params.htl_ajax_url.toString(),
				type: 'POST',
				success: function (response) {
					if (response) {
						checkin_val = response.checkin.toString();
						checkout_val = response.checkout.toString();

						// Init datepicker
						init_datepicker(checkin_val, checkout_val);
					}
				}
			});
		}
	}

	function init_datepicker(checkin, checkout, trigger) {
		var date_select_inputs = $('.datepicker-input-select');

		if (date_select_inputs.length > 0) {
			date_select_inputs.each(function () {
				var date_select_input = $(this);
				var form = date_select_input.closest('form');
				var checkin_input = form.find('.datepicker-input--checkin');
				var checkout_input = form.find('.datepicker-input--checkout');
				var inline_layout = form.hasClass('datepicker-form--inline') || datepicker_params.inline === '1' ? true : false;
				var clear_button = inline_layout ? true : false;
				var submit_button = inline_layout ? true : false;
				var topbar_position = form.hasClass('datepicker-form--bottom-bar') || datepicker_params.topbar_position === 'bottom' ? 'bottom' : 'top';

				checkin_input.val(checkin);
				checkout_input.val(checkout);

				fecha.setGlobalDateI18n({
					dayNamesShort: datepicker_params.i18n['day-names-short'],
					dayNames: datepicker_params.i18n['day-names'],
					monthNamesShort: datepicker_params.i18n['month-names-short'],
					monthNames: datepicker_params.i18n['month-names']
				});

				var checkin_date = new Date(checkin.replace(/-/g, '\/'));
				var checkout_date = new Date(checkout.replace(/-/g, '\/'));
				var checkin_date_formatted = fecha.format(checkin_date, datepicker_params.datepicker_format);
				var checkout_date_formatted = fecha.format(checkout_date, datepicker_params.datepicker_format);

				date_select_input.val(checkin_date_formatted + ' - ' + checkout_date_formatted);

				var picker = new HotelDatepicker(date_select_input[0], {
					infoFormat: datepicker_params.datepicker_format,
					startOfWeek: datepicker_params.start_of_week,
					startDate: datepicker_params.start_date,
					endDate: datepicker_params.end_date,
					minNights: parseInt(datepicker_params.min_nights, 10),
					maxNights: parseInt(datepicker_params.max_nights, 10),
					disabledDates: datepicker_params.disabled_dates,
					enableCheckout: datepicker_params.enable_checkout,
					disabledDaysOfWeek: datepicker_params.disabled_days_of_week,
					noCheckInDaysOfWeek: datepicker_params.no_checkin_week_days,
					noCheckOutDaysOfWeek: datepicker_params.no_checkout_week_days,
					moveBothMonths: datepicker_params.move_both_months === '1',
					autoClose: datepicker_params.autoclose === '1',
					inline: inline_layout,
					clearButton: clear_button,
					submitButton: clear_button,
					submitButtonName: datepicker_params.submit_button_name,
					topbarPosition: topbar_position,
					i18n: datepicker_params.i18n,
					onSelectRange: function () {
						date_select_input.trigger('hotelier-datepicker-dates-selected');
					},
					getValue: function () {
						if (checkin_input.val() && checkout_input.val()) {
							return checkin_input.val() + ' - ' + checkout_input.val();
						}
						return '';
					},
					setValue: function (s, s1, s2) {
						var checkin_date = new Date(s1.replace(/-/g, '\/'));
						var checkout_date = new Date(s2.replace(/-/g, '\/'));
						var checkin_date_formatted = fecha.format(checkin_date, datepicker_params.datepicker_format);
						var checkout_date_formatted = fecha.format(checkout_date, datepicker_params.datepicker_format);

						date_select_input.val(checkin_date_formatted + ' - ' + checkout_date_formatted);
						checkin_input.val(s1);
						checkout_input.val(s2);
					}
				});

				fix_datepicker_poisiton(picker);
			});
		}
	}

	function fix_datepicker_poisiton(datepicker) {
		var parent_el = $(datepicker.parent);
		var picker_el = $(datepicker.datepicker);
		var picker_el_width = picker_el.outerWidth();
		var window_width = $(window).width();
		var parent_postion = parent_el.offset();

		if (picker_el_width < window_width && parent_postion.left + picker_el_width > window_width) {
			picker_el.css({
				left: 'auto',
				right: '0'
			});
		}
	}

	$(document).ready(function () {
		get_data();
	});

	$(window).on('hotelier_init_datepicker', function (e, checkin, checkout) {
		init_datepicker(checkin, checkout);
	});
});
