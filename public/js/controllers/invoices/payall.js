define([
    'app/container',
    'models/invoice',
    'models/payment',
    'models/customer',
    'app/helper',
    'helpers/invoices',
    'helpers/payment.method',
    'sticky',
    'libs/jquery.mask'
], function(ioc, Invoices, Payments, Customer, Helper, InvoiceHelper, paymentMethod) {
    return ioc.Controller({
        defaults: {

        }
    }, {
        init: function() {
            var invoiceId = can.route.attr('param1');

            this.PG = can.compute(false);
            this.cardInfo = new can.Map({});
            this.newCard = new can.Map();
            this.wizardBoxStep = can.compute(0);
            this.enableInvoicePage = can.compute(true);
            this.enableReviewForm = can.compute(false);
            this.enableBillingForm = can.compute(false);
            this.enablePaymentMethodForm = can.compute(false);
            this.enableCompletePaymentPage = can.compute(false);
            this.enableHistoryPage = can.compute(false);
            this.enableProcessPaymentBtn = can.compute(false);
            this.overPaid = can.compute(false);
            this.showPartialPaymentInput = can.compute(false);
            this.enablePartialUpdateBtn = can.compute(true);
            this.changePaymentInformation = can.compute(false);

            this.minimumChargeAmount = 5;
            this.paymentInvoiceAmount = can.compute(0, function(newVal) {
                return newVal.replace(',', '');
            });

            this.provider = null;
            this.invoice = new can.Map;
            this.customer = null;
            this.invoices = null;
            this.billingInfo = new can.Map({});
            this.paymentInvoiceTotal = null;

            var def1 = Customer.findOne({}, can.proxy(function(customer) {
                this.customer = customer;
            }, this));

            var def4 = new Payments.List({
                id: invoiceId
            }, can.proxy(function(data) {
                this.payments = data;
            },this));

            var def2 = Invoices.findAll({unpaid:1}, can.proxy(function(invoices) {
                this.invoices = invoices;

                this.paymentInvoiceTotal = parseFloat(InvoiceHelper.getBalance(invoices)).toFixed(2);
                this.paymentInvoiceAmount(this.paymentInvoiceTotal);

            }, this));

            var def3 = $.getJSON('/front/billing/status', can.proxy(function(data) {
                this.PG(ioc.app.hasAccount() && data.status);
            }, this));

            can.when(def1, def2, def3, def4).then(can.proxy(function() {
                if(this.invoices.length == 1) {
                    console.log('invoices length', this.invoices.length)
                    setTimeout(can.proxy(function() {
                        can.route.attr({controller:"invoices", action: "details", param1: this.invoices[0].Id}, true);
                    }, this), 300);
                    return;
                }

                console.log('render pay all view');
                this.render('views/invoices/payall.stache', this);

                $('.card-make-payment').sticky({
                    topSpacing: 90
                });
                $('#bill_zipcode').mask('00000-000');
                // $('#card_month').mask('00');
                // $('#card_year').mask('0000');
                $('#card_expiration_date').mask('00/00');
                $('#card_cvc').mask('0000');
                $('#totalInvoiceAmount').mask("###0.00", {
                    reverse: true
                });
                $('[name=billing_phone]').mask('(000) 000-0000');

                if (parseFloat(this.paymentInvoiceTotal) < 0) {
                    this.overPaid(true);
                }

                this.enableBillingForm(this.customer.attr("sources").attr("length") < 0);
            }, this));

            this.paymentInvoiceAmount.bind('change', can.proxy(function(evt, newVal, oldVal) {
                var amount = parseFloat(newVal.replace(',', ''));
                if (amount > parseFloat(this.paymentInvoiceTotal) || amount < this.minimumChargeAmount || isNaN(amount)) {
                    this.enableProcessPaymentBtn(false);
                    this.enablePartialUpdateBtn(false);
                    return $('#totalInvoiceAmount').parent().addClass('has-error');
                }
                if(this.showPartialPaymentInput() === false) {
                    this.enableProcessPaymentBtn(true);
                }

                this.enablePartialUpdateBtn(true);

                $('#totalInvoiceAmount').parent().removeClass('has-error');
            }, this));

            this.showPartialPaymentInput.bind('change', can.proxy(function(evt, newVal, oldVal) {
                if(newVal === true) {
                    this.enableProcessPaymentBtn(false);
                } else {
                    this.enableProcessPaymentBtn(true);
                }
            }, this));

            this.wizardBoxStep.bind('change', can.proxy(function(evt, newVal, oldVal) {
                var stepMethod = 'wizardBoxStep' + newVal;
                if (typeof this[stepMethod]) {
                    return this[stepMethod]();
                }
            }, this));


            this.wizardBoxStep(1);

            this.enableHistoryPage.bind('change', can.proxy(function(evt, newVal, oldVal) {
                if (newVal === true && oldVal === false) {
                    this.wizardBoxStep(1);
                    this.cardInfo.attr({}, true);
                    this.enableInvoicePage(false);
                } else if(oldVal === true) {
                    this.wizardBoxStep(1);
                    this.enableInvoicePage(true);
                }
            }, this));

            this.changePaymentInformation.bind('change', can.proxy(function(evt, newVal, oldVal) {
                if(newVal === false) return;
                this.wizardBoxStep(2);
            }, this));

            this.enableBillingForm.bind('change', can.proxy(function(evt, newVal, oldVal) {
                if(newVal) {
                    this.enablePaymentMethodForm(false);
                } else if(this.wizardBoxStep() == 2) {
                    this.enablePaymentMethodForm(true);
                }
            }, this));
        },
        "destroyed": function(el, ev) {},
        "removed": function(el, ev) {
            this.destroy();
        },
        wizardBoxStep1: function() {
            this.cardInfo.attr({}, true);
            this.enableInvoicePage(true);
            this.enableReviewForm(false);
            this.enableBillingForm(false);
            this.enableHistoryPage(false);
            this.enablePaymentMethodForm(false);
            this.changePaymentInformation(false);
            setTimeout(function() {
                $('.card-make-payment').sticky({
                    topSpacing: 90
                });
            });
        },
        wizardBoxStep2: function() {
            console.log('Fire: wizardBoxStep2');
            this.cardInfo.attr({}, true);

            this.enableHistoryPage(false);
            this.enableInvoicePage(false);
            this.enableReviewForm(false);

            var hasSources = this.customer.attr("sources").attr("length") > 0;

            this.enablePaymentMethodForm(hasSources);
            this.enableBillingForm(!hasSources);

            this.customer.attr('sources').each(can.proxy(function(source, index) {
                if(this.customer.attr('default_source') == source.attr('id')) {
                    if(this.changePaymentInformation() === false) {
                        this.cardInfo.attr(source.attr());
                        this.wizardBoxStep(3);
                    } else {
                        $('#card_'+source.attr('id')).trigger('click');
                        // this.selectCard(source, $('.is-default-card'));
                    }
                    return;
                }
            }, this))
        },
        wizardBoxStep3: function() {
            console.log('Fire: wizardBoxStep3');
            if (!this.cardInfo.attr('id')) {
                return;
            }

            this.enableReviewForm(true);
            this.changePaymentInformation(false);
            this.enableInvoicePage(false);
            this.enablePaymentMethodForm(false);
            this.enableBillingForm(false);
            this.enableCompletePaymentPage(false);
        },
        wizardBoxStep4: function() {
            console.log('Fire: wizardBoxStep4');
            this.enableInvoicePage(false);
            this.enablePaymentMethodForm(false);
            this.enableReviewForm(false);
            this.enableBillingForm(false);
            this.enableCompletePaymentPage(true);
        },
        processPayment: function(context, element, event) {
            event.preventDefault();
            this.enableProcessPaymentBtn(false);
            if (this.paymentInvoiceAmount() >= this.minimumChargeAmount) {
                ioc.app.showLoader({
                    opacity: .5
                });

                var invoices = [];
                this.invoices.each(function(invoice, index) {
                    invoices.push(invoice.attr('Id'));
                });

                console.log(invoices);
                $.ajax({
                    method: "POST",
                    url: '/front/invoices/process_mass_payment',
                    data: {
                        source: this.cardInfo.id,
                        invoices: invoices,
                        amount: this.paymentInvoiceAmount()
                    }
                }).done($.proxy(function(data) {
                    if (data.status == 'success') {
                        this.wizardBoxStep(4);
                    }
                    this.enableProcessPaymentBtn(true);
                }, this)).fail($.proxy(function(xhr) {
                    Helper.handleErrors(xhr.responseJSON);
                    this.enableProcessPaymentBtn(true);
                    ioc.app.hideLoader();
                }, this)).done(function() {
                    ioc.app.hideLoader();
                });
            }
        },
        openReviewInvoice: function(context, element, event) {
            event.preventDefault();
            if (!this.cardInfo.attr('id')) {
                return;
            }
            this.wizardBoxStep(3);
        },
        makeDefault: function(card, element, event) {
            event.preventDefault();

            this.customer.attr('default_source', card.attr('id'));
            this.customer.save();
        },
        selectCard: function(card, element, event) {
            event.preventDefault();
            this.cardInfo.attr(card.attr());
            element.find('input').prop('checked', true);
        },
        deleteCard: function(context, element, event) {
            event.preventDefault();
            event.stopPropagation();
            Helper.Modal.confirm('Are you sure you want to remove this card?', $.proxy(function(result) {
                if (result) {
                    if (this.cardInfo.attr('id') == context.attr('id')) {
                        this.cardInfo.attr({}, true);
                    }
                    context.destroy();
                }
            }, this));
        },
        'form.payment-method-add submit': function(element, event) {
            event.preventDefault();

            $.get('/front/payments/token', can.proxy(function(data) {

                var client = paymentMethod.setup(data);

                var formData = element.serializeObject();
                var paymentSource = new Sources({
                    billing_address_city: formData.billing_address_city,
                    billing_address_line_1: formData.billing_address_line_1,
                    billing_address_state: formData.billing_address_state,
                    billing_address_zipcode: formData.billing_address_zipcode,
                    billing_first_name: formData.billing_first_name,
                    billing_last_name: formData.billing_last_name,
                    billing_phone: formData.billing_phone,
                    card_first_name: formData.card_first_name,
                    card_last_name: formData.card_last_name
                });

                client.tokenize(formData, can.proxy(function(token, status, err) {
                    ioc.app.showLoader({
                        opacity: .5
                    });

                    if (status != 200) {
                        ioc.Helper.handleErrors({
                            "card_error": [err.message]
                        });
                        ioc.app.hideLoader();
                        return false;
                    }

                    paymentSource.attr('payment_method_token', token);

                    paymentSource.save(can.proxy(function(paymentSource) {
                        this.customer.attr("sources").push(paymentSource);
                        this.enableBillingForm(false);
                        ioc.app.hideLoader();
                        $('.billing-card-info').find('input').val('');
                    }, this), function(XmlHttpRequest) {
                        ioc.Helper.handleErrors(XmlHttpRequest.responseJSON);
                        ioc.app.hideLoader();
                    });
                }, this));
            }, this));
        },
        print: function(context, element, event) {
            event.preventDefault();

            window.print();
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
        },
    });
});
