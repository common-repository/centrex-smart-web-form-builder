(function ($, centrexApp) {
    const formRenderer = centrexApp.formRenderer = centrexApp.formRenderer || {};
    const utils = centrexApp.utils;

    window.fbControls = window.fbControls || [];
    formRenderer.enableMultiPageForms = function (existingOptions) {
        return existingOptions;
    };

})(jQuery, window.CENTREX_APP = window.CENTREX_APP || {});