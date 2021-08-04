define(['models/sources', 'can/model'], function(Sources) {
	return can.Model.extend({
		create: "POST /front/billing",
		update : function(id, attrs) {
            return $.post("/front/billing/customers/"+id, {default_source: attrs.default_source}, null,"json").fail(function(XmlHttpRequest) {
                console.log(XmlHttpRequest.responseJSON);
            });
		},
		findOne: 'GET /front/billing/sources'
	}, {
		define: {
			sources: {
				Type: Sources.List
			}
		}
	});
});
