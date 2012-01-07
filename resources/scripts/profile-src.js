/*jslint browser: true, sloppy: true, maxerr: 50, indent: 4 */
(function () {
    var device = {
        profile : {},
        features: {
            /* This is a template string replaced by the profile.js.php generator.
             * It is wrapped in a comment block so this file remains valid JS
             * syntax andpasses JSLint.
             */
            /*[FEATURE_DETECTION]*/
        },
        get: function (name) {
            var c, i,
                nameEQ = name + "=",
                ca = document.cookie.split(';');
            for (i = 0; i < ca.length; i += 1) {
                c = ca[i];
                while (c.charAt(0) === ' ') {
                    c = c.substring(1, c.length);
                }
                if (c.indexOf(nameEQ) === 0) {
                    return c.substring(nameEQ.length, c.length);
                }
            }
            return null;
        },
        set: function (name, value, days) {
            var date, expires;
            if (days) {
                date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = ";expires=" + date.toGMTString();
            } else {
                expires = "";
            }
            document.cookie = name + "=" + value + expires + ";path=/";
        },
        clear: function (name) {
            this.set(name, "", -1);
        },
        update: function (init) {
            var feature,
                data = "%7B";
            /* we don't need (or want) to pass the ua through the cookie */
            delete this.profile.ua;
            /* add features to profile based on defined feature tests */
            for (feature in this.features) {
                if (this.features.hasOwnProperty(feature)) {
                    this.profile[feature] = this.features[feature]();
                    if (this.profile[feature] === 0) {
                        delete this.profile.feature;
                    }
                }
            }
            /* assemble profile object as string for cookie */
            for (feature in this.profile) {
                if (this.profile.hasOwnProperty(feature)) {
                    data += "%22" + feature + "%22:%22" + this.profile[feature] + "%22%2C";
                }
            }
            data = data.substring(0, data.length - 3);
            data += "%7D";
            /* write profile object as string to cookie */
            this.set('profile', data, 30);
            /* this doesn't need to be the in cookie, and is merely a convenience */
            this.profile.ua = navigator.userAgent;
        },
        init : function () {
            /* read profile object from cookie */
            var update, prof, feature,
                data = unescape(unescape(this.get('profile')));
            if (this.features.json) {
                prof = eval('(' + data + ')');
            } else {
                prof = JSON.parse(data);
            }
            /* copy features from cookie to this.profile */
            for (feature in prof) {
                if (prof.hasOwnProperty(feature)) {
                    this.profile[feature] = prof[feature];
                }
            }
            window.device = this;
            update = (function () {
                var timer,
                    delay = 100;
                return function () {
                    if (timer) {
                        clearTimeout(timer);
                    }
                    timer = setTimeout(function () {
                        window.device.update();
                        clearTimeout(timer);
                    }, delay);
                };
            }());
            if (window.addEventListener) {
                window.addEventListener('resize', update, false);
            } else {
                window.attachEvent("onresize", update);
            }
            this.update(true);
        }
    };
    device.init();
}());
