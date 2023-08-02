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

  var PhotoGallery = function ($) {
    var Defaults = {
      index: 0,
      shareEl: false,
      history: false,
      tapToClose: false,
      clickToClose: false,
      pinchToClose: false,
      closeOnScroll: false,
      closeOnVerticalDrag: false,
      showHideOpacity: false,
      hideAnimationDuration: 0,
      showAnimationDuration: 0,
      bgOpacity: 0.95,

      showThumbs: true
    };

    var Gallery = function () {
      function Gallery(element, items, options) {
        var _this = this;

        classCallCheck(this, Gallery);

        options = $.extend({}, Defaults, options);

        var pswpElement = document.querySelector('.pswp.pswp--awebooking');
        var asideElement = pswpElement.querySelector('.pswp__aside');
        var thumbsElement = pswpElement.querySelector('.pswp__thumbs');

        $(element).on('click', function (e) {
          e.preventDefault();

          var gallery = new PhotoSwipe(_this.getMarkupElement(), window.PhotoSwipeUI_Default, items, options);

          if (asideElement) {
            asideElement.innerHTML = ''; // TODO: ...
          }

          if (options.showThumbs) {
            thumbsElement.innerHTML = '<ul class="thumbnail-list"></ul>';

            $.each(items, function (index, item) {
              thumbsElement.querySelector('ul').innerHTML += '<li class="thumbnail-wrapper"><img src="' + item.src + '"></li>';
            });

            $(thumbsElement).on('click', 'img', function (e) {
              e.preventDefault();
              e.stopPropagation();

              gallery.goTo($(this).parent().index());
            });

            gallery.listen('close', function () {
              if (asideElement) {
                asideElement.innerHTML = '';
              }

              thumbsElement.innerHTML = '';
              $(thumbsElement).off('click', 'img');
            });

            gallery.listen('afterChange', function () {
              $(thumbsElement).find('li.active').removeClass('active');
              $(thumbsElement).find('li').eq(gallery.getCurrentIndex()).addClass('active');
            });
          }

          gallery.init();
        });
      }

      createClass(Gallery, [{
        key: 'getMarkupElement',
        value: function getMarkupElement() {
          return document.querySelectorAll('.pswp.pswp--awebooking')[0];
        }
      }]);
      return Gallery;
    }();

    // Fire on document ready.


    $(function () {
      var setting = window._awebookingGallery;

      if (setting.displayOnSearch) {
        $('#search-rooms-results .roommaster-info__image').each(function (index, element) {
          var items = $(element).find('[data-gallery]').data('gallery') || [];

          if (!items || items.length === 0) {
            return;
          }

          $(element).data('awebooking-photo-gallery', new Gallery(element, items));
        });
      }

      if (setting.displayOnSingle) {
        $('.room-gallery-section .room-gallery__item').each(function (index, element) {
          var items = $(element).closest('.room-gallery').data('gallery') || [];

          var gallery = new Gallery(element, items, {
            index: index,
            showThumbs: false,
            hideAnimationDuration: 333,
            showAnimationDuration: 333,
            getThumbBoundsFn: function getThumbBoundsFn(index) {
              var thumbnail = element.getElementsByTagName('img')[0],
                  pageYScroll = window.pageYOffset || document.documentElement.scrollTop,
                  rect = thumbnail.getBoundingClientRect();

              return { x: rect.left, y: rect.top + pageYScroll, w: rect.width };
            }
          });
        });
      }
    });

    return Gallery;
  }(jQuery);

  // Share the object into the awebooking instances.
  awebooking.instances.PhotoGallery = PhotoGallery;

}());

//# sourceMappingURL=photo-gallery.js.map
