<template>
	<select class="form-control select2" data-toggle="tooltip" title="Seleccione una opción">
		<slot></slot>
	</select>
</template>

<script>
	export default {
		props: ['options', 'value'],
		mounted: function() {
			var vm = this;
			this.options.slice(0).unshift({id: '', text: 'Seleccione...'});
			$(this.$el).select2({data: this.options})
					   .val(this.value)
					   .trigger('change')
					   .on('change', function() {
					   		vm.$emit('input', this.value)
					   });
		},
		/**
		 * Monitorea el valor de un select y carga los datos del select dependiente cuando este cambia su valor
		 *
		 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
		 * @type {Object}
		 */
		watch: {
			value: function(value) {
				$(this.$el).val(value).trigger('change');
			},
			options: function(options) {
				try {
					options.slice(0).unshift({id: '', text: 'Seleccione...'});
					$(this.$el).empty().trigger('change').select2({data: options});
				}
				catch(err) {}
			}
		},
		destroyed: function() {
			$(this.$el).off().select2('destroy');
		}
	};
</script>
