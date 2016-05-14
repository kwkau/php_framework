(function (app) {
    app.service("storageService", ["$window", function ($window) {
        return {
            ses_get_json: function (key) {
                return JSON.parse($window.sessionStorage.getItem(key));
            },
            ses_set_json: function (key, value) {
                $window.sessionStorage.setItem(key,JSON.stringify(value));
            },
            ses_get: function (key) {
                return $window.sessionStorage.getItem(key)
            },
            ses_set: function (key, value) {
                $window.sessionStorage.setItem(key,value);
            },
            loc_get: function (key) {
                $window.localStorage.getItem(key)
            },
            loc_set: function (key, value) {
                $window.localStorage.setItem(key,value);
            },
            loc_get_json: function (key) {
                return JSON.parse($window.localStorage.getItem(key));
            },
            loc_set_json: function (key, value) {
                $window.localStorage.setItem(key,JSON.stringify(value));
            },
            ses_del: function (key) {
                $window.sessionStorage.removeItem(key);
            },
            loc_del: function (key) {
                $window.localStorage.removeItem(key);
            },
            ses_clear: function () {
                $window.sessionStorage.clear();
            },
            loc_clear: function () {
                $window.localStorage.clear();
            }
        }
    }]);
})(window.hbcmis);