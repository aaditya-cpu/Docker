(function ($, plugin) {
  'use strict';

  class SyncICalProcess {
    constructor() {
      this.queue = [];
      this.popup = swal.mixin({
        html: '<code></code>',
        width: '650px',
        padding: '1rem',
        customClass: 'icalendar-alertbox',
        allowEscapeKey: false,
        allowOutsideClick: false,
      });
    }

    run(id) {
      if (this.popup.isVisible()) {
        this._createEventSource(id);
        return;
      }

      this.popup({
        title: 'Syncing',
        onOpen: () => {
          this._createEventSource(id);
        }
      });
    }

    _writeLineUpdate(message, level = 'debug') {
      if (!this.popup.isVisible()) {
        return;
      }

      level = level.toLowerCase();
      const code = $(this.popup.getContent()).find('code');

      if (code.length > 0) {
        code.append(`<p class="line-${level}">${message}</p>`);
        code.scrollTop(code[0].scrollHeight);
      }
    }

    _createEventSource(id) {
      const sse = new EventSource(awebooking.route(`/ical/${id}/pull`));
      const closeConnection = () => {
        sse.close();

        if (this.popup.isVisible()) {
          this.popup.hideLoading();
        }
      }

      if ( this.popup.isVisible() ) {
        this.popup.showLoading();
      }

      sse.onerror = () => {
        closeConnection();

        if (this.popup.isVisible()) {
          $(this.popup.getContent()).find('code').hide();
          this.popup.showValidationError('Connection error');
        }
      };

      sse.onmessage = function ( message ) {
        const data = JSON.parse(message.data);

        switch ( data.action ) {
          case 'update':
            break;

          case 'complete':
            closeConnection();
            break;
        }
      };

      // Logging the log.
      sse.addEventListener('log', (message) => {
        const data = JSON.parse(message.data);
        this._writeLineUpdate(data.message, data.level);
      });
    };
  }

  $(function () {
    // Create the processer.
    const process = new SyncICalProcess()

    $('.js-icalendar-sync').on('click', function(e) {
      e.preventDefault();
      process.run($(this).data('room'));
    });

    $('.js-icalendar-export').on('click', function (e) {
      e.preventDefault();

      swal({
        title: 'Export iCalendar',
        input: 'text',
        inputValue: $(this).data('link'),
      });
    });

  });

})(jQuery, window.awebooking);
