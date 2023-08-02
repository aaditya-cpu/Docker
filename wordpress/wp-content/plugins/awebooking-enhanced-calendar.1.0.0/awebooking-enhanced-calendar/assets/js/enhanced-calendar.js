(function () {
  'use strict';

  var classCallCheck = function (instance, Constructor) {
    if (!(instance instanceof Constructor)) {
      throw new TypeError("Cannot call a class as a function");
    }
  };

  var createClass = function () {
    function defineProperties(target, props) {
      for (var i = 0; i < props.length; i++) {
        var descriptor = props[i];
        descriptor.enumerable = descriptor.enumerable || false;
        descriptor.configurable = true;
        if ("value" in descriptor) descriptor.writable = true;
        Object.defineProperty(target, descriptor.key, descriptor);
      }
    }

    return function (Constructor, protoProps, staticProps) {
      if (protoProps) defineProperties(Constructor.prototype, protoProps);
      if (staticProps) defineProperties(Constructor, staticProps);
      return Constructor;
    };
  }();

  /**
   * AweBooking Availability Calendar.
   *
   * @type {ACalendar}
   */
  var AvailabilityCalendar = function ($) {
    var DEFAULTS = {
      show_month: 1
    };

    function getDateFromElement(element) {
      element = element instanceof jQuery ? element[0] : element;
      return awebooking.utils.dates.format(element.dateObj, 'Y-m-d');
    }

    var ACalendar = function () {
      function ACalendar(element, room, options) {
        classCallCheck(this, ACalendar);

        this.room = room;
        this.element = element;
        this.options = $.extend({}, DEFAULTS, options);
        this.availability = void 0;
        this._xhrRequest = void 0;
      }

      createClass(ACalendar, [{
        key: 'init',
        value: function init() {
          flatpickr(this.element, {
            mode: 'single',
            inline: true,
            static: true,
            showMonths: this.options.show_months || 1,
            minDate: 'today',
            maxDate: this.getMaxDate(),
            onReady: this._getAvailability.bind(this),
            onMonthChange: this._getAvailability.bind(this),
            onDayCreate: this._onFlatpickrDayCreate.bind(this)
          });
        }
      }, {
        key: 'getMaxDate',
        value: function getMaxDate() {
          var d = new Date();
          d.setMonth(d.getMonth() + 6);
          return d;
        }
      }, {
        key: '_getAvailability',
        value: function _getAvailability(_, __, fp) {
          var _this = this;

          if (this._xhrRequest) {
            this._xhrRequest.abort();
          }

          // Create xhr request.
          this._xhrRequest = ACalendar.fetchAvailability(this.room, fp);

          // Fetch the availability
          this._xhrRequest.then(function (data) {
            _this._xhrRequest = void 0;
            _this.availability = data;
            fp.redraw();
          });
        }
      }, {
        key: '_onFlatpickrDayCreate',
        value: function _onFlatpickrDayCreate(_, __, fp, dayElem) {
          if (!this.availability || dayElem.classList.contains('disabled')) {
            return;
          }

          var dateIndex = getDateFromElement(dayElem);
          if (!this.availability.hasOwnProperty(dateIndex)) {
            return;
          }

          var rooms = this.availability[dateIndex];
          $(dayElem).addClass(rooms === 0 ? 'unavailable disabled' : 'available');
        }
      }]);
      return ACalendar;
    }();

    /**
     * Fetch the availability calendar.
     *
     * @param calendar
     * @param fp
     * @returns {JQuery.jqXHR}
     */


    ACalendar.fetchAvailability = function (calendar, fp) {
      var args = {};

      if (fp.config.showMonths === 1) {
        args = { month: fp.currentMonth + 1, year: fp.currentYear };
      } else {
        var $days = $(fp.daysContainer).find('.flatpickr-day');
        args.start_date = getDateFromElement($days.first());
        args.end_date = getDateFromElement($days.last());
      }

      return $.ajax({
        url: awebooking.route('/calendar/availability/' + calendar),
        data: args,
        method: 'GET',
        dataType: 'json'
      });
    };

    // Fire on document loaded.
    $(function () {
      $('[data-init="availability-calendar"]').each(function (index, element) {
        var room = $(element).data('room');
        var options = $(element).data('settings');

        var calendar = new ACalendar(element, room, options);
        calendar.init();

        $(element).data('a-calendar', calendar);
      });
    });

    return ACalendar;
  }(jQuery);

  // Share the object into the awebooking instances.
  awebooking.instances.AvailabilityCalendar = AvailabilityCalendar;

}());

//# sourceMappingURL=enhanced-calendar.js.map
