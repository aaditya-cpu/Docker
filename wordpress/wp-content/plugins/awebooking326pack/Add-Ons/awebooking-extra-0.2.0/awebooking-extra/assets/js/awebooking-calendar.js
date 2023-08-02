window.AweBooking = window.AweBooking || {};

;(function($, AweBooking) {
  'use strict';

  /**
   * The AweBooking Calendar.
   *
   * @param {string} element Selector element.
   * @param {object} options Optional for the calendar options.
   */
  var Calendar = function(element, options) {
    this.$el = $(element);

    this.options = $.extend(options, {
      // ...
    });

    // Initialize the calendar.
    this.initialize();
  };

  $.extend(Calendar.prototype, {
    /**
     * Initialize the Calendar.
     *
     * @return {void}
     */
    initialize: function() {
      // Binding this object in "awebooking-calendar" element data.
      this.$el.data('awebooking-calendar', this);

      // Trigger hover handler.
      this.$el.find('.abookingcal__day')
        // .on('mouseover', this.ui.hoverHeadingOver.bind(this))
        // .on('mouseleave', this.ui.hoverHeadingLeave.bind(this));

      this.$el.find('.calendar__prev-month')
        .on('click', this.getCalendar.bind(this));

      this.$el.find('.calendar__next-month')
        .on('click', this.getCalendar.bind(this));
    },

    /**
     * Destroy the Calendar.
     *
     * @return {void}
     */
    destroy: function() {
      this.$el.removeData();
      this.$el.find('.calendar__next-month').off();
      this.$el.find('.calendar__prev-month').off();
    },

    getCalendar: function(e) {
      e.preventDefault();

      var $target = $(e.target);
      var data = {};

      if ($target.hasClass('calendar__prev-month')) {
        data.trigger = 'prev';
      } else if ($target.hasClass('calendar__next-month')) {
        data.trigger = 'next';
      }

      this.ajaxRequest(data, function() {

      });
    },

    ajaxRequest: function(requestData, callback) {
      var self = this;

      var requestData = $.extend(requestData, {
        action: 'awebooking/get_calendar',
        date: this.$el.data('date'),
        room_type: this.$el.data('room-type'),
        options: this.$el.data('options')
      });

      return $.ajax({
        url: window.booking_ajax.ajax_url,
        type: 'POST',
        data: requestData,
      })
      .done(function(response) {
        self.$el.attr('data-date', $(response).data('date'));

        self.destroy();

        /*self.$el.find('.awebookingcal__title').html(
          $(response).find('.awebookingcal__title').html()
        );*/

        self.$el.html( $(response).html() );

        self.initialize();
      })
      .fail(function() {
        // Alert errors.
      })
      .always(function() {
        // Remove ajax loading.
      });
    },
  });

  AweBooking.Calendar = Calendar;

  $(function() {

    new Calendar('.awebookingcal-ajax');

  });

})(jQuery, AweBooking);
