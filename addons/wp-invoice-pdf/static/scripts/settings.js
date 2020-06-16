!function($) {
    $.fn.pdf_logo_select = function(options) {
        var file_frame, settings = $.extend({
            url_input: ".url_input",
            image: ".image_input"
        }, options), that = this;
        this.on("click", function(event) {
            return event.preventDefault(), file_frame ? void file_frame.open() : (file_frame = wp.media.frames.file_frame = wp.media({
                title: that.data("uploader_title"),
                button: {
                    text: that.data("uploader_button_text")
                },
                multiple: !1
            }), file_frame.on("select", function() {
                $(settings.url_input).val(file_frame.state().get("selection").first().toJSON().url), 
                $(settings.image).attr("src", file_frame.state().get("selection").first().toJSON().url);
            }), void file_frame.open());
        });
    }, $("#pdf-template-select").on("change", function() {
        var template = $(this).val();
        $("[data-template]").hide(), $('[data-template="' + template + '"]').show();
    }).change();
}(jQuery);