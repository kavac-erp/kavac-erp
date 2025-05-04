<template>
	<div>
		<a class="btn btn-default btn-xs btn-icon btn-action"
		   href="#" title="Solicitud de Prorroga" data-toggle="tooltip"
		   :disabled="(((state == 'Aprobado')||(state == 'Pendiente por entrega')) && hasEquipments && asset_request_extension)?false:true"
		   @click="(((state == 'Aprobado')||(state == 'Pendiente por entrega')) && hasEquipments && asset_request_extension)?
		   			showDate('add_prorroga'+ requestid, delivery_date, requestid, $event):viewMessage()">
			<i class="fa fa-calendar-plus-o"></i>
		</a>

		<div :id="'add_prorroga'+ requestid" class="modal fade text-left" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-xs">
				<div class="modal-content">
					<div class="modal-header">
						<button  @click="reset()" type="button" class="close" data-dismiss="modal" aria-label="Close" >
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-meeting-add ico-2x"></i>
							Solicitud de Prorroga
						</h6>
					</div>

					<div class="modal-body">

						<div class="alert alert-danger" v-if="errors.length > 0">
							<div class="container">
								<div class="alert-icon">
									<i class="now-ui-icons objects_support-17"></i>
								</div>
								<strong>Cuidado!</strong> Debe verificar los siguientes errores antes de continuar:
								<button type="button" class="close" data-dismiss="alert" aria-label="Close"
										@click.prevent="errors = []">
									<span aria-hidden="true">
										<i class="now-ui-icons ui-1_simple-remove"></i>
									</span>
								</button>
								<ul>
									<li v-for="error in errors" :key="error">{{ error }}</li>
								</ul>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label>Fecha de entrega actual</label>
					        		<input type="date"
										data-toggle="tooltip"
										:min="add_period(delivery_date, 1, 'days', format = 'YYYY-MM-DD')"
										:max="date_max"
										id="delivery_date"
										class="form-control"
										v-model="record.delivery_date">
								</div>
							</div>
						</div>
					</div>

	                <div class="modal-footer">

	                	<button type="button" @click="reset()"
								class="btn btn-default btn-sm btn-round btn-modal-close"
	                			data-dismiss="modal">
	                		Cerrar
	                	</button>
	                	<button type="button" @click="createRecord('asset/requests/request-extensions')"
	                			class="btn btn-primary btn-sm btn-round btn-modal-save">
	                		Aceptar
		                </button>
		            </div>

		        </div>
		    </div>
		</div>
	</div>
</template>

<script>
	export default {
		data() {
			return {
				record: {
					id: '',
					delivery_date: '',
					asset_request_id: '',
				},
                date_max:'2050-07-05',
				date_min : '',
				records: [],
				errors: [],
			}
		},
		props: {
			requestid: Number,
			delivery_date: String,
			state: String,
			hasEquipments: Boolean,
			asset_request_extension: Boolean,
		},

		methods: {
			/**
             * Método que borra todos los datos del formulario
             *
             * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
            reset() {
                this.record = {
                    id: '',
					delivery_date: '',
					asset_request_id: ''
                };
            },

			 showDate(modal_id, delivery_date, requestid, event){
				const vm = this;
				vm.record = {
					id: '',
					delivery_date: vm.format_date(delivery_date, 'YYYY-MM-DD'),
					asset_request_id: requestid,
				};

				if ($("#" + modal_id).length) {
					$("#" + modal_id).modal('show');
				}
			},

            viewMessage() {
            	const vm = this;
            	vm.showMessage(
                    'custom', 'Alerta', 'danger', 'screen-error',
                    'La solicitud está en un tramite que no le permite acceder a esta funcionalidad'
                );
            	return false;
            },

            /**
	         * Método que permite crear o actualizar un registro
	         *
	         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
	         *
	         * @param  {string} url    Ruta de la acción a ejecutar para la creación o actualización de datos
	         * @param  {string} list   Condición para establecer si se cargan datos en un listado de tabla.
	         *                         El valor por defecto es verdadero.
	         * @param  {string} reset  Condición que evalúa si se inicializan datos del formulario.
	         *                         El valor por defecto es verdadero.
	         */
	        async createRecord(url, list = true, reset = true) {
	            const vm = this;
	            url = vm.setUrl(url);

	            if (vm.record.id) {
	                vm.updateRecord(url);
	            }
	            else {
	                vm.loading = true;
	                var fields = {};

	                for (var index in vm.record) {
	                    fields[index] = vm.record[index];
	                }
	                await axios.post(url, fields).then(response => {
	                    if (typeof(response.data.redirect) !== "undefined") {
	                        location.href = response.data.redirect;
	                    }
	                    else {
	                        vm.errors = [];
	                        if (reset) {
	                            vm.reset();
	                        }
	                        if (list) {
	                            vm.readRecords(url);
	                        }

	                        vm.showMessage('store');
	                    }
	                }).catch(error => {
	                    vm.errors = [];

	                    if (typeof(error.response) !="undefined") {
	                    	if (error.response.status == 403) {
		                        vm.showMessage(
		                            'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
		                        );
		                    }

	                        for (var index in error.response.data.errors) {
	                            if (error.response.data.errors[index]) {
	                                vm.errors.push(error.response.data.errors[index][0]);
	                            }
	                        }
	                    }

	                });

	                vm.loading = false;
	            }

	        },

	        /**
	         * Método que permite actualizar información
	         *
	         * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
	         *
	         * @param  {string} url Ruta de la acci´on que modificará los datos
	         */
	        async updateRecord(url) {
	            const vm = this;
	            vm.loading = true;
	            var fields = {};
	            url = vm.setUrl(url);

	            for (var index in vm.record) {
	                fields[index] = vm.record[index];
	            }
	            await axios.patch(`${url}${(url.endsWith('/'))?'':'/'}${vm.record.id}`, fields).then(response => {
	                if (typeof(response.data.redirect) !== "undefined") {
	                    location.href = response.data.redirect;
	                }
	                else {
	                    vm.readRecords(url);
	                    vm.reset();
	                    vm.showMessage('update');
	                }

	            }).catch(error => {
	                vm.errors = [];

	                if (typeof(error.response) !="undefined") {
	                	if (error.response.status == 403) {
	                        vm.showMessage(
	                            'custom', 'Acceso Denegado', 'danger', 'screen-error', error.response.data.message
	                        );
	                    }

	                    for (var index in error.response.data.errors) {
	                        if (error.response.data.errors[index]) {
	                            vm.errors.push(error.response.data.errors[index][0]);
	                        }
	                    }
	                }
	            });
	            vm.loading = false;
	        },
		},
		mounted() {
            const vm = this;
            vm.record.delivery_date = vm.delivery_date;
			vm.record.asset_request_id = vm.requestid;
        },
	};
</script>
