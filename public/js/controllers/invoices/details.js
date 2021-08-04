define([
    'app/container',
    'models/invoice',
    'models/payment',
    'models/customer',
    'models/sources',
    'app/helper',
    'helpers/invoices',
    'sticky',
    'libs/jquery.mask'
], function(ioc, Invoices, Payments, Customer, Sources, Helper, InvoiceHelper) {
    return ioc.Controller({
        defaults: {

        }
    }, {
        init: function() {
            var invoiceId = can.route.attr('param1');
            // Credit card convenience fee = 2.9% + $0.30
            const CREDIT_CARD_FEE = 0.029;
            const CREDIT_CARD_ADDITIONAL_CHARGE = 0.3;

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
            this.showPartialPaymentInput = can.compute(false);
            this.enablePartialUpdateBtn = can.compute(false);
            this.changePaymentInformation = can.compute(false);
            this.overPaid = can.compute(false);

            this.minimumChargeAmount = 5;
            this.paymentInvoiceAmount = can.compute(0, function(newVal) {
                return newVal.replace(',', '');
            });
            this.paymentInvoiceFee = can.compute(0, function(newVal) {
                var fee = newVal * CREDIT_CARD_FEE + CREDIT_CARD_ADDITIONAL_CHARGE;
                return fee.toFixed(2).replace(',', '');
            });
            this.paymentInvoiceGrandTotal = can.compute(0, function(newVal) {
                var fee = newVal * CREDIT_CARD_FEE + CREDIT_CARD_ADDITIONAL_CHARGE;
                var total = newVal + fee;
                return total.toFixed(2).replace(',', '');
            });

            this.provider = null;
            this.customer = null;
            this.invoice = null;
            this.billingInfo = new can.Map({});
            this.paymentInvoiceTotal = null;
            this.clientAuthToken = null;
            this.confirmationId = null;

            var def1 = Customer.findOne({}, can.proxy(function(customer) {
                this.customer = customer;
            }, this));

            var def2 = Invoices.findOne({id: invoiceId}, can.proxy(function(invoice) {
                this.invoice = invoice;
                this.provider = ioc.provider;

                this.paymentInvoiceTotal = parseFloat(InvoiceHelper.getBalance(invoice));

                this.paymentInvoiceAmount(this.paymentInvoiceTotal.toFixed(2));
                this.paymentInvoiceFee(this.paymentInvoiceTotal);
                this.paymentInvoiceGrandTotal(this.paymentInvoiceTotal);

                this.billingInfo.attr({
                    AddressCity: this.invoice.attr('Billing')['AddressCity'],
                    AddressLine1: this.invoice.attr('Billing')['AddressLine1'],
                    AddressLine2: this.invoice.attr('Billing')['AddressLine2'],
                    AddressStateCode: this.invoice.attr('Billing')['AddressStateCode'],
                    AddressZipCode: this.invoice.attr('Billing')['AddressZipCode'],
                    PhoneHome: this.invoice.attr('Billing')['PhoneHome'],
                    FirstName: this.invoice.attr('Billing')['FirstName'],
                    LastName: this.invoice.attr('Billing')['LastName'],
                });
            }, this));

            var def3 = $.getJSON('/front/billing/status', can.proxy(function(data) {
                this.PG(ioc.app.hasAccount() && data.status);
            }, this));

            var def4 = new Payments.List({
                id: invoiceId
            }, can.proxy(function(data) {
                this.payments = data;
            },this));

            var def5 = $.get('/front/payments/token', can.proxy(function (tokenString) {
                this.clientAuthToken = tokenString;
            }, this));

            can.when(def1, def2, def3, def4, def5).then(can.proxy(function() {
                this.render('views/invoices/billing.stache', this);
                $('.card-make-payment').sticky({
                    topSpacing: 90
                });
                $('#bill_zipcode').mask('00000-000');
                $('#card_number').mask('0000-0000-0000-0000');
                $('#card_expiration_date').mask('00/00');
                $('#card_cvc').mask('0000');
                $('#totalInvoiceAmount').mask("###0.00", {
                    reverse: true
                });
                $('[name=billing_phone]').mask('(000) 000-0000');

                if (this.paymentInvoiceTotal < 0) {
                    this.overPaid(true);
                }

                this.enableBillingForm(this.customer.attr('sources').attr("length") < 0);
            }, this));


            this.paymentInvoiceAmount.bind('change', can.proxy(function(evt, newVal, oldVal) {
                var amount = parseFloat(newVal.replace(',', ''));

                if (amount > this.paymentInvoiceTotal || amount < this.minimumChargeAmount || isNaN(amount)) {
                    this.enableProcessPaymentBtn(false);
                    this.enablePartialUpdateBtn(false);
                    return $('#totalInvoiceAmount').parent().addClass('has-error');
                }

                this.paymentInvoiceFee(amount);
                this.paymentInvoiceGrandTotal(amount);

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
                if (newVal === true) {
                    this.clientSdk();
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
            this.cardInfo.attr({}, true);

            this.enableHistoryPage(false);
            this.enableInvoicePage(false);
            this.enableReviewForm(false);

            var hasSources = this.customer.attr('sources').attr("length") > 0;

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
            if (!this.cardInfo.attr('id')) {
                console.log('No default card found.');
                return;
            }

            this.changePaymentInformation(false);
            this.enableReviewForm(true);
            this.enableInvoicePage(false);
            this.enablePaymentMethodForm(false);
            this.enableBillingForm(false);
            this.enableCompletePaymentPage(false);
        },
        wizardBoxStep4: function() {
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

                $.ajax({
                    method: "POST",
                    url: '/front/invoices/' + this.invoice.Id + '/process',
                    data: {
                        source: this.cardInfo.id,
                        amount: this.paymentInvoiceAmount(),
                        fee: this.paymentInvoiceFee()
                    }
                }).done($.proxy(function(data) {
                    if (data.status == 'success') {
                        this.confirmationId = data.codes.confirmationId;
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
        selectCard: function(card, element, event) {
            event.preventDefault();
            this.cardInfo.attr(card.attr());
            element.find('input').prop('checked', true);
        },
        makeDefault: function(card, element, event) {
            event.preventDefault();

            this.customer.attr('default_source', card.attr('id'));
            this.customer.save();
        },
        deleteCard: function(source, element, event) {
            event.preventDefault();
            event.stopPropagation();
            Helper.Modal.confirm('Are you sure you want to remove this card?', $.proxy(function(result) {
                if (result) {
                    if (this.cardInfo.attr('id') == source.attr('id')) {
                        this.cardInfo.attr({}, true);
                    }
                    source.destroy();
                }
            }, this));
        },
        clientSdk: function() {
            // Client authorization
            braintree.client.create({
                authorization: this.clientAuthToken

            }, can.proxy( function( clientErr, clientInstance ) {
                if ( clientErr ) {
                    ioc.Helper.handleErrors( {"status": "error", "message": "Error: Failed to connect to payment gateway."} );

                    console.error( clientErr.message, clientErr );
                    return false;
                }

                // Hosted fields
                braintree.hostedFields.create({
                    client: clientInstance,
                    styles: {
                        "input": {
                            "color": "#777777",
                            "font-size": "14px",
                        },
                        "input.invalid": {
                            "color": "#ff4a55",
                        },
                        "input.valid": {
                            "color": "#000000",
                        },
                        "::-moz-placeholder": {
                            "color": "#dddddd",
                            "opacity": "1",
                            "filter": "alpha(opacity=100)",
                        },
                        ":-moz-placeholder": {
                            "color": "#dddddd",
                            "opacity": "1",
                            "filter": "alpha(opacity=100)",
                        },
                        "::-webkit-input-placeholder": {
                            "color": "#dddddd",
                            "opacity": "1",
                            "filter": "alpha(opacity=100)",
                        },
                        ":-ms-input-placeholder": {
                            "color": "#dddddd",
                            "opacity": "1",
                            "filter": "alpha(opacity=100)",
                        },
                    },
                    fields: {
                        number: {
                            selector: "#card-number",
                            placeholder: "XXXX-XXXX-XXXX-XXXX",
                        },
                        cvv: {
                            selector: "#cvv",
                            placeholder: "CVV",
                        },
                        expirationDate: {
                            selector: "#expiration-date",
                            placeholder: "MM/YY",
                        },
                    }

                }, can.proxy( function( hostedFieldsErr, hostedFieldsInstance ) {
                    if ( hostedFieldsErr ) {
                        ioc.Helper.handleErrors( {"status": "error", "message": "Error: Failed to initalize payment form."} );

                        console.error( hostedFieldsErr.message, hostedFieldsErr );
                        return false;
                    }

                    $( "#add-card-button" ).removeAttr( "disabled" );

                    $( "#add-card-button" ).on( "click", can.proxy( function( e ) {
                        event.preventDefault();

                        hostedFieldsInstance.tokenize( can.proxy( function( tokenizeErr, payload ) {
                            ioc.app.showLoader( { opacity: 0.5 } );

                            if ( tokenizeErr ) {
                                ioc.app.hideLoader();

                                this.handleTokenizeError( tokenizeErr );
                                return false;
                            }

                            var creditCardInfo = $( "#payment-method-add" ).serializeObject();

                            var paymentSource = new Sources({
                                billing_address_city: creditCardInfo.billing_address_city,
                                billing_address_line_1: creditCardInfo.billing_address_line_1,
                                billing_address_state: creditCardInfo.billing_address_state,
                                billing_address_zipcode: creditCardInfo.billing_address_zipcode,
                                billing_first_name: creditCardInfo.billing_first_name,
                                billing_last_name: creditCardInfo.billing_last_name,
                                billing_phone: creditCardInfo.billing_phone,
                                card_first_name: creditCardInfo.card_first_name,
                                card_last_name: creditCardInfo.card_last_name,
                                payment_method_token: payload.nonce
                            });

                            paymentSource.save( can.proxy( function( paymentSource ) {
                                this.customer.attr( "sources" ).push( paymentSource );

                                this.enableBillingForm( false );
                                $( ".billing-card-info" ).find( "input" ).val( "" );

                                ioc.app.hideLoader();

                            }, this ), function( XmlHttpRequest ) {
                                ioc.Helper.handleErrors( XmlHttpRequest.responseJSON );
                                ioc.app.hideLoader();
                            });
                        }, this ));
                    }, this ));
                }, this ));
            }, this ));
        },
        handleTokenizeError: function( tokenizeErr ) {
            $message = "";
            $data = null;

            switch ( tokenizeErr.code ) {
                case "HOSTED_FIELDS_FIELDS_EMPTY":
                    $message = "Error: All credit card fields are empty!";
                    $data = tokenizeErr;
                    break;

                case "HOSTED_FIELDS_FIELDS_INVALID":
                    $message = "Error: Some credit card fields are invalid.";
                    $data = tokenizeErr.details.invalidFieldKeys;

                    $.each( tokenizeErr.details.invalidFields, function( fieldKey, fieldContainer ) {
                        fieldContainer.className = "has-error";
                    });
                    break;

                case "HOSTED_FIELDS_TOKENIZATION_FAIL_ON_DUPLICATE":
                    // occurs when:
                    //   * the client token used for client authorization was generated
                    //     with a customer ID and the fail on duplicate payment method
                    //     option is set to true
                    //   * the card being tokenized has previously been vaulted (with any customer)
                    // See: https://developers.braintreepayments.com/reference/request/client-token/generate/#options.fail_on_duplicate_payment_method
                    $message = "Error: This payment method already exists in your vault.";
                    $data = tokenizeErr;
                    break;

                case "HOSTED_FIELDS_TOKENIZATION_CVV_VERIFICATION_FAILED":
                    // occurs when:
                    //   * the client token used for client authorization was generated
                    //     with a customer ID and the verify card option is set to true
                    //     and you have credit card verification turned on in the Braintree
                    //     control panel
                    //   * the cvv does not pass verfication (https://developers.braintreepayments.com/reference/general/testing/#avs-and-cvv/cid-responses)
                    // See: https://developers.braintreepayments.com/reference/request/client-token/generate/#options.verify_card
                    $message = "Error: CVV did not pass verification";
                    $data = tokenizeErr;
                    break;

                case "HOSTED_FIELDS_FAILED_TOKENIZATION":
                    // occurs for any other tokenization error on the server
                    $message = "Error: Tokenization failed server side. Is the card valid?";
                    $data = tokenizeErr;
                    break;

                case "HOSTED_FIELDS_TOKENIZATION_NETWORK_ERROR":
                    // occurs when the Braintree gateway cannot be contacted
                    $message = "Error: Network error occurred when tokenizing.";
                    $data = tokenizeErr;
                    break;

                default:
                    $message = "Error: Something bad happened!";
                    $data = tokenizeErr;
            }

            ioc.Helper.handleErrors( {"status": "error", "message": $message} );
            console.error( $message, $data );
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
            window.open(element.attr('href'), '_blank');
        },
    });
});
