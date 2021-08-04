define(['jquery', 'app/container', 'moment', 'toastr', 'bootbox', 'helpers/base64', 'can/view/stache'], function ($, ioc, moment, toastr, bootbox, base64) {
    console.log('App Helper Loaded');
    $.fn.serializeObject = function () {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function () {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };
    var relationship = {
        "01": "Spouse",
        "02": "Mother",
        "03": "Father",
        "04": "Friend",
        "05": "Emergency Contact",
        "06": "POA",
        "18": "Self",
        "19": "Child",
        "20": "Employee",
        "21": "Unknown",
        "39": "Organ donor",
        "40": "Cadaver donor",
        "53": "Life partner",
        "G8": "Other"
    };

    function pad(str, max) {
        return str.length < max ? pad("0" + str, max) : str;
    }

    function capitalize(str) {
        return str.replace(/\w\S*/g, function (txt) {
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        });
    }

    function isFloat(n) {
        return n === +n && n !== (n | 0);
    }

    function isInteger(n) {
        return n === +n && n === (n | 0);
    }
    can.stache.registerHelper('relationship', function (rid) {
        var id = rid();
        if (typeof relationship[id] == 'undefined') {
            return id;
        }
        return relationship[id];
    });
    can.stache.registerHelper('hasAccess', function (options) {
        if (ioc.app.hasAccess()) {
            return options.fn(this);
        }
        return options.inverse(this);
    });
    can.stache.registerHelper('phone', function (value) {
        var obj = value();
        if (!obj) return;
        var numbers = obj.replace(/\D/g, ''),
            char = {
                0: '(',
                3: ') ',
                6: ' - '
            };
        obj = '';
        for (var i = 0; i < numbers.length; i++) {
            obj += (char[i] || '') + numbers[i];
        }
        return obj;
    });
    // 09/22/2015
    can.stache.registerHelper('dateFormat1', function (value) {
        return moment(value(), 'YYYY-MM-DD').format('MM/DD/YYYY');
    });
    // 09/22/2015 12:00 am
    can.stache.registerHelper('dateFormat2', function (value) {
        if (value().indexOf(' ') < 0) {
            return moment(value(), 'YYYY-MM-DD').format('MM/DD/YYYY');
        }
        return moment(value(), 'YYYY-MM-DD HH:mm:ss').format('MM/DD/YYYY h:mm a');
    });

    // 12:00 am
    can.stache.registerHelper('timeFormat1', function (value) {
        return moment(value(), 'YYYY-MM-DD HH:mm:ss').format('h:mm a');
    });

    // September 22, 2015
    can.stache.registerHelper('dateFormat3', function (value) {
        return moment(value(), 'YYYY-MM-DD HH:mm:ss').format('MMMM DD, YYYY');
    });
    // 2015-09-04T05:15:00Z
    can.stache.registerHelper('dateFormat4', function (value) {
        return moment(value()).format('MMMM DD, YYYY');
    });
    // Sept 22, 2015
    can.stache.registerHelper('dateFormat5', function (value) {
        return moment(value(), 'YYYY-MM-DD HH:mm:ss').format('MMM DD, YYYY');
    });
    // TODO: Need to capitalize every word
    can.stache.registerHelper('capitalize', function (value) {
        if (!value()) return;
        return capitalize(value().toLowerCase());
    });
    can.stache.registerHelper('round', function (value) {
        var val = value();
        if (parseInt(val) === val) {
            return val;
        }
        return val.toFixed(1);
    });
    can.stache.registerHelper('activeRoute', function (options) {
        console.log(options);
    });
    can.stache.registerHelper('formatExpiryMonth', function (date, options) {
        if (!date()) {
            return '';
        }
        var expiry = date().split('/');
        return expiry[0];
    });
    can.stache.registerHelper('formatExpiryYear', function (date, options) {
        if (!date()) {
            return '';
        }
        var expiry = date().split('/');
        return (new Date).getFullYear().toString().substring(0, 2) + expiry[1];
    });
    can.stache.registerHelper('nl2br', function (str) {
        var breakTag = '<br/>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    });
    can.stache.registerHelper('getPhotoUrl', function (params, params2, options) {
        if (typeof options !== 'undefined') {
            if (!params()) {
                return '';
            }
            return '/assets/' + params() + '/' + params2();
        }
        if (typeof params == 'function') {
            var params = params();
            return '/assets/' + params.PhotoId + '/' + params.AgencyId;
        }
        return '/assets/' + params.PhotoId + '/' + params.AgencyId;
    });
    can.stache.registerHelper('validPhotoAsset', function (value, options) {
        return (value() === '00000000-0000-0000-0000-000000000000' || !value()) ? options.inverse(this) : options.fn(this);
    });
    can.stache.registerHelper('moment', function (value, format) {
        return moment(value()).format(format);
    });
    can.stache.registerHelper('ifStatus', function (value, checkAgainst, options) {
        if (value() == checkAgainst) {
            return options.fn(this);
        }
        return options.inverse(this);
    });
    can.stache.registerHelper('pad', function (value, max) {
        return pad(value() + "", parseInt(max));
    });
    can.stache.registerHelper('lesser', function (value, n, options) {
        if (value() < parseInt(n)) {
            return options.fn(this);
        }
        return options.inverse(this);
    });
    can.stache.registerHelper('wizardBoxStatus', function (value, n) {
        var currentStep = value();
        var step = parseInt(n);
        if (currentStep < step) {
            return 'disabled';
        } else if (currentStep > step) {
            return 'complete'
        }
        return 'active';
    });
    can.stache.registerHelper('currentRoute', function (options) {
        var hash = options.hash;
        if (hash.controller == can.route.attr('controller')) {
            return options.fn(this);
        }
        return options.inverse(this);
    });
    return ioc.Helper = {
        Base64: base64,
        capitalize: capitalize,
        handleErrors: function (res) {
            if (typeof res.status !== 'undefined' && typeof res.message !== 'undefined') {
                return toastr.error(res.message);
            } else if (typeof res.status !== 'undefined' && typeof res.data !== 'undefined') {
                return this.handleErrors(res.data);
            }
            if (res) {
                $('.has-error').removeClass('has-error');
                for (var i in res) {
                    toastr.error(res[i][0]);
                    $('[name=' + i + ']').parent().addClass('has-error');
                }
                return false;
            }
            return true;
        },
        iconLoader: function (element, options) {
            if (element.hasClass('loading')) {
                return false;
            }
            var fn = this,
                defaultsOptions = {
                    loaderClasses: 'fa fa-circle-o-notch fa-spin loading',
                    savedClasses: '',
                    timeout: 5000,
                    delay: 500
                }
            var options = $.extend(defaultsOptions, options);
            fn.element = element;
            fn.start = function () {
                options.savedClasses = fn.element.attr('class');
                fn.element.attr('class', options.loaderClasses);
                fn.timer = setTimeout(function () {
                    fn.stop();
                }, options.timeout)
            }
            fn.stop = function () {
                setTimeout(function () {
                    fn.element.attr('class', options.savedClasses);
                    fn.timer = null;
                }, options.delay);
            }
            return fn;
        },
        callPrint: function (iframeId) {
            var PDF = document.getElementById(iframeId);
            PDF.focus();
            PDF.contentWindow.print();
        },
        download: function (url, callback) {
            var fn = this;
            fn.iframeId = 'iframeprint';
            fn.element = $('iframe#' + fn.iframeId);
            if (!fn.element.length) {
                fn.element = $('<iframe>');
                fn.element.attr('id', fn.iframeId);
                $('body').append(fn.element);
            }
            // Reset iframe source
            if (!fn.element.attr('src') || fn.element.attr('src') == 'about:blank') {
                fn.element.attr('src', 'about:blank');
            }
            // New source delay
            setTimeout(function () {
                fn.element.attr('src', url);
            }, 500);
            fn.element.load(function () {
                if (fn.element.attr('src') != 'about:blank') {
                    if (typeof callback === 'function') {
                        callback.apply(fn);
                    }
                }
            });
        },
        Modal: bootbox
    }
});
