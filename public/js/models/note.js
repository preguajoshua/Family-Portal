define(['moment', 'can/model'], function(moment) {

	var model = can.Model.extend({
		parseModel: "data",
		id: "Id",
	  	findAll: 'GET /front/notes',
	  	findOne: 'GET /front/notes/{id}',
	  	create:  'POST /front/notes',
	  	update: function(id, attrs) {
	  		return $.ajax({
			  	url: '/front/notes/'+id,
			  	type: 'PUT',
			  	data: attrs,
			});
	  	},
	  	// update:  'PUT /front/notes/{id}',
	  	destroy: 'DELETE /front/notes/{id}'
	},{});

	model.prototype.rules = {
		Title: {
	        validators: {
	            notEmpty: {
	                message: 'The title is required'
	            }
	        }
	    },
		StartDate: {
			// container: '#date-picker',
	        validators: {
				date: {
	                format: 'MM/DD/YYYY',
	                message: 'The start date is not a valid'
	            },
	            callback: {
                    message: 'The date is not in the range',
                    callback: function(value, validator) {
                        var m = new moment(value, 'MM/DD/YYYY', true);
                        if (!m.isValid()) {
                            return false;
                        }

                        var today = new Date();
                        // Check if the date in our range
                        return m.isSameOrAfter(moment((today.getMonth()+1)+'/'+today.getDate()+'/'+today.getFullYear(), 'M/DD/YYYY', true));
                    }
                }
            }
		},
		StartTime: {
			// container: '#date-picker',
			validators: {
				notEmpty: {
                    message: 'The start time is required'
                },
                // regexp: {
                //     regexp: /^(09|1[0-7]{1}):[0-5]{1}[0-9]{1}$/,
                //     message: 'The start time must be between 09:00 and 17:59'
                // },
				callback: {
                    message: 'The start time must be between 12:00 AM and 11:59 PM',
	                callback: function(value, validator) {
	                	var d = moment(value, 'h:mm A', true);
	                	console.log(d);
	                	if(d === null || !d.isValid()){
	                		return false;
	                	}
	                	return true;
	                }
	            }
            }
		}
	}

	return model;
});
