define([
    'app/container'
], function(ioc) {
    return ioc.Controller({
        init: function() {
            this.render('views/profile', {user: ioc.user});
        },
    });
});
