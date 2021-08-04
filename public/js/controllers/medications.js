define(['app/container', 'models/medication', 'fullcalendar', 'can/view/stache', 'can/model', 'sticky', 'can/control/route', 'can/route/pushstate',], function (ioc, medicationModel) {
    return ioc.Controller({
        defaults: {
            defaultStatus: 'active',
            activeLabel: 'active',
            inactiveLabel: 'inactive',
            view: 'views/medications',
            sideMenuClass: '.side-menu',
            sideMenu: {
                'active': {
                    label: 'Active Medications'
                },
                'inactive': {
                    label: 'Discontinued Medications'
                },
                'all': {
                    label: 'All Medications'
                }
            },
            printUrl: '/pdf/medications'
        }
    }, {
        init: function (el, options) {
            this.medications = new can.List;
            this.originalMedications = null;
            this.filterSelected = can.compute(can.route.attr('filter') ? can.route.attr('filter') : options.defaultStatus);
            this.firstInit = true;

            var request = medicationModel.findAll({}).done(can.proxy(function (items) {
                this.originalMedications = items;
            }, this));

            can.when(request).done(can.proxy(function () {
                this.apiFailed = false;
                this.render(this.options.view, this);
                this.medicationsFilter(this.filterSelected());
            }, this));

            can.when(request).fail(can.proxy(function () {
                this.apiFailed = true;
                return this.render(this.options.view, this);
            }, this));

            $(this.options.sideMenuClass).sticky({
                topSpacing: 90
            });
        },


        medicationsFilter: function (filter) {
            if (this.apiFailed) return;

            if (this.firstInit) {
                this.firstInit = false;
            } else {
                ioc.app.disableRoute();
            }
            if (filter == this.options.activeLabel) {
                this.medications.replace(this.originalMedications.filter(function (item, index, list) {
                    return item.Active == true;
                }));
            } else if (filter == this.options.inactiveLabel) {
                this.medications.replace(this.originalMedications.filter(function (item, index, list) {
                    return item.Active == false;
                }));
            } else {
                this.medications.replace(this.originalMedications);
            }
            this.filterSelected(filter);
            if (filter == this.options.defaultStatus) {
                can.route.removeAttr('filter');
            } else {
                can.route.attr({
                    filter: filter
                });
            }
        },
        pageTitle: function () {
            return this.options.sideMenu[this.filterSelected()].label;
        },
        getPDFUrl: function (type) {
            var params = ["ticket=" + ioc.attr('token'), 'print=' + type];
            return this.options.printUrl + '?' + params.join('&');
        },
        print: function (context, element, event) {
            event.preventDefault();
            var icon = new ioc.Helper.iconLoader(element.closest('.dropdown').find('.btn-print > i'));
            icon.start();
            var url = this.getPDFUrl(element.data('type'));
            var iframe = new ioc.Helper.download(url, function () {
                ioc.Helper.callPrint(this.iframeId);
                icon.stop();
            });
        },
        download: function (context, element, event) {
            event.preventDefault();
            var icon = new ioc.Helper.iconLoader(element.find('i'));
            icon.start();
            var url = this.getPDFUrl(element.data('type')) + '&download=1';
            var iframe = new ioc.Helper.download(url, function () {
                icon.stop();
            });
        }
    });
});
