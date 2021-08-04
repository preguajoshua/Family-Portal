define([
    'app/container',
    'can/route'
], function(ioc) {
    return ioc.Controller({
        init: function() {
            if (ioc.app.isGuest()) {
                return;
            }
            if (!ioc.clients) {
                var promise = $.get('/front/clients', function(data) {
                    ioc.attr('clients', data);
                });

                promise.then(can.proxy(function() {
                    this.ready();
                }, this));
            } else {
                this.ready();
            }
        },
        ready: function() {
            ioc.ready(true);
            this.clients = ioc.clients;
            this.enableCancel = ioc.client != null;

            $('#wrapper').html(can.view('views/switch', this));
        },
        cancel: function(context, element, event) {
            event.preventDefault();
            var promise = ioc.app.showLoader();

            promise.done(can.proxy(function() {
                this.reboot();
            }, this));
        },
        select: function(client, element, event) {
            event.preventDefault();

            var promise = ioc.app.showLoader();

            promise.done(can.proxy(function() {
                $.post('/front/session/client', {
                    id: client.attr('Id')
                }, can.proxy(function() {
                    ioc.attr('client', client.attr());
                    ioc.removeAttr('clients');

                    this.reboot();
                }, this));
            }, this));
        },
        reboot: function() {

            ioc.render(false);
            ioc.attr('guest', false);
            ioc.app.render();
            ioc.app.boot();

            console.log(ioc);

            this.redirectTo();
        },
        redirectTo: function() {
            if (ioc.previousRoute) {
                can.route.attr(ioc.attr('previousRoute'), true);
            } else {
                can.route.attr({
                    controller: ioc.Config.redirectTo
                }, true);
            }
        }
    });
});
