define([
    'app/container',
    'fullcalendar',
    'moment',
    'can/view/stache',
    'can/model',
    'can/control/route',
    'can/route/pushstate',
], function(ioc) {
    return can.Control.extend({
        init: function(element) {
            ioc.app.hideLoader();

            element.html(can.view('views/login.stache', self));

            ioc.ready(true);
        },
        'form submit': function(element, event) {
            event.preventDefault();

            ioc.app.showLoader();

            $.post(element.attr('action'), element.serialize(), function(data) {
                if (data.status == 'success') {
                    ioc.render(false);
                    var promise = ioc.app.boot();

                    promise.then(function() {
                        if (ioc.previousRoute) {
                            can.route.attr(ioc.attr('previousRoute'), true);
                        } else {
                            can.route.attr({
                                controller: ioc.Config.redirectTo
                            }, true);
                        }
                    });
                }
            });
        }
    });
});