// Call any queued functions and immediately call any new functions coming in:
(function ($, centrexApp) {
    centrexApp.callbackQueue = centrexApp.callbackQueue || [];
    centrexApp.callbackQueue.forEach(function (callback) {
        callback(centrexApp);
    });
    centrexApp.callbackQueue.push = function (callback) {
        callback(centrexApp);
    };
})(jQuery, (window.CENTREX_APP = window.CENTREX_APP || {}));
