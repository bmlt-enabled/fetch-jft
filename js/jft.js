jQuery(document).ready(function($) {
    const jftLanguageSelect = $('#jft_language');
    const timezoneContainer = $('#timezone-container');
    const layoutContainer = $('#layout-container');

    function updateEnglishOnlyOptions() {
        let isEnglish = jftLanguageSelect.val() === 'english';

        if (isEnglish) {
            timezoneContainer.show();
            layoutContainer.show();
        } else {
            timezoneContainer.hide();
            layoutContainer.hide();
        }
    }

    // Initial update
    updateEnglishOnlyOptions();

    // Listen for changes
    jftLanguageSelect.on('change', updateEnglishOnlyOptions);
});
