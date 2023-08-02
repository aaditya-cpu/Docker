(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

(function ($, plugin) {
  'use strict';

  var SyncICalProcess = function () {
    function SyncICalProcess() {
      _classCallCheck(this, SyncICalProcess);

      this.queue = [];
      this.popup = swal.mixin({
        html: '<code></code>',
        width: '650px',
        padding: '1rem',
        customClass: 'icalendar-alertbox',
        allowEscapeKey: false,
        allowOutsideClick: false
      });
    }

    _createClass(SyncICalProcess, [{
      key: 'run',
      value: function run(id) {
        var _this = this;

        if (this.popup.isVisible()) {
          this._createEventSource(id);
          return;
        }

        this.popup({
          title: 'Syncing',
          onOpen: function onOpen() {
            _this._createEventSource(id);
          }
        });
      }
    }, {
      key: '_writeLineUpdate',
      value: function _writeLineUpdate(message) {
        var level = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'debug';

        if (!this.popup.isVisible()) {
          return;
        }

        level = level.toLowerCase();
        var code = $(this.popup.getContent()).find('code');

        if (code.length > 0) {
          code.append('<p class="line-' + level + '">' + message + '</p>');
          code.scrollTop(code[0].scrollHeight);
        }
      }
    }, {
      key: '_createEventSource',
      value: function _createEventSource(id) {
        var _this2 = this;

        var sse = new EventSource(awebooking.route('/ical/' + id + '/pull'));
        var closeConnection = function closeConnection() {
          sse.close();

          if (_this2.popup.isVisible()) {
            _this2.popup.hideLoading();
          }
        };

        if (this.popup.isVisible()) {
          this.popup.showLoading();
        }

        sse.onerror = function () {
          closeConnection();

          if (_this2.popup.isVisible()) {
            $(_this2.popup.getContent()).find('code').hide();
            _this2.popup.showValidationError('Connection error');
          }
        };

        sse.onmessage = function (message) {
          var data = JSON.parse(message.data);

          switch (data.action) {
            case 'update':
              break;

            case 'complete':
              closeConnection();
              break;
          }
        };

        // Logging the log.
        sse.addEventListener('log', function (message) {
          var data = JSON.parse(message.data);
          _this2._writeLineUpdate(data.message, data.level);
        });
      }
    }]);

    return SyncICalProcess;
  }();

  $(function () {
    // Create the processer.
    var process = new SyncICalProcess();

    $('.js-icalendar-sync').on('click', function (e) {
      e.preventDefault();
      process.run($(this).data('room'));
    });

    $('.js-icalendar-export').on('click', function (e) {
      e.preventDefault();

      swal({
        title: 'Export iCalendar',
        input: 'text',
        inputValue: $(this).data('link')
      });
    });
  });
})(jQuery, window.awebooking);

},{}]},{},[1]);

//# sourceMappingURL=icalendar.js.map
