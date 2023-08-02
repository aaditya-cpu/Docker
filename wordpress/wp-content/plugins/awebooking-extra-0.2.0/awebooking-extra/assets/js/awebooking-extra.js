(function($) {
  "use strict";

  var Awebooking_Extra = {
       initSlick: function() {
      $('[data-init="slick"]').each(function () {
        var el = $(this);

        var breakpointsWidth = {xs: 300, sm: 768, md: 992, lg: 1200};

        var slickDefault = {
          // fade: true,
          infinite: true,
          autoplay: true,
          pauseOnHover: true,
          speed: 1000,
          adaptiveHeight: true,

          slidesToShow: 1,
          slidesToScroll: 1,

          mobileFirst: true
        };

        // Merge settings.
        var settings = $.extend(slickDefault, el.data());
        delete settings.init;

        // Build breakpoints.
        if (settings.breakpoints) {
          var _responsive = [];
          var _breakpoints = settings.breakpoints;

          var buildBreakpoints = function (key, show, scroll) {
            if (breakpointsWidth[key] < 992) {
              _responsive.push({
                breakpoint: breakpointsWidth[key],
                settings: {
                  slidesToShow: parseInt(show),
                  slidesToScroll: 1,
                  arrows: false,
                  dots: true
                }
              });
            } else {
              _responsive.push({
                breakpoint: breakpointsWidth[key],
                settings: {
                  slidesToShow: parseInt(show),
                  slidesToScroll: 1
                }
              });
            }
          };

          if (typeof _breakpoints === "object") {
            $.each(_breakpoints, buildBreakpoints);
          }

          delete settings.breakpoints;
          settings.responsive = _responsive;
        };

        el.slick(settings);
      });
    },

    load: function() {
      this.initSlick();
    }
  };

  $(document).ready(function() {
    Awebooking_Extra.load();
  });
})(jQuery);
