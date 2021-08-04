define([
    'app/container',
    'models/carelog',
    'moment',
    'maps/paginator',
    'can/model',
    'sticky'
], function (ioc, careLogModel, moment, paginator) {
    return ioc.Controller({
        defaults: {
            defaultFilter: 'all',
            sideMenuClass: '.box-menu-events',
            eventFirstChild: '.carelogs-container .event:first-child',
            sideMenu: {
                'all': {
                    label: 'All'
                },
                'today': {
                    label: 'Today'
                },
                'yesterday': {
                    label: 'Yesterday'
                },
                '7days': {
                    label: 'Last 7 Days'
                },
                '30days': {
                    label: 'Last 30 Days'
                }
            }
        }
    }, {
        init: function () {
            let client = ioc.attr('client');
            this.canAccessDocumentation = !!client.canViewDocumentation;
            this.showEvvDataKeys = client.Emr === "Hospice" ? false : true;

            this.firstInit = true;
            this.apiFailed = false;
            this.provider = ioc.attr('provider');
            this.filterSelected = this.getDefaultFilter();
            this.items = new can.List;

            this.pagination = new paginator();
            this.pagination.bind('page', function () {
                this.updateFilter(this.filterSelected())
            }.bind(this));

            this.updateFilter(this.filterSelected(), can.proxy(function () {
                this.render('views/carelogs', this);
                $(this.options.sideMenuClass).sticky({
                    topSpacing: 90
                });
            }, this));
        },

        updateFilter: function (filter, callback) {
            this.firstInit ? this.firstInit = false : ioc.app.disableRoute();
            this.filterSelected(filter);

            var req = careLogModel.findAll({
                filter: this.filterSelected(),
                page: this.pagination.page,
                perPage: this.pagination.perPage
            });

            this.items.replace(req);
            (filter == 'all') ? can.route.removeAttr('filter') : can.route.attr('filter', filter);

            if (!this.pagination.page || this.pagination.page == 1) {
                can.route.removeAttr('page');
            } else {
                can.route.attr('page', this.pagination.page);
            }

            if (!this.pagination.perPage || this.pagination.perPage == this.pagination.defaultPerPage) {
                can.route.removeAttr('perPage');
            } else {
                can.route.attr('perPage', this.pagination.perPage);
            }

            can.when(req).fail(can.proxy(function () {
                this.apiFailed = true;
                if (typeof callback === 'function') callback();
            }, this));

            can.when(req).done(can.proxy(function (models) {
                if (typeof callback === 'function') callback();
                $(this.options.eventFirstChild).trigger('click');
                this.pagination.attr('totalItems', models.count);
            }, this));

            return req;
        },

        getDefaultFilter: function () {
            var filter = can.route.attr('filter') ? can.route.attr('filter') : this.options.defaultFilter;
            if (typeof this.options.sideMenu[filter] === 'undefined') {
                filter = 'all';
            }
            return can.compute(filter);
        },

        pageTitle: function () {
            return this.options.sideMenu[this.filterSelected()].label;
        }
    });
});
