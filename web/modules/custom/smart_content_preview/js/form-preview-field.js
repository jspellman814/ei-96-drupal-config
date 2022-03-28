/**
 * @file
 * Handle preview functionality on smart segment form.
 */
 (function ($, Drupal) {
  Drupal.behaviors.previewFieldBehavior = {
    attach: function (context, settings) {
      // Segment elements.
      var segments = $('.smart-content-segment-set-edit-form .smart-segments-preview');

      segments.each(function() {
        // Add click once for each segment.
        $(this).once('previewFieldBehavior').on('click', function() {
          // If checked, uncheck all the other checkboxes.
          if (this.checked) {
            segments.not(this).prop('checked', false);
          }
        });
      });
    }
  };

})(jQuery, Drupal);