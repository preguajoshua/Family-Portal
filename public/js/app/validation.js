define(['app/container', 'bootstrap-validation'], function(core) {
	
	core.Validator = function(form, modelOrRules) {
		
		if (modelOrRules === undefined || typeof modelOrRules !== 'object') {
			return;
		} else {
			
			var fields = modelOrRules.rules || modelOrRules,
			
				inputs = form.find('[can-value], [can-datepicker]').each(function(i, el) {
					
					// Get Bound Model Attribute
					var name = el.getAttribute('can-value') || el.getAttribute('can-datepicker');
					
					// Set element "name" for Bootstrap Validator 
					el.setAttribute('name', name);
					
					// Instantiate if does not exist
					if (fields[name] === undefined)
						fields[name] = { validators: {} };
					
					// Add ModelState validator
					fields[name].validators.ModelState = { enabled:false };
				}),
				
				// TODO Memory Leak
				// bv should be destroyed when view is gone
				bv = this.bv = form.bootstrapValidator({
					excluded: [':hidden', ':not(:visible)'],
					feedbackIcons: {
						// valid: 'fa fa-check',
						// invalid: 'fa fa-times',
						// validating: 'fa fa-refresh fa-spin'
					},
					fields: fields,
					// live: 'submitted'
				}).data('bootstrapValidator');
				
			// Only want to display errored states, hide success states
			form.on('success.field.bv', function(e, data) {
				var $parent = data.element.parents('.form-group');
				// Remove the has-success class
				$parent.removeClass('has-success');
				// Hide the success icon
				// data.element.data('bv.icon').hide();
			});
			
			// Datepicker needs to trigger validation because no key events fire.
			inputs.filter('[can-datepicker]').on('changeDate', function(e) {
				bv.revalidateField(e.target.getAttribute('name'));
			});
		}
	}
				
	core.Validator.prototype.isValid = function() {
		for (var key in this.bv.options.fields) {
			this.bv.enableFieldValidators(key, false, 'ModelState');
		}
		return this.bv.validate().isValid();
	}
	
	core.Validator.prototype.reset = function() {
		return this.bv.resetForm();
	}
	
	core.Validator.prototype.resolveModelState = function(ModelState) {
		for (var key in ModelState) {
			
			this.bv.updateOption(key, 'ModelState', 'message', ModelState[key]);
			this.bv.enableFieldValidators(key, true, 'ModelState');
			this.bv.revalidateField(key);
			
			// status.field.fv
			// form.find('[name="'+key+'"]').one('change', function(e, data) {
			//	bv.updateOption(key, 'ModelState', 'valid', true);
			//	bv.revalidateField(key);
			// });
		}
	}
	
	core.Validator.prototype.toggleManual = function(field, onOff) {
		this.bv.enableFieldValidators(field, onOff, 'manual');
		this.bv.revalidateField(field);
	}
	
	return core.Validator;
	
});