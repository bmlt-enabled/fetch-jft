jQuery(document).ready(function($) {
    var disallowedLanguages = ['danish', 'farsi'];

    if (disallowedLanguages.includes($('#jft_language').val())) {
        $('#layout-container').hide();
    }
    $('#jft_language').change(function() {
        if (!disallowedLanguages.includes($(this).val())) {
            $('#layout-container').show();
        } else {
            $('#layout-container').hide();
        }
    });
});