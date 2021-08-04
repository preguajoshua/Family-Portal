define([
    'moment',
    'can/view/stache',
], function(moment) {

    var Helper = {
        currency: function(value) {
            var amount = '$' + parseFloat(Math.abs(value)).toFixed(2).replace(/./g, function(c, i, a) {
                return i && c !== "." && ((a.length - i) % 3 === 0) ? ',' + c : c;
            });

            if(value < 0) {
                return '(' + amount +')';
            }

            return amount;
        },
        getBalance: function(invoice) {
            if(invoice.length) {
                var amount = 0;
                invoice.each(can.proxy(function(item, index) {
                    amount += this.getBalance(item);
                }, this));
                return amount;
            }
            return (invoice.NetDue - invoice.PaidAmount + invoice.AdjustmentAmount)-invoice.NegativeAdjustmentAmount;
        },
        getAdjustmentAmount: function(invoice) {
            return parseFloat(invoice.AdjustmentAmount-invoice.NegativeAdjustmentAmount);
        },
        getInvoiceTotal: function(invoice) {
            return invoice.NetDue;
            return (parseFloat(invoice.ProspectivePay) + parseFloat(invoice.TotalTax)) - parseFloat(invoice.AdjustmentAmount) + parseFloat(invoice.NegativeAdjustmentAmount);
        },
        getInvoiceTotalBeforeTax: function(invoice) {
            return invoice.ProspectivePay;
            return parseFloat(invoice.ProspectivePay) - parseFloat(invoice.AdjustmentAmount) + parseFloat(invoice.NegativeAdjustmentAmount);
        },
        isPaidInvoice: function(invoice) {
            return invoice.Status == 3030 || this.getBalance(invoice) <= 0;
        }
    }

    var relationship = {
        "01": "Spouse",
        "02": "Mother",
        "03": "Father",
        "04": "Friend",
        "05": "Emergency Contact",
        "06": "POA",
        "18": "Self",
        "19": "Child",
        "20": "Employee",
        "21": "Unknown",
        "39": "Organ donor",
        "40": "Cadaver donor",
        "53": "Life partner",
        "G8": "Other"
    };

    can.stache.registerHelper('currency', function(value) {
        return Helper.currency(value());
    });

    can.stache.registerHelper('invoicePaid', function(invoice, options) {
        if (Helper.isPaidInvoice(invoice)) {
            return options.fn(this);
        }
        return options.inverse(this);
    });

    // All Invoices balance
    can.stache.registerHelper('totalInvoiceBalance', function(invoices, options) {
        var balance = 0;
        for (var i = 0; i < invoices.invoices.length; i++) {
            var dueAmount = Helper.getBalance(invoices.invoices[i]);
            if(dueAmount > 0) {
                balance += dueAmount;
            }
        }
        return Helper.currency(balance);
    });

    can.stache.registerHelper('invoiceOverdue', function(invoice, options) {
        if (moment(invoice.DueDate).isBefore(moment()) && !Helper.isPaidInvoice(invoice)) {
            return options.fn(this);
        }
        return options.inverse(this);
    });

    can.stache.registerHelper('ifOverPaidInvoice', function(invoice, options) {
        if (parseFloat(Helper.getBalance(invoice)) < 0) {
            return options.fn(this);
        }
        return options.inverse(this);
    });

    function formatInvoiceNumber(str) {
        var pad = '00000';
        return pad.substring(0, pad.length - str.length) + str;
    }
    can.stache.registerHelper('invoiceNo', function(value, options) {
        return formatInvoiceNumber(value().toString());
    });

    can.stache.registerHelper('adjustmentAmount', function(invoice, options) {
        return Helper.currency(Helper.getAdjustmentAmount(invoice));
    });

    can.stache.registerHelper('hasAdjustmentAmount', function(invoice, options) {
        var adjustmentAmount = Helper.getAdjustmentAmount(invoice);
        if (adjustmentAmount) {
            return options.fn(this);
        }
        return options.inverse(this);
    });

    can.stache.registerHelper('invoiceBalance', function(invoice, options) {
        return Helper.currency(Helper.getBalance(invoice));
    });

    can.stache.registerHelper('invoiceTotal', function(invoice) {
        if(invoice.length) {
            var amount = 0;
            invoice.each(function(item, index) {
                amount += Helper.getInvoiceTotal(item);
            });
            return Helper.currency(amount);
        }
        return Helper.currency(Helper.getInvoiceTotal(invoice));
    });

    can.stache.registerHelper('invoiceTotalRaw', function(invoice) {
        return parseFloat(Helper.getInvoiceTotal(invoice)).toFixed(2);
    });

    can.stache.registerHelper('isFullAmount', function(invoice, partialAmount, options) {
        if(invoice.length) {
            var amount = 0;
            invoice.each(function(item, index) {
                amount += parseFloat(Helper.getBalance(item));
            });

            if (parseFloat(amount).toFixed(2) > parseFloat(partialAmount()) && options.context.minimumChargeAmount <= parseFloat(partialAmount())) {
                return options.inverse(this);
            }
            return options.fn(this);
        }

        if (parseFloat(Helper.getBalance(invoice)).toFixed(2) > parseFloat(partialAmount()) && options.context.minimumChargeAmount <= parseFloat(partialAmount())) {
            return options.inverse(this);
        }
        return options.fn(this);
    });

    can.stache.registerHelper('balanceDuePartial', function(invoice, partialAmount) {
        if(invoice.length) {
            var amount = 0;
            invoice.each(function(item, index) {
                amount += parseFloat(Helper.getBalance(item));
            });
            return Helper.currency(amount - parseFloat(partialAmount()));
        }
        return Helper.currency(parseFloat(Helper.getBalance(invoice)) - parseFloat(partialAmount()));
    });

    can.stache.registerHelper('invoiceRateQtyTotal', function(visit) {
        return Helper.currency(parseFloat(visit.Rate) * parseFloat(visit.Unit));
    });

    can.stache.registerHelper('invoiceTotalBeforeTax', function(invoice) {
        if(invoice.length) {
            var amount = 0;
            invoice.each(function(item, index) {
                amount += Helper.getInvoiceTotalBeforeTax(item);
            });
            return Helper.currency(amount);
        }
        return Helper.currency(Helper.getInvoiceTotalBeforeTax(invoice));
    });

    can.stache.registerHelper('invoiceTotalTax', function(invoice) {
        if(invoice.length) {
            var amount = 0;
            invoice.each(function(item, index) {
                amount += item.TotalTax;
            });
            return Helper.currency(amount);
        }
        return Helper.currency(item.TotalTax);
    });

    can.stache.registerHelper('showInvoice', function(invoice, options) {
        if (invoice.Enabled === true || typeof invoice.Enabled === 'undefined') {
            return options.fn(this);
        }
    });

    return Helper;
});
