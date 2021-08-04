define([
    'app/container',
    'models/client',
    'helpers/clients',
    'can/model',
], function (ioc, clientModel) {
    return ioc.Controller({
        init: function () {
            $('[data-toggle="tooltip"]').tooltip();

            var clientId = this.options.getParam('action');

            var request = clientModel.findAll({}).done(can.proxy(function (items) {
                this.apiFailed = false;
                this.render('views/clients/client', {
                    clients: items,
                    clientId: clientId
                });
            }, this));

            can.when(request).fail(can.proxy(function () {
                this.apiFailed = true;
                this.render('views/clients/client', {
                    clients: [],
                    clientId: clientId
                });
            }, this));
        },
    });
});
