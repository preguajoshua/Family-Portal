define([
    'app/container',
    'components',
    'can/view/stache',
    'app/controller',
    'app/config',
    'app/request',
    'app/helper',
    'bootstrap',
    'can/control/route',
    'can/route/pushstate',
    'pace',
], function(ioc, components) {
    can.view.ext = '.stache';

    // Pace.start({
    //     startOnPageLoad: false
    // //     ajax: false, // disabled
    // //     document: false, // disabled
    // //     eventLag: false, // disabled
    // //     elements: {
    // //         selectors: ['.content']
    // //     }
    // });

    ioc.app = new(can.Control({
        init: function(emptyEl, options) {
            if (this.isMobile()) {
                $('body').addClass('is-mobile');
            }

            this.flushSession();
            this.boot();
        },
        boot: function() {
            var promise = $.get('/front/session').success(can.proxy(function(data) {
                ioc.attr(data);
                ioc.attr('guest', false);
                if (data.client) {
                    this.render();
                }

                this.setCSRFToken(ioc.attr('token'));

                can.route.replaceStateOff('filter');
                can.route.ready();
            }, this)).error(can.proxy(function(httpObj, textStatus) {
                ioc.attr('token', httpObj.responseJSON.token);

                this.setCSRFToken(ioc.attr('token'));

                can.route.ready();
                can.route.attr({
                    controller: 'login'
                }, true);
            }, this));

            return promise;
        },
        flushSession: function() {
            ioc.attr('guest', true);
            ioc.attr('clients', {});
            ioc.attr('provider', {});
            ioc.attr('user', {});
            ioc.attr('token', '');
        },
        isGuest: function() {
            return ioc.attr('guest');
        },
        hasAccess: function() {
            if (ioc.provider.attr('Application') == 1) {
                return false;
            }

            if (ioc.client.attr('isPayor') == 1) {
                return true;
            }

            return false;
        },
        hasAccount: function() {
            if (ioc.provider.attr('Application') == 1) {
                return false;
            }

            if (ioc.client.attr('isAgencyBankAccountSetup') == 1) {
                return true;
            }

            return false;
        },
        setCSRFToken: function(token) {
            $.ajaxSetup({
                statusCode: {
                    401: can.proxy(function(xhr) {
                        if (xhr.responseJSON.message == 'Invalid Token') {
                            return location.reload();
                        }
                        this.flushSession();

                        can.route.attr({
                            controller: ioc.Config.redirectLogin
                        }, true);
                    }, this)
                },
                headers: {
                    'X-CSRF-TOKEN': token
                }
            });
        },
        Request: function(data) {
            this.data = data;

            this.getParam = function(key) {
                return typeof this.data[key] ? this.data[key] : null;
            }
        },
        'route': function(data) {
            this.handleRoute(data);
        },
        '/:controller route': function(data) {
            this.handleRoute(data);
        },
        '/:controller/:action route': function(data) {
            this.handleRoute(data);
        },
        '/:controller/:action/:param1 route': function(data) {
            this.handleRoute(data);
        },
        '{ready} change': function() {
            this.hideLoader();
        },
        hideLoader: function() {
            var def = new can.Deferred();

            $(document).ready(function() {
                setTimeout(function() {
                    $('#preloader').fadeOut(500, function() {
                        $(this).hide();
                        $('body').addClass('loaded');

                        def.resolve();
                    });
                }, 500);
            });

            return def;
        },
        showLoader: function(options) {
            var def = new can.Deferred(),
                defaultOptions = {
                    opacity: 1
                };

            options = $.extend({}, defaultOptions, options);

            $('#preloader').css('opacity', (typeof options.opacity !== 'undefined' ? options.opacity : 1)).fadeIn(100, function() {
                $(this).show();

                $('body').removeClass('loaded');
                setTimeout(function() {
                    def.resolve();
                }, 500);
            });

            return def;
        },
        render: function() {
            if (ioc.render()) {
                return;
            }

            $('#wrapper').html(can.view('wrapper-content'));
            // new components.Sidebar('#sidebar', ioc);
            // new components.Navbar('#navbar', ioc);

            ioc.render(true);
        },
        redirectPath: function(name) {
            if (this.isGuest() && (name != 'login' && name != 'switch')) {
                if (ioc.client === null) {
                    return ioc.Config.redirectSwitch;
                }
                return ioc.Config.redirectLogin;
            } else if (!this.isGuest() && name != 'switch') {
                if (ioc.client === null) {
                    return ioc.Config.redirectSwitch;
                }
            }
            return false;
        },
        handleMultiClients: function() {
            $('#wrapper').before(can.stache('<multiclients id="multiclients"></multiclients>'));
        },
        middleware: function(controllerName) {
            var def = can.Deferred();

            // Authenticate
            if (this.isGuest()) {

                if (controllerName != 'login') {
                    can.route.attr({
                        controller: ioc.Config.redirectLogin
                    }, true);
                } else {
                    def.resolve(controllerName);
                }
            } else if (ioc.attr('clients')) {

                this.handleMultiClients();
            } else {
                if (controllerName == 'login') {
                    can.route.attr({
                        controller: ioc.Config.redirectTo
                    }, true);
                } else {
                    def.resolve(controllerName);
                }
            }

            return def;
        },
        previousController: null,
        previousControllerName: null,
        previousControllerAction: null,
        _disableRoute: false,
        disableRoute: function() {
            this._disableRoute = true;
        },
        handleRoute: function(data) {
            if (this._disableRoute) {
                this._disableRoute = false;
                return;
            }

            ioc.attr('cachedRequest', data);
            var request = new ioc.Request(data);
            var name = request.getParam('controller') ? request.getParam('controller') : 'home';


            var app = this,
                promise = this.middleware(name);

            promise.done(function(name) {
                require(['controllers/' + name], function(controller) {
                    var $element = $('<div>');
                    $(ioc.Config.ContentClass).append($element);

                    var c = new controller($element, request);

                    if (app.previousController !== null) {
                        app.previousController.destroy();
                    }
                    app.previousControllerName = name;
                    app.previousController = c;
                    app.previousControllerAction = request.getParam('action');
                });
            });
        },
        reload: function() {
            this.handleRoute(ioc.attr('cachedRequest'));
        },
        isMobile: function() {
            try {
                document.createEvent("TouchEvent");
                return true;
            } catch (e) {
                return false;
            }
        }
    }))(document, {
        ready: ioc.ready
    });

    return ioc;
});
