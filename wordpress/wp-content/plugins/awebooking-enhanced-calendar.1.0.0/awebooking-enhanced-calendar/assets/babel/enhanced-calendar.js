/**
 * AweBooking Availability Calendar.
 *
 * @type {ACalendar}
 */
const AvailabilityCalendar = (function($) {
  const DEFAULTS = {
    show_month: 1,
  }

  function getDateFromElement (element) {
    element = element instanceof jQuery ? element[0] : element
    return awebooking.utils.dates.format(element.dateObj, 'Y-m-d')
  }

  class ACalendar {
    constructor (element, room, options) {
      this.room    = room
      this.element = element
      this.options = $.extend({}, DEFAULTS, options)
      this.availability = void 0
      this._xhrRequest  = void 0
    }

    init() {
      flatpickr(this.element, {
        mode: 'single',
        inline: true,
        static: true,
        showMonths: this.options.show_months || 1,
        minDate: 'today',
        maxDate: this.getMaxDate(),
        onReady: this._getAvailability.bind(this),
        onMonthChange: this._getAvailability.bind(this),
        onDayCreate: this._onFlatpickrDayCreate.bind(this),
      })
    }

    getMaxDate() {
      let d = new Date
      d.setMonth(d.getMonth() + 6)
      return d
    }

    _getAvailability(_, __, fp) {
      if (this._xhrRequest) {
        this._xhrRequest.abort()
      }

      // Create xhr request.
      this._xhrRequest = ACalendar.fetchAvailability(this.room, fp)

      // Fetch the availability
      this._xhrRequest.then((data) => {
        this._xhrRequest  = void 0
        this.availability = data;
        fp.redraw()
      })
    }

    _onFlatpickrDayCreate(_, __, fp, dayElem) {
      if (!this.availability || dayElem.classList.contains('disabled')) {
        return
      }

      const dateIndex = getDateFromElement(dayElem)
      if (!this.availability.hasOwnProperty(dateIndex)) {
        return
      }

      const rooms = this.availability[dateIndex];
      $(dayElem).addClass(rooms === 0 ? 'unavailable disabled' : 'available')
    }
  }

  /**
   * Fetch the availability calendar.
   *
   * @param calendar
   * @param fp
   * @returns {JQuery.jqXHR}
   */
  ACalendar.fetchAvailability = function (calendar, fp) {
    let args = {}

    if (fp.config.showMonths === 1) {
      args = {month: fp.currentMonth + 1, year: fp.currentYear}
    } else {
      const $days = $(fp.daysContainer).find('.flatpickr-day')
      args.start_date = getDateFromElement($days.first())
      args.end_date = getDateFromElement($days.last())
    }

    return $.ajax({
      url: awebooking.route(`/calendar/availability/${calendar}`),
      data: args,
      method: 'GET',
      dataType: 'json',
    })
  }

  // Fire on document loaded.
  $(function () {
    $('[data-init="availability-calendar"]').each(function (index, element) {
      const room = $(element).data('room')
      const options = $(element).data('settings')

      const calendar = new ACalendar(element, room, options)
      calendar.init()

      $(element).data('a-calendar', calendar)
    })
  })

  return ACalendar
})(jQuery)

// Share the object into the awebooking instances.
awebooking.instances.AvailabilityCalendar = AvailabilityCalendar
