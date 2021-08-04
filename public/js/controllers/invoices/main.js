define([
    'app/container',
    'models/invoice',
    'helpers/invoices',
    'moment',
    'can/control/route',
    'can/route/pushstate',
    'jquery.print',
], function(ioc, Invoices, InvoiceHelper, moment) {
    return ioc.Controller({
        init: function() {
            this.viewPaidBills = can.compute(true);
            this.provider = ioc.provider;
            this.invoices = new can.List;
            this.PG = can.compute(true);

            var def1 = Invoices.findAll({}, can.proxy(function(invoices) {
                this.invoices.replace(invoices);
                this.invoiceToggle(this);

            }, this));

            var def3 = $.getJSON('/front/billing/status', can.proxy(function(data) {
                this.PG(ioc.app.hasAccount() && data.status);
            }, this));

            can.when(def1, def3).then(can.proxy(function() {
                this.render('views/invoices', this);
            },this));
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
        callPrint: function(iframeId) {
            var PDF = document.getElementById(iframeId);
            PDF.focus();
            PDF.contentWindow.print();
        },
        download: function(context, element, event) {
            event.preventDefault();

            window.location.href = element.attr('href');
        },
        invoiceToggle: function(context, element, event) {
            if (this.viewPaidBills() === false) {
                for (var i = 0; i < context.invoices.length; i++) {

                    if (InvoiceHelper.isPaidInvoice(context.invoices[i])) {
                        context.invoices[i].attr('Enabled', true);
                    } else {
                        context.invoices[i].attr('Enabled', false);
                    }
                }
            } else {
                for (var i = 0; i < context.invoices.length; i++) {
                	if(moment(context.invoices[i].ClaimDate) <= moment()) {
                    	context.invoices[i].attr('Enabled', true);
                	} else {
                    	context.invoices[i].attr('Enabled', false);
                	}
                }
            }

            this.viewPaidBills(!this.viewPaidBills());
        }
    });
});
