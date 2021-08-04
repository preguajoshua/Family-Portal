xdefine([
    'app/container',
    'models/invoice',
    'models/payment',
    'helpers/invoices',
    'libs/jquery.mask'
], function(ioc, Invoices, Payments) {
    return ioc.Controller({
        provider: null,
        invoice: null,
        payments: null,
        init: function() {

            this.payments = new can.List;

            Invoices.findOne({
                id: this.options.getParam('action')
            }, can.proxy(function(invoice) {
                this.invoice = invoice;
                this.provider = ioc.provider;
                this.render('views/invoices/checkout.stache', this);

                $('#bill_zipcode').mask('00000-000');
                $('#card_month').mask('00');
                $('#card_year').mask('0000');
                $('#card_cvc').mask('0000');
            }, this));

            Payments.findAll({
                id: this.options.getParam('action')
            }, can.proxy(function(payments) {
                this.payments.replace(payments);
            }, this));
        },
        print: function(context, element, event) {
            event.preventDefault();

            var url = element.attr('href');

            var _this = this,
                iframeId = 'iframeprint',
                $iframe = $('iframe#iframeprint');
            $iframe.attr('src', url);

            $iframe.load(function() {
                _this.callPrint(iframeId);
            });
        },
        goback: function(context, element, event) {
            event.preventDefault();

            can.route.attr({
                controller: 'invoices'
            }, true);
        },
        callPrint: function(iframeId) {
            var PDF = document.getElementById(iframeId);
            PDF.focus();
            PDF.contentWindow.print();
        },
        download: function(context, element, event) {
            event.preventDefault();

            window.location.href = element.attr('href');

            console.log(context, element, event);
        },
    });
});