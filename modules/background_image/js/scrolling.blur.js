/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function (domready, debounce, drupalSettings, RuntimeCss, $) {
  if (!drupalSettings.backgroundImage) {
    return;
  }

  var settings = drupalSettings.backgroundImage || {};
  if (!settings.baseClass) {
    settings.baseClass = 'background-image';
  }

  var trigger = function trigger(element, name) {
    var data = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;

    var event = void 0;
    data = data || {};
    if (window.CustomEvent) {
      event = new CustomEvent(name, {
        bubbles: true,
        cancelable: true,
        detail: data
      });
    } else {
      event = document.createEvent('CustomEvent');
      event.initCustomEvent(name, true, true, data);
    }

    element.dispatchEvent(event);
  };

  var scrollTop = RuntimeCss.getScrollTop();
  domready(function () {
    var wrapper = document.querySelector('.' + settings.baseClass + '-wrapper');
    var image = wrapper && wrapper.querySelector('.' + settings.baseClass);

    if (!image) {
      return;
    }

    var css = new RuntimeCss(settings.baseClass);

    var updateOffset = function updateOffset() {
      if (settings.fullViewport) {
        css.add(wrapper, { marginTop: '-' + RuntimeCss.getOffset().top + 'px' });
      }
    };
    updateOffset();

    var doBlur = function doBlur() {
      updateOffset();

      if (!(parseInt(settings.blur.type, 10) === 1 || parseInt(settings.blur.type, 10) === 2 && settings.fullViewport)) {
        css.add(image, { prefixFilter: '' });
        return;
      }

      var max = parseInt(settings.blur.radius, 10) || 50;
      var speed = (parseInt(settings.blur.speed, 10) || 1) / 10;

      var blur = scrollTop * speed;
      if (blur > max) {
        blur = max;
      }
      css.add(image, { prefixFilter: 'blur(' + blur + 'px)' });

      var overlay = image.querySelector('.' + settings.baseClass + '-overlay');
      if (overlay) {
        var opacity = 0.25 + blur / 100;
        if (opacity > 1) {
          opacity = 1;
        }
        css.add(overlay, { prefixOpacity: opacity });
      }

      trigger(image, 'blur.background_image', settings);
    };

    var draw = debounce(function () {
      scrollTop = RuntimeCss.getScrollTop();
      RuntimeCss.raf(doBlur);
    }, 10);

    window.addEventListener('scroll', draw);
    window.addEventListener('resize', draw);
    window.addEventListener('touchmove', draw);

    if ($) {
      $(document).on('drupalViewportOffsetChange', draw).on('drupalToolbarOrientationChange', draw).on('drupalToolbarTabChange', draw).on('drupalToolbarTrayChange', draw);
    }

    draw();
  });
})(domready, Drupal.debounce, drupalSettings, window.RuntimeCss, jQuery);