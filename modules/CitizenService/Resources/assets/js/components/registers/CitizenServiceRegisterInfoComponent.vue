<template>
	<section>
		<div id="CitizenServiceRegisterInfo" class="modal fade" tabindex="-1" role="dialog"
				aria-labelledby="CitizenServiceRegisterInfoModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document" style="max-width:60rem">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<h6>
							<i class="icofont icofont-read-book ico-2x"></i>
							Información de Cronograma Registrado
						</h6>
					</div>

				<div class="modal-body">
					<div class="tab-content">
						<div class="tab-pane active" id="info_general" role="tabpanel">
		                    <div class="row">
		                    	<div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Fecha del registro</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.date_register}}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Nombre del director</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.payroll_staff ? record.payroll_staff.first_name + ' ' + record.payroll_staff.last_name : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Código de la solicitud</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.request_code }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Actividades</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.activities }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Correo electrónico</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.email }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Fecha de inicio</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.start_date }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Fecha de culminación</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.end_date }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>Porcentaje de cumplimiento</strong>
                                        <div class="row" style="margin: 1px 0">
                                            <span class="col-md-12">
                                                {{ record.percent }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <strong>Equipo responsable</strong>
                                        <ol>
	                                        <div v-for="(job, index) in record.team_name" class="row" :key="index">
	                                    		<div class="col-md-12">
			                                        <div class="form-group">
			                                        	<li><div class="row" style="margin: 1px 0">
			                                                <span class="col-md-12">
			                                                    {{ job.text }}
			                                                </span>
			                                            </div></li>
			                                        </div>
			                                    </div>
	                                    	</div>
	                                    </ol>

                                    </div>
                                </div>
							</div>
						</div>
					</div>
	            </div>

				<div class="modal-footer">

	                <button type="button" class="btn btn-default btn-sm btn-round btn-modal-close" data-dismiss="modal">
	                	Cerrar
	                </button>
		        </div>

		        </div>
		    </div>
		</div>
	</section>
</template>

<script>
	export default {
		data() {
			return {
				record: {
					date_register: '',
					payroll_staff_id: '',
					request_code: '',
					team_name: [],
					activities: '',
					start_date: '',
					end_date: '',
					email: '',
					percent: ''
				},
				errors: [],

			}
		},
		created() {


		},
		methods: {
			/**
             * Método que borra todos los datos del formulario
             *
             * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
             */
            reset() {
            },
		},
	};
</script>
