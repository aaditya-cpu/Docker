const PhotoGallery = function($) {
  const Defaults = {
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

    showThumbs: true,
  }

  class Gallery {
    constructor(element, items, options) {
      options = $.extend({}, Defaults, options)

      const pswpElement = document.querySelector('.pswp.pswp--awebooking')
      const asideElement = pswpElement.querySelector('.pswp__aside')
      const thumbsElement = pswpElement.querySelector('.pswp__thumbs')

      $(element).on('click', (e) => {
        e.preventDefault()

        const gallery = new PhotoSwipe(this.getMarkupElement(), window.PhotoSwipeUI_Default, items, options)

        if (asideElement) {
          asideElement.innerHTML = ''; // TODO: ...
        }

        if (options.showThumbs) {
          thumbsElement.innerHTML = '<ul class="thumbnail-list"></ul>'

          $.each(items, function (index, item) {
            thumbsElement.querySelector('ul').innerHTML += `<li class="thumbnail-wrapper"><img src="${item.src}"></li>`;
          })

          $(thumbsElement).on('click', 'img', function (e) {
            e.preventDefault();
            e.stopPropagation();

            gallery.goTo($(this).parent().index())
          })

          gallery.listen('close', () => {
            if (asideElement) {
              asideElement.innerHTML = ''
            }

            thumbsElement.innerHTML = ''
            $(thumbsElement).off('click', 'img');
          })

          gallery.listen('afterChange', () => {
            $(thumbsElement).find('li.active').removeClass('active');
            $(thumbsElement).find('li').eq(gallery.getCurrentIndex()).addClass('active');
          })
        }

        gallery.init()
      })
    }

    getMarkupElement() {
      return document.querySelectorAll('.pswp.pswp--awebooking')[0]
    }
  }

  // Fire on document ready.
  $(function () {
    const setting = window._awebookingGallery

    if (setting.displayOnSearch) {
      $('#search-rooms-results .roommaster-info__image').each((index, element) => {
        let items = $(element).find('[data-gallery]').data('gallery') || []

        if (!items || items.length === 0) {
          return
        }

        $(element).data('awebooking-photo-gallery', new Gallery(element, items))
      })
    }

    if (setting.displayOnSingle) {
      $('.room-gallery-section .room-gallery__item').each((index, element) => {
        let items = $(element).closest('.room-gallery').data('gallery') || []

        const gallery = new Gallery(element, items, {
          index: index,
          showThumbs: false,
          hideAnimationDuration: 333,
          showAnimationDuration: 333,
          getThumbBoundsFn: function(index) {
            const thumbnail = element.getElementsByTagName('img')[0],
              pageYScroll = window.pageYOffset || document.documentElement.scrollTop,
              rect = thumbnail.getBoundingClientRect();

            return { x: rect.left, y: rect.top + pageYScroll, w: rect.width }
          }
        })
      })
    }
  })

  return Gallery
}(jQuery);

// Share the object into the awebooking instances.
awebooking.instances.PhotoGallery = PhotoGallery
