define([
    'app/container',
    'models/contact',
    'models/physician',
    'fullcalendar',
    'moment',
    'can/view/stache',
    'can/model',
    'can/control/route',
    'can/route/pushstate',
    'pace',
    'sticky'
], function (ioc, contactModel, physicianModel) {
    return ioc.Controller({
        defaults: {
            defaultFilter: 'all',
            filtersLabel: ['family', 'physicians', 'agency'],
            sideMenuClass: '.box-menu-contacts',
            view: 'views/contacts'
        }
    }, {
        init: function () {
            this.contacts = null;
            this.provider = null;
            this.physicians = null;
            this.filterSelected = null;
            this.filters = null;
            this.firstInit = true;
            this.apiSuccessful = true;

            this.provider = ioc.attr('provider');
            $(this.options.sideMenuClass).sticky({
                topSpacing: 90
            });

            var req1 = contactModel.findAll({}, can.proxy(function (items) {
                this.contacts = items;
            }, this));

            var req2 = physicianModel.findAll({}, can.proxy(function (items) {
                this.physicians = items;
            }, this));

            this.filterSelected = can.compute(can.route.attr('filter') ? can.route.attr('filter') : this.options.defaultFilter);

            can.when(req1, req2).done(can.proxy(function () {
                this.initFilters();
                this.render(this.options.view, this);
                this.updateFilter(this.filterSelected());
            }, this));

            can.when(req1).fail(can.proxy(function () {
                this.initFilters();
                this.apiSuccessful = false;
                 this.render(this.options.view, this);
                this.updateFilter(this.filterSelected());
            }, this));

            can.when(req2).fail(can.proxy(function () {
                this.initFilters();
                this.apiSuccessful = false;
                 this.render(this.options.view, this);
                this.updateFilter(this.filterSelected());
            }, this));
        },

        initFilters: function () {
            var filters = {};
            for (var i in this.options.filtersLabel) {
                filters[this.options.filtersLabel[i]] = true;
            }

            this.filters = new can.Map(filters);
        },

        resetFilter: function () {
            this.filterSelected(this.options.defaultFilter);

            for (var i in this.options.filtersLabel) {
                var label = this.options.filtersLabel[i];
                this.filters.attr(label, true);
            }
            can.route.removeAttr('filter');
        },

        updateFilter: function (filter) {
            if (this.firstInit) {
                this.firstInit = false;
            } else {
                ioc.app.disableRoute();
            }

            if (filter == this.options.defaultFilter)
                return this.resetFilter();

            for (var i in this.options.filtersLabel) {
                var label = this.options.filtersLabel[i];
                if (filter == label) {
                    this.filters.attr(label, true);
                    this.filterSelected(label);

                    can.route.attr({
                        filter: label
                    });
                } else {
                    this.filters.attr(label, false);
                }
            }
        }
    });
});
