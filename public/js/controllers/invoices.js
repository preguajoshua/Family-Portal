define([
    'app/container',
    'helpers/invoices',
    'can/route',
    'can/model',
], function (ioc) {
    return ioc.Controller({
        defaults: {
            action: 'main'
        }
    }, {
        init: function (element, options) {
            var action = can.route.attr('action') || options.action;

            require(['controllers/invoices/' + action], can.proxy(function (controller) {
                new controller(this.element);
            }, this));
        },
    });
});


function routeToInvoiceDetails(Id) {
    $(".quickPayButton").attr("disabled", true);
    let url = '/invoices/details/' + Id;
    return window.history.pushState({}, 'invoiceDetails', url);
}
