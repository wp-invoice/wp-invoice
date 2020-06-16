(function ($) {
  $.fn.pdf_logo_select = function (options) {

    var settings = $.extend({
      url_input: '.url_input',
      image: '.image_input'
    }, options );

    var file_frame;
    var that = this;

    this.on('click', function (event) {

      event.preventDefault();

      // If the media frame already exists, reopen it.
      if (file_frame) {
        file_frame.open();
        return;
      }

      // Create the media frame.
      file_frame = wp.media.frames.file_frame = wp.media({
        title: that.data('uploader_title'),
        button: {
          text: that.data('uploader_button_text')
        },
        multiple: false  // Set to true to allow multiple files to be selected
      });

      // When an image is selected, run a callback.
      file_frame.on('select', function () {
        $(settings.url_input).val(file_frame.state().get('selection').first().toJSON().url);
        $(settings.image).attr('src', file_frame.state().get('selection').first().toJSON().url);
      });

      // Finally, open the modal
      file_frame.open();
    });
  };

  $('#pdf-template-select').on('change', function(){
    var template = $(this).val();
    $('[data-template]').hide();
    $('[data-template="'+template+'"]').show();
  }).change();
}(jQuery));