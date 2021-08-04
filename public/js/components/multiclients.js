define([
    'app/container',
    'can/component',
], function (ioc) {
    can.Component.extend({
        tag: "multiclients",
        template: can.view('/js/views/switch.stache'),
        init: function () {

            this.viewModel.enableCancel(ioc.attr('client.Id') != null);

            $.get('/front/clients', can.proxy(function () {
            }))
                .then(can.proxy(function (response) {
                    ioc.attr('clients', response);
                    this.viewModel.attr('clients', ioc.attr('clients').attr());
                    ioc.app.hideLoader();
                }, this));
        },
        viewModel: {
            visible: false,
            enableCancel: can.compute(false),
            clients: null,
            cancel: function (context, element, event) {
                event.preventDefault();

                if (ioc.app.isGuest()) return;

                this.removePanel();
            },
            removePanel: function () {
                $('#multiclients').fadeOut(function () {
                    $(this).remove();
                });
            },
            select: function (client, element, event) {
                event.preventDefault();

                var self = this;

                var promise = ioc.app.showLoader();

                promise.done(function () {
                    $.post('/front/session/client', {
                        id: client.attr('Id')
                    }, function () {
                        ioc.attr('client', client.attr());
                        ioc.removeAttr('clients');

                        self.reboot();
                        self.removePanel();
                    });

                });
            },
            reboot: function () {
                ioc.render(false);
                ioc.attr('guest', false);
                ioc.app.render();
                var promise = ioc.app.boot();

                promise.done(can.proxy(function () {
                    this.redirectTo();
                }, this));
            },
            redirectTo: function () {
                if (ioc.previousRoute) {
                    can.route.attr(ioc.attr('previousRoute'), true);
                } else {
                    can.route.attr({
                        controller: ioc.Config.redirectTo
                    }, true);
                }
            },
            logout: function (content, element, event) {
                event.preventDefault();

                $.post('/auth/logout', can.proxy(function (data) {
                    this.removePanel();

                    ioc.attr('client', null);
                    ioc.attr('clients', null);
                    ioc.attr('guest', true);

                    window.location = ioc.Config.redirectLogin;
                }, this));
            }
        }
    });
});
