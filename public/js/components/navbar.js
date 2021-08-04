define([
    'jquery',
    'app/container',
    'can/component',
    'can/route',
], function($, ioc) {
    can.Component.extend({
        tag: "navbar",
        template: can.view('/js/views/navbar.stache'),
        init: function() {
            this.viewModel.attr('client', ioc.attr('client'));
            this.viewModel.attr('provider', ioc.attr('provider'));
            this.viewModel.attr('clients', []);

            if (!ioc.attr('clients')) {
                $.getJSON('/front/clients', can.proxy(function(data) {
                    this.viewModel.attr('clients', data);
                }, this));
            }
        },
        viewModel: {
            client: null,
            provider: null,
            clientSwitch: function(context, element, event) {
                event.preventDefault();
                ioc.app.showLoader();
                ioc.app.handleMultiClients();
            },
            logout: function(content, element, event) {
                event.preventDefault();

                $.post('/auth/logout', function(data) {
                    ioc.attr('client', null);
                    ioc.attr('clients', null);
                    ioc.attr('guest', true);

                    window.location = ioc.Config.redirectLogin;
                });
            }
        }
    });
});
