jQuery(document).ready(function() {
    jQuery(".sigPad").signaturePad({
        drawOnly: !0,
        lineTop: 120
    }), jQuery("#online_payment_form_wrapper").on("formLoaded", function() {
        jQuery(".sigPad").signaturePad({
            drawOnly: !0,
            lineTop: 120
        });
    });
});